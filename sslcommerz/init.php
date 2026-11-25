<?php
// ============================================================
// SSLCommerz Payment Initialization — E-Sheba (Person 4)
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
Auth::requireLogin();

$type = $_GET['type'] ?? 'subscription'; // 'subscription' | 'booking'
$myId = Auth::id();
$user = DB::fetchOne("SELECT * FROM users WHERE id = ?", [$myId]);

$amount = 0;
$tranId = '';
$productName = '';
$refId = 0;

if ($type === 'subscription') {
    Auth::requireRole('worker');
    $worker = DB::fetchOne("SELECT * FROM workers WHERE user_id = ?", [$myId]);
    if (!$worker) { die("Worker profile not found."); }
    $refId = $worker['id'];
    $amount = SUBSCRIPTION_PRICE; // ৳399
    $tranId = 'SUB_' . $worker['id'] . '_' . time();
    $productName = 'E-Sheba Monthly Worker Subscription';

    // Insert pending subscription record
    DB::query(
        "INSERT INTO subscriptions (worker_id, amount, currency, status, payment_method, transaction_id)
         VALUES (?, ?, 'BDT', 'pending', 'sslcommerz', ?)",
        [$worker['id'], $amount, $tranId]
    );

} elseif ($type === 'booking') {
    $bookingId = (int)($_GET['booking_id'] ?? 0);
    $booking = DB::fetchOne("SELECT b.*, c.name as category_name FROM bookings b JOIN categories c ON b.category_id=c.id WHERE b.id = ?", [$bookingId]);
    if (!$booking || ($booking['customer_id'] !== $myId && Auth::role() !== 'admin')) {
        die("Invalid booking.");
    }
    $refId = $booking['id'];
    $amount = $booking['total_amount'] > 0 ? (float)$booking['total_amount'] : 500.00;
    $tranId = 'BOOK_' . $booking['id'] . '_' . time();
    $productName = 'E-Sheba Booking: ' . $booking['category_name'];

    // Update booking with pending payment details
    DB::query("UPDATE bookings SET pay_txn_id = ?, pay_method = 'sslcommerz' WHERE id = ?", [$tranId, $booking['id']]);
} else {
    die("Unsupported payment type.");
}

// Prepare SSLCommerz Payload
$postData = [
    'store_id'         => SSLCOMMERZ_STORE_ID,
    'store_passwd'     => SSLCOMMERZ_STORE_PASS,
    'total_amount'     => $amount,
    'currency'         => 'BDT',
    'tran_id'          => $tranId,
    'success_url'      => APP_URL . '/sslcommerz/success.php',
    'fail_url'         => APP_URL . '/sslcommerz/fail.php',
    'cancel_url'       => APP_URL . '/sslcommerz/cancel.php',
    'ipn_url'          => APP_URL . '/sslcommerz/ipn.php',
    'cus_name'         => $user['name'],
    'cus_email'        => $user['email'],
    'cus_add1'         => 'Dhaka, Bangladesh',
    'cus_city'         => 'Dhaka',
    'cus_country'      => 'Bangladesh',
    'cus_phone'        => $user['phone'] ?: '01700000000',
    'shipping_method'  => 'NO',
    'product_name'     => $productName,
    'product_category' => 'Service',
    'product_profile'  => 'general',
    'value_a'          => $type,
    'value_b'          => $refId,
];

// Determine API URL (Sandbox vs Live)
$apiUrl = SSLCOMMERZ_SANDBOX 
    ? "https://sandbox.sslcommerz.com/gwprocess/v4/api.php" 
    : "https://securepay.sslcommerz.com/gwprocess/v4/api.php";

$hasRealCreds = (SSLCOMMERZ_STORE_ID !== 'your_store_id' && !empty(SSLCOMMERZ_STORE_ID));

if ($hasRealCreds) {
    // Attempt real SSLCommerz API call
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $apiUrl);
    curl_setopt($handle, CURLOPT_TIMEOUT, 30);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($handle, CURLOPT_POST, 1);
    curl_setopt($handle, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

    $content = curl_exec($handle);
    $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    $sslcz = json_decode($content, true);

    if (isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL'] != "") {
        header("Location: " . $sslcz['GatewayPageURL']);
        exit;
    }
}

// ── Interactive Sandbox Gateway Simulator (for Academic Demo / Testing) ──
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SSLCommerz Gateway Sandbox — <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body { background: #f0f2f5; font-family: 'Inter', sans-serif; }
    .gateway-container { max-width: 500px; margin: 40px auto; }
    .method-card { border: 2px solid #e0e0e0; border-radius: 12px; padding: 14px; cursor: pointer; transition: all .2s; }
    .method-card:hover, .method-card.active { border-color: #1a73e8; background: #f0f7ff; }
  </style>
</head>
<body>

<div class="gateway-container">
  
  <div class="text-center mb-3">
    <div style="font-size:1.6rem;font-weight:800;color:#e2136e">
      <i class="fa fa-shield-alt me-1"></i>SSLCOMMERZ
    </div>
    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-600">TEST SANDBOX GATEWAY</span>
  </div>

  <div class="bg-white rounded-4 shadow-sm border p-4">
    
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
      <div>
        <div class="text-muted" style="font-size:.8rem;text-transform:uppercase">Payment For</div>
        <div class="fw-700" style="font-size:1.05rem"><?= htmlspecialchars($productName) ?></div>
        <div class="text-muted" style="font-size:.75rem">Txn ID: <code><?= htmlspecialchars($tranId) ?></code></div>
      </div>
      <div class="text-end">
        <div class="text-muted" style="font-size:.8rem">Amount</div>
        <div class="fw-800 text-primary" style="font-size:1.5rem">৳<?= number_format($amount, 2) ?></div>
      </div>
    </div>

    <div class="mb-3">
      <div class="fw-700 mb-2" style="font-size:.88rem">Select Test Payment Channel:</div>
      <div class="row g-2">
        <div class="col-4">
          <div class="method-card text-center active" onclick="selectMethod(this, 'bkash')">
            <div class="fw-700 text-danger" style="font-size:1rem">bKash</div>
            <div class="text-muted" style="font-size:.7rem">Mobile</div>
          </div>
        </div>
        <div class="col-4">
          <div class="method-card text-center" onclick="selectMethod(this, 'nagad')">
            <div class="fw-700 text-warning" style="font-size:1rem">Nagad</div>
            <div class="text-muted" style="font-size:.7rem">Mobile</div>
          </div>
        </div>
        <div class="col-4">
          <div class="method-card text-center" onclick="selectMethod(this, 'card')">
            <div class="fw-700 text-primary" style="font-size:1rem">Visa / MC</div>
            <div class="text-muted" style="font-size:.7rem">Card</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Simulation Form -->
    <form method="POST" action="success.php" class="mb-2">
      <input type="hidden" name="tran_id" value="<?= htmlspecialchars($tranId) ?>">
      <input type="hidden" name="val_id" value="VAL_<?= time() ?>">
      <input type="hidden" name="amount" value="<?= $amount ?>">
      <input type="hidden" name="card_type" id="cardTypeInput" value="BKASH-MobileBanking">
      <input type="hidden" name="value_a" value="<?= htmlspecialchars($type) ?>">
      <input type="hidden" name="value_b" value="<?= htmlspecialchars((string)$refId) ?>">
      <input type="hidden" name="bank_tran_id" value="BANK_<?= rand(100000,999999) ?>">
      <input type="hidden" name="status" value="VALID">

      <button type="submit" class="btn btn-success w-100 py-3 rounded-pill fw-800 mb-2 shadow-sm">
        <i class="fa fa-check-circle me-1"></i> Pay ৳<?= number_format($amount, 2) ?> (Success)
      </button>
    </form>

    <!-- Fail & Cancel Actions -->
    <div class="row g-2">
      <div class="col-6">
        <form method="POST" action="fail.php">
          <input type="hidden" name="tran_id" value="<?= htmlspecialchars($tranId) ?>">
          <button type="submit" class="btn btn-outline-danger w-100 rounded-pill py-2" style="font-size:.85rem">
            Simulate Fail
          </button>
        </form>
      </div>
      <div class="col-6">
        <form method="POST" action="cancel.php">
          <input type="hidden" name="tran_id" value="<?= htmlspecialchars($tranId) ?>">
          <button type="submit" class="btn btn-outline-secondary w-100 rounded-pill py-2" style="font-size:.85rem">
            Cancel
          </button>
        </form>
      </div>
    </div>

    <div class="text-center mt-3 text-muted" style="font-size:.75rem">
      <i class="fa fa-lock me-1"></i>Protected by 128-bit SSL encryption · SSLCommerz Sandbox
    </div>

  </div>
</div>

<script>
function selectMethod(el, method) {
  document.querySelectorAll('.method-card').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  const typeMap = { 'bkash': 'BKASH-MobileBanking', 'nagad': 'NAGAD-MobileBanking', 'card': 'VISA-CreditCard' };
  document.getElementById('cardTypeInput').value = typeMap[method] || 'SSLCOMMERZ';
}
</script>
</body>
</html>
