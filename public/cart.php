<?php
/**
 * Shopping cart — Arena Market
 *
 * Reads the shared session cart via storeCartDetails(), which also
 * re-validates every line against live stock and price.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/store.php';

$db    = getDB();
$flash = getFlash();
$cart  = storeCartDetails($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Your Cart – Arena Market</title>
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
    <span>Cart</span>
  </div>
</div>

<main class="store-page">

  <?php if (!empty($flash)): ?>
    <div class="flash-banner flash-<?= sanitize($flash['type']) ?>"><?= sanitize($flash['message']) ?></div>
  <?php endif; ?>

  <div class="store-head">
    <h1>Your Cart</h1>
    <p><?= (int) $cart['count'] ?> item<?= $cart['count'] === 1 ? '' : 's' ?> in your cart</p>
  </div>

  <?php if (empty($cart['items'])): ?>

    <div class="empty-store">
      <h2>Your cart is empty</h2>
      <p>Browse today's deals or the marketplace to find something you love.</p>
      <a class="btn-store-primary" href="deals.php">Shop today's deals</a>
    </div>

  <?php else: ?>

    <div class="cart-layout">
      <div class="cart-list">
        <?php foreach ($cart['items'] as $item): ?>
          <?php
          $hasImage  = !empty($item['image']) && is_file(UPLOADS_PATH . '/' . $item['image']);
          $productId = (int) $item['id'];
          ?>
          <div class="cart-row">
            <a class="cart-thumb" href="<?= productUrl($item) ?>">
              <?php if ($hasImage): ?>
                <img src="<?= UPLOADS_URL ?>/<?= sanitize($item['image']) ?>" alt="<?= sanitize($item['name']) ?>" loading="lazy" />
              <?php else: ?>
                <span class="deal-placeholder"><?= sanitize(mb_substr($item['name'], 0, 1)) ?></span>
              <?php endif; ?>
            </a>

            <div class="cart-row-main">
              <div class="cart-row-top">
                <div>
                  <a class="cart-name" href="<?= productUrl($item) ?>"><?= sanitize($item['name']) ?></a>
                  <?php if (!empty($item['shop_name'])): ?>
                    <span class="cart-shop"><?= sanitize($item['shop_name']) ?></span>
                  <?php endif; ?>
                </div>
                <span class="cart-unit"><?= formatPrice($item['price']) ?> each</span>
              </div>

              <div class="cart-row-bottom">
                <form method="post" action="cart-action.php" class="cart-qty-form" data-cart-form data-reload-on-success>
                  <input type="hidden" name="_token" value="<?= getCSRF() ?>" />
                  <input type="hidden" name="action" value="cart_update" />
                  <input type="hidden" name="id" value="<?= $productId ?>" />
                  <input type="hidden" name="return" value="cart.php" />
                  <input
                    class="cart-qty-input"
                    type="number"
                    name="qty"
                    value="<?= (int) $item['qty'] ?>"
                    min="0"
                    max="<?= (int) $item['quantity'] ?>"
                    aria-label="Quantity for <?= sanitize($item['name']) ?>"
                  />
                  <button type="submit" class="cart-btn">Update</button>
                </form>

                <form method="post" action="cart-action.php" data-cart-form data-reload-on-success>
                  <input type="hidden" name="_token" value="<?= getCSRF() ?>" />
                  <input type="hidden" name="action" value="cart_remove" />
                  <input type="hidden" name="id" value="<?= $productId ?>" />
                  <input type="hidden" name="return" value="cart.php" />
                  <button type="submit" class="cart-btn cart-remove">Remove</button>
                </form>

                <span class="cart-line-total"><?= formatPrice($item['line_total']) ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <aside class="cart-summary">
        <h2>Order Summary</h2>
        <div class="summary-row">
          <span>Items</span>
          <span><?= (int) $cart['count'] ?></span>
        </div>
        <div class="summary-row">
          <span>Subtotal</span>
          <span><?= formatPrice($cart['subtotal']) ?></span>
        </div>
        <div class="summary-row">
          <span>Shipping</span>
          <span>Calculated at checkout</span>
        </div>
        <div class="summary-total">
          <span>Total</span>
          <span><?= formatPrice($cart['subtotal']) ?></span>
        </div>

        <a class="btn-store-primary" href="deals.php">Continue shopping</a>
        <form method="post" action="cart-action.php" data-cart-form data-reload-on-success>
          <input type="hidden" name="_token" value="<?= getCSRF() ?>" />
          <input type="hidden" name="action" value="cart_clear" />
          <input type="hidden" name="return" value="cart.php" />
          <button type="submit" class="btn-store-ghost">Empty cart</button>
        </form>

        <p class="cart-note">
          Checkout is not enabled in this project yet — your cart is saved in
          this session so nothing is lost while you keep browsing.
        </p>
      </aside>
    </div>

  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script src="js/site.js"></script>
<script src="js/store.js"></script>
</body>
</html>
