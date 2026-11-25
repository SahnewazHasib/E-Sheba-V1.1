<?php
// ============================================================
// Official Invoice & Receipt — E-Sheba
// ============================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';
Auth::requireLogin();

$myId = Auth::id();
$role = Auth::role();

$bookingId = (int)($_GET['booking_id'] ?? 0);
$subId     = (int)($_GET['sub_id'] ?? 0);

if (!$bookingId && !$subId) {
    die("Invalid invoice request.");
}

$invoiceType = $bookingId ? 'booking' : 'subscription';
$booking = null;
$subscription = null;

if ($invoiceType === 'booking') {
    $booking = DB::fetchOne(
        "SELECT b.*, 
                cu.name as customer_name, cu.email as customer_email, cu.phone as customer_phone,
                wu.name as worker_name, wu.email as worker_email, wu.phone as worker_phone,
                w.nid_number, w.working_area, w.district,
                c.name as category_name
         FROM bookings b
         JOIN users cu ON b.customer_id = cu.id
         JOIN workers w ON b.worker_id = w.id
         JOIN users wu ON w.user_id = wu.id
         JOIN categories c ON b.category_id = c.id
         WHERE b.id = ?",
        [$bookingId]
    );

    if (!$booking) {
        die("Booking not found.");
    }

    // Security check
    if ($role !== 'admin' && $booking['customer_id'] !== $myId && $booking['worker_id'] !== $myId) {
        die("Unauthorized access to invoice.");
    }

    $invNumber = "ESHEBA-INV-" . str_pad((string)$booking['id'], 5, '0', STR_PAD_LEFT);
    $invDate   = date('d F Y', strtotime($booking['created_at']));
    $amount    = (float)($booking['total_amount'] ?: 500.00);
    $isPaid    = ($booking['pay_status'] === 'paid');
    $payMethod = strtoupper($booking['pay_method'] ?? 'CASH');
    $txnId     = $booking['pay_txn_id'] ?: 'N/A (Cash on Service)';

} else {
    $subscription = DB::fetchOne(
        "SELECT s.*, u.name as worker_name, u.email as worker_email, u.phone as worker_phone, w.nid_number, w.district
         FROM subscriptions s
         JOIN workers w ON s.worker_id = w.id
         JOIN users u ON w.user_id = u.id
         WHERE s.id = ?",
        [$subId]
    );

    if (!$subscription) {
        die("Subscription invoice not found.");
    }

    $invNumber = "ESHEBA-SUB-" . str_pad((string)$subscription['id'], 5, '0', STR_PAD_LEFT);
    $invDate   = date('d F Y', strtotime($subscription['created_at']));
    $amount    = (float)$subscription['amount'];
    $isPaid    = ($subscription['status'] === 'active');
    $payMethod = strtoupper($subscription['payment_method']);
    $txnId     = $subscription['transaction_id'] ?: 'PENDING';
}

$backUrl = match($role) {
    'worker' => 'worker/dashboard.php',
    'admin'  => 'admin/dashboard.php',
    default  => 'customer/dashboard.php'
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice <?= htmlspecialchars($invNumber) ?> — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; font-family: 'Inter', sans-serif; color: #2d3748; }
    .invoice-card {
      max-width: 820px;
      margin: 40px auto;
      background: #fff;
      border-radius: 16px;
      padding: 48px;
      box-shadow: 0 8px 30px rgba(0,0,0,.06);
      border: 1px solid #e2e8f0;
    }
    .badge-paid { background: #e6f4ea; color: #137333; font-weight: 700; padding: 6px 16px; border-radius: 50px; border: 1px solid #ceead6; font-size: .85rem; }
    .badge-unpaid { background: #fce8e6; color: #c5221f; font-weight: 700; padding: 6px 16px; border-radius: 50px; border: 1px solid #fad2cf; font-size: .85rem; }
    @media print {
      body { background: #fff; }
      .invoice-card { box-shadow: none; border: none; padding: 0; margin: 0; max-width: 100%; }
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>

<!-- Action Bar (hidden in print) -->
<div class="container no-print pt-4" style="max-width:820px">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <a href="<?= $backUrl ?>" class="btn btn-outline-secondary rounded-pill px-3">
      <i class="fa fa-arrow-left me-1"></i>Back to Dashboard
    </a>
    <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-700 shadow-sm">
      <i class="fa fa-print me-1"></i>Print / Save as PDF
    </button>
  </div>
</div>

<div class="invoice-card">
  
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
    <div>
      <div style="font-size:1.8rem;font-weight:800;color:#1a73e8;letter-spacing:-.5px">
        🔧 <?= APP_NAME ?>
      </div>
      <div class="text-muted" style="font-size:.85rem">Local Service Provider Marketplace</div>
      <div class="text-muted" style="font-size:.78rem">World University of Bangladesh · CSE 06133248</div>
    </div>
    <div class="text-end">
      <h3 class="fw-800 text-dark mb-1">INVOICE</h3>
      <div class="fw-700 text-muted" style="font-size:.9rem"><?= htmlspecialchars($invNumber) ?></div>
      <div class="mt-2">
        <?php if ($isPaid): ?>
          <span class="badge-paid"><i class="fa fa-check-circle me-1"></i>PAID RECEIPT</span>
        <?php else: ?>
          <span class="badge-unpaid"><i class="fa fa-clock me-1"></i>PAYMENT PENDING</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Billed To & Provider Info -->
  <div class="row g-4 mb-4">
    <?php if ($invoiceType === 'booking'): ?>
      <div class="col-sm-6">
        <div class="text-muted fw-700 mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Billed To (Customer):</div>
        <h6 class="fw-800 mb-1"><?= htmlspecialchars($booking['customer_name']) ?></h6>
        <div style="font-size:.88rem;color:#4a5568"><i class="fa fa-envelope me-1 text-muted"></i><?= htmlspecialchars($booking['customer_email']) ?></div>
        <div style="font-size:.88rem;color:#4a5568"><i class="fa fa-phone me-1 text-muted"></i><?= htmlspecialchars($booking['customer_phone'] ?: 'N/A') ?></div>
        <div style="font-size:.88rem;color:#4a5568"><i class="fa fa-map-marker-alt me-1 text-muted"></i><?= htmlspecialchars($booking['address']) ?></div>
      </div>
      <div class="col-sm-6">
        <div class="text-muted fw-700 mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Service Provider:</div>
        <h6 class="fw-800 mb-1"><?= htmlspecialchars($booking['worker_name']) ?></h6>
        <div style="font-size:.88rem;color:#4a5568"><i class="fa fa-tools me-1 text-muted"></i><?= htmlspecialchars($booking['category_name']) ?> Specialist</div>
        <div style="font-size:.88rem;color:#4a5568"><i class="fa fa-phone me-1 text-muted"></i><?= htmlspecialchars($booking['worker_phone']) ?></div>
        <div style="font-size:.88rem;color:#4a5568"><i class="fa fa-shield-alt me-1 text-success"></i>NID: <?= htmlspecialchars($booking['nid_number']) ?> (Verified)</div>
      </div>
    <?php else: ?>
      <div class="col-sm-6">
        <div class="text-muted fw-700 mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Subscriber (Worker):</div>
        <h6 class="fw-800 mb-1"><?= htmlspecialchars($subscription['worker_name']) ?></h6>
        <div style="font-size:.88rem;color:#4a5568"><i class="fa fa-envelope me-1 text-muted"></i><?= htmlspecialchars($subscription['worker_email']) ?></div>
        <div style="font-size:.88rem;color:#4a5568"><i class="fa fa-phone me-1 text-muted"></i><?= htmlspecialchars($subscription['worker_phone']) ?></div>
        <div style="font-size:.88rem;color:#4a5568"><i class="fa fa-shield-alt me-1 text-success"></i>NID: <?= htmlspecialchars($subscription['nid_number']) ?></div>
      </div>
      <div class="col-sm-6 text-sm-end">
        <div class="text-muted fw-700 mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px">Plan Period:</div>
        <div class="fw-700"><?= date('d M Y', strtotime($subscription['starts_at'])) ?> → <?= date('d M Y', strtotime($subscription['expires_at'])) ?></div>
        <div class="text-muted" style="font-size:.85rem">30 Days Pro Visibility</div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Itemized Table -->
  <div class="table-responsive mb-4">
    <table class="table table-bordered mb-0 align-middle">
      <thead style="background:#f8fafc;font-size:.85rem">
        <tr>
          <th>Description</th>
          <th class="text-center" style="width:140px">Date</th>
          <th class="text-end" style="width:140px">Amount (BDT)</th>
        </tr>
      </thead>
      <tbody style="font-size:.9rem">
        <?php if ($invoiceType === 'booking'): ?>
          <tr>
            <td>
              <div class="fw-700"><?= htmlspecialchars($booking['category_name']) ?> Home Service</div>
              <div class="text-muted" style="font-size:.82rem"><?= htmlspecialchars($booking['description'] ?: 'Standard service appointment and diagnostics.') ?></div>
            </td>
            <td class="text-center"><?= $booking['scheduled_at'] ? date('d M Y', strtotime($booking['scheduled_at'])) : $invDate ?></td>
            <td class="text-end fw-700">৳<?= number_format($amount, 2) ?></td>
          </tr>
        <?php else: ?>
          <tr>
            <td>
              <div class="fw-700">Monthly Service Provider Directory Subscription</div>
              <div class="text-muted" style="font-size:.82rem">30-day listing in customer searches, incoming bookings, Pusher chat, and Agora voice calling.</div>
            </td>
            <td class="text-center"><?= $invDate ?></td>
            <td class="text-end fw-700">৳<?= number_format($amount, 2) ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
      <tfoot style="background:#f8fafc">
        <tr>
          <td colspan="2" class="text-end fw-700">Total Payable:</td>
          <td class="text-end fw-800 text-primary" style="font-size:1.15rem">৳<?= number_format($amount, 2) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <!-- Payment Details Box -->
  <div class="p-3 rounded-3 mb-4" style="background:#f8fafc;border:1px solid #edf2f7;font-size:.85rem">
    <div class="row g-2">
      <div class="col-sm-4">
        <span class="text-muted">Payment Method:</span>
        <strong class="d-block"><?= htmlspecialchars($payMethod) ?></strong>
      </div>
      <div class="col-sm-4">
        <span class="text-muted">Transaction ID:</span>
        <code class="d-block text-dark fw-700"><?= htmlspecialchars($txnId) ?></code>
      </div>
      <div class="col-sm-4">
        <span class="text-muted">Issue Date:</span>
        <strong class="d-block"><?= $invDate ?></strong>
      </div>
    </div>
  </div>

  <!-- Footer Notice -->
  <div class="text-center border-top pt-4 text-muted" style="font-size:.8rem">
    <p class="mb-1">Thank you for using <strong>E-Sheba</strong>. This is a computer-generated invoice and requires no physical signature.</p>
    <p class="mb-0">Support & Disputes: support@e-sheba.com · World University of Bangladesh (WUB)</p>
  </div>

</div>

</body>
</html>
