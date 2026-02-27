## 🚀 COMPLETE Postman Manual Testing Guide: Core Booking Engine Architecture

This guide covers everything outlined in your Booking Engine Backend Architecture Document. By following these exact steps, you will verify every business rule, security check, and concurrency lock in Postman.

---

### Prerequisites
1. Ensure your Laravel server is running (`php artisan serve` at `http://127.0.0.1:8000`).
2. Have Postman open.

---

### Phase 1: Authentication & Authorization Module
*Validating Sanctum-based access and retrieving our Bearer token.*

**Test 1: Admin Login**
- **Method:** `POST`
- **URL:** `{{base_url}}/login`
- **Body:**
  ```json
  {
      "email": "admin@example.com",
      "password": "password"
  }
  ```
- **Action:** Send -> Copy the `access_token` from the response. Use this in the `Authorization > Bearer Token` tab for all restricted requests moving forward.

---

### Phase 2: Core Components Validation (Stores & Services)
*Testing Input Validation and data retrieval constraints.*

**Test 2.1: Get Stores**
- **Method:** `GET`
- **URL:** `{{base_url}}/stores`
- **Action:** Send -> Copy the `"id"` of an active store. Let's call this `YOUR_STORE_ID`.

**Test 2.2: Get Services by Store**
- **Method:** `GET`
- **URL:** `{{base_url}}/stores/YOUR_STORE_ID/services`
- **Action:** Send -> Copy the `"id"` of a service. Let's call this `YOUR_SERVICE_ID`.

---

### Phase 3: Slot Management Engine (Availability)
*Validating the Time Slot endpoints exist and status logic.*

**Test 3.1: Find Available Slots**
- **Method:** `GET`
- **URL:** `{{base_url}}/stores/YOUR_STORE_ID/slots?date=2026-03-01` *(Ensure the date is valid)*
- **Action:** Send -> Note the list of slots where `"status": "available"`. 
- **Next:** Copy the `"id"` of an available slot (`YOUR_SLOT_ID`).

---

### Phase 4: Security & Race Condition Prevention (CRITICAL)
*Testing the Slot Locking Strategy, Concurrency, and Validations.*

**Test 4.1: Lock an Available Slot**
- **Method:** `POST`
- **URL:** `{{base_url}}/slots/YOUR_SLOT_ID/lock`
- **Auth:** Bearer Token -> `Paste Your Token`
- **Body:**
  ```json
  {
      "date": "2026-03-01" 
  }
  ```
- **Action:** Send.
- **Expected Result:** `200 OK`
  ```json
  {
      "status": "locked",
      "expires_at": "..."
  }
  ```
> **ARCHITECTURE VALIDATED:** The slot is immediately locked for your user account for exactly 5 minutes with pessimistic DB row locking (`SELECT FOR UPDATE`).

**Test 4.2: Concurrency & Double Booking Attempt**
- **Action:** Without changing anything, hit **Send** on the Lock request AGAIN immediately.
- **Expected Result:** `409 Conflict`
  ```json
  {
      "message": "Slot unavailable"
  }
  ```
> **ARCHITECTURE VALIDATED:** The engine instantly prevents duplicate reservations.

---

### Phase 5: Backend Booking Confirmation
*Testing transactions, status updates, and constraint finalization.*

**Test 5.1: Confirm Valid Booking**
- **Method:** `POST`
- **URL:** `{{base_url}}/bookings`
- **Auth:** Bearer Token -> `Paste Your Token`
- **Body:**
  ```json
  {
      "store_id": "YOUR_STORE_ID",
      "service_ids": [
          "YOUR_SERVICE_ID"
      ],
      "slot_id": "YOUR_SLOT_ID",
      "date": "2026-03-01" 
  }
  ```
- **Action:** Send.
- **Expected Result:** `201 Created`
  ```json
  {
      "booking_id": "...",
      "confirmation_number": "BK-...",
      "booking_details": "Booking confirmed for 2026-03-01 at ...",
      "status": "confirmed"
  }
  ```
> **ARCHITECTURE VALIDATED:** The `TimeSlot` is updated to `booked`. The DB transaction securely creates the `Bookings` record natively. 

**Test 5.2: Verification**
- Return to **Test 3.1** (`GET /slots`) and hit Send.
- The slot you locked will no longer exist in the `available` list.

---

### Phase 6: Edge Cases & API Testing Constraints
*Explicit architectural breach tests.*

**Test 6.1: Unauthorized Access**
- Go back to **Test 4.1** (Lock Slot).
- Remove your Bearer Token (set Authorization to 'No Auth').
- Hit Send.
- **Expected Result:** `401 Unauthorized`. The engine prevents unauthenticated interactions entirely.

**Test 6.2: Invalid Service Selection**
- Go to **Test 5.1** (Confirm Booking).
- Change your `"YOUR_SERVICE_ID"` to a fake UUID string (e.g., `"f000b000-0000-0000-0000-000000000000"`).
- Hit Send.
- **Expected Result:** `422 Unprocessable Entity - Validation Error. The selected service is invalid.`

**Test 6.3: Testing Lock Expiry (Abandoned Slots)**
1. Find a *new* available slot and Lock it (Run Test 4.1 again).
2. Look at the `"expires_at"` timestamp. 
3. *Wait exactly 5 minutes.* 
4. Attempt to confirm the booking (Run Test 5.1).
5. **Expected Result:** `422 Unprocessable Entity - Slot lock invalid or expired.` The `expire:slot-locks` background job automatically cleared your abandoned cart in the database!
