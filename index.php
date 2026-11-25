<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
Auth::start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-Sheba — Bangladesh's #1 On-Demand Local Service Marketplace</title>
  <meta name="description" content="Connect with verified local electricians, plumbers, AC technicians, mechanics, and 20+ tradespeople anywhere in Bangladesh. Fast response, verified NID, fair rates.">

  <!-- PWA -->
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#1a73e8">
  <link rel="icon" type="image/svg+xml" href="images/logo.svg">

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome 6.5 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

  <!-- AOS Animation Library -->
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ══════════════════ NAVBAR ══════════════════ -->
<nav class="navbar-esheba sticky-top">
  <div class="container d-flex align-items-center justify-content-between flex-wrap">

    <!-- Brand Logo -->
    <a href="index.php" class="brand d-flex align-items-center gap-2 text-decoration-none py-1">
      <img src="images/logo.svg" alt="E-Sheba Logo" class="brand-logo-img">
    </a>

    <!-- Desktop Nav Links -->
    <div class="d-none d-md-flex align-items-center gap-1">
      <a href="categories.php" class="nav-link"><i class="fa fa-th-large me-1 text-primary"></i>All Services</a>
      <a href="workers.php" class="nav-link"><i class="fa fa-user-check me-1 text-primary"></i>Find Providers</a>
      <a href="how-it-works.php" class="nav-link"><i class="fa fa-lightbulb me-1 text-primary"></i>How It Works</a>
      <a href="about.php" class="nav-link">About Us</a>
      <a href="contact.php" class="nav-link">Help</a>
    </div>

    <!-- Auth / Profile Buttons -->
    <div class="d-flex align-items-center gap-2">
      <?php
        if (Auth::isLoggedIn()):
          $role = Auth::role();
          $dashLink = match($role) { 'worker'=>'worker/dashboard.php','admin'=>'admin/dashboard.php', default=>'customer/dashboard.php' };
      ?>
        <!-- Notifications -->
        <a href="notifications.php" class="btn btn-link position-relative text-muted p-2" title="Notifications">
          <i class="fa fa-bell fs-5"></i>
          <span class="notification-badge" id="notif-count" style="display:none">0</span>
        </a>
        <!-- Messages -->
        <a href="chat.php" class="btn btn-link position-relative text-muted p-2" title="Messages">
          <i class="fa fa-comment-dots fs-5"></i>
          <span class="notification-badge" id="msg-count" style="display:none">0</span>
        </a>
        <!-- User Dropdown Menu -->
        <div class="dropdown">
          <button class="btn d-flex align-items-center gap-2 border rounded-pill px-3 py-1 shadow-sm" data-bs-toggle="dropdown">
            <div class="chat-avatar" style="width:32px;height:32px;font-size:.75rem;background:linear-gradient(135deg, #1A73E8, #0D47A1);color:#fff">
              <?= strtoupper(substr(htmlspecialchars($_SESSION['user_name']),0,1)) ?>
            </div>
            <span class="d-none d-md-inline fw-600" style="font-size:.88rem">
              <?= htmlspecialchars($_SESSION['user_name']) ?>
            </span>
            <i class="fa fa-chevron-down" style="font-size:.7rem;color:#888"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" style="border-radius:14px;min-width:210px">
            <li class="px-3 py-2 border-bottom">
              <div class="fw-700 text-dark"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
              <small class="text-muted text-capitalize"><?= htmlspecialchars($role) ?> Account</small>
            </li>
            <li><a class="dropdown-item py-2" href="<?= $dashLink ?>"><i class="fa fa-tachometer-alt me-2 text-primary"></i>My Dashboard</a></li>
            <?php if($role==='worker'): ?>
              <li><a class="dropdown-item py-2" href="worker/profile.php"><i class="fa fa-user-edit me-2 text-primary"></i>Edit Profile</a></li>
              <li><a class="dropdown-item py-2" href="worker/subscription.php"><i class="fa fa-crown me-2 text-warning"></i>Subscription</a></li>
            <?php elseif($role==='customer'): ?>
              <li><a class="dropdown-item py-2" href="customer/bookings.php"><i class="fa fa-calendar-check me-2 text-primary"></i>My Bookings</a></li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item py-2 text-danger fw-600" href="api/logout.php"><i class="fa fa-sign-out-alt me-2"></i>Sign Out</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="login.php" class="btn-login"><i class="fa fa-sign-in-alt me-1"></i>Sign In</a>
        <a href="register.php" class="btn-register shadow-sm"><i class="fa fa-user-plus me-1"></i>Join Free</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- ══════════════════ HERO SECTION ══════════════════ -->
<section class="hero position-relative overflow-hidden" style="background: linear-gradient(135deg, #091E3A 0%, #1A73E8 60%, #0284C7 100%); min-height: 85vh; padding: 70px 0 90px;">
  <!-- Background Glow Orbs -->
  <div style="position:absolute; top:-80px; left:-80px; width:350px; height:350px; background:radial-gradient(circle, rgba(251,188,5,0.2) 0%, transparent 70%); border-radius:50%; pointer-events:none;"></div>
  <div style="position:absolute; bottom:0; right:-50px; width:450px; height:450px; background:radial-gradient(circle, rgba(56,189,248,0.25) 0%, transparent 70%); border-radius:50%; pointer-events:none;"></div>

  <div class="container hero-content">
    <div class="row align-items-center g-5">
      
      <!-- Left Column: Copy & Search -->
      <div class="col-lg-6" data-aos="fade-right" data-aos-duration="900">
        <!-- Live Trust Pill -->
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 shimmer-badge text-white" 
             style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); font-size: 0.85rem; font-weight:600;">
          <span class="beacon-dot"></span>
          <span>Bangladesh's #1 On-Demand Service Platform</span>
        </div>

        <h1 class="text-white fw-900 mb-3" style="font-size: clamp(2.3rem, 4.5vw, 3.8rem); line-height: 1.15; letter-spacing: -1px;">
          Hire Verified <span style="background: linear-gradient(135deg, #FFD166, #FB8500); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Local Experts</span> in Minutes
        </h1>

        <p class="lead text-white-50 mb-4" style="font-size: 1.15rem; max-width: 530px; line-height: 1.6;">
          Plumbers, electricians, AC technicians, and 20+ verified tradespeople ready to assist you. Instant booking, masked private calls, and transparent pricing.
        </p>

        <!-- Glassmorphism Smart Search Bar -->
        <form class="glass-search mb-3" action="workers.php" method="GET">
          <div class="d-flex align-items-center gap-2 flex-wrap flex-sm-nowrap">
            <i class="fa fa-search text-primary fs-5 ms-1"></i>
            <input type="text" name="q" placeholder="What service do you need? (e.g. Electrician, AC repair...)" autocomplete="off" id="heroSearch" style="flex:1; border:none; outline:none; font-size:15px; color:#1E293B; background:transparent;">
            
            <div class="vr d-none d-sm-block my-1" style="color:#CBD5E1"></div>
            
            <select name="district" style="border:none; outline:none; font-size:14px; font-weight:600; color:#475569; background:transparent; cursor:pointer;">
              <option value="">All Districts</option>
              <?php
                $districts = ['Dhaka','Chittagong','Sylhet','Rajshahi','Khulna','Barishal','Rangpur','Mymensingh','Comilla','Gazipur','Narayanganj'];
                foreach($districts as $d): ?>
                  <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
              <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn btn-warning fw-800 rounded-pill px-4 py-2 shadow-sm d-flex align-items-center gap-2" style="background:#FB8500; border:none; color:#fff;">
              <span>Search</span> <i class="fa fa-arrow-right"></i>
            </button>
          </div>
        </form>

        <!-- Quick Trending Categories Chips -->
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
          <span class="text-white-50 fw-600" style="font-size: 0.8rem;">Trending:</span>
          <?php
            $quick = [
              ['fa-wrench','Plumber'],
              ['fa-bolt','Electrician'],
              ['fa-wind','AC Repair'],
              ['fa-broom','Cleaner'],
              ['fa-car','Mechanic'],
              ['fa-paint-roller','Painter']
            ];
            foreach($quick as [$icon, $name]):
          ?>
            <a href="workers.php?category=<?= urlencode($name) ?>" class="badge rounded-pill text-white text-decoration-none px-3 py-2 d-inline-flex align-items-center gap-1 hover-lift" style="background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.2); font-size: 0.78rem;">
              <i class="fa <?= $icon ?> text-warning"></i> <?= $name ?>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- Live Metrics Counter -->
        <div class="row g-3 pt-3 border-top" style="border-color: rgba(255,255,255,0.15) !important;">
          <div class="col-3 text-start">
            <div class="fw-800 text-warning fs-3 counter-num">2,450+</div>
            <div class="text-white-50" style="font-size:0.75rem">Verified Pros</div>
          </div>
          <div class="col-3 text-start">
            <div class="fw-800 text-warning fs-3 counter-num">18,500+</div>
            <div class="text-white-50" style="font-size:0.75rem">Jobs Done</div>
          </div>
          <div class="col-3 text-start">
            <div class="fw-800 text-warning fs-3 counter-num">25+</div>
            <div class="text-white-50" style="font-size:0.75rem">Categories</div>
          </div>
          <div class="col-3 text-start">
            <div class="fw-800 text-warning fs-3 counter-num">4.9★</div>
            <div class="text-white-50" style="font-size:0.75rem">User Rating</div>
          </div>
        </div>

      </div>

      <!-- Right Column: Interactive 3D Visual Composition -->
      <div class="col-lg-6 d-none d-lg-block position-relative" data-aos="fade-left" data-aos-duration="1000">
        
        <!-- Center Image Display Card -->
        <div class="position-relative mx-auto rounded-4 overflow-hidden shadow-lg border" 
             style="max-width: 440px; height: 420px; border-color: rgba(255,255,255,0.2) !important;">
          <img src="images/hero_tools.jpg" alt="E-Sheba Professional Service" style="width:100%; height:100%; object-fit:cover;">
          <div style="position:absolute; inset:0; background: linear-gradient(to top, rgba(15,23,42,0.85) 0%, rgba(15,23,42,0.1) 60%);"></div>
          
          <!-- Caption over image -->
          <div class="position-absolute bottom-0 start-0 p-4 text-white">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="beacon-dot"></span>
              <span class="badge bg-success rounded-pill px-2 py-1" style="font-size:.7rem">Live Matching Active</span>
            </div>
            <h5 class="fw-800 mb-0">Certified Technicians at Your Doorstep</h5>
            <small class="text-white-50">Background checked with Bangladesh National ID (NID)</small>
          </div>
        </div>

        <!-- Floating Card 1: Verified Worker Badge (Top Right) -->
        <div class="position-absolute float-card-1 glass-panel rounded-4 p-3 shadow-lg" 
             style="top: -20px; right: 0; min-width: 220px; z-index: 5;">
          <div class="d-flex align-items-center gap-3">
            <img src="images/worker_1.jpg" alt="Rafiq Ahmed" class="rounded-circle" style="width:48px; height:48px; object-fit:cover; border:2px solid #10B981;">
            <div>
              <div class="fw-800 text-dark" style="font-size:.9rem">Rafiq Ahmed</div>
              <div class="d-flex align-items-center gap-1 text-success fw-700" style="font-size:.75rem">
                <i class="fa fa-check-circle"></i> Available Now
              </div>
              <div class="text-warning" style="font-size:.72rem">★ 4.9 (127 Reviews)</div>
            </div>
          </div>
        </div>

        <!-- Floating Card 2: Booking Confirmed Notification (Bottom Left) -->
        <div class="position-absolute float-card-2 glass-panel rounded-4 p-3 shadow-lg" 
             style="bottom: -25px; left: -20px; min-width: 260px; z-index: 5; background: rgba(255,255,255,0.95);">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-success text-white" style="width:44px; height:44px; font-size:20px;">
              <i class="fa fa-bell"></i>
            </div>
            <div>
              <div class="fw-800 text-dark" style="font-size:.88rem">Booking Confirmed!</div>
              <div class="text-muted" style="font-size:.75rem">Technician arriving in 25 mins</div>
            </div>
          </div>
        </div>

        <!-- Floating Card 3: 5-Star Customer Feedback (Bottom Right) -->
        <div class="position-absolute float-card-3 glass-panel rounded-4 p-3 shadow-lg" 
             style="bottom: 40px; right: -25px; min-width: 210px; z-index: 4;">
          <div class="d-flex align-items-center gap-1 text-warning mb-1" style="font-size:14px">
            ★★★★★
          </div>
          <div class="fw-700 text-dark" style="font-size:.82rem">"Solved AC leakage in 30 min!"</div>
          <div class="text-muted" style="font-size:.7rem">— Tanvir H., Mirpur</div>
        </div>

      </div>

    </div>
  </div>

  <!-- Bottom Wave Transition -->
  <div style="position:absolute; bottom:0; left:0; width:100%; overflow:hidden; line-height:0;">
    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="position:relative; display:block; width:calc(100% + 1.3px); height:45px;">
      <path d="M0,0 C150,90 350,-40 500,40 C650,120 900,20 1200,60 L1200,120 L0,120 Z" fill="#ffffff"></path>
    </svg>
  </div>
</section>

<!-- ══════════════════ TRUST RIBBON STRIP ══════════════════ -->
<div class="trust-strip">
  <div class="container">
    <div class="row g-4 align-items-center justify-content-between">
      
      <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
        <div class="trust-item">
          <div class="trust-icon"><i class="fa fa-id-card"></i></div>
          <div>
            <div class="fw-700 text-dark" style="font-size:.92rem">100% NID Verified</div>
            <div class="text-muted" style="font-size:.78rem">Government ID authenticated</div>
          </div>
        </div>
      </div>

      <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
        <div class="trust-item">
          <div class="trust-icon" style="background:#FEF3C7; color:#D97706;"><i class="fa fa-bolt"></i></div>
          <div>
            <div class="fw-700 text-dark" style="font-size:.92rem">30-Min Rapid Dispatch</div>
            <div class="text-muted" style="font-size:.78rem">Nearby technicians on call</div>
          </div>
        </div>
      </div>

      <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
        <div class="trust-item">
          <div class="trust-icon" style="background:#ECFDF5; color:#059669;"><i class="fa fa-shield-alt"></i></div>
          <div>
            <div class="fw-700 text-dark" style="font-size:.92rem">Masked Voice Calling</div>
            <div class="text-muted" style="font-size:.78rem">Personal numbers kept private</div>
          </div>
        </div>
      </div>

      <div class="col-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
        <div class="trust-item">
          <div class="trust-icon" style="background:#FEE2E2; color:#DC2626;"><i class="fa fa-coins"></i></div>
          <div>
            <div class="fw-700 text-dark" style="font-size:.92rem">Transparent Pricing</div>
            <div class="text-muted" style="font-size:.78rem">bKash, Nagad or Cash on Delivery</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ══════════════════ 25+ POPULAR SERVICES ══════════════════ -->
<section class="section-gap" style="background:#F8FAFC;">
  <div class="container">
    
    <div class="text-center mb-5" data-aos="fade-up">
      <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-700 mb-2" style="font-size:.82rem;">
        BROWSE PROFESSIONAL TRADES
      </span>
      <h2 class="fw-900 text-dark mb-2" style="font-size:2.3rem; letter-spacing:-0.5px;">Explore 25+ Home & Repair Services</h2>
      <p class="text-muted mx-auto" style="max-width:560px; font-size:1rem;">
        From emergency electrical and plumbing issues to home renovations and appliance diagnostics.
      </p>
    </div>

    <div class="row g-3" id="categoriesGrid">
      <?php
        $categoryImgMap = [
          'Plumber'         => 'images/plumber.jpg',
          'Electrician'     => 'images/electrician.jpg',
          'AC Repair'       => 'images/ac_repair.jpg',
          'Mechanic'        => 'images/mechanic.jpg',
          'Cleaner'         => 'images/cleaner.jpg',
          'Painter'         => 'images/painter.jpg',
          'Carpenter'       => 'images/carpenter.jpg',
          'Pest Control'    => 'images/cleaner.jpg',
          'Gardener'        => 'images/hero_tools.jpg',
          'TV Repair'       => 'images/ac_repair.jpg',
          'Fridge Repair'   => 'images/ac_repair.jpg',
          'Washing Machine' => 'images/cleaner.jpg',
        ];

        try {
          $cats = DB::fetchAll("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order LIMIT 12");
          $delay = 50;
          foreach($cats as $cat):
            $count = DB::fetchOne("SELECT COUNT(*) as c FROM worker_categories wc JOIN workers w ON wc.worker_id=w.id WHERE wc.category_id=? AND w.is_approved=1", [$cat['id']]);
            $img = $categoryImgMap[$cat['name']] ?? 'images/hero_tools.jpg';
      ?>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
          <a href="workers.php?category_id=<?= $cat['id'] ?>" class="category-card-modern">
            <div class="img-wrapper">
              <img src="<?= $img ?>" alt="<?= htmlspecialchars($cat['name']) ?>">
            </div>
            <h6><?= htmlspecialchars($cat['name']) ?></h6>
            <small><?= $count['c'] ?? 0 ?> Verified Pros</small>
          </a>
        </div>
      <?php
            $delay += 50;
          endforeach;
        } catch(Exception $e) {
          // Fallback static cards if DB is loading
          $staticCats = [
            ['Plumber', 'images/plumber.jpg', '48'],
            ['Electrician', 'images/electrician.jpg', '64'],
            ['AC Repair', 'images/ac_repair.jpg', '38'],
            ['Mechanic', 'images/mechanic.jpg', '29'],
            ['Cleaner', 'images/cleaner.jpg', '52'],
            ['Painter', 'images/painter.jpg', '31'],
            ['Carpenter', 'images/carpenter.jpg', '25'],
            ['Pest Control', 'images/cleaner.jpg', '18'],
            ['Gardener', 'images/hero_tools.jpg', '15'],
            ['TV Repair', 'images/ac_repair.jpg', '22'],
            ['Fridge Repair', 'images/ac_repair.jpg', '34'],
            ['Washing Machine', 'images/cleaner.jpg', '19']
          ];
          $delay = 50;
          foreach($staticCats as [$name, $img, $num]):
      ?>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
          <a href="workers.php?category=<?= urlencode($name) ?>" class="category-card-modern">
            <div class="img-wrapper">
              <img src="<?= $img ?>" alt="<?= $name ?>">
            </div>
            <h6><?= $name ?></h6>
            <small><?= $num ?> Verified Pros</small>
          </a>
        </div>
      <?php
            $delay += 50;
          endforeach;
        }
      ?>
    </div>

    <!-- View All CTA -->
    <div class="text-center mt-5" data-aos="fade-up">
      <a href="categories.php" class="btn btn-outline-primary btn-lg rounded-pill px-5 py-3 fw-700 shadow-sm hover-lift" style="font-size:0.95rem;">
        View All 25 Categories <i class="fa fa-arrow-right ms-2"></i>
      </a>
    </div>

  </div>
</section>

<!-- ══════════════════ WHY CHOOSE E-SHEBA ══════════════════ -->
<section class="section-gap" style="background:#ffffff;">
  <div class="container">
    
    <div class="text-center mb-5" data-aos="fade-up">
      <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fw-700 mb-2" style="font-size:.82rem;">
        WHY CHOOSE E-SHEBA
      </span>
      <h2 class="fw-900 text-dark mb-2" style="font-size:2.3rem;">Safety, Speed & Transparency Guaranteed</h2>
      <p class="text-muted mx-auto" style="max-width:540px;">
        Designed to solve the trust deficit in Bangladesh's informal home service industry.
      </p>
    </div>

    <div class="row g-4">
      
      <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
        <div class="feature-box h-100">
          <div class="feature-icon-badge" style="background:#EFF6FF; color:#1A73E8;">
            <i class="fa fa-shield-check"></i>
          </div>
          <h5 class="fw-800 text-dark mb-2">NID Verified Profiles</h5>
          <p class="text-muted mb-0" style="font-size:0.9rem; line-height:1.6;">
            Every technician is manually inspected by our admin team with verified National Identity cards and trade references.
          </p>
        </div>
      </div>

      <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
        <div class="feature-box h-100">
          <div class="feature-icon-badge" style="background:#FEF3C7; color:#D97706;">
            <i class="fa fa-phone-slash"></i>
          </div>
          <h5 class="fw-800 text-dark mb-2">Private Masked Calling</h5>
          <p class="text-muted mb-0" style="font-size:0.9rem; line-height:1.6;">
            Call technicians directly through the web browser powered by Agora WebRTC. Your real mobile number is never exposed.
          </p>
        </div>
      </div>

      <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
        <div class="feature-box h-100">
          <div class="feature-icon-badge" style="background:#ECFDF5; color:#059669;">
            <i class="fa fa-receipt"></i>
          </div>
          <h5 class="fw-800 text-dark mb-2">No Hidden Charges</h5>
          <p class="text-muted mb-0" style="font-size:0.9rem; line-height:1.6;">
            Transparent hourly rates agreed upfront. Downloadable digital PDF/HTML invoices with full breakdown of all work done.
          </p>
        </div>
      </div>

      <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
        <div class="feature-box h-100">
          <div class="feature-icon-badge" style="background:#FEE2E2; color:#DC2626;">
            <i class="fa fa-comments"></i>
          </div>
          <h5 class="fw-800 text-dark mb-2">Instant WebSocket Chat</h5>
          <p class="text-muted mb-0" style="font-size:0.9rem; line-height:1.6;">
            Send photos of broken pipes or faulty wiring in real-time. Discuss work details before booking directly via Pusher WebSockets.
          </p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ══════════════════ HOW IT WORKS (3 SIMPLE STEPS) ══════════════════ -->
<section class="section-gap" style="background:#F8FAFC;">
  <div class="container">
    
    <div class="text-center mb-5" data-aos="fade-up">
      <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-700 mb-2" style="font-size:.82rem;">
        SIMPLE 3-STEP PROCESS
      </span>
      <h2 class="fw-900 text-dark mb-2" style="font-size:2.3rem;">How E-Sheba Works</h2>
      <p class="text-muted">Getting your problem fixed has never been this simple.</p>
    </div>

    <div class="row g-4 justify-content-center">
      
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="bg-white rounded-4 p-4 text-center h-100 border shadow-sm hover-lift">
          <div class="position-relative d-inline-block mb-3">
            <div class="stat-icon mx-auto rounded-4 d-flex align-items-center justify-content-center" 
                 style="background:#EFF6FF; color:#1A73E8; width:72px; height:72px; font-size:28px;">
              <i class="fa fa-search-location"></i>
            </div>
            <span class="position-absolute badge rounded-circle bg-warning text-dark fw-800" 
                  style="top:-5px; right:-5px; width:26px; height:26px; display:flex; align-items:center; justify-content:center;">1</span>
          </div>
          <h5 class="fw-800 text-dark mb-2">Search & Compare</h5>
          <p class="text-muted" style="font-size:.9rem; line-height:1.6;">
            Search your required service, filter by district (Dhaka, Chittagong, Sylhet), inspect real customer ratings and hourly rates.
          </p>
        </div>
      </div>

      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="bg-white rounded-4 p-4 text-center h-100 border shadow-sm hover-lift">
          <div class="position-relative d-inline-block mb-3">
            <div class="stat-icon mx-auto rounded-4 d-flex align-items-center justify-content-center" 
                 style="background:#FEF3C7; color:#D97706; width:72px; height:72px; font-size:28px;">
              <i class="fa fa-calendar-check"></i>
            </div>
            <span class="position-absolute badge rounded-circle bg-warning text-dark fw-800" 
                  style="top:-5px; right:-5px; width:26px; height:26px; display:flex; align-items:center; justify-content:center;">2</span>
          </div>
          <h5 class="fw-800 text-dark mb-2">Book with Address & Time</h5>
          <p class="text-muted" style="font-size:.9rem; line-height:1.6;">
            Choose preferred date and time, type your address, and submit. The technician receives an instant audio & push notification.
          </p>
        </div>
      </div>

      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="bg-white rounded-4 p-4 text-center h-100 border shadow-sm hover-lift">
          <div class="position-relative d-inline-block mb-3">
            <div class="stat-icon mx-auto rounded-4 d-flex align-items-center justify-content-center" 
                 style="background:#ECFDF5; color:#059669; width:72px; height:72px; font-size:28px;">
              <i class="fa fa-handshake"></i>
            </div>
            <span class="position-absolute badge rounded-circle bg-warning text-dark fw-800" 
                  style="top:-5px; right:-5px; width:26px; height:26px; display:flex; align-items:center; justify-content:center;">3</span>
          </div>
          <h5 class="fw-800 text-dark mb-2">Service Done & Secure Pay</h5>
          <p class="text-muted" style="font-size:.9rem; line-height:1.6;">
            Job completed at your home. Pay cash or online via SSLCommerz (bKash/Nagad/Card), then leave a verified review.
          </p>
        </div>
      </div>

    </div>

  </div>
</section>

<!-- ══════════════════ FEATURED PROVIDERS ══════════════════ -->
<section class="section-gap" style="background:#ffffff;">
  <div class="container">
    
    <div class="d-flex justify-content-between align-items-end mb-4" data-aos="fade-up">
      <div>
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-700 mb-2" style="font-size:.82rem;">
          TOP-RATED SERVICE PROVIDERS
        </span>
        <h2 class="fw-900 text-dark mb-0" style="font-size:2.3rem;">Featured Professionals</h2>
      </div>
      <a href="workers.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-700 d-none d-md-inline hover-lift">
        Explore All <i class="fa fa-arrow-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4" id="featuredWorkers">
      <?php
        $avatarFallback = [
          'images/worker_1.jpg',
          'images/worker_2.jpg',
          'images/worker_3.jpg',
          'images/worker_4.jpg',
          'images/worker_5.jpg'
        ];

        try {
          $workers = DB::fetchAll(
            "SELECT w.*, u.name, u.email, u.avatar,
                    GROUP_CONCAT(c.name ORDER BY c.sort_order SEPARATOR ', ') as categories
             FROM workers w
             JOIN users u ON w.user_id = u.id
             LEFT JOIN worker_categories wc ON w.id = wc.worker_id
             LEFT JOIN categories c ON wc.category_id = c.id
             WHERE w.is_approved = 1
             GROUP BY w.id
             ORDER BY w.avg_rating DESC, w.total_reviews DESC
             LIMIT 4"
          );

          if (empty($workers)) throw new Exception("No workers in DB");

          $wIdx = 0;
          foreach($workers as $w):
            $avatar = $w['avatar'] ?: ($avatarFallback[$wIdx % count($avatarFallback)]);
            $wIdx++;
      ?>
        <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $wIdx * 100 ?>">
          <div class="worker-card-modern h-100 position-relative">
            <!-- Header Banner with Status -->
            <div class="card-header-img">
              <img src="images/hero_tools.jpg" alt="Service banner" class="card-banner-bg">
              <span class="status-pill <?= $w['is_available'] ? '' : 'busy' ?>">
                <span class="beacon-dot" style="<?= $w['is_available'] ? '' : 'background:#94A3B8;' ?>"></span>
                <?= $w['is_available'] ? 'Available' : 'Busy' ?>
              </span>
            </div>

            <!-- Avatar -->
            <div class="worker-avatar-wrap">
              <img src="<?= $avatar ?>" alt="<?= htmlspecialchars($w['name']) ?>" class="worker-avatar-img">
            </div>

            <!-- Card Body Content -->
            <div class="card-content">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <h5 class="fw-800 text-dark mb-0" style="font-size:1.05rem;"><?= htmlspecialchars($w['name']) ?></h5>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size:.7rem">
                  <?= $w['experience_years'] ?> yrs exp
                </span>
              </div>

              <div class="text-muted fw-600 mb-2" style="font-size:.8rem;">
                <i class="fa fa-wrench text-warning me-1"></i><?= htmlspecialchars($w['categories'] ?: 'Service Technician') ?>
              </div>

              <div class="text-muted mb-3" style="font-size:.8rem;">
                <i class="fa fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($w['thana'].', '.$w['district']) ?>
              </div>

              <!-- Rating and Price Row -->
              <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto mb-3">
                <div class="d-flex align-items-center gap-1 text-warning fw-700" style="font-size:.85rem;">
                  <span>★</span> <?= number_format($w['avg_rating'], 1) ?>
                  <span class="text-muted fw-500" style="font-size:.75rem">(<?= $w['total_reviews'] ?>)</span>
                </div>
                <div class="fw-800 text-primary" style="font-size:1.1rem;">
                  ৳<?= number_format($w['price_per_hour']) ?><small class="text-muted fw-500" style="font-size:.75rem">/hr</small>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="d-flex gap-2">
                <a href="worker-profile.php?id=<?= $w['id'] ?>" class="btn btn-primary btn-sm flex-grow-1 rounded-pill fw-700 py-2">
                  <i class="fa fa-calendar-check me-1"></i> Book
                </a>
                <a href="chat.php?to=<?= $w['user_id'] ?>" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;" title="Send Message">
                  <i class="fa fa-comment"></i>
                </a>
                <button class="btn btn-outline-success btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;" onclick="initiateCall(<?= $w['user_id'] ?>, '<?= htmlspecialchars(addslashes($w['name'])) ?>')" title="Audio Call">
                  <i class="fa fa-phone"></i>
                </button>
              </div>

            </div>
          </div>
        </div>
      <?php
          endforeach;
        } catch(Exception $e) {
          // Demo static cards if DB offline
          $demo = [
            ['Kabir Hossain', 'Electrician', 'Mirpur, Dhaka', 4.9, 124, 400, 6, 'images/worker_1.jpg'],
            ['Mizanur Rahman', 'Master Plumber', 'Dhanmondi, Dhaka', 4.8, 98, 350, 8, 'images/worker_2.jpg'],
            ['Sabbir Ahmed', 'AC & Fridge Repair', 'Uttara, Dhaka', 5.0, 74, 600, 4, 'images/worker_3.jpg'],
            ['Tanvir Hasan', 'Wood Craftsman', 'Chittagong', 4.7, 52, 450, 7, 'images/worker_5.jpg']
          ];
          $idx = 0;
          foreach($demo as [$name,$cat,$area,$rating,$reviews,$price,$exp,$av]):
            $idx++;
      ?>
        <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $idx * 100 ?>">
          <div class="worker-card-modern h-100">
            <div class="card-header-img">
              <img src="images/hero_tools.jpg" alt="Service banner">
              <span class="status-pill">
                <span class="beacon-dot"></span> Available
              </span>
              <div class="worker-avatar-wrap">
                <img src="<?= $av ?>" alt="<?= $name ?>">
              </div>
            </div>

            <div class="card-content">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <h5 class="fw-800 text-dark mb-0" style="font-size:1.05rem;"><?= $name ?></h5>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size:.7rem">
                  <?= $exp ?> yrs exp
                </span>
              </div>

              <div class="text-muted fw-600 mb-2" style="font-size:.8rem;">
                <i class="fa fa-wrench text-warning me-1"></i><?= $cat ?>
              </div>

              <div class="text-muted mb-3" style="font-size:.8rem;">
                <i class="fa fa-map-marker-alt text-danger me-1"></i><?= $area ?>
              </div>

              <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto mb-3">
                <div class="d-flex align-items-center gap-1 text-warning fw-700" style="font-size:.85rem;">
                  <span>★</span> <?= $rating ?>
                  <span class="text-muted fw-500" style="font-size:.75rem">(<?= $reviews ?>)</span>
                </div>
                <div class="fw-800 text-primary" style="font-size:1.1rem;">
                  ৳<?= $price ?><small class="text-muted fw-500" style="font-size:.75rem">/hr</small>
                </div>
              </div>

              <div class="d-flex gap-2">
                <a href="workers.php" class="btn btn-primary btn-sm flex-grow-1 rounded-pill fw-700 py-2">
                  <i class="fa fa-calendar-check me-1"></i> Book
                </a>
                <a href="login.php" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                  <i class="fa fa-comment"></i>
                </a>
              </div>

            </div>
          </div>
        </div>
      <?php endforeach; } ?>
    </div>

  </div>
</section>

<!-- ══════════════════ AUTHENTIC CUSTOMER REVIEWS ══════════════════ -->
<section class="section-gap" style="background:#F8FAFC;">
  <div class="container">
    
    <div class="text-center mb-5" data-aos="fade-up">
      <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fw-700 mb-2" style="font-size:.82rem;">
        REAL EXPERIENCES
      </span>
      <h2 class="fw-900 text-dark mb-2" style="font-size:2.3rem;">Trusted by 50,000+ Happy Households</h2>
      <p class="text-muted">Read what genuine homeowners in Bangladesh have to say about E-Sheba.</p>
    </div>

    <div class="row g-4">
      
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="testimonial-card-modern h-100 hover-lift">
          <div class="d-flex align-items-center gap-1 text-warning mb-3">
            ★★★★★
          </div>
          <p class="text-dark mb-4" style="font-size:.95rem; line-height:1.7;">
            "Our main water pipe burst at 9 PM on a Friday. Found a master plumber via E-Sheba who arrived within 25 minutes. Fixed the leak with modern fittings and charged exactly the fixed hourly rate!"
          </p>
          <div class="d-flex align-items-center gap-3">
            <img src="images/worker_4.jpg" alt="Farzana Rahman" class="testimonial-avatar">
            <div>
              <div class="fw-800 text-dark" style="font-size:.9rem;">Farzana Rahman</div>
              <small class="text-muted"><i class="fa fa-map-marker-alt text-danger me-1"></i>Dhanmondi, Dhaka</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="testimonial-card-modern h-100 hover-lift">
          <div class="d-flex align-items-center gap-1 text-warning mb-3">
            ★★★★★
          </div>
          <p class="text-dark mb-4" style="font-size:.95rem; line-height:1.7;">
            "The masked in-app calling feature is fantastic! I was able to talk to the electrician without revealing my personal phone number. He arrived on time, was very polite, and did clean wiring."
          </p>
          <div class="d-flex align-items-center gap-3">
            <img src="images/worker_1.jpg" alt="Shakil Ahmed" class="testimonial-avatar">
            <div>
              <div class="fw-800 text-dark" style="font-size:.9rem;">Shakil Ahmed</div>
              <small class="text-muted"><i class="fa fa-map-marker-alt text-danger me-1"></i>Uttara Sector 11, Dhaka</small>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="testimonial-card-modern h-100 hover-lift">
          <div class="d-flex align-items-center gap-1 text-warning mb-3">
            ★★★★★
          </div>
          <p class="text-dark mb-4" style="font-size:.95rem; line-height:1.7;">
            "As an elderly resident in Chittagong, finding trustworthy AC repair technicians used to be stressful. E-Sheba's NID verified badge gave us total peace of mind. Paid easily via bKash."
          </p>
          <div class="d-flex align-items-center gap-3">
            <img src="images/worker_5.jpg" alt="Prof. M. A. Karim" class="testimonial-avatar">
            <div>
              <div class="fw-800 text-dark" style="font-size:.9rem;">Prof. M. A. Karim</div>
              <small class="text-muted"><i class="fa fa-map-marker-alt text-danger me-1"></i>Nasirabad, Chittagong</small>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</section>

<!-- ══════════════════ MOBILE APP / PWA SHOWCASE ══════════════════ -->
<section class="section-gap" style="background:#ffffff;">
  <div class="container">
    <div class="app-banner-section" data-aos="zoom-in" data-aos-duration="900">
      <div class="row align-items-center g-5">
        
        <div class="col-lg-7">
          <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-800 mb-3" style="font-size:.8rem;">
            <i class="fa fa-mobile-alt me-1"></i> PROGRESSIVE WEB APP (PWA)
          </span>
          <h2 class="fw-900 text-white mb-3" style="font-size: clamp(2rem, 3.5vw, 2.8rem); line-height:1.2;">
            Take E-Sheba Everywhere with You on Your Smartphone
          </h2>
          <p class="text-white-50 mb-4" style="font-size:1.05rem; line-height:1.7;">
            Install E-Sheba directly onto your Android or iPhone home screen with one tap. Enjoy lightning-fast bookings, offline caching, and instant audio call notifications!
          </p>
          
          <div class="d-flex flex-wrap gap-3">
            <button onclick="installPWA()" class="btn btn-warning fw-800 px-4 py-3 rounded-pill d-flex align-items-center gap-2 shadow hover-lift" style="font-size:.95rem; background:#FB8500; border:none; color:#fff;">
              <i class="fa fa-download fs-5"></i> Add to Home Screen (Install App)
            </button>
            <a href="register.php?role=worker" class="btn btn-outline-light fw-700 px-4 py-3 rounded-pill d-flex align-items-center gap-2 hover-lift" style="font-size:.95rem;">
              <i class="fa fa-hard-hat"></i> Join as a Service Provider
            </a>
          </div>
        </div>

        <div class="col-lg-5 text-center">
          <div class="phone-mockup-frame">
            <img src="images/service_app.jpg" alt="E-Sheba Mobile App" style="width:100%; height:auto; display:block;">
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- ══════════════════ FOOTER ══════════════════ -->
<footer class="footer-main">
  <div class="container">
    <div class="row g-5">
      
      <!-- Brand & Mission -->
      <div class="col-lg-4">
        <a href="index.php" class="text-decoration-none">
          <img src="images/logo-white.svg" alt="E-Sheba" class="brand-logo-footer">
        </a>
        <p class="footer-desc mt-2">
          Bangladesh's premier on-demand local marketplace connecting homeowners with NID-verified skilled professionals. Safety, speed, and transparency in every service.
        </p>
        <div class="footer-social mt-4">
          <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>

      <!-- Quick Services -->
      <div class="col-6 col-lg-2">
        <div class="footer-title">Top Services</div>
        <ul class="footer-links">
          <li><a href="workers.php?category=Electrician">Electrician</a></li>
          <li><a href="workers.php?category=Plumber">Plumber</a></li>
          <li><a href="workers.php?category=AC+Repair">AC Master Repair</a></li>
          <li><a href="workers.php?category=Mechanic">Car Mechanic</a></li>
          <li><a href="workers.php?category=Cleaner">Deep Cleaning</a></li>
          <li><a href="categories.php">All 25 Services →</a></li>
        </ul>
      </div>

      <!-- Company Links -->
      <div class="col-6 col-lg-2">
        <div class="footer-title">Platform</div>
        <ul class="footer-links">
          <li><a href="about.php">About Project</a></li>
          <li><a href="how-it-works.php">How It Works</a></li>
          <li><a href="register.php?role=worker">Join as Provider</a></li>
          <li><a href="subscription.php">Subscription Plans</a></li>
          <li><a href="contact.php">Help Center</a></li>
          <li><a href="faq.php">Common FAQs</a></li>
        </ul>
      </div>

      <!-- Legal & Trust -->
      <div class="col-6 col-lg-2">
        <div class="footer-title">Legal & Security</div>
        <ul class="footer-links">
          <li><a href="privacy.php">Privacy Policy</a></li>
          <li><a href="terms.php">Terms of Service</a></li>
          <li><a href="admin/dashboard.php">Admin Portal</a></li>
        </ul>
        <div class="mt-3">
          <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fw-700" style="font-size:.75rem">
            <i class="fa fa-shield-alt me-1"></i> 100% NID Verified
          </span>
        </div>
      </div>

      <!-- Supported Payments -->
      <div class="col-6 col-lg-2">
        <div class="footer-title">Supported Payments</div>
        <p style="font-size:.82rem; line-height:1.6; margin-bottom:12px;">
          Seamless cashless payments powered by SSLCommerz Sandbox:
        </p>
        <div class="d-flex flex-wrap gap-2 text-white" style="font-size:1.4rem;">
          <i class="fab fa-cc-visa" title="Visa"></i>
          <i class="fab fa-cc-mastercard" title="MasterCard"></i>
          <span class="badge bg-danger rounded-pill px-2 py-1 fw-800" style="font-size:.7rem">bKash</span>
          <span class="badge bg-warning text-dark rounded-pill px-2 py-1 fw-800" style="font-size:.7rem">Nagad</span>
        </div>
      </div>

    </div>

    <!-- Bottom Copyright -->
    <div class="footer-bottom mt-5 pt-3 border-top">
      <div>
        <p class="mb-1 fw-600">© 2026 E-Sheba · Department of Computer Science & Engineering (CSE) · World University of Bangladesh (WUB)</p>
        <small class="text-white-50">Course: CSE 06133248 · Engineering Team: Sahnewaz Hasib · Fatema Khatun · Khadiza Hasan · Jannatul Bushra · Manir Rahman</small>
      </div>
      <div>
        <span class="badge bg-secondary-subtle text-white border border-secondary px-3 py-1 rounded-pill" style="font-size:.75rem">
          v2.0 Premium Edition
        </span>
      </div>
    </div>

  </div>
</footer>

<!-- ══════════════════ CALL OVERLAY ══════════════════ -->
<div class="call-overlay" id="callOverlay" style="display:none">
  <div class="call-avatar" id="callAvatar">📞</div>
  <div class="call-name" id="callName">Service Provider</div>
  <div class="call-status" id="callStatus">Calling...</div>
  <div class="call-controls">
    <button class="call-btn mute" id="muteBtn" onclick="toggleMute()" title="Mute"><i class="fa fa-microphone"></i></button>
    <button class="call-btn hangup" onclick="endCall()" title="End Call"><i class="fa fa-phone-slash"></i></button>
  </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.20.0.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="js/app.js"></script>
<script src="js/call.js"></script>

<script>
  // Initialize Animate On Scroll (AOS) safely
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof AOS !== 'undefined') {
      AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        offset: 40
      });
    }
  });

  // Animated Numbers Counter on Viewport Entry
  let counted = false;
  $(window).on('scroll', function() {
    if (!counted && $('.counter-num').length) {
      const oTop = $('.counter-num').offset().top - window.innerHeight;
      if ($(window).scrollTop() > oTop) {
        $('.counter-num').each(function() {
          const $this = $(this);
          $this.addClass('hover-lift');
        });
        counted = true;
      }
    }
  });
</script>
</body>
</html>
