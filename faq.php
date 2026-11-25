<?php
// FAQ Page — E-Sheba
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Frequently Asked Questions — <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body style="background:#f4f6fb">
<div class="container py-5" style="max-width:800px">
  <div class="bg-white rounded-4 border p-5">
    <h3 class="fw-800 mb-4 text-center">Frequently Asked Questions</h3>

    <div class="accordion" id="faqAccordion">
      <div class="accordion-item mb-2 border rounded-3 overflow-hidden">
        <h2 class="accordion-header">
          <button class="accordion-button fw-700" type="button" data-bs-toggle="collapse" data-bs-target="#q1">
            How does worker verification work?
          </button>
        </h2>
        <div id="q1" class="accordion-collapse collapse show">
          <div class="accordion-body text-muted" style="font-size:.9rem">
            Every service provider provides their National Identity (NID) card number during registration, which is reviewed and approved by the E-Sheba administrative team before their profile displays the verified badge.
          </div>
        </div>
      </div>

      <div class="accordion-item mb-2 border rounded-3 overflow-hidden">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed fw-700" type="button" data-bs-toggle="collapse" data-bs-target="#q2">
            What is the ৳399 monthly provider fee?
          </button>
        </h2>
        <div id="q2" class="accordion-collapse collapse">
          <div class="accordion-body text-muted" style="font-size:.9rem">
            The ৳399/month subscription keeps a service giver's profile active in customer searches, covers real-time chat/voice call infrastructure, and connects them directly with customers without charging high per-job commissions.
          </div>
        </div>
      </div>

      <div class="accordion-item mb-2 border rounded-3 overflow-hidden">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed fw-700" type="button" data-bs-toggle="collapse" data-bs-target="#q3">
            Can I install E-Sheba on my mobile phone?
          </button>
        </h2>
        <div id="q3" class="accordion-collapse collapse">
          <div class="accordion-body text-muted" style="font-size:.9rem">
            Yes! E-Sheba is a Progressive Web App (PWA). Open the site in Chrome or Safari on your smartphone, and tap <strong>"Add to Home Screen"</strong> to install it as an app with an icon and full-screen experience.
          </div>
        </div>
      </div>
    </div>

    <div class="text-center mt-4">
      <a href="index.php" class="btn btn-outline-primary rounded-pill">Back to Home</a>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
