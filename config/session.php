<?php
/**
 * Session Configuration & Helpers
 * Arena Market - E-Commerce Platform
 */

// ── Start Session ──
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'domain'   => '',
        'secure'   => false, // Set to true in production with HTTPS
        'httponly'  => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// ── Session Helper Functions ──

/**
 * Set a session flash message
 * @param string $type  - success, error, warning, info
 * @param string $message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return string|null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * @return string|null
 */
function getUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Require user to be logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Please sign in to continue.');
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

/**
 * Redirect to URL
 * @param string $url
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Sanitize input
 * @param string $data
 * @return string
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

/**
 * Get CSRF token
 * @return string
 */
function getCSRF() {
    generateCSRF();
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format price in Naira
 * @param float $amount
 * @return string
 */
function formatPrice($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

/**
 * Check if current user is admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
