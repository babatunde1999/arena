<?php
/**
 * Admin dashboard - overview stats + recent signups.
 * Arena Market - E-Commerce Platform
 */

require_once __DIR__ . '/includes/bootstrap.php';
requireAdmin();

$pageTitle = 'Dashboard';
$active    = 'dashboard';

try {
    $db = getDB();

    $stats = [
        'users'       => (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'vendors'     => (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'vendor'")->fetchColumn(),
        'admins'      => (int) $db->query("SELECT COUNT(*) FROM admins")->fetchColumn(),
        'verified'    => (int) $db->query("SELECT COUNT(*) FROM users WHERE email_verified = 1")->fetchColumn(),
        'unverified'  => (int) $db->query("SELECT COUNT(*) FROM users WHERE email_verified = 0")->fetchColumn(),
        'shops'       => (int) $db->query("SELECT COUNT(*) FROM shops")->fetchColumn(),
        'products'    => (int) $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        'orders'      => (int) $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    ];

    $recentUsers = $db->query(
        "SELECT id, first_name, last_name, username, email, role, email_verified, created_at
         FROM users ORDER BY created_at DESC LIMIT 5"
    )->fetchAll();
} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    setFlash('error', 'Could not load dashboard data.');
    redirect(ADMIN_URL . '/logout.php');
}

require __DIR__ . '/partials/header.php';
?>

<div class="admin-page-head">
  <div>
    <h1>Dashboard</h1>
    <p>Welcome back, <?= sanitize($_SESSION['admin_username'] ?? '') ?>. Here's an overview of the market.</p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a class="admin-btn" href="<?= ADMIN_URL ?>/products.php">Manage Products</a>
    <a class="admin-btn admin-btn-ghost" href="<?= ADMIN_URL ?>/users.php">Manage Users</a>
  </div>
</div>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-label">Store Accounts</div>
    <div class="stat-value"><?= $stats['users'] ?></div>
    <div class="stat-sub">
      <a href="<?= ADMIN_URL ?>/users.php?role=vendor"><?= $stats['vendors'] ?> vendors</a> ·
      <a href="<?= ADMIN_URL ?>/users.php">view all</a>
    </div>
  </div>
  <div class="stat-card stat-red">
    <div class="stat-label">Admins</div>
    <div class="stat-value"><?= $stats['admins'] ?></div>
    <div class="stat-sub">separate admin accounts</div>
  </div>
  <div class="stat-card stat-green">
    <div class="stat-label">Verified Emails</div>
    <div class="stat-value"><?= $stats['verified'] ?></div>
    <div class="stat-sub"><a href="<?= ADMIN_URL ?>/users.php?verified=0"><?= $stats['unverified'] ?> pending</a></div>
  </div>
  <div class="stat-card stat-blue">
    <div class="stat-label">Shops</div>
    <div class="stat-value"><?= $stats['shops'] ?></div>
    <div class="stat-sub">registered sellers</div>
  </div>
  <div class="stat-card stat-amber">
    <div class="stat-label">Products</div>
    <div class="stat-value"><?= $stats['products'] ?></div>
    <div class="stat-sub">listed across shops</div>
  </div>
  <div class="stat-card stat-red">
    <div class="stat-label">Orders</div>
    <div class="stat-value"><?= $stats['orders'] ?></div>
    <div class="stat-sub">placed on the store</div>
  </div>
</div>

<div class="admin-card">
  <h2>Recent Signups</h2>
  <?php if (empty($recentUsers)): ?>
    <div class="empty-state">No users have registered yet.</div>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Email</th>
            <th>Status</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentUsers as $u): ?>
            <tr>
              <td class="user-cell">
                <div class="user-name"><?= sanitize($u['first_name'] . ' ' . $u['last_name']) ?></div>
                <div class="user-email">@<?= sanitize($u['username']) ?> · #<?= (int) $u['id'] ?></div>
              </td>
              <td><span class="badge badge-<?= sanitize($u['role']) ?>"><?= sanitize($u['role']) ?></span></td>
              <td><?= sanitize($u['email']) ?></td>
              <td>
                <?php if ($u['email_verified']): ?>
                  <span class="badge badge-verified">Verified</span>
                <?php else: ?>
                  <span class="badge badge-unverified">Unverified</span>
                <?php endif; ?>
              </td>
              <td><?= sanitize(date('M j, Y', strtotime($u['created_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>