<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';

define('RESET_CODE_LENGTH', 4);
define('RESET_CODE_TTL', 10 * 60);        // seconds
define('RESET_MAX_ATTEMPTS', 5);

$step  = 1;
$error = '';
$info  = '';

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        // ── STEP 1: Request a reset code ──
        if ($action === 'request') {
            $email = trim($_POST['email'] ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
                $step = 1;
            } else {
                $stmt = $db->prepare("SELECT id, first_name, email FROM users WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                $_SESSION['reset_email']   = $email;
                $_SESSION['reset_attempts'] = 0;

                if ($user) {
                    $code = str_pad((string) random_int(0, 9999), RESET_CODE_LENGTH, '0', STR_PAD_LEFT);
                    $sent = sendPasswordResetCode($user, $code);

                    if ($sent) {
                        $stmt = $db->prepare("
                            UPDATE users
                            SET reset_token = :token, reset_token_expires = :expires, updated_at = NOW()
                            WHERE id = :id
                        ");
                        $stmt->execute([
                            ':token'   => password_hash($code, PASSWORD_DEFAULT),
                            ':expires' => date('Y-m-d H:i:s', time() + RESET_CODE_TTL),
                            ':id'      => $user['id'],
                        ]);
                        $_SESSION['reset_sent_at'] = time();
                        $info = 'We sent a ' . RESET_CODE_LENGTH . '-digit reset code to <strong>' . sanitize($email) . '</strong>. Check your inbox (and spam folder).';
                    } else {
                        $error = 'We could not send the email. Please try again later.';
                    }
                } else {
                    $info = 'If an account exists for <strong>' . sanitize($email) . '</strong>, a reset code has been sent to it. Check your inbox (and spam folder).';
                }

                if ($error === '') {
                    $step = 2;
                }
            }
        }

        // ── STEP 2: Verify the code ──
        elseif ($action === 'verify') {
            $email = trim($_SESSION['reset_email'] ?? '');
            $code  = implode('', array_map('trim', $_POST['otp'] ?? []));

            if ($email === '' || !preg_match('/^\d{' . RESET_CODE_LENGTH . '}$/', $code)) {
                $error = 'Please enter the ' . RESET_CODE_LENGTH . '-digit code from your email.';
                $step = 2;
            } else {
                $stmt = $db->prepare("
                    SELECT id, first_name, reset_token, reset_token_expires
                    FROM users
                    WHERE email = :email
                    LIMIT 1
                ");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                $attempts = (int) ($_SESSION['reset_attempts'] ?? 0);
                $expired  = !$user
                    || empty($user['reset_token'])
                    || empty($user['reset_token_expires'])
                    || strtotime($user['reset_token_expires']) < time();

                if ($expired) {
                    $error = 'That code is invalid or has expired. Please request a new one.';
                    $step = 2;
                } elseif ($attempts >= RESET_MAX_ATTEMPTS) {
                    $stmt = $db->prepare("UPDATE users SET reset_token = NULL, reset_token_expires = NULL WHERE id = :id");
                    $stmt->execute([':id' => $user['id']]);
                    $_SESSION['reset_attempts'] = 0;
                    $error = 'Too many incorrect attempts. Please request a new code.';
                    $step = 2;
                } elseif (password_verify($code, $user['reset_token'])) {
                    $_SESSION['reset_verified_email'] = $email;
                    unset($_SESSION['reset_attempts']);
                    $step = 3;
                } else {
                    $_SESSION['reset_attempts'] = $attempts + 1;
                    $attemptsLeft = RESET_MAX_ATTEMPTS - $attempts - 1;
                    $error = 'That code is incorrect. ' . ($attemptsLeft > 0 ? $attemptsLeft . ' attempt' . ($attemptsLeft === 1 ? '' : 's') . ' left.' : '');
                    $step = 2;
                }
            }
        }

        // ── RESEND: New code ──
        elseif ($action === 'resend') {
            $email = trim($_POST['email'] ?? $_SESSION['reset_email'] ?? '');

            $stmt = $db->prepare("SELECT id, first_name, email FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'Unable to resend. Please start again.';
                $step = 1;
            } else {
                $code = str_pad((string) random_int(0, 9999), RESET_CODE_LENGTH, '0', STR_PAD_LEFT);
                $sent = sendPasswordResetCode($user, $code);

                if ($sent) {
                    $stmt = $db->prepare("
                        UPDATE users
                        SET reset_token = :token, reset_token_expires = :expires, updated_at = NOW()
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':token'   => password_hash($code, PASSWORD_DEFAULT),
                        ':expires' => date('Y-m-d H:i:s', time() + RESET_CODE_TTL),
                        ':id'      => $user['id'],
                    ]);
                    $_SESSION['reset_email']    = $email;
                    $_SESSION['reset_attempts'] = 0;
                    $_SESSION['reset_sent_at']  = time();
                    $info = 'A new code has been sent to <strong>' . sanitize($email) . '</strong>.';
                } else {
                    $error = 'We could not send the email. Please try again later.';
                }
                $step = 2;
            }
        }

        // ── STEP 3: Set the new password ──
        elseif ($action === 'reset') {
            $email   = trim($_SESSION['reset_verified_email'] ?? '');
            $newPw   = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($email === '') {
                $error = 'Your reset session has expired. Please start over.';
                $step = 1;
            } else {
                $stmt = $db->prepare("
                    SELECT id, reset_token, reset_token_expires
                    FROM users
                    WHERE email = :email
                    LIMIT 1
                ");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                $stillValid = $user
                    && !empty($user['reset_token'])
                    && !empty($user['reset_token_expires'])
                    && strtotime($user['reset_token_expires']) >= time();

                if (!$stillValid) {
                    unset($_SESSION['reset_verified_email']);
                    $error = 'Your reset session has expired. Please start over.';
                    $step = 1;
                } elseif (strlen($newPw) < PASSWORD_MIN_LENGTH) {
                    $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
                    $step = 3;
                } elseif ($newPw !== $confirm) {
                    $error = 'Passwords do not match.';
                    $step = 3;
                } else {
                    $stmt = $db->prepare("
                        UPDATE users
                        SET password = :pwd, reset_token = NULL, reset_token_expires = NULL, updated_at = NOW()
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':pwd' => password_hash($newPw, PASSWORD_HASH_ALGO),
                        ':id'  => $user['id'],
                    ]);

                    unset(
                        $_SESSION['reset_email'],
                        $_SESSION['reset_attempts'],
                        $_SESSION['reset_sent_at'],
                        $_SESSION['reset_verified_email']
                    );
                    $step = 4;
                }
            }
        }
    }

    $resetEmail = trim($_SESSION['reset_email'] ?? '');
} catch (PDOException $e) {
    error_log("Password reset error: " . $e->getMessage());
    $error = 'Something went wrong. Please try again.';
    $step  = 1;
}

// Progress dots: which are done / active
$dotMap     = [1 => 0, 2 => 1, 3 => 2, 4 => 2];
$activeDot  = $dotMap[$step] ?? 0;
$dotClass   = function ($i) use ($activeDot) {
    if ($i < $activeDot) return 'done';
    if ($i === $activeDot) return 'active';
    return '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password</title>

  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/password.css">
</head>
<body>

<div class="container">

  <!-- Progress dots -->
  <div class="progress-dots">
    <div class="dot <?= $dotClass(0) ?>"></div>
    <div class="dot <?= $dotClass(1) ?>"></div>
    <div class="dot <?= $dotClass(2) ?>"></div>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= sanitize($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($info)): ?>
    <div class="alert alert-info"><?= sanitize($info) ?></div>
  <?php endif; ?>

  <!-- ── STEP 1: Enter email ── -->
  <div class="step<?= $step === 1 ? ' active' : '' ?>" id="step-1">
    <div class="icon-circle orange">
      <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
    </div>

    <h1>Forgot Password?</h1>
    <p class="subtitle">No worries! Enter your email and we'll send you a reset code.</p>

    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>
      <input type="hidden" name="action" value="request" />
      <div class="field-group">
        <label for="email">Email Address</label>
        <div class="input-wrapper">
          <input type="email" id="email" name="email" placeholder="john@example.com"
                 value="<?= sanitize($resetEmail) ?>" required />
          <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
        </div>
      </div>

      <button type="submit" class="btn-main">Send Reset Code</button>
    </form>
    <br/>
    <a class="back-link" href="login.php">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Sign In
    </a>
  </div>

  <!-- ── STEP 2: Enter OTP ── -->
  <div class="step<?= $step === 2 ? ' active' : '' ?>" id="step-2">
    <div class="icon-circle orange">
      <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>

    <h1>Check Your Email</h1>
    <p class="subtitle">We sent a <?= RESET_CODE_LENGTH ?>-digit code to <span id="email-display"><?= sanitize($resetEmail) ?></span>. Enter it below.</p>

    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>
      <input type="hidden" name="action" value="verify" />
      <div class="otp-row">
        <input type="text" maxlength="1" class="otp-input" inputmode="numeric" name="otp[]" autocomplete="one-time-code" />
        <input type="text" maxlength="1" class="otp-input" inputmode="numeric" name="otp[]" autocomplete="one-time-code" />
        <input type="text" maxlength="1" class="otp-input" inputmode="numeric" name="otp[]" autocomplete="one-time-code" />
        <input type="text" maxlength="1" class="otp-input" inputmode="numeric" name="otp[]" autocomplete="one-time-code" />
      </div>

      <button type="submit" class="btn-main">Verify Code</button>
    </form>

    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="resend-row">
      <input type="hidden" name="action" value="resend" />
      <input type="hidden" name="email" value="<?= sanitize($resetEmail) ?>" />
      Didn't receive it?
      <button type="submit" id="resend-btn" disabled>Resend in <span id="timer">60s</span></button>
    </form>

    <button class="back-link" onclick="history.back()">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      Go back
    </button>
  </div>

  <!-- ── STEP 3: New password ── -->
  <div class="step<?= $step === 3 ? ' active' : '' ?>" id="step-3">
    <div class="icon-circle orange">
      <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>

    <h1>New Password</h1>
    <p class="subtitle">Create a strong password you haven't used before.</p>

    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>
      <input type="hidden" name="action" value="reset" />
      <div class="field-group">
        <label for="new-password">New Password</label>
        <div class="input-wrapper">
          <input type="password" id="new-password" name="new_password" placeholder="Enter new password" oninput="checkStrength(this.value)" />
          <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div class="strength-bar">
          <span id="s1"></span><span id="s2"></span><span id="s3"></span><span id="s4"></span>
        </div>
        <div class="strength-label" id="strength-label"></div>
      </div>

      <div class="field-group">
        <label for="confirm-password">Confirm Password</label>
        <div class="input-wrapper">
          <input type="password" id="confirm-password" name="confirm_password" placeholder="Repeat new password" />
          <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
      </div>

      <button type="submit" class="btn-main">Reset Password</button>
    </form>
    <br/>
    <button class="back-link" onclick="location.href='login.php'">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Sign In
    </button>
  </div>

  <!-- ── STEP 4: Success ── -->
  <div class="step<?= $step === 4 ? ' active' : '' ?>" id="step-4">
    <div class="icon-circle green">
      <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    </div>

    <h1>Password Reset!</h1>
    <p class="subtitle">Your password has been changed successfully. You can now sign in with your new password.</p>

    <a class="btn-main" style="display:block; text-align:center;" href="login.php">Go to Sign In</a>
  </div>

</div>

<script>
  // OTP auto-advance + backspace
  document.querySelectorAll('.otp-input').forEach((input, i, all) => {
    input.addEventListener('input', () => {
      input.value = input.value.replace(/\D/g, '');
      if (input.value && i < all.length - 1) all[i + 1].focus();
    });
    input.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !input.value && i > 0) all[i - 1].focus();
    });
  });

  // Resend countdown timer (only on step 2)
  if (document.getElementById('timer')) {
    let remaining = 60;
    const btn      = document.getElementById('resend-btn');
    const timerEl  = document.getElementById('timer');

    const interval = setInterval(() => {
      remaining--;
      if (remaining <= 0) {
        clearInterval(interval);
        btn.disabled = false;
        timerEl.textContent = '';
        btn.innerHTML = 'Resend code';
      } else {
        timerEl.textContent = remaining + 's';
      }
    }, 1000);
  }

  // Password strength checker (step 3)
  function checkStrength(val) {
    const bars  = [document.getElementById('s1'), document.getElementById('s2'),
                   document.getElementById('s3'), document.getElementById('s4')];
    const label = document.getElementById('strength-label');

    if (val.length === 0) {
      bars.forEach(b => b.style.background = '');
      label.textContent = '';
      return;
    }

    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    if (score === 0) score = 1;

    const colors = ['#e74c3c', '#e67e22', '#f1c40f', '#27ae60'];
    const labels = ['Weak', 'Fair', 'Good', 'Strong'];

    bars.forEach((b, i) => b.style.background = i < score ? colors[score - 1] : '');
    label.textContent = labels[score - 1];
    label.style.color = colors[score - 1];
  }
</script>

</body>
</html>