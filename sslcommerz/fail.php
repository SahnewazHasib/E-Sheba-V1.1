<?php
// ============================================================
// SSLCommerz Payment Failed Callback — E-Sheba
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
Auth::start();

$tranId = $_POST['tran_id'] ?? $_GET['tran_id'] ?? 'N/A';
$error  = $_POST['error'] ?? 'Your payment transaction was declined or failed to process at the gateway.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Failed — <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background:#f4f6fb">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      
      <div class="bg-white rounded-4 shadow-sm border p-4 p-md-5 text-center">
        
        <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle mb-3" style="width:80px;height:80px;font-size:2.5rem">
          <i class="fa fa-times"></i>
        </div>

        <h3 class="fw-800 text-dark mb-1">Payment Failed</h3>
        <p class="text-muted" style="font-size:.9rem"><?= htmlspecialchars($error) ?></p>

        <div class="p-3 rounded-3 my-4 text-start" style="background:#fff5f5;border:1px solid #fed7d7;font-size:.88rem">
          <div class="d-flex justify-content-between">
            <span class="text-muted">Transaction ID:</span>
            <code class="text-danger fw-700"><?= htmlspecialchars($tranId) ?></code>
          </div>
        </div>

        <a href="<?= APP_URL ?>/index.php" class="btn btn-outline-danger w-100 py-3 rounded-pill fw-700 mb-2">
          Return to Home
        </a>

        <?php if (Auth::role() === 'worker'): ?>
          <a href="<?= APP_URL ?>/subscription.php" class="btn btn-primary w-100 py-2 rounded-pill fw-600" style="font-size:.88rem">
            Try Subscription Payment Again
          </a>
        <?php else: ?>
          <a href="<?= APP_URL ?>/customer/dashboard.php" class="btn btn-primary w-100 py-2 rounded-pill fw-600" style="font-size:.88rem">
            Back to Customer Dashboard
          </a>
        <?php endif; ?>

      </div>

    </div>
  </div>
</div>

</body>
</html>
