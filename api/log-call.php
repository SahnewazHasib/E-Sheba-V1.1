<?php
// ============================================================
// API: Call Logging — E-Sheba (Person 5)
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

Auth::requireLogin();
$myId = Auth::id();

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$action   = $input['action'] ?? 'start';
$channel  = trim($input['channel'] ?? '');
$calleeId = (int)($input['callee_id'] ?? 0);
$duration = (int)($input['duration'] ?? 0);

if (!$channel) {
    echo json_encode(['success' => false, 'error' => 'Missing channel']);
    exit;
}

switch ($action) {
    case 'start':
        DB::query(
            "INSERT INTO call_logs (caller_id, callee_id, channel, status) VALUES (?, ?, ?, 'initiated')",
            [$myId, $calleeId, $channel]
        );
        break;

    case 'answered':
        DB::query(
            "UPDATE call_logs SET status = 'answered', started_at = NOW() WHERE channel = ? ORDER BY id DESC LIMIT 1",
            [$channel]
        );
        break;

    case 'declined':
        DB::query(
            "UPDATE call_logs SET status = 'declined', ended_at = NOW() WHERE channel = ? ORDER BY id DESC LIMIT 1",
            [$channel]
        );
        break;

    case 'ended':
        DB::query(
            "UPDATE call_logs SET status = 'answered', ended_at = NOW(), duration_sec = ? WHERE channel = ? ORDER BY id DESC LIMIT 1",
            [$duration, $channel]
        );
        break;
}

echo json_encode(['success' => true]);
