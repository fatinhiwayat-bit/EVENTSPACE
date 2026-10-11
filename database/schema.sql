CREATE DATABASE IF NOT EXISTS event_ticketing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE event_ticketing;
CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL, role ENUM('admin','organiser','customer') NOT NULL DEFAULT 'customer',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS venues (
 id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, address VARCHAR(255) NOT NULL,
 capacity INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CHECK (capacity > 0)
);
CREATE TABLE IF NOT EXISTS events (
 id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(160) NOT NULL, description TEXT,
 venue_id INT NOT NULL, organiser_id INT NOT NULL, event_date DATETIME NOT NULL,
 ticket_price DECIMAL(10,2) NOT NULL DEFAULT 0, total_tickets INT NOT NULL,
 status ENUM('draft','published','cancelled') NOT NULL DEFAULT 'published',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(venue_id) REFERENCES venues(id) ON DELETE RESTRICT,
 FOREIGN KEY(organiser_id) REFERENCES users(id) ON DELETE RESTRICT,
 CHECK(ticket_price >= 0), CHECK(total_tickets > 0)
);
CREATE TABLE IF NOT EXISTS bookings (
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, event_id INT NOT NULL,
 quantity INT NOT NULL, total_amount DECIMAL(10,2) NOT NULL,
 status ENUM('confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
 booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE RESTRICT,
 FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE RESTRICT,
 CHECK(quantity > 0)
);
CREATE TABLE IF NOT EXISTS audit_logs (
 id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, action VARCHAR(100) NOT NULL, detail VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
);
CREATE INDEX idx_events_date ON events(event_date);
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_bookings_event_status ON bookings(event_id,status);
CREATE INDEX idx_bookings_user ON bookings(user_id);
