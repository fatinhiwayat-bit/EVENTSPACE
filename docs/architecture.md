# Architecture and ERD

Browser (`frontend/index.html`) → JavaScript modules (`frontend/js`) → PHP entrypoint (`backend/index.php`) → individual resource controllers → helper services / PDO → MySQL.

```mermaid
erDiagram
 USERS ||--o{ EVENTS : organises
 USERS ||--o{ BOOKINGS : makes
 VENUES ||--o{ EVENTS : hosts
 EVENTS ||--o{ BOOKINGS : contains
 USERS ||--o{ AUDIT_LOGS : creates
 USERS { int id PK string name string email string role }
 VENUES { int id PK string name string address int capacity }
 EVENTS { int id PK int organiser_id FK int venue_id FK datetime event_date int total_tickets }
 BOOKINGS { int id PK int user_id FK int event_id FK int quantity string status }
 AUDIT_LOGS { int id PK int user_id FK string action }
```

Ticket sales are calculated using confirmed bookings. Venue capacity bounds tickets. Foreign keys restrict deleting resources with existing dependencies.
