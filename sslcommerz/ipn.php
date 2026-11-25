<?php
// ============================================================
// SSLCommerz IPN (Instant Payment Notification) Webhook
// ============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

$tranId = $_POST['tran_id'] ?? '';
$status = $_POST['status'] ?? '';
$valId  = $_POST['val_id'] ?? '';
$amount = (float)($_POST['amount'] ?? 0);
$type   = $_POST['value_a'] ?? '';
$refId  = (int)($_POST['value_b'] ?? 0);

if (!$tranId || ($status !== 'VALID' && $status !== 'VALIDATED')) {
    exit("Invalid or unverified IPN payload");
}

// Check if already processed
if ($type === 'subscription') {
    $sub = DB::fetchOne("SELECT * FROM subscriptions WHERE transaction_id = ?", [$tranId]);
    if ($sub && $sub['status'] !== 'active') {
        $startsAt  = date('Y-m-d');
        $expiresAt = date('Y-m-d', strtotime('+30 days'));
        DB::query(
            "UPDATE subscriptions SET status = 'active', starts_at = ?, expires_at = ? WHERE id = ?",
            [$startsAt, $expiresAt, $sub['id']]
        );
        DB::query("UPDATE workers SET is_visible = 1 WHERE id = ?", [$sub['worker_id']]);
    }
} elseif ($type === 'booking') {
    $booking = DB::fetchOne("SELECT * FROM bookings WHERE pay_txn_id = ? OR id = ?", [$tranId, $refId]);
    if ($booking && $booking['pay_status'] !== 'paid') {
        DB::query(
            "UPDATE bookings SET pay_status = 'paid', pay_method = 'sslcommerz', pay_txn_id = ? WHERE id = ?",
            [$tranId, $booking['id']]
        );
    }
}

echo "IPN_SUCCESS";
