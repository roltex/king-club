🔵 Poker Tournament Reservation & QR Check-In System
Technical Specification (SRS)
1. Project Overview
The goal is to build a simple, fast, user-friendly Poker Tournament Reservation System that:
Allows customers to reserve a seat online
Randomly assigns the customer to one of 6 poker tables, each with 9 seats (total 54 seats)
Places additional users into a Waiting List when all seats are full
Provides each customer with a unique QR code for tournament check-in
Allows staff to scan the QR code to confirm attendance
Provides an Admin Dashboard to view players, tables, seats, and waiting list
2. Tournament Structure
Item	Value
Total tables	6
Seats per table	9
Total available seats	54
If seats > 54	Add customer to Waiting List
3. User Reservation Flow
3.1 Customer Input (Reservation Form)
Form fields:
First Name (required)
Last Name (required)
Phone Number (required)
Email (optional)
Button: Reserve Seat
3.2 Seat Assignment Logic
If seats < 54 (available):
System randomly selects:
A table between 1–6
A seat between 1–9
If the seat is already taken → the system generates another random pair until a free seat is found.
The reservation is saved with:
User info
Random table
Random seat
Status: reserved
QR code generated for check-in
If seats = 54 (full):
The system assigns:
Status: waiting
Waiting Number = current waiting count + 1
The waiting user receives confirmation with “Waiting #X”.
4. Customer Confirmation Screen
4.1 If a seat is confirmed
Shows:
✔ Your reservation is confirmed
Table: X
Seat: Y
QR Code
Optional: “Cancel Reservation” button
4.2 If placed on waiting list
Shows:
⚠ All 54 seats are full
Your waiting number: #X
5. QR Code System
5.1 Purpose
Every confirmed reservation generates a unique QR code used for check-in at the venue.
5.2 QR Code Content Options
Option A — Encoded JSON
{"type": "poker_reservation","id": "<UUID>","checksum": "<sha256>" } 
Option B — URL Format (recommended for MVP)
https://tournament.ge/checkin?id=<UUID>
QR does not contain personal data, only a reference ID.
5.3 When QR Code is Generated
Immediately after seat reservation
Shown on confirmation page
Sent via email or SMS (optional)
6. QR Check-In Process
6.1 Operator Procedure
Customer shows QR code
Staff scans QR using:
QR scanner device, or
Mobile phone camera with built-in scanner
System validates the reservation
If valid → marks user as checked_in
6.2 Check-In Backend Validations
System checks:
✔ Reservation exists
✔ Status is reserved
✔ Not already checked in
✖ Waiting list users cannot check in
✖ Cancelled reservations cannot check in
6.3 Check-In Responses
Success
{"status": "success","user": "John Doe","table": 3,"seat": 7,"checkin_time": "2025-11-20T14:32:00" } 
Error
{"status": "error","message": "Already checked in" } 
7. Admin Dashboard (Back Office)
Admin must be able to:
View all reservations
Search by name/phone
See tables and who is seated at each
View waiting list
See check-in statuses
Manually mark “checked-in” if needed
Export list to CSV
Reset entire tournament
8. System Data Model (Database Schema)
8.1 Table: reservations
Field	Type	Description
id	UUID (PK)	Unique reservation ID
first_name	string	Customer first name
last_name	string	Customer last name
phone	string	Customer phone number
email	string (nullable)	Optional
status	enum(reserved, waiting, checked_in, cancelled)	Reservation status
table_number	integer (nullable)	1–6
seat_number	integer (nullable)	1–9
waiting_position	integer (nullable)	Waiting list number
qr_code	string	Stored QR value
checkin_time	timestamp nullable	Check-in time
created_at	timestamp	Timestamp
9. API Endpoints
9.1 Create Reservation
POST /reserve
Request:
{
  "first_name": "Alex",
  "last_name": "Smith",
  "phone": "555123123",
  "email": "a@example.com"}
Response (seat available):
{"status": "reserved","table": 4,"seat": 7,"qr": "https://tournament.ge/checkin?id=UUID" } 
Response (waiting):
{"status": "waiting","waiting_number": 2 } 
9.2 QR Check-In
POST /checkin
Request:
{"qr": "<scanned QR data>" } 
Response:
{"status": "success","user": "Alex Smith","table": 4,"seat": 7 } 
9.3 Get Reservation Info
GET /reservation/{phone}
Response:
{"status": "reserved","table": 3,"seat": 6 } 
10. Random Seat Allocation Algorithm
Pseudocode:
tables = 6seats_per_table = 9while True:
    random_table = random(1,6)
    random_seat = random(1,9)
    if seat is empty:
        assign seat
        break 
11. Additional Features (Future Phases)
Phase 2 Enhancements
Automatic No-Show Release (unconfirmed players after X minutes)
Auto-assign waiting list users to new seats
Multi-language UI
SMS notifications
Phase 3 Enhancements
Live Tournament Screen (big display showing who is seated where)
Payment integration
Mobile App (iOS/Android)
Staff roles & permissions
12. Security Requirements
QR codes must not expose any personal data — only reservation ID
Every reservation must include server-side checksum validation
Rate-limiting: Max 5 requests per minute per IP
Admin panel must require authentication
 

 frontend make in Vue 3 with tailwind
 backend make in laravel 12 with filament admin