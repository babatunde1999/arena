<?php
/**
 * Wishlist — Arena Market
 *
 * Uses the same session wishlist and product card partial as the rest
 * of the storefront; removing an item reloads the page so the grid
 * stays accurate.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/store.php';

$db       = getDB();
$flash    = getFlash();
$products = storeWishlistDetails($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Your Wishlist – Arena Market</title>
  <link rel="stylesheet" href="css/default.css" />
  <link rel="stylesheet" href="css/site.css?v=2" />
  <link rel="stylesheet" href="css/store.css?v=3" />
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="breadcrumb">
  <div class="breadcrumb-inner">
    <a href="index.php">Home</a>
    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Wishlist</span>
  </div>
</div>

<main class="store-page">

  <?php if (!empty($flash)): ?>
    <div class="flash-banner flash-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div>
  <?php endif; ?>

  <div class="store-head">
    <h1>Your Wishlist</h1>
    <p><?= count($products) ?> saved product<?= count($products) === 1 ? '' : 's' ?></p>
  </div>

  <?php if (empty($products)): ?>

    <div class="empty-store">
      <h2>Nothing saved yet</h2>
      <p>Tap the heart on any product to save it here for later.</p>
      <a class="btn-store-primary" href="deals.php">Browse today's deals</a>
    </div>

  <?php else: ?>

    <div class="deals-grid">
      <?php foreach ($products as $wish): ?>
        <?php
        $card                 = $wish;
        $cardReturn           = 'wishlist.php';
        $cardRemoveOnWishlist = true;
        include __DIR__ . '/includes/product-card.php';
        ?>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="js/site.js"></script>
<script src="js/store.js"></script>
</body>
</html>
