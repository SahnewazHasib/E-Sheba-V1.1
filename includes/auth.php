<?php
// ============================================================
// Auth Helper — session, role guard, OTP generation
// ============================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class Auth {

    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
            session_set_cookie_params(SESSION_LIFETIME, '/', '', false, true);
            session_start();
        }
    }

    // ── Login ────────────────────────────────────────────────
    public static function login(array $user): void {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['user_role']   = $user['role'];        // customer | worker | admin
        $_SESSION['user_name']   = $user['name'];
        $_SESSION['user_email']  = $user['email'];
        $_SESSION['user_avatar'] = $user['avatar'] ?? null;
    }

    public static function avatar(): ?string {
        self::start();
        if (self::isLoggedIn()) {
            if (!isset($_SESSION['user_avatar'])) {
                $row = DB::fetchOne("SELECT avatar FROM users WHERE id = ?", [self::id()]);
                $_SESSION['user_avatar'] = $row['avatar'] ?? null;
            }
            return $_SESSION['user_avatar'] ?? null;
        }
        return null;
    }

    public static function logout(): void {
        self::start();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
    }

    public static function isLoggedIn(): bool {
        self::start();
        return isset($_SESSION['user_id']);
    }

    public static function role(): ?string {
        self::start();
        return $_SESSION['user_role'] ?? null;
    }

    public static function id(): ?int {
        self::start();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    // ── Guards ──────────────────────────────────────────────
    public static function requireLogin(string $redirect = 'login.php'): void {
        if (!self::isLoggedIn()) {
            header("Location: " . APP_URL . "/$redirect");
            exit;
        }
    }

    public static function requireRole(string $role, string $redirect = 'login.php'): void {
        self::requireLogin($redirect);
        if (self::role() !== $role) {
            header("Location: " . APP_URL . "/403.php");
            exit;
        }
    }

    // ── OTP ─────────────────────────────────────────────────
    public static function generateOTP(): string {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function storeOTP(int $userId, string $otp, string $type = 'email_verify'): void {
        self::start();
        $_SESSION['last_demo_otp'] = $otp;
        $expires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
        DB::query(
            "INSERT INTO otp_tokens (user_id, token, type, expires_at)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE token=VALUES(token), expires_at=VALUES(expires_at)",
            [$userId, password_hash($otp, PASSWORD_DEFAULT), $type, $expires]
        );
    }

    public static function verifyOTP(int $userId, string $otp, string $type = 'email_verify'): bool {
        self::start();
        // Master Demo OTP and session match for seamless local testing
        if ($otp === '123456' || (isset($_SESSION['last_demo_otp']) && $otp === $_SESSION['last_demo_otp'])) {
            DB::query("DELETE FROM otp_tokens WHERE user_id=? AND type=?", [$userId, $type]);
            return true;
        }

        $row = DB::fetchOne(
            "SELECT token FROM otp_tokens
             WHERE user_id=? AND type=? AND expires_at > NOW()",
            [$userId, $type]
        );
        if (!$row) return false;
        if (!password_verify($otp, $row['token'])) return false;
        // consume it
        DB::query("DELETE FROM otp_tokens WHERE user_id=? AND type=?", [$userId, $type]);
        return true;
    }

    // ── CSRF ────────────────────────────────────────────────
    public static function csrfToken(): string {
        self::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(): void {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die(json_encode(['error' => 'CSRF token mismatch']));
        }
    }
}
