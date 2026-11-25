<?php
// ============================================================
// Mailer — sends OTP / verification emails via PHPMailer
// ============================================================
// Requires: composer require phpmailer/phpmailer
// Or: download PHPMailer and place in vendor/phpmailer/
require_once __DIR__ . '/config.php';

// Autoload PHPMailer if present
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/phpmailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
}

class Mailer {

    private static function instance() {
        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            return null;
        }
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        return $mail;
    }

    // ── Send OTP ────────────────────────────────────────────
    public static function sendOTP(string $toEmail, string $toName, string $otp, string $purpose = 'verify'): bool {
        // Save to session for local academic testing & demo convenience
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['last_demo_otp'] = $otp;
            $_SESSION['last_demo_email'] = $toEmail;
        }

        $subjects = [
            'verify'        => 'Verify your E-Sheba account',
            'password_reset'=> 'Reset your E-Sheba password',
        ];
        $subject = $subjects[$purpose] ?? 'E-Sheba OTP';
        $body = self::otpTemplate($toName, $otp, $purpose);

        // 1. Try sending via PHPMailer if library is loaded
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer') && MAIL_PASSWORD !== 'PASTE_YOUR_16_CHAR_APP_PASSWORD_HERE') {
            try {
                $mail = self::instance();
                if ($mail) {
                    $mail->addAddress($toEmail, $toName);
                    $mail->Subject = $subject;
                    $mail->Body    = $body;
                    $mail->AltBody = "Your E-Sheba OTP is: $otp. It expires in " . OTP_EXPIRY_MINUTES . " minutes.";
                    return $mail->send();
                }
            } catch (\Throwable $e) {
                error_log("PHPMailer error: " . $e->getMessage());
            }
        }

        // 2. Direct Socket SMTP to smtp.gmail.com (Zero-dependency, works directly on vanilla XAMPP)
        if (MAIL_PASSWORD !== 'PASTE_YOUR_16_CHAR_APP_PASSWORD_HERE' && !empty(MAIL_PASSWORD)) {
            $sent = self::sendDirectSMTP($toEmail, $subject, $body);
            if ($sent) return true;
        }

        // 3. Fallback to PHP native mail or local logger
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: " . MAIL_FROM . "\r\n";
        @mail($toEmail, $subject, $body, $headers);
        return true;
    }

    // ── Direct Socket SMTP for Gmail (Port 465 SSL) ───────────
    public static function sendDirectSMTP(string $toEmail, string $subject, string $htmlBody): bool {
        $host = 'ssl://smtp.gmail.com';
        $port = 465;
        $username = trim(MAIL_USERNAME);
        $password = trim(str_replace(' ', '', MAIL_PASSWORD)); // remove spaces from App Password
        $from = MAIL_FROM;
        $fromName = MAIL_FROM_NAME;

        $timeout = 12;
        $socket = @stream_socket_client("$host:$port", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        if (!$socket) {
            error_log("Direct SMTP Connect Failed: $errstr ($errno)");
            return false;
        }

        stream_set_timeout($socket, $timeout);

        $read = function() use ($socket) {
            $data = "";
            while ($str = fgets($socket, 515)) {
                $data .= $str;
                if (substr($str, 3, 1) === " ") break;
            }
            return $data;
        };

        $write = function($cmd) use ($socket) {
            fputs($socket, $cmd . "\r\n");
        };

        $res = $read(); // 220 banner
        if (substr($res, 0, 3) !== '220') { fclose($socket); return false; }

        $write("EHLO " . gethostname());
        $res = $read();

        $write("AUTH LOGIN");
        $res = $read();

        $write(base64_encode($username));
        $res = $read();

        $write(base64_encode($password));
        $res = $read();
        if (substr($res, 0, 3) !== '235') {
            error_log("Gmail SMTP Auth Failed: $res (Make sure you use a 16-character App Password)");
            fclose($socket);
            return false;
        }

        $write("MAIL FROM: <$from>");
        $res = $read();

        $write("RCPT TO: <$toEmail>");
        $res = $read();

        $write("DATA");
        $res = $read();

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: $fromName <$from>\r\n";
        $headers .= "To: <$toEmail>\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "Date: " . date("r") . "\r\n";

        $write($headers . "\r\n" . $htmlBody . "\r\n.");
        $res = $read();

        $write("QUIT");
        fclose($socket);

        return (substr($res, 0, 3) === '250');
    }

    // ── HTML Email Template ─────────────────────────────────
    private static function otpTemplate(string $name, string $otp, string $purpose): string {
        $purposeText = $purpose === 'password_reset'
            ? 'reset your password'
            : 'verify your email address';
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body{font-family:'Segoe UI',sans-serif;background:#f4f6fb;margin:0;padding:0}
    .wrap{max-width:520px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
    .header{background:linear-gradient(135deg,#1a73e8,#0d47a1);padding:36px 32px;text-align:center}
    .header h1{color:#fff;margin:0;font-size:28px;letter-spacing:1px}
    .header p{color:rgba(255,255,255,.8);margin:6px 0 0;font-size:14px}
    .body{padding:36px 32px}
    .body p{color:#444;line-height:1.7;font-size:15px}
    .otp-box{background:#f0f4ff;border:2px dashed #1a73e8;border-radius:12px;padding:20px;text-align:center;margin:24px 0}
    .otp-code{font-size:42px;font-weight:800;color:#1a73e8;letter-spacing:12px;font-family:monospace}
    .timer{color:#888;font-size:13px;margin-top:8px}
    .footer{background:#f9f9f9;padding:20px 32px;text-align:center;border-top:1px solid #eee}
    .footer p{color:#aaa;font-size:12px;margin:0}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <h1>🔧 E-Sheba</h1>
      <p>Your Trusted Service Platform</p>
    </div>
    <div class="body">
      <p>Hi <strong>$name</strong>,</p>
      <p>You requested to $purposeText on <strong>E-Sheba</strong>. Use the OTP below:</p>
      <div class="otp-box">
        <div class="otp-code">$otp</div>
        <div class="timer">⏱ Expires in {OTP_EXPIRY_MINUTES} minutes</div>
      </div>
      <p>If you did not request this, please ignore this email or contact our support team.</p>
      <p>— The E-Sheba Team</p>
    </div>
    <div class="footer">
      <p>© 2026 E-Sheba · World University of Bangladesh</p>
    </div>
  </div>
</body>
</html>
HTML;
    }
}
