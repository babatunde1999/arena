<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

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
  <style>
    :root {
      --orange: #d47719;
      --brown: #3E2C23;
      --orange-light: #f5b474;
      --gray: #f5f5f5;
      --border: #e8e8e8;
      --text-muted: #777;
      --nav-h: 64px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #f7f7f7; color: #222; }
    a { text-decoration: none; color: inherit; }
    button { border: none; background: none; cursor: pointer; font-family: inherit; }
    ul { list-style: none; }

    /* ══ HEADER ══ */
    header { position: sticky; top: 0; z-index: 200; background: #fff; border-bottom: 1px solid var(--border); }
    nav { height: var(--nav-h); display: flex; align-items: center; justify-content: space-between; padding: 0 20px; gap: 16px; }
    .logo-search { display: flex; align-items: center; gap: 12px; }
    .logo { height: 32px; width: auto; }
    .search-box { display: flex; align-items: center; border: 1.5px solid var(--border); border-radius: 8px; padding: 6px 12px; gap: 8px; transition: border-color 0.2s; }
    .search-box:focus-within { border-color: var(--orange); }
    .search-box input { border: none; font-size: 13px; width: 220px; background: transparent; outline: none; }
    .search-box input::placeholder { color: #aaa; }
    .search-icon { width: 16px; height: 16px; color: #aaa; flex-shrink: 0; }
    .nav-icons { display: flex; align-items: center; gap: 4px; }
    .nav-icons a { display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; color: #444; transition: background 0.15s, color 0.15s; }
    .nav-icons a:hover { background: var(--gray); color: var(--orange); }
    .nav-icon { width: 20px; height: 20px; }

    /* ══ BREADCRUMB ══ */
    .breadcrumb { padding: 14px 20px; font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; background: #fff; border-bottom: 1px solid var(--border); }
    .breadcrumb a { color: var(--orange); }
    .breadcrumb a:hover { text-decoration: underline; }
    .breadcrumb svg { width: 12px; height: 12px; stroke: #bbb; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    /* ══ PAGE WRAPPER ══ */
    .page-wrapper { max-width: 1200px; margin: 0 auto; padding: 28px 20px 60px; }

    /* ══ TOP BAR ══ */
    .top-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 24px; }
    .page-title { font-size: 1.3rem; font-weight: 700; color: var(--brown); display: flex; align-items: center; gap: 10px; }
    .page-title::before { content: ''; display: block; width: 4px; height: 22px; background: var(--orange); border-radius: 2px; }
    .shops-count { font-size: 12px; color: var(--text-muted); }

    /* ══ FILTER / SORT BAR ══ */
    .filter-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 28px; }
    .filter-label { font-size: 12px; font-weight: 600; color: var(--brown); white-space: nowrap; }
    .filter-pills { display: flex; gap: 8px; flex-wrap: wrap; flex: 1; }
    .pill { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; border: 1.5px solid var(--border); color: #555; cursor: pointer; transition: all 0.2s; background: #fff; }
    .pill:hover { border-color: var(--orange); color: var(--orange); }
    .pill.active { background: var(--orange); color: #fff; border-color: var(--orange); }
    .sort-select { padding: 7px 12px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--brown); background: #fff; outline: none; cursor: pointer; transition: border-color 0.2s; }
    .sort-select:focus { border-color: var(--orange); }

    /* ══ FEATURED BANNER ══ */
    .featured-banner { background: linear-gradient(135deg, var(--brown) 0%, #5c3d2e 100%); border-radius: 14px; padding: 28px 24px; margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
    .featured-banner-text h2 { color: #fff; font-size: 1.1rem; margin-bottom: 6px; }
    .featured-banner-text p { color: rgba(255,255,255,0.65); font-size: 12px; }
    .featured-banner-btn { padding: 10px 22px; background: var(--orange); color: #fff; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background 0.2s; }
    .featured-banner-btn:hover { background: #b8660f; }

    /* ══ SECTION HEADER ══ */
    .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .section-title { font-size: 1rem; font-weight: 700; color: #1a1a1a; position: relative; padding-left: 12px; }
    .section-title::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 4px; height: 70%; background: var(--orange); border-radius: 2px; }

    /* ══ SHOPS GRID ══ */
    .shops-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 40px; }
    .shop-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; overflow: hidden; transition: box-shadow 0.2s, transform 0.2s; display: flex; flex-direction: column; }
    .shop-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.09); transform: translateY(-2px); }
    .shop-banner { width: 100%; height: 80px; object-fit: cover; display: block; }
    .shop-banner-placeholder { width: 100%; height: 80px; display: flex; align-items: center; justify-content: center; }
    .shop-body { padding: 0 14px 16px; flex: 1; display: flex; flex-direction: column; }
    .shop-logo-wrap { margin-top: -22px; margin-bottom: 8px; }
    .shop-logo { width: 44px; height: 44px; border-radius: 10px; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.12); object-fit: cover; background: var(--gray); display: block; }
    .shop-logo-placeholder { width: 44px; height: 44px; border-radius: 10px; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.12); background: var(--orange); display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; color: #fff; }
    .shop-name { font-size: 13.5px; font-weight: 700; color: var(--brown); margin-bottom: 3px; }
    .shop-category { font-size: 11px; color: var(--text-muted); margin-bottom: 8px; }
    .shop-meta { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; flex-wrap: wrap; }
    .shop-products { font-size: 11px; color: var(--text-muted); }
    .shop-verified { display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 600; color: #27ae60; background: #eafaf1; padding: 2px 7px; border-radius: 10px; align-self: flex-start; }
    .shop-verified svg { width: 10px; height: 10px; stroke: #27ae60; fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
    .visit-btn { margin-top: auto; width: 100%; padding: 9px; background: var(--brown); color: #fff; border-radius: 8px; font-size: 12px; font-weight: 600; transition: background 0.2s; cursor: pointer; letter-spacing: 0.3px; display: block; text-align: center; }
    .visit-btn:hover { background: var(--orange); }

    /* ══ EMPTY STATE ══ */
    .empty-state { grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: #fff; border: 1px solid var(--border); border-radius: 14px; }
    .empty-state svg { width: 44px; height: 44px; color: var(--orange-light); margin-bottom: 14px; }
    .empty-state h3 { font-size: 1rem; color: var(--brown); margin-bottom: 6px; }
    .empty-state p { font-size: 13px; color: var(--text-muted); margin-bottom: 16px; }

    /* ══ NEWSLETTER ══ */
    .newsletter-section { background: linear-gradient(135deg, var(--brown) 0%, #5c3d2e 100%); padding: 48px 20px; text-align: center; border-radius: 16px; margin-top: 20px; }
    .newsletter-section h2 { color: #fff; font-size: 1.3rem; margin-bottom: 8px; }
    .newsletter-section p { color: rgba(255,255,255,0.7); font-size: 13px; margin-bottom: 24px; }
    .newsletter-form { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; max-width: 460px; margin: 0 auto; }
    .newsletter-input { flex: 1; min-width: 200px; height: 42px; border: 1.5px solid rgba(255,255,255,0.2); border-radius: 8px; padding: 0 16px; background: rgba(255,255,255,0.08); color: #fff; font-size: 13px; outline: none; }
    .newsletter-input::placeholder { color: rgba(255,255,255,0.4); }
    .newsletter-input:focus { border-color: var(--orange-light); }
    .newsletter-btn { height: 42px; padding: 0 24px; background: var(--orange); color: #fff; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }

    /* ══ FOOTER ══ */
    footer { background: #1a1109; color: rgba(255,255,255,0.75); padding: 48px 20px 24px; margin-top: 40px; }
    .footer-grid { display: flex; flex-wrap: wrap; gap: 32px; max-width: 1200px; margin: 0 auto 40px; }
    .footer-col { flex: 1 1 150px; }
    .footer-col h4 { color: #fff; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
    .footer-col ul li { margin-bottom: 9px; }
    .footer-col ul li a { font-size: 12px; color: rgba(255,255,255,0.6); transition: color 0.2s; }
    .footer-col ul li a:hover { color: var(--orange-light); }
    .footer-bottom { border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; max-width: 1200px; margin: 0 auto; font-size: 12px; color: rgba(255,255,255,0.35); }

    @media (min-width: 800px) { .shops-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); } }
    @media (min-width: 1100px) {
      nav { padding: 0 40px; }
      .page-wrapper { padding: 28px 40px 60px; }
    }
  </style>
</head>
<body>

<!-- ══ HEADER ══ -->
<header>
  <nav>
    <div class="logo-search">
      <a href="index.php"><img src="./images/logo.jpg" class="logo" alt="Arena Market" /></a>
      <form class="search-box" action="index.php" method="GET">
        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
        <input type="text" name="q" placeholder="Search products or shops..." />
      </form>
    </div>
    <div class="nav-icons">
      <a href="#" title="Wishlist"><svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg></a>
      <a href="<?= isLoggedIn() ? 'index.php' : 'login.php' ?>" title="My Account"><svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></a>
      <a href="#" title="Cart"><svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg></a>
    </div>
  </nav>
</header>

<!-- ══ BREADCRUMB ══ -->
<div class="breadcrumb">
  <a href="index.php">Home</a>
  <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
  <span>Shops</span>
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

<!-- ══ FOOTER ══ -->
<footer>
  <div class="footer-grid">
    <div class="footer-col">
      <h4>Arena Market</h4>
      <ul><li><a href="#">About Us</a></li><li><a href="#">Careers</a></li><li><a href="#">Press</a></li><li><a href="#">Blog</a></li></ul>
    </div>
    <div class="footer-col">
      <h4>Help</h4>
      <ul><li><a href="#">Help Centre</a></li><li><a href="#">Track an Order</a></li><li><a href="#">Returns &amp; Refunds</a></li><li><a href="#">Contact Us</a></li></ul>
    </div>
    <div class="footer-col">
      <h4>Sell on Arena</h4>
      <ul><li><a href="#">Become a Vendor</a></li><li><a href="#">Vendor Portal</a></li><li><a href="#">Seller Guidelines</a></li></ul>
    </div>
    <div class="footer-col">
      <h4>Policies</h4>
      <ul><li><a href="#">Privacy Policy</a></li><li><a href="#">Terms of Use</a></li><li><a href="#">Cookie Policy</a></li></ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> Arena Market. All rights reserved.</p>
    <p>Nigerian Army Shopping Arena</p>
  </div>
</footer>

</body>
</html>