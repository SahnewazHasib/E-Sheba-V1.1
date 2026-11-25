<?php
// ============================================================
// Worker Profile Settings — E-Sheba
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
Auth::requireRole('worker');

$myId = Auth::id();
$msg = '';
$err = '';

$worker = DB::fetchOne(
    "SELECT w.*, u.name, u.email, u.phone, u.avatar 
     FROM workers w JOIN users u ON w.user_id = u.id 
     WHERE w.user_id = ?",
    [$myId]
);

if (!$worker) {
    header("Location: ../register.php?role=worker");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone    = trim($_POST['phone'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');
    $price    = (float)($_POST['price_per_hour'] ?? 0);
    $area     = trim($_POST['working_area'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $thana    = trim($_POST['thana'] ?? '');
    $exp      = (int)($_POST['experience_years'] ?? 0);

    if (!$district || !$thana || $price <= 0) {
        $err = 'Please fill all required fields.';
    } else {
        // Handle avatar upload if provided
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            $allowedMimes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif'
            ];

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if ($file['size'] > $maxSize) {
                $err = 'Avatar picture must be less than 5 MB.';
            } elseif (!isset($allowedMimes[$mime])) {
                $err = 'Only JPG, PNG, WEBP, or GIF images are allowed.';
            } else {
                $ext = $allowedMimes[$mime];
                $targetDir = __DIR__ . '/../uploads/avatars/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                
                $fileName = 'avatar_user_' . $myId . '_' . time() . '.' . $ext;
                $targetPath = $targetDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // Remove old uploaded avatar
                    if (!empty($worker['avatar']) && strpos($worker['avatar'], 'uploads/avatars/') !== false) {
                        $oldPath = __DIR__ . '/../' . $worker['avatar'];
                        if (file_exists($oldPath)) @unlink($oldPath);
                    }
                    $avatarRel = 'uploads/avatars/' . $fileName;
                    DB::query("UPDATE users SET avatar = ? WHERE id = ?", [$avatarRel, $myId]);
                    $_SESSION['user_avatar'] = $avatarRel;
                }
            }
        }

        if (empty($err)) {
            DB::query("UPDATE users SET phone = ? WHERE id = ?", [$phone, $myId]);
            DB::query(
                "UPDATE workers SET bio = ?, price_per_hour = ?, working_area = ?, district = ?, thana = ?, experience_years = ? WHERE id = ?",
                [$bio, $price, $area, $district, $thana, $exp, $worker['id']]
            );
            $msg = 'Profile and picture updated successfully!';
            $worker = DB::fetchOne(
                "SELECT w.*, u.name, u.email, u.phone, u.avatar 
                 FROM workers w JOIN users u ON w.user_id = u.id 
                 WHERE w.user_id = ?",
                [$myId]
            );
        }
    }
}

$districts = ['Dhaka','Chittagong','Sylhet','Rajshahi','Khulna','Barishal','Rangpur','Mymensingh','Comilla','Narayanganj','Gazipur'];
$currentAvatar = !empty($worker['avatar']) ? '../' . htmlspecialchars($worker['avatar']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Worker Profile Settings — <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .avatar-upload-box {
      position: relative;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      overflow: hidden;
      border: 3px solid #1a73e8;
      box-shadow: 0 4px 12px rgba(26,115,232,0.18);
      background: #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    .avatar-upload-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .avatar-upload-box .avatar-overlay {
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,0.45);
      color: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
      opacity: 0;
      transition: opacity .2s;
    }
    .avatar-upload-box:hover .avatar-overlay {
      opacity: 1;
    }
  </style>
</head>
<body style="background:#f4f6fb">

<nav class="navbar-esheba sticky-top">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="../index.php" class="brand d-flex align-items-center gap-2 text-decoration-none py-1">
      <img src="../images/logo.svg" alt="E-Sheba Logo" class="brand-logo-img" style="height:40px">
    </a>
    <div class="d-flex align-items-center gap-2">
      <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-3" style="font-size:.85rem">
        <i class="fa fa-arrow-left me-1"></i>Back to Dashboard
      </a>
      <a href="../worker-profile.php?id=<?= $worker['id'] ?>" class="btn btn-outline-primary rounded-pill px-3" style="font-size:.85rem">
        <i class="fa fa-eye me-1"></i>View Public Profile
      </a>
    </div>
  </div>
</nav>

<div class="container py-4" style="max-width:760px">
  
  <div class="bg-white rounded-4 border p-4 p-md-5 shadow-sm">
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
      <div>
        <h4 class="fw-800 mb-0">Service Provider Settings</h4>
        <p class="text-muted mb-0" style="font-size:.85rem">Update your profile photo, service rate, service area, and biography.</p>
      </div>
      <div>
        <?php if ($currentAvatar): ?>
          <img src="<?= $currentAvatar ?>" alt="<?= htmlspecialchars($worker['name']) ?>" class="rounded-circle shadow-sm" style="width:48px;height:48px;object-fit:cover;border:2px solid #1a73e8">
        <?php else: ?>
          <div class="chat-avatar" style="width:48px;height:48px;font-size:1.1rem;background:linear-gradient(135deg, #1A73E8, #0D47A1);color:#fff">
            <?= strtoupper(substr($worker['name'], 0, 1)) ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-success d-flex align-items-center gap-2 mb-3" style="border-radius:10px;font-size:.88rem">
        <i class="fa fa-check-circle"></i> <?= $msg ?>
      </div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius:10px;font-size:.88rem">
        <i class="fa fa-exclamation-circle"></i> <?= $err ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
      
      <!-- Profile Picture Upload Card -->
      <div class="p-3 mb-4 rounded-4 bg-light border d-flex align-items-center gap-4 flex-wrap flex-sm-nowrap">
        <div class="avatar-upload-box" onclick="document.getElementById('avatarInput').click()">
          <img id="avatarPreview" src="<?= $currentAvatar ?: 'https://ui-avatars.com/api/?name='.urlencode($worker['name']).'&background=1A73E8&color=fff&size=128' ?>" alt="Avatar">
          <div class="avatar-overlay">
            <i class="fa fa-camera mb-1"></i>
            <span>Change</span>
          </div>
        </div>
        <div class="flex-grow-1">
          <h6 class="fw-700 mb-1">Profile Photo</h6>
          <p class="text-muted mb-2" style="font-size:.82rem">Upload a professional, clear portrait so customers can trust your service.</p>
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="document.getElementById('avatarInput').click()">
              <i class="fa fa-upload me-1"></i> Choose New Photo
            </button>
            <small class="text-muted" style="font-size:.75rem">JPG, PNG, WEBP (Max 5MB)</small>
          </div>
          <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="previewImage(this)">
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-600" style="font-size:.85rem">Full Name</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($worker['name']) ?>" disabled>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-600" style="font-size:.85rem">Email</label>
          <input type="email" class="form-control" value="<?= htmlspecialchars($worker['email']) ?>" disabled>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-600" style="font-size:.85rem">Contact Phone *</label>
          <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($worker['phone'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-600" style="font-size:.85rem">Standard Hourly Rate (৳ BDT) *</label>
          <input type="number" step="10" name="price_per_hour" class="form-control" value="<?= (int)$worker['price_per_hour'] ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-600" style="font-size:.85rem">District *</label>
          <select name="district" class="form-select" required>
            <?php foreach ($districts as $d): ?>
              <option value="<?= $d ?>" <?= $worker['district'] === $d ? 'selected' : '' ?>><?= $d ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-600" style="font-size:.85rem">Thana / Upazila *</label>
          <input type="text" name="thana" class="form-control" value="<?= htmlspecialchars($worker['thana']) ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-600" style="font-size:.85rem">Experience (Years)</label>
          <input type="number" name="experience_years" class="form-control" value="<?= (int)$worker['experience_years'] ?>" min="0" max="50">
        </div>

        <div class="col-12">
          <label class="form-label fw-600" style="font-size:.85rem">Working Area / Specific Locations</label>
          <input type="text" name="working_area" class="form-control" value="<?= htmlspecialchars($worker['working_area']) ?>" placeholder="e.g. Mirpur, Pallabi, Dhanmondi">
        </div>

        <div class="col-12">
          <label class="form-label fw-600" style="font-size:.85rem">Biography & Service Description</label>
          <textarea name="bio" class="form-control" rows="4" placeholder="Tell customers about your skills, certifications, and reliability..."><?= htmlspecialchars($worker['bio'] ?? '') ?></textarea>
        </div>

        <div class="col-12 text-end mt-4">
          <button type="submit" class="btn btn-primary rounded-pill px-4 fw-700 shadow-sm">
            <i class="fa fa-save me-1"></i> Save Changes
          </button>
        </div>
      </div>
    </form>
  </div>

</div>

<script>
function previewImage(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
      alert('Selected file is too large! Maximum allowed is 5MB.');
      input.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('avatarPreview').src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
}
</script>

</body>
</html>
