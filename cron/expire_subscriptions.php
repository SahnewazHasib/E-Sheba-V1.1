<?php
// ============================================================
// Cron Job: Auto-Expire Subscriptions — E-Sheba (Person 4)
// Run daily via Linux cron or Windows Task Scheduler:
// php /path/to/e-sheba/cron/expire_subscriptions.php
// ============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';

$today = date('Y-m-d');
echo "[" . date('Y-m-d H:i:s') . "] Starting subscription expiry check...\n";

// Find all subscriptions that have expired
$expiredSubs = DB::fetchAll(
    "SELECT s.*, w.user_id, w.id as worker_id 
     FROM subscriptions s
     JOIN workers w ON s.worker_id = w.id
     WHERE s.status = 'active' AND s.expires_at < ?",
    [$today]
);

$count = 0;
foreach ($expiredSubs as $sub) {
    // 1. Mark subscription as expired
    DB::query("UPDATE subscriptions SET status = 'expired' WHERE id = ?", [$sub['id']]);

    // 2. Check if the worker has any other active subscription
    $hasActive = DB::fetchOne(
        "SELECT id FROM subscriptions WHERE worker_id = ? AND status = 'active' AND expires_at >= ?",
        [$sub['worker_id'], $today]
    );

    if (!$hasActive) {
        // Hide worker from customer search
        DB::query("UPDATE workers SET is_visible = 0 WHERE id = ?", [$sub['worker_id']]);

        // Send expiry notification
        DB::query(
            "INSERT INTO notifications (user_id, type, title, body)
             VALUES (?, 'subscription', 'Subscription Expired', 'Your monthly subscription has ended. Please renew for ৳399 to keep your profile visible to customers.')",
            [$sub['user_id']]
        );
    }
    $count++;
}

echo "[" . date('Y-m-d H:i:s') . "] Completed. Expired $count subscriptions.\n";
