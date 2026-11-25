<?php
// ============================================================
// SSLCommerz Payment Cancelled Callback — E-Sheba
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
Auth::start();

$tranId = $_POST['tran_id'] ?? $_GET['tran_id'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Cancelled — <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background:#f4f6fb">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      
      <div class="bg-white rounded-4 shadow-sm border p-4 p-md-5 text-center">
        
        <div class="d-inline-flex align-items-center justify-content-center bg-secondary-subtle text-secondary rounded-circle mb-3" style="width:80px;height:80px;font-size:2.5rem">
          <i class="fa fa-ban"></i>
        </div>

        <h3 class="fw-800 text-dark mb-1">Payment Cancelled</h3>
        <p class="text-muted" style="font-size:.9rem">You cancelled the payment request. No money was deducted from your account.</p>

        <div class="p-3 rounded-3 my-4 text-start" style="background:#f8f9fa;border:1px solid #e9ecef;font-size:.88rem">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Transaction ID:</span>
            <code><?= htmlspecialchars($tranId) ?></code>
          </div>
        </div>

        <?php if (Auth::role() === 'worker'): ?>
          <a href="<?= APP_URL ?>/subscription.php" class="btn btn-primary w-100 py-3 rounded-pill fw-700">
            Back to Subscription
          </a>
        <?php else: ?>
          <a href="<?= APP_URL ?>/customer/dashboard.php" class="btn btn-primary w-100 py-3 rounded-pill fw-700">
            Back to Customer Dashboard
          </a>
        <?php endif; ?>

      </div>

    </div>
  </div>
</div>

</body>
</html>
