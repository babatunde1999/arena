<?php
/**
 * Admin login - separate sign in for administrators only.
 * Arena Market - E-Commerce Platform
 */

require_once __DIR__ . '/includes/bootstrap.php';

if (isAdmin()) {
    redirect(ADMIN_URL . '/index.php');
}

$username   = '';
$flashType  = null;
$flashMsg   = null;
$loginError = null;
$csrfPassed = true;

$flash = getFlash();
if ($flash) {
    $flashType = $flash['type'];
    $flashMsg  = $flash['message'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token   = $_POST['csrf'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!verifyCSRF($token)) {
        $csrfPassed = false;
        $loginError = 'Security token expired. Please try again.';
    } elseif (empty($username) || empty($password)) {
        $loginError = 'Please enter your username and password.';
    } else {
        try {
            $db = getDB();

            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                $stmt = $db->prepare("SELECT * FROM admins WHERE email = :username LIMIT 1");
            } else {
                $stmt = $db->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
            }
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($password, $admin['password'])) {
                $loginError = 'Invalid username or password.';
            } else {
                session_regenerate_id(true);

                $_SESSION['is_admin']       = true;
                $_SESSION['admin_id']       = (int) $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                $db->prepare("UPDATE admins SET last_login_at = NOW() WHERE id = :id")
                   ->execute([':id' => $admin['id']]);

                setFlash('success', 'Welcome back, ' . $admin['username'] . '!');
                redirect(ADMIN_URL . '/index.php');
            }
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
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
  <title>Admin Sign In - <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= ADMIN_URL ?>/css/admin.css">
</head>
<body class="admin-login-page">

  <main class="admin-login-wrap">
    <div class="admin-login-card">
      <div class="admin-login-brand">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2 4 5v6c0 5.25 3.4 10.4 8 11 4.6-.6 8-5.75 8-11V5l-8-3z"/>
        </svg>
        <h1>Arena <span>Admin</span></h1>
        <p>Restricted area - administrators only.</p>
      </div>

      <?php if ($loginError !== null): ?>
        <div class="alert alert-error"><?= sanitize($loginError) ?></div>
      <?php endif; ?>

      <?php if ($flashMsg !== null): ?>
        <div class="alert alert-<?= sanitize($flashType) ?>"><?= sanitize($flashMsg) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>
        <input type="hidden" name="csrf" value="<?= getCSRF() ?>" />

        <div class="field-group">
          <label for="username">Username or Email</label>
          <input type="text" id="username" name="username" placeholder="admin"
                 value="<?= sanitize($username) ?>" autofocus />
        </div>

        <div class="field-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your admin password" />
        </div>

        <button type="submit" class="admin-btn">Sign In to Admin</button>
      </form>

      <a class="back-to-store" href="<?= SITE_URL ?>/index.php">← Back to <?= SITE_NAME ?></a>
    </div>
  </main>

</body>
</html>