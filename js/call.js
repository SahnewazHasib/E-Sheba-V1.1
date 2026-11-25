// ============================================================
// E-Sheba — Agora Voice Calling
// ============================================================
// Requires Agora Web SDK 4.x (loaded via CDN or npm)
// <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>

let agoraClient, localAudioTrack;
let callTimerInterval, callSeconds = 0;
let callState = 'idle'; // idle | calling | in-call | incoming
let currentCallData = {};
let isMuted = false, isSpeaker = true;
let pendingCallChannel = null;

// ── Initialize Agora Client ──────────────────────────────────
function initAgora() {
  if (typeof AgoraRTC === 'undefined') {
    console.warn('Agora SDK not loaded. Add: <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"><\/script>');
    return;
  }
  agoraClient = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });
  agoraClient.on('user-published', async (user, mediaType) => {
    await agoraClient.subscribe(user, mediaType);
    if (mediaType === 'audio') user.audioTrack.play();
  });
  agoraClient.on('user-left', () => {
    endCall();
  });
}

// ── Initiate Outgoing Call ────────────────────────────────────
async function initiateCall(peerId, peerName) {
  if (callState !== 'idle') return;
  if (!CHAT_CONFIG || !CHAT_CONFIG.agoraAppId) {
    showToast('Calling not configured. Set Agora App ID.', 'warning');
    return;
  }
  callState = 'calling';
  currentCallData = { peerId, peerName };

  // Show call overlay
  document.getElementById('callOverlay').classList.add('active');
  document.getElementById('callAvatar').textContent = peerName[0].toUpperCase();
  document.getElementById('callName').textContent = peerName;
  document.getElementById('callStatus').textContent = 'Calling...';

  // Get Agora token + channel from server
  const res = await fetch(`api/agora-token.php?peer_id=${peerId}`);
  const data = await res.json();
  if (!data.token) { endCall(); showToast('Could not start call.','error'); return; }

  currentCallData.channel = data.channel;
  currentCallData.token   = data.token;

  // Signal peer via Pusher (through PHP endpoint)
  $.post('api/call-signal.php', {
    action:      'call',
    callee_id:   peerId,
    caller_name: (typeof CHAT_CONFIG !== 'undefined' && CHAT_CONFIG.myName) || 'Unknown',
    channel:     data.channel,
  });

  // Log call
  $.post('api/log-call.php', { callee_id: peerId, channel: data.channel });
}

// ── Join Agora Channel (after accepted) ──────────────────────
async function joinAgoraChannel(channel) {
  try {
    document.getElementById('callStatus').textContent = 'Connected';
    startCallTimer();

    if (typeof AgoraRTC === 'undefined') return;
    const token = currentCallData.token;
    await agoraClient.join(CHAT_CONFIG.agoraAppId, channel, token, CHAT_CONFIG.myId);
    localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
    await agoraClient.publish([localAudioTrack]);
    callState = 'in-call';
  } catch (err) {
    console.error('Agora join error:', err);
    showToast('Could not connect audio.', 'error');
  }
}

// ── Show Incoming Call ────────────────────────────────────────
function showIncomingCall(callerId, callerName, channel) {
  if (callState !== 'idle') {
    // Auto-decline if busy
    $.post('api/call-signal.php', { action:'decline', caller_id:callerId, channel });
    return;
  }
  callState = 'incoming';
  pendingCallChannel = channel;
  currentCallData = { peerId: callerId, peerName: callerName, channel };

  document.getElementById('incomingCallOverlay').classList.add('active');
  document.getElementById('incomingCallAvatar').textContent = callerName[0].toUpperCase();
  document.getElementById('incomingCallName').textContent = callerName;

  // Ring sound (optional — add ring.mp3 to /sounds/)
  // ringAudio = new Audio('sounds/ring.mp3'); ringAudio.loop=true; ringAudio.play();
}

function acceptCall() {
  if (!pendingCallChannel) return;
  document.getElementById('incomingCallOverlay').classList.remove('active');
  document.getElementById('callOverlay').classList.add('active');
  document.getElementById('callAvatar').textContent = (currentCallData.peerName||'?')[0].toUpperCase();
  document.getElementById('callName').textContent = currentCallData.peerName;
  document.getElementById('callStatus').textContent = 'Connected';

  $.post('api/call-signal.php', {
    action:    'accept',
    caller_id: currentCallData.peerId,
    channel:   pendingCallChannel,
  });

  joinAgoraChannel(pendingCallChannel);
}

function declineCall() {
  if (!pendingCallChannel) return;
  $.post('api/call-signal.php', {
    action:    'decline',
    caller_id: currentCallData.peerId,
    channel:   pendingCallChannel,
  });
  document.getElementById('incomingCallOverlay').classList.remove('active');
  callState = 'idle';
  pendingCallChannel = null;
}

// ── End Call ─────────────────────────────────────────────────
async function endCall() {
  if (callState === 'idle') return;
  const prevState = callState;
  callState = 'idle';

  // Signal peer
  if (currentCallData.peerId) {
    $.post('api/call-signal.php', {
      action:   'end',
      peer_id:  currentCallData.peerId,
      channel:  currentCallData.channel,
      duration: callSeconds,
    });
  }

  endCallUI();
  if (prevState === 'in-call') showToast(`Call ended · ${formatCallDuration(callSeconds)}`, 'info');

  // Leave Agora
  try {
    if (localAudioTrack) { localAudioTrack.stop(); localAudioTrack.close(); }
    if (agoraClient && agoraClient.connectionState !== 'DISCONNECTED') await agoraClient.leave();
  } catch(e) {}
  localAudioTrack = null;
}

function endCallUI() {
  document.getElementById('callOverlay').classList.remove('active');
  document.getElementById('incomingCallOverlay').classList.remove('active');
  stopCallTimer();
  callSeconds = 0;
  document.getElementById('callTimer').style.display = 'none';
  document.getElementById('callTimer').textContent = '0:00';
  currentCallData = {};
  pendingCallChannel = null;
  callState = 'idle';
}

// ── Mute / Speaker ───────────────────────────────────────────
function toggleMute() {
  isMuted = !isMuted;
  if (localAudioTrack) localAudioTrack.setEnabled(!isMuted);
  const btn = document.getElementById('muteBtn');
  if (btn) {
    btn.innerHTML = `<i class="fa fa-microphone${isMuted?'-slash':''}"></i>`;
    btn.style.background = isMuted ? 'rgba(234,67,53,.7)' : 'rgba(255,255,255,.2)';
  }
  showToast(isMuted ? 'Microphone muted' : 'Microphone on', 'info');
}

function toggleSpeaker() {
  isSpeaker = !isSpeaker;
  // On mobile browser, can't truly toggle speaker but we can show state
  const btn = document.getElementById('speakerBtn');
  if (btn) {
    btn.innerHTML = `<i class="fa fa-volume-${isSpeaker?'up':'mute'}"></i>`;
  }
}

// ── Call Timer ───────────────────────────────────────────────
function startCallTimer() {
  callSeconds = 0;
  document.getElementById('callTimer').style.display = 'block';
  callTimerInterval = setInterval(() => {
    callSeconds++;
    document.getElementById('callTimer').textContent = formatCallDuration(callSeconds);
  }, 1000);
}
function stopCallTimer() {
  clearInterval(callTimerInterval);
}
function formatCallDuration(s) {
  const m = Math.floor(s / 60);
  return m + ':' + (s % 60).toString().padStart(2, '0');
}

// ── Init on page load ────────────────────────────────────────
document.addEventListener('DOMContentLoaded', initAgora);
