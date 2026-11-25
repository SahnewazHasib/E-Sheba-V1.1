<?php
// ============================================================
// Worker Profile Page — E-Sheba
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/config.php';
Auth::start();

$workerId = (int)($_GET['id'] ?? 0);
if (!$workerId) {
    header("Location: workers.php");
    exit;
}

// Fetch worker with user account
$worker = DB::fetchOne(
    "SELECT w.*, u.id as user_account_id, u.name, u.email, u.phone, u.avatar, u.created_at as joined_date
     FROM workers w
     JOIN users u ON w.user_id = u.id
     WHERE w.id = ?",
    [$workerId]
);

if (!$worker) {
    header("Location: workers.php");
    exit;
}

// Fetch categories
$workerCategories = DB::fetchAll(
    "SELECT c.* FROM categories c
     JOIN worker_categories wc ON c.id = wc.category_id
     WHERE wc.worker_id = ? AND c.is_active = 1",
    [$workerId]
);

// Fetch reviews
$reviews = DB::fetchAll(
    "SELECT r.*, u.name as customer_name, u.avatar as customer_avatar
     FROM reviews r
     JOIN users u ON r.customer_id = u.id
     WHERE r.worker_id = ?
     ORDER BY r.created_at DESC",
    [$workerId]
);

// Check active subscription
$subscription = DB::fetchOne("SELECT * FROM subscriptions WHERE worker_id = ? AND status = 'active' AND expires_at >= CURDATE()", [$workerId]);

$myId = Auth::id();
$isLoggedIn = Auth::isLoggedIn();
$myRole = Auth::role();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($worker['name']) ?> — Service Provider on <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar-esheba">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="brand text-decoration-none">🔧 <?= APP_NAME ?></a>
    <div class="d-flex align-items-center gap-2">
      <a href="workers.php" class="btn btn-outline-secondary rounded-pill px-3" style="font-size:.85rem">
        <i class="fa fa-arrow-left me-1"></i>All Providers
      </a>
      <?php if ($isLoggedIn): ?>
        <?php $dash = match($myRole) { 'worker'=>'worker/dashboard.php', 'admin'=>'admin/dashboard.php', default=>'customer/dashboard.php' }; ?>
        <a href="<?= $dash ?>" class="btn btn-primary rounded-pill px-3" style="font-size:.85rem">Dashboard</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary rounded-pill px-3" style="font-size:.85rem">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="row g-4">

    <!-- ══ Left Column: Profile Card ══ -->
    <div class="col-lg-4">
      <div class="bg-white rounded-4 border p-4 text-center sticky-top" style="top:90px;z-index:10">
        
        <!-- Avatar & Status -->
        <div class="position-relative d-inline-block mb-3">
          <div class="chat-avatar mx-auto" style="width:96px;height:96px;font-size:2.2rem;background:#e8f0fe;color:#1a73e8">
            <?= strtoupper($worker['name'][0]) ?>
          </div>
          <?php if ($worker['is_available']): ?>
            <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle" title="Available for work"></span>
          <?php else: ?>
            <span class="position-absolute bottom-0 end-0 p-2 bg-secondary border border-white rounded-circle" title="Offline"></span>
          <?php endif; ?>
        </div>

        <h4 class="fw-800 mb-1"><?= htmlspecialchars($worker['name']) ?></h4>
        
        <!-- Verified Badge -->
        <?php if ($worker['is_approved']): ?>
          <div class="mb-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill" style="font-size:.78rem">
              <i class="fa fa-check-circle me-1"></i>Verified Service Provider
            </span>
          </div>
        <?php endif; ?>

        <!-- Rating & Reviews -->
        <div class="d-flex align-items-center justify-content-center gap-1 mb-3 text-warning">
          <?php
          $fullStars = floor($worker['avg_rating']);
          for ($i = 0; $i < 5; $i++) {
              if ($i < $fullStars) echo '<i class="fa fa-star"></i>';
              elseif ($i == $fullStars && ($worker['avg_rating'] - $fullStars) >= 0.5) echo '<i class="fa fa-star-half-alt"></i>';
              else echo '<i class="far fa-star text-muted" style="opacity:.4"></i>';
          }
          ?>
          <span class="text-dark fw-700 ms-1" style="font-size:.95rem"><?= number_format($worker['avg_rating'], 1) ?></span>
          <span class="text-muted" style="font-size:.85rem">(<?= $worker['total_reviews'] ?> reviews)</span>
        </div>

        <!-- Hourly / Base Rate -->
        <div class="p-3 rounded-3 mb-3" style="background:#f0f7ff;border:1px solid #d0e4ff">
          <div class="text-muted" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.5px">Standard Rate</div>
          <div class="fw-800 text-primary" style="font-size:1.6rem">
            ৳<?= number_format($worker['price_per_hour']) ?> <span style="font-size:.9rem;font-weight:500;color:var(--muted)">/ service</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-grid gap-2 mb-3">
          <button class="btn btn-warning fw-700 py-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#bookingModal">
            <i class="fa fa-calendar-check me-2"></i>Book This Provider
          </button>
          
          <div class="row g-2">
            <div class="col-6">
              <a href="chat.php?to=<?= $worker['user_account_id'] ?>" class="btn btn-outline-primary w-100 rounded-pill py-2">
                <i class="fa fa-comment-dots me-1"></i>Message
              </a>
            </div>
            <div class="col-6">
              <button class="btn btn-outline-success w-100 rounded-pill py-2" onclick="startCall(<?= $worker['user_account_id'] ?>, '<?= htmlspecialchars(addslashes($worker['name'])) ?>')">
                <i class="fa fa-phone me-1"></i>Call
              </button>
            </div>
          </div>
        </div>

        <p class="text-muted mb-0" style="font-size:.78rem">
          <i class="fa fa-shield-alt me-1 text-primary"></i>Free phone masking — your real phone number remains private.
        </p>

      </div>
    </div>

    <!-- ══ Right Column: Experience, Map, Reviews ══ -->
    <div class="col-lg-8">

      <!-- Categories & Skills -->
      <div class="bg-white rounded-4 border p-4 mb-4">
        <h5 class="fw-700 mb-3"><i class="fa fa-tools me-2 text-primary"></i>Services Offered</h5>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ($workerCategories as $cat): ?>
            <span class="badge bg-light text-dark border p-2 px-3 rounded-pill" style="font-size:.85rem">
              <i class="fa <?= $cat['icon'] ?? 'fa-wrench' ?> text-primary me-1"></i>
              <?= htmlspecialchars($cat['name']) ?>
            </span>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- About & Experience -->
      <div class="bg-white rounded-4 border p-4 mb-4">
        <h5 class="fw-700 mb-3"><i class="fa fa-user-tie me-2 text-primary"></i>About & Experience</h5>
        <div class="row g-3 mb-3">
          <div class="col-sm-6">
            <div class="p-3 rounded-3 bg-light">
              <div class="text-muted" style="font-size:.8rem">Experience</div>
              <div class="fw-700" style="font-size:1.1rem"><?= $worker['experience_years'] ?> Years Experience</div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="p-3 rounded-3 bg-light">
              <div class="text-muted" style="font-size:.8rem">Service Area</div>
              <div class="fw-700" style="font-size:1.1rem"><?= htmlspecialchars($worker['thana'] . ', ' . $worker['district']) ?></div>
            </div>
          </div>
        </div>

        <div class="text-muted" style="line-height:1.7;font-size:.92rem">
          <?= nl2br(htmlspecialchars($worker['bio'] ?: 'No detailed biography provided. Contact the provider directly for specific questions or service quotes.')) ?>
        </div>
      </div>

      <!-- Location Map -->
      <div class="bg-white rounded-4 border p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h5 class="fw-700 mb-0"><i class="fa fa-map-marked-alt me-2 text-danger"></i>Service Coverage Map</h5>
          <span class="badge bg-light text-muted border"><?= htmlspecialchars($worker['working_area']) ?></span>
        </div>
        <div id="serviceMap" style="height:250px;border-radius:12px"></div>
        <div class="mt-2 text-muted" style="font-size:.8rem">
          <i class="fa fa-info-circle me-1"></i>This provider covers jobs in <strong><?= htmlspecialchars($worker['working_area'] . ', ' . $worker['thana'] . ', ' . $worker['district']) ?></strong> and surrounding areas.
        </div>
      </div>

      <!-- Verified Reviews -->
      <div class="bg-white rounded-4 border p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <h5 class="fw-700 mb-0">
            <i class="fa fa-star me-2 text-warning"></i>Customer Reviews (<?= count($reviews) ?>)
          </h5>
          <span class="fw-700 text-dark"><?= number_format($worker['avg_rating'], 1) ?> out of 5</span>
        </div>

        <?php if (empty($reviews)): ?>
          <div class="text-center py-4 text-muted">
            <i class="fa fa-comment-slash fa-2x mb-2 text-muted" style="opacity:.3"></i>
            <p class="mb-0">No reviews submitted yet. Be the first to book and rate this service giver!</p>
          </div>
        <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($reviews as $rev): ?>
              <div class="p-3 rounded-3" style="background:#f8f9fc;border:1px solid #edf0f5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <div class="chat-avatar" style="width:34px;height:34px;font-size:.8rem">
                      <?= strtoupper($rev['customer_name'][0]) ?>
                    </div>
                    <div>
                      <div class="fw-700" style="font-size:.88rem"><?= htmlspecialchars($rev['customer_name']) ?></div>
                      <div class="text-muted" style="font-size:.75rem"><?= date('d M Y', strtotime($rev['created_at'])) ?></div>
                    </div>
                  </div>
                  <div class="text-warning" style="font-size:.85rem">
                    <?= str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']) ?>
                  </div>
                </div>
                <?php if ($rev['comment']): ?>
                  <p class="mb-0 text-dark" style="font-size:.88rem;line-height:1.5">
                    "<?= htmlspecialchars($rev['comment']) ?>"
                  </p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>

    </div>
  </div>
</div>

<!-- ══ Booking Modal ══ -->
<div class="modal fade" id="bookingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-800">Book <?= htmlspecialchars($worker['name']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="bookingForm" onsubmit="submitBooking(event)">
        <input type="hidden" name="worker_id" value="<?= $worker['id'] ?>">
        
        <div class="modal-body">
          <?php if (!$isLoggedIn): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="border-radius:10px;font-size:.88rem">
              <i class="fa fa-exclamation-triangle"></i>
              <div>Please <a href="login.php?redirect=worker-profile.php?id=<?= $worker['id'] ?>" class="fw-700">Login</a> to book this provider.</div>
            </div>
          <?php endif; ?>

          <!-- Category selection -->
          <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.85rem">Select Service Type *</label>
            <select name="category_id" class="form-select" required <?= !$isLoggedIn ? 'disabled' : '' ?>>
              <?php foreach ($workerCategories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Schedule Date & Time -->
          <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.85rem">Preferred Date & Time *</label>
            <input type="datetime-local" name="scheduled_at" class="form-control" required <?= !$isLoggedIn ? 'disabled' : '' ?> min="<?= date('Y-m-d\TH:i') ?>">
          </div>

          <!-- Service Address -->
          <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.85rem">Service Address *</label>
            <input type="text" name="address" class="form-control" placeholder="House/Flat, Road, Area (e.g. Mirpur-10, Dhaka)" required <?= !$isLoggedIn ? 'disabled' : '' ?>>
          </div>

          <!-- Problem Note -->
          <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.85rem">Describe the Work / Problem</label>
            <textarea name="description" class="form-control" rows="2" placeholder="Brief details about what needs fixing..." <?= !$isLoggedIn ? 'disabled' : '' ?>></textarea>
          </div>

          <!-- Payment Option -->
          <div class="mb-3">
            <label class="form-label fw-600" style="font-size:.85rem">Payment Method</label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="pay_method" id="payCash" value="cash" checked>
                <label class="form-check-label" for="payCash" style="font-size:.88rem">Cash on Service</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="pay_method" id="paySSL" value="sslcommerz">
                <label class="form-check-label" for="paySSL" style="font-size:.88rem">SSLCommerz (Online)</label>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning fw-700 rounded-pill px-4" id="bookSubmitBtn" <?= !$isLoggedIn ? 'disabled' : '' ?>>
            Confirm Booking
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Call Overlay -->
<div class="call-overlay" id="callOverlay" style="display:none">
  <div class="call-avatar" id="callAvatar">📞</div>
  <div class="call-name" id="callName"><?= htmlspecialchars($worker['name']) ?></div>
  <div class="call-status" id="callStatus">Calling...</div>
  <div class="call-controls">
    <button class="call-btn hangup" onclick="endCall()"><i class="fa fa-phone-slash"></i></button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.20.0.js"></script>
<script src="js/call.js"></script>
<script>
// Leaflet Map Init
const lat = <?= $worker['latitude'] ? (float)$worker['latitude'] : 23.8103 ?>;
const lng = <?= $worker['longitude'] ? (float)$worker['longitude'] : 90.4125 ?>;
const map = L.map('serviceMap').setView([lat, lng], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);
L.marker([lat, lng]).addTo(map)
  .bindPopup('<b><?= htmlspecialchars(addslashes($worker['name'])) ?></b><br><?= htmlspecialchars(addslashes($worker['working_area'])) ?>')
  .openPopup();

// Booking AJAX submit
async function submitBooking(e) {
  e.preventDefault();
  const form = document.getElementById('bookingForm');
  const btn = document.getElementById('bookSubmitBtn');
  btn.disabled = true;
  btn.innerText = 'Processing...';

  const formData = new FormData(form);
  const jsonBody = {};
  formData.forEach((val, key) => jsonBody[key] = val);

  try {
    const res = await fetch('api/book-worker.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(jsonBody)
    });
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); }
    catch(e) {
      console.error('Server response:', text);
      alert('Server error. Please check you are logged in as a Customer and try again.');
      btn.disabled = false; btn.innerText = 'Confirm Booking'; return;
    }
    if (data.success) {
      alert('Booking request sent successfully! You can track it in your Customer Dashboard.');
      if (jsonBody.pay_method === 'sslcommerz') {
        window.location.href = 'sslcommerz/init.php?type=booking&booking_id=' + data.booking_id;
      } else {
        window.location.href = 'customer/dashboard.php';
      }
    } else {
      if (data.redirect) { window.location.href = data.redirect; return; }
      alert(data.error || 'Failed to submit booking.');
      btn.disabled = false;
      btn.innerText = 'Confirm Booking';
    }
  } catch (err) {
    console.error('Booking fetch error:', err);
    alert('Network error. Please check your connection and try again.');
    btn.disabled = false;
    btn.innerText = 'Confirm Booking';
  }
}

function startCall(peerUserId, peerName) {
  if (typeof startVoiceCall === 'function') {
    startVoiceCall(peerUserId, peerName);
  } else {
    alert('Voice call service is initializing. Please try again.');
  }
}
</script>
</body>
</html>
