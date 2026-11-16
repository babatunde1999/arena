<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/shop.php';
require_once __DIR__ . '/../includes/categories.php';

requireLogin();
requireVendor();

$db   = getDB();
$shop = getOrCreateShop($db);
ensureCategories($db);

$editingId = (int) ($_GET['id'] ?? 0);
$editing   = false;
$product   = null;
$gallery   = [];

if ($editingId > 0) {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = :id AND shop_id = :shop_id LIMIT 1");
    $stmt->execute([':id' => $editingId, ':shop_id' => $shop['id']]);
    $product = $stmt->fetch();

    if (!$product) {
        setFlash('error', 'Product not found or you do not own it.');
        redirect(SITE_URL . '/products.php');
    }
    $editing = true;

    $stmt = $db->prepare(
        "SELECT id, image, sort_order FROM product_images
         WHERE product_id = :pid ORDER BY sort_order ASC, id ASC"
    );
    $stmt->execute([':pid' => $editingId]);
    $gallery = $stmt->fetchAll();
}

$old  = $product ?? [
    'name'        => '',
    'category_id' => '',
    'price'       => '',
    'old_price'   => '',
    'quantity'    => '0',
    'description' => '',
    'image'       => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['_token'] ?? '')) {
        $errors['general'] = 'Invalid security token. Please try again.';
    } else {
        $old['name']         = trim($_POST['name'] ?? '');
        $old['category_id']  = (int) ($_POST['category_id'] ?? 0);
        $old['price']        = trim($_POST['price'] ?? '');
        $old['old_price']    = trim($_POST['old_price'] ?? '');
        $old['quantity']     = trim($_POST['quantity'] ?? '0');
        $old['description']  = trim($_POST['description'] ?? '');

        // ── Validation ──
        if ($old['name'] === '') {
            $errors['name'] = 'Product name is required.';
        } elseif (strlen($old['name']) > 200) {
            $errors['name'] = 'Product name must be 200 characters or fewer.';
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
                         SET name = :name, category_id = :category, price = :price,
                             old_price = :old_price, quantity = :quantity,
                             description = :description, image = :image
                         WHERE id = :id AND shop_id = :shop_id"
                    );
                    $stmt->execute([
                        ':name'        => $old['name'],
                        ':category'    => $category,
                        ':price'       => $price,
                        ':old_price'   => $oldPrice,
                        ':quantity'    => $quantity,
                        ':description' => $old['description'],
                        ':image'       => $image,
                        ':id'          => $editingId,
                        ':shop_id'     => $shop['id'],
                    ]);

                    // Remove flagged gallery rows from the database
                    foreach ($gallery as $row) {
                        if (in_array((int) $row['id'], $removedIds, true)) {
                            $db->prepare("DELETE FROM product_images WHERE id = :id")
                               ->execute([':id' => $row['id']]);
                        }
                    }

                    // Re-sort + append new gallery images
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
                          :category, :image, 0, NOW())"
                    );
                    $stmt->execute([
                        ':shop_id'    => $shop['id'],
                        ':name'       => $old['name'],
                        ':slug'       => uniqueProductSlug($db, $old['name']),
                        ':description'=> $old['description'],
                        ':price'      => $price,
                        ':old_price'  => $oldPrice,
                        ':quantity'   => $quantity,
                        ':category'   => $category,
                        ':image'      => $image,
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

                redirect(SITE_URL . '/products.php');
            } catch (PDOException $e) {
                error_log("Product save error: " . $e->getMessage());
                if (!empty($newImages)) {
                    deleteImageFiles($newImages);
                }
                $errors['general'] = 'Something went wrong while saving. Please try again.';
            }
        } else {
            // Field validation failed before saving – remove any files already moved.
            if (!empty($newImages)) {
                deleteImageFiles($newImages);
            }
        }
    }
}

// ── Category list for the dropdown ──
$stmt = $db->prepare("SELECT id, name FROM categories ORDER BY name ASC");
$stmt->execute();
$categories = $stmt->fetchAll();

$pageTitle = $editing ? 'Edit Product' : 'Add Product';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $pageTitle ?> – Arena Market</title>
  <link rel="stylesheet" href="css/default.css">
  <link rel="stylesheet" href="css/products.css?v=2">
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

    <div class="page-head">
      <div>
        <h1><?= $pageTitle ?></h1>
        <p class="sub"><?= $editing ? 'Update the details of <strong>' . sanitize($product['name']) . '</strong>.' : 'Fill in the details below to list a new product in your shop.' ?></p>
      </div>
      <a class="btn-secondary" href="products.php">&larr; Back to Products</a>
    </div>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-error" style="margin-bottom:18px;"><?= sanitize($errors['general']) ?></div>
    <?php endif; ?>

    <div class="form-card">
      <form method="POST" action="product-form.php<?= $editing ? '?id=' . (int) $editingId : '' ?>"
            enctype="multipart/form-data" novalidate>
        <input type="hidden" name="_token" value="<?= getCSRF() ?>">

        <div class="field-group">
          <label>Product Name <span class="req">*</span></label>
          <input class="form-input" type="text" name="name" placeholder="e.g. Men Leather Sneakers"
                 value="<?= sanitize($old['name']) ?>">
          <?php if (!empty($errors['name'])): ?><span class="hint" style="color:#e74c3c;"><?= sanitize($errors['name']) ?></span><?php endif; ?>
        </div>

        <div class="form-row">
          <div class="field-group">
            <label>Price (₦) <span class="req">*</span></label>
            <input class="form-input" type="number" step="0.01" min="0" name="price"
                   placeholder="0.00" value="<?= sanitize($old['price']) ?>">
            <?php if (!empty($errors['price'])): ?><span class="hint" style="color:#e74c3c;"><?= sanitize($errors['price']) ?></span><?php endif; ?>
          </div>
          <div class="field-group">
            <label>Old Price (₦) <span class="hint">(optional)</span></label>
            <input class="form-input" type="number" step="0.01" min="0" name="old_price"
                   placeholder="0.00" value="<?= sanitize($old['old_price']) ?>">
            <?php if (!empty($errors['old_price'])): ?><span class="hint" style="color:#e74c3c;"><?= sanitize($errors['old_price']) ?></span><?php endif; ?>
          </div>
        </div>

        <div class="form-row">
          <div class="field-group">
            <label>Quantity <span class="req">*</span></label>
            <input class="form-input" type="number" min="0" name="quantity"
                   placeholder="0" value="<?= sanitize($old['quantity']) ?>">
            <?php if (!empty($errors['quantity'])): ?><span class="hint" style="color:#e74c3c;"><?= sanitize($errors['quantity']) ?></span><?php endif; ?>
          </div>
          <div class="field-group">
            <label>Category</label>
            <select class="form-select" name="category_id">
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

        <div class="field-group">
          <label>Description</label>
          <textarea class="form-textarea" name="description"
                    placeholder="Describe your product…"><?= sanitize($old['description']) ?></textarea>
        </div>

        <div class="field-group">
          <label>Product Images <span class="hint">(optional, up to <?= PRODUCT_MAX_IMAGES ?> images, max 5MB each – JPG/PNG/WEBP/GIF)</span></label>
          <div class="upload-preview">
            <?php if ($editing && !empty($product['image'])): ?>
              <img id="img-preview" src="<?= SITE_URL ?>/uploads/<?= sanitize($product['image']) ?>" alt="Cover image">
            <?php else: ?>
              <img id="img-preview" style="display:none;" alt="Preview">
              <div class="ph" id="img-placeholder"><?= $editing ? sanitize(mb_substr($product['name'] ?? '', 0, 1)) : 'IMG' ?></div>
            <?php endif; ?>
          </div>
          <input class="form-input" type="file" name="images[]" id="image-input" multiple
                 accept="image/jpeg,image/png,image/webp,image/gif">
          <?php if (!empty($errors['images'])): ?>
            <span class="hint" style="color:#e74c3c;"><?= sanitize(implode(' ', (array) $errors['images'])) ?></span>
          <?php endif; ?>

          <?php if ($editing): ?>
            <?php if (!empty($product['image'])): ?>
              <label class="checkbox-field" style="margin-top:12px;">
                <input type="checkbox" name="remove_cover">
                <span>Remove current cover image</span>
              </label>
            <?php endif; ?>

            <?php if (!empty($gallery)): ?>
              <div class="gallery-list">
                <?php foreach ($gallery as $g): ?>
                  <div class="gallery-item">
                    <img src="<?= SITE_URL ?>/uploads/<?= sanitize($g['image']) ?>" alt="Product image">
                    <label class="gallery-remove">
                      <input type="checkbox" name="remove_gallery[]" value="<?= (int) $g['id'] ?>">
                      <span>Remove</span>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-submit"><?= $editing ? 'Update Product' : 'Add Product' ?></button>
          <a class="btn-secondary" href="products.php">Cancel</a>
        </div>
      </form>
    </div>

  </div>

  <script>
    // Live image preview (first selected file becomes the cover)
    var input = document.getElementById('image-input');
    var preview = document.getElementById('img-preview');
    var placeholder = document.getElementById('img-placeholder');

    if (input && preview) {
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
    }
  </script>

</body>
</html>