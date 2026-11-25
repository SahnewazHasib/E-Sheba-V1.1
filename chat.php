<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages — E-Sheba</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/config.php';
Auth::requireLogin();
$me     = Auth::id();
$toId   = (int)($_GET['to'] ?? 0);
$myRole = Auth::role();

// Get conversation threads
$threads = DB::fetchAll(
    "SELECT
       u.id as peer_id, u.name as peer_name, u.avatar as peer_avatar,
       MAX(m.id) as last_msg_id,
       (SELECT body FROM messages WHERE id=MAX(m.id)) as last_body,
       (SELECT created_at FROM messages WHERE id=MAX(m.id)) as last_time,
       SUM(CASE WHEN m.receiver_id=? AND m.is_read=0 THEN 1 ELSE 0 END) as unread
     FROM messages m
     JOIN users u ON u.id = CASE WHEN m.sender_id=? THEN m.receiver_id ELSE m.sender_id END
     WHERE m.sender_id=? OR m.receiver_id=?
     GROUP BY u.id
     ORDER BY last_msg_id DESC",
    [$me,$me,$me,$me]
);

// If a specific peer is selected, load messages
$peer = null; $messages = [];
if ($toId) {
    $peer = DB::fetchOne("SELECT id,name,avatar,role FROM users WHERE id=?", [$toId]);
    // Mark messages as read
    DB::query("UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=?", [$toId,$me]);
    // Load messages
    $messages = DB::fetchAll(
        "SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY created_at ASC",
        [$me,$toId,$toId,$me]
    );
    // Add peer to threads list if not there
    if (!$peer && $toId) { $peer = DB::fetchOne("SELECT * FROM users WHERE id=?",[$toId]); }
}

// Worker availability for call
$workerAvailable = false;
if ($toId) {
    $wInfo = DB::fetchOne("SELECT is_available FROM workers WHERE user_id=?",[$toId]);
    $workerAvailable = $wInfo && $wInfo['is_available'];
}
?>

<!-- Minimal Navbar -->
<nav class="navbar-esheba">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.php" class="brand text-decoration-none">🔧 E-Sheba</a>
    <div class="d-flex align-items-center gap-3">
      <?php $dashLink = match($myRole){ 'worker'=>'worker/dashboard.php','admin'=>'admin/dashboard.php',default=>'customer/dashboard.php' }; ?>
      <a href="<?= $dashLink ?>" class="btn btn-link text-muted"><i class="fa fa-tachometer-alt me-1"></i>Dashboard</a>
      <a href="api/logout.php" class="btn btn-link text-danger"><i class="fa fa-sign-out-alt"></i></a>
    </div>
  </div>
</nav>

<div class="container-fluid px-0" style="height:calc(100vh - 72px)">
  <div class="chat-container" style="height:100%;border-radius:0">

    <!-- ══ Sidebar: Thread List ══ -->
    <div class="chat-sidebar">
      <div class="chat-sidebar-header">
        <i class="fa fa-comment-dots me-2"></i>Messages
      </div>
      <div class="chat-search">
        <input type="text" id="threadSearch" placeholder="Search conversations..." oninput="filterThreads(this.value)">
      </div>
      <div class="chat-list" id="threadList">
        <?php if(empty($threads) && !$toId): ?>
          <div class="empty-state py-5" style="padding:40px 16px">
            <div class="empty-icon"><i class="fa fa-comments"></i></div>
            <h5>No conversations yet</h5>
            <p>Find a service provider and start chatting!</p>
          </div>
        <?php else:
          // Ensure the current peer is in the list
          if ($peer && !array_filter($threads, fn($t)=>$t['peer_id']==$toId)):
        ?>
          <div class="chat-list-item active" data-id="<?= $peer['id'] ?>">
            <div class="chat-avatar"><?= strtoupper($peer['name'][0]) ?></div>
            <div class="chat-list-info">
              <div class="chat-list-name"><?= htmlspecialchars($peer['name']) ?></div>
              <div class="chat-list-preview">Start a new conversation</div>
            </div>
          </div>
        <?php endif;
          foreach($threads as $t): $isActive = $t['peer_id'] == $toId; ?>
          <div class="chat-list-item <?= $isActive?'active':'' ?>" data-id="<?= $t['peer_id'] ?>"
               onclick="window.location='chat.php?to=<?= $t['peer_id'] ?>'">
            <div class="chat-avatar" style="position:relative">
              <?= strtoupper($t['peer_name'][0]) ?>
              <!-- Online dot placeholder (Pusher presence) -->
              <span class="online-dot" id="dot-<?= $t['peer_id'] ?>" style="display:none"></span>
            </div>
            <div class="chat-list-info">
              <div class="chat-list-name"><?= htmlspecialchars($t['peer_name']) ?></div>
              <div class="chat-list-preview"><?= htmlspecialchars(mb_substr($t['last_body']??'',0,36)) ?></div>
            </div>
            <div class="d-flex flex-column align-items-end gap-1">
              <div class="chat-list-time"><?= $t['last_time'] ? date('H:i', strtotime($t['last_time'])) : '' ?></div>
              <?php if($t['unread']>0): ?>
              <div class="unread-count"><?= $t['unread'] ?></div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- ══ Main Chat Panel ══ -->
    <?php if(!$peer): ?>
    <!-- No conversation selected -->
    <div class="chat-main d-flex align-items-center justify-content-center">
      <div class="text-center">
        <div style="font-size:72px;color:var(--border)">💬</div>
        <h4 class="fw-700 mt-3">Select a Conversation</h4>
        <p class="text-muted">Choose from your message list or <a href="workers.php">find a provider</a></p>
      </div>
    </div>
    <?php else: ?>
    <div class="chat-main">
      <!-- Chat Header -->
      <div class="chat-header">
        <a href="chat.php" class="btn btn-link text-muted d-md-none me-2 p-0"><i class="fa fa-arrow-left"></i></a>
        <div class="chat-avatar" style="width:40px;height:40px;font-size:.85rem">
          <?= strtoupper($peer['name'][0]) ?>
          <span class="online-dot" id="peerOnlineDot" style="display:none"></span>
        </div>
        <div class="peer-info">
          <div class="peer-name"><?= htmlspecialchars($peer['name']) ?></div>
          <div class="peer-status" id="peerStatus">
            <?= ucfirst($peer['role']) ?>
            <?php if($workerAvailable): ?> · <span style="color:var(--success)">● Available</span><?php endif; ?>
          </div>
        </div>
        <div class="chat-header-actions">
          <?php if($peer['role']==='worker'): ?>
          <button class="chat-action-btn call" onclick="initiateCall(<?= $peer['id'] ?>, '<?= htmlspecialchars($peer['name']) ?>')" title="Voice Call">
            <i class="fa fa-phone"></i>
          </button>
          <?php endif; ?>
          <a href="worker-profile.php?uid=<?= $peer['id'] ?>" class="chat-action-btn info" title="View Profile">
            <i class="fa fa-user"></i>
          </a>
        </div>
      </div>

      <!-- Messages -->
      <div class="chat-messages" id="messagesBox">
        <?php if(empty($messages)): ?>
          <div class="text-center py-5" style="color:var(--muted)">
            <i class="fa fa-comment-dots fa-2x mb-2"></i>
            <p>No messages yet. Say hello! 👋</p>
          </div>
        <?php endif;
        $prevDate='';
        foreach($messages as $msg):
          $isMe = $msg['sender_id'] == $me;
          $date = date('d M Y', strtotime($msg['created_at']));
          if($date !== $prevDate): $prevDate=$date; ?>
            <div class="text-center my-3">
              <span style="background:#e0e7ef;color:#666;font-size:.72rem;padding:4px 14px;border-radius:50px"><?= $date ?></span>
            </div>
          <?php endif; ?>
          <div class="msg-row <?= $isMe?'mine':'' ?>" data-id="<?= $msg['id'] ?>">
            <?php if(!$isMe): ?>
            <div class="chat-avatar" style="width:32px;height:32px;font-size:.7rem;flex-shrink:0">
              <?= strtoupper($peer['name'][0]) ?>
            </div>
            <?php endif; ?>
            <div class="msg-bubble <?= $isMe?'mine':'theirs' ?>">
              <?= nl2br(htmlspecialchars($msg['body'])) ?>
              <span class="msg-time">
                <?= date('H:i', strtotime($msg['created_at'])) ?>
                <?php if($isMe): ?><span class="msg-read"><?= $msg['is_read']?'✓✓':'✓' ?></span><?php endif; ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Typing Indicator -->
      <div id="typingIndicator" style="padding:4px 20px;font-size:.78rem;color:var(--muted);display:none">
        <em><?= htmlspecialchars($peer['name']) ?> is typing<span class="typing-dots">...</span></em>
      </div>

      <!-- Chat Input -->
      <div class="chat-input-area">
        <button class="chat-action-btn" style="background:var(--primary-light);color:var(--primary)" title="Attachment" onclick="showToast('File upload coming soon','info')">
          <i class="fa fa-paperclip"></i>
        </button>
        <textarea id="msgInput" placeholder="Type a message..." rows="1" onkeydown="handleMsgKey(event)" oninput="autoGrow(this); sendTyping()"></textarea>
        <button class="chat-send-btn" id="sendBtn" onclick="sendMessage()">
          <i class="fa fa-paper-plane"></i>
        </button>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Call Overlay -->
<div class="call-overlay" id="callOverlay">
  <div class="call-avatar" id="callAvatar"></div>
  <div class="call-name" id="callName"></div>
  <div class="call-status" id="callStatus">Calling...</div>
  <div id="callTimer" style="font-size:1.2rem;font-weight:600;margin-bottom:24px;display:none">0:00</div>
  <div class="call-controls">
    <button class="call-btn mute" id="muteBtn" onclick="toggleMute()" title="Mute">
      <i class="fa fa-microphone"></i>
    </button>
    <button class="call-btn hangup" onclick="endCall()" title="End Call">
      <i class="fa fa-phone-slash"></i>
    </button>
    <button class="call-btn speaker" id="speakerBtn" onclick="toggleSpeaker()" title="Speaker">
      <i class="fa fa-volume-up"></i>
    </button>
  </div>
</div>

<!-- Incoming Call Overlay -->
<div class="call-overlay" id="incomingCallOverlay" style="background:rgba(13,71,161,.95)">
  <div class="call-avatar" id="incomingCallAvatar"></div>
  <div class="call-name" id="incomingCallName"></div>
  <div class="call-status">Incoming Call...</div>
  <div class="call-controls">
    <button class="call-btn accept" onclick="acceptCall()" title="Accept">
      <i class="fa fa-phone"></i>
    </button>
    <button class="call-btn hangup" onclick="declineCall()" title="Decline">
      <i class="fa fa-phone-slash"></i>
    </button>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="js/app.js"></script>
<script src="js/chat.js"></script>
<script src="js/call.js"></script>
<script>
// Initialize real-time chat
const CHAT_CONFIG = {
  myId:   <?= $me ?>,
  peerId: <?= $toId ?: 'null' ?>,
  peerName: <?= $peer ? json_encode($peer['name']) : 'null' ?>,
  pusherKey: '<?= PUSHER_KEY ?>',
  pusherCluster: '<?= PUSHER_CLUSTER ?>',
  agoraAppId: '<?= AGORA_APP_ID ?>',
};
// Scroll to bottom
const msgBox = document.getElementById('messagesBox');
if(msgBox) msgBox.scrollTop = msgBox.scrollHeight;

// Auto-grow textarea
function autoGrow(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}
function handleMsgKey(e) {
  if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); sendMessage(); }
}
function filterThreads(q) {
  document.querySelectorAll('.chat-list-item').forEach(item => {
    const name = item.querySelector('.chat-list-name')?.textContent.toLowerCase()||'';
    item.style.display = name.includes(q.toLowerCase()) ? '' : 'none';
  });
}
</script>
</body>
</html>
