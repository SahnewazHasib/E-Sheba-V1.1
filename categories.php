<?php
// ============================================================
// Service Categories Directory — E-Sheba
// ============================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';
Auth::start();

$categories = DB::fetchAll(
    "SELECT c.*, COUNT(DISTINCT wc.worker_id) as worker_count
     FROM categories c
     LEFT JOIN worker_categories wc ON c.id = wc.category_id
     LEFT JOIN workers w ON wc.worker_id = w.id AND w.is_approved = 1
     WHERE c.is_active = 1
     GROUP BY c.id
     ORDER BY c.sort_order"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Service Categories — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .cat-box {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      padding: 24px 20px;
      text-align: center;
      text-decoration: none;
      display: block;
      transition: all .25s ease;
      color: var(--dark);
    }
    .cat-box:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px rgba(0,0,0,.07);
      border-color: var(--primary);
      color: var(--primary);
    }
    .cat-icon-wrap {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      background: #f0f7ff;
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      margin: 0 auto 16px;
      transition: all .25s ease;
    }
    .cat-box:hover .cat-icon-wrap {
      background: var(--primary);
      color: #fff;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-esheba">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="brand text-decoration-none py-1"><img src="images/logo.svg" alt="E-Sheba Logo" class="brand-logo-img"></a>
    <div class="d-flex align-items-center gap-2">
      <a href="workers.php" class="btn btn-outline-primary rounded-pill px-3" style="font-size:.85rem">
        <i class="fa fa-search me-1"></i>Find Providers
      </a>
      <?php if (Auth::isLoggedIn()): ?>
        <?php $dash = match(Auth::role()) { 'worker'=>'worker/dashboard.php', 'admin'=>'admin/dashboard.php', default=>'customer/dashboard.php' }; ?>
        <a href="<?= $dash ?>" class="btn btn-primary rounded-pill px-3" style="font-size:.85rem">Dashboard</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary rounded-pill px-3" style="font-size:.85rem">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="text-center mb-5">
    <span class="badge bg-primary-subtle text-primary fw-700 px-3 py-2 rounded-pill mb-2">25 VERIFIED CATEGORIES</span>
    <h2 class="fw-800 text-dark">Find the Right Service for Your Home</h2>
    <p class="text-muted" style="max-width:550px;margin:0 auto;font-size:1rem">
      Connect instantly with pre-screened plumbers, electricians, mechanics, and technicians across Bangladesh.
    </p>
  </div>

  <div class="row g-3 g-md-4">
    <?php
    $catImgMap = [
      'Plumber' => 'images/plumber.jpg',
      'Electrician' => 'images/electrician.jpg',
      'Mechanic' => 'images/mechanic.jpg',
      'AC Repair' => 'images/ac_repair.jpg',
      'Cleaner' => 'images/cleaner.jpg',
      'Painter' => 'images/painter.jpg',
      'Carpenter' => 'images/carpenter.jpg',
    ];
    $cIdx = 0;
    foreach ($categories as $cat): 
      $cIdx++;
      $img = $catImgMap[$cat['name']] ?? null;
    ?>
      <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?= min(350, $cIdx * 40) ?>">
        <a href="workers.php?category_id=<?= $cat['id'] ?>" class="cat-box hover-lift">
          <div class="cat-icon-wrap">
            <i class="fa <?= $cat['icon'] ?? 'fa-wrench' ?>"></i>
          </div>
          <div class="fw-700 mb-1" style="font-size:1rem"><?= htmlspecialchars($cat['name']) ?></div>
          <div class="text-muted" style="font-size:.78rem">
            <?= $cat['worker_count'] ?> <?= $cat['worker_count'] == 1 ? 'Provider' : 'Providers' ?> Available
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="p-4 rounded-4 mt-5 text-center text-white" style="background:linear-gradient(135deg, #1a73e8, #0d47a1)">
    <h4 class="fw-800 mb-2">Are you a skilled technician or worker?</h4>
    <p class="mb-3 opacity-85">Join over 2,400 registered service givers and start receiving job requests today.</p>
    <a href="register.php?role=worker" class="btn btn-warning rounded-pill px-4 fw-800">
      Join as Service Provider →
    </a>
  </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({duration:600,once:true});</script>
</body>
</html>
