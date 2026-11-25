<?php
// ============================================================
// Customer Dashboard — E-Sheba
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/config.php';

Auth::requireRole('customer');
$myId   = Auth::id();
$myName = $_SESSION['user_name'] ?? 'Customer';

// Customer details
$customer = DB::fetchOne("SELECT * FROM users WHERE id = ?", [$myId]);

// Booking statistics
$totalBookings  = DB::fetchOne("SELECT COUNT(*) c FROM bookings WHERE customer_id = ?", [$myId])['c'];
$activeBookings = DB::fetchOne("SELECT COUNT(*) c FROM bookings WHERE customer_id = ? AND status IN ('pending','accepted','in_progress')", [$myId])['c'];
$completedJobs  = DB::fetchOne("SELECT COUNT(*) c FROM bookings WHERE customer_id = ? AND status = 'completed'", [$myId])['c'];
$totalSpent     = DB::fetchOne("SELECT COALESCE(SUM(total_amount),0) c FROM bookings WHERE customer_id = ? AND pay_status = 'paid'", [$myId])['c'];
$unreadMessages = DB::fetchOne("SELECT COUNT(*) c FROM messages WHERE receiver_id = ? AND is_read = 0", [$myId])['c'];

// Fetch all bookings for this customer
$bookings = DB::fetchAll(
    "SELECT b.*, 
            wu.name as worker_name, 
            wu.email as worker_email, 
            wu.phone as worker_phone, 
            wu.avatar as worker_avatar,
            wr.id as worker_table_id,
            wr.working_area,
            wr.district,
            wr.avg_rating,
            c.name as category_name,
            c.icon as category_icon,
            r.id as review_id,
            r.rating as review_rating
     FROM bookings b
     JOIN workers wr ON b.worker_id = wr.id
     JOIN users wu ON wr.user_id = wu.id
     JOIN categories c ON b.category_id = c.id
     LEFT JOIN reviews r ON b.id = r.booking_id
     WHERE b.customer_id = ?
     ORDER BY b.created_at DESC",
    [$myId]
);

// Featured categories for quick booking
$categories = DB::fetchAll("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Dashboard — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .star-rating i { font-size: 1.5rem; color: #d1d5db; cursor: pointer; transition: color .2s; }
    .star-rating i.active, .star-rating i.hovered { color: #fbbc05; }
    .booking-card { background: #fff; border-radius: var(--radius-md); border: 1px solid var(--border); transition: transform .2s, box-shadow .2s; }
    .booking-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-sm); }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-esheba">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="../index.php" class="brand text-decoration-none">🔧 <?= APP_NAME ?></a>
    <div class="d-flex align-items-center gap-3">
      <a href="../workers.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 d-none d-md-inline-block">
        <i class="fa fa-search me-1"></i>Find Providers
      </a>
      <a href="../chat.php" class="btn btn-link position-relative text-muted p-2" title="Messages">
        <i class="fa fa-comment-dots fs-5"></i>
        <?php if ($unreadMessages > 0): ?>
          <span class="notification-badge"><?= $unreadMessages ?></span>
        <?php endif; ?>
      </a>
      <div class="dropdown">
        <button class="btn d-flex align-items-center gap-2 border rounded-pill px-3 py-1" data-bs-toggle="dropdown">
          <?php if (!empty($customer['avatar']) && file_exists(__DIR__ . '/../' . $customer['avatar'])): ?>
            <img src="../<?= htmlspecialchars($customer['avatar']) ?>" alt="Avatar" class="rounded-circle shadow-sm" style="width:32px;height:32px;object-fit:cover;border:1px solid #1a73e8">
          <?php else: ?>
            <div class="chat-avatar" style="width:32px;height:32px;font-size:.75rem"><?= strtoupper($myName[0]) ?></div>
          <?php endif; ?>
          <span class="d-none d-md-inline" style="font-size:.88rem;font-weight:600"><?= htmlspecialchars($myName) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" style="border-radius:12px">
          <li><span class="dropdown-item-text text-muted" style="font-size:.8rem">Customer Account</span></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item py-2" href="../workers.php"><i class="fa fa-search me-2 text-primary"></i>Find Service</a></li>
          <li><a class="dropdown-item py-2" href="../chat.php"><i class="fa fa-comments me-2 text-success"></i>Messages</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item py-2 text-danger" href="../api/logout.php"><i class="fa fa-sign-out-alt me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="container py-4">

  <!-- Welcome Banner -->
  <div class="p-4 mb-4 rounded-4" style="background:linear-gradient(135deg, #1a73e8, #0d47a1);color:#fff">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h3 class="fw-800 mb-1">Welcome back, <?= htmlspecialchars($myName) ?>! 👋</h3>
        <p class="mb-0 opacity-85" style="font-size:.95rem">
          Manage your booked home services, contact verified service givers, and pay securely.
        </p>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="../workers.php" class="btn btn-warning rounded-pill px-4 fw-700 shadow-sm">
          <i class="fa fa-plus me-1"></i> Book New Service
        </a>
      </div>
    </div>
  </div>

  <!-- Metric Cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:#e8f0fe;color:#1a73e8">
          <i class="fa fa-calendar-check"></i>
        </div>
        <div>
          <div class="stat-num"><?= $totalBookings ?></div>
          <div class="stat-label">Total Bookings</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:#fef7e0;color:#fbbc05">
          <i class="fa fa-clock"></i>
        </div>
        <div>
          <div class="stat-num"><?= $activeBookings ?></div>
          <div class="stat-label">Active / In-Progress</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:#e6f4ea;color:#34a853">
          <i class="fa fa-check-circle"></i>
        </div>
        <div>
          <div class="stat-num"><?= $completedJobs ?></div>
          <div class="stat-label">Completed Jobs</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card">
        <div class="stat-icon" style="background:#fce8e6;color:#ea4335">
          <i class="fa fa-wallet"></i>
        </div>
        <div>
          <div class="stat-num">৳<?= number_format($totalSpent) ?></div>
          <div class="stat-label">Total Paid</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content Row -->
  <div class="row g-4">
    <!-- Left Column: Bookings List -->
    <div class="col-lg-8">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-700 mb-0"><i class="fa fa-clipboard-list me-2 text-primary"></i>My Bookings</h5>
        <div class="btn-group btn-group-sm" role="group">
          <button type="button" class="btn btn-outline-secondary active" onclick="filterBookings('all', this)">All</button>
          <button type="button" class="btn btn-outline-secondary" onclick="filterBookings('active', this)">Active</button>
          <button type="button" class="btn btn-outline-secondary" onclick="filterBookings('completed', this)">Completed</button>
        </div>
      </div>

      <?php if (empty($bookings)): ?>
        <div class="text-center py-5 bg-white rounded-4 border">
          <i class="fa fa-calendar-times fa-3x text-muted mb-3" style="opacity:.4"></i>
          <h5 class="fw-700">No bookings yet!</h5>
          <p class="text-muted" style="max-width:380px;margin:0 auto 20px;font-size:.9rem">
            Need an electrician, plumber, or technician? Browse our verified local providers.
          </p>
          <a href="../workers.php" class="btn btn-primary rounded-pill px-4">Browse Providers</a>
        </div>
      <?php else: ?>
        <div class="d-flex flex-column gap-3" id="bookingsContainer">
          <?php foreach ($bookings as $b): 
            $statusGroup = in_array($b['status'], ['pending','accepted','in_progress']) ? 'active' : ($b['status'] === 'completed' ? 'completed' : 'cancelled');
            $statusBadge = match($b['status']) {
              'pending'     => 'bg-warning text-dark',
              'accepted'    => 'bg-info text-dark',
              'in_progress' => 'bg-primary text-white',
              'completed'   => 'bg-success text-white',
              'cancelled'   => 'bg-danger text-white',
              default       => 'bg-secondary text-white'
            };
          ?>
            <div class="booking-card p-3 p-md-4" data-status="<?= $statusGroup ?>">
              <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="chat-avatar" style="width:48px;height:48px;font-size:1.1rem;background:#e8f0fe;color:#1a73e8">
                    <?= strtoupper($b['worker_name'][0]) ?>
                  </div>
                  <div>
                    <h6 class="fw-700 mb-0">
                      <a href="../worker-profile.php?id=<?= $b['worker_table_id'] ?>" class="text-dark text-decoration-none hover-primary">
                        <?= htmlspecialchars($b['worker_name']) ?>
                      </a>
                      <span class="badge bg-light text-muted border ms-1" style="font-size:.75rem">
                        <i class="fa <?= $b['category_icon'] ?? 'fa-wrench' ?> me-1"></i><?= htmlspecialchars($b['category_name']) ?>
                      </span>
                    </h6>
                    <div style="font-size:.82rem;color:var(--muted)">
                      <i class="fa fa-map-marker-alt me-1 text-danger"></i><?= htmlspecialchars($b['working_area'] . ', ' . $b['district']) ?>
                    </div>
                  </div>
                </div>
                <div class="text-end">
                  <span class="badge <?= $statusBadge ?> px-3 py-2 rounded-pill" style="font-size:.8rem">
                    <?= ucfirst(str_replace('_',' ',$b['status'])) ?>
                  </span>
                  <div class="mt-1 fw-700" style="font-size:.95rem">
                    <?= $b['total_amount'] ? '৳' . number_format($b['total_amount']) : 'Amount TBD' ?>
                  </div>
                </div>
              </div>

              <!-- Booking Details -->
              <div class="p-3 rounded-3 mb-3" style="background:#f8f9fc;font-size:.88rem">
                <div class="row g-2">
                  <div class="col-sm-6">
                    <span class="text-muted"><i class="fa fa-calendar me-1"></i>Scheduled:</span>
                    <strong><?= $b['scheduled_at'] ? date('d M Y, h:i A', strtotime($b['scheduled_at'])) : 'Flexible / Pending' ?></strong>
                  </div>
                  <div class="col-sm-6">
                    <span class="text-muted"><i class="fa fa-money-bill me-1"></i>Payment:</span>
                    <?php if ($b['pay_status'] === 'paid'): ?>
                      <span class="badge bg-success"><i class="fa fa-check-circle me-1"></i>Paid (<?= strtoupper($b['pay_method'] ?? 'Online') ?>)</span>
                    <?php elseif ($b['pay_method'] === 'cash' && $b['pay_status'] === 'cash_pending'): ?>
                      <span class="badge bg-warning text-dark"><i class="fa fa-hourglass-half me-1"></i>Cash Paid (Awaiting Worker Confirmation)</span>
                    <?php elseif ($b['pay_method'] === 'cash'): ?>
                      <span class="badge bg-secondary"><i class="fa fa-money-bill me-1"></i>Cash on Service</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Unpaid Online</span>
                    <?php endif; ?>
                  </div>
                  <?php if ($b['description']): ?>
                    <div class="col-12 mt-2">
                      <span class="text-muted"><i class="fa fa-info-circle me-1"></i>Work Note:</span>
                      <?= htmlspecialchars($b['description']) ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Action Bar -->
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top">
                <div class="d-flex gap-2">
                  <!-- Chat Button -->
                  <a href="../chat.php?to=<?= $b['worker_id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                    <i class="fa fa-comment-dots me-1"></i>Message
                  </a>
                  <!-- Call Button (Voice Call via Agora) -->
                  <button class="btn btn-sm btn-outline-success rounded-pill" onclick="startCall(<?= $b['worker_id'] ?>, '<?= htmlspecialchars(addslashes($b['worker_name'])) ?>')">
                    <i class="fa fa-phone me-1"></i>Call
                  </button>
                </div>

                <div class="d-flex gap-2">
                  <!-- Cancel pending booking -->
                  <?php if ($b['status'] === 'pending'): ?>
                    <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="cancelBooking(<?= $b['id'] ?>)">
                      Cancel
                    </button>
                  <?php endif; ?>

                  <!-- Cash Payment Confirmation Button for Customer -->
                  <?php if ($b['pay_method'] === 'cash'): ?>
                    <?php if ($b['pay_status'] === 'unpaid' && in_array($b['status'], ['accepted', 'in_progress', 'completed'])): ?>
                      <button class="btn btn-sm btn-success rounded-pill fw-700 shadow-sm" onclick="markPaidInCash(<?= $b['id'] ?>)">
                        <i class="fa fa-hand-holding-usd me-1"></i>I Paid in Cash
                      </button>
                    <?php elseif ($b['pay_status'] === 'cash_pending'): ?>
                      <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-2 rounded-pill fw-600" style="font-size:.78rem">
                        <i class="fa fa-clock me-1 text-warning"></i>Waiting for Technician to Confirm Cash
                      </span>
                    <?php elseif ($b['pay_status'] === 'paid'): ?>
                      <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill fw-600" style="font-size:.78rem">
                        <i class="fa fa-check-circle me-1"></i>Cash Paid & Verified
                      </span>
                    <?php endif; ?>
                  <?php elseif ($b['pay_status'] !== 'paid' && in_array($b['status'], ['accepted', 'completed']) && $b['total_amount'] > 0): ?>
                    <!-- Pay Online via SSLCommerz if completed or accepted and unpaid -->
                    <a href="../sslcommerz/init.php?type=booking&booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-success rounded-pill fw-600">
                      <i class="fa fa-credit-card me-1"></i>Pay ৳<?= number_format($b['total_amount']) ?>
                    </a>
                  <?php endif; ?>

                  <!-- Leave Review if completed -->
                  <?php if ($b['status'] === 'completed'): ?>
                    <a href="../invoice.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill" title="Print Invoice">
                      <i class="fa fa-file-invoice me-1"></i>Invoice
                    </a>
                    <?php if ($b['review_id']): ?>
                      <span class="badge bg-light text-warning border align-self-center py-2 px-3" style="font-size:.82rem">
                        Reviewed <?= str_repeat('★', $b['review_rating']) ?>
                      </span>
                    <?php else: ?>
                      <button class="btn btn-sm btn-warning text-dark rounded-pill fw-700" onclick="openReviewModal(<?= $b['id'] ?>, <?= $b['worker_table_id'] ?>, '<?= htmlspecialchars(addslashes($b['worker_name'])) ?>')">
                        <i class="fa fa-star me-1"></i>Rate & Review
                      </button>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Right Column: Quick Categories & Customer Profile -->
    <div class="col-lg-4">
      
      <!-- Profile Card -->
      <div class="bg-white rounded-4 border p-4 mb-4 shadow-sm">
        <div class="d-flex align-items-center gap-3 mb-3">
          <?php if (!empty($customer['avatar']) && file_exists(__DIR__ . '/../' . $customer['avatar'])): ?>
            <img src="../<?= htmlspecialchars($customer['avatar']) ?>" alt="Avatar" class="rounded-circle shadow-sm" style="width:58px;height:58px;object-fit:cover;border:2px solid #1a73e8">
          <?php else: ?>
            <div class="chat-avatar" style="width:58px;height:58px;font-size:1.3rem;background:linear-gradient(135deg, #1A73E8, #0D47A1);color:#fff">
              <?= strtoupper($myName[0]) ?>
            </div>
          <?php endif; ?>
          <div>
            <h6 class="fw-800 mb-0"><?= htmlspecialchars($myName) ?></h6>
            <div style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($customer['email']) ?></div>
            <span class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size:.7rem">
              <i class="fa fa-check-circle me-1"></i>Verified Customer
            </span>
          </div>
        </div>
        <button class="btn btn-outline-primary btn-sm rounded-pill w-100 mb-3" data-bs-toggle="modal" data-bs-target="#customerPhotoModal">
          <i class="fa fa-camera me-1"></i>Change Profile Picture
        </button>
        <div class="border-top pt-3" style="font-size:.88rem">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted"><i class="fa fa-phone me-1"></i>Phone:</span>
            <strong><?= htmlspecialchars($customer['phone'] ?? 'Not set') ?></strong>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted"><i class="fa fa-calendar me-1"></i>Member Since:</span>
            <strong><?= date('M Y', strtotime($customer['created_at'])) ?></strong>
          </div>
        </div>
      </div>

      <!-- Quick Services Directory -->
      <div class="bg-white rounded-4 border p-4">
        <h6 class="fw-700 mb-3"><i class="fa fa-th-large me-2 text-primary"></i>Popular Services</h6>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ($categories as $cat): ?>
            <a href="../workers.php?category_id=<?= $cat['id'] ?>" class="badge bg-light text-dark border p-2 text-decoration-none hover-primary" style="font-size:.82rem">
              <i class="fa <?= $cat['icon'] ?? 'fa-tools' ?> text-primary me-1"></i><?= htmlspecialchars($cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="mt-3 text-center">
          <a href="../workers.php" class="text-primary text-decoration-none fw-600" style="font-size:.85rem">
            View all 25 categories →
          </a>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ══ Review Modal ══ -->
<div class="modal fade" id="reviewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-700">Rate Service Provider</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted" style="font-size:.88rem">How was your experience with <strong id="modalWorkerName">Provider</strong>?</p>
        
        <div class="text-center py-3">
          <div class="star-rating d-inline-flex gap-2" id="starSelector">
            <i class="fa fa-star" data-val="1"></i>
            <i class="fa fa-star" data-val="2"></i>
            <i class="fa fa-star" data-val="3"></i>
            <i class="fa fa-star" data-val="4"></i>
            <i class="fa fa-star" data-val="5"></i>
          </div>
          <div class="fw-600 mt-2 text-primary" id="ratingText">Select rating</div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-600" style="font-size:.85rem">Your Feedback / Review</label>
          <textarea class="form-control" id="reviewComment" rows="3" placeholder="Punctuality, quality of work, polite behavior..."></textarea>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning fw-700 rounded-pill px-4" id="submitReviewBtn" onclick="submitReview()">Submit Review</button>
      </div>
    </div>
  </div>
</div>

<!-- Call Overlay (for Agora voice calls) -->
<div class="call-overlay" id="callOverlay" style="display:none">
  <div class="call-avatar" id="callAvatar">📞</div>
  <div class="call-name" id="callName">Service Provider</div>
  <div class="call-status" id="callStatus">Connecting...</div>
  <div class="call-controls">
    <button class="call-btn hangup" onclick="endCall()"><i class="fa fa-phone-slash"></i></button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.20.0.js"></script>
<script src="../js/call.js"></script>
<script>
let currentBookingId = 0;
let currentWorkerId = 0;
let selectedRating = 5;

// Review Modal Handlers
function openReviewModal(bookingId, workerId, workerName) {
  currentBookingId = bookingId;
  currentWorkerId = workerId;
  document.getElementById('modalWorkerName').innerText = workerName;
  setRating(5);
  new bootstrap.Modal(document.getElementById('reviewModal')).show();
}

const stars = document.querySelectorAll('#starSelector i');
const ratingTexts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent!'];

stars.forEach(star => {
  star.addEventListener('click', function() {
    selectedRating = parseInt(this.dataset.val);
    setRating(selectedRating);
  });
  star.addEventListener('mouseover', function() {
    const val = parseInt(this.dataset.val);
    stars.forEach(s => s.classList.toggle('hovered', parseInt(s.dataset.val) <= val));
  });
  star.addEventListener('mouseout', function() {
    stars.forEach(s => s.classList.remove('hovered'));
  });
});

function setRating(val) {
  selectedRating = val;
  stars.forEach(s => s.classList.toggle('active', parseInt(s.dataset.val) <= val));
  document.getElementById('ratingText').innerText = ratingTexts[val] || '';
}

async function submitReview() {
  const comment = document.getElementById('reviewComment').value.trim();
  const btn = document.getElementById('submitReviewBtn');
  btn.disabled = true;
  btn.innerText = 'Submitting...';

  try {
    const res = await fetch('../api/submit-review.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        booking_id: currentBookingId,
        worker_id: currentWorkerId,
        rating: selectedRating,
        comment: comment
      })
    });
    const data = await res.json();
    if (data.success) {
      alert('Thank you! Your review has been published.');
      location.reload();
    } else {
      alert(data.error || 'Failed to submit review.');
      btn.disabled = false;
      btn.innerText = 'Submit Review';
    }
  } catch (e) {
    alert('Network error. Please try again.');
    btn.disabled = false;
    btn.innerText = 'Submit Review';
  }
}

// Cancel booking
async function cancelBooking(id) {
  if (!confirm('Are you sure you want to cancel this booking?')) return;
  try {
    const res = await fetch('../api/update-booking.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ booking_id: id, status: 'cancelled' })
    });
    const data = await res.json();
    if (data.success) {
      location.reload();
    } else {
      alert(data.error || 'Failed to cancel booking.');
    }
  } catch (e) {
    alert('Network error.');
  }
}

// Filter tabs
function filterBookings(type, btn) {
  document.querySelectorAll('.btn-group button').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const cards = document.querySelectorAll('.booking-card');
  cards.forEach(card => {
    if (type === 'all' || card.dataset.status === type) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }
  });
}

// Call helper
function startCall(peerUserId, peerName) {
  if (typeof startVoiceCall === 'function') {
    startVoiceCall(peerUserId, peerName);
  } else {
    alert('Voice calling is initializing. Please try again in a moment.');
  }
}

// ── Cash Payment Confirmation (Customer Side) ────────────────
function markPaidInCash(bookingId) {
  if (!confirm('Did you hand over cash payment to the service provider?')) return;
  $.ajax({
    url: '../api/confirm-cash-payment.php',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ booking_id: bookingId, action: 'customer_paid' }),
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => location.reload(), 1200);
      } else {
        showToast(res.error || 'Failed to update payment.', 'error');
      }
    },
    error: function() {
      showToast('Network error while processing cash confirmation.', 'error');
    }
  });
}

// ── Customer Avatar Upload ──────────────────────────────────
function previewCustomerAvatar(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
      alert('Selected file is too large! Maximum limit is 5MB.');
      input.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('customerAvatarPreview').src = e.target.result;
      document.getElementById('savePhotoBtn').disabled = false;
    };
    reader.readAsDataURL(file);
  }
}

function uploadCustomerAvatar() {
  const fileInput = document.getElementById('customerAvatarInput');
  if (!fileInput.files || !fileInput.files[0]) {
    alert('Please select an image file first.');
    return;
  }
  const formData = new FormData();
  formData.append('avatar', fileInput.files[0]);

  const btn = document.getElementById('savePhotoBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Uploading...';

  $.ajax({
    url: '../api/upload-avatar.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        showToast('Profile photo updated successfully!', 'success');
        setTimeout(() => location.reload(), 1200);
      } else {
        alert(res.error || 'Failed to upload photo.');
        btn.disabled = false;
        btn.innerHTML = 'Save Photo';
      }
    },
    error: function() {
      alert('Network error while uploading photo.');
      btn.disabled = false;
      btn.innerHTML = 'Save Photo';
    }
  });
}
</script>

<!-- ══ Customer Photo Modal ══ -->
<div class="modal fade" id="customerPhotoModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content" style="border-radius:18px;border:none">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-800">Profile Picture</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center pt-2">
        <div class="position-relative mx-auto mb-3" style="width:110px;height:110px">
          <img id="customerAvatarPreview" 
               src="<?= (!empty($customer['avatar']) && file_exists(__DIR__ . '/../' . $customer['avatar'])) ? '../' . htmlspecialchars($customer['avatar']) : 'https://ui-avatars.com/api/?name='.urlencode($myName).'&background=1A73E8&color=fff&size=128' ?>" 
               alt="Avatar Preview" 
               class="rounded-circle shadow" 
               style="width:110px;height:110px;object-fit:cover;border:3px solid #1a73e8;">
        </div>
        <p class="text-muted mb-3" style="font-size:.82rem">Upload JPG, PNG, or WEBP (Max 5MB)</p>
        <input type="file" id="customerAvatarInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="previewCustomerAvatar(this)">
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 mb-2" onclick="document.getElementById('customerAvatarInput').click()">
          <i class="fa fa-folder-open me-1"></i>Select from Device
        </button>
      </div>
      <div class="modal-footer border-0 pt-0 d-flex gap-2">
        <button type="button" class="btn btn-light rounded-pill flex-grow-1" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary rounded-pill flex-grow-1 fw-700" id="savePhotoBtn" onclick="uploadCustomerAvatar()" disabled>Save Photo</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>
