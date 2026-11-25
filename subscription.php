<?php
// ============================================================
// Service Provider Subscription Page — E-Sheba (Person 4)
// ============================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';
Auth::requireRole('worker');

$myId   = Auth::id();
$myName = $_SESSION['user_name'] ?? 'Provider';

// Fetch worker profile
$worker = DB::fetchOne("SELECT * FROM workers WHERE user_id = ?", [$myId]);
if (!$worker) {
    header("Location: " . APP_URL . "/register.php?role=worker");
    exit;
}
$workerId = $worker['id'];

// Fetch current active subscription
$activeSub = DB::fetchOne(
    "SELECT * FROM subscriptions 
     WHERE worker_id = ? AND status = 'active' AND expires_at >= CURDATE()
     ORDER BY expires_at DESC LIMIT 1",
    [$workerId]
);

// Fetch all subscription history
$subHistory = DB::fetchAll(
    "SELECT * FROM subscriptions 
     WHERE worker_id = ? 
     ORDER BY created_at DESC",
    [$workerId]
);

$daysLeft = 0;
if ($activeSub) {
    $now = new DateTime();
    $exp = new DateTime($activeSub['expires_at']);
    $diff = $now->diff($exp);
    $daysLeft = max(0, (int)$diff->format("%r%a"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Subscription — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= APP_URL ?>/css/style.css">
  <style>
    .sub-card {
      border-radius: 20px;
      overflow: hidden;
      border: 2px solid transparent;
      transition: all .25s ease;
    }
    .sub-card.featured {
      border-color: #1a73e8;
      box-shadow: 0 12px 32px rgba(26,115,232,.12);
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-esheba">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="<?= APP_URL ?>/index.php" class="brand text-decoration-none">🔧 <?= APP_NAME ?></a>
    <div class="d-flex align-items-center gap-2">
      <a href="<?= APP_URL ?>/worker/dashboard.php" class="btn btn-outline-secondary rounded-pill px-3" style="font-size:.85rem">
        <i class="fa fa-arrow-left me-1"></i>Back to Dashboard
      </a>
      <a href="<?= APP_URL ?>/api/logout.php" class="btn btn-outline-danger rounded-pill px-3" style="font-size:.85rem">
        Logout
      </a>
    </div>
  </div>
</nav>

<div class="container py-4">

  <!-- Status Banner -->
  <?php if ($activeSub): ?>
    <div class="p-4 mb-4 rounded-4" style="background:linear-gradient(135deg, #1a73e8, #0d47a1);color:#fff">
      <div class="row align-items-center">
        <div class="col-md-8">
          <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white text-primary fw-700 mb-2" style="font-size:.8rem">
            <i class="fa fa-check-circle"></i> SUBSCRIPTION ACTIVE
          </div>
          <h3 class="fw-800 mb-1">Your Profile is Public & Live!</h3>
          <p class="mb-0 opacity-85" style="font-size:.95rem">
            Expires on <strong><?= date('d F Y', strtotime($activeSub['expires_at'])) ?></strong> (<?= $daysLeft ?> days remaining).
          </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <a href="<?= APP_URL ?>/sslcommerz/init.php?type=subscription" class="btn btn-warning rounded-pill px-4 fw-700 shadow-sm">
            <i class="fa fa-sync me-1"></i> Renew for ৳<?= SUBSCRIPTION_PRICE ?>
          </a>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="p-4 mb-4 rounded-4" style="background:linear-gradient(135deg, #ea4335, #c62828);color:#fff">
      <div class="row align-items-center">
        <div class="col-md-8">
          <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white text-danger fw-700 mb-2" style="font-size:.8rem">
            <i class="fa fa-exclamation-triangle"></i> PROFILE HIDDEN
          </div>
          <h3 class="fw-800 mb-1">Subscription Required</h3>
          <p class="mb-0 opacity-85" style="font-size:.95rem">
            Your provider profile is currently hidden from customer search results. Activate your ৳<?= SUBSCRIPTION_PRICE ?>/month subscription to start receiving job requests.
          </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <a href="<?= APP_URL ?>/sslcommerz/init.php?type=subscription" class="btn btn-light text-danger rounded-pill px-4 fw-800 shadow-sm">
            <i class="fa fa-crown me-1 text-warning"></i> Activate Now (৳<?= SUBSCRIPTION_PRICE ?>)
          </a>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="row g-4 mb-4">
    
    <!-- ══ Left Column: Pricing Plan Details ══ -->
    <div class="col-lg-6">
      <div class="bg-white rounded-4 border p-4 p-md-5 sub-card featured h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <span class="badge bg-primary-subtle text-primary fw-700 px-3 py-1 rounded-pill" style="font-size:.85rem">
            MONTHLY PROVIDER PLAN
          </span>
          <i class="fa fa-crown fa-2x" style="color:#fbbc05"></i>
        </div>

        <h3 class="fw-800 mb-1">Standard Pro Visibility</h3>
        <p class="text-muted" style="font-size:.9rem">Complete marketplace access for certified Bangladeshi technicians & workers.</p>

        <div class="d-flex align-items-baseline gap-2 my-4">
          <span class="fw-800 text-dark" style="font-size:3rem">৳<?= SUBSCRIPTION_PRICE ?></span>
          <span class="text-muted" style="font-size:1.1rem">/ 30 Days</span>
        </div>

        <h6 class="fw-700 mb-3 text-dark">Everything included:</h6>
        <ul class="list-unstyled d-flex flex-column gap-2 mb-4" style="font-size:.95rem">
          <li class="d-flex align-items-center gap-2 text-dark">
            <i class="fa fa-check-circle text-success fs-5"></i>
            <span>Active listing in customer category search results</span>
          </li>
          <li class="d-flex align-items-center gap-2 text-dark">
            <i class="fa fa-check-circle text-success fs-5"></i>
            <span>Receive direct customer job bookings & leads</span>
          </li>
          <li class="d-flex align-items-center gap-2 text-dark">
            <i class="fa fa-check-circle text-success fs-5"></i>
            <span>Unlimited real-time in-app messaging via Pusher</span>
          </li>
          <li class="d-flex align-items-center gap-2 text-dark">
            <i class="fa fa-check-circle text-success fs-5"></i>
            <span>In-app masked voice calling via Agora RTC</span>
          </li>
          <li class="d-flex align-items-center gap-2 text-dark">
            <i class="fa fa-check-circle text-success fs-5"></i>
            <span>Showcase verified star ratings & customer reviews</span>
          </li>
          <li class="d-flex align-items-center gap-2 text-dark">
            <i class="fa fa-check-circle text-success fs-5"></i>
            <span>Real-time location sharing on coverage map</span>
          </li>
        </ul>

        <a href="<?= APP_URL ?>/sslcommerz/init.php?type=subscription" class="btn btn-primary w-100 py-3 rounded-pill fw-800 shadow-sm" style="font-size:1.05rem">
          <i class="fa fa-lock me-2"></i>Pay ৳<?= SUBSCRIPTION_PRICE ?> via SSLCommerz
        </a>

        <div class="text-center mt-3 text-muted" style="font-size:.8rem">
          <i class="fa fa-shield-alt text-success me-1"></i>Supports bKash, Nagad, Rocket, Cards, and Net Banking.
        </div>
      </div>
    </div>

    <!-- ══ Right Column: How it Works & FAQ ══ -->
    <div class="col-lg-6">
      <div class="bg-white rounded-4 border p-4 p-md-5 h-100">
        <h5 class="fw-700 mb-3"><i class="fa fa-question-circle me-2 text-primary"></i>How Subscription Works</h5>

        <div class="d-flex gap-3 mb-4">
          <div class="rounded-circle bg-light d-flex align-items-center justify-content-center fw-800 text-primary" style="width:40px;height:40px;flex-shrink:0">1</div>
          <div>
            <h6 class="fw-700 mb-1">Make Payment</h6>
            <p class="text-muted mb-0" style="font-size:.88rem">Pay ৳399 securely through SSLCommerz sandbox or live gateway using mobile banking or cards.</p>
          </div>
        </div>

        <div class="d-flex gap-3 mb-4">
          <div class="rounded-circle bg-light d-flex align-items-center justify-content-center fw-800 text-primary" style="width:40px;height:40px;flex-shrink:0">2</div>
          <div>
            <h6 class="fw-700 mb-1">Instant Activation</h6>
            <p class="text-muted mb-0" style="font-size:.88rem">Your profile status immediately switches to <strong>Active</strong>. Customers in your thana and district will see you first.</p>
          </div>
        </div>

        <div class="d-flex gap-3 mb-4">
          <div class="rounded-circle bg-light d-flex align-items-center justify-content-center fw-800 text-primary" style="width:40px;height:40px;flex-shrink:0">3</div>
          <div>
            <h6 class="fw-700 mb-1">30 Days Validity</h6>
            <p class="text-muted mb-0" style="font-size:.88rem">You will receive an automated notification before the 30-day period expires so you can renew without losing leads.</p>
          </div>
        </div>

        <div class="p-3 rounded-3" style="background:#fef7e0;border:1px solid #fce8b2;font-size:.85rem">
          <i class="fa fa-info-circle text-warning me-1"></i>
          <strong>Admin Verification:</strong> Ensure your profile is approved by Admin to appear on top of search rankings.
        </div>
      </div>
    </div>

  </div>

  <!-- ══ Subscription History Table ══ -->
  <div class="bg-white rounded-4 border p-4">
    <h5 class="fw-700 mb-3"><i class="fa fa-history me-2 text-primary"></i>Payment & Subscription History</h5>

    <?php if (empty($subHistory)): ?>
      <div class="text-center py-4 text-muted">
        <i class="fa fa-receipt fa-2x mb-2 text-muted" style="opacity:.3"></i>
        <p class="mb-0">No past transactions found.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light" style="font-size:.85rem">
            <tr>
              <th>Date</th>
              <th>Transaction ID</th>
              <th>Amount</th>
              <th>Method</th>
              <th>Validity</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody style="font-size:.88rem">
            <?php foreach ($subHistory as $s): 
              $badge = match($s['status']) {
                'active'   => 'bg-success',
                'expired'  => 'bg-secondary',
                'pending'  => 'bg-warning text-dark',
                default    => 'bg-danger'
              };
            ?>
              <tr>
                <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                <td><code><?= htmlspecialchars($s['transaction_id'] ?: 'Pending') ?></code></td>
                <td class="fw-700">৳<?= number_format($s['amount']) ?></td>
                <td><?= strtoupper($s['payment_method']) ?></td>
                <td>
                  <?= $s['starts_at'] ? date('d M Y', strtotime($s['starts_at'])) . ' → ' . date('d M Y', strtotime($s['expires_at'])) : '—' ?>
                </td>
                <td><span class="badge <?= $badge ?>"><?= ucfirst($s['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
