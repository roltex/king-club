# 📡 API Documentation

Complete API reference for the Poker Tournament Reservation System.

## Base URL

```
http://localhost:8000/api
```

## Database

This API uses **SQLite** - a lightweight, file-based database that requires no server setup. Perfect for development and small to medium production deployments.

## Response Format

All API responses follow this structure:

### Success Response
```json
{
  "data": {},
  "status": "success"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": {}
}
```

## Endpoints

### 1. Create Reservation

Create a new tournament reservation.

**Endpoint:** `POST /reserve`

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+995555123123",
  "email": "john@example.com"
}
```

**Validation Rules:**
- `first_name`: required, string, max 255 characters
- `last_name`: required, string, max 255 characters
- `phone`: required, string, max 20 characters, unique
- `email`: optional, email format, max 255 characters

**Success Response (Seat Available):**
```json
{
  "status": "reserved",
  "reservation_id": "550e8400-e29b-41d4-a716-446655440000",
  "table": 4,
  "seat": 7,
  "qr": "http://localhost:5173/checkin?id=550e8400-e29b-41d4-a716-446655440000",
  "message": "Your seat has been reserved successfully!"
}
```

**Success Response (Waiting List):**
```json
{
  "status": "waiting",
  "waiting_number": 2,
  "reservation_id": "550e8400-e29b-41d4-a716-446655440000",
  "message": "All seats are full. You have been added to the waiting list."
}
```

**Error Response (422):**
```json
{
  "success": false,
  "errors": {
    "phone": ["The phone has already been taken."]
  }
}
```

---

### 2. Get Reservation by ID

Retrieve reservation details by reservation ID.

**Endpoint:** `GET /reservation/{id}`

**Parameters:**
- `id` (path): Reservation UUID

**Success Response:**
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+995555123123",
  "email": "john@example.com",
  "status": "reserved",
  "table": 4,
  "seat": 7,
  "qr": "http://localhost:5173/checkin?id=550e8400-e29b-41d4-a716-446655440000",
  "created_at": "2025-11-16T10:30:00.000000Z"
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Reservation not found"
}
```

---

### 3. Get Reservation by Phone

Retrieve reservation by phone number.

**Endpoint:** `GET /reservation/phone/{phone}`

**Parameters:**
- `phone` (path): Phone number

**Example:** `/reservation/phone/+995555123123`

**Success Response:**
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+995555123123",
  "email": "john@example.com",
  "status": "reserved",
  "table": 4,
  "seat": 7,
  "qr": "http://localhost:5173/checkin?id=550e8400-e29b-41d4-a716-446655440000",
  "created_at": "2025-11-16T10:30:00.000000Z"
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "No reservation found for this phone number"
}
```

---

### 4. Check-In

Process tournament check-in via QR code.

**Endpoint:** `POST /checkin`

**Request Body:**
```json
{
  "reservation_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Success Response:**
```json
{
  "success": true,
  "user": "John Doe",
  "table": 4,
  "seat": 7,
  "checkin_time": "2025-11-16T14:32:00+00:00"
}
```

**Error Responses:**

Already Checked In:
```json
{
  "success": false,
  "message": "Already checked in at 2025-11-16 14:32:00"
}
```

Waiting List User:
```json
{
  "success": false,
  "message": "Cannot check in from waiting list"
}
```

Cancelled Reservation:
```json
{
  "success": false,
  "message": "This reservation has been cancelled"
}
```

---

### 5. Cancel Reservation

Cancel an existing reservation.

**Endpoint:** `POST /reservation/{id}/cancel`

**Parameters:**
- `id` (path): Reservation UUID

**Success Response:**
```json
{
  "success": true,
  "message": "Reservation cancelled successfully"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Cannot cancel this reservation"
}
```

---

### 6. Get Statistics

Get tournament statistics.

**Endpoint:** `GET /statistics`

**Success Response:**
```json
{
  "total_seats": 54,
  "occupied_seats": 32,
  "available_seats": 22,
  "reserved": 28,
  "checked_in": 4,
  "waiting_list": 3,
  "cancelled": 2
}
```

---

### 7. Get Table Layout

Get complete table and seat layout.

**Endpoint:** `GET /tables`

**Success Response:**
```json
{
  "tables": [
    {
      "table_number": 1,
      "seats": [
        {
          "seat_number": 1,
          "occupied": true,
          "status": "reserved",
          "player": {
            "name": "John Doe",
            "phone": "+995555123123",
            "checked_in": false
          }
        },
        {
          "seat_number": 2,
          "occupied": false,
          "status": null,
          "player": null
        }
        // ... seats 3-9
      ]
    }
    // ... tables 2-6
  ]
}
```

---

### 8. Get Waiting List

Get all users on the waiting list.

**Endpoint:** `GET /waiting-list`

**Success Response:**
```json
{
  "waiting_list": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "Jane Smith",
      "phone": "+995555456789",
      "position": 1,
      "created_at": "2025-11-16T15:00:00.000000Z"
    },
    {
      "id": "660e8400-e29b-41d4-a716-446655440001",
      "name": "Bob Johnson",
      "phone": "+995555789012",
      "position": 2,
      "created_at": "2025-11-16T15:05:00.000000Z"
    }
  ]
}
```

---

### 9. Health Check

Check API health status.

**Endpoint:** `GET /health`

**Success Response:**
```json
{
  "status": "ok",
  "timestamp": "2025-11-16T10:30:00+00:00"
}
```

---

## Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

## Rate Limiting

API endpoints are rate-limited to prevent abuse:

- **Limit:** 60 requests per minute per IP
- **Header:** `X-RateLimit-Limit: 60`
- **Remaining:** `X-RateLimit-Remaining: 59`

When limit exceeded:
```json
{
  "message": "Too Many Attempts."
}
```

## CORS

Allowed origins:
- `http://localhost:5173`
- `http://localhost:3000`
- Configured frontend URL

## Authentication

Public endpoints do not require authentication.

Admin panel endpoints (Filament) require:
- Session-based authentication
- CSRF token protection

## Error Handling

### Validation Errors (422)
```json
{
  "success": false,
  "errors": {
    "field_name": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

### Server Errors (500)
```json
{
  "success": false,
  "message": "Internal server error"
}
```

## Example Usage

### JavaScript (Axios)

```javascript
import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json'
  }
})

// Create reservation
const createReservation = async () => {
  try {
    const response = await api.post('/reserve', {
      first_name: 'John',
      last_name: 'Doe',
      phone: '+995555123123',
      email: 'john@example.com'
    })
    console.log(response.data)
  } catch (error) {
    console.error(error.response.data)
  }
}

// Get statistics
const getStats = async () => {
  const response = await api.get('/statistics')
  console.log(response.data)
}
```

### cURL

```bash
# Create reservation
curl -X POST http://localhost:8000/api/reserve \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+995555123123",
    "email": "john@example.com"
  }'

# Get reservation
curl http://localhost:8000/api/reservation/550e8400-e29b-41d4-a716-446655440000

# Check-in
curl -X POST http://localhost:8000/api/checkin \
  -H "Content-Type: application/json" \
  -d '{
    "reservation_id": "550e8400-e29b-41d4-a716-446655440000"
  }'
```

## Webhook Events (Future)

Planned webhook events for future versions:
- `reservation.created`
- `reservation.checked_in`
- `reservation.cancelled`
- `waiting_list.promoted`

---

**Last Updated:** November 16, 2025
**API Version:** 1.0.0

