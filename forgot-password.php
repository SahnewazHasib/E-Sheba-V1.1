<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password — E-Sheba</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/mailer.php';
Auth::start();

$step    = (int)($_SESSION['fp_step'] ?? 1);
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Step 1: request reset
    if ($action === 'request_reset') {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email.';
        } else {
            $user = DB::fetchOne("SELECT * FROM users WHERE email=? AND is_active=1", [$email]);
            if ($user) {
                $otp = Auth::generateOTP();
                Auth::storeOTP($user['id'], $otp, 'password_reset');
                Mailer::sendOTP($email, $user['name'], $otp, 'password_reset');
            }
            // Always show success (security: don't reveal if email exists)
            $_SESSION['fp_email']  = $email;
            $_SESSION['fp_step']   = 2;
            $step = 2;
            $success = "If <strong>$email</strong> is registered, a reset code has been sent.";
        }
    }

    // Step 2: verify OTP
    elseif ($action === 'verify_reset_otp') {
        $otp   = implode('', array_map(fn($k)=>$_POST["otp$k"]??'', [1,2,3,4,5,6]));
        $email = $_SESSION['fp_email'] ?? '';
        $user  = DB::fetchOne("SELECT * FROM users WHERE email=?", [$email]);
        if (!$user || !Auth::verifyOTP($user['id'], $otp, 'password_reset')) {
            $error = 'Invalid or expired code.'; $step = 2;
        } else {
            $_SESSION['fp_user_id'] = $user['id'];
            $_SESSION['fp_step'] = 3; $step = 3;
        }
    }

    // Step 3: set new password
    elseif ($action === 'set_password') {
        $uid  = (int)($_SESSION['fp_user_id'] ?? 0);
        $pass = $_POST['password'] ?? '';
        $conf = $_POST['confirm_password'] ?? '';
        if (!$uid) { $error='Session expired. Start over.'; $step=1; }
        elseif (strlen($pass) < 8) { $error='Password must be at least 8 characters.'; $step=3; }
        elseif ($pass !== $conf) { $error='Passwords do not match.'; $step=3; }
        else {
            DB::query("UPDATE users SET password_hash=? WHERE id=?", [password_hash($pass, PASSWORD_DEFAULT), $uid]);
            unset($_SESSION['fp_step'],$_SESSION['fp_email'],$_SESSION['fp_user_id']);
            header("Location: login.php?msg=password_reset"); exit;
        }
    }
}
?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="index.php" class="text-decoration-none">
        <div class="logo-text">🔧 E-Sheba</div>
      </a>
    </div>

    <!-- Progress -->
    <div class="progress-steps mb-4">
      <?php $stepLabels=[['fa-envelope','Email'],['fa-key','Verify'],['fa-lock','Reset']];
      foreach($stepLabels as $i=>[$icon,$label]): ?>
        <div class="step <?= $step>=$i+1?'active':'' ?> <?= $step>$i+1?'completed':'' ?>">
          <div class="step-num"><?= $step>$i+1?'<i class="fa fa-check"></i>':$i+1 ?></div>
          <div class="step-label"><?= $label ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if($error): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius:10px;font-size:.88rem">
        <i class="fa fa-exclamation-circle"></i> <span><?= $error ?></span>
      </div>
    <?php endif; ?>
    <?php if($success): ?>
      <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="border-radius:10px;font-size:.88rem">
        <i class="fa fa-check-circle"></i> <span><?= $success ?></span>
      </div>
    <?php endif; ?>

    <!-- STEP 1: Enter email -->
    <?php if($step===1): ?>
    <h2 class="auth-title">Forgot Password?</h2>
    <p class="auth-subtitle">No worries — enter your email and we'll send you a reset code.</p>

    <form method="POST" novalidate>
      <input type="hidden" name="action" value="request_reset">
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <div class="input-group-esheba">
          <i class="fa fa-envelope input-icon"></i>
          <input type="email" name="email" class="form-control-esheba" placeholder="you@gmail.com" required>
        </div>
      </div>
      <button type="submit" class="btn-esheba btn-esheba-primary">
        <i class="fa fa-paper-plane me-2"></i>Send Reset Code
      </button>
    </form>

    <!-- STEP 2: OTP -->
    <?php elseif($step===2): ?>
    <h2 class="auth-title">Enter Reset Code</h2>
    <p class="auth-subtitle">Enter the 6-digit code sent to <strong><?= htmlspecialchars($_SESSION['fp_email']??'') ?></strong></p>

    <!-- Demo Mode OTP Box -->
    <?php $demoOtp = $_SESSION['last_demo_otp'] ?? '123456'; ?>
    <div class="alert alert-info d-flex align-items-center gap-2 mb-3" style="border-radius:10px;font-size:.88rem">
      <i class="fa fa-info-circle fa-lg text-primary"></i>
      <div>
        <strong>Local Demo Mode:</strong> Reset OTP code is <code style="font-size:1.15rem;font-weight:800;color:#1a73e8;letter-spacing:3px"><?= $demoOtp ?></code>
        <div style="font-size:.76rem;color:#555">You can also enter master test code <strong>123456</strong>.</div>
      </div>
    </div>

    <form method="POST">
      <input type="hidden" name="action" value="verify_reset_otp">
      <div class="otp-inputs">
        <?php for($i=1;$i<=6;$i++): ?>
        <input type="text" name="otp<?= $i ?>" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
        <?php endfor; ?>
      </div>
      <p class="text-center text-muted mb-4" style="font-size:.88rem">
        Code expires in <span id="otpTimer" class="text-danger fw-600">10:00</span>
      </p>
      <button type="submit" class="btn-esheba btn-esheba-primary">
        <i class="fa fa-check me-2"></i>Verify Code
      </button>
    </form>

    <!-- STEP 3: New password -->
    <?php elseif($step===3): ?>
    <h2 class="auth-title">Set New Password</h2>
    <p class="auth-subtitle">Choose a strong password you haven't used before.</p>

    <form method="POST" novalidate>
      <input type="hidden" name="action" value="set_password">
      <div class="form-group">
        <label class="form-label">New Password</label>
        <div class="input-group-esheba">
          <i class="fa fa-lock input-icon"></i>
          <input type="password" name="password" id="newPass" class="form-control-esheba" placeholder="Min 8 characters" required>
          <i class="fa fa-eye-slash toggle-pass" id="toggleNewPass"></i>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Confirm New Password</label>
        <div class="input-group-esheba">
          <i class="fa fa-lock input-icon"></i>
          <input type="password" name="confirm_password" id="confNewPass" class="form-control-esheba" placeholder="Repeat password" required>
          <div class="invalid-feedback">Passwords don't match.</div>
        </div>
      </div>
      <button type="submit" class="btn-esheba btn-esheba-primary">
        <i class="fa fa-shield-alt me-2"></i>Reset Password
      </button>
    </form>
    <?php endif; ?>

    <p class="text-center mt-4 mb-0" style="font-size:.88rem">
      <a href="login.php"><i class="fa fa-arrow-left me-1"></i>Back to Login</a>
    </p>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/app.js"></script>
<script>
// OTP inputs
document.querySelectorAll('.otp-input').forEach((inp, i, all) => {
  inp.addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g,'');
    if (this.value && i < all.length-1) all[i+1].focus();
  });
  inp.addEventListener('keydown', function(e) {
    if (e.key==='Backspace' && !this.value && i>0) all[i-1].focus();
  });
  inp.addEventListener('paste', function(e) {
    e.preventDefault();
    const data=(e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'');
    data.split('').forEach((ch,j)=>{ if(all[j]) all[j].value=ch; });
  });
});
// OTP timer
const timerEl = document.getElementById('otpTimer');
if(timerEl){ let s=600; const t=setInterval(()=>{ s--; if(!s){clearInterval(t);timerEl.textContent='Expired';return;} const m=Math.floor(s/60); timerEl.textContent=m+':'+(s%60<10?'0':'')+(s%60); },1000); }
// Password toggle
const tnp=document.getElementById('toggleNewPass');
if(tnp){ tnp.addEventListener('click',()=>{ const i=document.getElementById('newPass'); i.type=i.type==='password'?'text':'password'; tnp.classList.toggle('fa-eye');tnp.classList.toggle('fa-eye-slash'); }); }
// Confirm match
const cnp=document.getElementById('confNewPass');
if(cnp){ cnp.addEventListener('input',function(){ this.classList.toggle('is-invalid', this.value!==document.getElementById('newPass').value); }); }
</script>
</body>
</html>
