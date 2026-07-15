<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// This file is in htdocs/admin, so go up one level to reach shared stuff

// Require admin guard (stays inside admin/)
require __DIR__ . '/require_admin.php';

$pageTitle = 'Admin Dashboard';
$currentPage = 'dashboard';

// Use shared header from app/Views
require __DIR__ . '/../app/Views/partials/admin_header.php';
?>

<section class="admin-dashboard-section">
    <div class="container">
        <div class="admin-dashboard-shell">
            <div class="admin-dashboard-hero">
                <div>
                    <span class="admin-dashboard-kicker">Admin Dashboard</span>
                    <h1>Welcome back</h1>
                    <p>Manage users, monitor access, and keep the RarityByAdel store organized from one clear admin space.</p>
                </div>

                <div class="admin-dashboard-hero-action">
                    <a href="<?= url('admin/users.php') ?>" class="admin-dashboard-btn admin-dashboard-btn-primary">Manage Users</a>
                </div>
            </div>

            <div class="admin-dashboard-grid">
                <article class="admin-dashboard-card">
                    <h2>User Management</h2>
                    <p>Review registered users, check roles, and control which accounts have admin access.</p>
                    <a href="<?= url('admin/users.php') ?>" class="admin-dashboard-link">Open users page</a>
                </article>

                <article class="admin-dashboard-card">
                    <h2>Storefront Access</h2>
                    <p>Return to the public site anytime to review how the store looks for customers.</p>
                    <a href="<?= url('index.php') ?>" class="admin-dashboard-link">Visit storefront</a>
                </article>
            </div>
        </div>
    </div>
</section>

<style>
.admin-dashboard-section {
    padding: 56px 0 80px;
    background: linear-gradient(180deg, #fffaf6 0%, #f7efe9 100%);
}

.admin-dashboard-shell {
    display: grid;
    gap: 24px;
}

.admin-dashboard-hero {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.admin-dashboard-kicker {
    display: inline-block;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--color-text-soft);
}

.admin-dashboard-hero h1 {
    margin: 0 0 10px;
    font-family: var(--font-heading);
    font-size: clamp(2rem, 4vw, 3.5rem);
    line-height: 0.98;
}

.admin-dashboard-hero p {
    margin: 0;
    max-width: 60ch;
    color: var(--color-text-soft);
}

.admin-dashboard-btn {
    min-height: 48px;
    padding: 0 18px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-weight: 800;
}

.admin-dashboard-btn-primary {
    background: #111;
    color: #fff;
}

.admin-dashboard-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.admin-dashboard-card {
    padding: 24px;
    border-radius: 28px;
    background: rgba(255,255,255,0.94);
    border: 1px solid rgba(17,17,17,0.08);
    box-shadow: var(--shadow-soft);
}

.admin-dashboard-card h2 {
    margin: 0 0 10px;
    font-family: var(--font-heading);
    font-size: 1.5rem;
}

.admin-dashboard-card p {
    margin: 0 0 16px;
    color: var(--color-text-soft);
    max-width: 44ch;
}

.admin-dashboard-link {
    text-decoration: none;
    color: var(--color-text);
    font-weight: 800;
}

@media (max-width: 768px) {
    .admin-dashboard-section {
        padding: 42px 0 64px;
    }

    .admin-dashboard-grid {
        grid-template-columns: 1fr;
    }

    .admin-dashboard-card {
        padding: 20px;
        border-radius: 22px;
    }
}
</style>

<?php require __DIR__ . '/../app/Views/partials/footer.php'; ?>