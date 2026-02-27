# Slot Booking System - API Testing Guide (Postman)

This guide provides step-by-step instructions on how to test the Slot Booking System APIs using Postman.

## Base URL
The API base URL is:
```text
http://127.0.0.1:8000/api/v1
```
*(Make sure your Laravel server is running using `php artisan serve`)*

---

## 🔐 1. Authentication APIs

### 1.1 Register User
- **Method:** `POST`
- **URL:** `{{base_url}}/register`
- **Body (raw JSON):**
```json
{
    "name": "Test User",
    "email": "testuser@example.com",
    "password": "password",
    "password_confirmation": "password",
    "phone": "1234567890"
}
```

### 1.2 Login User
- **Method:** `POST`
- **URL:** `{{base_url}}/login`
- **Body (raw JSON):**
```json
{
    "email": "testuser@example.com",
    "password": "password"
}
```
**Important:** Copy the `access_token` from the response. You will need it for authenticated endpoints.

### 1.3 Logout
- **Method:** `POST`
- **URL:** `{{base_url}}/logout`
- **Headers:**
  - `Authorization`: `Bearer YOUR_ACCESS_TOKEN`

---

## 🏪 2. Store & Service APIs (Public)

### 2.1 Get Active Stores
- **Method:** `GET`
- **URL:** `{{base_url}}/stores`

### 2.2 Get Single Store Details
- **Method:** `GET`
- **URL:** `{{base_url}}/stores/{store_id}`

### 2.3 Get Store Services
- **Method:** `GET`
- **URL:** `{{base_url}}/stores/{store_id}/services`

---

## 📅 3. Slot APIs

### 3.1 Get Store Time Slots
- **Method:** `GET`
- **URL:** `{{base_url}}/stores/{store_id}/slots?date=YYYY-MM-DD`
- **Query Params:**
  - `date`: e.g., `2024-03-01`

### 3.2 Lock a Slot (Requires Auth)
- **Method:** `POST`
- **URL:** `{{base_url}}/slots/{slot_id}/lock`
- **Headers:**
  - `Authorization`: `Bearer YOUR_ACCESS_TOKEN`
- **Body (raw JSON):**
```json
{
    "date": "2024-03-01"
}
```
*(Locks the slot for 5 minutes before confirming booking)*

---

## 🛍️ 4. Booking APIs (Requires Auth)

### 4.1 Create Booking
- **Method:** `POST`
- **URL:** `{{base_url}}/bookings`
- **Headers:**
  - `Authorization`: `Bearer YOUR_ACCESS_TOKEN`
- **Body (raw JSON):**
```json
{
    "store_id": "YOUR_STORE_UUID",
    "service_ids": [
        "YOUR_SERVICE_UUID"
    ],
    "slot_id": "YOUR_SLOT_UUID",
    "date": "2024-03-01"
}
```

### 4.2 Get Booking Details
- **Method:** `GET`
- **URL:** `{{base_url}}/bookings/{booking_id}`
- **Headers:**
  - `Authorization`: `Bearer YOUR_ACCESS_TOKEN`

### 4.3 Cancel Booking
- **Method:** `POST`
- **URL:** `{{base_url}}/bookings/{booking_id}/cancel`
- **Headers:**
  - `Authorization`: `Bearer YOUR_ACCESS_TOKEN`

---

## ⚙️ 5. Admin APIs (Requires Auth & Admin Role)

*Ensure the user you log in as has the `admin` role.*

### 5.1 Create Store
- **Method:** `POST`
- **URL:** `{{base_url}}/admin/stores`
- **Headers:**
  - `Authorization`: `Bearer YOUR_ACCESS_TOKEN`
- **Body (raw JSON):**
```json
{
    "name": "New Test Store",
    "location": "123 Business Street",
    "description": "A great new store.",
    "status": "active",
    "working_days": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]
}
```

### 5.2 Update Store
- **Method:** `PUT`
- **URL:** `{{base_url}}/admin/stores/{store_id}`
- **Headers:**
  - `Authorization`: `Bearer YOUR_ACCESS_TOKEN`
- **Body (raw JSON):**
```json
{
    "name": "Updated Store Name",
    "location": "456 New Street"
}
```

### 5.3 Update Store Status
- **Method:** `PATCH`
- **URL:** `{{base_url}}/admin/stores/{store_id}/status`
- **Headers:**
  - `Authorization`: `Bearer YOUR_ACCESS_TOKEN`
- **Body (raw JSON):**
```json
{
    "status": "inactive"
}
```

### 5.4 Add Service to Store
- **Method:** `POST`
- **URL:** `{{base_url}}/admin/stores/{store_id}/services`
- **Headers:**
  - `Authorization`: `Bearer YOUR_ACCESS_TOKEN`
- **Body (raw JSON):**
```json
{
    "name": "Haircut",
    "description": "Standard Haircut",
    "duration_minutes": 30,
    "price": 25.00,
    "status": "active"
}
```

---

## 💡 How to Use Postman Environments (Pro Tip)
1. In Postman, go to **Environments** on the left panel.
2. Click **Create Environment** and name it `Slot Booking Local`.
3. Add a new variable:
   - **Variable:** `base_url`
   - **Initial Value:** `http://127.0.0.1:8000/api/v1`
   - **Current Value:** `http://127.0.0.1:8000/api/v1`
4. Make sure to select this environment from the top-right dropdown in Postman.
5. You can also add variables like `store_id`, `service_id`, `slot_id`, and `access_token` as you get them from responses, so you don't have to copy-paste them manually.
