<?php
/**
 * Product details — Arena Market
 *
 * Single source of truth for a product's own page. Deal cards,
 * wishlist cards and cart rows all link here.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/store.php';

$db     = getDB();
$flash  = getFlash();
$id     = (int) ($_GET['id'] ?? 0);
$product = storeProduct($id, $db);

$percent = 0;
$stock   = ['class' => 'out', 'label' => 'Out of stock'];
$images  = [];
$inWish  = false;

if ($product !== null) {
    $percent = dealPercent($product['price'], $product['old_price'] ?? 0);
    $qty     = (int) $product['quantity'];
    if ($qty <= 0) {
        $stock = ['class' => 'out', 'label' => 'Out of stock'];
    } elseif ($qty <= 5) {
        $stock = ['class' => 'low', 'label' => 'Only ' . $qty . ' left in stock'];
    } else {
        $stock = ['class' => 'ok', 'label' => $qty . ' in stock'];
    }

    if (!empty($product['image']) && is_file(UPLOADS_PATH . '/' . $product['image'])) {
        $images[] = $product['image'];
    }

    try {
        $stmt = $db->prepare(
            "SELECT image FROM product_images
              WHERE product_id = :id
              ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute([':id' => $id]);
        foreach ($stmt->fetchAll() as $row) {
            if (!empty($row['image']) && is_file(UPLOADS_PATH . '/' . $row['image'])) {
                $images[] = $row['image'];
            }
        }
    } catch (PDOException $e) {
        error_log('product gallery error: ' . $e->getMessage());
    }

    $images = array_values(array_unique($images));
    $inWish = storeInWishlist($id);
}

if ($product === null) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $product ? sanitize($product['name']) . ' – Arena Market' : 'Product not found – Arena Market' ?></title>
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
    <?php if ($product): ?>
      <?php if (!empty($product['category_name'])): ?>
        <a href="shop.php?cat=<?= urlencode((string) $product['category_slug']) ?>"><?= sanitize($product['category_name']) ?></a>
        <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
      <?php endif; ?>
      <span><?= sanitize(mb_strimwidth($product['name'], 0, 42, '…')) ?></span>
    <?php else: ?>
      <span>Product not found</span>
    <?php endif; ?>
  </div>
</div>

<main class="store-page">

  <?php if (!empty($flash)): ?>
    <div class="flash-banner flash-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div>
  <?php endif; ?>

  <?php if ($product === null): ?>

    <div class="empty-store">
      <h2>Product not found</h2>
      <p>This product may have been removed, or the link is incorrect.</p>
      <a class="btn-store-primary" href="deals.php">Browse today's deals</a>
    </div>

  <?php else: ?>

    <div class="product-detail">

      <!-- ══ GALLERY ══ -->
      <div class="pd-gallery">
        <div class="pd-main">
          <?php if (!empty($images)): ?>
            <img
              data-gallery-main
              src="<?= UPLOADS_URL ?>/<?= sanitize($images[0]) ?>"
              alt="<?= sanitize($product['name']) ?>"
            />
          <?php else: ?>
            <div class="deal-placeholder"><?= sanitize(mb_substr($product['name'], 0, 1)) ?></div>
          <?php endif; ?>
        </div>

        <?php if (count($images) > 1): ?>
          <div class="pd-thumbs">
            <?php foreach ($images as $index => $image): ?>
              <button
                type="button"
                class="pd-thumb<?= $index === 0 ? ' is-active' : '' ?>"
                data-gallery-thumb="<?= UPLOADS_URL ?>/<?= sanitize($image) ?>"
                data-gallery-alt="<?= sanitize($product['name']) ?> image <?= $index + 1 ?>"
                aria-label="View image <?= $index + 1 ?>"
              >
                <img src="<?= UPLOADS_URL ?>/<?= sanitize($image) ?>" alt="" loading="lazy" />
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- ══ INFO ══ -->
      <div class="pd-info">
        <?php if (!empty($product['category_name'])): ?>
          <a class="pd-cat" href="shop.php?cat=<?= urlencode((string) $product['category_slug']) ?>">
            <?= sanitize($product['category_name']) ?>
          </a>
        <?php endif; ?>

        <h1><?= sanitize($product['name']) ?></h1>

        <?php if ((float) $product['rating'] > 0): ?>
          <div class="pd-rating">
            <span aria-hidden="true">★</span>
            <?= number_format((float) $product['rating'], 1) ?> out of 5
          </div>
        <?php endif; ?>

        <div class="pd-price-row">
          <span class="pd-price"><?= formatPrice($product['price']) ?></span>
          <?php if ($percent > 0): ?>
            <span class="pd-old"><?= formatPrice($product['old_price']) ?></span>
            <span class="pd-badge">-<?= $percent ?>%</span>
            <span class="pd-save">You save <?= formatPrice((float) $product['old_price'] - (float) $product['price']) ?></span>
          <?php endif; ?>
        </div>

        <p class="pd-desc">
          <?= !empty($product['description'])
                ? sanitize($product['description'])
                : 'This vendor has not added a description for this product yet.' ?>
        </p>

        <span class="pd-stock <?= $stock['class'] ?>"><?= sanitize($stock['label']) ?></span>

        <div class="pd-meta">
          <?php if (!empty($product['category_name'])): ?>
            <span>Category: <strong><?= sanitize($product['category_name']) ?></strong></span>
          <?php endif; ?>
          <?php if (!empty($product['shop_name'])): ?>
            <span>Vendor: <strong><?= sanitize($product['shop_name']) ?></strong></span>
          <?php endif; ?>
          <span>Product ID: <strong>#<?= (int) $product['id'] ?></strong></span>
        </div>

        <div class="pd-buy">
          <form method="post" action="cart-action.php" data-cart-form>
            <input type="hidden" name="_token" value="<?= getCSRF() ?>" />
            <input type="hidden" name="action" value="cart_add" />
            <input type="hidden" name="id" value="<?= (int) $product['id'] ?>" />
            <input type="hidden" name="return" value="product.php?id=<?= (int) $product['id'] ?>" />

            <div class="qty-control">
              <button type="button" data-qty-step="pd-qty" data-step="down" aria-label="Decrease quantity">−</button>
              <input
                id="pd-qty"
                type="number"
                name="qty"
                value="1"
                min="1"
                max="<?= max(1, (int) $product['quantity']) ?>"
                aria-label="Quantity"
                <?= (int) $product['quantity'] <= 0 ? 'disabled' : '' ?>
              />
              <button type="button" data-qty-step="pd-qty" data-step="up" aria-label="Increase quantity">+</button>
            </div>

            <button type="submit" class="pd-add"<?= (int) $product['quantity'] <= 0 ? ' disabled' : '' ?>>
              <?= (int) $product['quantity'] <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
            </button>
          </form>

          <form method="post" action="cart-action.php" data-wishlist-form>
            <input type="hidden" name="_token" value="<?= getCSRF() ?>" />
            <input type="hidden" name="action" value="wishlist_toggle" />
            <input type="hidden" name="id" value="<?= (int) $product['id'] ?>" />
            <input type="hidden" name="return" value="product.php?id=<?= (int) $product['id'] ?>" />
            <button
              type="submit"
              class="pd-wish<?= $inWish ? ' is-active' : '' ?>"
              aria-pressed="<?= $inWish ? 'true' : 'false' ?>"
              aria-label="<?= $inWish ? 'Remove from wishlist' : 'Save to wishlist' ?>"
              title="<?= $inWish ? 'Remove from wishlist' : 'Save to wishlist' ?>"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
              </svg>
            </button>
          </form>
        </div>

        <?php if (!empty($product['shop_name'])): ?>
          <div class="pd-shop">
            <div class="pd-shop-avatar">
              <?php if (!empty($product['shop_logo']) && is_file(UPLOADS_PATH . '/' . $product['shop_logo'])): ?>
                <img src="<?= UPLOADS_URL ?>/<?= sanitize($product['shop_logo']) ?>" alt="<?= sanitize($product['shop_name']) ?>" />
              <?php else: ?>
                <?= sanitize(mb_substr($product['shop_name'], 0, 1)) ?>
              <?php endif; ?>
            </div>
            <div>
              <div class="pd-shop-name">
                <?= sanitize($product['shop_name']) ?>
                <?php if (!empty($product['shop_verified'])): ?>
                  <span class="deal-rating" style="display:inline-flex;margin-left:4px;">✓ Verified</span>
                <?php endif; ?>
              </div>
              <div class="pd-shop-meta">
                <?= !empty($product['shop_location']) ? sanitize($product['shop_location']) : 'Arena Market vendor' ?>
              </div>
            </div>
            <a class="pd-shop-link" href="shop.php">Browse shops →</a>
          </div>
        <?php endif; ?>
      </div>
    </div>

  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="js/site.js"></script>
<script src="js/store.js"></script>
</body>
</html>
