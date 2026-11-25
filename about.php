<?php
// About Page — E-Sheba
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';
Auth::start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us — <?= APP_NAME ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body style="background:#F8FAFC;">

<!-- Navbar -->
<nav class="navbar-esheba sticky-top">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="brand d-flex align-items-center gap-2 text-decoration-none py-1">
      <img src="images/logo.svg" alt="E-Sheba" class="brand-logo-img">
    </a>
    <div class="d-flex align-items-center gap-3">
      <a href="categories.php" class="nav-link d-none d-md-inline">Services</a>
      <a href="workers.php" class="btn btn-outline-primary rounded-pill px-3" style="font-size:.85rem">Find Providers</a>
      <?php if(Auth::isLoggedIn()): ?>
        <a href="<?= Auth::role()==='worker'?'worker/dashboard.php':(Auth::role()==='admin'?'admin/dashboard.php':'customer/dashboard.php') ?>" class="btn btn-primary rounded-pill px-3" style="font-size:.85rem">Dashboard</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary rounded-pill px-3" style="font-size:.85rem">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Hero Banner -->
<div class="py-5 text-white position-relative" style="background: linear-gradient(135deg, #091E3A 0%, #1A73E8 100%);">
  <div class="container py-4 text-center" data-aos="fade-up">
    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-800 mb-3" style="font-size:.8rem">
      WORLD UNIVERSITY OF BANGLADESH (WUB) • CSE CAPSTONE PROJECT
    </span>
    <h1 class="fw-900 mb-3" style="font-size:clamp(2rem, 4vw, 3.2rem)">Engineering Trust for Local Services in Bangladesh</h1>
    <p class="text-white-50 mx-auto" style="max-width:680px; font-size:1.1rem; line-height:1.7;">
      E-Sheba connects Bangladeshi households with pre-vetted, certified blue-collar technicians with transparent hourly pricing, masked voice calls, and digital cashless payments.
    </p>
  </div>
</div>

<!-- Mission Section -->
<div class="container py-5">
  <div class="row align-items-center g-5 mb-5">
    
    <div class="col-lg-6" data-aos="fade-right">
      <span class="badge bg-primary-subtle text-primary fw-700 px-3 py-1 rounded-pill mb-2">OUR VISION</span>
      <h2 class="fw-900 text-dark mb-3">Empowering 2,400+ Skilled Workers Across 64 Districts</h2>
      <p class="text-muted" style="line-height:1.8; font-size:1rem;">
        In Bangladesh, finding reliable handymen has historically depended on fragmented roadside stands and word-of-mouth. Technicians faced uncertain incomes, and homeowners dealt with safety risks and unpredictable pricing.
      </p>
      <p class="text-muted" style="line-height:1.8; font-size:1rem;">
        <strong>E-Sheba</strong> transforms this market into a formalized, tech-enabled ecosystem. With National Identity (NID) verification, Agora WebRTC private calling, Pusher WebSockets real-time chat, and SSLCommerz secured digital payments, we protect both the customer and the service worker.
      </p>

      <div class="row g-3 pt-2">
        <div class="col-6">
          <div class="p-3 bg-white rounded-3 border">
            <h4 class="fw-800 text-primary mb-1">100%</h4>
            <small class="text-muted fw-600">NID Verified Technicians</small>
          </div>
        </div>
        <div class="col-6">
          <div class="p-3 bg-white rounded-3 border">
            <h4 class="fw-800 text-success mb-1">৳399/mo</h4>
            <small class="text-muted fw-600">Fair Worker Subscription</small>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6" data-aos="fade-left">
      <div class="rounded-4 overflow-hidden shadow-lg border position-relative" style="height:360px;">
        <img src="images/hero_tools.jpg" alt="E-Sheba Service Team" style="width:100%; height:100%; object-fit:cover;">
        <div class="position-absolute bottom-0 start-0 w-100 p-4 text-white" style="background:linear-gradient(to top, rgba(0,0,0,0.8), transparent)">
          <h5 class="fw-800 mb-0">Professional Standards. Guaranteed Trust.</h5>
          <small class="text-white-50">Department of Computer Science & Engineering · World University of Bangladesh</small>
        </div>
      </div>
    </div>

  </div>

  <!-- Engineering Team Section -->
  <div class="text-center mb-5 mt-5" data-aos="fade-up">
    <span class="badge bg-primary-subtle text-primary fw-700 px-3 py-2 rounded-pill mb-2">PROJECT TEAM</span>
    <h2 class="fw-900 text-dark">Meet the 5 Engineers Behind E-Sheba</h2>
    <p class="text-muted">Department of Computer Science and Engineering · Course: CSE 06133248</p>
  </div>

  <div class="row g-4 justify-content-center">
    
    <!-- Member 1 -->
    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
      <div class="bg-white rounded-4 p-4 text-center border shadow-sm hover-lift h-100">
        <img src="images/worker_1.jpg" alt="Sahnewaz Hasib" class="rounded-circle mb-3 border shadow-sm" style="width:90px;height:90px;object-fit:cover;">
        <h5 class="fw-800 text-dark mb-1">Sahnewaz Hasib</h5>
        <div class="text-primary fw-700 mb-1" style="font-size:.85rem">Student ID: 4264</div>
        <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill mb-3" style="font-size:.75rem">Lead Frontend Engineer</span>
        <p class="text-muted mb-0" style="font-size:.85rem; line-height:1.6;">
          Architected the customer marketplace, search and filter directories, responsive Bootstrap 5 components, and Leaflet.js mapping.
        </p>
      </div>
    </div>

    <!-- Member 2 -->
    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
      <div class="bg-white rounded-4 p-4 text-center border shadow-sm hover-lift h-100">
        <img src="images/worker_4.jpg" alt="Fatema Khatun" class="rounded-circle mb-3 border shadow-sm" style="width:90px;height:90px;object-fit:cover;">
        <h5 class="fw-800 text-dark mb-1">Fatema Khatun</h5>
        <div class="text-primary fw-700 mb-1" style="font-size:.85rem">Student ID: 4276</div>
        <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill mb-3" style="font-size:.75rem">Lead Back-Office Engineer</span>
        <p class="text-muted mb-0" style="font-size:.85rem; line-height:1.6;">
          Engineered the Worker command center, live job lifecycle states (Accept/Decline/Start/Complete), and Admin NID verification panel.
        </p>
      </div>
    </div>

    <!-- Member 3 -->
    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
      <div class="bg-white rounded-4 p-4 text-center border shadow-sm hover-lift h-100">
        <img src="images/worker_2.jpg" alt="Khadiza Binte Hasan" class="rounded-circle mb-3 border shadow-sm" style="width:90px;height:90px;object-fit:cover;">
        <h5 class="fw-800 text-dark mb-1">Khadiza Binte Hasan</h5>
        <div class="text-primary fw-700 mb-1" style="font-size:.85rem">Student ID: 4260</div>
        <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill mb-3" style="font-size:.75rem">Lead Backend Architect</span>
        <p class="text-muted mb-0" style="font-size:.85rem; line-height:1.6;">
          Designed the 11-table relational MySQL schema, PDO database singleton, multi-role authentication, BCRYPT security, and email OTP verification.
        </p>
      </div>
    </div>

    <!-- Member 4 -->
    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
      <div class="bg-white rounded-4 p-4 text-center border shadow-sm hover-lift h-100">
        <img src="images/worker_3.jpg" alt="Jannatul Bushra" class="rounded-circle mb-3 border shadow-sm" style="width:90px;height:90px;object-fit:cover;">
        <h5 class="fw-800 text-dark mb-1">Jannatul Bushra</h5>
        <div class="text-primary fw-700 mb-1" style="font-size:.85rem">Student ID: 4286</div>
        <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill mb-3" style="font-size:.75rem">Lead FinTech & Subscriptions</span>
        <p class="text-muted mb-0" style="font-size:.85rem; line-height:1.6;">
          Implemented SSLCommerz payment integration, ৳399 worker recurring subscriptions, automated daily expiry cron jobs, and billing invoices.
        </p>
      </div>
    </div>

    <!-- Member 5 -->
    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
      <div class="bg-white rounded-4 p-4 text-center border shadow-sm hover-lift h-100">
        <img src="images/worker_5.jpg" alt="Manir Rahman" class="rounded-circle mb-3 border shadow-sm" style="width:90px;height:90px;object-fit:cover;">
        <h5 class="fw-800 text-dark mb-1">Manir Rahman</h5>
        <div class="text-primary fw-700 mb-1" style="font-size:.85rem">Student ID: 4278</div>
        <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill mb-3" style="font-size:.75rem">Lead Real-Time & Mobile Engineer</span>
        <p class="text-muted mb-0" style="font-size:.85rem; line-height:1.6;">
          Developed Pusher WebSocket instant chat, privacy-masked in-app audio voice calling with Agora WebRTC, and Progressive Web App (PWA) engine.
        </p>
      </div>
    </div>

  </div>

</div>

<!-- Footer -->
<footer class="footer-main mt-5">
  <div class="container text-center">
    <img src="images/logo-white.svg" alt="E-Sheba" style="height:40px; margin-bottom:12px;">
    <p class="mb-1 text-white-50">© 2026 E-Sheba · Department of Computer Science & Engineering (CSE) · World University of Bangladesh</p>
    <small class="text-white-50">Course: CSE 06133248 · Capstone Project</small>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({duration:800, once:true});</script>
</body>
</html>
