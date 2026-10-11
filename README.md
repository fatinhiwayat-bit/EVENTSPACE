# EventSpace — Modular RESTful Event Ticketing & Venue Booking

Built against SWC3633/SWC4443 August 2026 project description and marking rubric. This is a **development starter system**, not an automatically completed group submission: demonstrate it in XAMPP; prepare individual independent frontend work, evidence and videos yourself.

## Setup — Windows XAMPP
1. Extract folder to `C:\xampp\htdocs\eventspace`.
2. Start Apache and MySQL.
3. Open phpMyAdmin and import `database/schema.sql` (creates `event_ticketing`).
4. Set `JWT_SECRET` to a long unpredictable environment variable before any non-local deployment. Locally the file `backend/config/database.php` defaults to root user and empty password.
5. Run `C:\xampp\php\php.exe database\seed.php` from the project directory.
6. Visit `http://localhost/eventspace/frontend/`.
7. Test API: `http://localhost/eventspace/backend/index.php/events`.

Demo credentials (LOCAL TESTING ONLY): `admin@demo.test`, `organiser@demo.test`, `customer@demo.test`; password `Password123!`.

## Folders
- `database/schema.sql` schema with users/venues/events/bookings/audit logs and indexes
- `database/seed.php` demo fixtures (>=5 per table)
- `backend/index.php` HTTP router only
- `backend/config` MySQL and JWT configuration
- `backend/core` response, input validation and authentication helpers
- `backend/controllers` one PHP file per resource (Auth, User, Venue, Event, Booking, Ticket)
- `backend/services` event availability and audit log functions
- `frontend/index.html` HTML markup only
- `frontend/css/style.css` visual styling
- `frontend/js` separate modules by feature: API, auth, events, venues, bookings, users, app navigation
- `docs/API.md` API documentation
- `docs/postman_collection.json` import into Postman
- `docs/architecture.md` Mermaid ERD and flow
- `docs/debugging-template.md` evidence template
- `docs/contribution-template.md` individual portfolio reminder

## Important rubric requirements still requiring your group
Individual students must implement and demonstrate distinct *own* frontends. Complete actual Postman success+failure screenshots and export, a group demo with individually identified sections, a 200–300 word reflection per person, a PDF report, and verifiable GitHub commits. Run and verify actual SQL, API workflows and third-party QR requests on XAMPP. External `api.qrserver.com` requires internet; it may be unavailable and responds with HTTP 502.

## Security notes
JWT signed HS256, role checks, bound PDO parameters, request validation, HTTP responses, basic audit logs, login rate limiting, event search/pagination and foreign-key constraints are included. Development defaults are **not production hardened**: use HTTPS, trusted secrets, tighter rate limiting, robust testing and audited authorization before real deployments. The external QR image contains booking metadata and must not be used as a secure anti-fraud ticket without server-side verification.

## Fix for “Bearer token required” after logging in (XAMPP)
The frontend sends its JWT in the `Authorization` request header. Some Apache/PHP configurations do not expose this header automatically to PHP. `backend/.htaccess` now explicitly forwards it, and `backend/core/Auth.php` reads the supported server/header variants.

1. Copy the full updated `backend/.htaccess` and `backend/core/Auth.php` to your existing installation.
2. Ensure Apache's `mod_rewrite` and `mod_setenvif` modules and `.htaccess` overrides are enabled in XAMPP.
3. Reload the frontend with Ctrl+F5, sign in again. A stale token from the previous code can be cleared in DevTools Console with `localStorage.removeItem('event_token'); location.reload();`.
4. In DevTools > Network, check `POST /auth/login` (200 + token) followed by `GET /auth/me` (200 + user JSON). If the second request still returns 401, inspect Apache configuration and whether the Authorization request header arrives, without sharing the actual token.

The local frontend uses `../backend/index.php` relative to `/eventspace/frontend/`, so renaming the top-level folder does not require changing this setting.
