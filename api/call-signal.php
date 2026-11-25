<?php
// api/call-signal.php — Send call signals via Pusher
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/config.php';
header('Content-Type: application/json');
Auth::requireLogin();

$myId   = Auth::id();
$myName = $_SESSION['user_name'] ?? 'User';
$action = $_POST['action'] ?? '';

function triggerPusher($channel, $event, $data) {
    $appId=$_ENV['PUSHER_APP_ID']??PUSHER_APP_ID; $key=PUSHER_KEY; $secret=PUSHER_SECRET; $cluster=PUSHER_CLUSTER;
    $body=json_encode(['name'=>$event,'channels'=>[$channel],'data'=>json_encode($data)]);
    $path="/apps/{$appId}/events"; $ts=time(); $md5=md5($body);
    $str="POST\n{$path}\nauth_key={$key}&auth_timestamp={$ts}&auth_version=1.0&body_md5={$md5}";
    $sig=hash_hmac('sha256',$str,$secret);
    $url="https://api-{$cluster}.pusher.com{$path}?auth_key={$key}&auth_timestamp={$ts}&auth_version=1.0&body_md5={$md5}&auth_signature={$sig}";
    @file_get_contents($url,false,stream_context_create(['http'=>['method'=>'POST','header'=>'Content-Type: application/json','content'=>$body]]));
}

function getChannel($a,$b){ return 'private-chat-'.min($a,$b).'-'.max($a,$b); }

switch($action) {
    case 'call':
        $calleeId = (int)$_POST['callee_id'];
        $channel  = $_POST['channel'];
        $ch = getChannel($myId,$calleeId);
        triggerPusher($ch,'call-incoming',['caller_id'=>$myId,'caller_name'=>$myName,'channel'=>$channel]);
        // Notification
        DB::query("INSERT INTO notifications(user_id,type,title,body) VALUES(?,?,?,?)",[$calleeId,'call','Incoming Call',$myName.' is calling you']);
        break;

    case 'accept':
        $callerId = (int)$_POST['caller_id'];
        $channel  = $_POST['channel'];
        $ch = getChannel($myId,$callerId);
        triggerPusher($ch,'call-accepted',['channel'=>$channel]);
        // Update call log
        DB::query("UPDATE call_logs SET status='answered',started_at=NOW() WHERE channel=? AND callee_id=?",[$channel,$myId]);
        break;

    case 'decline':
        $callerId = (int)$_POST['caller_id'];
        $channel  = $_POST['channel'];
        $ch = getChannel($myId,$callerId);
        triggerPusher($ch,'call-declined',[]);
        DB::query("UPDATE call_logs SET status='declined' WHERE channel=?",[$channel]);
        break;

    case 'end':
        $peerId   = (int)$_POST['peer_id'];
        $channel  = $_POST['channel'];
        $duration = (int)$_POST['duration'];
        $ch = getChannel($myId,$peerId);
        triggerPusher($ch,'call-ended',[]);
        DB::query("UPDATE call_logs SET ended_at=NOW(),duration_sec=?,status='answered' WHERE channel=?",[$duration,$channel]);
        break;
}
echo json_encode(['success'=>true]);
