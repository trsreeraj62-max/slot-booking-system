# Booking Engine Backend Architecture & API Documentation

## 1. Overview
This document outlines the architecture, database design, core booking flow logic, and API testing guide for the reusable, scalable backend booking engine. This system can be integrated into any booking-based project (e.g., salon, clinic, event, rental).

### Core Flow
1. **Select Store:** User browses and selects a specific store.
2. **Select Services:** User selects one or more services offered by the store.
3. **Select Date & Time Slot:** User browses available time slots for a specific date and locks one.
4. **Backend Booking Confirmation:** User confirms the booking, which validates the locked slot and finalized the transaction.

### Key Features
- **Slot locking on selection:** Prevents double booking and race conditions.
- **Validation & security checks:** Ensures valid relationships and timestamps.
- **Background Expiry:** Unlocks abandoned slots seamlessly via a cron job.

---

## 2. System Architecture

### 2.1 High-Level Components
- **API Layer:** RESTful endpoints built with Laravel routes.
- **Business Logic Layer:** Controllers handling booking rules, validations, and transactions.
- **Slot Management Engine:** TimeSlot model, pessimistic row locking (`lockForUpdate()`), and expiry job.
- **Database Layer:** MySQL/PostgreSQL databases utilizing UUIDs for primary keys.
- **Authentication & Authorization Module:** Provided by Laravel Sanctum.

---

## 3. Database Design (Core Tables)
All primary keys (`id`) use **UUIDs**.

| Table Name | Description | Key Fields |
|---|---|---|
| **Users** | System users (customers & admins) | `id`, `name`, `email`, `password_hash`, `role` |
| **Stores** | Business entities accepting bookings | `id`, `name`, `location`, `status`, `created_by` |
| **Services** | Bookable items linked to stores | `id`, `store_id`, `name`, `duration_minutes`, `price` |
| **Time Slots** | Blocked times for booking | `id`, `store_id`, `start_time`, `end_time`, `slot_date`, `status`, `locked_by`, `lock_expires_at` |
| **Bookings** | Confirmed appointments | `id`, `user_id`, `store_id`, `service_id`, `slot_id`, `status` |

---

## 4. Core Booking Flow Logic

### Step 1: Slot Locking Strategy (Concurrency Protection)
When a user attempts to lock a slot:
1. Systems starts a DB transaction.
2. Selects the slot `FOR UPDATE` (Pessimistic Locking).
3. Validates that `status == "available"`.
4. Updates slot:
   - `status = "locked"`
   - `locked_by = user_id`
   - `lock_expires_at = NOW() + 10 minutes`
5. Commits transaction.

> **Note:** A cron job (`php artisan schedule:run`) continuously runs `expire:slot-locks` every minute. It runs `UPDATE time_slots SET status = 'available' WHERE status = 'locked' AND lock_expires_at < NOW()`.

### Step 2: Confirm Booking
During confirmation:
1. Starts DB transaction.
2. Validates:
   - `status == "locked"`
   - `locked_by == current_user`
   - `lock_expires_at > NOW()`
3. Creates a record in the `Bookings` table.
4. Updates the slot:
   - `status = 'booked'`
   - `locked_by = NULL`
5. Commits transaction.

---

## 5. Security & Rate Limiting
- **Database Row Locking:** Prevents multiple concurrent HTTP requests from acquiring the same slot simultaneously.
- **Transactions:** Ensures data consistency (if booking fails, slot reverts).
- **Rate Limiting:** The lock endpoint uses Laravel's `throttle:5,1` middleware (Max 5 lock attempts per minute per user).
- **Authentication:** Token expiration and authorization handled via Sanctum.

---

## 6. Manual Testing Guide (Postman)

Follow these steps exactly to simulate the backend architecture from end-to-end.

### Step 1: Login & Get Token
- **Endpoint:** `POST /api/v1/login`
- **Body:**
  ```json
  {
      "email": "admin@example.com",
      "password": "password"
  }
  ```
- **Action:** Copy the `access_token` from the response. Use this securely in the `Authorization: Bearer <token>` header for all subsequent requests.

### Step 2: Retrieve a Store
- **Endpoint:** `GET /api/v1/stores`
- **Action:** Find a store you like and **copy its `id`** (`STORE_ID`).

### Step 3: Retrieve Services for the Store
- **Endpoint:** `GET /api/v1/stores/{STORE_ID}/services`
- **Action:** Find a service you wish to book and **copy its `id`** (`SERVICE_ID`).

### Step 4: Find Available Time Slots
- **Endpoint:** `GET /api/v1/stores/{STORE_ID}/slots?date=YYYY-MM-DD` *(e.g. 2026-02-28)*
- **Action:** Locate a slot with `"status": "available"` and **copy its `id`** (`SLOT_ID`).

### Step 5: Lock the Slot (Protecting against concurrency)
- **Endpoint:** `POST /api/v1/slots/{SLOT_ID}/lock`
- **Body:**
  ```json
  {
      "date": "2026-02-28"
  }
  ```
- **Action:** Wait for the `200 OK` response with `"status": "locked"`. *(If you try sending this exact request a second time, it will rightfully fail with a 409 Conflict).*

### Step 6: Create & Confirm Booking
- **Endpoint:** `POST /api/v1/bookings`
- **Body:**
  ```json
  {
      "store_id": "YOUR_STORE_ID",
      "service_ids": [
          "YOUR_SERVICE_ID"
      ],
      "slot_id": "YOUR_SLOT_ID",
      "date": "2026-02-28"
  }
  ```
- **Action:** You will receive a `201 Created` with your `confirmation_number`. The slot is now officially marked as `booked` in the database!

---

## 7. Automated Testing
The system ships with comprehensive PHPUnit tests simulating the architecture.
- Run `php artisan test --filter BookingFlowTest` (Validates standard success operations).
- Run `php artisan test --filter AdvancedBookingTest` (Validates architecture edge cases like expired locks, unauthorized access, and double-booking attempts).
