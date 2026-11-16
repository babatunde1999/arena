<?php
/**
 * All deals — Arena Market
 *
 * Lists every product that currently has a genuine old_price > price
 * discount and is in stock. Uses the exact same query helper and card
 * partial as the homepage "Today's Deals" section.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/store.php';

$db    = getDB();
$flash = getFlash();

$allDeals   = storeDeals($db);
$totalDeals = count($allDeals);
$perPage    = 12;
$page       = max(1, (int) ($_GET['page'] ?? 1));
$pages      = max(1, (int) ceil($totalDeals / $perPage));
$page       = min($page, $pages);
$deals      = array_slice($allDeals, ($page - 1) * $perPage, $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Today's Deals – Arena Market</title>
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
    <span>Today's Deals</span>
  </div>
</div>

<main class="store-page">

  <?php if (!empty($flash)): ?>
    <div class="flash-banner flash-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div>
  <?php endif; ?>

  <div class="store-head">
    <h1>Today's Deals</h1>
    <p>
      <?= $totalDeals ?> special offer<?= $totalDeals === 1 ? '' : 's' ?> from Arena Market vendors
    </p>
  </div>

  <?php if (empty($deals)): ?>
    <div class="empty-store">
      <h2>No deals right now</h2>
      <p>New offers are added regularly — check back soon or browse the marketplace.</p>
      <a class="btn-store-primary" href="shop.php">Browse shops</a>
    </div>
  <?php else: ?>
    <div class="deals-grid">
      <?php foreach ($deals as $deal): ?>
        <?php
        $card       = $deal;
        $cardReturn = 'deals.php?page=' . $page;
        include __DIR__ . '/includes/product-card.php';
        ?>
      <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
      <nav class="store-pagination" aria-label="Deals pages">
        <?php if ($page > 1): ?>
          <a href="deals.php?page=<?= $page - 1 ?>">← Prev</a>
        <?php else: ?>
          <span class="disabled">← Prev</span>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="current" aria-current="page"><?= $i ?></span>
          <?php else: ?>
            <a href="deals.php?page=<?= $i ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $pages): ?>
          <a href="deals.php?page=<?= $page + 1 ?>">Next →</a>
        <?php else: ?>
          <span class="disabled">Next →</span>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="js/site.js"></script>
<script src="js/store.js"></script>
</body>
</html>
