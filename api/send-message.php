<?php
// api/send-message.php — Send a message (Pusher real-time)
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/config.php';
header('Content-Type: application/json');
Auth::requireLogin();
Auth::start();

$data = json_decode(file_get_contents('php://input'), true);
$senderId   = Auth::id();
$receiverId = (int)($data['receiver_id'] ?? 0);
$body       = trim($data['body'] ?? '');

if (!$receiverId || !$body) {
    echo json_encode(['success'=>false,'error'=>'Invalid data']); exit;
}
if (mb_strlen($body) > 2000) {
    echo json_encode(['success'=>false,'error'=>'Message too long']); exit;
}

// Save to DB
DB::query(
    "INSERT INTO messages (sender_id, receiver_id, body) VALUES (?,?,?)",
    [$senderId, $receiverId, $body]
);
$msgId = DB::lastInsertId();

// Push via Pusher
try {
    // Using Pusher PHP SDK (composer require pusher/pusher-php-server)
    // require_once '../vendor/autoload.php';
    // $pusher = new Pusher\Pusher(PUSHER_KEY, PUSHER_SECRET, PUSHER_APP_ID, ['cluster'=>PUSHER_CLUSTER,'useTLS'=>true]);
    $channelName = 'private-chat-' . min($senderId,$receiverId) . '-' . max($senderId,$receiverId);
    $payload = [
        'id'          => (int)$msgId,
        'sender_id'   => $senderId,
        'receiver_id' => $receiverId,
        'body'        => $body,
        'created_at'  => date('Y-m-d H:i:s'),
    ];
    // $pusher->trigger($channelName, 'new-message', $payload);
    // For demo without SDK, use Pusher HTTP API directly:
    triggerPusher($channelName, 'new-message', $payload);

    // Create notification for receiver
    DB::query(
        "INSERT INTO notifications (user_id,type,title,body) VALUES (?,?,?,?)",
        [$receiverId,'message','New Message',mb_substr($body,0,80)]
    );

    echo json_encode(['success'=>true,'id'=>(int)$msgId]);
} catch (Exception $e) {
    // Message saved but push failed
    echo json_encode(['success'=>true,'id'=>(int)$msgId,'warn'=>'Push failed']);
}

// ── Minimal Pusher HTTP trigger (no SDK) ────────────────────
function triggerPusher($channel, $event, $data) {
    $appId   = PUSHER_APP_ID;
    $key     = PUSHER_KEY;
    $secret  = PUSHER_SECRET;
    $cluster = PUSHER_CLUSTER;

    $body    = json_encode(['name'=>$event,'channels'=>[$channel],'data'=>json_encode($data)]);
    $path    = "/apps/{$appId}/events";
    $ts      = time();
    $md5body = md5($body, false);
    $strToSign = "POST\n{$path}\nauth_key={$key}&auth_timestamp={$ts}&auth_version=1.0&body_md5={$md5body}";
    $authSig = hash_hmac('sha256', $strToSign, $secret);

    $url = "https://api-{$cluster}.pusher.com{$path}?auth_key={$key}&auth_timestamp={$ts}&auth_version=1.0&body_md5={$md5body}&auth_signature={$authSig}";
    $opts = ['http'=>['method'=>'POST','header'=>'Content-Type: application/json','content'=>$body]];
    @file_get_contents($url, false, stream_context_create($opts));
}
