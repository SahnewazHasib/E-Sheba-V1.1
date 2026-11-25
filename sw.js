// E-Sheba Service Worker — PWA offline support
const CACHE_NAME = 'esheba-v1.0.0';
const STATIC_ASSETS = [
  '/e-sheba/',
  '/e-sheba/index.php',
  '/e-sheba/css/style.css',
  '/e-sheba/js/app.js',
  '/e-sheba/js/chat.js',
  '/e-sheba/js/call.js',
  '/e-sheba/login.php',
  '/e-sheba/register.php',
  '/e-sheba/categories.php',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
];

// ── Install: cache static assets ─────────────────────────────
self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

// ── Activate: clean old caches ────────────────────────────────
self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// ── Fetch: network-first with cache fallback ──────────────────
self.addEventListener('fetch', (e) => {
  if (e.request.method !== 'GET') return;
  const url = new URL(e.request.url);
  // Don't cache API calls
  if (url.pathname.includes('/api/')) return;

  e.respondWith(
    fetch(e.request)
      .then(res => {
        // Cache successful responses
        if (res.status === 200) {
          const clone = res.clone();
          caches.open(CACHE_NAME).then(c => c.put(e.request, clone));
        }
        return res;
      })
      .catch(() => caches.match(e.request).then(cached => cached || offlinePage()))
  );
});

function offlinePage() {
  return new Response(`<!DOCTYPE html><html><body style="font-family:sans-serif;text-align:center;padding:60px;background:#f4f6fb">
  <div style="font-size:72px">📡</div>
  <h2 style="color:#1a73e8">You're Offline</h2>
  <p style="color:#666">Please check your internet connection and try again.</p>
  <button onclick="location.reload()" style="background:#1a73e8;color:#fff;border:none;padding:12px 32px;border-radius:50px;font-size:16px;cursor:pointer">Retry</button>
  </body></html>`, { headers: { 'Content-Type': 'text/html' } });
}

// ── Push Notifications (future) ───────────────────────────────
self.addEventListener('push', (e) => {
  const data = e.data?.json() || {};
  e.waitUntil(
    self.registration.showNotification(data.title || 'E-Sheba', {
      body: data.body || 'You have a new notification',
      icon: '/e-sheba/images/icons/icon-192.png',
      badge:'/e-sheba/images/icons/icon-72.png',
      data: data.url || '/e-sheba/',
      vibrate: [200, 100, 200],
    })
  );
});

self.addEventListener('notificationclick', (e) => {
  e.notification.close();
  e.waitUntil(clients.openWindow(e.notification.data || '/e-sheba/'));
});
