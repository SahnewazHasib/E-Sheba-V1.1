<?php
// ============================================================
// 403 Forbidden — E-Sheba
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
Auth::start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 Access Forbidden — <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body style="background:#f4f6fb">

<div class="container py-5 text-center">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="bg-white rounded-4 shadow-sm border p-5">
        <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle mb-3" style="width:84px;height:84px;font-size:2.8rem">
          <i class="fa fa-user-lock"></i>
        </div>
        <h2 class="fw-800 text-dark mb-1">Access Restricted</h2>
        <p class="text-muted" style="font-size:.92rem">
          You do not have permission to view this section. Please switch accounts or return to your permitted dashboard.
        </p>

        <div class="d-grid gap-2 mt-4">
          <?php if (Auth::isLoggedIn()): ?>
            <?php $dash = match(Auth::role()) { 'worker'=>'worker/dashboard.php', 'admin'=>'admin/dashboard.php', default=>'customer/dashboard.php' }; ?>
            <a href="<?= APP_URL ?>/<?= $dash ?>" class="btn btn-primary rounded-pill py-2 fw-700">Go to My Dashboard</a>
          <?php else: ?>
            <a href="<?= APP_URL ?>/login.php" class="btn btn-primary rounded-pill py-2 fw-700">Login</a>
          <?php endif; ?>
          <a href="<?= APP_URL ?>/index.php" class="btn btn-outline-secondary rounded-pill py-2">Return to Homepage</a>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
