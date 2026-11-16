<?php
/**
 * Product card partial — Arena Market
 *
 * Shared by the homepage "Today's Deals" carousel, deals.php and
 * wishlist.php so a product looks and behaves the same everywhere.
 *
 * Expects:
 *   $card        array  product row (id, name, price, old_price, image,
 *                       rating, quantity, shop_name, …)
 *   $cardReturn  string optional return URL for no-JS cart submissions
 *
 * All buttons post to cart-action.php, which is the single cart/wishlist
 * handler. js/store.js upgrades these forms to AJAX automatically.
 */
$__percent  = dealPercent($card['price'], $card['old_price'] ?? 0);
$__inWish   = storeInWishlist((int) $card['id']);
$__return   = $cardReturn ?? storeReturnUrl();
$__rating   = (float) ($card['rating'] ?? 0);
$__stock    = (int) ($card['quantity'] ?? 0);
$__hasImage = !empty($card['image']) && is_file(UPLOADS_PATH . '/' . $card['image']);
?>
<article class="deal-card">
  <div class="deal-media">
    <a href="<?= productUrl($card) ?>" class="deal-media-link" tabindex="-1" aria-hidden="true">
      <?php if ($__hasImage): ?>
        <img
          src="<?= UPLOADS_URL ?>/<?= sanitize($card['image']) ?>"
          alt="<?= sanitize($card['name']) ?>"
          loading="lazy"
        />
      <?php else: ?>
        <span class="deal-placeholder">
          <?= sanitize(mb_substr($card['name'], 0, 1)) ?>
        </span>
      <?php endif; ?>
    </a>

    <?php if ($__percent > 0): ?>
      <span class="deal-badge">-<?= $__percent ?>%</span>
    <?php endif; ?>

    <form method="post" action="cart-action.php" class="deal-wish-form" data-wishlist-form<?= !empty($cardRemoveOnWishlist) ? ' data-reload-on-success' : '' ?>>
      <input type="hidden" name="_token" value="<?= getCSRF() ?>" />
      <input type="hidden" name="action" value="wishlist_toggle" />
      <input type="hidden" name="id" value="<?= (int) $card['id'] ?>" />
      <input type="hidden" name="return" value="<?= sanitize($__return) ?>" />
      <button
        type="submit"
        class="deal-wish<?= $__inWish ? ' is-active' : '' ?>"
        aria-pressed="<?= $__inWish ? 'true' : 'false' ?>"
        aria-label="<?= $__inWish ? 'Remove from wishlist' : 'Save to wishlist' ?>"
        title="<?= $__inWish ? 'Remove from wishlist' : 'Save to wishlist' ?>"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
        </svg>
      </button>
    </form>
  </div>

  <div class="deal-body">
    <a class="deal-name" href="<?= productUrl($card) ?>" title="<?= sanitize($card['name']) ?>">
      <?= sanitize(mb_strimwidth($card['name'], 0, 46, '…')) ?>
    </a>

    <?php if (!empty($card['shop_name'])): ?>
      <span class="deal-shop"><?= sanitize($card['shop_name']) ?></span>
    <?php endif; ?>

    <?php if ($__rating > 0): ?>
      <span class="deal-rating" aria-label="Rated <?= number_format($__rating, 1) ?> out of 5">
        <span aria-hidden="true">★</span> <?= number_format($__rating, 1) ?>
      </span>
    <?php endif; ?>

    <div class="deal-price-row">
      <span class="deal-price"><?= formatPrice($card['price']) ?></span>
      <?php if ($__percent > 0): ?>
        <span class="deal-old"><?= formatPrice($card['old_price']) ?></span>
      <?php endif; ?>
    </div>

    <form method="post" action="cart-action.php" class="deal-cart-form" data-cart-form>
      <input type="hidden" name="_token" value="<?= getCSRF() ?>" />
      <input type="hidden" name="action" value="cart_add" />
      <input type="hidden" name="id" value="<?= (int) $card['id'] ?>" />
      <input type="hidden" name="qty" value="1" />
      <input type="hidden" name="return" value="<?= sanitize($__return) ?>" />
      <button type="submit" class="deal-btn deal-btn-cart"<?= $__stock <= 0 ? ' disabled' : '' ?>>
        <?= $__stock <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
      </button>
    </form>
  </div>
</article>
