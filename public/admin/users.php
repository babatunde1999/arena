<?php
/**
 * Admin user management - search, filter, role changes, verification,
 * password resets and account deletion.
 * Arena Market - E-Commerce Platform
 */

require_once __DIR__ . '/includes/bootstrap.php';
requireAdmin();

$db = getDB();
$perPage = 20;

// ── Handle actions (POST + CSRF) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf'] ?? '')) {
        setFlash('error', 'Security token expired. Please try again.');
        redirect(ADMIN_URL . '/users.php');
    }

    $action = $_POST['action'] ?? '';
    $target = (int) ($_POST['id'] ?? 0);

    try {
        switch ($action) {
            case 'role':
                $newRole = $_POST['role'] ?? '';
                if (!in_array($newRole, getRoles(), true) || $target < 1) {
                    setFlash('error', 'Invalid role change.');
                } else {
                    $stmt = $db->prepare("UPDATE users SET role = :role, updated_at = NOW() WHERE id = :id");
                    $stmt->execute([':role' => $newRole, ':id' => $target]);
                    setFlash('success', 'User #' . $target . ' role updated to "' . $newRole . '".');
                }
                break;

            case 'verify':
            case 'unverify':
                $value = $action === 'verify' ? 1 : 0;
                $stmt = $db->prepare("UPDATE users SET email_verified = :v, updated_at = NOW() WHERE id = :id");
                $stmt->execute([':v' => $value, ':id' => $target]);
                setFlash('success', 'Email status updated for user #' . $target . '.');
                break;

            case 'reset_password':
                $raw = $_POST['new_password'] ?? '';
                if (strlen($raw) < PASSWORD_MIN_LENGTH) {
                    setFlash('error', 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.');
                } elseif (!preg_match('/[A-Z]/', $raw) || !preg_match('/[0-9]/', $raw)) {
                    setFlash('error', 'Password must contain at least one uppercase letter and one number.');
                } else {
                    $hash = password_hash($raw, PASSWORD_HASH_ALGO);
                    $stmt = $db->prepare("UPDATE users SET password = :pw, reset_token = NULL, updated_at = NOW() WHERE id = :id");
                    $stmt->execute([':pw' => $hash, ':id' => $target]);
                    setFlash('success', 'Password reset for user #' . $target . '.');
                }
                break;

            case 'delete':
                // Cascades: shops, products, product_images, orders, order_items.
                $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
                $stmt->execute([':id' => $target]);
                setFlash('success', 'User #' . $target . ' deleted (their shop, products and orders too).');
                break;

            default:
                setFlash('error', 'Unknown action.');
        }
    } catch (PDOException $e) {
        error_log("User management error: " . $e->getMessage());
        setFlash('error', 'Something went wrong. Please try again.');
    }

    // Preserve current list view after the action.
    redirect(ADMIN_URL . '/users.php?' . http_build_query(array_filter([
        'q'        => $_POST['q'] ?? '',
        'role'     => $_POST['f_role'] ?? '',
        'verified' => $_POST['f_verified'] ?? '',
        'page'     => $_POST['page'] ?? '',
    ])));
}

// ── Read filters ──
$q        = trim($_GET['q'] ?? '');
$role     = $_GET['role'] ?? '';
$verified = $_GET['verified'] ?? '';
$page     = max(1, (int) ($_GET['page'] ?? 1));

if (!in_array($role, getRoles(), true)) $role = '';
if (!in_array($verified, ['0', '1'], true)) $verified = '';

// ── Build query ──
$where  = [];
$params = [];

if ($q !== '') {
    $where[] = "(u.username LIKE :q OR u.email LIKE :q OR u.first_name LIKE :q OR u.last_name LIKE :q OR u.id = :qid)";
    $params[':q']   = '%' . $q . '%';
    $params[':qid'] = (int) $q;
}
if ($role !== '') {
    $where[] = 'u.role = :role';
    $params[':role'] = $role;
}
if ($verified !== '') {
    $where[] = 'u.email_verified = :verified';
    $params[':verified'] = (int) $verified;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

$totalSql = "SELECT COUNT(*) FROM users u" . $whereSql;
$stmt = $db->prepare($totalSql);
$stmt->execute($params);
$totalUsers = (int) $stmt->fetchColumn();

$totalPages = max(1, (int) ceil($totalUsers / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$listSql = "SELECT u.id, u.first_name, u.last_name, u.username, u.email, u.gender,
                   u.role, u.email_verified, u.created_at,
                   (SELECT COUNT(*) FROM shops s WHERE s.user_id = u.id) AS shop_count,
                   (SELECT COUNT(*) FROM products p
                      JOIN shops s ON s.id = p.shop_id
                     WHERE s.user_id = u.id) AS product_count
              FROM users u" . $whereSql . "
             ORDER BY u.created_at DESC, u.id DESC
             LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($listSql);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

function buildListQuery($extra = []) {
    global $q, $role, $verified;
    return http_build_query(array_filter(array_merge([
        'q'        => $q,
        'role'     => $role,
        'verified' => $verified,
    ], $extra)));
}

$pageTitle = 'Users';
$active    = 'users';

require __DIR__ . '/partials/header.php';
?>

<div class="admin-page-head">
  <div>
    <h1>User Management</h1>
    <p><?= $totalUsers ?> account<?= $totalUsers === 1 ? '' : 's' ?> found.</p>
  </div>
</div>

<div class="admin-card">
  <form class="toolbar" method="GET" action="<?= ADMIN_URL ?>/users.php">
    <input type="search" name="q" placeholder="Search username, email, name or ID…" value="<?= sanitize($q) ?>" />
    <select name="role">
      <option value="">All roles</option>
      <?php foreach (getRoles() as $r): ?>
        <option value="<?= $r ?>" <?= $role === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="verified">
      <option value="">Any email status</option>
      <option value="1" <?= $verified === '1' ? 'selected' : '' ?>>Verified</option>
      <option value="0" <?= $verified === '0' ? 'selected' : '' ?>>Unverified</option>
    </select>
    <button type="submit" class="admin-btn">Filter</button>
    <?php if ($q !== '' || $role !== '' || $verified !== ''): ?>
      <a class="admin-btn admin-btn-ghost" href="<?= ADMIN_URL ?>/users.php">Clear</a>
    <?php endif; ?>
  </form>

  <?php if (empty($users)): ?>
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
      </svg>
      <div>No users match your filters.</div>
    </div>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Role</th>
            <th>Email status</th>
            <th>Shop / Products</th>
            <th>Registered</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= (int) $u['id'] ?></td>
              <td class="user-cell">
                <div class="user-name">
                  <?= sanitize($u['first_name'] . ' ' . $u['last_name']) ?>
                </div>
                <div class="user-email">@<?= sanitize($u['username']) ?> · <?= sanitize($u['email']) ?></div>
              </td>
              <td><span class="badge badge-<?= sanitize($u['role']) ?>"><?= sanitize($u['role']) ?></span></td>
              <td>
                <?php if ($u['email_verified']): ?>
                  <span class="badge badge-verified">Verified</span>
                <?php else: ?>
                  <span class="badge badge-unverified">Unverified</span>
                <?php endif; ?>
              </td>
              <td><?= (int) $u['shop_count'] ?> / <?= (int) $u['product_count'] ?></td>
              <td><?= sanitize(date('M j, Y', strtotime($u['created_at']))) ?></td>
              <td>
                <div class="row-actions">
                  <form class="inline-form" method="POST" action="<?= ADMIN_URL ?>/users.php">
                    <input type="hidden" name="csrf" value="<?= getCSRF() ?>" />
                    <input type="hidden" name="action" value="role" />
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>" />
                    <input type="hidden" name="q" value="<?= sanitize($q) ?>" />
                    <input type="hidden" name="f_role" value="<?= sanitize($role) ?>" />
                    <input type="hidden" name="f_verified" value="<?= sanitize($verified) ?>" />
                    <input type="hidden" name="page" value="<?= $page ?>" />
                    <select name="role" aria-label="Change role">
                      <?php foreach (getRoles() as $r): ?>
                        <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="admin-btn admin-btn-sm">Set</button>
                  </form>

                  <form method="POST" action="<?= ADMIN_URL ?>/users.php">
                    <input type="hidden" name="csrf" value="<?= getCSRF() ?>" />
                    <input type="hidden" name="action" value="<?= $u['email_verified'] ? 'unverify' : 'verify' ?>" />
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>" />
                    <input type="hidden" name="q" value="<?= sanitize($q) ?>" />
                    <input type="hidden" name="f_role" value="<?= sanitize($role) ?>" />
                    <input type="hidden" name="f_verified" value="<?= sanitize($verified) ?>" />
                    <input type="hidden" name="page" value="<?= $page ?>" />
                    <button type="submit" class="admin-btn admin-btn-sm <?= $u['email_verified'] ? 'admin-btn-ghost' : '' ?>">
                      <?= $u['email_verified'] ? 'Unverify' : 'Verify' ?>
                    </button>
                  </form>

                  <form method="POST" action="<?= ADMIN_URL ?>/users.php"
                        onsubmit="return confirm('Reset password for @<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>?')">
                    <input type="hidden" name="csrf" value="<?= getCSRF() ?>" />
                    <input type="hidden" name="action" value="reset_password" />
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>" />
                    <input type="hidden" name="q" value="<?= sanitize($q) ?>" />
                    <input type="hidden" name="f_role" value="<?= sanitize($role) ?>" />
                    <input type="hidden" name="f_verified" value="<?= sanitize($verified) ?>" />
                    <input type="hidden" name="page" value="<?= $page ?>" />
                    <span class="reset-row">
                      <input type="text" name="new_password" placeholder="New password" required />
                      <button type="submit" class="admin-btn admin-btn-sm admin-btn-ghost">Reset</button>
                    </span>
                  </form>

                  <form method="POST" action="<?= ADMIN_URL ?>/users.php"
                          onsubmit="return confirm('Permanently delete @<?= htmlspecialchars($u['username'], ENT_QUOTES) ?> and all their data (shop, products, orders)?')">
                      <input type="hidden" name="csrf" value="<?= getCSRF() ?>" />
                      <input type="hidden" name="action" value="delete" />
                      <input type="hidden" name="id" value="<?= (int) $u['id'] ?>" />
                      <input type="hidden" name="q" value="<?= sanitize($q) ?>" />
                      <input type="hidden" name="f_role" value="<?= sanitize($role) ?>" />
                      <input type="hidden" name="f_verified" value="<?= sanitize($verified) ?>" />
                      <input type="hidden" name="page" value="<?= $page ?>" />
                      <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Delete</button>
                    </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="<?= ADMIN_URL ?>/users.php?<?= buildListQuery(['page' => $page - 1]) ?>">← Prev</a>
        <?php endif; ?>

        <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
        ?>
          <?php if ($i === $page): ?>
            <span class="current"><?= $i ?></span>
          <?php else: ?>
            <a href="<?= ADMIN_URL ?>/users.php?<?= buildListQuery(['page' => $i]) ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a href="<?= ADMIN_URL ?>/users.php?<?= buildListQuery(['page' => $page + 1]) ?>">Next →</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>