<?php
/**
 * Storefront FOOTER partial — Arena Market
 *
 * Usage:  include __DIR__ . '/includes/footer.php';
 * (expects config/config.php + config/session.php to be loaded)
 */
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/session.php';
}
?>
    <!-- ══ FOOTER ══ -->
    <footer>
      <div class="footer-inner">
        <div class="footer-grid">
          <div class="footer-col">
            <h4>About Arena</h4>
            <ul>
              <li><a href="#">About us</a></li>
              <li><a href="#">How it works</a></li>
              <li><a href="#">Terms &amp; Conditions</a></li>
              <li><a href="#">Privacy Policy</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h4>Categories</h4>
            <ul>
              <li><a href="<?= SITE_URL ?>/categories.php">All Categories</a></li>
              <li><a href="#">Fashion</a></li>
              <li><a href="#">Phones &amp; Tablets</a></li>
              <li><a href="#">Groceries</a></li>
              <li><a href="#">Electronics</a></li>
              <li><a href="#">Appliances</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h4>Sell on Arena</h4>
            <ul>
              <li><a href="<?= SITE_URL ?>/index.php?vendor_required=1">Become a Vendor</a></li>
              <li><a href="<?= SITE_URL ?>/products.php">Vendor Portal</a></li>
              <li><a href="#">Seller Guidelines</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h4>Help &amp; Support</h4>
            <ul>
              <li><a href="#">Help Center</a></li>
              <li><a href="#">FAQs</a></li>
              <li><a href="#">Order Tracking</a></li>
              <li><a href="#">Returns &amp; Refunds</a></li>
              <li><a href="#">Shipping Information</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h4>Our Socials</h4>
            <ul>
              <li><a href="#">Instagram</a></li>
              <li><a href="#">TikTok</a></li>
              <li><a href="#">Facebook</a></li>
              <li><a href="#">Twitter</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h4>Contact Us</h4>
            <ul>
              <li><span>support@thearenamarket.com</span></li>
              <li><span>+234 906 925 9369</span></li>
              <li><span>+234 704 524 3303</span></li>
              <li><span>Bolade Bus-Stop, Oshodi, Lagos, Nigeria</span></li>
            </ul>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; <?= date('Y') ?> Arena Market. All rights reserved.</p>
          <p>Made with care in Oshodi Lagos, Nigeria</p>
        </div>
      </div>
    </footer>
