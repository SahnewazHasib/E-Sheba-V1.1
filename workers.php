<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Find Service Providers — E-Sheba</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
Auth::start();
$loggedIn = Auth::isLoggedIn();

$q          = trim($_GET['q'] ?? '');
$district   = trim($_GET['district'] ?? '');
$categoryId = (int)($_GET['category_id'] ?? 0);
$category   = trim($_GET['category'] ?? '');
$sort       = $_GET['sort'] ?? 'rating';
$minPrice   = (float)($_GET['min_price'] ?? 0);
$maxPrice   = (float)($_GET['max_price'] ?? 9999);
$page       = max(1,(int)($_GET['page']??1));
$perPage    = 12;
$offset     = ($page-1)*$perPage;

// Build query
$where = ["w.is_approved=1","s.status='active'"];
$params= [];
if ($q) { $where[] = "(u.name LIKE ? OR c.name LIKE ? OR w.bio LIKE ?)"; $params=array_merge($params,["%$q%","%$q%","%$q%"]); }
if ($district) { $where[] = "w.district=?"; $params[]=$district; }
if ($categoryId) { $where[] = "wc.category_id=?"; $params[]=$categoryId; }
elseif ($category) { $where[] = "c.name LIKE ?"; $params[]="%$category%"; }
if ($minPrice>0) { $where[] = "w.price_per_hour >= ?"; $params[]=$minPrice; }
if ($maxPrice<9999) { $where[] = "w.price_per_hour <= ?"; $params[]=$maxPrice; }

$orderBy = match($sort) { 'price_asc'=>'w.price_per_hour ASC','price_desc'=>'w.price_per_hour DESC','new'=>'w.created_at DESC', default=>'w.avg_rating DESC, w.total_reviews DESC' };

$whereStr = $where ? 'WHERE '.implode(' AND ',$where) : '';
$total = DB::fetchOne("SELECT COUNT(DISTINCT w.id) c FROM workers w JOIN users u ON w.user_id=u.id LEFT JOIN worker_categories wc ON w.id=wc.worker_id LEFT JOIN categories c ON wc.category_id=c.id JOIN subscriptions s ON s.worker_id=w.id $whereStr", $params)['c'];
$workers = DB::fetchAll(
    "SELECT w.*,u.name,u.avatar,u.email,GROUP_CONCAT(DISTINCT c.name ORDER BY c.sort_order SEPARATOR ', ') as categories,GROUP_CONCAT(DISTINCT c.icon ORDER BY c.sort_order SEPARATOR ',') as icons
     FROM workers w JOIN users u ON w.user_id=u.id
     LEFT JOIN worker_categories wc ON w.id=wc.worker_id
     LEFT JOIN categories c ON wc.category_id=c.id
     JOIN subscriptions s ON s.worker_id=w.id
     $whereStr GROUP BY w.id ORDER BY $orderBy LIMIT $perPage OFFSET $offset",
    $params
);

$allCategories = DB::fetchAll("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order");
$districts = ['Dhaka','Chittagong','Sylhet','Rajshahi','Khulna','Barishal','Rangpur','Mymensingh','Comilla','Narayanganj','Gazipur'];
$pages = ceil($total/$perPage);
?>

<!-- Navbar -->
<nav class="navbar-esheba">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">
    <a href="index.php" class="brand text-decoration-none">🔧 E-Sheba</a>
    <!-- Search bar in nav -->
    <form action="workers.php" method="GET" class="d-none d-md-flex align-items-center gap-2 flex-grow-1" style="max-width:420px;margin:0 20px">
      <div class="position-relative flex-grow-1">
        <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px"></i>
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control rounded-pill" style="padding-left:36px;font-size:.88rem" placeholder="Search providers...">
      </div>
      <button type="submit" class="btn btn-primary rounded-pill px-3">Go</button>
    </form>
    <div class="d-flex gap-2">
      <?php if($loggedIn): ?>
      <a href="chat.php" class="btn btn-link text-muted"><i class="fa fa-comment-dots"></i></a>
      <?php $dashLink=match(Auth::role()){'worker'=>'worker/dashboard.php','admin'=>'admin/dashboard.php',default=>'customer/dashboard.php'}; ?>
      <a href="<?= $dashLink ?>" class="btn btn-outline-primary rounded-pill px-3" style="font-size:.85rem">Dashboard</a>
      <?php else: ?>
      <a href="login.php" class="btn-login">Login</a>
      <a href="register.php" class="btn-register">Join Free</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container-fluid px-0">
  <div class="row g-0">

    <!-- ══ Sidebar Filters ══ -->
    <div class="col-lg-3 col-xl-2 d-none d-lg-block" style="background:#fff;border-right:1px solid var(--border);min-height:calc(100vh - 72px);padding:24px 20px;position:sticky;top:72px;overflow-y:auto;height:calc(100vh - 72px)">
      <form action="workers.php" method="GET" id="filterForm">
        <?php if($q): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>

        <div class="fw-700 mb-3" style="font-size:.9rem;color:var(--dark)"><i class="fa fa-filter me-2 text-primary"></i>Filters</div>

        <!-- Category -->
        <div class="mb-4">
          <div class="fw-600 mb-2" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">Category</div>
          <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach($allCategories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $categoryId==$cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- District -->
        <div class="mb-4">
          <div class="fw-600 mb-2" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">District</div>
          <select name="district" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All Districts</option>
            <?php foreach($districts as $d): ?>
            <option value="<?= $d ?>" <?= $district===$d?'selected':'' ?>><?= $d ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Price Range -->
        <div class="mb-4">
          <div class="fw-600 mb-2" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">Price/hr (৳)</div>
          <div class="d-flex gap-2">
            <input type="number" name="min_price" value="<?= $minPrice?:'' ?>" placeholder="Min" class="form-control form-control-sm" style="width:70px">
            <input type="number" name="max_price" value="<?= $maxPrice<9999?$maxPrice:'' ?>" placeholder="Max" class="form-control form-control-sm" style="width:70px">
          </div>
        </div>

        <!-- Sort -->
        <div class="mb-4">
          <div class="fw-600 mb-2" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.5px;color:var(--muted)">Sort By</div>
          <div class="d-flex flex-column gap-1">
            <?php foreach(['rating'=>'⭐ Top Rated','price_asc'=>'৳ Price: Low','price_desc'=>'৳ Price: High','new'=>'🆕 Newest'] as $val=>$label): ?>
            <label class="d-flex align-items-center gap-2 cursor-pointer" style="font-size:.85rem">
              <input type="radio" name="sort" value="<?= $val ?>" <?= $sort===$val?'checked':'' ?> onchange="this.form.submit()"> <?= $label ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Availability -->
        <div class="mb-4">
          <label class="d-flex align-items-center gap-2 cursor-pointer" style="font-size:.88rem">
            <input type="checkbox" name="available" <?= isset($_GET['available'])?'checked':'' ?> onchange="this.form.submit()">
            <span>Available Now Only</span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary w-100 rounded-pill btn-sm">Apply Filters</button>
        <a href="workers.php" class="btn btn-link w-100 btn-sm mt-1 text-muted">Clear All</a>
      </form>
    </div>

    <!-- ══ Results ══ -->
    <div class="col-lg-9 col-xl-10 p-4">

      <!-- Results header -->
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
          <h2 style="font-size:1.3rem;font-weight:800;margin:0">
            <?php if($q): ?>Results for "<em><?= htmlspecialchars($q) ?></em>"
            <?php elseif($categoryId||$category): ?><?= htmlspecialchars($category) ?> Providers
            <?php else: ?>All Service Providers<?php endif; ?>
          </h2>
          <p class="text-muted mb-0" style="font-size:.85rem"><?= number_format($total) ?> providers found <?= $district?"in $district":'' ?></p>
        </div>
        <!-- Mobile filter -->
        <button class="btn btn-outline-primary rounded-pill d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
          <i class="fa fa-filter me-2"></i>Filters
        </button>
      </div>

      <?php if(empty($workers)): ?>
      <div class="empty-state">
        <div class="empty-icon"><i class="fa fa-search"></i></div>
        <h5>No providers found</h5>
        <p>Try different keywords, category or area.</p>
        <a href="workers.php" class="btn btn-primary rounded-pill mt-2">View All Providers</a>
      </div>
      <?php else: ?>

      <!-- Workers Grid -->
      <div class="row g-3" id="workersGrid">
        <?php
        $avatarFallback = ['images/worker_1.jpg','images/worker_2.jpg','images/worker_3.jpg','images/worker_4.jpg','images/worker_5.jpg'];
        $wCounter = 0;
        foreach($workers as $w):
          $initial = strtoupper(mb_substr($w['name'],0,1));
          $stars   = $w['avg_rating'];
          $catIcon = explode(',',$w['icons']??'fa-tools')[0];
          $wAvatar = !empty($w['avatar']) ? $w['avatar'] : $avatarFallback[$w['id'] % count($avatarFallback)];
          $wCounter++;
        ?>
        <div class="col-sm-6 col-xl-4" data-aos="fade-up" data-aos-delay="<?= min(350, $wCounter * 50) ?>">
          <div class="worker-card h-100">
            <span class="wc-badge <?= !$w['is_available']?'unavailable':'' ?>">
              <?= $w['is_available']?'● Available':'○ Busy' ?>
            </span>
            <div class="wc-img">
              <img src="<?= htmlspecialchars($wAvatar) ?>" alt="<?= htmlspecialchars($w['name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <div class="wc-body">
              <div class="wc-name"><?= htmlspecialchars($w['name']) ?></div>
              <div class="wc-category"><i class="fa <?= htmlspecialchars($catIcon) ?> me-1"></i><?= htmlspecialchars($w['categories']??'Service Provider') ?></div>
              <div class="wc-area"><i class="fa fa-map-marker-alt"></i><?= htmlspecialchars($w['thana'].', '.$w['district']) ?></div>
              <div class="wc-rating">
                <div class="wc-stars"><?= str_repeat('★',round($stars)).str_repeat('☆',5-round($stars)) ?></div>
                <span class="wc-rating-num"><?= number_format($stars,1) ?></span>
                <span class="wc-reviews">(<?= $w['total_reviews'] ?>)</span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <div class="wc-price">৳<?= number_format($w['price_per_hour']) ?><small>/hr</small></div>
                <span class="badge" style="background:var(--primary-light);color:var(--primary);font-size:.7rem"><?= $w['experience_years'] ?>yr exp</span>
              </div>
              <div class="wc-actions">
                <a href="worker-profile.php?id=<?= $w['id'] ?>" class="wc-btn wc-btn-primary text-decoration-none"><i class="fa fa-eye"></i> Profile</a>
                <?php if($loggedIn): ?>
                <a href="chat.php?to=<?= $w['user_id'] ?>" class="wc-btn wc-btn-outline text-decoration-none"><i class="fa fa-comment"></i> Chat</a>
                <button class="wc-btn wc-btn-call" onclick="initiateCall(<?= $w['user_id'] ?>, '<?= addslashes($w['name']) ?>')"><i class="fa fa-phone"></i></button>
                <?php else: ?>
                <a href="login.php" class="wc-btn wc-btn-outline text-decoration-none"><i class="fa fa-comment"></i> Chat</a>
                <a href="login.php" class="wc-btn wc-btn-call text-decoration-none"><i class="fa fa-phone"></i></a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if($pages>1): ?>
      <nav class="mt-4 d-flex justify-content-center">
        <ul class="pagination gap-1">
          <?php for($p=1;$p<=$pages;$p++): ?>
          <li class="page-item <?= $p===$page?'active':'' ?>">
            <a class="page-link rounded-2" href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>"><?= $p ?></a>
          </li>
          <?php endfor; ?>
        </ul>
      </nav>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Mobile Filters Offcanvas -->
<div class="offcanvas offcanvas-start" id="filterOffcanvas" style="max-width:280px">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title fw-700"><i class="fa fa-filter me-2"></i>Filters</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <!-- Same filter form for mobile -->
    <form action="workers.php" method="GET">
      <div class="mb-3"><label class="form-label fw-600 small">Category</label>
        <select name="category_id" class="form-select">
          <option value="">All</option>
          <?php foreach($allCategories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $categoryId==$cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3"><label class="form-label fw-600 small">District</label>
        <select name="district" class="form-select">
          <option value="">All</option>
          <?php foreach($districts as $d): ?><option value="<?= $d ?>" <?= $district===$d?'selected':'' ?>><?= $d ?></option><?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100">Apply</button>
    </form>
  </div>
</div>

<!-- Call Overlay -->
<div class="call-overlay" id="callOverlay">
  <div class="call-avatar" id="callAvatar"></div>
  <div class="call-name" id="callName"></div>
  <div class="call-status" id="callStatus">Calling...</div>
  <div class="call-controls">
    <button class="call-btn mute" id="muteBtn" onclick="toggleMute()"><i class="fa fa-microphone"></i></button>
    <button class="call-btn hangup" onclick="endCall()"><i class="fa fa-phone-slash"></i></button>
    <button class="call-btn speaker" onclick="toggleSpeaker()"><i class="fa fa-volume-up"></i></button>
  </div>
</div>
<div class="call-overlay" id="incomingCallOverlay">
  <div class="call-avatar" id="incomingCallAvatar"></div>
  <div class="call-name" id="incomingCallName"></div>
  <div class="call-status">Incoming Call...</div>
  <div class="call-controls">
    <button class="call-btn accept" onclick="acceptCall()"><i class="fa fa-phone"></i></button>
    <button class="call-btn hangup" onclick="declineCall()"><i class="fa fa-phone-slash"></i></button>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({duration:700,once:true});</script>
<script src="js/app.js"></script>
<script src="js/call.js"></script>
<?php if($loggedIn): ?>
<script>
const CHAT_CONFIG = {
  myId: <?= Auth::id() ?>,
  peerId: null, peerName: null,
  pusherKey: '<?= PUSHER_KEY ?>',
  pusherCluster: '<?= PUSHER_CLUSTER ?>',
  agoraAppId: '<?= AGORA_APP_ID ?>',
};
</script>
<?php endif; ?>
</body>
</html>
