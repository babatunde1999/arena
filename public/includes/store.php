<?php
/**
 * Storefront store helpers — Arena Market
 *
 * One shared, session-backed cart + wishlist used by the homepage deals
 * and featured sections, the product details page, cart.php, wishlist.php
 * and the header badges. There is deliberately only ONE implementation
 * so the card buttons, header and pages can never drift apart.
 *
 * Cart shape:     $_SESSION['cart'][product_id] = quantity
 * Wishlist shape: $_SESSION['wishlist'][product_id] = true
 *
 * Quantities are always re-validated against the database (product must
 * still exist and be in stock) whenever the cart is read or changed.
 */
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/session.php';
}

/**
 * Ensure the cart/wishlist session buckets exist.
 */
function storeInit(): void
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
}

/** Total number of units in the cart (for the header badge). */
function cartCount(): int
{
    storeInit();
    $count = 0;
    foreach ($_SESSION['cart'] as $qty) {
        $count += max(0, (int) $qty);
    }
    return $count;
}

/** Number of saved wishlist products (for the header badge). */
function wishlistCount(): int
{
    storeInit();
    return count($_SESSION['wishlist']);
}

/** Is a product already saved to the wishlist? */
function storeInWishlist(int $productId): bool
{
    storeInit();
    return !empty($_SESSION['wishlist'][$productId]);
}

/**
 * Fetch one product row with shop/category info.
 *
 * @return array|null
 */
function storeProduct(int $productId, ?PDO $db = null): ?array
{
    if ($productId <= 0) {
        return null;
    }
    try {
        $db = $db ?: getDB();
        $stmt = $db->prepare(
            "SELECT p.id, p.name, p.slug, p.description, p.price, p.old_price,
                    p.quantity, p.image, p.rating, p.is_featured, p.shop_id,
                    p.category_id,
                    c.name AS category_name, c.slug AS category_slug,
                    s.name AS shop_name, s.slug AS shop_slug, s.location AS shop_location,
                    s.logo AS shop_logo, s.verified AS shop_verified
               FROM products p
               LEFT JOIN categories c ON c.id = p.category_id
               LEFT JOIN shops s ON s.id = p.shop_id
              WHERE p.id = :id
              LIMIT 1"
        );
        $stmt->execute([':id' => $productId]);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (PDOException $e) {
        error_log('storeProduct error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Add a product to the cart.
 *
 * @return array{ok:bool,message:string,count:int}
 */
function cartAdd(int $productId, int $qty = 1, ?PDO $db = null): array
{
    storeInit();
    $qty = max(1, $qty);
    $product = storeProduct($productId, $db);

    if ($product === null) {
        return ['ok' => false, 'message' => 'That product could not be found.', 'count' => cartCount()];
    }
    if ((int) $product['quantity'] <= 0) {
        return ['ok' => false, 'message' => 'Sorry, that product is out of stock.', 'count' => cartCount()];
    }

    $current = (int) ($_SESSION['cart'][$productId] ?? 0);
    $limit   = (int) $product['quantity'];
    $wanted  = $current + $qty;
    $stored  = min($wanted, $limit);

    $_SESSION['cart'][$productId] = $stored;

    if ($stored < $wanted) {
        return [
            'ok'      => true,
            'message' => 'Only ' . $limit . ' unit' . ($limit === 1 ? '' : 's') . ' of ' . $product['name'] . ' available — cart updated.',
            'count'   => cartCount(),
        ];
    }

    return [
        'ok'      => true,
        'message' => $product['name'] . ' added to your cart.',
        'count'   => cartCount(),
    ];
}

/**
 * Set an exact quantity for a cart line. 0 removes the line.
 *
 * @return array{ok:bool,message:string,count:int}
 */
function cartSetQty(int $productId, int $qty, ?PDO $db = null): array
{
    storeInit();
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
        return ['ok' => true, 'message' => 'Item removed from your cart.', 'count' => cartCount()];
    }

    $product = storeProduct($productId, $db);
    if ($product === null) {
        unset($_SESSION['cart'][$productId]);
        return ['ok' => false, 'message' => 'That product is no longer available.', 'count' => cartCount()];
    }
    if ((int) $product['quantity'] <= 0) {
        unset($_SESSION['cart'][$productId]);
        return ['ok' => false, 'message' => 'That product is out of stock and was removed.', 'count' => cartCount()];
    }

    $limit = (int) $product['quantity'];
    $qty   = min($qty, $limit);
    $_SESSION['cart'][$productId] = $qty;

    return [
        'ok'      => true,
        'message' => 'Cart updated (' . $qty . ' × ' . $product['name'] . ').',
        'count'   => cartCount(),
    ];
}

/**
 * Remove one product from the cart.
 *
 * @return array{ok:bool,message:string,count:int}
 */
function cartRemove(int $productId): array
{
    storeInit();
    unset($_SESSION['cart'][$productId]);
    return ['ok' => true, 'message' => 'Item removed from your cart.', 'count' => cartCount()];
}

/**
 * Toggle a product in the wishlist.
 *
 * @return array{ok:bool,active:bool,message:string,count:int}
 */
function wishlistToggle(int $productId, ?PDO $db = null): array
{
    storeInit();
    if (storeInWishlist($productId)) {
        unset($_SESSION['wishlist'][$productId]);
        return [
            'ok'      => true,
            'active'  => false,
            'message' => 'Removed from your wishlist.',
            'count'   => wishlistCount(),
        ];
    }

    $product = storeProduct($productId, $db);
    if ($product === null) {
        return [
            'ok'      => false,
            'active'  => false,
            'message' => 'That product could not be found.',
            'count'   => wishlistCount(),
        ];
    }

    $_SESSION['wishlist'][$productId] = true;
    return [
        'ok'      => true,
        'active'  => true,
        'message' => $product['name'] . ' saved to your wishlist.',
        'count'   => wishlistCount(),
    ];
}

/** Remove one product from the wishlist. */
function wishlistRemove(int $productId): void
{
    storeInit();
    unset($_SESSION['wishlist'][$productId]);
}

/**
 * Read the cart with live product data.
 *
 * Products that were deleted or went out of stock are pruned automatically.
 *
 * @return array{items:array<int,array>,subtotal:float,count:int}
 */
function storeCartDetails(?PDO $db = null): array
{
    storeInit();
    $result = ['items' => [], 'subtotal' => 0.0, 'count' => 0];
    if (empty($_SESSION['cart'])) {
        return $result;
    }

    try {
        $db = $db ?: getDB();
        $ids = array_values(array_map('intval', array_keys($_SESSION['cart'])));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare(
            "SELECT p.id, p.name, p.slug, p.price, p.old_price, p.image, p.quantity,
                    s.name AS shop_name
               FROM products p
               LEFT JOIN shops s ON s.id = p.shop_id
              WHERE p.id IN ($placeholders)"
        );
        $stmt->execute($ids);

        foreach ($stmt->fetchAll() as $row) {
            $id  = (int) $row['id'];
            $qty = max(1, (int) ($_SESSION['cart'][$id] ?? 1));

            if ((int) $row['quantity'] <= 0) {
                unset($_SESSION['cart'][$id]);
                continue;
            }

            $qty = min($qty, (int) $row['quantity']);
            $_SESSION['cart'][$id] = $qty;

            $lineTotal = (float) $row['price'] * $qty;
            $row['qty']        = $qty;
            $row['line_total'] = $lineTotal;

            $result['items'][] = $row;
            $result['subtotal'] += $lineTotal;
            $result['count']    += $qty;
        }
    } catch (PDOException $e) {
        error_log('storeCartDetails error: ' . $e->getMessage());
    }

    return $result;
}

/**
 * Read wishlist products with live data (missing products are pruned).
 *
 * @return array<int,array>
 */
function storeWishlistDetails(?PDO $db = null): array
{
    storeInit();
    if (empty($_SESSION['wishlist'])) {
        return [];
    }

    try {
        $db = $db ?: getDB();
        $ids = array_values(array_map('intval', array_keys($_SESSION['wishlist'])));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare(
            "SELECT p.id, p.name, p.slug, p.price, p.old_price, p.image, p.quantity, p.rating,
                    c.name AS category_name, s.name AS shop_name
               FROM products p
               LEFT JOIN categories c ON c.id = p.category_id
               LEFT JOIN shops s ON s.id = p.shop_id
              WHERE p.id IN ($placeholders)
              ORDER BY p.name ASC"
        );
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();

        $found = [];
        foreach ($rows as $row) {
            $found[(int) $row['id']] = true;
        }
        foreach ($ids as $id) {
            if (!isset($found[$id])) {
                unset($_SESSION['wishlist'][$id]);
            }
        }

        return $rows;
    } catch (PDOException $e) {
        error_log('storeWishlistDetails error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Products that currently carry a genuine discount.
 *
 * A deal is valid when:
 *   - old_price and price are both set and greater than zero
 *   - old_price is strictly greater than price
 *   - the product is in stock (quantity > 0)
 *
 * The database has no "discount active/expires" columns, so the only
 * enforceable validity rules are the ones above (see project notes).
 *
 * @param int|null $limit        Maximum rows to return.
 * @param bool     $uniqueNames  Keep only the best offer per product name
 *                               (used for the homepage highlights so the same
 *                               item from many vendors doesn't fill the row).
 * @return array<int,array>
 */
function storeDeals(PDO $db, ?int $limit = null, bool $uniqueNames = false): array
{
    $sql = "SELECT p.id, p.name, p.slug, p.price, p.old_price, p.image,
                   p.quantity, p.rating,
                   c.name AS category_name, c.slug AS category_slug,
                   s.id AS shop_id, s.name AS shop_name, s.verified AS shop_verified
              FROM products p
              LEFT JOIN categories c ON c.id = p.category_id
              LEFT JOIN shops s ON s.id = p.shop_id
             WHERE p.old_price IS NOT NULL
               AND p.old_price > 0
               AND p.price > 0
               AND p.old_price > p.price
               AND p.quantity > 0
             ORDER BY ((p.old_price - p.price) / p.old_price) DESC,
                      p.created_at DESC, p.id DESC";

    if ($limit !== null && !$uniqueNames) {
        $sql .= ' LIMIT ' . max(1, $limit);
    }

    try {
        $rows = $db->query($sql)->fetchAll();
    } catch (PDOException $e) {
        error_log('storeDeals error: ' . $e->getMessage());
        return [];
    }

    if ($uniqueNames) {
        $seen  = [];
        $deals = [];
        foreach ($rows as $row) {
            $key = mb_strtolower(trim((string) $row['name']));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $deals[] = $row;
            if ($limit !== null && count($deals) >= $limit) {
                break;
            }
        }
        return $deals;
    }

    return $rows;
}

/**
 * Products pinned by the admin ("Show on home page" / is_featured = 1).
 *
 * Uses the same column list as storeDeals() so the shared product card
 * partial can render these rows unchanged on the homepage and on
 * featured.php.
 *
 * @param int|null $limit Maximum rows to return.
 * @return array<int,array>
 */
function storeFeaturedProducts(PDO $db, ?int $limit = null): array
{
    $sql = "SELECT p.id, p.name, p.slug, p.price, p.old_price, p.image,
                   p.quantity, p.rating,
                   c.name AS category_name, c.slug AS category_slug,
                   s.id AS shop_id, s.name AS shop_name, s.verified AS shop_verified
              FROM products p
              LEFT JOIN categories c ON c.id = p.category_id
              LEFT JOIN shops s ON s.id = p.shop_id
             WHERE p.is_featured = 1
             ORDER BY p.created_at DESC, p.id DESC";

    if ($limit !== null) {
        $sql .= ' LIMIT ' . max(1, $limit);
    }

    try {
        return $db->query($sql)->fetchAll();
    } catch (PDOException $e) {
        error_log('storeFeaturedProducts error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Discount percentage for a product (1–99). Returns 0 when there is no
 * genuine discount, so no misleading badge can ever be rendered.
 */
function dealPercent($price, $oldPrice): int
{
    $price    = (float) $price;
    $oldPrice = (float) $oldPrice;

    if ($oldPrice <= 0 || $price <= 0 || $oldPrice <= $price) {
        return 0;
    }

    $percent = (int) round((($oldPrice - $price) / $oldPrice) * 100);
    return max(0, min(99, $percent));
}

/** Canonical details URL for a product. */
function productUrl(array $product): string
{
    return SITE_URL . '/product.php?id=' . (int) $product['id'];
}

/**
 * Safe "return to this page" value for the cart forms, so a no-JS
 * submit brings the shopper straight back where they were.
 */
function storeReturnUrl(): string
{
    $self = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    $qs   = $_SERVER['QUERY_STRING'] ?? '';
    $safe = preg_match('/^[A-Za-z0-9_-]+\.php$/', $self) ? $self : 'index.php';
    if ($qs !== '' && preg_match('/^[A-Za-z0-9_\-=&%.+]*$/', $qs)) {
        $safe .= '?' . $qs;
    }
    return $safe;
}
