// ============================================================
// E-Sheba — Global App JS
// ============================================================

// ── Toast Notification System ────────────────────────────────
function showToast(message, type = 'info', title = '') {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const icons = {
    success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️',
  };
  const defaults = {
    success: 'Success', error: 'Error', warning: 'Warning', info: 'Info',
  };
  const toast = document.createElement('div');
  toast.className = `toast-esheba ${type}`;
  toast.innerHTML = `
    <div class="toast-icon">${icons[type]||'ℹ️'}</div>
    <div class="toast-body">
      <div class="toast-title">${title || defaults[type]}</div>
      <div class="toast-msg">${message}</div>
    </div>
    <div class="toast-close" onclick="this.parentElement.remove()">×</div>
  `;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'toastIn .3s ease reverse';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

// ── PWA Install Prompt ────────────────────────────────────────
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  // Show install button if not already installed
  const installBtns = document.querySelectorAll('[data-pwa-install]');
  installBtns.forEach(btn => btn.style.display = 'inline-flex');
});

function installPWA() {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(choice => {
      if (choice.outcome === 'accepted') {
        showToast('E-Sheba installed on your device!', 'success');
      }
      deferredPrompt = null;
    });
  } else {
    showToast('To install: open in Chrome → Menu → "Add to Home Screen"', 'info', 'Install App');
  }
}

// ── AJAX helper ──────────────────────────────────────────────
function apiPost(url, data, onSuccess, onError) {
  $.ajax({
    url, type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(data),
    headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' },
    success: onSuccess,
    error: (xhr) => {
      const msg = xhr.responseJSON?.error || 'An error occurred.';
      if (onError) onError(msg); else showToast(msg, 'error');
    }
  });
}

// ── Live notification polling (fallback for Pusher) ──────────
function pollNotifications() {
  if (typeof CHAT_CONFIG === 'undefined') {
    // Lightweight polling if Pusher not set up
    setInterval(() => {
      $.getJSON('api/unread-counts.php', function(res) {
        const msgBadge  = document.getElementById('msg-count');
        const notifBadge= document.getElementById('notif-count');
        if (msgBadge) {
          msgBadge.textContent  = res.messages || 0;
          msgBadge.style.display= res.messages > 0 ? 'flex' : 'none';
        }
        if (notifBadge) {
          notifBadge.textContent  = res.notifications || 0;
          notifBadge.style.display= res.notifications > 0 ? 'flex' : 'none';
        }
      });
    }, 15000); // every 15s
  }
}

// ── Smooth number counter animation ──────────────────────────
function animateCounters() {
  document.querySelectorAll('.hero-stat .num').forEach(el => {
    const target = parseFloat(el.textContent.replace(/[^0-9.]/g, ''));
    const suffix = el.textContent.replace(/[0-9.,]/g, '');
    if (!target) return;
    let count = 0;
    const step = target / 60;
    const interval = setInterval(() => {
      count = Math.min(count + step, target);
      el.textContent = (Number.isInteger(target) ? Math.round(count) : count.toFixed(1)) + suffix;
      if (count >= target) clearInterval(interval);
    }, 20);
  });
}

// ── Rating stars helper ───────────────────────────────────────
function renderStars(rating, max = 5) {
  let html = '';
  for (let i = 1; i <= max; i++) {
    html += i <= Math.round(rating) ? '★' : '☆';
  }
  return html;
}

// ── Date/time utilities ───────────────────────────────────────
function timeAgo(ts) {
  const diff = Math.floor((Date.now() - new Date(ts)) / 1000);
  if (diff < 60) return 'just now';
  if (diff < 3600) return Math.floor(diff/60) + 'm ago';
  if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
  return Math.floor(diff/86400) + 'd ago';
}

// ── Scroll animations ─────────────────────────────────────────
function initScrollAnimations() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.category-card, .worker-card, .stat-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity .4s ease, transform .4s ease';
    observer.observe(el);
  });
}

// ── Location: request GPS coordinates ────────────────────────
function requestLocation(callback, errorCallback) {
  if (!navigator.geolocation) {
    if (typeof errorCallback === 'function') errorCallback();
    else showToast('Geolocation not supported.', 'warning');
    return;
  }
  navigator.geolocation.getCurrentPosition(
    pos => callback(pos.coords.latitude, pos.coords.longitude),
    err => {
      if (typeof errorCallback === 'function') {
        errorCallback(err);
      } else {
        const msgs = {1:'Location permission denied.',2:'Could not detect location.',3:'Location request timed out.'};
        showToast(msgs[err.code]||'Location error.','warning');
      }
    },
    { enableHighAccuracy: false, timeout: 5000 }
  );
}

// ── Map: show worker location ─────────────────────────────────
function initWorkerMap(lat, lng, name, available, containerId = 'map') {
  if (!window.L) return;
  const container = document.getElementById(containerId);
  if (!container) return;
  if (container._leaflet_id) { container._leaflet_id = null; }
  const map = L.map(containerId, { zoomControl: true }).setView([lat, lng], 14);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 18,
  }).addTo(map);

  const icon = L.divIcon({
    html: `<div style="background:${available?'var(--success)':'#999'};width:36px;height:36px;border-radius:50%;border:3px solid #fff;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px;box-shadow:0 2px 8px rgba(0,0,0,.25)">${name[0].toUpperCase()}</div>`,
    iconSize: [36, 36], iconAnchor: [18, 18], className: '',
  });

  L.marker([lat, lng], { icon })
   .addTo(map)
   .bindPopup(`<strong>${name}</strong><br>${available ? '✅ Available' : '❌ Busy'}`)
   .openPopup();
}

// ── Confirm dialog ────────────────────────────────────────────
function confirmAction(message, onConfirm) {
  if (window.confirm(message)) onConfirm();
}

// ── Form: loading state ───────────────────────────────────────
function setButtonLoading(btn, loading) {
  if (loading) {
    btn.disabled = true;
    btn.dataset.origText = btn.innerHTML;
    btn.classList.add('btn-loading');
  } else {
    btn.disabled = false;
    btn.innerHTML = btn.dataset.origText || btn.innerHTML;
    btn.classList.remove('btn-loading');
  }
}

// ── Init ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  animateCounters();
  initScrollAnimations();
  pollNotifications();

  // Service worker registration (PWA)
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js').catch(() => {});
  }

  // Active nav link
  const path = window.location.pathname;
  document.querySelectorAll('.nav-link').forEach(link => {
    if (link.getAttribute('href') && path.includes(link.getAttribute('href').split('?')[0])) {
      link.classList.add('active');
    }
  });
});
