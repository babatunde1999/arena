<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

$username = '';
$loginError = '';

$flash = getFlash();
if ($flash) {
    $flashType  = $flash['type'];
    $flashMsg   = $flash['message'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $loginError = 'Please enter your username and password.';
    } else {
        try {
            $db = getDB();

            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $stmt = $db->prepare("SELECT * FROM users WHERE email = :username LIMIT 1");
            } else {
                $stmt = $db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            }
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $loginError = 'Invalid username or password.';
            } elseif (!$user['email_verified']) {
                $loginError = 'Please verify your email address first. Check your inbox for the verification link.';
            } else {
                session_regenerate_id(true);

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['role']      = $user['role'];

                if ($remember) {
                    setcookie(
                        'remember_me',
                        $user['id'] . ':' . hash('sha256', $user['password']),
                        time() + (30 * 24 * 3600),
                        '/',
                        '',
                        false,
                        true
                    );
                }

                setFlash('success', 'Welcome back, ' . $user['first_name'] . '!');
                redirect(SITE_URL . '/index.php');
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $loginError = 'Something went wrong. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign In</title>
  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>

  <div class="signin-container">

    <!-- Brand mark -->
    <div class="brand-mark">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
      </svg>
    </div>

    <h1>Welcome back</h1>
    <p class="subtitle">Sign in to your account to continue.</p>

    <?php if (!empty($loginError)): ?>
      <div class="alert alert-error"><?= sanitize($loginError) ?></div>
    <?php endif; ?>

    <?php if (!empty($flashMsg)): ?>
      <div class="alert alert-<?= sanitize($flashType) ?>"><?= sanitize($flashMsg) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>
      <!-- Username -->
      <div class="field-group">
        <label for="username">Username or Email</label>
        <div class="input-wrapper">
          <input type="text" id="username" name="username" placeholder="@johndoe"
                 value="<?= sanitize($username) ?>" />
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
      </div>

      <!-- Password -->
      <div class="field-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
          <input type="password" id="password" name="password" placeholder="Enter your password" />
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <button class="toggle-pw" type="button" onclick="togglePassword()" aria-label="Toggle password visibility">
            <svg id="eye-icon" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Remember me + Forgot -->
      <div class="form-footer-row">
        <div class="remember-me">
          <input type="checkbox" id="remember" name="remember" />
          <label for="remember">Remember me</label>
        </div>
        <a href="password.php" class="forgot-link">Forgot password?</a>
      </div>

      <button type="submit" class="btn-signin">Sign In</button>
    </form>

    <div class="or-divider">or</div>

    <p class="register-link">Don't have an account? <a href="register.php">Create one</a></p>

  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById('password');
      const icon = document.getElementById('eye-icon');
      const isHidden = input.type === 'password';

      input.type = isHidden ? 'text' : 'password';

      icon.innerHTML = isHidden
        ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
           <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
           <line x1="1" y1="1" x2="23" y2="23"/>`
        : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
           <circle cx="12" cy="12" r="3"/>`;
    }
</script>

  </body>
</html>