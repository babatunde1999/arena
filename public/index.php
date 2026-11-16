<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/shop.php';
require_once __DIR__ . '/includes/store.php';

$db = null;
try { $db = getDB(); } catch (Exception $e) { $db = null; }

// Handle opening shop from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'open_shop' && isLoggedIn() && $db) {
    $csrfOk = true;
    if (function_exists('verifyCSRF')) {
        $csrfOk = verifyCSRF($_POST['_token'] ?? '');
    } elseif (isset($_SESSION['csrf_token'])) {
        $csrfOk = hash_equals((string)$_SESSION['csrf_token'], (string)($_POST['_token'] ?? ''));
    }
    if (!$csrfOk) {
        setFlash('error', 'Invalid security token. Please try again.');
        redirect(SITE_URL . '/index.php?vendor_required=1');
    }
    if (!isVendor($db)) {
        try {
            getOrCreateShop($db);
            setFlash('success', 'Congratulations! Your shop has been created. You are now a vendor.');
            redirect(SITE_URL . '/products.php');
        } catch (Exception $e) {
            setFlash('error', 'Failed to create shop. Please try again.');
            redirect(SITE_URL . '/index.php?vendor_required=1');
        }
    } else {
        setFlash('info', 'You already have a vendor account.');
        redirect(SITE_URL . '/products.php');
    }
}

$flash = getFlash();

// ── Featured products pinned by the admin ("Show on home page") ──
$featuredProducts = [];
try {
    $featuredProducts = storeFeaturedProducts($db ?: getDB());
} catch (Exception $e) {
    error_log("Featured products error: " . $e->getMessage());
}

// ── Today's Deals — genuine, in-stock discounts only ──
$dealProducts = [];
try {
    $dealProducts = storeDeals($db ?: getDB(), 10, true);
} catch (Exception $e) {
    error_log("Deal products error: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home</title>
    <link rel="stylesheet" href="css/default.css" />
    <link rel="stylesheet" href="css/site.css?v=2" />
    <link rel="stylesheet" href="css/index.css?v=3" />
    <link rel="stylesheet" href="css/store.css?v=3" />
  </head>
  <body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <!-- ══ HERO ══ -->
    <section class="hero">
      <div class="hero-inner">
        <span class="hero-badge">Nigerian army shopping arena</span>
        <h1>Find Everything in <span>Arena Market</span></h1>
        <p>
          Explore thousands of shops selling clothes, shoes, bags and more — all
          in one place.
        </p>
        <div class="hero-buttons">
          <button class="btn-primary"><a href="./shop.php">Browse Shops</a></button>
          <button class="btn-outline"><a href="./categories.php">View All Categories</a></button>
        </div>
      </div>
    </section>
    <a href=""></a>

    <!-- ══ CATEGORIES ══ -->
    <section class="section">
      <div class="section-header">
        <h2 class="section-title">Browse Categories</h2>
        <a href="categories.php" class="see-all">See all →</a>
      </div>
      <div class="carousel">
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/shirt.jpg"
            alt="Shirt"
          />
          <div class="image-content">Shirt</div>
        </div>
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/bags.jpg"
            alt="Bags"
          />
          <div class="image-content">Bags</div>
        </div>
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/traveelling bag.jpg"
            alt="Travelling Bag"
          />
          <div class="image-content">Travelling Bag</div>
        </div>
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/office shoe.jpg"
            alt="Men Shoe"
          />
          <div class="image-content">Men Shoe</div>
        </div>
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/woman shoe.jpg"
            alt="women Shoe"
          />
          <div class="image-content">women Shoe</div>
        </div>
        <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/sneakers.jpg"
            alt="Sneakers"
          />
          <div class="image-content">Sneakers</div>
        </div>
           <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/crocs.jpg"
            alt="Crocs"
          />
          <div class="image-content">Crocs</div>
        </div>
           <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/slide.jpg"
            alt="Slide"
          />
          <div class="image-content">Slide</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/office shirt.jpg"
            alt="Office Shirts"
          />
          <div class="image-content">Office Shirts</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/women slide.jpg"
            alt="Women Slide"
          />
          <div class="image-content">Women Slide</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/pant trouser.jpg"
            alt="Pant Trouser"
          />
          <div class="image-content">Pant Trouser</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/jeans.jpg"
            alt="Jeans"
          />
          <div class="image-content">Jeans</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/body cream.jpg"
            alt="Body cream"
          />
          <div class="image-content">Body cream</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/body soap.jpg"
            alt="Body Soap"
          />
          <div class="image-content">Body Soap</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/spray.jpg"
            alt="Body Spray"
          />
          <div class="image-content">Body Spray</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/perfume.jpg"
            alt="Perfume"
          />
          <div class="image-content">Perfume</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/deodorant.jpg"
            alt="Deodorant"
          />
          <div class="image-content">Deodorant</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/fridge.jpg"
            alt="Fridge"
          />
          <div class="image-content">Fridge</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/jbl.jpg"
            alt="JBL"
          />
          <div class="image-content">JBL</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/television.jpg"
            alt="Television"
          />
          <div class="image-content">Television</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/iron.jpg"
            alt="Iron"
          />
          <div class="image-content">Iron</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/standing fan.jpg"
            alt="Standing fan"
          />
          <div class="image-content">Standing fan</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/ceoling fan.jpg"
            alt="Ceiling Fan"
          />
          <div class="image-content">Ceiling Fan</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
         <div class="carousel-item">
          <img
            class="carousel-image"
            src="./images/products/2.jpg"
            alt="Appliances"
          />
          <div class="image-content">Appliances</div>
        </div>
      
      </div>
      
    </section>

    <?php if (!empty($featuredProducts)): ?>
    <!-- ══ FEATURED PRODUCTS (pinned by admin) ══ -->
    <section class="section featured-products">
      <div class="section-header">
        <h2 class="section-title">Featured Products</h2>
        <a href="featured.php" class="see-all">See all →</a>
      </div>
      <div class="carousel-market-card">
        <?php foreach ($featuredProducts as $fp): ?>
          <div class="carousel-market-card-items">
            <?php if (!empty($fp['image'])): ?>
              <img
                class="carousel-market-card-image"
                src="<?= UPLOADS_URL ?>/<?= sanitize($fp['image']) ?>"
                alt="<?= sanitize($fp['name']) ?>"
              />
            <?php else: ?>
              <div
                class="carousel-market-card-image"
                style="background:#f7f4ef;display:flex;align-items:center;justify-content:center;"
              >
                <span style="font-size:2rem;font-weight:800;color:#d97706;text-transform:uppercase;">
                  <?= sanitize(mb_substr($fp['name'], 0, 1)) ?>
                </span>
              </div>
            <?php endif; ?>
            <div class="market-card-image-content">
              <h3 class="market-image-tittle" title="<?= sanitize($fp['name']) ?>">
                <?= sanitize(mb_strimwidth($fp['name'], 0, 40, '…')) ?>
              </h3>
              <span class="market-image-text-shop"><?= sanitize($fp['shop_name'] ?? 'Arena Market') ?></span>
              <span class="market-image-text-price">
                <?= formatPrice($fp['price']) ?>
                <?php if (!empty($fp['old_price']) && (float) $fp['old_price'] > 0): ?>
                  <del style="margin-left:6px;color:#aaa;"><?= formatPrice($fp['old_price']) ?></del>
                <?php endif; ?>
              </span>
              <a class="market-card-button" href="shop.php">View in Market</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($dealProducts)): ?>
    <!-- ══ TODAY'S DEALS (products with a real discount) ══ -->
    <section class="section deals-section">
      <div class="section-header">
        <div>
          <h2 class="section-title">Today's Deals</h2>
          <p class="deals-subtitle">Limited-time offers from Arena Market vendors</p>
        </div>
        <a href="deals.php" class="see-all">View all deals →</a>
      </div>
      <div class="carousel-market-card deals-carousel">
        <?php foreach ($dealProducts as $deal): ?>
          <?php
          $card       = $deal;
          $cardReturn = 'index.php';
          include __DIR__ . '/includes/product-card.php';
          ?>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- ══ MARKET VENDORS ══ -->
    <section
      class="section"
      style="
        background: #fafafa;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
      "
    >
      <div class="section-header">
        <h2 class="section-title">Market Vendors</h2>
        <a href="#" class="see-all">See all →</a>
      </div>
      <div class="carousel-card">
        <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
         <div class="carousel-card-items">
          <img
            class="carousel-card-image"
            src="./images/products card/Frame 40.jpg"
            alt="Mary Fashion Hub"
          />
          <div class="card-image-content">
            <h3 class="image-tittle">Mary Fashion Hub</h3>
            <span class="image-text-shop">Block F-17, Shop 14</span>
            <span class="image-text-second">Verified Market Shop</span>
            <button class="card-button">Chat Seller</button>
          </div>
        </div>
      </div>
    </section>

    <!-- ══ TRENDING ══ -->
    <section class="section">
      <div class="section-header">
        <h2 class="section-title">Trending in the Market</h2>
        <a href="#" class="see-all">See all →</a>
      </div>
      <div class="carousel-market-card">
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
      </div>
    </section>

     <!-- ══ TRENDING ══ -->
    <section class="section">
      <div class="section-header">
        <h2 class="section-title">New in the Market</h2>
        <a href="#" class="see-all">See all →</a>
      </div>
      <div class="carousel-market-card">
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
        <div class="carousel-market-card-items">
          <img
            class="carousel-market-card-image"
            src="./images/products card/3.jpg"
            alt="Dara Cloth Shop"
          />
          <div class="market-card-image-content">
            <h3 class="market-image-tittle">Dara Cloth Shop</h3>
            <span class="market-image-text-shop">Block F-17, Shop 14</span>
            <span class="market-image-text-price">Verified Market Shop</span>
            <button class="market-card-button">Chat Seller</button>
          </div>
        </div>
      </div>
    </section>


    <!-- ══ NEWSLETTER ══ -->
    <div class="newsletter-section">
      <h2>Subscribe to Our Newsletter</h2>
      <p>Get updates on our latest offers and new arrivals</p>
      <div class="newsletter-form">
        <input
          class="newsletter-input"
          type="email"
          placeholder="Enter your email address"
        />
        <button class="newsletter-btn">Subscribe</button>
      </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <?php include __DIR__ . '/includes/site-modals.php'; ?>

    <!-- ══ WELCOME MODAL (shows after a successful login) ══ -->
    <?php $showWelcome = (!empty($flash) && $flash['type'] === 'success'); ?>
    <div class="modal-overlay<?= $showWelcome ? ' active' : '' ?>" id="welcome-modal">
      <div class="modal-card">
        <div class="modal-icon success">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>
        <h3><?= $showWelcome ? htmlspecialchars($flash['message']) : 'Welcome back!' ?></h3>
        <p>
          You are now signed in as
          <strong><?= sanitize(getUsername()) ?: 'User' ?></strong>.
          Enjoy shopping at Arena Market.
        </p>
        <div class="modal-actions">
          <button type="button" class="modal-btn confirm" onclick="closeWelcomeModal()">Continue Shopping</button>
        </div>
      </div>
    </div>

    <!-- ══ BECOME VENDOR MODAL ══ -->
    <?php $showBecomeVendor = (!empty($flash) && $flash['type'] === 'error' && (stripos($flash['message'], 'vendor') !== false || isset($_GET['vendor_required']))) || (isset($_GET['vendor_required']) && isLoggedIn() && !isVendor($db ?? null)); ?>
    <div class="modal-overlay<?= $showBecomeVendor ? ' active' : '' ?>" id="become-vendor-modal">
      <div class="modal-card">
        <div class="modal-icon" style="background:#fef3c7;color:#d97706;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
          </svg>
        </div>
        <h3>Vendor Access Required</h3>
        <p>Only vendors can add and manage products. Open your shop to become a vendor and start selling on Arena Market.</p>
        <form method="POST" action="<?= SITE_URL ?>/index.php" style="margin-top:12px;">
          <input type="hidden" name="action" value="open_shop">
          <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? $_SESSION['_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div class="modal-actions">
            <button type="button" class="modal-btn cancel" onclick="closeBecomeVendorModal()">Cancel</button>
            <button type="submit" class="modal-btn confirm">Open My Shop</button>
          </div>
        </form>
      </div>
    </div>

    <script src="js/site.js"></script>
    <script src="js/index.js?v=2"></script>
    <script src="js/store.js"></script>
  </body>
</html>
