<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/login.php');
}

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Please provide a valid email address.');
    redirect(SITE_URL . '/login.php');
}

try {
    $db = getDB();

    $stmt = $db->prepare("
        SELECT id, first_name, email, email_verified, verification_token
        FROM users
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        setFlash('error', 'No account found with that email address.');
        redirect(SITE_URL . '/login.php');
    }

    if ($user['email_verified']) {
        setFlash('info', 'Your email is already verified. You can sign in.');
        redirect(SITE_URL . '/login.php');
    }

    $verificationToken = $user['verification_token'];

    // Always refresh token/expiry on resend for security and to show updated expiring time
    $verificationToken = generateVerificationToken();
    $stmt = $db->prepare("
        UPDATE users
        SET verification_token = :token, verification_token_expires = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 12 HOUR), updated_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':token' => $verificationToken,
        ':id'    => $user['id'],
    ]);

    $mailSent = sendVerificationEmail([
        'id'                 => $user['id'],
        'first_name'         => $user['first_name'],
        'email'              => $user['email'],
        'verification_token' => $verificationToken,
    ]);

    if ($mailSent) {
        setFlash('success', 'Verification email sent!');
        redirect(SITE_URL . '/verify.php?sent=1&email=' . urlencode($user['email']));
    } else {
        setFlash('error', 'Failed to send verification email. Please try again later.');
        redirect(SITE_URL . '/verify.php?sent=1&email=' . urlencode($user['email']));
    }

} catch (PDOException $e) {
    error_log("Resend verification error: " . $e->getMessage());
    setFlash('error', 'Something went wrong. Please try again.');
    redirect(SITE_URL . '/login.php');
}