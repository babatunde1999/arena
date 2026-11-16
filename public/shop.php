<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$flash = getFlash();

$catSlug = trim($_GET['cat'] ?? '');
$sort    = trim($_GET['sort'] ?? '');

$catId = 0;
if ($catSlug !== '') {
    $stmt = $db->prepare("SELECT id FROM categories WHERE slug = :slug LIMIT 1");
    $stmt->execute([':slug' => $catSlug]);
    $catId = (int) $stmt->fetchColumn();
}

// ── Category pills (categories that actually contain products) ──
$pills = $db->query(
    "SELECT c.slug, c.name FROM categories c
     JOIN products p ON p.category_id = c.id
     GROUP BY c.id ORDER BY c.name ASC"
)->fetchAll();

// ── Shops that carry at least one product ──
$where  = "";
$params = [];
if ($catId > 0) {
    $where = " AND EXISTS (
        SELECT 1 FROM products f
        WHERE f.shop_id = s.id AND f.category_id = :cid
    )";
    $params = [':cid' => $catId];
}

switch ($sort) {
    case 'newest':
        $order = "MAX(p.created_at) DESC, s.id DESC";
        break;
    case 'products':
    default:
        $order = "COUNT(p.id) DESC, s.id ASC";
        break;
}

$sql = "SELECT s.id, s.name, s.slug, s.location, s.verified, s.logo, s.banner,
               COUNT(p.id) AS product_count,
               (SELECT c2.name FROM categories c2
                 WHERE c2.id = (SELECT p3.category_id FROM products p3
                                WHERE p3.shop_id = s.id AND p3.category_id IS NOT NULL
                                GROUP BY p3.category_id
                                ORDER BY COUNT(*) DESC, p3.category_id ASC LIMIT 1)
               ) AS top_category
        FROM shops s
        JOIN products p ON p.shop_id = s.id
        WHERE 1 = 1 {$where}
        GROUP BY s.id
        ORDER BY {$order}";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$shops = $stmt->fetchAll();

$totalShops = count($shops);

$catName = '';
foreach ($pills as $pill) {
    if ($pill['slug'] === $catSlug) {
        $catName = $pill['name'];
        break;
    }
}

$bannerGradients = [
    'linear-gradient(135deg,#f5e6d0,#e8c88a)',
    'linear-gradient(135deg,#d0e8f5,#a0c8e8)',
    'linear-gradient(135deg,#d5f0e0,#a0dbb8)',
    'linear-gradient(135deg,#f0d5f5,#dba0e8)',
    'linear-gradient(135deg,#f5d0d0,#e88a8a)',
    'linear-gradient(135deg,#fff0cc,#ffd980)',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Browse Shops – Arena Market</title>
  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/site.css?v=2">
  <link rel="stylesheet" href="css/shop.css?v=2">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<!-- ══ BREADCRUMB ══ -->
<div class="breadcrumb">
  <div class="breadcrumb-inner">
    <a href="index.php">Home</a>
    <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <span>Shops</span>
  </div>
</div>

<!-- ══ PAGE ══ -->
<div class="page-wrapper">

  <div class="top-bar">
    <h1 class="page-title">Browse Shops</h1>
    <span class="shops-count">Showing <?= $totalShops ?> shop<?= $totalShops === 1 ? '' : 's' ?></span>
  </div>

  <div class="filter-bar">
    <span class="filter-label">Filter:</span>
    <div class="filter-pills">
      <a class="pill <?= $catSlug === '' ? 'active' : '' ?>" href="shop.php?sort=<?= urlencode($sort) ?>">All</a>
      <?php foreach ($pills as $pill): ?>
        <a class="pill <?= $catSlug === $pill['slug'] ? 'active' : '' ?>"
           href="shop.php?cat=<?= urlencode($pill['slug']) ?>&sort=<?= urlencode($sort) ?>">
          <?= sanitize($pill['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>
    <select class="sort-select" onchange="location.href=this.value;">
      <option value="shop.php?sort=products<?= $catSlug !== '' ? '&cat=' . urlencode($catSlug) : '' ?>" <?= $sort === 'products' || $sort === '' ? 'selected' : '' ?>>Sort: Most Products</option>
      <option value="shop.php?sort=newest<?= $catSlug !== '' ? '&cat=' . urlencode($catSlug) : '' ?>" <?= $sort === 'newest' ? 'selected' : '' ?>>Sort: Newest</option>
    </select>
  </div>

  <!-- FEATURED BANNER -->
  <div class="featured-banner">
    <div class="featured-banner-text">
      <h2>Are you a vendor?</h2>
      <p>Open your shop today and reach thousands of buyers at Arena Market.</p>
    </div>
    <a class="featured-banner-btn" href="<?= isLoggedIn() ? 'products.php' : 'login.php' ?>">Open a Shop &rarr;</a>
  </div>

  <!-- SHOPS -->
  <div class="section-header">
    <h2 class="section-title"><?= $catName !== '' ? $catName : 'Shops on Arena' ?></h2>
  </div>

  <div class="shops-grid">
    <?php if (empty($shops)): ?>
      <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72L4.318 3.44A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72m-13.5 8.65h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
        </svg>
        <h3>No shops yet</h3>
        <p>Shops appear here as vendors start uploading their goods.</p>
        <?php if (isLoggedIn()): ?>
          <a class="visit-btn" style="max-width:200px; margin:0 auto;" href="products.php">Start Selling</a>
        <?php else: ?>
          <a class="visit-btn" style="max-width:200px; margin:0 auto;" href="login.php">Open a Shop</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php foreach ($shops as $i => $shop): ?>
      <div class="shop-card">
        <?php if (!empty($shop['banner'])): ?>
          <img class="shop-banner" src="<?= SITE_URL ?>/uploads/<?= sanitize($shop['banner']) ?>" alt="">
        <?php else: ?>
          <div class="shop-banner-placeholder" style="background: <?= $bannerGradients[$i % count($bannerGradients)] ?>;"></div>
        <?php endif; ?>
        <div class="shop-body">
          <div class="shop-logo-wrap">
            <?php if (!empty($shop['logo'])): ?>
              <img class="shop-logo" src="<?= SITE_URL ?>/uploads/<?= sanitize($shop['logo']) ?>" alt="">
            <?php else: ?>
              <div class="shop-logo-placeholder"><?= sanitize(mb_strtoupper(mb_substr($shop['name'], 0, 1))) ?></div>
            <?php endif; ?>
          </div>
          <div class="shop-name"><?= sanitize($shop['name']) ?></div>
          <div class="shop-category"><?= sanitize($shop['top_category'] ?? 'Marketplace') ?></div>
          <div class="shop-meta">
            <span class="shop-products"><?= (int) $shop['product_count'] ?> product<?= (int) $shop['product_count'] === 1 ? '' : 's' ?></span>
          </div>
          <?php if ((int) $shop['verified'] === 1): ?>
            <span class="shop-verified"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Verified</span>
          <?php endif; ?>
          <button class="visit-btn" style="margin-top:12px;">Visit Shop</button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

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