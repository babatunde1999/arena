<?php
/**
 * FLASH MESSAGE partial — Arena Market
 *
 * Renders the flash banner from the page-provided $flash variable.
 * The page is responsible for fetching it first:
 *     $flash = getFlash();
 *
 * Usage:  include __DIR__ . '/includes/flash.php';
 */
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/session.php';
}
?>
<?php if (!empty($flash)): ?>
    <div class="flash-banner flash-<?= sanitize($flash['type'] ?? 'info') ?>">
      <?= sanitize($flash['message'] ?? '') ?>
    </div>
<?php endif; ?>
