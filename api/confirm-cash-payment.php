<?php
// ============================================================
// API: Confirm Cash on Delivery Payment — E-Sheba
// Allows Customer to mark "I Paid in Cash" and Worker to confirm "Yes, I got paid"
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
$action    = trim($input['action'] ?? ''); // 'customer_paid' | 'worker_confirmed'

if (!$bookingId || !in_array($action, ['customer_paid', 'worker_confirmed'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters.']);
    exit;
}

$booking = DB::fetchOne(
    "SELECT b.*, w.user_id as worker_user_id, u.name as customer_name, wu.name as worker_name
     FROM bookings b
     JOIN workers w ON b.worker_id = w.id
     JOIN users u ON b.customer_id = u.id
     JOIN users wu ON w.user_id = wu.id
     WHERE b.id = ?",
    [$bookingId]
);

if (!$booking) {
    echo json_encode(['success' => false, 'error' => 'Booking not found.']);
    exit;
}

if ($booking['pay_method'] !== 'cash') {
    echo json_encode(['success' => false, 'error' => 'This booking is not set to Cash on Delivery.']);
    exit;
}

// ── CUSTOMER ACTION: "I Paid in Cash" ────────────────────────
if ($action === 'customer_paid') {
    if ($role !== 'customer' || (int)$booking['customer_id'] !== $myId) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized action.']);
        exit;
    }

    if ($booking['pay_status'] === 'paid') {
        echo json_encode(['success' => false, 'error' => 'This booking is already marked as paid.']);
        exit;
    }

    DB::query("UPDATE bookings SET pay_status = 'cash_pending' WHERE id = ?", [$bookingId]);

    // Notify worker
    $amountStr = $booking['total_amount'] ? '৳' . number_format($booking['total_amount']) : '';
    DB::query(
        "INSERT INTO notifications (user_id, type, title, body) VALUES (?, 'payment', ?, ?)",
        [
            $booking['worker_user_id'],
            "Cash Payment Awaiting Confirmation — Booking #$bookingId",
            "{$booking['customer_name']} marked $amountStr as paid in cash. Please confirm receipt in your dashboard."
        ]
    );

    echo json_encode([
        'success'    => true,
        'pay_status' => 'cash_pending',
        'message'    => 'You marked payment as handed over. Waiting for technician confirmation.'
    ]);
    exit;
}

// ── WORKER ACTION: "Confirm Cash Received" ────────────────────
if ($action === 'worker_confirmed') {
    if ($role !== 'worker' || (int)$booking['worker_user_id'] !== $myId) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized action.']);
        exit;
    }

    // Set pay_status to paid
    DB::query("UPDATE bookings SET pay_status = 'paid' WHERE id = ?", [$bookingId]);

    // Notify customer
    $amountStr = $booking['total_amount'] ? '৳' . number_format($booking['total_amount']) : '';
    DB::query(
        "INSERT INTO notifications (user_id, type, title, body) VALUES (?, 'payment', ?, ?)",
        [
            $booking['customer_id'],
            "Payment Confirmed — Booking #$bookingId",
            "{$booking['worker_name']} confirmed receiving your cash payment ($amountStr). Thank you for using E-Sheba!"
        ]
    );

    echo json_encode([
        'success'    => true,
        'pay_status' => 'paid',
        'message'    => 'Cash payment confirmed! ৳' . number_format($booking['total_amount']) . ' added to your verified earnings.'
    ]);
    exit;
}
