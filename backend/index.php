<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/core/Http.php';
require __DIR__ . '/core/Auth.php';
require __DIR__ . '/services/EventService.php';
require __DIR__ . '/services/AuditService.php';
require __DIR__ . '/middleware/RateLimit.php';
foreach (['Auth', 'User', 'Venue', 'Event', 'Booking', 'Ticket'] as $name) {
    require __DIR__ . '/controllers/' . $name . 'Controller.php';
}
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE,OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
set_exception_handler(function (Throwable $e) {
    error_log((string)$e);
    reply(500, ['error' => 'Internal server error']);
});
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (str_starts_with($path, $base . '/')) $path = substr($path, strlen($base));
$path = '/' . trim($path, '/');
if ($path === '/index.php') $path = '/';
elseif (str_starts_with($path, '/index.php/')) $path = substr($path, 10);
$part = array_values(array_filter(explode('/', $path), 'strlen'));
$resource = $part[0] ?? '';
$id = isset($part[1]) && ctype_digit($part[1]) ? (int)$part[1] : null;
$method = $_SERVER['REQUEST_METHOD'];
try {
    if ($resource === '' && $method === 'GET') reply(200, ['service' => 'EventSpace REST API', 'status' => 'ok', 'resources' => ['/users', '/events', '/venues', '/bookings']]);
    switch ($resource) {
        case 'auth':
            AuthController($resource, $id, $part, $method);
            break;
        case 'users':
            UserController($resource, $id, $part, $method);
            break;
        case 'venues':
            VenueController($resource, $id, $part, $method);
            break;
        case 'events':
            EventController($resource, $id, $part, $method);
            break;
        case 'bookings':
            BookingController($resource, $id, $part, $method);
            break;
        case 'ticket-qr':
            TicketController($resource, $id, $part, $method);
            break;
    }
    reply(404, ['error' => 'Endpoint not found']);
} catch (PDOException $e) {
    if (db()->inTransaction()) db()->rollBack();
    error_log((string)$e);
    if ($e->getCode() === '23000') reply(409, ['error' => 'Duplicate or referenced record violates database integrity']);
    reply(500, ['error' => 'Database error']);
}
