<?php
// ============================================================
// API: Submit Review & Rating — E-Sheba
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

Auth::requireRole('customer');
$customerId = Auth::id();

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$bookingId = (int)($input['booking_id'] ?? 0);
$workerId  = (int)($input['worker_id'] ?? 0);
$rating    = (int)($input['rating'] ?? 5);
$comment   = trim($input['comment'] ?? '');

if (!$bookingId || !$workerId) {
    echo json_encode(['success' => false, 'error' => 'Missing booking or provider ID.']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Rating must be between 1 and 5 stars.']);
    exit;
}

// Validate booking
$booking = DB::fetchOne("SELECT * FROM bookings WHERE id = ? AND customer_id = ?", [$bookingId, $customerId]);
if (!$booking) {
    echo json_encode(['success' => false, 'error' => 'Booking not found or does not belong to you.']);
    exit;
}

if ($booking['status'] !== 'completed') {
    echo json_encode(['success' => false, 'error' => 'You can only review completed services.']);
    exit;
}

// Check if already reviewed
$existing = DB::fetchOne("SELECT id FROM reviews WHERE booking_id = ?", [$bookingId]);
if ($existing) {
    echo json_encode(['success' => false, 'error' => 'You have already submitted a review for this booking.']);
    exit;
}

// Insert review
DB::query(
    "INSERT INTO reviews (booking_id, customer_id, worker_id, rating, comment) VALUES (?, ?, ?, ?, ?)",
    [$bookingId, $customerId, $workerId, $rating, $comment]
);

// Recalculate worker stats
$stats = DB::fetchOne(
    "SELECT COALESCE(AVG(rating), 0) as avg_r, COUNT(*) as total_r FROM reviews WHERE worker_id = ?",
    [$workerId]
);

DB::query(
    "UPDATE workers SET avg_rating = ?, total_reviews = ? WHERE id = ?",
    [(float)$stats['avg_r'], (int)$stats['total_r'], $workerId]
);

// Notify worker
$wUser = DB::fetchOne("SELECT user_id FROM workers WHERE id = ?", [$workerId]);
if ($wUser) {
    $custName = $_SESSION['user_name'] ?? 'Customer';
    DB::query(
        "INSERT INTO notifications (user_id, type, title, body) VALUES (?, 'review', 'New $rating★ Review!', ?)",
        [$wUser['user_id'], "$custName rated your service $rating stars" . ($comment ? ": \"$comment\"" : "")]
    );
}

echo json_encode(['success' => true]);
