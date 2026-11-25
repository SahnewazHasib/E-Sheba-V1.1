<?php
// api/update-availability.php — Toggle worker availability + GPS location
require_once '../includes/auth.php';
require_once '../includes/db.php';
header('Content-Type: application/json');
Auth::requireRole('worker');

$myId   = Auth::id();
$data   = json_decode(file_get_contents('php://input'), true);
$available = isset($data['available']) ? (int)$data['available'] : null;
$lat    = isset($data['lat'])  ? (float)$data['lat']  : null;
$lng    = isset($data['lng'])  ? (float)$data['lng']  : null;

$worker = DB::fetchOne("SELECT id FROM workers WHERE user_id=?", [$myId]);
if (!$worker) { echo json_encode(['error'=>'Worker profile not found']); exit; }

$sets  = []; $params = [];
if ($available !== null) { $sets[] = 'is_available=?'; $params[] = $available; }
if ($lat !== null)       { $sets[] = 'latitude=?';    $params[] = $lat; }
if ($lng !== null)       { $sets[] = 'longitude=?';   $params[] = $lng; }

if (!empty($sets)) {
    $params[] = $worker['id'];
    DB::query("UPDATE workers SET ".implode(',',$sets)." WHERE id=?", $params);
}
echo json_encode(['success'=>true,'available'=>(bool)$available]);
