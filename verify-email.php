<?php
// ============================================================
// Verify Email (OTP) — E-Sheba
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/mailer.php';
require_once 'includes/config.php';
Auth::start();

$email = trim($_GET['email'] ?? $_POST['email'] ?? ($_SESSION['reg_data']['email'] ?? ''));
$error = '';
$success = '';

$user = $email ? DB::fetchOne("SELECT * FROM users WHERE email = ?", [$email]) : null;

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'verify';

    if ($action === 'resend') {
        if (!$user) {
            $error = 'User not found. Please register again.';
        } else {
            $otp = Auth::generateOTP();
            Auth::storeOTP($user['id'], $otp, 'email_verify');
            Mailer::sendOTP($user['email'], $user['name'], $otp, 'verify');
            $success = 'A fresh 6-digit verification code has been sent to your email!';
        }
    } elseif ($action === 'verify') {
        $otp = trim($_POST['otp'] ?? '');
        // If passed as separate digit inputs
        if (!$otp) {
            $digits = [];
            for ($i = 1; $i <= 6; $i++) {
                $digits[] = $_POST["otp$i"] ?? '';
            }
            $otp = implode('', $digits);
        }

        if (!$user) {
            $error = 'Invalid email session. Please enter your email.';
        } elseif (strlen($otp) !== 6) {
            $error = 'Please enter all 6 digits of the verification code.';
        } elseif (!Auth::verifyOTP($user['id'], $otp, 'email_verify')) {
            $error = 'Invalid or expired verification code. Please try again or request a new one.';
        } else {
            // Success: mark verified
            DB::query("UPDATE users SET email_verified = 1 WHERE id = ?", [$user['id']]);
            $user['email_verified'] = 1;
            Auth::login($user);

            // Redirect based on role
            if ($user['role'] === 'worker') {
                header("Location: worker/dashboard.php?welcome=1");
            } elseif ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: customer/dashboard.php");
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Email — <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .otp-inputs { display: flex; justify-content: center; gap: 10px; margin: 24px 0; }
    .otp-digit {
      width: 48px; height: 56px; font-size: 1.5rem; font-weight: 700; text-align: center;
      border: 2px solid var(--border); border-radius: 12px; background: #fff;
      transition: all .2s ease;
    }
    .otp-digit:focus {
      border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(26,115,232,.15);
    }
  </style>
</head>
<body>

<div class="auth-page">
  <div class="auth-card" style="max-width:440px">
    
    <!-- Logo -->
    <div class="auth-logo text-center">
      <a href="index.php" class="text-decoration-none">
        <div class="logo-text">🔧 <?= APP_NAME ?></div>
      </a>
      <p>Bangladesh's Trusted Service Platform</p>
    </div>

    <div class="text-center mb-3">
      <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width:64px;height:64px;font-size:1.8rem">
        <i class="fa fa-envelope-open-text"></i>
      </div>
      <h3 class="auth-title">Verify Your Email</h3>
      <p class="auth-subtitle mb-0">
        Enter the 6-digit OTP code sent to:
        <br><strong class="text-dark"><?= htmlspecialchars($email ?: 'your Gmail address') ?></strong>
      </p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius:10px;font-size:.88rem">
        <i class="fa fa-exclamation-circle"></i> <?= $error ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="border-radius:10px;font-size:.88rem">
        <i class="fa fa-check-circle"></i> <?= $success ?>
      </div>
    <?php endif; ?>

    <!-- Demo Mode OTP Box -->
    <?php $demoOtp = $_SESSION['last_demo_otp'] ?? '123456'; ?>
    <div class="alert alert-info d-flex align-items-center gap-2 mb-3" style="border-radius:10px;font-size:.88rem">
      <i class="fa fa-info-circle fa-lg text-primary"></i>
      <div>
        <strong>Local Demo Mode:</strong> Your OTP code is <code style="font-size:1.15rem;font-weight:800;color:#1a73e8;letter-spacing:3px"><?= $demoOtp ?></code>
        <div style="font-size:.76rem;color:#555">You can also enter master test code <strong>123456</strong>.</div>
      </div>
    </div>

    <form method="POST" action="" id="otpForm">
      <input type="hidden" name="action" value="verify">
      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

      <?php if (!$email): ?>
        <div class="form-group mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@gmail.com" required>
        </div>
      <?php endif; ?>

      <!-- 6-digit boxes -->
      <div class="otp-inputs">
        <input type="text" class="otp-digit" name="otp1" maxlength="1" pattern="[0-9]" inputmode="numeric" autofocus required>
        <input type="text" class="otp-digit" name="otp2" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
        <input type="text" class="otp-digit" name="otp3" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
        <input type="text" class="otp-digit" name="otp4" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
        <input type="text" class="otp-digit" name="otp5" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
        <input type="text" class="otp-digit" name="otp6" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-700 mb-3">
        Verify Account →
      </button>
    </form>

    <!-- Resend OTP Form -->
    <div class="text-center pt-2 border-top">
      <form method="POST" action="" style="display:inline">
        <input type="hidden" name="action" value="resend">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <span class="text-muted" style="font-size:.88rem">Didn't receive the email? </span>
        <button type="submit" class="btn btn-link p-0 fw-700 text-decoration-none" style="font-size:.88rem">
          Resend OTP
        </button>
      </form>
      <div class="mt-2">
        <a href="login.php" class="text-muted text-decoration-none" style="font-size:.82rem">← Back to Login</a>
      </div>
    </div>

  </div>
</div>

<script>
// Auto-advance OTP boxes & paste handling
const inputs = document.querySelectorAll('.otp-digit');
inputs.forEach((input, index) => {
  input.addEventListener('input', (e) => {
    if (e.target.value.length === 1 && index < inputs.length - 1) {
      inputs[index + 1].focus();
    }
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace' && !e.target.value && index > 0) {
      inputs[index - 1].focus();
    }
  });

  input.addEventListener('paste', (e) => {
    e.preventDefault();
    const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
    if (/^\d{6}$/.test(pasteData)) {
      pasteData.split('').forEach((char, i) => {
        if (inputs[i]) inputs[i].value = char;
      });
      inputs[inputs.length - 1].focus();
    }
  });
});
</script>
</body>
</html>
