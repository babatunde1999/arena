<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$token = $_GET['token'] ?? '';
$sent  = $_GET['sent'] ?? '0';
$email = $_GET['email'] ?? '';
$status = '';
$message = '';
$resend = false;

if ($sent === '1') {
    $status  = 'info';
    $message = 'We have sent a verification link to <strong>' . sanitize($email) . '</strong>. Please check your inbox (and spam folder).';
    $resend  = true;
} elseif (empty($token)) {
    $status  = 'error';
    $message = 'Invalid verification link.';
} else {
    try {
        $db = getDB();

        $stmt = $db->prepare("SELECT id, email_verified FROM users WHERE verification_token = :token LIMIT 1");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            $status  = 'error';
            $message = 'Invalid or expired verification link.';
        } elseif ($user['email_verified']) {
            $status  = 'info';
            $message = 'Your email is already verified. You can sign in.';
        } else {
            $stmt = $db->prepare("
                UPDATE users
                SET email_verified = 1, verification_token = NULL, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([':id' => $user['id']]);

            $status  = 'success';
            $message = 'Email verified successfully! You can now sign in.';
        }
    } catch (PDOException $e) {
        error_log("Verification error: " . $e->getMessage());
        $status  = 'error';
        $message = 'Something went wrong. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Email Verification</title>
  <link rel="stylesheet" href="css/default.css">
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #f9efe6 0%, #f5f5f5 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      padding: 32px 16px;
    }
    .verify-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(62, 44, 35, 0.12);
      padding: 48px 40px;
      width: 100%;
      max-width: 440px;
      text-align: center;
    }
    .icon-circle {
      width: 72px; height: 72px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 20px;
    }
    .icon-circle svg { width: 36px; height: 36px; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .icon-circle.success { background: #eafaf1; }
    .icon-circle.success svg { stroke: #27ae60; }
    .icon-circle.error { background: #fef5f5; }
    .icon-circle.error svg { stroke: #e74c3c; }
    .icon-circle.info { background: #eaf4fe; }
    .icon-circle.info svg { stroke: #3498db; }
    .verify-card h1 { font-size: 1.4rem; color: #3E2C23; margin: 0 0 8px; }
    .verify-card p { font-size: 0.9rem; color: #777; line-height: 1.6; margin: 0 0 28px; }
    .btn-main {
      display: inline-block; padding: 13px 36px;
      background: linear-gradient(135deg, #d47719, #b8620f);
      color: #fff; font-size: 0.95rem; font-weight: 700;
      border: none; border-radius: 12px; cursor: pointer;
      text-decoration: none; transition: transform 0.15s, box-shadow 0.2s;
      box-shadow: 0 4px 14px rgba(212,119,25,0.35);
    }
    .btn-main:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(212,119,25,0.45); }
    .btn-secondary {
      display: inline-block; padding: 11px 28px;
      background: transparent; color: #b8620f;
      border: 1.5px solid #d47719; border-radius: 12px;
      font-size: 0.85rem; font-weight: 600; cursor: pointer;
      text-decoration: none; margin-top: 10px; transition: all 0.2s;
    }
    .btn-secondary:hover { background: #fff7ef; }
  </style>
</head>
<body>

  <div class="verify-card">
    <?php if ($status === 'success'): ?>
      <div class="icon-circle success">
        <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <h1>Email Verified!</h1>
      <p><?= sanitize($message) ?></p>
      <a href="<?= SITE_URL ?>/login.php" class="btn-main">Go to Sign In</a>
    <?php elseif ($status === 'info'): ?>
      <div class="icon-circle info">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </div>
      <h1><?= $sent === '1' ? 'Check Your Email' : 'Check Your Email' ?></h1>
      <p><?= $message ?></p>
      <?php if ($resend): ?>
        <form method="POST" action="<?= SITE_URL ?>/resend-verification.php">
          <input type="hidden" name="email" value="<?= sanitize($email) ?>" />
          <button type="submit" class="btn-secondary">Resend Verification Email</button>
        </form>
      <?php endif; ?>
      <a href="<?= SITE_URL ?>/login.php" class="btn-secondary" style="display:block;">Go to Sign In</a>
    <?php else: ?>
      <div class="icon-circle error">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
      </div>
      <h1>Verification Failed</h1>
      <p><?= sanitize($message) ?></p>
      <a href="<?= SITE_URL ?>/login.php" class="btn-main">Go to Sign In</a>
    <?php endif; ?>
  </div>

</body>
</html>
