<?php
// ============================================================
// Worker Dashboard & Management Portal — E-Sheba
// Handles Dashboard, Bookings, Earnings, and Reviews
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';
Auth::requireRole('worker');

$myId   = Auth::id();
$myName = $_SESSION['user_name'] ?? 'Worker';
$page   = $_GET['page'] ?? basename($_SERVER['PHP_SELF'], '.php');
if (!in_array($page, ['dashboard', 'bookings', 'earnings', 'reviews'])) {
    $page = 'dashboard';
}

// Get worker data
$worker = DB::fetchOne(
    "SELECT w.*, u.name, u.email, u.avatar, u.phone
     FROM workers w JOIN users u ON w.user_id=u.id
     WHERE w.user_id=?", [$myId]
);
if (!$worker) { header('Location: ../register.php?role=worker'); exit; }

$wId = $worker['id'];

// Stats
$totalBookings = DB::fetchOne("SELECT COUNT(*) c FROM bookings WHERE worker_id=?", [$wId])['c'];
$completedJobs = DB::fetchOne("SELECT COUNT(*) c FROM bookings WHERE worker_id=? AND status='completed'", [$wId])['c'];
$pendingJobs   = DB::fetchOne("SELECT COUNT(*) c FROM bookings WHERE worker_id=? AND status='pending'", [$wId])['c'];
$totalEarnings = DB::fetchOne("SELECT COALESCE(SUM(total_amount),0) c FROM bookings WHERE worker_id=? AND pay_status='paid'", [$wId])['c'];
$unreadMessages= DB::fetchOne("SELECT COUNT(*) c FROM messages WHERE receiver_id=? AND is_read=0", [$myId])['c'];

// Active subscription
$subscription = DB::fetchOne("SELECT * FROM subscriptions WHERE worker_id=? AND status='active' ORDER BY expires_at DESC LIMIT 1", [$wId]);

// Bookings query (with description and customer contact)
$bookingsQuery = "SELECT b.*, u.name as customer_name, u.phone as customer_phone, u.email as customer_email, c.name as category_name
                  FROM bookings b
                  JOIN users u ON b.customer_id=u.id
                  JOIN categories c ON b.category_id=c.id
                  WHERE b.worker_id=? ORDER BY b.created_at DESC";

if ($page === 'dashboard') {
    $bookings = DB::fetchAll($bookingsQuery . " LIMIT 8", [$wId]);
} else {
    $bookings = DB::fetchAll($bookingsQuery, [$wId]);
}

// Reviews query
$reviews = DB::fetchAll(
    "SELECT r.*, u.name as customer_name, u.avatar as customer_avatar, b.id as booking_id, c.name as category_name
     FROM reviews r
     JOIN users u ON r.customer_id=u.id
     LEFT JOIN bookings b ON r.booking_id=b.id
     LEFT JOIN categories c ON b.category_id=c.id
     WHERE r.worker_id=? ORDER BY r.created_at DESC",
    [$wId]
);

// Earnings Breakdown (for Earnings page)
$cashEarnings   = DB::fetchOne("SELECT COALESCE(SUM(total_amount),0) c FROM bookings WHERE worker_id=? AND pay_status='paid' AND pay_method='cash'", [$wId])['c'];
$onlineEarnings = DB::fetchOne("SELECT COALESCE(SUM(total_amount),0) c FROM bookings WHERE worker_id=? AND pay_status='paid' AND pay_method!='cash'", [$wId])['c'];
$pendingPayment = DB::fetchOne("SELECT COALESCE(SUM(total_amount),0) c FROM bookings WHERE worker_id=? AND pay_status IN ('unpaid','cash_pending')", [$wId])['c'];

$welcome = isset($_GET['welcome']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Worker Portal — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .page-title { font-weight: 900; font-size: 1.6rem; color: #0F172A; }
    .work-note-box { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 6px 10px; font-size: 0.8rem; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-esheba sticky-top">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="../index.php" class="brand d-flex align-items-center gap-2 text-decoration-none py-1">
      <img src="../images/logo.svg" alt="E-Sheba Logo" class="brand-logo-img" style="height:38px">
    </a>
    <div class="d-flex align-items-center gap-3">
      <a href="../chat.php" class="btn btn-link position-relative text-muted p-2" title="Messages">
        <i class="fa fa-comment-dots fs-5"></i>
        <?php if ($unreadMessages > 0): ?><span class="notification-badge"><?= $unreadMessages ?></span><?php endif; ?>
      </a>
      <div class="dropdown">
        <button class="btn d-flex align-items-center gap-2 border rounded-pill px-3 py-1 shadow-sm" data-bs-toggle="dropdown">
          <?php if (!empty($worker['avatar']) && file_exists(__DIR__ . '/../' . $worker['avatar'])): ?>
            <img src="../<?= htmlspecialchars($worker['avatar']) ?>" alt="Avatar" class="rounded-circle shadow-sm" style="width:32px;height:32px;object-fit:cover;border:1px solid #1a73e8">
          <?php else: ?>
            <div class="chat-avatar" style="width:32px;height:32px;font-size:.75rem;background:linear-gradient(135deg,#1A73E8,#0D47A1);color:#fff"><?= strtoupper($myName[0]) ?></div>
          <?php endif; ?>
          <span class="d-none d-md-inline" style="font-size:.88rem;font-weight:600"><?= htmlspecialchars($myName) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" style="border-radius:12px">
          <li><a class="dropdown-item py-2" href="profile.php"><i class="fa fa-user me-2 text-primary"></i>My Profile & Photo</a></li>
          <li><a class="dropdown-item py-2" href="subscription.php"><i class="fa fa-crown me-2 text-warning"></i>Subscription</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item py-2 text-danger" href="../api/logout.php"><i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="dashboard-layout">

  <!-- ══ Sidebar ══ -->
  <div class="sidebar">
    <div class="p-4 border-bottom" style="border-color:rgba(255,255,255,.1)!important">
      <div class="d-flex align-items-center gap-3">
        <?php if (!empty($worker['avatar']) && file_exists(__DIR__ . '/../' . $worker['avatar'])): ?>
          <img src="../<?= htmlspecialchars($worker['avatar']) ?>" alt="Avatar" class="rounded-circle shadow-sm" style="width:48px;height:48px;object-fit:cover;border:2px solid #fff">
        <?php else: ?>
          <div class="chat-avatar" style="width:48px;height:48px;font-size:1.1rem"><?= strtoupper($myName[0]) ?></div>
        <?php endif; ?>
        <div>
          <div class="text-white fw-700" style="font-size:.9rem"><?= htmlspecialchars($myName) ?></div>
          <div style="font-size:.72rem;color:rgba(255,255,255,.5)">Service Provider</div>
          <?php if ($subscription): ?>
            <span style="background:#fbbc05;color:#333;font-size:.65rem;padding:2px 8px;border-radius:50px;font-weight:700">PRO ✓</span>
          <?php else: ?>
            <span style="background:var(--danger);color:#fff;font-size:.65rem;padding:2px 8px;border-radius:50px;font-weight:700">Subscribe!</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="sidebar-menu">
      <div class="sidebar-label">Main</div>
      <a href="dashboard.php" class="sidebar-item <?= $page==='dashboard'?'active':'' ?>"><i class="fa fa-tachometer-alt"></i> Dashboard</a>
      <a href="bookings.php"  class="sidebar-item <?= $page==='bookings'?'active':'' ?>"><i class="fa fa-calendar-check"></i> Bookings <span class="ms-auto badge bg-warning text-dark"><?= $pendingJobs ?></span></a>
      <a href="../chat.php"   class="sidebar-item"><i class="fa fa-comments"></i> Messages <span class="ms-auto badge bg-danger"><?= $unreadMessages ?: '' ?></span></a>
      
      <div class="sidebar-label">Finances & Feedback</div>
      <a href="earnings.php"     class="sidebar-item <?= $page==='earnings'?'active':'' ?>"><i class="fa fa-wallet"></i> Earnings <span class="ms-auto badge bg-success">৳<?= number_format($totalEarnings) ?></span></a>
      <a href="reviews.php"      class="sidebar-item <?= $page==='reviews'?'active':'' ?>"><i class="fa fa-star"></i> Reviews <span class="ms-auto badge bg-warning text-dark"><?= $worker['avg_rating'] ?>★</span></a>
      <a href="subscription.php" class="sidebar-item"><i class="fa fa-crown"></i> Subscription</a>
      
      <div class="sidebar-label">Account</div>
      <a href="profile.php"      class="sidebar-item"><i class="fa fa-user-edit"></i> Edit Profile & Photo</a>
      <a href="../api/logout.php" class="sidebar-item"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>

  <!-- ══ Main Content ══ -->
  <div class="dashboard-content">

    <?php if ($welcome): ?>
    <div class="alert alert-success d-flex align-items-center gap-3 mb-4 shadow-sm" style="border-radius:14px">
      <span style="font-size:28px">🎉</span>
      <div>
        <strong>Welcome to E-Sheba!</strong> Your profile is active. Remember to keep your availability status updated.
      </div>
    </div>
    <?php endif; ?>

    <!-- ══════════════════ 1. DASHBOARD OVERVIEW ══════════════════ -->
    <?php if ($page === 'dashboard'): ?>
    
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
      <div>
        <h1 class="page-title mb-0">Technician Dashboard</h1>
        <small class="text-muted">Welcome back, <?= htmlspecialchars($myName) ?>. Manage jobs & live incoming orders.</small>
      </div>
      <!-- Availability Toggle -->
      <div class="availability-card <?= !$worker['is_available']?'off':'' ?>" id="availCard" style="padding:14px 20px">
        <div>
          <div class="fw-700" style="font-size:.92rem">Availability Status</div>
          <div style="font-size:.78rem;opacity:.85" id="availText"><?= $worker['is_available']?'You are AVAILABLE for jobs':'You are BUSY / Offline' ?></div>
        </div>
        <label class="toggle-switch ms-auto" title="Toggle availability">
          <input type="checkbox" id="availToggle" <?= $worker['is_available']?'checked':'' ?> onchange="toggleAvailability(this.checked)">
          <span class="toggle-slider"></span>
        </label>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
      <?php $stats = [
        ['fa-calendar-check','Total Bookings',$totalBookings,'#1a73e8','linear-gradient(135deg,#e8f0fe,#d0e3ff)'],
        ['fa-tools','Completed Jobs',$completedJobs,'#34a853','linear-gradient(135deg,#e6f4ea,#ceead6)'],
        ['fa-clock','Pending Jobs',$pendingJobs,'#fbbc05','linear-gradient(135deg,#fef9e0,#fde68a)'],
        ['fa-wallet','Total Earnings','৳'.number_format($totalEarnings),'#8b5cf6','linear-gradient(135deg,#f3e8ff,#ddd6fe)'],
        ['fa-star','Avg Rating',$worker['avg_rating'].'★','#f59e0b','linear-gradient(135deg,#fffbeb,#fde68a)'],
        ['fa-comment-dots','Unread Msgs',$unreadMessages,'#06b6d4','linear-gradient(135deg,#ecfeff,#cffafe)'],
      ]; foreach($stats as [$icon,$label,$val,$color,$bg]): ?>
      <div class="col-6 col-xl-4">
        <div class="stat-card">
          <div class="stat-icon" style="background:<?= $bg ?>;color:<?= $color ?>"><i class="fa <?= $icon ?>"></i></div>
          <div class="stat-info">
            <div class="num"><?= $val ?></div>
            <div class="label"><?= $label ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="row g-4">
      <!-- Recent Bookings Table with Full Description Support -->
      <div class="col-lg-8">
        <div class="stat-card flex-column align-items-stretch p-0 shadow-sm" style="border-radius:var(--radius-md);overflow:hidden">
          <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="fw-700 mb-0"><i class="fa fa-calendar-alt me-2 text-primary"></i>Recent Bookings</h5>
            <a href="bookings.php" class="btn btn-sm btn-outline-primary rounded-pill">View All Bookings</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead style="background:#f8f9ff"><tr>
                <th class="ps-4 py-3">Customer</th><th>Service & Note</th><th>Amount & Pay</th><th>Status</th><th>Actions</th>
              </tr></thead>
              <tbody>
                <?php if (empty($bookings)): ?>
                <tr><td colspan="5" class="text-center py-5 text-muted"><i class="fa fa-calendar fa-2x mb-2 d-block"></i>No bookings received yet</td></tr>
                <?php else: foreach ($bookings as $b):
                  $badgeClass = match($b['status']){
                    'completed'=>'success','accepted'=>'primary','in_progress'=>'info','cancelled'=>'danger',default=>'warning'
                  };
                  $jsonStr = htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8');
                ?>
                <tr>
                  <td class="ps-4 py-3">
                    <div class="d-flex align-items-center gap-2">
                      <div class="chat-avatar" style="width:34px;height:34px;font-size:.72rem"><?= strtoupper($b['customer_name'][0]) ?></div>
                      <div>
                        <span class="fw-700" style="font-size:.88rem"><?= htmlspecialchars($b['customer_name']) ?></span>
                        <div style="font-size:.75rem;color:var(--muted)"><?= $b['scheduled_at'] ? date('d M, h:i A', strtotime($b['scheduled_at'])) : '—' ?></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-primary-subtle text-primary mb-1"><?= htmlspecialchars($b['category_name']) ?></span>
                    <?php if ($b['description']): ?>
                      <div class="work-note-box text-truncate" style="max-width:240px;" title="<?= htmlspecialchars($b['description']) ?>">
                        <i class="fa fa-info-circle me-1 text-primary"></i><?= htmlspecialchars($b['description']) ?>
                      </div>
                    <?php else: ?>
                      <div class="text-muted" style="font-size:.78rem">No specific note</div>
                    <?php endif; ?>
                    <?php if ($b['address']): ?>
                      <div style="font-size:.75rem;color:#64748b;margin-top:2px"><i class="fa fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($b['address']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="fw-800" style="font-size:.9rem">৳<?= number_format($b['total_amount'] ?? 0) ?></div>
                    <!-- Payment Status & Worker Cash Confirmation -->
                    <?php if ($b['pay_method'] === 'cash'): ?>
                      <?php if ($b['pay_status'] === 'cash_pending'): ?>
                        <button class="btn btn-sm btn-success fw-700 py-1 px-2 mt-1 shadow-sm" onclick="confirmCashPayment(<?= $b['id'] ?>, <?= $b['total_amount'] ?>)">
                          <i class="fa fa-check-double me-1"></i>Confirm Cash (৳<?= number_format($b['total_amount']) ?>)
                        </button>
                      <?php elseif ($b['pay_status'] === 'paid'): ?>
                        <span class="badge bg-success-subtle text-success border border-success mt-1"><i class="fa fa-check-circle me-1"></i>Cash Paid ✓</span>
                      <?php else: ?>
                        <button class="btn btn-sm btn-outline-success py-1 px-2 mt-1" onclick="confirmCashPayment(<?= $b['id'] ?>, <?= $b['total_amount'] ?>)">
                          <i class="fa fa-money-bill-wave me-1"></i>Got Cash
                        </button>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="badge bg-<?= $b['pay_status']==='paid'?'success':'secondary' ?> mt-1">
                        Online: <?= ucfirst($b['pay_status']) ?>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge bg-<?= $badgeClass ?>"><?= ucfirst(str_replace('_',' ',$b['status'])) ?></span></td>
                  <td>
                    <div class="d-flex gap-1 flex-wrap">
                      <?php if ($b['status'] === 'pending'): ?>
                        <button class="btn btn-sm btn-success py-1 px-2" onclick="updateBooking(<?= $b['id'] ?>,'accepted')">Accept</button>
                        <button class="btn btn-sm btn-outline-danger py-1 px-2" onclick="updateBooking(<?= $b['id'] ?>,'cancelled')">Decline</button>
                      <?php elseif ($b['status'] === 'accepted'): ?>
                        <button class="btn btn-sm btn-primary py-1 px-2" onclick="updateBooking(<?= $b['id'] ?>,'in_progress')">Start Job</button>
                      <?php elseif ($b['status'] === 'in_progress'): ?>
                        <button class="btn btn-sm btn-success py-1 px-2" onclick="updateBooking(<?= $b['id'] ?>,'completed')">Complete Job</button>
                      <?php endif; ?>
                      <a href="../chat.php?to=<?= $b['customer_id'] ?>" class="btn btn-sm btn-outline-primary py-1 px-2" title="Chat"><i class="fa fa-comment"></i></a>
                      <button class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="viewBookingDetails(<?= $jsonStr ?>)" title="View Details"><i class="fa fa-eye"></i></button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="col-lg-4">
        <!-- Subscription Status -->
        <div class="mb-3 p-4 rounded-4 shadow-sm" style="background:<?= $subscription?'linear-gradient(135deg,#1a73e8,#0d47a1)':'linear-gradient(135deg,#ea4335,#c62828)' ?>;color:#fff">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa fa-crown fa-lg" style="color:#fbbc05"></i>
            <span class="fw-700">Membership Tier</span>
          </div>
          <?php if ($subscription): ?>
            <div style="font-size:1.3rem;font-weight:900">PRO ACTIVE ✓</div>
            <div style="font-size:.82rem;opacity:.85">Valid until: <?= date('d M Y', strtotime($subscription['expires_at'])) ?></div>
          <?php else: ?>
            <div style="font-size:1.1rem;font-weight:900">Not Subscribed</div>
            <div style="font-size:.82rem;opacity:.9;margin:8px 0">Subscribe for ৳399/month to receive customer leads & orders.</div>
            <a href="subscription.php" style="background:#fbbc05;color:#333;padding:8px 20px;border-radius:50px;font-weight:800;font-size:.85rem;text-decoration:none;display:inline-block;margin-top:4px">Subscribe Now →</a>
          <?php endif; ?>
        </div>

        <!-- Live Location -->
        <div class="stat-card flex-column align-items-stretch p-0 mb-3 shadow-sm" style="border-radius:var(--radius-md);overflow:hidden">
          <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="fw-700 mb-0"><i class="fa fa-map-marker-alt me-2 text-danger"></i>Service Location</h6>
            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="shareLocation()">Update GPS</button>
          </div>
          <div class="location-map" style="height:180px;border-radius:0;border:none">
            <?php if ($worker['latitude'] && $worker['longitude']): ?>
              <div id="workerMap" style="height:180px"></div>
            <?php else: ?>
              <div class="d-flex align-items-center justify-content-center h-100 flex-column" style="background:#f0f4ff">
                <i class="fa fa-map-marker-alt fa-2x text-muted mb-2"></i>
                <span style="font-size:.82rem;color:var(--muted)">GPS Location not set</span>
                <button class="btn btn-sm btn-primary mt-2 rounded-pill" onclick="shareLocation()">Share Location</button>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Recent Reviews Widget -->
        <div class="stat-card flex-column align-items-stretch p-0 shadow-sm" style="border-radius:var(--radius-md);overflow:hidden">
          <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="fw-700 mb-0"><i class="fa fa-star me-2 text-warning"></i>Recent Feedback</h6>
            <a href="reviews.php" class="btn btn-sm btn-link text-decoration-none">View All</a>
          </div>
          <div class="p-3" style="display:flex;flex-direction:column;gap:12px">
            <?php if (empty($reviews)): ?>
              <div class="text-center py-3 text-muted" style="font-size:.85rem"><i class="fa fa-star fa-lg mb-2 d-block" style="opacity:.3"></i>No reviews received yet</div>
            <?php else: foreach (array_slice($reviews, 0, 3) as $r): ?>
              <div class="p-3 rounded-3" style="background:#f8f9ff">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <div class="chat-avatar" style="width:28px;height:28px;font-size:.65rem"><?= strtoupper($r['customer_name'][0]) ?></div>
                  <span class="fw-700" style="font-size:.82rem"><?= htmlspecialchars($r['customer_name']) ?></span>
                  <span class="ms-auto text-warning" style="font-size:.8rem"><?= str_repeat('★', $r['rating']) ?></span>
                </div>
                <?php if ($r['comment']): ?>
                  <p class="mb-0 text-muted" style="font-size:.8rem">"<?= htmlspecialchars($r['comment']) ?>"</p>
                <?php endif; ?>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════ 2. FULL BOOKINGS PAGE ══════════════════ -->
    <?php elseif ($page === 'bookings'): ?>
    
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
      <div>
        <h1 class="page-title mb-0">Bookings Management (<?= count($bookings) ?>)</h1>
        <small class="text-muted">Inspect customer work notes, service addresses, and manage statuses</small>
      </div>
      <input type="text" placeholder="Search customer, note, service..." class="form-control" style="max-width:280px;border-radius:50px" oninput="filterTable(this,'workerBookingsTable')">
    </div>

    <!-- Filter Buttons -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
      <button class="btn btn-sm btn-primary rounded-pill px-3 active" onclick="filterBookingsRow('all', this)">All (<?= count($bookings) ?>)</button>
      <button class="btn btn-sm btn-outline-warning rounded-pill px-3 text-dark" onclick="filterBookingsRow('pending', this)">Pending</button>
      <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="filterBookingsRow('accepted', this)">Accepted</button>
      <button class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="filterBookingsRow('in_progress', this)">In Progress</button>
      <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="filterBookingsRow('completed', this)">Completed</button>
    </div>

    <div class="stat-card flex-column align-items-stretch p-0 shadow-sm" style="border-radius:var(--radius-md);overflow:hidden">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="workerBookingsTable">
          <thead style="background:#f8f9ff"><tr>
            <th class="ps-4 py-3">Booking ID & Customer</th>
            <th>Service Type</th>
            <th>Work Description & Note</th>
            <th>Service Address</th>
            <th>Scheduled</th>
            <th>Amount & Cash Flow</th>
            <th>Status</th>
            <th>Actions</th>
          </tr></thead>
          <tbody>
            <?php if (empty($bookings)): ?>
              <tr><td colspan="8" class="text-center py-5 text-muted">No bookings found for your profile.</td></tr>
            <?php else: foreach ($bookings as $b):
              $badgeClass = match($b['status']){
                'completed'=>'success','accepted'=>'primary','in_progress'=>'info','cancelled'=>'danger',default=>'warning'
              };
              $jsonStr = htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8');
            ?>
            <tr class="booking-row" data-status="<?= $b['status'] ?>">
              <td class="ps-4 py-3">
                <span class="badge bg-secondary mb-1">#<?= $b['id'] ?></span>
                <div class="fw-700" style="font-size:.88rem"><?= htmlspecialchars($b['customer_name']) ?></div>
                <div style="font-size:.75rem;color:var(--muted)"><i class="fa fa-phone me-1"></i><?= htmlspecialchars($b['customer_phone'] ?? '—') ?></div>
              </td>
              <td><span class="badge bg-primary-subtle text-primary fw-700"><?= htmlspecialchars($b['category_name']) ?></span></td>
              <td>
                <div class="work-note-box" style="max-width:260px;">
                  <strong class="d-block text-dark"><i class="fa fa-wrench me-1 text-warning"></i>Problem Description:</strong>
                  <?= htmlspecialchars($b['description'] ?: 'No details provided by customer.') ?>
                </div>
              </td>
              <td style="font-size:.82rem;color:#475569;max-width:200px">
                <i class="fa fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($b['address'] ?: 'Not provided') ?>
              </td>
              <td style="font-size:.82rem;color:var(--muted)"><?= $b['scheduled_at'] ? date('d M, h:i A', strtotime($b['scheduled_at'])) : 'Flexible' ?></td>
              <td>
                <div class="fw-800" style="font-size:.92rem">৳<?= number_format($b['total_amount'] ?? 0) ?></div>
                <?php if ($b['pay_method'] === 'cash'): ?>
                  <?php if ($b['pay_status'] === 'cash_pending'): ?>
                    <button class="btn btn-sm btn-success fw-700 py-1 px-2 mt-1 shadow-sm" onclick="confirmCashPayment(<?= $b['id'] ?>, <?= $b['total_amount'] ?>)">
                      <i class="fa fa-check-double me-1"></i>Confirm Cash (৳<?= number_format($b['total_amount']) ?>)
                    </button>
                  <?php elseif ($b['pay_status'] === 'paid'): ?>
                    <span class="badge bg-success-subtle text-success border border-success mt-1"><i class="fa fa-check-circle me-1"></i>Cash Received ✓</span>
                  <?php else: ?>
                    <button class="btn btn-sm btn-outline-success py-1 px-2 mt-1" onclick="confirmCashPayment(<?= $b['id'] ?>, <?= $b['total_amount'] ?>)">
                      <i class="fa fa-money-bill-wave me-1"></i>Got Cash
                    </button>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="badge bg-<?= $b['pay_status']==='paid'?'success':'secondary' ?> mt-1">Online: <?= ucfirst($b['pay_status']) ?></span>
                <?php endif; ?>
              </td>
              <td><span class="badge bg-<?= $badgeClass ?>"><?= ucfirst(str_replace('_',' ',$b['status'])) ?></span></td>
              <td>
                <div class="d-flex gap-1 flex-wrap">
                  <?php if ($b['status'] === 'pending'): ?>
                    <button class="btn btn-sm btn-success py-1 px-2" onclick="updateBooking(<?= $b['id'] ?>,'accepted')">Accept</button>
                    <button class="btn btn-sm btn-outline-danger py-1 px-2" onclick="updateBooking(<?= $b['id'] ?>,'cancelled')">Decline</button>
                  <?php elseif ($b['status'] === 'accepted'): ?>
                    <button class="btn btn-sm btn-primary py-1 px-2" onclick="updateBooking(<?= $b['id'] ?>,'in_progress')">Start Job</button>
                  <?php elseif ($b['status'] === 'in_progress'): ?>
                    <button class="btn btn-sm btn-success py-1 px-2" onclick="updateBooking(<?= $b['id'] ?>,'completed')">Complete Job</button>
                  <?php endif; ?>
                  <a href="../chat.php?to=<?= $b['customer_id'] ?>" class="btn btn-sm btn-outline-primary py-1 px-2"><i class="fa fa-comment"></i></a>
                  <button class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="viewBookingDetails(<?= $jsonStr ?>)"><i class="fa fa-eye"></i></button>
                </div>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════════════════ 3. EARNINGS & PAYOUTS PAGE ══════════════════ -->
    <?php elseif ($page === 'earnings'): ?>
    
    <div class="mb-4">
      <h1 class="page-title mb-1">Earnings & Transaction History</h1>
      <small class="text-muted">Track your cash collections and verified earnings from completed service bookings</small>
    </div>

    <!-- Earnings Overview Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon" style="background:#e8f0fe;color:#1a73e8"><i class="fa fa-wallet"></i></div>
          <div class="stat-info">
            <div class="num">৳<?= number_format($totalEarnings) ?></div>
            <div class="label">Total Verified Earnings</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon" style="background:#e6f4ea;color:#10b981"><i class="fa fa-money-bill-wave"></i></div>
          <div class="stat-info">
            <div class="num">৳<?= number_format($cashEarnings) ?></div>
            <div class="label">Cash on Delivery Paid</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon" style="background:#f3e8ff;color:#8b5cf6"><i class="fa fa-credit-card"></i></div>
          <div class="stat-info">
            <div class="num">৳<?= number_format($onlineEarnings) ?></div>
            <div class="label">Paid Online (SSLCommerz)</div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon" style="background:#fef9e0;color:#f59e0b"><i class="fa fa-hourglass-half"></i></div>
          <div class="stat-info">
            <div class="num">৳<?= number_format($pendingPayment) ?></div>
            <div class="label">Awaiting Payment / Cash Conf.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Earnings Breakdown Table -->
    <div class="stat-card flex-column align-items-stretch p-0 shadow-sm" style="border-radius:var(--radius-md);overflow:hidden">
      <div class="p-4 border-bottom">
        <h5 class="fw-700 mb-0"><i class="fa fa-receipt me-2 text-primary"></i>Service Revenue Breakdown</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background:#f8f9ff"><tr>
            <th class="ps-4 py-3">Booking ID</th>
            <th>Customer</th>
            <th>Service</th>
            <th>Date</th>
            <th>Method</th>
            <th>Earned Amount</th>
            <th>Payment Status</th>
            <th>Job Status</th>
          </tr></thead>
          <tbody>
            <?php if (empty($bookings)): ?>
              <tr><td colspan="8" class="text-center py-5 text-muted">No transactions found.</td></tr>
            <?php else: foreach ($bookings as $b): ?>
            <tr>
              <td class="ps-4 py-3 fw-700">#<?= $b['id'] ?></td>
              <td><?= htmlspecialchars($b['customer_name']) ?></td>
              <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($b['category_name']) ?></span></td>
              <td style="font-size:.82rem;color:var(--muted)"><?= date('d M Y', strtotime($b['created_at'])) ?></td>
              <td style="font-size:.82rem;text-transform:uppercase;font-weight:600"><?= htmlspecialchars($b['pay_method'] ?: 'Cash') ?></td>
              <td style="font-weight:800;font-size:.95rem;color:#0F172A">৳<?= number_format($b['total_amount'] ?? 0) ?></td>
              <td>
                <?php if ($b['pay_status'] === 'paid'): ?>
                  <span class="badge bg-success-subtle text-success border border-success"><i class="fa fa-check-circle me-1"></i>Verified Paid</span>
                <?php elseif ($b['pay_status'] === 'cash_pending'): ?>
                  <span class="badge bg-warning text-dark"><i class="fa fa-clock me-1"></i>Awaiting Cash Confirmation</span>
                <?php else: ?>
                  <span class="badge bg-secondary-subtle text-muted border">Unpaid</span>
                <?php endif; ?>
              </td>
              <td><span class="badge bg-<?= $b['status']==='completed'?'success':($b['status']==='cancelled'?'danger':'primary') ?>"><?= ucfirst(str_replace('_',' ',$b['status'])) ?></span></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════════════════ 4. REVIEWS & RATINGS PAGE ══════════════════ -->
    <?php elseif ($page === 'reviews'): ?>
    
    <div class="mb-4">
      <h1 class="page-title mb-1">Customer Reviews & Ratings</h1>
      <small class="text-muted">Real client feedback from verified service completions</small>
    </div>

    <!-- Rating Summary Card -->
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="bg-white rounded-4 border p-4 text-center shadow-sm h-100 d-flex flex-column justify-content-center">
          <div style="font-size:3.5rem;font-weight:900;color:#F59E0B;line-height:1"><?= number_format($worker['avg_rating'], 1) ?></div>
          <div class="text-warning fs-5 my-2"><?= str_repeat('★', (int)round($worker['avg_rating'])) ?><?= str_repeat('☆', 5 - (int)round($worker['avg_rating'])) ?></div>
          <div class="text-muted fw-600" style="font-size:.88rem">Based on <?= count($reviews) ?> customer reviews</div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="bg-white rounded-4 border p-4 shadow-sm h-100">
          <h6 class="fw-700 mb-3">Customer Trust & Performance</h6>
          <p class="text-muted" style="font-size:.88rem;line-height:1.6">
            Higher ratings increase your profile ranking in search results. Every customer review is verified and tied directly to a completed booking job.
          </p>
          <div class="d-flex align-items-center gap-2 text-success fw-700 mt-3" style="font-size:.85rem">
            <i class="fa fa-shield-alt fs-5"></i> 100% Genuine Verified Reviews
          </div>
        </div>
      </div>
    </div>

    <!-- Reviews List -->
    <div class="row g-3">
      <?php if (empty($reviews)): ?>
        <div class="col-12">
          <div class="stat-card text-center py-5 text-muted justify-content-center">
            <i class="fa fa-star fa-3x mb-3 text-warning opacity-25"></i>
            <h5>No Reviews Yet</h5>
            <p class="mb-0">Complete jobs and ask your customers to leave a review after service.</p>
          </div>
        </div>
      <?php else: foreach ($reviews as $r): ?>
        <div class="col-md-6">
          <div class="bg-white rounded-4 border p-4 shadow-sm h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="chat-avatar" style="width:42px;height:42px;font-size:.85rem;background:linear-gradient(135deg,#1A73E8,#0D47A1);color:#fff">
                <?= strtoupper($r['customer_name'][0]) ?>
              </div>
              <div class="flex-grow-1">
                <div class="fw-700" style="font-size:.92rem"><?= htmlspecialchars($r['customer_name']) ?></div>
                <div style="font-size:.75rem;color:var(--muted)">
                  <i class="fa fa-calendar me-1"></i><?= date('d M Y', strtotime($r['created_at'])) ?>
                  <?php if ($r['category_name']): ?> · <span class="badge bg-light text-dark border"><?= htmlspecialchars($r['category_name']) ?></span><?php endif; ?>
                </div>
              </div>
              <div class="text-warning fw-800" style="font-size:1.1rem">
                <?= str_repeat('★', $r['rating']) ?>
              </div>
            </div>
            <div class="p-3 rounded-3 bg-light" style="font-size:.88rem;color:#334155;line-height:1.6">
              "<?= htmlspecialchars($r['comment'] ?: 'Great service, technician was on time and very professional.') ?>"
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <?php endif; ?>

  </div>
</div>

<!-- ══ Booking Detail Modal ══ -->
<div class="modal fade" id="bookingDetailModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:18px;border:none">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-800">Booking Details <span id="bModalId" class="text-primary"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="p-3 rounded-3 bg-light mb-3">
          <div class="row g-2" style="font-size:.88rem">
            <div class="col-6"><span class="text-muted d-block">Customer:</span><strong id="bModalCustomer"></strong></div>
            <div class="col-6"><span class="text-muted d-block">Service:</span><strong id="bModalService"></strong></div>
            <div class="col-6"><span class="text-muted d-block">Scheduled:</span><strong id="bModalDate"></strong></div>
            <div class="col-6"><span class="text-muted d-block">Amount:</span><strong id="bModalAmount" class="text-success fs-6"></strong></div>
            <div class="col-6"><span class="text-muted d-block">Payment:</span><strong id="bModalPayMethod"></strong></div>
            <div class="col-6"><span class="text-muted d-block">Status:</span><strong id="bModalStatus"></strong></div>
          </div>
        </div>

        <div class="mb-3">
          <label class="fw-700 mb-1" style="font-size:.85rem"><i class="fa fa-wrench me-1 text-warning"></i>Customer Problem / Work Description</label>
          <div class="p-3 rounded-3 border bg-white" id="bModalDesc" style="font-size:.88rem;line-height:1.6"></div>
        </div>

        <div class="mb-2">
          <label class="fw-700 mb-1" style="font-size:.85rem"><i class="fa fa-map-marker-alt me-1 text-danger"></i>Service Address</label>
          <div class="p-2 rounded-3 border bg-white text-muted" id="bModalAddress" style="font-size:.85rem"></div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Call Overlays -->
<div class="call-overlay" id="callOverlay" style="display:none">
  <div class="call-avatar" id="callAvatar">📞</div>
  <div class="call-name" id="callName"></div>
  <div class="call-status" id="callStatus">Calling...</div>
  <div class="call-controls">
    <button class="call-btn mute" onclick="toggleMute()"><i class="fa fa-microphone"></i></button>
    <button class="call-btn hangup" onclick="endCall()"><i class="fa fa-phone-slash"></i></button>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="../js/app.js"></script>

<script>
// Filter bookings rows by status
function filterBookingsRow(status, btn) {
  $(btn).parent().find('button').removeClass('active btn-primary').addClass('btn-outline-primary');
  $(btn).removeClass('btn-outline-primary').addClass('active btn-primary');
  
  if (status === 'all') {
    $('.booking-row').show();
  } else {
    $('.booking-row').hide();
    $(`.booking-row[data-status="${status}"]`).show();
  }
}

// Table search filter
function filterTable(input, tableId) {
  const filter = input.value.toLowerCase();
  document.querySelectorAll(`#${tableId} tbody tr`).forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
  });
}

// View booking details in modal
function viewBookingDetails(data) {
  document.getElementById('bModalId').innerText = '#' + data.id;
  document.getElementById('bModalCustomer').innerText = data.customer_name + (data.customer_phone ? ' (' + data.customer_phone + ')' : '');
  document.getElementById('bModalService').innerText = data.category_name;
  document.getElementById('bModalDate').innerText = data.scheduled_at || 'Flexible';
  document.getElementById('bModalAddress').innerText = data.address || 'No address provided';
  document.getElementById('bModalDesc').innerText = data.description || 'No work description provided';
  document.getElementById('bModalAmount').innerText = data.total_amount ? '৳' + Number(data.total_amount).toLocaleString() : 'TBD';
  document.getElementById('bModalPayMethod').innerText = (data.pay_method ? data.pay_method.toUpperCase() : 'CASH') + ' (' + (data.pay_status || 'unpaid').toUpperCase() + ')';
  document.getElementById('bModalStatus').innerText = (data.status || '').toUpperCase();
  new bootstrap.Modal(document.getElementById('bookingDetailModal')).show();
}

// Update booking status
function updateBooking(id, status) {
  if (!confirm(`Change booking status to "${status}"?`)) return;
  $.post('../api/update-booking.php', { booking_id: id, id: id, status: status }, function(res) {
    if (res.success) {
      showToast('Booking updated!', 'success');
      setTimeout(() => location.reload(), 1200);
    } else {
      showToast(res.error || 'Failed to update booking.', 'error');
    }
  }, 'json');
}

// ── Confirm Cash Received (Worker Side) ──────────────────────
function confirmCashPayment(bookingId, amount) {
  const amtStr = amount ? `৳${Number(amount).toLocaleString()}` : 'cash payment';
  if (!confirm(`Did you receive ${amtStr} in cash from the customer?`)) return;
  $.ajax({
    url: '../api/confirm-cash-payment.php',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ booking_id: bookingId, action: 'worker_confirmed' }),
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => location.reload(), 1200);
      } else {
        showToast(res.error || 'Failed to confirm payment.', 'error');
      }
    },
    error: function() {
      showToast('Network error while confirming cash payment.', 'error');
    }
  });
}

// Toggle Availability
function toggleAvailability(checked) {
  $.post('../api/update-availability.php', { is_available: checked ? 1 : 0 }, function(res) {
    if (res.success) {
      document.getElementById('availCard').classList.toggle('off', !checked);
      document.getElementById('availText').innerText = checked ? 'You are AVAILABLE for jobs' : 'You are BUSY / Offline';
      showToast(checked ? 'You are now marked Available!' : 'You are now marked Busy', 'info');
    }
  }, 'json');
}

// Live Location
function shareLocation() {
  if (!navigator.geolocation) {
    showToast('Geolocation is not supported by your browser.', 'error');
    return;
  }
  navigator.geolocation.getCurrentPosition(
    pos => {
      const lat = pos.coords.latitude;
      const lng = pos.coords.longitude;
      $.post('../api/update-availability.php', { latitude: lat, longitude: lng }, res => {
        if (res.success) {
          showToast('Location updated successfully!', 'success');
          setTimeout(() => location.reload(), 1000);
        }
      }, 'json');
    },
    err => {
      showToast('Location permission denied in browser.', 'warning');
    }
  );
}

<?php if ($worker['latitude'] && $worker['longitude'] && $page === 'dashboard'): ?>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof L !== 'undefined' && document.getElementById('workerMap')) {
    const map = L.map('workerMap').setView([<?= $worker['latitude'] ?>, <?= $worker['longitude'] ?>], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    L.marker([<?= $worker['latitude'] ?>, <?= $worker['longitude'] ?>]).addTo(map).bindPopup('<?= addslashes($worker['name']) ?>').openPopup();
  }
});
<?php endif; ?>
</script>
</body>
</html>
