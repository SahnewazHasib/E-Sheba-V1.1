<?php
// api/agora-token.php — Generate Agora RTC token
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/config.php';
header('Content-Type: application/json');
Auth::requireLogin();

$myId   = Auth::id();
$peerId = (int)($_GET['peer_id'] ?? 0);
if (!$peerId) { echo json_encode(['error'=>'Invalid peer']); exit; }

$channel = 'esheba-call-' . min($myId,$peerId) . '-' . max($myId,$peerId);
$expiry  = time() + 3600;

// ── Agora token generation ────────────────────────────────────
// For production, use the official Agora token builder library:
// https://github.com/AgoraIO/Tools/tree/master/DynamicKey/AgoraDynamicKey/php
// Here we build a simple token for demonstration:
$appId   = AGORA_APP_ID;
$appCert = AGORA_APP_CERT;

if ($appCert) {
    // Build real Agora token using RtcTokenBuilder
    // require_once '../vendor/agora/RtcTokenBuilder2.php';
    // $token = RtcTokenBuilder2::buildTokenWithUid($appId, $appCert, $channel, $myId, RtcTokenBuilder2::ROLE_PUBLISHER, $expiry, $expiry);
    $token = "DEMO_TOKEN_$channel"; // Replace with real token builder
} else {
    $token = null; // Agora allows null token for testing (disable auth in console)
}

echo json_encode(['channel'=>$channel,'token'=>$token,'uid'=>$myId,'expiry'=>$expiry]);
