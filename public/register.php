<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name'] ?? ''),
        'username'   => trim($_POST['username'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'gender'     => $_POST['gender'] ?? 'male',
    ];

    $firstName = $old['first_name'];
    $lastName  = $old['last_name'];
    $username  = $old['username'];
    $email     = $old['email'];
    $gender    = $old['gender'];
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if (empty($firstName)) {
        $errors['first_name'] = 'First name is required.';
    } elseif (strlen($firstName) < 2) {
        $errors['first_name'] = 'First name must be at least 2 characters.';
    }

    if (empty($lastName)) {
        $errors['last_name'] = 'Last name is required.';
    } elseif (strlen($lastName) < 2) {
        $errors['last_name'] = 'Last name must be at least 2 characters.';
    }

    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $errors['username'] = 'Username must be 3-30 characters (letters, numbers, underscores only).';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetch()) {
            $errors['username'] = 'Username is already taken.';
        }
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        $db = $db ?? getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email address is already registered.';
        }
    }

    if (!in_array($gender, ['male', 'female'])) {
        $errors['gender'] = 'Please select a gender.';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors['password'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter and one number.';
    }

    if (empty($confirm)) {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            $db = getDB();
            $hashedPassword = password_hash($password, PASSWORD_HASH_ALGO);
            $verificationToken = generateVerificationToken();

            $stmt = $db->prepare("
                INSERT INTO users (first_name, last_name, username, email, gender, password, verification_token, verification_token_expires, created_at)
                VALUES (:first_name, :last_name, :username, :email, :gender, :password, :verification_token, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 12 HOUR), NOW())
            ");

            $stmt->execute([
                ':first_name'         => $firstName,
                ':last_name'          => $lastName,
                ':username'           => $username,
                ':email'              => $email,
                ':gender'             => $gender,
                ':password'           => $hashedPassword,
                ':verification_token' => $verificationToken,
            ]);

            $userId = $db->lastInsertId();

            $mailSent = sendVerificationEmail([
                'id'                  => $userId,
                'first_name'          => $firstName,
                'email'               => $email,
                'verification_token'  => $verificationToken,
            ]);

            if ($mailSent) {
                setFlash('success', 'Account created! Please check your email to verify your account.');
                redirect(SITE_URL . '/verify.php?sent=1&email=' . urlencode($email));
            } else {
                setFlash('warning', 'Account created, but the verification email could not be sent. You can request a new one later.');
                redirect(SITE_URL . '/login.php');
            }

        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registration</title>
  <link rel="stylesheet" href="css/register.css">
  <link rel="stylesheet" href="css/default.css">
</head>
<body>

  <div class="signup-container">
    <h1>Create an Account</h1>
    <p class="subtitle">Fill in the details below to get started.</p>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-error"><?= $errors['general'] ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>

      <p class="section-label">Personal Information</p>

      <div class="field-row">
        <div class="field-group">
          <label for="first-name">First Name</label>
          <input type="text" id="first-name" name="first_name" placeholder="John"
                 value="<?= sanitize($old['first_name'] ?? '') ?>"
                 class="<?= !empty($errors['first_name']) ? 'input-error' : '' ?>" />
          <?php if (!empty($errors['first_name'])): ?>
            <span class="error-text"><?= $errors['first_name'] ?></span>
          <?php endif; ?>
        </div>
        <div class="field-group">
          <label for="last-name">Last Name</label>
          <input type="text" id="last-name" name="last_name" placeholder="Doe"
                 value="<?= sanitize($old['last_name'] ?? '') ?>"
                 class="<?= !empty($errors['last_name']) ? 'input-error' : '' ?>" />
          <?php if (!empty($errors['last_name'])): ?>
            <span class="error-text"><?= $errors['last_name'] ?></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="field-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="@johndoe"
               value="<?= sanitize($old['username'] ?? '') ?>"
               class="<?= !empty($errors['username']) ? 'input-error' : '' ?>" />
        <?php if (!empty($errors['username'])): ?>
          <span class="error-text"><?= $errors['username'] ?></span>
        <?php endif; ?>
      </div>

      <div class="field-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="john@example.com"
               value="<?= sanitize($old['email'] ?? '') ?>"
               class="<?= !empty($errors['email']) ? 'input-error' : '' ?>" />
        <?php if (!empty($errors['email'])): ?>
          <span class="error-text"><?= $errors['email'] ?></span>
        <?php endif; ?>
      </div>

      <div class="field-row">
        <div class="field-group">
          <label>Gender</label>
          <div class="options-row">
            <div class="option-item">
              <input type="radio" name="gender" id="male" value="male"
                     <?= ($old['gender'] ?? 'male') === 'male' ? 'checked' : '' ?> />
              <label for="male">Male</label>
            </div>
            <div class="option-item">
              <input type="radio" name="gender" id="female" value="female"
                     <?= ($old['gender'] ?? '') === 'female' ? 'checked' : '' ?> />
              <label for="female">Female</label>
            </div>
          </div>
          <?php if (!empty($errors['gender'])): ?>
            <span class="error-text"><?= $errors['gender'] ?></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="field-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Create a strong password"
               class="<?= !empty($errors['password']) ? 'input-error' : '' ?>" />
        <?php if (!empty($errors['password'])): ?>
          <span class="error-text"><?= $errors['password'] ?></span>
        <?php endif; ?>
      </div>

      <div class="field-group">
        <label for="confirm-password">Confirm Password</label>
        <input type="password" id="confirm-password" name="confirm_password" placeholder="Repeat your password"
               class="<?= !empty($errors['confirm_password']) ? 'input-error' : '' ?>" />
        <?php if (!empty($errors['confirm_password'])): ?>
          <span class="error-text"><?= $errors['confirm_password'] ?></span>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn-create">Create Account</button>

      <p class="login-link">Already have an account? <a href="login.php">Sign in</a></p>
    </form>
  </div>

</body>
</html>
