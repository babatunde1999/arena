<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

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

    /* HEADER */
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

    /* BREADCRUMB */
    .breadcrumb { padding: 14px 20px; font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; background: #fff; border-bottom: 1px solid var(--border); }
    .breadcrumb a { color: var(--orange); }
    .breadcrumb a:hover { text-decoration: underline; }
    .breadcrumb svg { width: 12px; height: 12px; stroke: #bbb; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    /* LAYOUT */
    .page-wrapper { max-width: 1200px; margin: 0 auto; padding: 24px 20px 60px; }
    .page-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 24px; }
    .page-title { font-size: 1.3rem; font-weight: 700; color: var(--brown); display: flex; align-items: center; gap: 10px; }
    .page-title::before { content: ''; display: block; width: 4px; height: 22px; background: var(--orange); border-radius: 2px; }
    .categories-count { font-size: 12px; color: var(--text-muted); }

    /* GRID */
    .cat-wrap { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 1px; background: var(--border); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
    .cat-item { background: #fff; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 18px 10px 14px; gap: 10px; cursor: pointer; transition: background 0.15s; text-align: center; }
    .cat-item:hover { background: #fff8f2; }
    .cat-item:hover .cat-name { color: var(--orange); }
    .cat-item:hover .cat-img, .cat-item:hover .cat-icon-placeholder { border-color: var(--orange); transform: scale(1.05); }
    .cat-img { width: 72px; height: 72px; object-fit: cover; border-radius: 10px; border: 2px solid transparent; transition: border-color 0.2s, transform 0.2s; background: var(--gray); }
    .cat-icon-placeholder { width: 72px; height: 72px; border-radius: 10px; background: linear-gradient(135deg, #fff0e0, #ffe0b2); display: flex; align-items: center; justify-content: center; border: 2px solid transparent; transition: border-color 0.2s, transform 0.2s; flex-shrink: 0; }
    .cat-icon-placeholder svg { width: 30px; height: 30px; stroke: var(--orange); fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
    .cat-name { font-size: 11.5px; font-weight: 600; color: #333; line-height: 1.3; transition: color 0.15s; }
    .cat-count { font-size: 10.5px; color: var(--text-muted); font-weight: 600; }

    /* EMPTY STATE */
    .empty-state { text-align: center; padding: 60px 20px; background: #fff; border: 1px solid var(--border); border-radius: 12px; }
    .empty-state svg { width: 44px; height: 44px; color: var(--orange-light); margin-bottom: 14px; }
    .empty-state h3 { font-size: 1rem; color: var(--brown); margin-bottom: 6px; }
    .empty-state p { font-size: 13px; color: var(--text-muted); margin-bottom: 16px; }
    .empty-state a { display: inline-block; padding: 9px 20px; background: var(--orange); color: #fff; border-radius: 8px; font-size: 12px; font-weight: 700; }

    /* NEWSLETTER */
    .newsletter-section { background: linear-gradient(135deg, var(--brown) 0%, #5c3d2e 100%); padding: 48px 20px; text-align: center; margin-top: 40px; border-radius: 16px; }
    .newsletter-section h2 { color: #fff; font-size: 1.3rem; margin-bottom: 8px; }
    .newsletter-section p { color: rgba(255,255,255,0.7); font-size: 13px; margin-bottom: 24px; }
    .newsletter-form { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; max-width: 460px; margin: 0 auto; }
    .newsletter-input { flex: 1; min-width: 200px; height: 42px; border: 1.5px solid rgba(255,255,255,0.2); border-radius: 8px; padding: 0 16px; background: rgba(255,255,255,0.08); color: #fff; font-size: 13px; outline: none; }
    .newsletter-input::placeholder { color: rgba(255,255,255,0.4); }
    .newsletter-input:focus { border-color: var(--orange-light); }
    .newsletter-btn { height: 42px; padding: 0 24px; background: var(--orange); color: #fff; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }

    /* FOOTER */
    footer { background: #1a1109; color: rgba(255,255,255,0.75); padding: 48px 20px 24px; margin-top: 40px; }
    .footer-grid { display: flex; flex-wrap: wrap; gap: 32px; max-width: 1200px; margin: 0 auto 40px; }
    .footer-col { flex: 1 1 150px; }
    .footer-col h4 { color: #fff; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
    .footer-col ul li { margin-bottom: 9px; }
    .footer-col ul li a { font-size: 12px; color: rgba(255,255,255,0.6); transition: color 0.2s; }
    .footer-col ul li a:hover { color: var(--orange-light); }
    .footer-bottom { border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; max-width: 1200px; margin: 0 auto; font-size: 12px; color: rgba(255,255,255,0.35); }

    @media (min-width: 600px) {
      .cat-wrap { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); }
      .cat-img, .cat-icon-placeholder { width: 80px; height: 80px; }
    }
    @media (min-width: 1100px) {
      nav { padding: 0 40px; }
      .page-wrapper { padding: 28px 40px 60px; }
    }
  </style>
</head>
<body>

<!-- HEADER -->
<header>
  <nav>
    <div class="logo-search">
      <a href="index.php"><img src="./images/logo.jpg" class="logo" alt="Arena Market" /></a>
      <form class="search-box" action="index.php" method="GET">
        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
        <input type="text" name="q" placeholder="Search products or shops" />
      </form>
    </div>
    <div class="nav-icons">
      <a href="#" title="Wishlist"><svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg></a>
      <a href="<?= isLoggedIn() ? 'index.php' : 'login.php' ?>" title="My Account"><svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></a>
      <a href="#" title="Cart"><svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg></a>
    </div>
  </nav>
</header>

<!-- BREADCRUMB -->
<div class="breadcrumb">
  <a href="index.php">Home</a>
  <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
  <span>All Categories</span>
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

<!-- FOOTER -->
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