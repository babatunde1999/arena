<?php
/**
 * Shared admin header. Expects includes/bootstrap.php to be loaded already.
 * Usage: set $pageTitle before including this file; $active = 'dashboard'|'users'.
 */
$pageTitle = $pageTitle ?? 'Admin';
$active    = $active ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= sanitize($pageTitle) ?> - <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= ADMIN_URL ?>/css/admin.css">
</head>
<body>
  <header class="admin-topbar">
    <div class="admin-topbar-inner">
      <a class="admin-brand" href="<?= ADMIN_URL ?>/index.php">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2 4 5v6c0 5.25 3.4 10.4 8 11 4.6-.6 8-5.75 8-11V5l-8-3z"/>
        </svg>
        <span>Arena <em>Admin</em></span>
      </a>

      <nav class="admin-nav">
        <a href="<?= ADMIN_URL ?>/index.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="<?= ADMIN_URL ?>/users.php" class="<?= $active === 'users' ? 'active' : '' ?>">Users</a>
        <a href="<?= ADMIN_URL ?>/products.php" class="<?= $active === 'products' ? 'active' : '' ?>">Products</a>
        <a href="<?= SITE_URL ?>/index.php" target="_blank" rel="noopener">View Store ↗</a>
      </nav>

      <div class="admin-topbar-right">
        <span class="admin-chip" title="<?= sanitize($_SESSION['admin_username'] ?? '') ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <?= sanitize($_SESSION['admin_username'] ?? 'admin') ?>
        </span>
        <a class="admin-logout" href="<?= ADMIN_URL ?>/logout.php">Sign Out</a>
      </div>
    </div>
  </header>

  <main class="admin-content"><?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div>
  <?php endif; ?>