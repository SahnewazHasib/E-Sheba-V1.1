<?php
// ============================================================
// API: Update Booking Status — E-Sheba
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

Auth::requireLogin();
$myId = Auth::id();
$role = Auth::role();

$rawInput = file_get_contents('php://input');
$json = !empty($rawInput) ? json_decode($rawInput, true) : null;
$input = (!empty($json) && is_array($json)) ? $json : array_merge($_GET, $_POST);
$bookingId = (int)($input['booking_id'] ?? $input['id'] ?? 0);
$newStatus = trim($input['status'] ?? '');

$allowed = ['pending', 'accepted', 'in_progress', 'completed', 'cancelled'];
if (!$bookingId || !in_array($newStatus, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
    exit;
}

$booking = DB::fetchOne("SELECT b.*, w.user_id as worker_user_id FROM bookings b JOIN workers w ON b.worker_id=w.id WHERE b.id = ?", [$bookingId]);
if (!$booking) {
    echo json_encode(['success' => false, 'error' => 'Booking not found.']);
    exit;
}

// Permission checking
if ($role === 'worker') {
    if ((int)$booking['worker_user_id'] !== $myId) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized: not your booking.']);
        exit;
    }
} elseif ($role === 'customer') {
    if ((int)$booking['customer_id'] !== $myId) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized.']);
        exit;
    }
    // Customer can only cancel pending jobs
    if ($newStatus !== 'cancelled' || $booking['status'] !== 'pending') {
        echo json_encode(['success' => false, 'error' => 'You can only cancel pending bookings.']);
        exit;
    }
}

// Update booking status
DB::query("UPDATE bookings SET status = ? WHERE id = ?", [$newStatus, $bookingId]);

// Create notification for the other party
$notifyUserId = ($role === 'worker') ? $booking['customer_id'] : $booking['worker_user_id'];
$statusTitle  = 'Booking #' . $bookingId . ' ' . ucfirst(str_replace('_', ' ', $newStatus));
$statusMsg    = 'Status updated to ' . strtoupper(str_replace('_', ' ', $newStatus)) . ' by ' . $_SESSION['user_name'];

DB::query(
    "INSERT INTO notifications (user_id, type, title, body) VALUES (?, 'booking_update', ?, ?)",
    [$notifyUserId, $statusTitle, $statusMsg]
);

echo json_encode(['success' => true]);
