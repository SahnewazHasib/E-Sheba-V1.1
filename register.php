<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — E-Sheba</title>
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
if (Auth::isLoggedIn()) { header("Location: index.php"); exit; }

$step   = (int)($_SESSION['reg_step'] ?? 1);
$role   = $_SESSION['reg_role'] ?? ($_GET['role'] ?? 'customer');
$error  = '';
$success= '';

// ── Handle form submissions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Step 1: basic info
    if ($action === 'step1') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $role     = $_POST['role'] ?? 'customer';

        if (!$name || !$email || !$password) { $error = 'All fields are required.'; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Invalid email address.'; }
        elseif (strlen($password) < 8) { $error = 'Password must be at least 8 characters.'; }
        elseif ($password !== $confirm) { $error = 'Passwords do not match.'; }
        elseif (DB::fetchOne("SELECT id FROM users WHERE email=?", [$email])) { $error = 'Email already registered. <a href="login.php">Login</a>'; }
        else {
            $_SESSION['reg_data'] = compact('name','email','phone','password','role');
            // Create user (unverified)
            $hash = password_hash($password, PASSWORD_DEFAULT);
            DB::query("INSERT INTO users (name,email,phone,password_hash,role,email_verified) VALUES (?,?,?,?,?,0)",
                       [$name,$email,$phone,$hash,$role]);
            $userId = (int)DB::lastInsertId();
            $_SESSION['reg_user_id'] = $userId;
            // Generate & send OTP
            $otp = Auth::generateOTP();
            Auth::storeOTP($userId, $otp, 'email_verify');
            Mailer::sendOTP($email, $name, $otp, 'verify');
            $_SESSION['reg_step'] = 2;
            $_SESSION['reg_role'] = $role;
            $step = 2;
            $success = 'OTP sent to <strong>' . htmlspecialchars($email) . '</strong>. Check your inbox!';
        }
    }

    // Step 2: verify OTP
    elseif ($action === 'verify_otp') {
        $otp    = implode('', array_map(fn($k)=> $_POST["otp$k"]??'', [1,2,3,4,5,6]));
        $userId = (int)($_SESSION['reg_user_id'] ?? 0);
        if (!$userId) { $error = 'Session expired. Please register again.'; $_SESSION['reg_step']=1; $step=1; }
        elseif (!Auth::verifyOTP($userId, $otp, 'email_verify')) { $error = 'Invalid or expired OTP. Try again.'; }
        else {
            DB::query("UPDATE users SET email_verified=1 WHERE id=?", [$userId]);
            if ($role === 'worker') {
                $_SESSION['reg_step'] = 3; $step = 3;
                $success = 'Email verified! Complete your worker profile.';
            } else {
                // Customer — log in directly
                $user = DB::fetchOne("SELECT * FROM users WHERE id=?", [$userId]);
                Auth::login($user);
                unset($_SESSION['reg_step'],$_SESSION['reg_data'],$_SESSION['reg_user_id'],$_SESSION['reg_role']);
                header("Location: customer/dashboard.php"); exit;
            }
        }
    }

    // Step 2b: resend OTP
    elseif ($action === 'resend_otp') {
        $userId = (int)($_SESSION['reg_user_id'] ?? 0);
        $email = $_SESSION['reg_data']['email'] ?? '';
        $name = $_SESSION['reg_data']['name'] ?? 'User';
        if ($userId && $email) {
            $otp = Auth::generateOTP();
            Auth::storeOTP($userId, $otp, 'email_verify');
            Mailer::sendOTP($email, $name, $otp, 'verify');
            $success = 'A fresh 6-digit verification code has been generated!';
        } else {
            $error = 'Session expired. Please register again.';
            $_SESSION['reg_step'] = 1; $step = 1;
        }
    }

    // Step 3: worker profile
    elseif ($action === 'worker_profile') {
        $userId = (int)($_SESSION['reg_user_id'] ?? 0);
        $district  = trim($_POST['district'] ?? '');
        $thana     = trim($_POST['thana'] ?? '');
        $bio       = trim($_POST['bio'] ?? '');
        $price     = (float)($_POST['price_per_hour'] ?? 0);
        $exp       = (int)($_POST['experience_years'] ?? 0);
        $cats      = $_POST['categories'] ?? [];
        $nid       = trim($_POST['nid'] ?? '');
        $nidPhoto  = 'sample_nid.svg';

        if (!empty($_FILES['nid_card']['name'])) {
            $ext = strtolower(pathinfo($_FILES['nid_card']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','pdf','svg'])) {
                $filename = 'nid_' . $userId . '_' . time() . '.' . $ext;
                $targetDir = __DIR__ . '/uploads/nid/';
                if (!is_dir($targetDir)) { @mkdir($targetDir, 0777, true); }
                if (move_uploaded_file($_FILES['nid_card']['tmp_name'], $targetDir . $filename)) {
                    $nidPhoto = $filename;
                }
            }
        }

        if (!$district || !$thana || !$price || empty($cats)) {
            $error = 'Please fill required fields.';
        } else {
            try {
                DB::query(
                    "INSERT INTO workers (user_id,nid_number,nid_image_path,bio,price_per_hour,working_area,district,thana,experience_years) VALUES (?,?,?,?,?,?,?,?,?)",
                    [$userId, $nid, $nidPhoto, $bio, $price, "$thana, $district", $district, $thana, $exp]
                );
            } catch (\PDOException $e) {
                // If column nid_image_path is missing in existing database, auto-add it and retry
                if ($e->getCode() == '42S22' || strpos($e->getMessage(), 'nid_image_path') !== false) {
                    try {
                        DB::query("ALTER TABLE workers ADD COLUMN nid_image_path VARCHAR(255) NULL AFTER nid_number");
                        DB::query(
                            "INSERT INTO workers (user_id,nid_number,nid_image_path,bio,price_per_hour,working_area,district,thana,experience_years) VALUES (?,?,?,?,?,?,?,?,?)",
                            [$userId, $nid, $nidPhoto, $bio, $price, "$thana, $district", $district, $thana, $exp]
                        );
                    } catch (\Throwable $ex) {
                        // Fallback: insert without nid_image_path if alter fails
                        DB::query(
                            "INSERT INTO workers (user_id,nid_number,bio,price_per_hour,working_area,district,thana,experience_years) VALUES (?,?,?,?,?,?,?,?)",
                            [$userId, $nid, $bio, $price, "$thana, $district", $district, $thana, $exp]
                        );
                    }
                } else {
                    throw $e;
                }
            }
            $wId = (int)DB::lastInsertId();
            foreach ($cats as $catId) {
                DB::query("INSERT IGNORE INTO worker_categories (worker_id,category_id) VALUES (?,?)", [$wId,(int)$catId]);
            }
            $user = DB::fetchOne("SELECT * FROM users WHERE id=?", [$userId]);
            Auth::login($user);
            unset($_SESSION['reg_step'],$_SESSION['reg_data'],$_SESSION['reg_user_id'],$_SESSION['reg_role']);
            header("Location: worker/dashboard.php?welcome=1"); exit;
        }
    }

    // Resend OTP
    elseif ($action === 'resend_otp') {
        $userId = (int)($_SESSION['reg_user_id'] ?? 0);
        $data   = $_SESSION['reg_data'] ?? [];
        if ($userId && $data) {
            $otp = Auth::generateOTP();
            Auth::storeOTP($userId, $otp, 'email_verify');
            Mailer::sendOTP($data['email'], $data['name'], $otp, 'verify');
            $success = 'OTP resent! Check your inbox.';
        } else { $error = 'Session expired.'; }
        $step = 2;
    }
}
?>

<div class="auth-page" style="align-items:flex-start;padding:30px 16px">
  <div class="auth-card" style="max-width:520px;margin:auto">

    <!-- Logo -->
    <div class="auth-logo">
      <a href="index.php" class="text-decoration-none">
        <div class="logo-text">🔧 E-Sheba</div>
      </a>
      <p>Bangladesh's Trusted Service Platform</p>
    </div>

    <!-- Progress Steps -->
    <div class="progress-steps mb-4">
      <div class="step <?= $step>=1?'active':'' ?> <?= $step>1?'completed':'' ?>">
        <div class="step-num"><?= $step>1 ? '<i class="fa fa-check"></i>' : '1' ?></div>
        <div class="step-label">Account</div>
      </div>
      <div class="step <?= $step>=2?'active':'' ?> <?= $step>2?'completed':'' ?>">
        <div class="step-num"><?= $step>2 ? '<i class="fa fa-check"></i>' : '2' ?></div>
        <div class="step-label">Verify</div>
      </div>
      <?php if($role==='worker'): ?>
      <div class="step <?= $step>=3?'active':'' ?>">
        <div class="step-num">3</div>
        <div class="step-label">Profile</div>
      </div>
      <?php endif; ?>
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

    <!-- ══ STEP 1: Basic Info ══ -->
    <?php if($step === 1): ?>
    <h2 class="auth-title">Create Account</h2>
    <p class="auth-subtitle">Join thousands of users on E-Sheba</p>

    <!-- Role Tabs -->
    <div class="role-tabs mb-4">
      <div class="role-tab <?= $role==='customer'?'active':'' ?>" onclick="setRole('customer',this)">
        <i class="fa fa-user"></i> Customer
      </div>
      <div class="role-tab <?= $role==='worker'?'active':'' ?>" onclick="setRole('worker',this)">
        <i class="fa fa-hard-hat"></i> Service Provider
      </div>
    </div>

    <form method="POST" id="regForm1" novalidate>
      <input type="hidden" name="action" value="step1">
      <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($role) ?>">

      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <div class="input-group-esheba">
          <i class="fa fa-user input-icon"></i>
          <input type="text" name="name" class="form-control-esheba" placeholder="Your full name" required>
          <div class="invalid-feedback">Name is required.</div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Gmail / Email Address *</label>
        <div class="input-group-esheba">
          <i class="fa fa-envelope input-icon"></i>
          <input type="email" name="email" id="emailInput" class="form-control-esheba" placeholder="you@gmail.com" required>
          <div class="invalid-feedback">Valid email required.</div>
          <div class="valid-feedback">Looks good!</div>
        </div>
        <div id="emailStatus" style="font-size:.78rem;margin-top:4px"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Mobile Number (optional)</label>
        <div class="input-group-esheba">
          <i class="fa fa-phone input-icon"></i>
          <input type="tel" name="phone" class="form-control-esheba" placeholder="+880 1XXXXXXXXX">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Password *</label>
        <div class="input-group-esheba">
          <i class="fa fa-lock input-icon"></i>
          <input type="password" name="password" id="passInput" class="form-control-esheba" placeholder="Min 8 characters" required>
          <i class="fa fa-eye-slash toggle-pass" id="togglePass"></i>
        </div>
        <!-- Password strength -->
        <div class="mt-2" id="passStrength" style="display:none">
          <div style="height:4px;background:#eee;border-radius:2px;overflow:hidden">
            <div id="passStrengthBar" style="height:100%;width:0;background:var(--danger);transition:all .3s;border-radius:2px"></div>
          </div>
          <span id="passStrengthText" style="font-size:.72rem;color:var(--muted)"></span>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Confirm Password *</label>
        <div class="input-group-esheba">
          <i class="fa fa-lock input-icon"></i>
          <input type="password" name="confirm_password" id="confirmPass" class="form-control-esheba" placeholder="Repeat password" required>
          <div class="invalid-feedback" id="passMismatch">Passwords don't match.</div>
        </div>
      </div>

      <div class="mb-4">
        <label class="d-flex align-items-start gap-2 cursor-pointer" style="font-size:.82rem;color:#555">
          <input type="checkbox" name="agree" required style="accent-color:var(--primary);margin-top:3px">
          I agree to the <a href="terms.php" class="fw-600">Terms of Service</a> and <a href="privacy.php" class="fw-600">Privacy Policy</a>
        </label>
      </div>

      <button type="submit" class="btn-esheba btn-esheba-primary" id="regBtn">
        <i class="fa fa-arrow-right me-2"></i>Continue
      </button>
    </form>

    <p class="text-center mt-3 mb-0" style="font-size:.88rem">
      Already registered? <a href="login.php" class="fw-600">Sign in</a>
    </p>

    <!-- ══ STEP 2: OTP Verification ══ -->
    <?php elseif($step === 2): ?>
    <h2 class="auth-title">Verify Your Email</h2>
    <p class="auth-subtitle">We sent a 6-digit OTP to <strong><?= htmlspecialchars($_SESSION['reg_data']['email'] ?? '') ?></strong></p>

    <!-- Demo Mode OTP Box -->
    <?php $demoOtp = $_SESSION['last_demo_otp'] ?? '123456'; ?>
    <div class="alert alert-info d-flex align-items-center gap-2 mb-3" style="border-radius:10px;font-size:.88rem">
      <i class="fa fa-info-circle fa-lg text-primary"></i>
      <div>
        <strong>Local Demo Mode:</strong> Your OTP code is <code style="font-size:1.15rem;font-weight:800;color:#1a73e8;letter-spacing:3px"><?= $demoOtp ?></code>
        <div style="font-size:.76rem;color:#555">You can also enter master test code <strong>123456</strong>.</div>
      </div>
    </div>

    <form method="POST" id="otpForm">
      <input type="hidden" name="action" value="verify_otp">

      <!-- OTP inputs -->
      <div class="otp-inputs" id="otpInputs">
        <?php for($i=1;$i<=6;$i++): ?>
        <input type="text" name="otp<?= $i ?>" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required>
        <?php endfor; ?>
      </div>

      <!-- Timer -->
      <p class="text-center text-muted mb-4" style="font-size:.88rem">
        OTP expires in <span id="otpTimer" class="text-danger fw-600">10:00</span>
      </p>

      <button type="submit" class="btn-esheba btn-esheba-primary" id="verifyBtn">
        <i class="fa fa-check me-2"></i>Verify & Continue
      </button>
    </form>

    <form method="POST" class="mt-3 text-center">
      <input type="hidden" name="action" value="resend_otp">
      <button type="submit" class="btn btn-link p-0" style="font-size:.88rem" id="resendBtn" disabled>
        Didn't receive? <span class="fw-600">Resend OTP</span> <span id="resendTimer">(60s)</span>
      </button>
    </form>

    <!-- ══ STEP 3: Worker Profile ══ -->
    <?php elseif($step === 3): ?>
    <h2 class="auth-title">Set Up Your Profile</h2>
    <p class="auth-subtitle">Help customers find you. This takes 2 minutes.</p>

    <form method="POST" id="workerForm" enctype="multipart/form-data">
      <input type="hidden" name="action" value="worker_profile">

      <!-- Categories -->
      <div class="form-group">
        <label class="form-label">Service Categories * (select all that apply)</label>
        <div class="d-flex flex-wrap gap-2" id="catPills">
          <?php
            try {
              $allCats = DB::fetchAll("SELECT id,name,icon FROM categories WHERE is_active=1 ORDER BY sort_order");
            } catch(Exception $e) {
              $allCats = [
                ['id'=>1,'name'=>'Plumber','icon'=>'fa-wrench'],
                ['id'=>2,'name'=>'Electrician','icon'=>'fa-bolt'],
                ['id'=>3,'name'=>'Mechanic','icon'=>'fa-car'],
                ['id'=>4,'name'=>'Painter','icon'=>'fa-paint-roller'],
                ['id'=>5,'name'=>'Cleaner','icon'=>'fa-broom'],
              ];
            }
            foreach($allCats as $cat):
          ?>
            <label class="cat-pill" style="cursor:pointer">
              <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>" style="display:none" class="cat-check">
              <span class="d-flex align-items-center gap-1 px-3 py-2 rounded-pill border" style="font-size:.8rem;font-weight:600;transition:all .2s">
                <i class="fa <?= $cat['icon'] ?>"></i> <?= htmlspecialchars($cat['name']) ?>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-6">
          <div class="form-group">
            <label class="form-label">District *</label>
            <select name="district" id="districtSel" class="form-control-esheba" required>
              <option value="">Select District</option>
              <?php foreach(['Dhaka','Chittagong','Sylhet','Rajshahi','Khulna','Barishal','Rangpur','Mymensingh','Comilla','Narayanganj','Gazipur','Faridpur','Jessore','Bogra'] as $d): ?>
                <option value="<?= $d ?>"><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label class="form-label">Thana/Area *</label>
            <input type="text" name="thana" class="form-control-esheba" placeholder="e.g. Dhanmondi" required>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-6">
          <div class="form-group">
            <label class="form-label">Price per Hour (৳) *</label>
            <div class="input-group-esheba">
              <i class="fa fa-taka input-icon" style="font-size:12px">৳</i>
              <input type="number" name="price_per_hour" class="form-control-esheba" placeholder="300" min="50" required>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label class="form-label">Experience (years)</label>
            <input type="number" name="experience_years" class="form-control-esheba" placeholder="2" min="0" max="50">
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">NID Number</label>
        <div class="input-group-esheba">
          <i class="fa fa-id-card input-icon"></i>
          <input type="text" name="nid" class="form-control-esheba" placeholder="10 or 17-digit NID">
        </div>
        <div class="mt-1" style="font-size:.76rem;color:var(--muted)"><i class="fa fa-shield-alt me-1"></i>Stored securely & encrypted. Required for verification.</div>
      </div>

      <div class="form-group">
        <label class="form-label">Upload NID Card Photo (Front / Both Sides)</label>
        <div class="input-group-esheba">
          <i class="fa fa-camera input-icon"></i>
          <input type="file" name="nid_card" class="form-control-esheba" accept="image/*,.pdf">
        </div>
        <div class="mt-1" style="font-size:.76rem;color:var(--muted)">Admin reviews this document to grant your Verified badge.</div>
      </div>

      <div class="form-group">
        <label class="form-label">Bio / Description</label>
        <textarea name="bio" class="form-control-esheba" rows="3" placeholder="Tell customers about your skills, experience, and why they should hire you..."></textarea>
      </div>

      <button type="submit" class="btn-esheba btn-esheba-primary">
        <i class="fa fa-check me-2"></i>Complete Registration
      </button>
    </form>
    <?php endif; ?>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/app.js"></script>
<script>
// ── Role switching ──
function setRole(role, el) {
  document.querySelectorAll('.role-tab').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('roleInput').value = role;
}

// ── Password strength ──
const passInput = document.getElementById('passInput');
if (passInput) {
  passInput.addEventListener('input', function() {
    const v = this.value;
    document.getElementById('passStrength').style.display = v ? 'block' : 'none';
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const bar = document.getElementById('passStrengthBar');
    const txt = document.getElementById('passStrengthText');
    const labels = ['','Weak','Fair','Good','Strong'];
    const colors = ['','var(--danger)','var(--warning)','var(--info)','var(--success)'];
    bar.style.width = (score * 25) + '%';
    bar.style.background = colors[score];
    txt.textContent = labels[score];
    txt.style.color = colors[score];
  });
}

// ── Password toggle ──
const togglePass = document.getElementById('togglePass');
if (togglePass) {
  togglePass.addEventListener('click', function() {
    const inp = document.getElementById('passInput');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    this.classList.toggle('fa-eye'); this.classList.toggle('fa-eye-slash');
  });
}

// ── Confirm password match ──
const confirmPass = document.getElementById('confirmPass');
if (confirmPass) {
  confirmPass.addEventListener('input', function() {
    if (this.value !== passInput.value) this.classList.add('is-invalid');
    else { this.classList.remove('is-invalid'); this.classList.add('is-valid'); }
  });
}

// ── Real-time email check ──
let emailTimer;
const emailInput = document.getElementById('emailInput');
if (emailInput) {
  emailInput.addEventListener('input', function() {
    clearTimeout(emailTimer);
    const val = this.value.trim();
    const status = document.getElementById('emailStatus');
    if (val.length < 4) { status.textContent=''; return; }
    emailTimer = setTimeout(() => {
      $.get('api/check-email.php', { email: val }, function(res) {
        if (res.exists) {
          status.innerHTML = '<span style="color:var(--danger)"><i class="fa fa-times-circle me-1"></i>Email already registered. <a href="login.php">Login instead?</a></span>';
        } else if (res.valid) {
          status.innerHTML = '<span style="color:var(--success)"><i class="fa fa-check-circle me-1"></i>Email is available!</span>';
        }
      }, 'json');
    }, 500);
  });
}

// ── OTP inputs: auto-advance ──
const otpInputs = document.querySelectorAll('.otp-input');
otpInputs.forEach((inp, i) => {
  inp.addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g,'');
    if (this.value && i < otpInputs.length - 1) otpInputs[i+1].focus();
  });
  inp.addEventListener('keydown', function(e) {
    if (e.key === 'Backspace' && !this.value && i > 0) otpInputs[i-1].focus();
  });
  inp.addEventListener('paste', function(e) {
    e.preventDefault();
    const data = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'');
    data.split('').forEach((ch, j) => { if (otpInputs[j]) otpInputs[j].value = ch; });
    if (otpInputs[Math.min(data.length, otpInputs.length-1)]) otpInputs[Math.min(data.length, otpInputs.length-1)].focus();
  });
});

// ── OTP countdown timer ──
const timerEl = document.getElementById('otpTimer');
if (timerEl) {
  let secs = 600; // 10 minutes
  const interval = setInterval(() => {
    secs--;
    if (secs <= 0) { clearInterval(interval); timerEl.textContent='Expired'; return; }
    const m = Math.floor(secs/60), s = secs%60;
    timerEl.textContent = m+':'+(s<10?'0':'')+s;
  }, 1000);
}

// ── Resend OTP timer ──
const resendBtn = document.getElementById('resendBtn');
const resendTimer = document.getElementById('resendTimer');
if (resendBtn) {
  let rs = 60;
  const ri = setInterval(() => {
    rs--;
    resendTimer.textContent = rs > 0 ? `(${rs}s)` : '';
    if (rs <= 0) { clearInterval(ri); resendBtn.disabled=false; resendTimer.textContent=''; }
  }, 1000);
}

// ── Category pills toggle ──
document.querySelectorAll('.cat-check').forEach(cb => {
  cb.addEventListener('change', function() {
    const span = this.closest('label').querySelector('span');
    if (this.checked) {
      span.style.background='var(--primary)';
      span.style.color='#fff';
      span.style.borderColor='var(--primary)';
    } else {
      span.style.background='';
      span.style.color='';
      span.style.borderColor='';
    }
  });
});
</script>
</body>
</html>
