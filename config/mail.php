<?php
/**
 * Mail Service
 * Arena Market - E-Commerce Platform
 *
 * Uses PHPMailer for reliable SMTP email delivery.
 * Dependencies installed via Composer (see composer.json).
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── SMTP Settings (edit these) ──
define('MAIL_HOST', 'sandbox.smtp.mailtrap.io');          // e.g. smtp.gmail.com
define('MAIL_PORT', 2525);                       // 587 = TLS, 465 = SSL
define('MAIL_SECURE', PHPMailer::ENCRYPTION_STARTTLS); // ENCRYPTION_SMTPS for 465
define('MAIL_USERNAME', 'ba5f5e6d8e6143');                    // your@gmail.com
define('MAIL_PASSWORD', 'cb126f918aefc7');                    // Gmail App Password
define('MAIL_FROM_EMAIL', SITE_EMAIL);                  // send-as address (or empty to use username)
define('MAIL_FROM_NAME', SITE_NAME);
define('MAIL_SUBJECT_PREFIX', '[' . SITE_NAME . '] ');

/**
 * Send an email using PHPMailer + SMTP
 * @param string $to         Recipient email
 * @param string $subject    Subject line
 * @param string $html       HTML body
 * @param string $plainText  Plain-text fallback
 * @return bool Always true; throws on failure
 * @throws Exception
 */
function sendMail($to, $subject, $html, $plainText = '') {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_SECURE;
        $mail->Port       = MAIL_PORT;

        $fromEmail = MAIL_FROM_EMAIL !== '' ? MAIL_FROM_EMAIL : MAIL_USERNAME;

        $mail->setFrom($fromEmail, MAIL_FROM_NAME);
        $mail->addReplyTo(SITE_EMAIL, SITE_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = MAIL_SUBJECT_PREFIX . $subject;
        $mail->Body    = $html;
        $mail->AltBody = $plainText !== '' ? $plainText : strip_tags($html);
        $mail->CharSet = 'UTF-8';
        $mail->XMailer = 'ArenaMarket/2.0';

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mail error to {$to}: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Generate a verification token
 * @return string
 */
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Build the email verification URL
 * @param string $token
 * @return string
 */
function getVerificationURL($token) {
    return SITE_URL . "/verify.php?token=" . urlencode($token);
}

/**
 * Send verification email to a newly registered user
 * @param array $user  ['id', 'first_name', 'email', 'verification_token']
 * @return bool
 */
function sendVerificationEmail($user) {
    $name  = sanitize($user['first_name']);
    $email = $user['email'];
    $token = $user['verification_token'];
    $url   = getVerificationURL($token);

    $subject = 'Verify Your Email Address';

    $plainText  = "Hi {$name},\r\n\r\n";
    $plainText .= "Welcome to " . SITE_NAME . "!\r\n\r\n";
    $plainText .= "Please verify your email address by clicking the link below:\r\n\r\n";
    $plainText .= "{$url}\r\n\r\n";
    $plainText .= "This link will expire in 24 hours.\r\n\r\n";
    $plainText .= "If you did not create an account, please ignore this email.\r\n\r\n";
    $plainText .= "Regards,\r\n";
    $plainText .= SITE_NAME . " Team\r\n";
    $plainText .= SITE_ADDRESS;

    $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>';
    $html .= '<body style="margin:0;padding:0;background:#f7f7f7;font-family:Segoe UI,system-ui,sans-serif;">';
    $html .= '<div style="max-width:520px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">';
    $html .= '<div style="background:linear-gradient(135deg,#3E2C23,#5c3d2e);padding:32px 24px;text-align:center;">';
    $html .= '<h1 style="color:#d47719;font-size:1.5rem;margin:0;">' . SITE_NAME . '</h1></div>';
    $html .= '<div style="padding:32px 24px;">';
    $html .= '<h2 style="color:#3E2C23;font-size:1.2rem;margin:0 0 12px;">Verify Your Email</h2>';
    $html .= '<p style="color:#555;font-size:0.9rem;line-height:1.6;margin:0 0 24px;">Hi ' . $name . ',<br><br>';
    $html .= 'Welcome to ' . SITE_NAME . '! Please confirm your email address by clicking the button below.</p>';
    $html .= '<a href="' . $url . '" style="display:inline-block;padding:14px 36px;background:#d47719;color:#fff;';
    $html .= 'font-weight:700;font-size:0.95rem;border-radius:10px;text-decoration:none;';
    $html .= 'box-shadow:0 4px 14px rgba(212,119,25,0.35);">Verify Email Address</a>';
    $html .= '<p style="color:#999;font-size:0.8rem;line-height:1.6;margin:28px 0 0;">This link expires in 24 hours.</p>';
    $html .= '<p style="color:#999;font-size:0.8rem;line-height:1.6;margin:8px 0 0;">If you did not create an account, please ignore this email.</p>';
    $html .= '</div>';
    $html .= '<div style="background:#f9f5f0;padding:20px 24px;text-align:center;">';
    $html .= '<p style="color:#999;font-size:0.75rem;margin:0;">&copy; ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.</p>';
    $html .= '<p style="color:#bbb;font-size:0.7rem;margin:4px 0 0;">' . SITE_ADDRESS . '</p>';
    $html .= '</div></div></body></html>';

    return sendMail($email, $subject, $html, $plainText);
}

/**
 * Send password reset email
 * @param array $user  ['id', 'first_name', 'email']
 * @param string $token  Reset token
 * @return bool
 */
function sendPasswordResetEmail($user, $token) {
    $name  = sanitize($user['first_name']);
    $email = $user['email'];
    $url   = SITE_URL . "/password.php?token=" . urlencode($token);

    $subject = 'Reset Your Password';

    $plainText  = "Hi {$name},\r\n\r\n";
    $plainText .= "We received a request to reset your password.\r\n\r\n";
    $plainText .= "Click the link below to set a new password:\r\n\r\n";
    $plainText .= "{$url}\r\n\r\n";
    $plainText .= "This link will expire in 1 hour.\r\n\r\n";
    $plainText .= "If you did not request this, please ignore this email.\r\n\r\n";
    $plainText .= "Regards,\r\n";
    $plainText .= SITE_NAME . " Team";

    $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>';
    $html .= '<body style="margin:0;padding:0;background:#f7f7f7;font-family:Segoe UI,system-ui,sans-serif;">';
    $html .= '<div style="max-width:520px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">';
    $html .= '<div style="background:linear-gradient(135deg,#3E2C23,#5c3d2e);padding:32px 24px;text-align:center;">';
    $html .= '<h1 style="color:#d47719;font-size:1.5rem;margin:0;">' . SITE_NAME . '</h1></div>';
    $html .= '<div style="padding:32px 24px;">';
    $html .= '<h2 style="color:#3E2C23;font-size:1.2rem;margin:0 0 12px;">Reset Your Password</h2>';
    $html .= '<p style="color:#555;font-size:0.9rem;line-height:1.6;margin:0 0 24px;">Hi ' . $name . ',<br><br>';
    $html .= 'Click the button below to set a new password.</p>';
    $html .= '<a href="' . $url . '" style="display:inline-block;padding:14px 36px;background:#d47719;color:#fff;';
    $html .= 'font-weight:700;font-size:0.95rem;border-radius:10px;text-decoration:none;';
    $html .= 'box-shadow:0 4px 14px rgba(212,119,25,0.35);">Reset Password</a>';
    $html .= '<p style="color:#999;font-size:0.8rem;line-height:1.6;margin:28px 0 0;">This link expires in 1 hour.</p>';
    $html .= '<p style="color:#999;font-size:0.8rem;line-height:1.6;margin:8px 0 0;">If you did not request this, please ignore this email.</p>';
    $html .= '</div>';
    $html .= '<div style="background:#f9f5f0;padding:20px 24px;text-align:center;">';
    $html .= '<p style="color:#999;font-size:0.75rem;margin:0;">&copy; ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.</p>';
    $html .= '</div></div></body></html>';

    return sendMail($email, $subject, $html, $plainText);
}

/**
 * Send a password reset code (OTP) email
 * @param array $user  ['id', 'first_name', 'email']
 * @param string $code 4-digit reset code
 * @return bool
 */
function sendPasswordResetCode($user, $code) {
    $name  = sanitize($user['first_name']);
    $email = $user['email'];

    $subject = 'Your Password Reset Code';

    $plainText  = "Hi {$name},\r\n\r\n";
    $plainText .= "We received a request to reset your password.\r\n\r\n";
    $plainText .= "Your password reset code is: {$code}\r\n\r\n";
    $plainText .= "Enter this code on the reset page. It expires in 10 minutes.\r\n\r\n";
    $plainText .= "If you did not request this, please ignore this email.\r\n\r\n";
    $plainText .= "Regards,\r\n";
    $plainText .= SITE_NAME . " Team";

    $html  = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>';
    $html .= '<body style="margin:0;padding:0;background:#f7f7f7;font-family:Segoe UI,system-ui,sans-serif;">';
    $html .= '<div style="max-width:520px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">';
    $html .= '<div style="background:linear-gradient(135deg,#3E2C23,#5c3d2e);padding:32px 24px;text-align:center;">';
    $html .= '<h1 style="color:#d47719;font-size:1.5rem;margin:0;">' . SITE_NAME . '</h1></div>';
    $html .= '<div style="padding:32px 24px;text-align:center;">';
    $html .= '<h2 style="color:#3E2C23;font-size:1.2rem;margin:0 0 12px;">Reset Your Password</h2>';
    $html .= '<p style="color:#555;font-size:0.9rem;line-height:1.6;margin:0 0 24px;">Hi ' . $name . ',<br><br>';
    $html .= 'Use the code below to set a new password.</p>';
    $html .= '<div style="display:inline-block;padding:18px 34px;background:#f9f5f0;border:1.5px solid #d47719;';
    $html .= 'border-radius:12px;color:#b8620f;font-size:2rem;font-weight:800;letter-spacing:10px;';
    $html .= 'font-family:Consolas,monospace;">' . $code . '</div>';
    $html .= '<p style="color:#999;font-size:0.8rem;line-height:1.6;margin:28px 0 0;">This code expires in 10 minutes.</p>';
    $html .= '<p style="color:#999;font-size:0.8rem;line-height:1.6;margin:8px 0 0;">If you did not request this, please ignore this email.</p>';
    $html .= '</div>';
    $html .= '<div style="background:#f9f5f0;padding:20px 24px;text-align:center;">';
    $html .= '<p style="color:#999;font-size:0.75rem;margin:0;">&copy; ' . date('Y') . ' ' . SITE_NAME . '. All rights reserved.</p>';
    $html .= '</div></div></body></html>';

    return sendMail($email, $subject, $html, $plainText);
}