# REST API Documentation

Base: `http://localhost/eventspace-modular/backend/index.php`. JSON request/response unless noted. Authentication: `Authorization: Bearer <JWT>`.

| Method | Resource | Role | Request | Success | Typical errors |
|---|---|---|---|---|---|
| POST | /auth/register | public | `{ "name":"Test", "email":"test@test.com", "password":"Password123!" }` | 201 `{ "id":6,"message":"Registered successfully" }` | 409, 422 |
| POST | /auth/login | public | `{ "email":"customer@demo.test", "password":"Password123!" }` | 200 `{ "token":"...", "user":{...} }` | 401, 422 |
| GET | /auth/me | logged in | - | 200 user object | 401 |
| GET | /users | admin | - | 200 list | 401, 403 |
| GET | /users/{id} | admin/self | - | 200 user object | 403, 404 |
| POST | /users | admin | name,email,password,role | 201 `{ "id":6 }` | 403, 409, 422 |
| PUT/PATCH | /users/{id} | admin/self | name/email/password; role admin only | 200 confirmation | 403, 422 |
| DELETE | /users/{id} | admin | - | 200 confirmation | 403, 404, 409 |
| GET | /venues | public | - | 200 venue array | 500 |
| GET | /venues/{id} | public | - | 200 venue | 404 |
| POST | /venues | admin/organiser | name,address,capacity | 201 `{ "id":6 }` | 403, 422 |
| PUT/PATCH | /venues/{id} | admin/organiser | name,address,capacity | 200 confirmation | 403, 422 |
| DELETE | /venues/{id} | admin | - | 200 confirmation | 403, 409 |
| GET | /events | public | `?page=1&limit=10&search=Tech&status=published` | 200 `{ "page":1,"limit":10,"data":[...] }` | 500 |
| GET | /events/{id} | public if published | - | 200 event with available | 403, 404 |
| POST | /events | admin/organiser | title,description,venue_id,event_date,ticket_price,total_tickets,status | 201 `{ "id":6 }` | 403, 422 |
| PUT/PATCH | /events/{id} | admin/owner organiser | all event fields | 200 confirmation | 403, 404, 422 |
| DELETE | /events/{id} | admin/owner organiser | - | 200 confirmation | 403, 404, 409 |
| GET | /bookings | logged in | - | 200 filtered bookings | 401 |
| GET | /bookings/{id} | admin/owner/organiser of event | - | 200 booking | 403, 404 |
| POST | /bookings | logged in | `{ "event_id":1,"quantity":2 }` | 201 `{ "id":7,"status":"confirmed","message":"Booking successful" }` | 401, 404, 409, 422 |
| PATCH/PUT | /bookings/{id} | admin/owner | `{ "status":"cancelled" }` | 200 confirmation | 403, 404, 409 |
| DELETE | /bookings/{id} | admin/owner | - (soft cancellation) | 200 confirmation | 403, 404, 409 |
| GET | /ticket-qr/{bookingId} | admin/owner/organiser of event | - | 200 image/png from external API | 401, 403, 404, 502 |

Business rules: customer accounts are self-registered; only admins can provision organiser/admin roles. Only published, future events can be booked. Sold tickets cannot exceed event ticket allocation. Event allocation cannot exceed venue capacity. Cancellation updates status, preserving booking history. Deletion of referenced events/venues/users is blocked by foreign key constraints.

## Middleware and API management
- Authentication: JWT authorization for protected endpoints
- Validation and central error handler
- Audit logging (`audit_logs` table) on bookings
- Pagination (`page`, `limit`), search (`search`), filter (`status`) for events
- Prepared statements to mitigate SQL injection

## Third-party API evidence to collect
1. In Postman, `GET /ticket-qr/{bookingId}` with a valid token and capture the PNG output.
2. Show the `curl_init` / `curl_exec` integration in `api/index.php`.
3. Capture the request against `https://api.qrserver.com/v1/create-qr-code/` and the returned QR in your running frontend.

## Recommended live test evidence
- Registration 201, login 200, expired/missing token 401, forbidden action 403, missing event 404, invalid event inputs 422, insufficient stock 409, duplicate email 409, external service failure 502 if simulated.
- Demonstrate SQL `EXPLAIN` before/after an index change and measure API latency for the report. Do **not** claim performance improvements without measurements.
