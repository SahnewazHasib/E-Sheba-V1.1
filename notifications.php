<?php
// ============================================================
// Notifications Center — E-Sheba
// ============================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';
Auth::requireLogin();

$myId = Auth::id();

// Mark unread as read
DB::query("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0", [$myId]);

// Fetch notifications
$notifications = DB::fetchAll(
    "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
    [$myId]
);

$dashLink = match(Auth::role()) {
    'worker' => 'worker/dashboard.php',
    'admin'  => 'admin/dashboard.php',
    default  => 'customer/dashboard.php'
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications — <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body style="background:#f4f6fb">

<nav class="navbar-esheba">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="brand text-decoration-none">🔧 <?= APP_NAME ?></a>
    <div class="d-flex align-items-center gap-2">
      <a href="<?= $dashLink ?>" class="btn btn-outline-primary rounded-pill px-3" style="font-size:.85rem">
        <i class="fa fa-arrow-left me-1"></i>My Dashboard
      </a>
      <a href="api/logout.php" class="btn btn-outline-danger rounded-pill px-3" style="font-size:.85rem">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4" style="max-width:680px">
  
  <div class="bg-white rounded-4 border p-4 p-md-5">
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
      <h4 class="fw-800 mb-0"><i class="fa fa-bell me-2 text-primary"></i>Notifications</h4>
      <span class="badge bg-light text-muted border"><?= count($notifications) ?> total</span>
    </div>

    <?php if (empty($notifications)): ?>
      <div class="text-center py-5 text-muted">
        <i class="fa fa-bell-slash fa-3x mb-3" style="opacity:.3"></i>
        <h6 class="fw-700">No notifications yet</h6>
        <p class="mb-0" style="font-size:.88rem">You'll see alerts here when customers book jobs or workers respond.</p>
      </div>
    <?php else: ?>
      <div class="d-flex flex-column gap-2">
        <?php foreach ($notifications as $n): 
          $icon = match($n['type']) {
            'booking', 'booking_update' => ['fa-calendar-check', '#1a73e8', '#e8f0fe'],
            'call'                      => ['fa-phone-alt', '#34a853', '#e6f4ea'],
            'message'                   => ['fa-comment-dots', '#1a73e8', '#e8f0fe'],
            'subscription'              => ['fa-crown', '#fbbc05', '#fef7e0'],
            'approval'                  => ['fa-check-circle', '#34a853', '#e6f4ea'],
            'review'                    => ['fa-star', '#fbbc05', '#fef7e0'],
            default                     => ['fa-info-circle', '#5f6368', '#f1f3f4']
          };
        ?>
          <div class="p-3 rounded-3 d-flex align-items-start gap-3" style="background:#f8f9fc;border:1px solid #edf0f5">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;flex-shrink:0;background:<?= $icon[2] ?>;color:<?= $icon[1] ?>">
              <i class="fa <?= $icon[0] ?>"></i>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="fw-700 text-dark" style="font-size:.9rem"><?= htmlspecialchars($n['title']) ?></span>
                <span class="text-muted" style="font-size:.75rem"><?= date('d M, h:i A', strtotime($n['created_at'])) ?></span>
              </div>
              <p class="mb-0 text-muted" style="font-size:.85rem"><?= htmlspecialchars($n['body']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>

</div>

</body>
</html>
