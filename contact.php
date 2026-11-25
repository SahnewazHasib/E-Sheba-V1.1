<?php
// Contact Page — E-Sheba
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';
Auth::start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Support — <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar-esheba">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="brand text-decoration-none">🔧 <?= APP_NAME ?></a>
    <div class="d-flex align-items-center gap-2">
      <a href="index.php" class="btn btn-outline-secondary rounded-pill px-3" style="font-size:.85rem">Home</a>
    </div>
  </div>
</nav>

<div class="container py-5" style="max-width:700px">
  <div class="bg-white rounded-4 border p-4 p-md-5">
    <h3 class="fw-800 mb-2">Get in Touch with E-Sheba</h3>
    <p class="text-muted mb-4">Have an inquiry, feedback, or need dispute resolution assistance?</p>

    <div class="row g-3 mb-4">
      <div class="col-sm-6">
        <div class="p-3 rounded-3 bg-light">
          <i class="fa fa-envelope text-primary mb-1"></i>
          <div class="fw-700">Email Support</div>
          <div class="text-muted" style="font-size:.85rem">support@e-sheba.com</div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="p-3 rounded-3 bg-light">
          <i class="fa fa-phone text-success mb-1"></i>
          <div class="fw-700">Helpline</div>
          <div class="text-muted" style="font-size:.85rem">+880 17458-22558</div>
        </div>
      </div>
    </div>

    <form onsubmit="alert('Thank you! Your message has been received.'); return false;">
      <div class="mb-3">
        <label class="form-label fw-600">Your Name</label>
        <input type="text" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-600">Email Address</label>
        <input type="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-600">Message / Dispute Details</label>
        <textarea class="form-control" rows="4" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary rounded-pill px-4 fw-700">Send Message</button>
    </form>
  </div>
</div>

</body>
</html>
