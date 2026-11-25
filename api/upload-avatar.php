<?php
// ============================================================
// API: Upload Profile Picture (Avatar) — E-Sheba
// Supports both Worker and Customer
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

Auth::requireLogin();
$myId = Auth::id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    $errCode = $_FILES['avatar']['error'] ?? -1;
    $msg = 'Please select a valid image file.';
    if ($errCode === UPLOAD_ERR_INI_SIZE || $errCode === UPLOAD_ERR_FORM_SIZE) {
        $msg = 'File size exceeds maximum allowed limit (5MB).';
    }
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

$file = $_FILES['avatar'];
$maxSize = 5 * 1024 * 1024; // 5 MB

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'Image size must be less than 5 MB.']);
    exit;
}

// Validate MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif'
];

if (!isset($allowedMimes[$mime])) {
    echo json_encode(['success' => false, 'error' => 'Only JPG, PNG, WEBP, or GIF images are allowed.']);
    exit;
}

$ext = $allowedMimes[$mime];
$targetDir = __DIR__ . '/../uploads/avatars/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Unique filename
$fileName = 'avatar_user_' . $myId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$targetPath = $targetDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded picture on server.']);
    exit;
}

// Get old avatar to delete if it was uploaded
$old = DB::fetchOne("SELECT avatar FROM users WHERE id = ?", [$myId]);
if ($old && !empty($old['avatar']) && strpos($old['avatar'], 'uploads/avatars/') !== false) {
    $oldFilePath = __DIR__ . '/../' . $old['avatar'];
    if (file_exists($oldFilePath)) {
        @unlink($oldFilePath);
    }
}

// Relative URL to store in DB
$avatarRel = 'uploads/avatars/' . $fileName;

// Update database
DB::query("UPDATE users SET avatar = ? WHERE id = ?", [$avatarRel, $myId]);

// Update session
Auth::start();
$_SESSION['user_avatar'] = $avatarRel;

echo json_encode([
    'success'    => true,
    'avatar_url' => $avatarRel,
    'message'    => 'Profile picture updated successfully!'
]);
