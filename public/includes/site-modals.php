<?php
/**
 * SITE MODALS partial — Arena Market
 *
 * Shared modals reachable from the site header (logout confirmation).
 * Requires js/site.js for open/close behaviour.
 *
 * Usage (just before </body>, after footer):
 *     include __DIR__ . '/includes/site-modals.php';
 *     <script src="<?= SITE_URL ?>/js/site.js"></script>
 */
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/session.php';
}
?>
    <!-- ══ LOGOUT CONFIRMATION MODAL ══ -->
    <div class="modal-overlay" id="logout-modal">
      <div class="modal-card">
        <div class="modal-icon">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
          </svg>
        </div>
        <h3>Sign out of Arena Market?</h3>
        <p>You can always sign back in later. Your cart and wishlist will be waiting for you.</p>
        <div class="modal-actions">
          <button type="button" class="modal-btn cancel" onclick="closeLogoutModal()">Stay Signed In</button>
          <a href="<?= SITE_URL ?>/logout.php" class="modal-btn confirm" id="logout-confirm">
            <span class="btn-label">Sign Out</span>
            <span class="btn-spinner" aria-hidden="true"></span>
          </a>
        </div>
      </div>
    </div>
