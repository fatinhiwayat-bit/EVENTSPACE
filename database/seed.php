<?php
require_once __DIR__ . '/../backend/config/database.php';
$db = db();
$users = [['Admin Demo', 'admin@demo.test', 'admin'], ['Organiser Demo', 'organiser@demo.test', 'organiser'], ['Customer One', 'customer@demo.test', 'customer'], ['Customer Two', 'customer2@demo.test', 'customer'], ['Customer Three', 'customer3@demo.test', 'customer']];
$stmt = $db->prepare('INSERT INTO users(name,email,password_hash,role) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name)');
foreach ($users as [$name, $email, $role]) $stmt->execute([$name, $email, password_hash('Password123!', PASSWORD_DEFAULT), $role]);
$venues = [['Grand Hall', 'Kuala Lumpur', 450], ['City Auditorium', 'Petaling Jaya', 300], ['Innovation Centre', 'Cyberjaya', 150], ['Community Theatre', 'Shah Alam', 200], ['Expo Hall', 'Putrajaya', 600]];
if ((int)$db->query('SELECT COUNT(*) FROM venues')->fetchColumn() === 0) {
    $s = $db->prepare('INSERT INTO venues(name,address,capacity) VALUES(?,?,?)');
    foreach ($venues as $v) $s->execute($v);
}
$oid = $db->query("SELECT id FROM users WHERE email='organiser@demo.test'")->fetchColumn();
if ((int)$db->query('SELECT COUNT(*) FROM events')->fetchColumn() === 0) {
    $events = [['Tech Discovery Summit', 1, 'Technology talks', 30, 40.00], ['Creative Arts Expo', 2, 'Local arts exhibition', 36, 20.00], ['Startup Pitch Night', 3, 'Pitch competition', 42, 0.00], ['Community Music Fest', 4, 'Music showcase', 48, 35.00], ['Innovation Showcase', 5, 'Student innovation', 54, 15.00]];
    $s = $db->prepare("INSERT INTO events(title,venue_id,organiser_id,description,event_date,total_tickets,ticket_price,status) VALUES(?,?,?, ?,DATE_ADD(NOW(), INTERVAL ? DAY),?,?,'published')");
    foreach ($events as [$title, $venue, $desc, $days, $price]) $s->execute([$title, $venue, $oid, $desc, $days, 80, $price]);
}
if ((int)$db->query('SELECT COUNT(*) FROM bookings')->fetchColumn() === 0) {
    $ids = $db->query("SELECT id FROM users WHERE role='customer' ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
    $s = $db->prepare('INSERT INTO bookings(user_id,event_id,quantity,total_amount,status) SELECT ?,id,1,ticket_price,? FROM events WHERE id=?');
    for ($i = 0; $i < 5; $i++) $s->execute([$ids[$i % count($ids)], $i === 4 ? 'cancelled' : 'confirmed', $i + 1]);
}
if ((int)$db->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn() === 0) {
    $s = $db->prepare('INSERT INTO audit_logs(user_id,action,detail) VALUES(?,?,?)');
    for ($i = 0; $i < 5; $i++) $s->execute([$oid, 'seed', 'Sample log ' . ($i + 1)]);
}
echo "Seed completed. Login: admin@demo.test / organiser@demo.test / customer@demo.test with Password123!\n";
