<?php
// ============================================================
// API: Send Typing Indicator — E-Sheba
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

Auth::requireLogin();
$myId = Auth::id();

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$receiverId = (int)($input['receiver_id'] ?? 0);
$isTyping   = (bool)($input['is_typing'] ?? false);

if (!$receiverId) {
    echo json_encode(['success' => false]);
    exit;
}

// Trigger Pusher typing event directly
try {
    $channelName = 'private-chat-' . min($myId, $receiverId) . '-' . max($myId, $receiverId);
    $appId   = PUSHER_APP_ID;
    $key     = PUSHER_KEY;
    $secret  = PUSHER_SECRET;
    $cluster = PUSHER_CLUSTER;

    if ($key !== 'your_pusher_key') {
        $body = json_encode([
            'name' => 'client-typing',
            'channels' => [$channelName],
            'data' => json_encode(['sender_id' => $myId, 'is_typing' => $isTyping])
        ]);
        $path = "/apps/{$appId}/events";
        $ts = time();
        $md5 = md5($body);
        $sig = hash_hmac('sha256', "POST\n{$path}\nauth_key={$key}&auth_timestamp={$ts}&auth_version=1.0&body_md5={$md5}", $secret);
        $url = "https://api-{$cluster}.pusher.com{$path}?auth_key={$key}&auth_timestamp={$ts}&auth_version=1.0&body_md5={$md5}&auth_signature={$sig}";
        @file_get_contents($url, false, stream_context_create(['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => $body]]));
    }
} catch (Exception $e) {}

echo json_encode(['success' => true]);
