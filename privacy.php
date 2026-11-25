<?php
// Privacy Policy — E-Sheba
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Privacy Policy — <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body style="background:#f4f6fb">
<div class="container py-5" style="max-width:800px">
  <div class="bg-white rounded-4 border p-5">
    <h3 class="fw-800 mb-3">Privacy & Data Protection</h3>
    <p class="text-muted">Last updated: October 2026</p>
    <hr>
    <h6>1. Personal Identification & NID Security</h6>
    <p class="text-muted" style="font-size:.9rem">Service provider National Identity (NID) numbers are stored securely and used exclusively for identity verification. We never sell personal data.</p>
    <h6>2. Phone Number Masking</h6>
    <p class="text-muted" style="font-size:.9rem">To protect personal privacy, in-app voice calling uses the Agora Web SDK, allowing customers and service givers to communicate without revealing raw personal telephone numbers.</p>
    <h6>3. Payment Security</h6>
    <p class="text-muted" style="font-size:.9rem">Digital transactions are processed securely through SSLCommerz's PCI-DSS compliant payment gateway.</p>
    <a href="index.php" class="btn btn-outline-primary rounded-pill mt-3">Back to Home</a>
  </div>
</div>
</body>
</html>
