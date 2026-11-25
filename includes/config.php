<?php
// ============================================================
// E-Sheba Configuration File
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'esheba_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// App settings
define('APP_NAME', 'E-Sheba');
define('APP_URL',  'http://localhost/e-sheba');
define('APP_VERSION', '1.0.0');

// Gmail / SMTP Settings
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USERNAME', 'khasib2288@gmail.com');
define('MAIL_PASSWORD', 'loki oijh hytf dfdy'); // Generate at https://myaccount.google.com/apppasswords
define('MAIL_FROM',     'khasib2288@gmail.com');
define('MAIL_FROM_NAME','E-Sheba');

// Pusher (real-time messaging)
define('PUSHER_APP_ID',  'your_pusher_app_id');
define('PUSHER_KEY',     'your_pusher_key');
define('PUSHER_SECRET',  'your_pusher_secret');
define('PUSHER_CLUSTER', 'ap2');

// Agora (voice calling)
define('AGORA_APP_ID',   'your_agora_app_id');
define('AGORA_APP_CERT', 'your_agora_certificate');

// SSLCommerz (payment)
define('SSLCOMMERZ_STORE_ID',  'your_store_id');
define('SSLCOMMERZ_STORE_PASS','your_store_pass');
define('SSLCOMMERZ_SANDBOX',   true);

// Subscription price (BDT)
define('SUBSCRIPTION_PRICE', 399);

// Session settings
define('SESSION_LIFETIME', 3600 * 24); // 24 hours

// Encryption key (change this!)
define('ENCRYPT_KEY', 'esheba_secret_key_32chars_here!!');

// OTP settings
define('OTP_EXPIRY_MINUTES', 10);
