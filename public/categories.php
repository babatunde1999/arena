<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$flash = getFlash();

// Categories that actually contain uploaded products, with a sample product image.
$stmt = $db->query(
    "SELECT c.id, c.name, c.slug, COUNT(p.id) AS product_count,
            (SELECT p2.image FROM products p2
              WHERE p2.category_id = c.id AND p2.image IS NOT NULL
              ORDER BY p2.created_at DESC, p2.id DESC LIMIT 1) AS sample_img
     FROM categories c
     JOIN products p ON p.category_id = c.id
     GROUP BY c.id
     ORDER BY product_count DESC, c.name ASC"
);
$categories = $stmt->fetchAll();

$totalProducts = 0;
foreach ($categories as $c) {
    $totalProducts += (int) $c['product_count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>All Categories – Arena Market</title>
  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/site.css?v=2">
  <link rel="stylesheet" href="css/categories.css?v=2">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<!-- BREADCRUMB -->
<div class="breadcrumb">
  <div class="breadcrumb-inner">
    <a href="index.php">Home</a>
    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <span>All Categories</span>
  </div>
</div>

<!-- PAGE -->
<div class="page-wrapper">

  <div class="page-head">
    <h1 class="page-title">All Categories</h1>
    <span class="categories-count">
      <?= count($categories) ?> categor<?= count($categories) === 1 ? 'y' : 'ies' ?> &middot; <?= $totalProducts ?> product<?= $totalProducts === 1 ? '' : 's' ?>
    </span>
  </div>

  <?php if (empty($categories)): ?>
    <div class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125h18a1.125 1.125 0 0 1 1.125 1.125v1.5m-19.125 0h7.5m7.5 0h3.75m-3.75 0h3.75m-13.5-9.375 2.25 2.25m0 0 2.25-2.25m-2.25 2.25V4.5m4.5 9.375L15 15.375m0 0 2.25-2.25M15 15.375V9" />
      </svg>
      <h3>No categories with products yet</h3>
      <p>Categories fill up as vendors upload their goods.</p>
      <?php if (isLoggedIn()): ?>
        <a href="products.php">Start Selling</a>
      <?php else: ?>
        <a href="login.php">Open a Shop</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="cat-wrap">
      <?php foreach ($categories as $cat): ?>
        <a href="#" class="cat-item">
          <?php if (!empty($cat['sample_img'])): ?>
            <img class="cat-img" src="<?= SITE_URL ?>/uploads/<?= sanitize($cat['sample_img']) ?>" alt="<?= sanitize($cat['name']) ?>">
          <?php else: ?>
            <div class="cat-icon-placeholder">
              <svg viewBox="0 0 24 24"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.57a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.57a2 2 0 0 0-1.34-2.23z"/></svg>
            </div>
          <?php endif; ?>
          <span class="cat-name"><?= sanitize($cat['name']) ?></span>
          <span class="cat-count"><?= (int) $cat['product_count'] ?> item<?= (int) $cat['product_count'] === 1 ? '' : 's' ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- NEWSLETTER -->
  <div class="newsletter-section">
    <h2>Stay in the Loop</h2>
    <p>Get the latest deals and new arrivals straight to your inbox.</p>
    <div class="newsletter-form">
      <input class="newsletter-input" type="email" placeholder="Enter your email address" />
      <button class="newsletter-btn">Subscribe</button>
    </div>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php include __DIR__ . '/includes/site-modals.php'; ?>

<script src="js/site.js"></script>
</body>
</html>