<?php
// ============================================================
// Admin Panel Dashboard — E-Sheba
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';
Auth::requireRole('admin');
$page = $_GET['page'] ?? 'dashboard';

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $workerId = (int)($_POST['worker_id'] ?? 0);
    $catId    = (int)($_POST['category_id'] ?? 0);

    if ($action === 'approve' && $workerId) {
        DB::query("UPDATE workers SET is_approved=1 WHERE id=?", [$workerId]);
        $wUser = DB::fetchOne("SELECT user_id FROM workers WHERE id=?", [$workerId]);
        if ($wUser) {
            DB::query("INSERT INTO notifications(user_id,type,title,body) VALUES(?,?,?,?)", [
                $wUser['user_id'], 'approval', 'Profile Approved!',
                'Congratulations! Your service provider profile has been verified and approved.'
            ]);
        }
        showAdminMsg('Worker approved successfully!', 'success');
    } elseif ($action === 'reject' && $workerId) {
        DB::query("UPDATE workers SET is_approved=0 WHERE id=?", [$workerId]);
        showAdminMsg('Worker deactivated.', 'info');
    } elseif ($action === 'toggle_category' && $catId) {
        DB::query("UPDATE categories SET is_active = NOT is_active WHERE id=?", [$catId]);
        showAdminMsg('Category status updated.', 'success');
    } elseif ($action === 'add_category') {
        $cName = trim($_POST['name'] ?? '');
        $cIcon = trim($_POST['icon'] ?? 'fa-tools');
        $cDesc = trim($_POST['description'] ?? '');
        if ($cName) {
            DB::query("INSERT INTO categories (name, icon, description, is_active, sort_order) VALUES (?, ?, ?, 1, 0)", [$cName, $cIcon, $cDesc]);
            showAdminMsg("Category '$cName' added successfully!", 'success');
        } else {
            showAdminMsg('Category name is required.', 'danger');
        }
    }
}

function showAdminMsg($msg, $type) {
    $_SESSION['admin_msg'] = compact('msg', 'type');
}

// Stats
$totalUsers          = DB::fetchOne("SELECT COUNT(*) c FROM users WHERE role='customer'")['c'];
$totalWorkers        = DB::fetchOne("SELECT COUNT(*) c FROM workers")['c'];
$pendingApproval     = DB::fetchOne("SELECT COUNT(*) c FROM workers WHERE is_approved=0")['c'];
$totalBookings       = DB::fetchOne("SELECT COUNT(*) c FROM bookings")['c'];
$activeSubscriptions = DB::fetchOne("SELECT COUNT(*) c FROM subscriptions WHERE status='active'")['c'];
$revenue             = DB::fetchOne("SELECT COALESCE(SUM(amount),0) c FROM subscriptions WHERE status='active'")['c'];

// Data queries
$pendingWorkers = DB::fetchAll(
    "SELECT w.*, u.name, u.email, u.phone, u.created_at as reg_date,
            GROUP_CONCAT(c.name SEPARATOR ', ') as categories
     FROM workers w JOIN users u ON w.user_id=u.id
     LEFT JOIN worker_categories wc ON w.id=wc.worker_id
     LEFT JOIN categories c ON wc.category_id=c.id
     WHERE w.is_approved=0 GROUP BY w.id ORDER BY w.created_at DESC"
);

$approvedWorkers = DB::fetchAll(
    "SELECT w.*, u.name, u.email,
            GROUP_CONCAT(c.name SEPARATOR ', ') as categories,
            (SELECT status FROM subscriptions WHERE worker_id=w.id ORDER BY created_at DESC LIMIT 1) as sub_status
     FROM workers w JOIN users u ON w.user_id=u.id
     LEFT JOIN worker_categories wc ON w.id=wc.worker_id
     LEFT JOIN categories c ON wc.category_id=c.id
     WHERE w.is_approved=1 GROUP BY w.id ORDER BY w.avg_rating DESC LIMIT 50"
);

$recentBookings = DB::fetchAll(
    "SELECT b.*, cu.name as customer_name, wu.name as worker_name, c.name as category_name
     FROM bookings b
     JOIN users cu ON b.customer_id=cu.id
     JOIN workers wr ON b.worker_id=wr.id
     JOIN users wu ON wr.user_id=wu.id
     JOIN categories c ON b.category_id=c.id
     ORDER BY b.created_at DESC LIMIT 20"
);

// Specific page data queries
if ($page === 'bookings') {
    $allBookings = DB::fetchAll(
        "SELECT b.*, cu.name as customer_name, cu.email as customer_email, cu.phone as customer_phone,
                wu.name as worker_name, wu.phone as worker_phone, c.name as category_name
         FROM bookings b
         JOIN users cu ON b.customer_id = cu.id
         JOIN workers wr ON b.worker_id = wr.id
         JOIN users wu ON wr.user_id = wu.id
         JOIN categories c ON b.category_id = c.id
         ORDER BY b.created_at DESC"
    );
} elseif ($page === 'categories') {
    $allCategories = DB::fetchAll(
        "SELECT c.*, COUNT(wc.worker_id) as total_workers
         FROM categories c
         LEFT JOIN worker_categories wc ON c.id = wc.category_id
         GROUP BY c.id
         ORDER BY c.sort_order ASC, c.name ASC"
    );
} elseif ($page === 'subscriptions') {
    $allSubscriptions = DB::fetchAll(
        "SELECT s.*, u.name as worker_name, u.email as worker_email, u.phone as worker_phone,
                w.working_area, w.district
         FROM subscriptions s
         JOIN workers w ON s.worker_id = w.id
         JOIN users u ON w.user_id = u.id
         ORDER BY s.created_at DESC"
    );
} elseif ($page === 'users') {
    $allCustomers = DB::fetchAll(
        "SELECT u.*, COUNT(b.id) as total_bookings,
                COALESCE(SUM(CASE WHEN b.pay_status = 'paid' THEN b.total_amount ELSE 0 END), 0) as total_spent
         FROM users u
         LEFT JOIN bookings b ON u.id = b.customer_id
         WHERE u.role = 'customer'
         GROUP BY u.id
         ORDER BY u.created_at DESC"
    );
}

$adminMsg = $_SESSION['admin_msg'] ?? null;
unset($_SESSION['admin_msg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .page-title { font-weight: 900; font-size: 1.6rem; color: #0F172A; margin-bottom: 20px; }
    .badge-status { padding: 4px 10px; border-radius: 30px; font-weight: 700; font-size: 0.75rem; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-esheba sticky-top">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="../index.php" class="brand d-flex align-items-center gap-2 text-decoration-none py-1">
      <img src="../images/logo.svg" alt="E-Sheba Logo" class="brand-logo-img" style="height:38px">
      <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size:.65rem;letter-spacing:.5px">ADMIN</span>
    </a>
    <div class="d-flex align-items-center gap-3">
      <span class="text-muted" style="font-size:.88rem"><i class="fa fa-user-shield me-1 text-primary"></i>Super Administrator</span>
      <a href="../api/logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</a>
    </div>
  </div>
</nav>

<div class="dashboard-layout">

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="p-4 border-bottom" style="border-color:rgba(255,255,255,.1)!important">
      <div class="text-white fw-700">Admin Control Center</div>
      <div style="font-size:.72rem;color:rgba(255,255,255,.5)">Platform Management</div>
    </div>
    <div class="sidebar-menu">
      <a href="dashboard.php" class="sidebar-item <?= $page==='dashboard'?'active':'' ?>"><i class="fa fa-tachometer-alt"></i> Overview</a>
      <a href="dashboard.php?page=pending" class="sidebar-item <?= $page==='pending'?'active':'' ?>"><i class="fa fa-user-clock"></i> Pending Approvals <span class="ms-auto badge bg-danger"><?= $pendingApproval ?></span></a>
      <a href="dashboard.php?page=workers" class="sidebar-item <?= $page==='workers'?'active':'' ?>"><i class="fa fa-hard-hat"></i> All Workers</a>
      <a href="dashboard.php?page=bookings" class="sidebar-item <?= $page==='bookings'?'active':'' ?>"><i class="fa fa-calendar-check"></i> Bookings <span class="ms-auto badge bg-primary"><?= $totalBookings ?></span></a>
      <a href="dashboard.php?page=categories" class="sidebar-item <?= $page==='categories'?'active':'' ?>"><i class="fa fa-th-large"></i> Categories</a>
      <a href="dashboard.php?page=subscriptions" class="sidebar-item <?= $page==='subscriptions'?'active':'' ?>"><i class="fa fa-crown"></i> Subscriptions <span class="ms-auto badge bg-warning text-dark"><?= $activeSubscriptions ?></span></a>
      <a href="dashboard.php?page=users" class="sidebar-item <?= $page==='users'?'active':'' ?>"><i class="fa fa-users"></i> Customers <span class="ms-auto badge bg-secondary"><?= $totalUsers ?></span></a>
      <div class="sidebar-label">Quick Links</div>
      <a href="../index.php" class="sidebar-item"><i class="fa fa-globe"></i> View Website</a>
      <a href="../api/logout.php" class="sidebar-item"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>

  <!-- Main Content Area -->
  <div class="dashboard-content">

    <?php if ($adminMsg): ?>
    <div class="alert alert-<?= $adminMsg['type'] === 'success' ? 'success' : 'danger' ?> mb-4 shadow-sm" style="border-radius:12px">
      <i class="fa fa-info-circle me-2"></i><?= htmlspecialchars($adminMsg['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- ══════════════════ 1. OVERVIEW ══════════════════ -->
    <?php if ($page === 'dashboard'): ?>
    <h1 class="page-title">System Overview</h1>
    <div class="row g-3 mb-4">
      <?php $adminStats = [
        ['fa-users','Total Customers',$totalUsers,'#1a73e8','linear-gradient(135deg,#e8f0fe,#d0e3ff)'],
        ['fa-hard-hat','Service Providers',$totalWorkers,'#34a853','linear-gradient(135deg,#e6f4ea,#ceead6)'],
        ['fa-user-clock','Pending Approval',$pendingApproval,'#ea4335','linear-gradient(135deg,#fce8e6,#f5c6c2)'],
        ['fa-calendar-check','Total Bookings',$totalBookings,'#fbbc05','linear-gradient(135deg,#fef9e0,#fde68a)'],
        ['fa-crown','Active Subscriptions',$activeSubscriptions,'#8b5cf6','linear-gradient(135deg,#f3e8ff,#ddd6fe)'],
        ['fa-wallet','Subscription Revenue','৳'.number_format($revenue),'#06b6d4','linear-gradient(135deg,#ecfeff,#cffafe)'],
      ]; foreach($adminStats as [$icon,$label,$val,$color,$bg]): ?>
      <div class="col-6 col-xl-4">
        <div class="stat-card">
          <div class="stat-icon" style="background:<?= $bg ?>;color:<?= $color ?>"><i class="fa <?= $icon ?>"></i></div>
          <div class="stat-info"><div class="num"><?= $val ?></div><div class="label"><?= $label ?></div></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Recent Bookings Table -->
    <div class="stat-card flex-column align-items-stretch p-0 shadow-sm" style="border-radius:var(--radius-md);overflow:hidden">
      <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="fw-700 mb-0">Recent Platform Activity</h5>
        <a href="dashboard.php?page=bookings" class="btn btn-sm btn-outline-primary rounded-pill">View All Bookings</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background:#f8f9ff"><tr>
            <th class="ps-4">Customer</th><th>Worker</th><th>Service</th><th>Status</th><th>Payment</th><th>Amount</th><th>Date</th>
          </tr></thead>
          <tbody>
            <?php foreach(array_slice($recentBookings, 0, 8) as $b): 
              $bc = match($b['status']){'completed'=>'success','accepted'=>'primary','in_progress'=>'info','cancelled'=>'danger',default=>'warning'};
            ?>
            <tr>
              <td class="ps-4 py-3" style="font-size:.88rem;font-weight:600"><?= htmlspecialchars($b['customer_name']) ?></td>
              <td style="font-size:.88rem"><?= htmlspecialchars($b['worker_name']) ?></td>
              <td style="font-size:.85rem"><?= htmlspecialchars($b['category_name']) ?></td>
              <td><span class="badge bg-<?= $bc ?>"><?= ucfirst(str_replace('_',' ',$b['status'])) ?></span></td>
              <td>
                <span class="badge <?= $b['pay_status']==='paid'?'bg-success':'bg-secondary' ?>" style="font-size:.72rem">
                  <?= strtoupper($b['pay_method'] ?? 'N/A') ?>: <?= ucfirst($b['pay_status']) ?>
                </span>
              </td>
              <td style="font-size:.85rem;font-weight:700">৳<?= number_format($b['total_amount'] ?? 0) ?></td>
              <td style="font-size:.8rem;color:var(--muted)"><?= date('d M Y, h:i A', strtotime($b['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════════════════ 2. PENDING APPROVALS ══════════════════ -->
    <?php elseif ($page === 'pending'): ?>
    <h1 class="page-title">⏳ Pending Provider Approvals (<?= count($pendingWorkers) ?>)</h1>
    <?php if (empty($pendingWorkers)): ?>
    <div class="empty-state"><div class="empty-icon"><i class="fa fa-check-circle text-success"></i></div><h5>All caught up!</h5><p>No workers awaiting approval.</p></div>
    <?php else: ?>
    <div class="row g-3">
      <?php foreach ($pendingWorkers as $w): ?>
      <div class="col-lg-6">
        <div class="p-4 rounded-3 bg-white border shadow-sm">
          <div class="d-flex align-items-start gap-3 mb-3">
            <div class="chat-avatar" style="width:48px;height:48px;font-size:1rem;flex-shrink:0"><?= strtoupper($w['name'][0]) ?></div>
            <div class="flex-grow-1">
              <div class="fw-700"><?= htmlspecialchars($w['name']) ?></div>
              <div style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($w['email']) ?></div>
              <div style="font-size:.82rem;color:var(--muted)"><i class="fa fa-phone me-1"></i><?= htmlspecialchars($w['phone'] ?? '—') ?></div>
            </div>
            <span class="badge bg-warning text-dark">Pending Review</span>
          </div>
          <div class="row g-2 mb-3" style="font-size:.82rem">
            <div class="col-6"><strong>Area:</strong> <?= htmlspecialchars($w['thana'].', '.$w['district']) ?></div>
            <div class="col-6"><strong>Rate:</strong> ৳<?= number_format($w['price_per_hour']) ?>/hr</div>
            <div class="col-6"><strong>Experience:</strong> <?= $w['experience_years'] ?> yrs</div>
            <div class="col-6"><strong>NID:</strong> <?= $w['nid_number'] ? htmlspecialchars($w['nid_number']) : '✗ Not provided' ?></div>
            <div class="col-12"><strong>Services:</strong> <?= htmlspecialchars($w['categories'] ?? '—') ?></div>
          </div>
          <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2 rounded-pill" onclick="viewNid('<?= htmlspecialchars(addslashes($w['name'])) ?>', '<?= htmlspecialchars(addslashes($w['nid_number'])) ?>', '<?= htmlspecialchars(addslashes($w['nid_image_path'] ?? 'sample_nid.svg')) ?>')">
            <i class="fa fa-id-card me-1"></i>Inspect NID Document
          </button>
          <form method="POST" class="d-flex gap-2">
            <input type="hidden" name="worker_id" value="<?= $w['id'] ?>">
            <button type="submit" name="action" value="approve" class="btn btn-success flex-grow-1 fw-600"><i class="fa fa-check me-1"></i>Approve Worker</button>
            <button type="submit" name="action" value="reject" class="btn btn-outline-danger flex-grow-1" onclick="return confirm('Reject this worker?')"><i class="fa fa-times me-1"></i>Reject</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ══════════════════ 3. ALL WORKERS ══════════════════ -->
    <?php elseif ($page === 'workers'): ?>
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
      <h1 class="page-title mb-0">All Service Providers (<?= count($approvedWorkers) ?>)</h1>
      <input type="text" placeholder="Search provider..." class="form-control" style="max-width:240px;border-radius:50px" oninput="filterTable(this,'workersTable')">
    </div>
    <div class="stat-card flex-column align-items-stretch p-0 shadow-sm" style="border-radius:var(--radius-md);overflow:hidden">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="workersTable">
          <thead style="background:#f8f9ff"><tr>
            <th class="ps-4">Provider</th><th>Email</th><th>Area</th><th>Rating</th><th>Subscription</th><th>Action</th>
          </tr></thead>
          <tbody>
            <?php foreach ($approvedWorkers as $w): $subBadge = match($w['sub_status'] ?? ''){ 'active'=>'success', default=>'danger' }; ?>
            <tr>
              <td class="ps-4 py-3">
                <div class="d-flex align-items-center gap-2">
                  <div class="chat-avatar" style="width:34px;height:34px;font-size:.72rem"><?= strtoupper($w['name'][0]) ?></div>
                  <div>
                    <div class="fw-700" style="font-size:.88rem"><?= htmlspecialchars($w['name']) ?></div>
                    <div style="font-size:.75rem;color:var(--muted)"><?= htmlspecialchars($w['categories'] ?? 'Technician') ?></div>
                  </div>
                </div>
              </td>
              <td style="font-size:.85rem"><?= htmlspecialchars($w['email']) ?></td>
              <td style="font-size:.82rem"><?= htmlspecialchars($w['thana'].', '.$w['district']) ?></td>
              <td><span class="text-warning">★</span> <?= number_format($w['avg_rating'], 1) ?> <small class="text-muted">(<?= $w['total_reviews'] ?>)</small></td>
              <td><span class="badge bg-<?= $subBadge ?>"><?= ucfirst($w['sub_status'] ?? 'inactive') ?></span></td>
              <td>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="worker_id" value="<?= $w['id'] ?>">
                  <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger py-1 px-3 rounded-pill" onclick="return confirm('Deactivate this worker profile?')">Deactivate</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════════════════ 4. ALL BOOKINGS ══════════════════ -->
    <?php elseif ($page === 'bookings'): ?>
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
      <div>
        <h1 class="page-title mb-0">Bookings Registry (<?= count($allBookings) ?>)</h1>
        <small class="text-muted">Monitor all customer orders, descriptions, and payments</small>
      </div>
      <input type="text" placeholder="Search customer, worker, service..." class="form-control" style="max-width:280px;border-radius:50px" oninput="filterTable(this,'allBookingsTable')">
    </div>
    <div class="stat-card flex-column align-items-stretch p-0 shadow-sm" style="border-radius:var(--radius-md);overflow:hidden">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="allBookingsTable">
          <thead style="background:#f8f9ff"><tr>
            <th class="ps-4">ID</th><th>Customer</th><th>Provider</th><th>Service & Note</th><th>Scheduled</th><th>Amount & Pay</th><th>Status</th>
          </tr></thead>
          <tbody>
            <?php if (empty($allBookings)): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted">No bookings found in system.</td></tr>
            <?php else: foreach ($allBookings as $b): 
              $bc = match($b['status']){'completed'=>'success','accepted'=>'primary','in_progress'=>'info','cancelled'=>'danger',default=>'warning'};
            ?>
            <tr>
              <td class="ps-4 py-3 fw-700">#<?= $b['id'] ?></td>
              <td>
                <div class="fw-700" style="font-size:.88rem"><?= htmlspecialchars($b['customer_name']) ?></div>
                <div style="font-size:.75rem;color:var(--muted)"><i class="fa fa-phone me-1"></i><?= htmlspecialchars($b['customer_phone'] ?? 'N/A') ?></div>
              </td>
              <td>
                <div class="fw-700" style="font-size:.88rem"><?= htmlspecialchars($b['worker_name']) ?></div>
                <div style="font-size:.75rem;color:var(--muted)"><i class="fa fa-phone me-1"></i><?= htmlspecialchars($b['worker_phone'] ?? 'N/A') ?></div>
              </td>
              <td>
                <span class="badge bg-primary-subtle text-primary mb-1"><?= htmlspecialchars($b['category_name']) ?></span>
                <?php if ($b['description']): ?>
                  <div class="text-muted text-truncate" style="max-width:220px;font-size:.78rem;" title="<?= htmlspecialchars($b['description']) ?>">
                    <i class="fa fa-comment-alt me-1"></i><?= htmlspecialchars($b['description']) ?>
                  </div>
                <?php endif; ?>
                <?php if ($b['address']): ?>
                  <div style="font-size:.75rem;color:#64748b"><i class="fa fa-map-marker-alt me-1 text-danger"></i><?= htmlspecialchars($b['address']) ?></div>
                <?php endif; ?>
              </td>
              <td style="font-size:.82rem;color:var(--muted)"><?= $b['scheduled_at'] ? date('d M, h:i A', strtotime($b['scheduled_at'])) : '—' ?></td>
              <td>
                <div class="fw-800 text-dark">৳<?= number_format($b['total_amount'] ?? 0) ?></div>
                <span class="badge <?= $b['pay_status']==='paid'?'bg-success':($b['pay_status']==='cash_pending'?'bg-warning text-dark':'bg-secondary') ?>" style="font-size:.7rem">
                  <?= strtoupper($b['pay_method'] ?? 'Cash') ?>: <?= ucfirst(str_replace('_',' ',$b['pay_status'])) ?>
                </span>
              </td>
              <td><span class="badge bg-<?= $bc ?>"><?= ucfirst(str_replace('_',' ',$b['status'])) ?></span></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════════════════ 5. CATEGORIES ══════════════════ -->
    <?php elseif ($page === 'categories'): ?>
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
      <div>
        <h1 class="page-title mb-0">Service Categories (<?= count($allCategories) ?>)</h1>
        <small class="text-muted">Manage all trade categories and offerings</small>
      </div>
      <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-700" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fa fa-plus me-1"></i> Add Category
      </button>
    </div>
    <div class="stat-card flex-column align-items-stretch p-0 shadow-sm" style="border-radius:var(--radius-md);overflow:hidden">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background:#f8f9ff"><tr>
            <th class="ps-4">Icon</th><th>Category Name</th><th>Description</th><th>Registered Providers</th><th>Status</th><th>Action</th>
          </tr></thead>
          <tbody>
            <?php foreach ($allCategories as $c): ?>
            <tr>
              <td class="ps-4 py-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-light border text-primary" style="width:36px;height:36px;font-size:16px">
                  <i class="fa <?= htmlspecialchars($c['icon'] ?: 'fa-tools') ?>"></i>
                </div>
              </td>
              <td class="fw-700" style="font-size:.9rem"><?= htmlspecialchars($c['name']) ?></td>
              <td class="text-muted" style="font-size:.82rem;max-width:300px"><?= htmlspecialchars($c['description'] ?: '—') ?></td>
              <td><span class="badge bg-info-subtle text-info border border-info fw-700"><?= $c['total_workers'] ?> Providers</span></td>
              <td>
                <span class="badge bg-<?= $c['is_active'] ? 'success' : 'secondary' ?>">
                  <?= $c['is_active'] ? 'Active' : 'Disabled' ?>
                </span>
              </td>
              <td>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                  <button type="submit" name="action" value="toggle_category" class="btn btn-sm <?= $c['is_active'] ? 'btn-outline-secondary' : 'btn-outline-success' ?> rounded-pill py-1 px-3">
                    <?= $c['is_active'] ? 'Deactivate' : 'Activate' ?>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════════════════ 6. SUBSCRIPTIONS ══════════════════ -->
    <?php elseif ($page === 'subscriptions'): ?>
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
      <div>
        <h1 class="page-title mb-0">Worker Subscriptions (<?= count($allSubscriptions) ?>)</h1>
        <small class="text-muted">Track monthly ৳399/mo technician memberships & SSLCommerz transactions</small>
      </div>
      <input type="text" placeholder="Search subscription..." class="form-control" style="max-width:240px;border-radius:50px" oninput="filterTable(this,'subsTable')">
    </div>
    <div class="stat-card flex-column align-items-stretch p-0 shadow-sm" style="border-radius:var(--radius-md);overflow:hidden">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="subsTable">
          <thead style="background:#f8f9ff"><tr>
            <th class="ps-4">Provider</th><th>Plan & Amount</th><th>Payment Method</th><th>Txn ID</th><th>Valid From</th><th>Expires</th><th>Status</th>
          </tr></thead>
          <tbody>
            <?php if (empty($allSubscriptions)): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted">No subscriptions found.</td></tr>
            <?php else: foreach ($allSubscriptions as $s): ?>
            <tr>
              <td class="ps-4 py-3">
                <div class="fw-700" style="font-size:.88rem"><?= htmlspecialchars($s['worker_name']) ?></div>
                <div style="font-size:.75rem;color:var(--muted)"><?= htmlspecialchars($s['district']) ?></div>
              </td>
              <td>
                <span class="badge bg-warning text-dark"><i class="fa fa-crown me-1"></i>PRO Plan</span>
                <span class="fw-700 ms-1">৳<?= number_format($s['amount']) ?></span>
              </td>
              <td style="font-size:.85rem;text-transform:uppercase"><?= htmlspecialchars($s['payment_method']) ?></td>
              <td style="font-size:.8rem;font-family:monospace;color:var(--muted)"><?= htmlspecialchars($s['transaction_id'] ?? 'MANUAL_ACTIVATION') ?></td>
              <td style="font-size:.82rem"><?= $s['starts_at'] ? date('d M Y', strtotime($s['starts_at'])) : '—' ?></td>
              <td style="font-size:.82rem"><?= $s['expires_at'] ? date('d M Y', strtotime($s['expires_at'])) : '—' ?></td>
              <td>
                <span class="badge bg-<?= $s['status'] === 'active' ? 'success' : 'danger' ?>">
                  <?= ucfirst($s['status']) ?>
                </span>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ══════════════════ 7. CUSTOMERS (USERS) ══════════════════ -->
    <?php elseif ($page === 'users'): ?>
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
      <div>
        <h1 class="page-title mb-0">Registered Customers (<?= count($allCustomers) ?>)</h1>
        <small class="text-muted">All customer accounts, contact details, and bookings</small>
      </div>
      <input type="text" placeholder="Search customer..." class="form-control" style="max-width:240px;border-radius:50px" oninput="filterTable(this,'customersTable')">
    </div>
    <div class="stat-card flex-column align-items-stretch p-0 shadow-sm" style="border-radius:var(--radius-md);overflow:hidden">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="customersTable">
          <thead style="background:#f8f9ff"><tr>
            <th class="ps-4">Customer</th><th>Email</th><th>Phone</th><th>Total Bookings</th><th>Total Spent</th><th>Joined</th><th>Status</th>
          </tr></thead>
          <tbody>
            <?php if (empty($allCustomers)): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted">No customers registered yet.</td></tr>
            <?php else: foreach ($allCustomers as $u): ?>
            <tr>
              <td class="ps-4 py-3">
                <div class="d-flex align-items-center gap-2">
                  <div class="chat-avatar" style="width:34px;height:34px;font-size:.75rem;background:#E0E7FF;color:#4338CA"><?= strtoupper($u['name'][0]) ?></div>
                  <div class="fw-700" style="font-size:.88rem"><?= htmlspecialchars($u['name']) ?></div>
                </div>
              </td>
              <td style="font-size:.85rem"><?= htmlspecialchars($u['email']) ?></td>
              <td style="font-size:.85rem"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
              <td><span class="badge bg-primary-subtle text-primary fw-700"><?= $u['total_bookings'] ?> Bookings</span></td>
              <td style="font-weight:700;font-size:.88rem">৳<?= number_format($u['total_spent']) ?></td>
              <td style="font-size:.82rem;color:var(--muted)"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
              <td><span class="badge bg-success">Active</span></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- NID Preview Modal -->
<div class="modal fade" id="nidModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-800" id="nidModalName">NID Card Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <div class="mb-3">
          <span class="text-muted" style="font-size:.85rem">Provided NID Number: </span>
          <code class="fw-700 fs-6 text-primary" id="nidModalNumber"></code>
        </div>
        <img id="nidModalImg" src="" alt="NID Card" class="img-fluid rounded-3 border shadow-sm" style="max-height:280px;width:100%;object-fit:contain;background:#f8f9fa">
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none">
      <form method="POST">
        <input type="hidden" name="action" value="add_category">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-800">Add New Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.85rem">Category Name *</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. CCTV & Security" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.85rem">FontAwesome Icon Class</label>
            <input type="text" name="icon" class="form-control" value="fa-tools" placeholder="e.g. fa-video, fa-wrench">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.85rem">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Brief overview of services included in this category..."></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4 fw-700">Add Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../js/app.js"></script>
<script>
function filterTable(input, tableId) {
  const filter = input.value.toLowerCase();
  document.querySelectorAll(`#${tableId} tbody tr`).forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
  });
}

function viewNid(name, number, img) {
  document.getElementById('nidModalName').innerText = name + ' — NID Verification';
  document.getElementById('nidModalNumber').innerText = number || 'N/A';
  document.getElementById('nidModalImg').src = '../uploads/nid/' + (img || 'sample_nid.svg');
  new bootstrap.Modal(document.getElementById('nidModal')).show();
}
</script>
</body>
</html>
