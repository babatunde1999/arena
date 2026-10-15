<?php
/**
 * Admin product management - list, filter, feature (pin to home page),
 * delete products. Add/edit is handled by product-form.php.
 * Arena Market - E-Commerce Platform
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/../../includes/shop.php';
requireAdmin();

$db      = getDB();
$perPage = 15;

// ── Handle actions (POST + CSRF) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf'] ?? '')) {
        setFlash('error', 'Security token expired. Please try again.');
        redirect(ADMIN_URL . '/products.php');
    }

    $action = $_POST['action'] ?? '';
    $target = (int) ($_POST['id'] ?? 0);

    try {
        if ($action === 'feature') {
            // Toggle: pin to the home page (is_featured = 1) or unpin.
            $row = $db->query("SELECT is_featured FROM products WHERE id = {$target}")->fetch();
            if ($row !== false) {
                $new = (int) $row['is_featured'] === 1 ? 0 : 1;
                $db->prepare("UPDATE products SET is_featured = :f WHERE id = :id")
                   ->execute([':f' => $new, ':id' => $target]);
                setFlash('success', $new === 1
                    ? 'Product #' . $target . ' is now featured and shown on the home page.'
                    : 'Product #' . $target . ' was removed from the home page.');
            }
        } elseif ($action === 'delete') {
            $files = getProductImageFiles($db, $target);
            $db->prepare("DELETE FROM products WHERE id = :id")->execute([':id' => $target]);
            deleteImageFiles($files);
            setFlash('success', 'Product #' . $target . ' deleted, including its images.');
        } elseif ($action === 'feature_all') {
            $db->exec("UPDATE products SET is_featured = 1");
            setFlash('success', 'All products are now featured and shown on the home page.');
        } elseif ($action === 'unfeature_all') {
            $db->exec("UPDATE products SET is_featured = 0");
            setFlash('success', 'All products were removed from the home page.');
        } else {
            setFlash('error', 'Unknown action.');
        }
    } catch (PDOException $e) {
        error_log("Admin product action error: " . $e->getMessage());
        setFlash('error', 'Something went wrong. Please try again.');
    }

    redirect(ADMIN_URL . '/products.php?' . http_build_query(array_filter([
        'q'         => $_POST['q'] ?? '',
        'featured'  => $_POST['f_featured'] ?? '',
        'category'  => $_POST['f_category'] ?? '',
        'shop'      => $_POST['f_shop'] ?? '',
        'page'      => $_POST['page'] ?? '',
    ])));
}

// ── Filters ──
$q        = trim($_GET['q'] ?? '');
$featured = $_GET['featured'] ?? '';
$category = $_GET['category'] ?? '';
$shop     = $_GET['shop'] ?? '';
$page     = max(1, (int) ($_GET['page'] ?? 1));

if (!in_array($featured, ['0', '1'], true)) $featured = '';
$category  = $category === '' ? '' : (string) (int) $category;
$shop      = $shop === '' ? '' : (string) (int) $shop;

$where  = [];
$params = [];

if ($q !== '') {
    $where[] = "(p.name LIKE :q OR p.slug LIKE :q OR s.name LIKE :q)";
    $params[':q'] = '%' . $q . '%';
}
if ($featured !== '') {
    $where[] = 'p.is_featured = :feat';
    $params[':feat'] = (int) $featured;
}
if ($category !== '') {
    $where[] = 'p.category_id = :cat';
    $params[':cat'] = (int) $category;
}
if ($shop !== '') {
    $where[] = 'p.shop_id = :shop';
    $params[':shop'] = (int) $shop;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("SELECT COUNT(*) FROM products p
                       LEFT JOIN shops s ON s.id = p.shop_id" . $whereSql);
$stmt->execute($params);
$totalProducts = (int) $stmt->fetchColumn();

$totalPages = max(1, (int) ceil($totalProducts / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$listSql = "SELECT p.id, p.name, p.slug, p.price, p.old_price, p.quantity,
                   p.image, p.is_featured, p.created_at, p.category_id,
                   c.name AS category_name, s.id AS shop_id, s.name AS shop_name
              FROM products p
              LEFT JOIN categories c ON c.id = p.category_id
              LEFT JOIN shops s ON s.id = p.shop_id" . $whereSql . "
             ORDER BY p.is_featured DESC, p.created_at DESC, p.id DESC
             LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($listSql);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$shops      = $db->query("SELECT id, name FROM shops ORDER BY name ASC")->fetchAll();

function buildProductQuery($extra = []) {
    global $q, $featured, $category, $shop;
    return http_build_query(array_filter(array_merge([
        'q'        => $q,
        'featured' => $featured,
        'category' => $category,
        'shop'     => $shop,
    ], $extra)));
}

$pageTitle = 'Products';
$active    = 'products';

require __DIR__ . '/partials/header.php';
?>

<div class="admin-page-head">
  <div>
    <h1>Product Management</h1>
    <p><?= $totalProducts ?> product<?= $totalProducts === 1 ? '' : 's' ?>. Mark a product as <strong>featured</strong> to show it on the home page.</p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <form method="POST" action="<?= ADMIN_URL ?>/products.php">
      <input type="hidden" name="csrf" value="<?= getCSRF() ?>" />
      <input type="hidden" name="action" value="feature_all" />
      <button type="submit" class="admin-btn">Pin all to home</button>
    </form>
    <form method="POST" action="<?= ADMIN_URL ?>/products.php">
      <input type="hidden" name="csrf" value="<?= getCSRF() ?>" />
      <input type="hidden" name="action" value="unfeature_all" />
      <button type="submit" class="admin-btn admin-btn-ghost">Unpin all</button>
    </form>
    <a class="admin-btn admin-btn-ghost" href="<?= ADMIN_URL ?>/product-form.php">+ Add Product</a>
  </div>
</div>

<div class="admin-card">
  <form class="toolbar" method="GET" action="<?= ADMIN_URL ?>/products.php">
    <input type="search" name="q" placeholder="Search product name or shop…" value="<?= sanitize($q) ?>" />
    <select name="featured">
      <option value="">Any placement</option>
      <option value="1" <?= $featured === '1' ? 'selected' : '' ?>>Featured (home)</option>
      <option value="0" <?= $featured === '0' ? 'selected' : '' ?>>Not featured</option>
    </select>
    <select name="category">
      <option value="">All categories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int) $c['id'] ?>" <?= $category === (string) $c['id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="shop">
      <option value="">All shops</option>
      <?php foreach ($shops as $sopt): ?>
        <option value="<?= (int) $sopt['id'] ?>" <?= $shop === (string) $sopt['id'] ? 'selected' : '' ?>><?= sanitize($sopt['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="admin-btn">Filter</button>
    <?php if ($q !== '' || $featured !== '' || $category !== '' || $shop !== ''): ?>
      <a class="admin-btn admin-btn-ghost" href="<?= ADMIN_URL ?>/products.php">Clear</a>
    <?php endif; ?>
  </form>

  <?php if (empty($products)): ?>
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
      </svg>
      <div>No products match your filters.</div>
    </div>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Product</th>
            <th>Shop</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Home</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td><?= (int) $p['id'] ?></td>
              <td class="user-cell">
                <div style="display:flex;align-items:center;gap:10px;min-width:220px;">
                  <?php if (!empty($p['image'])): ?>
                    <img src="<?= UPLOADS_URL ?>/<?= sanitize($p['image']) ?>" alt=""
                         style="width:44px;height:44px;object-fit:cover;border-radius:8px;background:#f3f4f6;">
                  <?php else: ?>
                    <span style="width:44px;height:44px;border-radius:8px;background:#f3f4f6;display:inline-flex;align-items:center;justify-content:center;font-weight:800;color:#9ca3af;">?</span>
                  <?php endif; ?>
                  <div>
                    <div class="user-name"><?= sanitize($p['name']) ?></div>
                    <div class="user-email"><?= sanitize($p['category_name'] ?? 'Uncategorised') ?></div>
                  </div>
                </div>
              </td>
              <td><?= sanitize($p['shop_name'] ?? '—') ?></td>
              <td>
                <strong><?= formatPrice($p['price']) ?></strong>
                <?php if (!empty($p['old_price'])): ?>
                  <div class="user-email"><del><?= formatPrice($p['old_price']) ?></del></div>
                <?php endif; ?>
              </td>
              <td><?= (int) $p['quantity'] ?></td>
              <td>
                <?php if ((int) $p['is_featured'] === 1): ?>
                  <span class="badge badge-verified">On home</span>
                <?php else: ?>
                  <span class="badge badge-unverified">Hidden</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="row-actions">
                  <form method="POST" action="<?= ADMIN_URL ?>/products.php">
                    <input type="hidden" name="csrf" value="<?= getCSRF() ?>" />
                    <input type="hidden" name="action" value="feature" />
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                    <input type="hidden" name="q" value="<?= sanitize($q) ?>" />
                    <input type="hidden" name="f_featured" value="<?= sanitize($featured) ?>" />
                    <input type="hidden" name="f_category" value="<?= sanitize($category) ?>" />
                    <input type="hidden" name="f_shop" value="<?= sanitize($shop) ?>" />
                    <input type="hidden" name="page" value="<?= $page ?>" />
                    <button type="submit" class="admin-btn admin-btn-sm <?= (int) $p['is_featured'] === 1 ? '' : 'admin-btn-ghost' ?>">
                      <?= (int) $p['is_featured'] === 1 ? 'Unpin' : 'Pin to home' ?>
                    </button>
                  </form>
                  <a class="admin-btn admin-btn-sm admin-btn-ghost" href="<?= ADMIN_URL ?>/product-form.php?id=<?= (int) $p['id'] ?>">Edit</a>
                  <form method="POST" action="<?= ADMIN_URL ?>/products.php"
                        onsubmit="return confirm('Permanently delete &quot;<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>&quot; and its images?')">
                    <input type="hidden" name="csrf" value="<?= getCSRF() ?>" />
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                    <input type="hidden" name="q" value="<?= sanitize($q) ?>" />
                    <input type="hidden" name="f_featured" value="<?= sanitize($featured) ?>" />
                    <input type="hidden" name="f_category" value="<?= sanitize($category) ?>" />
                    <input type="hidden" name="f_shop" value="<?= sanitize($shop) ?>" />
                    <input type="hidden" name="page" value="<?= $page ?>" />
                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="<?= ADMIN_URL ?>/products.php?<?= buildProductQuery(['page' => $page - 1]) ?>">← Prev</a>
        <?php endif; ?>
        <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
        ?>
          <?php if ($i === $page): ?>
            <span class="current"><?= $i ?></span>
          <?php else: ?>
            <a href="<?= ADMIN_URL ?>/products.php?<?= buildProductQuery(['page' => $i]) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="<?= ADMIN_URL ?>/products.php?<?= buildProductQuery(['page' => $page + 1]) ?>">Next →</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>