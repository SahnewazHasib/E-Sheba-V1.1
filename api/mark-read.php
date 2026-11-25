<?php
// ============================================================
// API: Mark Messages as Read — E-Sheba
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

Auth::requireLogin();
$myId = Auth::id();

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$peerId = (int)($input['peer_id'] ?? 0);

if (!$peerId) {
    echo json_encode(['success' => false, 'error' => 'Missing peer_id']);
    exit;
}

DB::query(
    "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0",
    [$peerId, $myId]
);

echo json_encode(['success' => true]);
