<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/require_admin.php';
// remove the database.php line entirely

$pageTitle = 'Manage Users';
$currentPage = 'users';

$users = [];
$loadError = '';

$currentAdminId = (int) ($_SESSION['user']['id'] ?? 0);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

$flashMessage = $_SESSION['flash_message'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_message'], $_SESSION['flash_error']);

try {
    $db = Database::getInstance();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postedToken = $_POST['csrf_token'] ?? '';
        $action = $_POST['action'] ?? '';
        $userId = (int) ($_POST['user_id'] ?? 0);

        if (!is_string($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
            $_SESSION['flash_error'] = 'Security check failed. Please try again.';
        } elseif ($userId <= 0) {
            $_SESSION['flash_error'] = 'Invalid user selected.';
        } elseif ($userId === $currentAdminId && in_array($action, ['delete', 'remove_admin'], true)) {
            $_SESSION['flash_error'] = 'You cannot remove or delete your own current admin account.';
        } else {
            if ($action === 'delete') {
                $stmt = $db->prepare('DELETE FROM users WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $userId]);

                if ($stmt->rowCount() > 0) {
                    $_SESSION['flash_message'] = 'User deleted successfully.';
                } else {
                    $_SESSION['flash_error'] = 'User not found or already removed.';
                }
            } elseif ($action === 'make_admin') {
                $stmt = $db->prepare('UPDATE users SET is_admin = 1 WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $userId]);

                $_SESSION['flash_message'] = 'User promoted to admin successfully.';
            } elseif ($action === 'remove_admin') {
                $stmt = $db->prepare('UPDATE users SET is_admin = 0 WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $userId]);

                $_SESSION['flash_message'] = 'Admin access removed successfully.';
            } else {
                $_SESSION['flash_error'] = 'Invalid action requested.';
            }
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: ' . url('admin/users.php'));
        exit;
    }

    $stmt = $db->query('
        SELECT id, full_name, email, is_admin, created_at
        FROM users
        ORDER BY created_at DESC, id DESC
    ');

    $users = $stmt->fetchAll();
    $csrfToken = $_SESSION['csrf_token'];
} catch (Throwable $e) {
    $loadError = 'Unable to load users right now.';
}

require __DIR__ . '/../app/Views/partials/admin_header.php';
?>

<section class="admin-users-section">
    <div class="container">
        <div class="admin-users-shell">
            <div class="admin-users-header">
                <div>
                    <span class="admin-users-kicker">Admin Panel</span>
                    <h1>Manage Users</h1>
                    <p>View registered accounts, update admin access, and remove users when needed.</p>
                </div>
            </div>

            <?php if ($flashMessage !== ''): ?>
                <div class="admin-users-alert admin-users-alert-success"><?= e($flashMessage) ?></div>
            <?php endif; ?>

            <?php if ($flashError !== ''): ?>
                <div class="admin-users-alert admin-users-alert-error"><?= e($flashError) ?></div>
            <?php endif; ?>

            <?php if ($loadError !== ''): ?>
                <div class="admin-users-alert admin-users-alert-error"><?= e($loadError) ?></div>
            <?php endif; ?>

            <div class="admin-users-card">
                <div class="admin-users-card-head">
                    <div>
                        <h2>Registered Users</h2>
                        <p>All accounts are listed from newest to oldest.</p>
                    </div>
                    <span class="admin-users-count"><?= e((string) count($users)) ?> total</span>
                </div>

                <?php if (empty($users)): ?>
                    <div class="admin-users-empty">
                        <p>No users found yet.</p>
                    </div>
                <?php else: ?>
                    <div class="admin-users-table-wrap">
                        <table class="admin-users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $userId = (int) $user['id'];
                                    $isTargetAdmin = (int) $user['is_admin'] === 1;
                                    $isCurrentAdmin = $userId === $currentAdminId;
                                    ?>
                                    <tr>
                                        <td>#<?= e((string) $userId) ?></td>
                                        <td><?= e((string) $user['full_name']) ?></td>
                                        <td><?= e((string) $user['email']) ?></td>
                                        <td>
                                            <?php if ($isTargetAdmin): ?>
                                                <span class="admin-users-badge admin-users-badge-admin">Admin</span>
                                            <?php else: ?>
                                                <span class="admin-users-badge admin-users-badge-customer">Customer</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e(date('M d, Y h:i A', strtotime((string) $user['created_at']))) ?></td>
                                        <td>
                                            <?php if ($isCurrentAdmin): ?>
                                                <span class="admin-users-self-note">Current account</span>
                                            <?php else: ?>
                                                <div class="admin-users-actions">
                                                    <?php if ($isTargetAdmin): ?>
                                                        <form method="POST" class="admin-users-inline-form" onsubmit="return confirm('Remove admin access from this user?');">
                                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                            <input type="hidden" name="user_id" value="<?= e((string) $userId) ?>">
                                                            <input type="hidden" name="action" value="remove_admin">
                                                            <button type="submit" class="admin-users-action-btn admin-users-action-btn-secondary">Remove Admin</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form method="POST" class="admin-users-inline-form">
                                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                            <input type="hidden" name="user_id" value="<?= e((string) $userId) ?>">
                                                            <input type="hidden" name="action" value="make_admin">
                                                            <button type="submit" class="admin-users-action-btn admin-users-action-btn-primary">Make Admin</button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <form method="POST" class="admin-users-inline-form" onsubmit="return confirm('Delete this user account? This action cannot be undone.');">
                                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                        <input type="hidden" name="user_id" value="<?= e((string) $userId) ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="admin-users-action-btn admin-users-action-btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.admin-users-section {
    padding: 56px 0 80px;
    background: linear-gradient(180deg, #fffaf6 0%, #f7efe9 100%);
}

.admin-users-shell {
    display: grid;
    gap: 24px;
}

.admin-users-header {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 20px;
    flex-wrap: wrap;
}

.admin-users-kicker {
    display: inline-block;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--color-text-soft);
}

.admin-users-header h1 {
    margin: 0 0 10px;
    font-family: var(--font-heading);
    font-size: clamp(2rem, 4vw, 3.4rem);
    line-height: 0.98;
}

.admin-users-header p {
    margin: 0;
    color: var(--color-text-soft);
    max-width: 58ch;
}

.admin-users-alert {
    padding: 14px 16px;
    border-radius: 16px;
    font-weight: 700;
}

.admin-users-alert-success {
    background: rgba(34, 110, 52, 0.10);
    border: 1px solid rgba(34, 110, 52, 0.18);
    color: #225c31;
}

.admin-users-alert-error {
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.18);
    color: #8f1f14;
}

.admin-users-card {
    background: rgba(255,255,255,0.94);
    border: 1px solid rgba(17,17,17,0.08);
    border-radius: 28px;
    box-shadow: var(--shadow-soft);
    overflow: hidden;
}

.admin-users-card-head {
    padding: 24px;
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: center;
    border-bottom: 1px solid rgba(17,17,17,0.08);
    flex-wrap: wrap;
}

.admin-users-card-head h2 {
    margin: 0 0 6px;
    font-family: var(--font-heading);
    font-size: 1.6rem;
}

.admin-users-card-head p {
    margin: 0;
    color: var(--color-text-soft);
}

.admin-users-count {
    color: var(--color-text-soft);
    font-weight: 800;
}

.admin-users-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.admin-users-table {
    width: 100%;
    min-width: 980px;
    border-collapse: collapse;
}

.admin-users-table th,
.admin-users-table td {
    padding: 16px 24px;
    text-align: left;
    border-bottom: 1px solid rgba(17,17,17,0.06);
    vertical-align: middle;
}

.admin-users-table th {
    font-size: 0.84rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--color-text-soft);
}

.admin-users-table td {
    font-weight: 600;
    color: var(--color-text);
}

.admin-users-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 32px;
    padding: 0 12px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 800;
}

.admin-users-badge-admin {
    background: rgba(17,17,17,0.1);
    color: #111;
}

.admin-users-badge-customer {
    background: rgba(17,17,17,0.06);
    color: #444;
}

.admin-users-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.admin-users-inline-form {
    margin: 0;
}

.admin-users-action-btn {
    min-height: 38px;
    padding: 0 14px;
    border-radius: 999px;
    font-weight: 800;
    cursor: pointer;
    border: 1px solid transparent;
}

.admin-users-action-btn-primary {
    background: #111;
    color: #fff;
    border-color: #111;
}

.admin-users-action-btn-secondary {
    background: rgba(17,17,17,0.06);
    color: #111;
    border-color: rgba(17,17,17,0.08);
}

.admin-users-action-btn-danger {
    border: 1px solid rgba(180, 35, 24, 0.18);
    background: rgba(180, 35, 24, 0.08);
    color: #8f1f14;
}

.admin-users-self-note {
    color: var(--color-text-soft);
    font-weight: 700;
    font-size: 0.92rem;
}

.admin-users-empty {
    padding: 32px 24px;
    color: var(--color-text-soft);
}

@media (max-width: 768px) {
    .admin-users-section {
        padding: 42px 0 64px;
    }

    .admin-users-card-head,
    .admin-users-table th,
    .admin-users-table td {
        padding-left: 16px;
        padding-right: 16px;
    }
}
</style>

<?php require __DIR__ . '/../app/Views/partials/footer.php'; ?>