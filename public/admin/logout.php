<?php
/**
 * Admin logout - signs out and returns to the admin login.
 * Arena Market - E-Commerce Platform
 */

require_once __DIR__ . '/includes/bootstrap.php';

logout();

setFlash('info', 'You have been signed out of the admin panel.');
redirect(ADMIN_URL . '/login.php');