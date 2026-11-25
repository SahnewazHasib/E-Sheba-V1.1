<?php
// How It Works — E-Sheba
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';
Auth::start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>How It Works — <?= APP_NAME ?></title>
  
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

<!-- Hero Section -->
<div class="py-5 text-white position-relative" style="background: linear-gradient(135deg, #091E3A 0%, #1A73E8 100%);">
  <div class="container py-4 text-center" data-aos="fade-up">
    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-800 mb-3" style="font-size:.8rem">
      STEP-BY-STEP USER GUIDE
    </span>
    <h1 class="fw-900 mb-3" style="font-size:clamp(2rem, 4vw, 3.2rem)">How E-Sheba Works for Everyone</h1>
    <p class="text-white-50 mx-auto" style="max-width:640px; font-size:1.1rem; line-height:1.7;">
      Whether you need emergency plumbing repairs or you want to earn reliable income as a service provider, E-Sheba makes it effortless, safe, and transparent.
    </p>
  </div>
</div>

<div class="container py-5">
  
  <!-- For Customers -->
  <div class="mb-5" data-aos="fade-up">
    <div class="text-center mb-5">
      <span class="badge bg-primary-subtle text-primary fw-700 px-3 py-2 rounded-pill mb-2">FOR CUSTOMERS</span>
      <h2 class="fw-900 text-dark">Getting Service Done in 4 Steps</h2>
    </div>

    <div class="row g-4">
      <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
        <div class="bg-white rounded-4 p-4 text-center h-100 border shadow-sm hover-lift">
          <div class="stat-icon mx-auto rounded-4 mb-3 d-flex align-items-center justify-content-center" style="background:#EFF6FF; color:#1A73E8; width:70px; height:70px; font-size:26px;">
            <i class="fa fa-search"></i>
          </div>
          <h5 class="fw-800 text-dark mb-2">1. Search & Filter</h5>
          <p class="text-muted" style="font-size:.88rem; line-height:1.6;">
            Select your service trade (e.g., Electrician, AC Repair) and choose your district. Review technician bios, reviews, and hourly rates.
          </p>
        </div>
      </div>

      <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
        <div class="bg-white rounded-4 p-4 text-center h-100 border shadow-sm hover-lift">
          <div class="stat-icon mx-auto rounded-4 mb-3 d-flex align-items-center justify-content-center" style="background:#FEF3C7; color:#D97706; width:70px; height:70px; font-size:26px;">
            <i class="fa fa-comments"></i>
          </div>
          <h5 class="fw-800 text-dark mb-2">2. Chat & Masked Call</h5>
          <p class="text-muted" style="font-size:.88rem; line-height:1.6;">
            Send photos of the damaged part in real-time or place an in-app voice call powered by Agora WebRTC without sharing phone numbers.
          </p>
        </div>
      </div>

      <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
        <div class="bg-white rounded-4 p-4 text-center h-100 border shadow-sm hover-lift">
          <div class="stat-icon mx-auto rounded-4 mb-3 d-flex align-items-center justify-content-center" style="background:#ECFDF5; color:#059669; width:70px; height:70px; font-size:26px;">
            <i class="fa fa-calendar-check"></i>
          </div>
          <h5 class="fw-800 text-dark mb-2">3. Book with Address</h5>
          <p class="text-muted" style="font-size:.88rem; line-height:1.6;">
            Pick your preferred schedule date and time, enter your home address, and confirm. The technician receives an instant job alert.
          </p>
        </div>
      </div>

      <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
        <div class="bg-white rounded-4 p-4 text-center h-100 border shadow-sm hover-lift">
          <div class="stat-icon mx-auto rounded-4 mb-3 d-flex align-items-center justify-content-center" style="background:#FEE2E2; color:#DC2626; width:70px; height:70px; font-size:26px;">
            <i class="fa fa-star"></i>
          </div>
          <h5 class="fw-800 text-dark mb-2">4. Pay & Leave Review</h5>
          <p class="text-muted" style="font-size:.88rem; line-height:1.6;">
            Technician completes the job. Pay cash or digital via SSLCommerz (bKash/Nagad), and rate the technician to help other users.
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- For Technicians -->
  <div class="mt-5 pt-4 border-top" data-aos="fade-up">
    <div class="text-center mb-5">
      <span class="badge bg-warning-subtle text-warning-emphasis fw-700 px-3 py-2 rounded-pill mb-2">FOR SERVICE TECHNICIANS</span>
      <h2 class="fw-900 text-dark">How to Grow Your Income as a Provider</h2>
    </div>

    <div class="row g-4 align-items-center">
      <div class="col-lg-6" data-aos="fade-right">
        <div class="d-flex flex-column gap-3">
          
          <div class="d-flex gap-3 p-3 bg-white rounded-3 border">
            <div class="stat-icon rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px; height:44px; font-size:18px;">1</div>
            <div>
              <h6 class="fw-800 mb-1">Join & Upload Your National ID</h6>
              <p class="text-muted mb-0" style="font-size:.85rem">Register as a worker, choose your trade categories, working area, and upload your NID card for admin verification.</p>
            </div>
          </div>

          <div class="d-flex gap-3 p-3 bg-white rounded-3 border">
            <div class="stat-icon rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px; height:44px; font-size:18px;">2</div>
            <div>
              <h6 class="fw-800 mb-1">Activate Your ৳399/Month Subscription</h6>
              <p class="text-muted mb-0" style="font-size:.85rem">Maintain profile visibility in customer search results across your district by activating a flat ৳399 monthly subscription via SSLCommerz.</p>
            </div>
          </div>

          <div class="d-flex gap-3 p-3 bg-white rounded-3 border">
            <div class="stat-icon rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px; height:44px; font-size:18px;">3</div>
            <div>
              <h6 class="fw-800 mb-1">Receive & Accept Direct Bookings</h6>
              <p class="text-muted mb-0" style="font-size:.85rem">Get instant alerts on your dashboard when customers book your service. One click to Accept, Start, and Complete jobs.</p>
            </div>
          </div>

        </div>
      </div>

      <div class="col-lg-6 text-center" data-aos="fade-left">
        <div class="p-5 rounded-4 text-white shadow-lg" style="background: linear-gradient(135deg, #1E3A8A 0%, #0284C7 100%);">
          <i class="fa fa-wallet fa-3x mb-3 text-warning"></i>
          <h3 class="fw-800 mb-2">Ready to Start Earning?</h3>
          <p class="text-white-50 mb-4" style="line-height:1.7;">Join 2,400+ certified providers who receive jobs every day across Bangladesh.</p>
          <a href="register.php?role=worker" class="btn btn-warning fw-800 rounded-pill px-4 py-3 shadow" style="background:#FB8500; border:none; color:#fff;">
            Register as a Provider Free
          </a>
        </div>
      </div>

    </div>
  </div>

</div>

<!-- Footer -->
<footer class="footer-main mt-5">
  <div class="container text-center">
    <img src="images/logo-white.svg" alt="E-Sheba" style="height:40px; margin-bottom:12px;">
    <p class="mb-1 text-white-50">© 2026 E-Sheba · Department of Computer Science & Engineering · World University of Bangladesh</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({duration:800, once:true});</script>
</body>
</html>
