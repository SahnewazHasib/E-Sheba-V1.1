<?php
// api/check-email.php — Real-time email availability check
require_once '../includes/db.php';
header('Content-Type: application/json');

$email = trim($_GET['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['valid'=>false,'exists'=>false]); exit;
}
$user = DB::fetchOne("SELECT id FROM users WHERE email=?",[$email]);
echo json_encode(['valid'=>true,'exists'=>(bool)$user]);
