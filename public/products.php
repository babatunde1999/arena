<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/shop.php';
require_once __DIR__ . '/../includes/categories.php';

requireLogin();

$db   = getDB();
$shop = getOrCreateShop($db);

$flash = getFlash();
$error = '';
$info  = '';

// ── DELETE product ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        if (!verifyCSRF($_POST['_token'] ?? '')) {
            setFlash('error', 'Invalid security token. Please try again.');
        } else {
            $id = (int) ($_POST['id'] ?? 0);

            $stmt = $db->prepare("SELECT id FROM products WHERE id = :id AND shop_id = :shop_id LIMIT 1");
            $stmt->execute([':id' => $id, ':shop_id' => $shop['id']]);

            if ($stmt->fetch()) {
                deleteImageFiles(getProductImageFiles($db, $id));

                $db->prepare("DELETE FROM products WHERE id = :id AND shop_id = :shop_id")
                   ->execute([':id' => $id, ':shop_id' => $shop['id']]);

                setFlash('success', 'Product deleted successfully.');
            } else {
                setFlash('error', 'Product not found or you do not own it.');
            }
        }
        redirect(SITE_URL . '/products.php');
    }
}

// ── Build querystring helpers ──
$q    = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$per  = PRODUCTS_PER_PAGE;

// ── Count + fetch with pagination ──
$where    = 'shop_id = :shop_id';
$params   = [':shop_id' => $shop['id']];
if ($q !== '') {
    $where          .= ' AND (name LIKE :q OR description LIKE :q)';
    $params[':q']    = '%' . $q . '%';
}

$stmt = $db->prepare("SELECT COUNT(*) AS total FROM products WHERE {$where}");
$stmt->execute($params);
$total  = (int) $stmt->fetch()['total'];
$pages  = max(1, (int) ceil($total / $per));
$page   = min($page, $pages);
$offset = ($page - 1) * $per;

$stmt = $db->prepare(
    "SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE {$where}
     ORDER BY p.created_at DESC, p.id DESC
     LIMIT {$per} OFFSET {$offset}"
);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ── Load gallery images for the products on this page ──
$galleryMap = [];
$ids = array_map(function ($row) { return (int) $row['id']; }, $products);
if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare(
        "SELECT product_id, image FROM product_images
         WHERE product_id IN ({$placeholders})
         ORDER BY sort_order ASC, id ASC"
    );
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $g) {
        $galleryMap[(int) $g['product_id']][] = $g['image'];
    }
}

$pageParam = $q !== '' ? 'q=' . urlencode($q) . '&' : '';

// ── Stock helper ──
function stockInfo($qty) {
    if ($qty <= 0) {
        return ['class' => 'out', 'label' => 'Out of stock'];
    }
    if ($qty <= 5) {
        return ['class' => 'low', 'label' => 'Only ' . $qty . ' left'];
    }
    return ['class' => 'ok', 'label' => $qty . ' in stock'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Products – Arena Market</title>
  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/products.css">
</head>
<body>

  <header class="vendor-header">
    <a href="index.php" title="Go to homepage">
      <img src="./images/logo.jpg" class="logo" alt="Arena Market">
    </a>
    <nav class="vendor-links">
      <a href="index.php">Home</a>
      <a href="products.php">My Products</a>
    </nav>
    <div class="vendor-user">
      <div class="avatar"><?= sanitize(mb_substr(getUsername(), 0, 1)) ?></div>
      <span class="name"><?= sanitize(getUsername()) ?></span>
      <a class="sign-out" href="<?= SITE_URL ?>/logout.php">Sign out</a>
    </div>
  </header>

  <div class="vendor-page">

    <?php if (!empty($flash)): ?>
      <div class="flash-banner flash-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div>
    <?php endif; ?>

    <div class="page-head">
      <div>
        <h1><?= sanitize($shop['name']) ?></h1>
        <p class="sub">Manage the products in your shop.</p>
      </div>
      <a class="btn-primary" href="product-form.php">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Add Product
      </a>
    </div>

    <div class="toolbar">
      <form class="search-form" method="GET" action="products.php">
        <input class="search-box-input" type="text" name="q" placeholder="Search your products…"
               value="<?= sanitize($q) ?>">
        <button class="search-submit" type="submit">Search</button>
      </form>
      <span class="results"><?= $total ?> product<?= $total === 1 ? '' : 's' ?></span>
    </div>

    <div class="product-grid">
      <?php if (empty($products)): ?>
        <div class="empty-state">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H4.5a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 0 0 9.375 7.5H4.5v9.75h15V7.5h-4.875A2.625 2.625 0 0 0 12 4.875Zm0 0a2.625 2.625 0 0 1 2.625 2.625v.75M9.375 7.5h5.25" />
          </svg>
          <h3><?= $q !== '' ? 'No products match "' . sanitize($q) . '"' : 'No products yet' ?></h3>
          <p><?= $q !== '' ? 'Try a different keyword.' : 'Add your first product to start selling on Arena Market.' ?></p>
          <?php if ($q !== ''): ?>
            <a class="btn-secondary" href="products.php">Clear search</a>
          <?php else: ?>
            <a class="btn-primary" href="product-form.php">Add a Product</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php foreach ($products as $p): ?>
        <?php
        $stock  = stockInfo((int) $p['quantity']);
        $images = [];
        if (!empty($p['image'])) {
            $images[] = $p['image'];
        }
        if (!empty($galleryMap[(int) $p['id']])) {
            $images = array_merge($images, $galleryMap[(int) $p['id']]);
        }
        ?>
        <div class="product-card">
          <div class="product-thumb">
            <?php if (!empty($images)): ?>
              <img class="thumb-main" src="<?= SITE_URL ?>/uploads/<?= sanitize($images[0]) ?>"
                   alt="<?= sanitize($p['name']) ?>" loading="lazy">
              <?php if (count($images) > 1): ?>
                <span class="img-count">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" /></svg>
                  <?= count($images) ?>
                </span>
              <?php endif; ?>
            <?php else: ?>
              <div class="thumb-placeholder"><?= sanitize(mb_substr($p['name'], 0, 1)) ?></div>
            <?php endif; ?>
            <?php if ((int) $p['is_featured'] === 1): ?>
              <span class="feat-badge">FEATURED</span>
            <?php endif; ?>
          </div>
          <?php if (count($images) > 1): ?>
            <div class="thumb-strip">
              <?php foreach ($images as $i => $img): ?>
                <img class="thumb-strip-img <?= $i === 0 ? 'active' : '' ?>"
                     src="<?= SITE_URL ?>/uploads/<?= sanitize($img) ?>"
                     data-full="<?= SITE_URL ?>/uploads/<?= sanitize($img) ?>"
                     alt="<?= sanitize($p['name']) ?> <?= $i + 1 ?>" loading="lazy">
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="product-body">
            <h3 class="product-name"><?= sanitize($p['name']) ?></h3>
            <span class="product-cat"><?= sanitize($p['category_name'] ?? 'Uncategorised') ?></span>
            <div class="product-price-row">
              <span class="product-price"><?= formatPrice($p['price']) ?></span>
              <?php if (!empty($p['old_price'])): ?>
                <span class="product-old-price"><?= formatPrice($p['old_price']) ?></span>
              <?php endif; ?>
            </div>
            <span class="stock-line <?= $stock['class'] ?>">
              <span class="stock-dot <?= $stock['class'] ?>"></span><?= $stock['label'] ?>
            </span>
            <div class="product-actions">
              <a class="btn-edit" href="product-form.php?id=<?= (int) $p['id'] ?>">Edit</a>
              <form method="POST" action="products.php" onsubmit="return confirm('Delete this product? This cannot be undone.')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <input type="hidden" name="_token" value="<?= getCSRF() ?>">
                <button type="submit" class="btn-delete">Delete</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
      <nav class="pagination" aria-label="Pagination">
        <a class="<?= $page <= 1 ? 'disabled' : '' ?>" href="products.php?<?= $pageParam ?>page=<?= $page - 1 ?>">&laquo;</a>
        <?php
        $start = max(1, $page - 2);
        $end   = min($pages, $page + 2);
        if ($start > 1):
        ?>
          <a href="products.php?<?= $pageParam ?>page=1">1</a>
          <?php if ($start > 2): ?><span class="ellipsis">&hellip;</span><?php endif; ?>
        <?php endif; ?>
        <?php for ($i = $start; $i <= $end; $i++): ?>
          <a class="<?= $i === $page ? 'current' : '' ?>" href="products.php?<?= $pageParam ?>page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($end < $pages): ?>
          <?php if ($end < $pages - 1): ?><span class="ellipsis">&hellip;</span><?php endif; ?>
          <a href="products.php?<?= $pageParam ?>page=<?= $pages ?>"><?= $pages ?></a>
        <?php endif; ?>
        <a class="<?= $page >= $pages ? 'disabled' : '' ?>" href="products.php?<?= $pageParam ?>page=<?= $page + 1 ?>">&raquo;</a>
      </nav>
    <?php endif; ?>

  </div>

  <script>
    // Click a thumbnail to switch the card's main image
    document.addEventListener('click', function (e) {
      var thumb = e.target.closest('.thumb-strip-img');
      if (!thumb) return;
      var card = thumb.closest('.product-card');
      if (!card) return;
      var main = card.querySelector('.thumb-main');
      if (main) {
        main.src = thumb.dataset.full;
        main.alt = thumb.alt;
      }
      card.querySelectorAll('.thumb-strip-img').forEach(function (t) {
        t.classList.toggle('active', t === thumb);
      });
    });
  </script>

</body>
</html>