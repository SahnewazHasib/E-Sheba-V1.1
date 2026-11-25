<?php
// ============================================================
// API: Pusher Channel Authentication — E-Sheba (Person 5)
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

Auth::requireLogin();
$myId   = Auth::id();
$myName = $_SESSION['user_name'] ?? 'User';

$socketId    = $_POST['socket_id'] ?? '';
$channelName = $_POST['channel_name'] ?? '';

if (!$socketId || !$channelName) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing socket_id or channel_name']);
    exit;
}

// Security Check: Verify user belongs to the channel
if (str_starts_with($channelName, 'private-chat-')) {
    $parts = explode('-', str_replace('private-chat-', '', $channelName));
    if (count($parts) === 2) {
        $u1 = (int)$parts[0];
        $u2 = (int)$parts[1];
        if ($myId !== $u1 && $myId !== $u2 && Auth::role() !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden channel']);
            exit;
        }
    }
}

// Presence Channel Handling
if (str_starts_with($channelName, 'presence-')) {
    $channelData = json_encode([
        'user_id'   => (string)$myId,
        'user_info' => [
            'name' => $myName,
            'role' => Auth::role()
        ]
    ]);
    $stringToSign = "{$socketId}:{$channelName}:{$channelData}";
    $signature    = hash_hmac('sha256', $stringToSign, PUSHER_SECRET);
    echo json_encode([
        'auth'         => PUSHER_KEY . ':' . $signature,
        'channel_data' => $channelData
    ]);
    exit;
}

// Private Channel Signature
$stringToSign = "{$socketId}:{$channelName}";
$signature    = hash_hmac('sha256', $stringToSign, PUSHER_SECRET);

echo json_encode([
    'auth' => PUSHER_KEY . ':' . $signature
]);
