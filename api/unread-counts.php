<?php
// api/unread-counts.php — Unread messages & notification counts
require_once '../includes/auth.php';
require_once '../includes/db.php';
header('Content-Type: application/json');
Auth::start();
if (!Auth::isLoggedIn()) { echo json_encode(['messages'=>0,'notifications'=>0]); exit; }

$me = Auth::id();
$msgs  = DB::fetchOne("SELECT COUNT(*) as c FROM messages WHERE receiver_id=? AND is_read=0",[$me]);
$notif = DB::fetchOne("SELECT COUNT(*) as c FROM notifications WHERE user_id=? AND is_read=0",[$me]);
echo json_encode(['messages'=>(int)$msgs['c'],'notifications'=>(int)$notif['c']]);
