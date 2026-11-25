// ============================================================
// E-Sheba — Real-time Chat (Pusher) + UI Logic
// ============================================================

let pusher, channel, typingTimeout;
let isTyping = false;

document.addEventListener('DOMContentLoaded', function () {
  if (typeof CHAT_CONFIG === 'undefined') return;
  if (!CHAT_CONFIG.peerId) return;

  initPusher();
});

// ── Init Pusher ──────────────────────────────────────────────
function initPusher() {
  pusher = new Pusher(CHAT_CONFIG.pusherKey, {
    cluster: CHAT_CONFIG.pusherCluster,
    authEndpoint: 'api/pusher-auth.php',
  });

  // ── Private channel for this conversation ──
  const channelName = getChannelName(CHAT_CONFIG.myId, CHAT_CONFIG.peerId);
  channel = pusher.subscribe('private-' + channelName);

  channel.bind('pusher:subscription_succeeded', () => {
    console.log('[Pusher] Connected to channel:', channelName);
  });

  // ── Incoming message ──
  channel.bind('new-message', function (data) {
    if (data.sender_id === CHAT_CONFIG.myId) return; // already shown
    appendMessage(data, false);
    scrollToBottom();
    // Mark as read via API
    $.post('api/mark-read.php', { sender_id: data.sender_id });
    // Update thread preview in sidebar
    updateSidebarPreview(data.sender_id, data.body);
  });

  // ── Typing indicator ──
  channel.bind('typing', function (data) {
    if (data.user_id !== CHAT_CONFIG.myId) {
      showTypingIndicator();
    }
  });

  // ── Incoming call signal ──
  channel.bind('call-incoming', function (data) {
    if (data.caller_id !== CHAT_CONFIG.myId) {
      showIncomingCall(data.caller_id, data.caller_name, data.channel);
    }
  });

  channel.bind('call-ended', function () {
    endCallUI();
  });

  channel.bind('call-declined', function () {
    endCallUI();
    showToast('Call declined', 'error');
  });

  channel.bind('call-accepted', function (data) {
    joinAgoraChannel(data.channel);
  });

  // ── Presence (online status) ──
  const presenceChannel = pusher.subscribe('presence-esheba');
  presenceChannel.bind('pusher:member_added', function (member) {
    if (member.id == CHAT_CONFIG.peerId) {
      document.getElementById('peerOnlineDot').style.display = 'block';
      const dot = document.getElementById('dot-' + member.id);
      if (dot) dot.style.display = 'block';
    }
  });
  presenceChannel.bind('pusher:member_removed', function (member) {
    if (member.id == CHAT_CONFIG.peerId) {
      document.getElementById('peerOnlineDot').style.display = 'none';
    }
  });
}

function getChannelName(a, b) {
  return 'chat-' + Math.min(a, b) + '-' + Math.max(a, b);
}

// ── Send Message ─────────────────────────────────────────────
function sendMessage() {
  const input = document.getElementById('msgInput');
  const body = input.value.trim();
  if (!body) return;

  const btn = document.getElementById('sendBtn');
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner" style="width:20px;height:20px;border-width:2px"></div>';

  $.ajax({
    url:  'api/send-message.php',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({
      receiver_id: CHAT_CONFIG.peerId,
      body: body,
    }),
    success: function (res) {
      if (res.success) {
        // Append immediately (optimistic UI)
        appendMessage({ body, created_at: new Date().toISOString(), sender_id: CHAT_CONFIG.myId, id: res.id }, true);
        input.value = '';
        input.style.height = 'auto';
        scrollToBottom();
        updateSidebarPreview(CHAT_CONFIG.peerId, body, true);
      } else {
        showToast(res.error || 'Failed to send', 'error');
      }
    },
    error: () => showToast('Network error. Please retry.', 'error'),
    complete: () => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa fa-paper-plane"></i>';
    }
  });
}

// ── Append Message Bubble ────────────────────────────────────
function appendMessage(msg, isMe) {
  const box   = document.getElementById('messagesBox');
  if (!box) return;

  // Remove empty-state if present
  box.querySelectorAll('.text-center.py-5').forEach(el => el.remove());

  const time = formatTime(msg.created_at);
  const html = `
    <div class="msg-row ${isMe ? 'mine' : ''}">
      ${!isMe ? `<div class="chat-avatar" style="width:32px;height:32px;font-size:.7rem;flex-shrink:0">${(CHAT_CONFIG.peerName||'?')[0].toUpperCase()}</div>` : ''}
      <div class="msg-bubble ${isMe ? 'mine' : 'theirs'}">
        ${escapeHtml(msg.body)}
        <span class="msg-time">${time}${isMe ? ' <span class="msg-read">✓</span>' : ''}</span>
      </div>
    </div>`;
  box.insertAdjacentHTML('beforeend', html);
  box.scrollTop = box.scrollHeight;
}

// ── Typing Indicator ─────────────────────────────────────────
let typingVisible = false;
function showTypingIndicator() {
  const el = document.getElementById('typingIndicator');
  if (!el) return;
  el.style.display = 'block';
  typingVisible = true;
  clearTimeout(typingTimeout);
  typingTimeout = setTimeout(() => {
    el.style.display = 'none';
    typingVisible = false;
  }, 3000);
  scrollToBottom();
}

function sendTyping() {
  if (!channel) return;
  if (!isTyping) {
    isTyping = true;
    $.post('api/send-typing.php', { receiver_id: CHAT_CONFIG.peerId });
    setTimeout(() => { isTyping = false; }, 2000);
  }
}

// ── Sidebar Preview Update ───────────────────────────────────
function updateSidebarPreview(peerId, body, isMine) {
  const item = document.querySelector(`.chat-list-item[data-id="${peerId}"]`);
  if (!item) return;
  const preview = item.querySelector('.chat-list-preview');
  if (preview) preview.textContent = (isMine ? 'You: ' : '') + body.substring(0, 36);
  // Clear unread badge if mine
  if (isMine) {
    const badge = item.querySelector('.unread-count');
    if (badge) badge.remove();
  }
}

// ── Utilities ─────────────────────────────────────────────────
function scrollToBottom() {
  const box = document.getElementById('messagesBox');
  if (box) box.scrollTop = box.scrollHeight;
}
function formatTime(ts) {
  const d = new Date(ts);
  return d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0');
}
function escapeHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
}
