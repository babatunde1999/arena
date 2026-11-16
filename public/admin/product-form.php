<?php
/**
 * Admin product form - add or edit any product in the market.
 * Admins can pick the owning shop and pin the product to the home page.
 * Arena Market - E-Commerce Platform
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/../../includes/shop.php';
require_once __DIR__ . '/../../includes/categories.php';
requireAdmin();

$db = getDB();
ensureCategories($db);

$editingId = (int) ($_GET['id'] ?? 0);
$editing   = false;
$product   = null;
$gallery   = [];

if ($editingId > 0) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $editingId]);
    $product = $stmt->fetch();

    if (!$product) {
        setFlash('error', 'Product not found.');
        redirect(ADMIN_URL . '/products.php');
    }
    $editing = true;

    $stmt = $db->prepare(
        "SELECT id, image, sort_order FROM product_images
         WHERE product_id = :pid ORDER BY sort_order ASC, id ASC"
    );
    $stmt->execute([':pid' => $editingId]);
    $gallery = $stmt->fetchAll();
}

$old = $product ? [
    'name'         => $product['name'],
    'shop_id'      => $product['shop_id'],
    'category_id'  => $product['category_id'],
    'price'        => $product['price'],
    'old_price'    => $product['old_price'],
    'quantity'     => $product['quantity'],
    'description'  => $product['description'],
    'image'        => $product['image'],
    'is_featured'  => $product['is_featured'],
    'slug'         => $product['slug'],
] : [
    'name'         => '',
    'shop_id'      => '',
    'category_id'  => '',
    'price'        => '',
    'old_price'    => '',
    'quantity'     => '0',
    'description'  => '',
    'image'        => '',
    'is_featured'  => 0,
    'slug'         => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['_token'] ?? '')) {
        $errors['general'] = 'Invalid security token. Please try again.';
    } else {
        $old['name']         = trim($_POST['name'] ?? '');
        $old['shop_id']      = (int) ($_POST['shop_id'] ?? 0);
        $old['category_id']  = (int) ($_POST['category_id'] ?? 0);
        $old['price']        = trim($_POST['price'] ?? '');
        $old['old_price']    = trim($_POST['old_price'] ?? '');
        $old['quantity']     = trim($_POST['quantity'] ?? '0');
        $old['description']  = trim($_POST['description'] ?? '');
        $old['is_featured']  = isset($_POST['is_featured']) ? 1 : 0;

        // ── Validation ──
        if ($old['name'] === '') {
            $errors['name'] = 'Product name is required.';
        } elseif (strlen($old['name']) > 200) {
            $errors['name'] = 'Product name must be 200 characters or fewer.';
        }

        if ($old['shop_id'] < 1) {
            $errors['shop_id'] = 'Please choose which shop this product belongs to.';
        }

        if ($old['price'] === '' || !is_numeric($old['price']) || (float) $old['price'] < 0) {
            $errors['price'] = 'Enter a valid price.';
        }

        if ($old['old_price'] !== '' && (!is_numeric($old['old_price']) || (float) $old['old_price'] < 0)) {
            $errors['old_price'] = 'Enter a valid old price or leave it empty.';
        }

        if ($old['quantity'] === '' || !ctype_digit($old['quantity'])) {
            $errors['quantity'] = 'Enter a valid quantity (whole number).';
        }

        // ── Image uploads (only after field validation passes) ──
        $newImages = [];

        if (empty($errors)) {
            $result = saveImageUploads(reindexPostFiles($_FILES['images'] ?? []));
            $newImages = $result['images'];
            if (!empty($result['errors'])) {
                $errors['images'] = $result['errors'];
            }
        }

        // ── Save ──
        if (empty($errors)) {
            $price     = round((float) $old['price'], 2);
            $oldPrice  = $old['old_price'] !== '' ? round((float) $old['old_price'], 2) : null;
            $quantity  = (int) $old['quantity'];
            $category  = $old['category_id'] > 0 ? $old['category_id'] : null;
            $featured  = $old['is_featured'];
            $filesToDelete = [];

            try {
                if ($editing) {
                    $removedIds = array_filter(array_map('intval', $_POST['remove_gallery'] ?? []));
                    $kept = [];
                    foreach ($gallery as $row) {
                        if (in_array((int) $row['id'], $removedIds, true)) {
                            $filesToDelete[] = $row['image'];
                        } else {
                            $kept[] = $row;
                        }
                    }

                    $image = $product['image'];
                    $coverChanged = false;

                    if (!empty($newImages)) {
                        $coverChanged   = !empty($product['image']);
                        $image          = array_shift($newImages);
                    } elseif (isset($_POST['remove_cover'])) {
                        $coverChanged = !empty($product['image']);
                        $promoted = null;
                        if (!empty($kept)) {
                            $promoted = array_shift($kept);
                            $image    = $promoted['image'];
                        } else {
                            $image = null;
                        }
                    }

                    if ($coverChanged && !empty($product['image'])) {
                        $filesToDelete[] = $product['image'];
                    }

                    $stmt = $db->prepare(
                        "UPDATE products
                         SET shop_id = :shop_id, name = :name, category_id = :category,
                             price = :price, old_price = :old_price, quantity = :quantity,
                             description = :description, image = :image, is_featured = :featured
                         WHERE id = :id"
                    );
                    $stmt->execute([
                        ':shop_id'    => $old['shop_id'],
                        ':name'       => $old['name'],
                        ':category'   => $category,
                        ':price'      => $price,
                        ':old_price'  => $oldPrice,
                        ':quantity'   => $quantity,
                        ':description'=> $old['description'],
                        ':image'      => $image,
                        ':featured'   => $featured,
                        ':id'         => $editingId,
                    ]);

                    foreach ($gallery as $row) {
                        if (in_array((int) $row['id'], $removedIds, true)) {
                            $db->prepare("DELETE FROM product_images WHERE id = :id")
                               ->execute([':id' => $row['id']]);
                        }
                    }

                    $ord = 0;
                    $upd = $db->prepare("UPDATE product_images SET sort_order = :ord WHERE id = :id");
                    foreach ($kept as $stay) {
                        $upd->execute([':ord' => $ord++, ':id' => $stay['id']]);
                    }

                    if (!empty($newImages)) {
                        $ins = $db->prepare(
                            "INSERT INTO product_images (product_id, image, sort_order)
                             VALUES (:pid, :img, :ord)"
                        );
                        foreach ($newImages as $img) {
                            $ins->execute([':pid' => $editingId, ':img' => $img, ':ord' => $ord++]);
                        }
                    }

                    deleteImageFiles(array_unique($filesToDelete));
                    setFlash('success', 'Product updated successfully.');
                } else {
                    $image = !empty($newImages) ? array_shift($newImages) : null;

                    $stmt = $db->prepare(
                        "INSERT INTO products
                         (shop_id, name, slug, description, price, old_price, quantity,
                          category_id, image, is_featured, created_at)
                         VALUES
                         (:shop_id, :name, :slug, :description, :price, :old_price, :quantity,
                          :category, :image, :featured, NOW())"
                    );
                    $stmt->execute([
                        ':shop_id'    => $old['shop_id'],
                        ':name'       => $old['name'],
                        ':slug'       => uniqueProductSlug($db, $old['name']),
                        ':description'=> $old['description'],
                        ':price'      => $price,
                        ':old_price'  => $oldPrice,
                        ':quantity'   => $quantity,
                        ':category'   => $category,
                        ':image'      => $image,
                        ':featured'   => $featured,
                    ]);

                    $pid = (int) $db->lastInsertId();

                    if (!empty($newImages)) {
                        $ins = $db->prepare(
                            "INSERT INTO product_images (product_id, image, sort_order)
                             VALUES (:pid, :img, :ord)"
                        );
                        foreach ($newImages as $i => $img) {
                            $ins->execute([':pid' => $pid, ':img' => $img, ':ord' => $i]);
                        }
                    }

                    setFlash('success', 'Product added successfully.');
                }

                redirect(ADMIN_URL . '/products.php');
            } catch (PDOException $e) {
                error_log("Admin product save error: " . $e->getMessage());
                if (!empty($newImages)) {
                    deleteImageFiles($newImages);
                }
                $errors['general'] = 'Something went wrong while saving. Please try again.';
            }
        } else {
            if (!empty($newImages)) {
                deleteImageFiles($newImages);
            }
        }
    }
}

$shops = $db->query(
    "SELECT s.id, s.name, u.username
       FROM shops s
       LEFT JOIN users u ON u.id = s.user_id
      ORDER BY s.name ASC"
)->fetchAll();
$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$pageTitle = $editing ? 'Edit Product' : 'Add Product';
$active    = 'products';

require __DIR__ . '/partials/header.php';
?>

<div class="admin-page-head">
  <div>
    <h1><?= $pageTitle ?></h1>
    <p><?= $editing ? 'Update the details of <strong>' . sanitize($product['name']) . '</strong>.' : 'Add a new product and optionally show it on the home page.' ?></p>
  </div>
  <a class="admin-btn admin-btn-ghost" href="<?= ADMIN_URL ?>/products.php">&larr; Back to Products</a>
</div>

<?php if (!empty($errors['general'])): ?>
  <div class="alert alert-error"><?= sanitize($errors['general']) ?></div>
<?php endif; ?>

<div class="admin-card">
  <form method="POST" action="<?= ADMIN_URL ?>/product-form.php<?= $editing ? '?id=' . (int) $editingId : '' ?>"
        enctype="multipart/form-data" novalidate>
    <input type="hidden" name="_token" value="<?= getCSRF() ?>">

    <div class="field-group" style="margin-bottom:16px;">
      <label for="shop_id" style="display:block;font-weight:700;font-size:0.85rem;margin-bottom:6px;">Shop <span style="color:#dc2626;">*</span></label>
      <select id="shop_id" name="shop_id" style="width:100%;padding:11px 12px;border:1px solid var(--admin-border);border-radius:9px;font-size:0.95rem;background:#fff;">
        <option value="">Choose the shop this product belongs to…</option>
        <?php foreach ($shops as $sopt): ?>
          <option value="<?= (int) $sopt['id'] ?>"
            <?= (int) $old['shop_id'] === (int) $sopt['id'] ? 'selected' : '' ?>>
            <?= sanitize($sopt['name']) ?><?= $sopt['username'] ? ' (@' . sanitize($sopt['username']) . ')' : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (!empty($errors['shop_id'])): ?>
        <span style="color:#dc2626;font-size:0.8rem;"><?= sanitize($errors['shop_id']) ?></span>
      <?php endif; ?>
    </div>

    <div class="field-group" style="margin-bottom:16px;">
      <label for="name" style="display:block;font-weight:700;font-size:0.85rem;margin-bottom:6px;">Product Name <span style="color:#dc2626;">*</span></label>
      <input id="name" type="text" name="name" placeholder="e.g. Men Leather Sneakers"
             value="<?= sanitize($old['name']) ?>"
             style="width:100%;padding:11px 12px;border:1px solid var(--admin-border);border-radius:9px;font-size:0.95rem;">
      <?php if (!empty($errors['name'])): ?>
        <span style="color:#dc2626;font-size:0.8rem;"><?= sanitize($errors['name']) ?></span>
      <?php endif; ?>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:16px;">
      <div class="field-group">
        <label for="price" style="display:block;font-weight:700;font-size:0.85rem;margin-bottom:6px;">Price (₦) <span style="color:#dc2626;">*</span></label>
        <input id="price" type="number" step="0.01" min="0" name="price" placeholder="0.00" value="<?= sanitize($old['price']) ?>"
               style="width:100%;padding:11px 12px;border:1px solid var(--admin-border);border-radius:9px;font-size:0.95rem;">
        <?php if (!empty($errors['price'])): ?>
          <span style="color:#dc2626;font-size:0.8rem;"><?= sanitize($errors['price']) ?></span>
        <?php endif; ?>
      </div>
      <div class="field-group">
        <label for="old_price" style="display:block;font-weight:700;font-size:0.85rem;margin-bottom:6px;">Old Price (₦) <span style="color:#6b7280;">(optional)</span></label>
        <input id="old_price" type="number" step="0.01" min="0" name="old_price" placeholder="0.00" value="<?= sanitize($old['old_price']) ?>"
               style="width:100%;padding:11px 12px;border:1px solid var(--admin-border);border-radius:9px;font-size:0.95rem;">
        <?php if (!empty($errors['old_price'])): ?>
          <span style="color:#dc2626;font-size:0.8rem;"><?= sanitize($errors['old_price']) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:16px;">
      <div class="field-group">
        <label for="quantity" style="display:block;font-weight:700;font-size:0.85rem;margin-bottom:6px;">Quantity <span style="color:#dc2626;">*</span></label>
        <input id="quantity" type="number" min="0" name="quantity" placeholder="0" value="<?= sanitize($old['quantity']) ?>"
               style="width:100%;padding:11px 12px;border:1px solid var(--admin-border);border-radius:9px;font-size:0.95rem;">
        <?php if (!empty($errors['quantity'])): ?>
          <span style="color:#dc2626;font-size:0.8rem;"><?= sanitize($errors['quantity']) ?></span>
        <?php endif; ?>
      </div>
      <div class="field-group">
        <label for="category_id" style="display:block;font-weight:700;font-size:0.85rem;margin-bottom:6px;">Category</label>
        <select id="category_id" name="category_id" style="width:100%;padding:11px 12px;border:1px solid var(--admin-border);border-radius:9px;font-size:0.95rem;background:#fff;">
          <option value="">Select a category…</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>"
              <?= (int) $old['category_id'] === (int) $c['id'] ? 'selected' : '' ?>>
              <?= sanitize($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="field-group" style="margin-bottom:16px;">
      <label for="description" style="display:block;font-weight:700;font-size:0.85rem;margin-bottom:6px;">Description</label>
      <textarea id="description" name="description" rows="4" placeholder="Describe this product…"
                style="width:100%;padding:11px 12px;border:1px solid var(--admin-border);border-radius:9px;font-size:0.95rem;font-family:inherit;"><?= sanitize($old['description']) ?></textarea>
    </div>

    <div class="field-group" style="margin-bottom:16px;">
      <label for="image-input" style="display:block;font-weight:700;font-size:0.85rem;margin-bottom:6px;">Product Images <span style="color:#6b7280;">(optional, up to <?= PRODUCT_MAX_IMAGES ?> images, max 5MB each – JPG/PNG/WEBP/GIF)</span></label>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
        <div style="width:90px;height:90px;border-radius:10px;overflow:hidden;background:#fff;display:flex;align-items:center;justify-content:center;">
          <?php if ($editing && !empty($product['image'])): ?>
            <img id="img-preview" src="<?= UPLOADS_URL ?>/<?= sanitize($product['image']) ?>" alt="Cover" style="width:100%;height:100%;object-fit:contain;padding:4px;">
          <?php else: ?>
            <img id="img-preview" style="display:none;width:100%;height:100%;object-fit:contain;padding:4px;" alt="Preview">
            <span id="img-placeholder" style="font-weight:800;color:#9ca3af;">IMG</span>
          <?php endif; ?>
        </div>
        <input class="form-input" type="file" name="images[]" id="image-input" multiple
               accept="image/jpeg,image/png,image/webp,image/gif"
               style="align-self:end;">
      </div>
      <?php if (!empty($errors['images'])): ?>
        <span style="color:#dc2626;font-size:0.8rem;"><?= sanitize(implode(' ', (array) $errors['images'])) ?></span>
      <?php endif; ?>

      <?php if ($editing): ?>
        <?php if (!empty($product['image'])): ?>
          <label style="display:flex;align-items:center;gap:8px;margin:8px 0;font-size:0.85rem;">
            <input type="checkbox" name="remove_cover"> Remove current cover image
          </label>
        <?php endif; ?>

        <?php if (!empty($gallery)): ?>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;">
            <?php foreach ($gallery as $g): ?>
              <div style="position:relative;">
                <img src="<?= UPLOADS_URL ?>/<?= sanitize($g['image']) ?>" alt="Product image"
                     style="width:70px;height:70px;object-fit:contain;padding:2px;background:#fff;border-radius:8px;">
                <label style="position:absolute;bottom:2px;right:2px;background:#000;color:#fff;font-size:10px;padding:2px 5px;border-radius:5px;cursor:pointer;">
                  <input type="checkbox" name="remove_gallery[]" value="<?= (int) $g['id'] ?>"> Remove
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <label style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding:12px 14px;border:1px solid var(--admin-border);border-radius:10px;background:#f8fafc;cursor:pointer;">
      <input type="checkbox" name="is_featured" value="1" <?= (int) $old['is_featured'] === 1 ? 'checked' : '' ?>>
      <span>
        <strong>Show on home page</strong>
        <span style="display:block;font-size:0.8rem;color:var(--admin-muted);">Featured products appear in the &quot;Featured Products&quot; section of the store homepage.</span>
      </span>
    </label>

    <div style="display:flex;gap:10px;">
      <button type="submit" class="admin-btn"><?= $editing ? 'Update Product' : 'Add Product' ?></button>
      <a class="admin-btn admin-btn-ghost" href="<?= ADMIN_URL ?>/products.php">Cancel</a>
    </div>
  </form>
</div>

<script>
  (function () {
    var input = document.getElementById('image-input');
    var preview = document.getElementById('img-preview');
    var placeholder = document.getElementById('img-placeholder');
    if (!input || !preview) return;
    input.addEventListener('change', function () {
      var file = this.files && this.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
      };
      reader.readAsDataURL(file);
    });
  })();
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>