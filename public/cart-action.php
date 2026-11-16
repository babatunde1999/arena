<?php
/**
 * Cart & wishlist action handler — Arena Market
 *
 * The single POST endpoint for every Add to Cart / Wishlist button in the
 * storefront. Handles both AJAX (JSON response, used by js/store.js) and
 * normal form submits (flash + redirect back to the originating page).
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/store.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

function storeIsAjax(): bool
{
    $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    $accept        = $_SERVER['HTTP_ACCEPT'] ?? '';
    return strcasecmp($requestedWith, 'XMLHttpRequest') === 0
        || strpos($accept, 'application/json') !== false;
}

/**
 * Only allow a same-directory PHP page + optional safe query string as the
 * post-submit destination. Prevents open redirects through the `return` field.
 */
function storeSafeReturn(string $value): string
{
    $value = trim($value);
    if (preg_match('#^([A-Za-z0-9_-]+\.php)(\?[A-Za-z0-9_\-=&%.+]*)?$#', $value, $matches)) {
        return $matches[1] . ($matches[2] ?? '');
    }
    return 'index.php';
}

function storeJson(array $payload): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

$ajax   = storeIsAjax();
$action = (string) ($_POST['action'] ?? '');
$id     = (int) ($_POST['id'] ?? 0);
$qty    = (int) ($_POST['qty'] ?? 1);
$return = storeSafeReturn((string) ($_POST['return'] ?? 'index.php'));

$result = ['ok' => false, 'message' => 'Unknown request.', 'count' => cartCount()];

// ── CSRF ──
$token = (string) ($_POST['_token'] ?? '');
if (!verifyCSRF($token)) {
    $result = ['ok' => false, 'message' => 'Your session expired. Please refresh the page and try again.', 'count' => cartCount()];
} elseif ($id <= 0 && !in_array($action, ['cart_clear'], true)) {
    $result = ['ok' => false, 'message' => 'Invalid product.', 'count' => cartCount()];
} else {
    switch ($action) {
        case 'cart_add':
            $result = cartAdd($id, max(1, $qty));
            break;

        case 'cart_update':
            $result = cartSetQty($id, $qty);
            break;

        case 'cart_remove':
            $result = cartRemove($id);
            break;

        case 'cart_clear':
            $_SESSION['cart'] = [];
            $result = ['ok' => true, 'message' => 'Your cart is now empty.', 'count' => 0];
            break;

        case 'wishlist_toggle':
            $wish = wishlistToggle($id);
            $result = [
                'ok'      => $wish['ok'],
                'message' => $wish['message'],
                'count'   => cartCount(),
                'active'  => $wish['active'],
            ];
            break;

        case 'wishlist_remove':
            wishlistRemove($id);
            $result = ['ok' => true, 'message' => 'Removed from your wishlist.', 'count' => cartCount()];
            break;

        default:
            $result = ['ok' => false, 'message' => 'Unknown request.', 'count' => cartCount()];
    }
}

if ($ajax) {
    storeJson([
        'ok'            => $result['ok'],
        'message'       => $result['message'],
        'cartCount'     => cartCount(),
        'wishlistCount' => wishlistCount(),
        'active'        => $result['active'] ?? null,
        'action'        => $action,
        'productId'     => $id,
    ]);
}

setFlash($result['ok'] ? 'success' : 'error', $result['message']);
redirect($return);
