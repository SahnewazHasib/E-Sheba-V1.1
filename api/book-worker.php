<?php
// ============================================================
// API: Book Worker -- E-Sheba
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

// Always return JSON -- never redirect
Auth::start();
if (!Auth::isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Please login to book a service.', 'redirect' => 'login.php']);
    exit;
}

// Only customers can book
if (Auth::role() !== 'customer') {
    echo json_encode(['success' => false, 'error' => 'Only customers can book a service provider. Please log in with a customer account.']);
    exit;
}

$customerId = Auth::id();

$rawInput = file_get_contents('php://input');
$json = !empty($rawInput) ? json_decode($rawInput, true) : null;
$input = (!empty($json) && is_array($json)) ? $json : array_merge($_GET, $_POST);

$workerId    = (int)($input['worker_id'] ?? 0);
$categoryId  = (int)($input['category_id'] ?? 0);
$scheduledAt = trim($input['scheduled_at'] ?? '');
$address     = trim($input['address'] ?? '');
$description = trim($input['description'] ?? '');
$payMethod   = in_array($input['pay_method'] ?? '', ['sslcommerz', 'card', 'cash']) ? $input['pay_method'] : 'cash';

if (!$workerId || !$categoryId || !$address) {
    echo json_encode(['success' => false, 'error' => 'Please provide all required booking details (service type, address).']);
    exit;
}

$worker = DB::fetchOne("SELECT w.*, u.name as worker_name, u.id as user_account_id FROM workers w JOIN users u ON w.user_id=u.id WHERE w.id = ?", [$workerId]);
if (!$worker) {
    echo json_encode(['success' => false, 'error' => 'Selected service provider not found.']);
    exit;
}

$category = DB::fetchOne("SELECT name FROM categories WHERE id = ?", [$categoryId]);
if (!$category) {
    echo json_encode(['success' => false, 'error' => 'Selected category not found.']);
    exit;
}

$amount = $worker['price_per_hour'] > 0 ? (float)$worker['price_per_hour'] : 500.00;
$scheduledDateTime = !empty($scheduledAt) ? date('Y-m-d H:i:s', strtotime($scheduledAt)) : null;

// Insert booking
DB::query(
    "INSERT INTO bookings (customer_id, worker_id, category_id, description, scheduled_at, status, total_amount, pay_method, pay_status, address)
     VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, 'unpaid', ?)",
    [$customerId, $workerId, $categoryId, $description, $scheduledDateTime, $amount, $payMethod, $address]
);

$bookingId = (int)DB::lastInsertId();

// Create in-app notification for the worker
$customerName = $_SESSION['user_name'] ?? 'Customer';
DB::query(
    "INSERT INTO notifications (user_id, type, title, body)
     VALUES (?, 'booking', ?, ?)",
    [
        $worker['user_account_id'],
        "New Service Booking #$bookingId",
        "$customerName booked you for " . $category['name'] . ($scheduledDateTime ? " on " . date('d M, h:i A', strtotime($scheduledDateTime)) : "")
    ]
);

// Trigger Pusher notification if configured
try {
    $key     = PUSHER_KEY;
    $secret  = PUSHER_SECRET;
    $appId   = PUSHER_APP_ID;
    $cluster = PUSHER_CLUSTER;
    if ($key !== 'your_pusher_key') {
        $channelName = 'presence-esheba';
        $body = json_encode([
            'name'     => 'new-booking',
            'channels' => [$channelName],
            'data'     => json_encode(['booking_id' => $bookingId, 'worker_id' => $workerId])
        ]);
        $path = "/apps/{$appId}/events";
        $ts   = time();
        $md5  = md5($body);
        $sig  = hash_hmac('sha256', "POST\n{$path}\nauth_key={$key}&auth_timestamp={$ts}&auth_version=1.0&body_md5={$md5}", $secret);
        $url  = "https://api-{$cluster}.pusher.com{$path}?auth_key={$key}&auth_timestamp={$ts}&auth_version=1.0&body_md5={$md5}&auth_signature={$sig}";
        @file_get_contents($url, false, stream_context_create(['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => $body]]));
    }
} catch (Exception $e) {}

echo json_encode(['success' => true, 'booking_id' => $bookingId]);
