<?php
// ============================================================
// SSLCommerz Payment Success Callback — E-Sheba (Person 4)
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
Auth::start();

$tranId  = $_POST['tran_id'] ?? $_GET['tran_id'] ?? '';
$valId   = $_POST['val_id'] ?? $_GET['val_id'] ?? '';
$amount  = (float)($_POST['amount'] ?? $_GET['amount'] ?? 0);
$type    = $_POST['value_a'] ?? $_GET['value_a'] ?? '';
$refId   = (int)($_POST['value_b'] ?? $_GET['value_b'] ?? 0);
$cardType= $_POST['card_type'] ?? 'SSLCommerz Gateway';

if (!$tranId) {
    die("Invalid transaction access.");
}

// Infer type from tranId if not passed
if (!$type) {
    if (str_starts_with($tranId, 'SUB_')) {
        $type = 'subscription';
    } elseif (str_starts_with($tranId, 'BOOK_')) {
        $type = 'booking';
    }
}

$success = false;
$msg = '';
$redirectUrl = APP_URL . '/index.php';

if ($type === 'subscription') {
    // Locate subscription row or create/update
    $sub = DB::fetchOne("SELECT * FROM subscriptions WHERE transaction_id = ?", [$tranId]);
    if ($sub) {
        $workerId = $sub['worker_id'];
    } elseif ($refId) {
        $workerId = $refId;
    } else {
        $w = DB::fetchOne("SELECT id FROM workers WHERE user_id = ?", [Auth::id()]);
        $workerId = $w ? $w['id'] : 0;
    }

    if ($workerId) {
        // Activate subscription for 30 days
        $startsAt  = date('Y-m-d');
        $expiresAt = date('Y-m-d', strtotime('+30 days'));

        if ($sub) {
            DB::query(
                "UPDATE subscriptions SET status = 'active', starts_at = ?, expires_at = ?, amount = ? WHERE id = ?",
                [$startsAt, $expiresAt, $amount ?: SUBSCRIPTION_PRICE, $sub['id']]
            );
        } else {
            DB::query(
                "INSERT INTO subscriptions (worker_id, amount, currency, status, payment_method, transaction_id, starts_at, expires_at)
                 VALUES (?, ?, 'BDT', 'active', 'sslcommerz', ?, ?, ?)",
                [$workerId, $amount ?: SUBSCRIPTION_PRICE, $tranId, $startsAt, $expiresAt]
            );
        }

        // Set worker visible in search results
        DB::query("UPDATE workers SET is_visible = 1 WHERE id = ?", [$workerId]);

        // Send notification
        $workerUser = DB::fetchOne("SELECT user_id FROM workers WHERE id = ?", [$workerId]);
        if ($workerUser) {
            DB::query(
                "INSERT INTO notifications (user_id, type, title, body) VALUES (?, 'subscription', 'Subscription Activated! 👑', 'Your 30-day profile visibility is active until " . date('d M Y', strtotime($expiresAt)) . "')",
                [$workerUser['user_id']]
            );
        }

        $success = true;
        $msg = "Your ৳" . ($amount ?: SUBSCRIPTION_PRICE) . " subscription has been activated! Your profile is now live in customer search results for 30 days.";
        $redirectUrl = APP_URL . "/worker/dashboard.php";
    }

} elseif ($type === 'booking') {
    $booking = null;
    if ($refId) {
        $booking = DB::fetchOne("SELECT * FROM bookings WHERE id = ?", [$refId]);
    } else {
        $booking = DB::fetchOne("SELECT * FROM bookings WHERE pay_txn_id = ?", [$tranId]);
    }

    if ($booking) {
        DB::query(
            "UPDATE bookings SET pay_status = 'paid', pay_method = 'sslcommerz', pay_txn_id = ? WHERE id = ?",
            [$tranId, $booking['id']]
        );

        // Notify Worker of payment
        $worker = DB::fetchOne("SELECT user_id FROM workers WHERE id = ?", [$booking['worker_id']]);
        if ($worker) {
            DB::query(
                "INSERT INTO notifications (user_id, type, title, body) VALUES (?, 'payment', 'Payment Received! ৳', 'Customer completed payment for booking #" . $booking['id'] . " via SSLCommerz.')",
                [$worker['user_id']]
            );
        }

        $success = true;
        $msg = "Payment of ৳" . number_format($amount, 2) . " received successfully for booking #" . $booking['id'] . ".";
        $redirectUrl = APP_URL . "/customer/dashboard.php";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Successful — <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background:#f4f6fb">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      
      <div class="bg-white rounded-4 shadow-sm border p-4 p-md-5 text-center">
        
        <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mb-3" style="width:80px;height:80px;font-size:2.5rem">
          <i class="fa fa-check"></i>
        </div>

        <h3 class="fw-800 text-dark mb-1">Payment Successful!</h3>
        <p class="text-muted" style="font-size:.9rem"><?= htmlspecialchars($msg) ?></p>

        <!-- Receipt Box -->
        <div class="p-3 rounded-3 my-4 text-start" style="background:#f8f9fa;border:1px dashed #d0d7de;font-size:.88rem">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Transaction ID:</span>
            <code class="fw-700"><?= htmlspecialchars($tranId) ?></code>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Amount Paid:</span>
            <strong class="text-success">৳<?= number_format($amount, 2) ?> BDT</strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Payment Method:</span>
            <span><?= htmlspecialchars($cardType) ?></span>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted">Date & Time:</span>
            <span><?= date('d M Y, h:i A') ?></span>
          </div>
        </div>

        <div class="d-grid gap-2">
          <a href="<?= $redirectUrl ?>" class="btn btn-primary py-3 rounded-pill fw-700 shadow-sm">
            Continue to Dashboard →
          </a>
          <?php if (!empty($booking['id'])): ?>
            <a href="../invoice.php?booking_id=<?= $booking['id'] ?>" class="btn btn-outline-secondary py-2 rounded-pill fw-600" style="font-size:.88rem">
              <i class="fa fa-print me-1"></i>Print Booking Invoice
            </a>
          <?php elseif (!empty($sub['id'])): ?>
            <a href="../invoice.php?sub_id=<?= $sub['id'] ?>" class="btn btn-outline-secondary py-2 rounded-pill fw-600" style="font-size:.88rem">
              <i class="fa fa-print me-1"></i>Print Subscription Invoice
            </a>
          <?php endif; ?>
        </div>

        <div class="mt-3">
          <a href="<?= APP_URL ?>/index.php" class="text-muted text-decoration-none" style="font-size:.82rem">Return to Homepage</a>
        </div>

      </div>

    </div>
  </div>
</div>

</body>
</html>
