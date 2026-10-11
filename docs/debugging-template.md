# Debugging and performance evidence

Complete this with **your actual tests** before submission; do not claim unperformed measurements.

| Problem reproduced | Root cause | Code fix | Before/after measured time or result | Screenshot / commit |
|---|---|---|---|---|
| | | | | |

Recommended test cases: failed login (401), customer accessing /users (403), invalid capacity (422), duplicate email (409), booking beyond remaining tickets (409), unavailable QR provider (502), and deletion of referenced venues (409). Examine `EXPLAIN` for events date and bookings event/status queries to verify indexes. Record measurements with Postman.
