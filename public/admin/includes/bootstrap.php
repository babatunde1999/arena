<?php
/**
 * Admin bootstrap - shared helpers for the admin area.
 * Arena Market - E-Commerce Platform
 */

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../config/database.php';

define('ADMIN_URL', SITE_URL . '/admin');

/**
 * Require an authenticated administrator, otherwise bounce to the admin login.
 * (Admin sessions are tracked via $_SESSION['is_admin']; they are not store users.)
 */
function requireAdmin() {
    if (!isAdmin()) {
        setFlash('error', 'Please sign in as an administrator.');
        redirect(ADMIN_URL . '/login.php');
    }
}

/**
 * Allowed user roles used by user management.
 * (Admins have their own `admins` table, so storefront roles are user/vendor only.)
 * @return array
 */
function getRoles() {
    return ['user', 'vendor'];
}