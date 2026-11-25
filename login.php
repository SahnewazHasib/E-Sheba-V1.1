<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — E-Sheba</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
require_once 'includes/auth.php';
Auth::start();
// Redirect if already logged in
if (Auth::isLoggedIn()) {
    $role = Auth::role();
    $redirect = match($role) { 'worker'=>'worker/dashboard.php','admin'=>'admin/dashboard.php', default=>'customer/dashboard.php' };
    header("Location: $redirect"); exit;
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/db.php';
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'customer';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $user = DB::fetchOne("SELECT * FROM users WHERE email=? AND role=? AND is_active=1", [$email, $role]);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Invalid email or password.';
        } elseif (!$user['email_verified']) {
            $error = 'Please verify your email first. <a href="verify-email.php?email=' . urlencode($email) . '">Resend OTP</a>';
        } else {
            Auth::login($user);
            $redirect = match($role) { 'worker'=>'worker/dashboard.php','admin'=>'admin/dashboard.php', default=>'customer/dashboard.php' };
            header("Location: $redirect"); exit;
        }
    }
}
?>

<div class="auth-page">
  <div class="auth-card">
    <!-- Logo -->
    <div class="auth-logo">
      <a href="index.php" class="text-decoration-none">
        <div class="logo-text">🔧 E-Sheba</div>
      </a>
      <p>Bangladesh's Trusted Service Platform</p>
    </div>

    <h2 class="auth-title">Welcome back!</h2>
    <p class="auth-subtitle">Sign in to access your account</p>

    <!-- Role Tabs -->
    <div class="role-tabs" id="roleTabs">
      <div class="role-tab active" data-role="customer" onclick="switchRole('customer', this)">
        <i class="fa fa-user"></i> Customer
      </div>
      <div class="role-tab" data-role="worker" onclick="switchRole('worker', this)">
        <i class="fa fa-hard-hat"></i> Provider
      </div>
      <div class="role-tab" data-role="admin" onclick="switchRole('admin', this)">
        <i class="fa fa-shield-alt"></i> Admin
      </div>
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

    <form method="POST" action="" id="loginForm" novalidate>
      <input type="hidden" name="role" id="roleInput" value="customer">

      <!-- Email -->
      <div class="form-group">
        <label class="form-label">Gmail / Email Address</label>
        <div class="input-group-esheba">
          <i class="fa fa-envelope input-icon"></i>
          <input type="email" name="email" id="email" class="form-control-esheba"
                 placeholder="you@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
          <div class="invalid-feedback">Please enter a valid email.</div>
        </div>
      </div>

      <!-- Password -->
      <div class="form-group">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label class="form-label mb-0">Password</label>
          <a href="forgot-password.php" style="font-size:.8rem;color:var(--primary)">Forgot password?</a>
        </div>
        <div class="input-group-esheba">
          <i class="fa fa-lock input-icon"></i>
          <input type="password" name="password" id="password" class="form-control-esheba"
                 placeholder="Enter your password" required autocomplete="current-password">
          <i class="fa fa-eye-slash toggle-pass" id="togglePass"></i>
          <div class="invalid-feedback">Password is required.</div>
        </div>
      </div>

      <!-- Remember me -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <label class="d-flex align-items-center gap-2 cursor-pointer" style="font-size:.88rem">
          <input type="checkbox" name="remember" style="accent-color:var(--primary)"> Remember me
        </label>
      </div>

      <button type="submit" class="btn-esheba btn-esheba-primary" id="loginBtn">
        <i class="fa fa-sign-in-alt me-2"></i>Sign In
      </button>
    </form>

    <div class="auth-divider">
      <hr><span>or continue with</span><hr>
    </div>

    <!-- Google OAuth (UI only — backend integration needed) -->
    <button class="btn-esheba btn-esheba-outline" onclick="showToast('Google OAuth integration coming soon!','info')">
      <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="20" style="display:inline;margin-right:8px"> Sign in with Google
    </button>

    <p class="text-center mt-4 mb-0" style="font-size:.88rem">
      Don't have an account? <a href="register.php" class="fw-600">Register now</a>
    </p>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/app.js"></script>
<script>
function switchRole(role, el) {
  document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('roleInput').value = role;
}

// Toggle password visibility
document.getElementById('togglePass').addEventListener('click', function() {
  const inp = document.getElementById('password');
  if (inp.type === 'password') {
    inp.type = 'text';
    this.classList.replace('fa-eye-slash','fa-eye');
  } else {
    inp.type = 'password';
    this.classList.replace('fa-eye','fa-eye-slash');
  }
});

// Form submit with loading state
document.getElementById('loginForm').addEventListener('submit', function(e) {
  const email = document.getElementById('email');
  const pass  = document.getElementById('password');
  let valid = true;
  if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
    email.classList.add('is-invalid'); valid = false;
  } else { email.classList.remove('is-invalid'); }
  if (!pass.value) {
    pass.classList.add('is-invalid'); valid = false;
  } else { pass.classList.remove('is-invalid'); }
  if (!valid) { e.preventDefault(); return; }
  document.getElementById('loginBtn').classList.add('btn-loading');
});
</script>
</body>
</html>
