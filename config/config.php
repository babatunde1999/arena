<?php
/**
 * Application Configuration
 * Arena Market - E-Commerce Platform
 */

// ── Site Info ──
define('SITE_NAME', 'Arena Market');
define('SITE_URL', 'http://localhost/Arena/public');
define('SITE_EMAIL', 'support@thearenamarket.com');
define('SITE_PHONE', '+234 906 925 9369');
define('SITE_ADDRESS', 'Bolade Bus-Stop, Oshodi, Lagos, Nigeria');

// ── Paths ──
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('IMAGES_PATH', PUBLIC_PATH . '/images');

// ── URLs ──
define('CSS_URL', SITE_URL . '/css');
define('JS_URL', SITE_URL . '/js');
define('IMAGES_URL', SITE_URL . '/images');
define('UPLOADS_URL', SITE_URL . '/uploads');

// ── Currency ──
define('CURRENCY_SYMBOL', '₦');
define('CURRENCY_CODE', 'NGN');

// ── Pagination ──
define('PRODUCTS_PER_PAGE', 12);
define('SHOPS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 10);

// ── Upload Limits ──
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('PRODUCT_MAX_IMAGES', 5); // cover + gallery

// ── Password Settings ──
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);

// ── Session ──
define('SESSION_LIFETIME', 3600 * 24); // 24 hours

// ── Error Reporting ──
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
