# Postman API Testing Documentation - Slot Booking System

This document provides instructions on how to test the Slot Booking System APIs using Postman.

## Base URL
`http://localhost/api/v1`

## Authentication
Most endpoints require a Bearer Token. To get a token, you'll need to implement or use a login/register endpoint (not explicitly requested but required for `auth:sanctum`). For testing purposes, you can create a token in a seeder or use the `BookingFlowTest` logic.

### 1. Store APIs
- **List All Active Stores**
    - `GET /stores`
    - Returns paginated list of active stores.
- **Get Store Details**
    - `GET /stores/{store_id}`
    - Returns details of a specific active store.

### 2. Service APIs
- **Get Services by Store**
    - `GET /stores/{store_id}/services`
    - Returns all available services for the given store.

### 3. Time Slot APIs
- **Get Available Slots**
    - `GET /stores/{store_id}/slots?date=YYYY-MM-DD`
    - Returns slots for a specific date.
- **Lock a Slot (Requires Auth)**
    - `POST /slots/{slot_id}/lock`
    - Body: `{"date": "2026-03-01"}`
    - Logic: Locks the slot for 5 minutes.
    - Rate Limit: Max 5 locks per minute per user.

### 4. Booking APIs
- **Create Booking (Requires Auth)**
    - `POST /bookings`
    - Body:
        ```json
        {
          "store_id": "UUID",
          "service_ids": ["UUID"],
          "slot_id": "UUID",
          "date": "2026-03-01"
        }
        ```
    - Note: Slot must be locked by you first.
- **Get Booking Details (Requires Auth)**
    - `GET /bookings/{booking_id}`
- **Cancel Booking (Requires Auth)**
    - `POST /bookings/{booking_id}/cancel`

### 5. Admin APIs (Requires Auth)
- **Create Store**
    - `POST /admin/stores`
- **Update Store**
    - `PUT /admin/stores/{store_id}`
- **Toggle Store Status**
    - `PATCH /admin/stores/{store_id}/status`
    - Body: `{"status": "active"}` or `{"status": "inactive"}`
- **Create Service**
    - `POST /admin/stores/{store_id}/services`

---

## Testing Workflow (Postman)
1. **Fetch Stores**: Run `GET /stores` and copy a `store_id`.
2. **Fetch Services**: Run `GET /stores/{id}/services` and copy a `service_id`.
3. **Fetch Slots**: Run `GET /stores/{id}/slots?date=2026-03-01` and copy a `slot_id`.
4. **Lock Slot**: Run `POST /slots/{id}/lock` with a Bearer token.
5. **Create Booking**: Run `POST /bookings` using the IDs from previous steps.
6. **Verify**: Check the booking details or try to lock the same slot again (should fail).
