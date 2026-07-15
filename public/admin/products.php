<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/require_admin.php';
// remove the database.php line entirely

$pageTitle = 'Manage Products';
$currentPage = 'products';

$products = [];
$loadError = '';

$successMessage = '';
$errorMessage = '';

if (!empty($_SESSION['success_message'])) {
    $successMessage = (string) $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (!empty($_SESSION['error_message'])) {
    $errorMessage = (string) $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

try {
    $db = Database::getInstance();

    $stmt = $db->query("
        SELECT
            p.id,
            p.name,
            p.slug,
            p.price,
            p.stock_quantity,
            p.image,
            p.is_featured,
            p.is_active,
            p.created_at,
            c.name AS category_name
        FROM products p
        INNER JOIN categories c ON c.id = p.category_id
        ORDER BY p.created_at DESC, p.id DESC
    ");

    $products = $stmt->fetchAll();
} catch (Throwable $e) {
    $loadError = 'Unable to load products right now.';
}

require __DIR__ . '/../app/Views/partials/admin_header.php';
?>

<section class="admin-products-section">
    <div class="container">
        <div class="admin-products-shell">
            <div class="admin-products-header">
                <div>
                    <span class="admin-products-kicker">Admin Panel</span>
                    <h1>Manage Products</h1>
                    <p>View your catalog, track stock levels, and manage product visibility from one place.</p>
                </div>

                <div class="admin-products-header-actions">
                    <a href="<?= url('admin/product_create.php') ?>" class="admin-products-btn admin-products-btn-primary">Add Product</a>
                </div>
            </div>

            <?php if ($successMessage !== ''): ?>
                <div class="admin-products-alert admin-products-alert-success"><?= e($successMessage) ?></div>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="admin-products-alert admin-products-alert-error"><?= e($errorMessage) ?></div>
            <?php endif; ?>

            <?php if ($loadError !== ''): ?>
                <div class="admin-products-alert admin-products-alert-error"><?= e($loadError) ?></div>
            <?php endif; ?>

            <div class="admin-products-card">
                <div class="admin-products-card-head">
                    <div>
                        <h2>Product Catalog</h2>
                        <p>All products currently available in your store database.</p>
                    </div>
                    <span class="admin-products-count"><?= e((string) count($products)) ?> total</span>
                </div>

                <?php if (empty($products)): ?>
                    <div class="admin-products-empty">
                        <p>No products found yet.</p>
                    </div>
                <?php else: ?>
                    <div class="admin-products-table-wrap">
                        <table class="admin-products-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                    $productId = (int) $product['id'];
                                    $isFeatured = (int) $product['is_featured'] === 1;
                                    $isActive = (int) $product['is_active'] === 1;
                                    $stockQty = (int) $product['stock_quantity'];

                                    if ($stockQty <= 0) {
                                        $stockClass = 'admin-products-stock-out';
                                        $stockLabel = 'Out of stock';
                                    } elseif ($stockQty <= 3) {
                                        $stockClass = 'admin-products-stock-low';
                                        $stockLabel = $stockQty . ' low stock';
                                    } else {
                                        $stockClass = 'admin-products-stock-in';
                                        $stockLabel = $stockQty . ' in stock';
                                    }

                                    $imageSrc = '';
                                    if (!empty($product['image'])) {
                                        $diskImagePath = __DIR__ . '/../uploads/products/' . $product['image'];

                                        if (is_file($diskImagePath)) {
                                            $imageSrc = url('uploads/products/' . $product['image']);
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td>#<?= e((string) $productId) ?></td>
                                        <td>
                                            <div class="admin-products-name-cell">
                                                <div class="admin-products-thumb" aria-hidden="true">
                                                    <?php if ($imageSrc !== ''): ?>
                                                        <img src="<?= e($imageSrc) ?>" alt="<?= e((string) $product['name']) ?>">
                                                    <?php else: ?>
                                                        <span>IMG</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <strong class="admin-products-name"><?= e((string) $product['name']) ?></strong>
                                                    <span class="admin-products-slug"><?= e((string) $product['slug']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= e((string) $product['category_name']) ?></td>
                                        <td>₵<?= e(number_format((float) $product['price'], 2)) ?></td>
                                        <td>
                                            <span class="admin-products-stock <?= e($stockClass) ?>">
                                                <?= e($stockLabel) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($isFeatured): ?>
                                                <span class="admin-products-badge admin-products-badge-featured">Featured</span>
                                            <?php else: ?>
                                                <span class="admin-products-badge admin-products-badge-muted">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($isActive): ?>
                                                <span class="admin-products-badge admin-products-badge-active">Active</span>
                                            <?php else: ?>
                                                <span class="admin-products-badge admin-products-badge-inactive">Hidden</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e(date('M d, Y', strtotime((string) $product['created_at']))) ?></td>
                                        <td>
                                            <div class="admin-products-actions">
                                                <a href="<?= url('admin/product_edit.php?id=' . $productId) ?>" class="admin-products-action admin-products-action-edit">Edit</a>

                                                <a href="<?= url('product.php?id=' . $productId) ?>" class="admin-products-action admin-products-action-view" target="_blank" rel="noopener noreferrer">View</a>

                                                <form action="<?= url('admin/product_delete.php') ?>" method="POST" class="admin-products-delete-form" onsubmit="return confirm('Delete this product?');">
                                                    <input type="hidden" name="id" value="<?= e((string) $productId) ?>">
                                                    <input type="hidden" name="mode" value="soft">
                                                    <button type="submit" class="admin-products-action admin-products-action-delete">Delete</button>
                                                </form>
                                            </div>
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
.admin-products-section {
    padding: 56px 0 80px;
    background: linear-gradient(180deg, #fffaf6 0%, #f7efe9 100%);
}

.admin-products-shell {
    display: grid;
    gap: 24px;
}

.admin-products-header {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 20px;
    flex-wrap: wrap;
}

.admin-products-kicker {
    display: inline-block;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--color-text-soft);
}

.admin-products-header h1 {
    margin: 0 0 10px;
    font-family: var(--font-heading);
    font-size: clamp(2rem, 4vw, 3.4rem);
    line-height: 0.98;
}

.admin-products-header p {
    margin: 0;
    color: var(--color-text-soft);
    max-width: 58ch;
}

.admin-products-header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.admin-products-btn {
    min-height: 46px;
    padding: 0 18px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-weight: 800;
}

.admin-products-btn-primary {
    background: #111;
    color: #fff;
}

.admin-products-alert {
    padding: 14px 16px;
    border-radius: 16px;
    font-weight: 700;
}

.admin-products-alert-success {
    background: rgba(34, 110, 52, 0.10);
    border: 1px solid rgba(34, 110, 52, 0.18);
    color: #225c31;
}

.admin-products-alert-error {
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.18);
    color: #8f1f14;
}

.admin-products-card {
    background: rgba(255,255,255,0.94);
    border: 1px solid rgba(17,17,17,0.08);
    border-radius: 28px;
    box-shadow: var(--shadow-soft);
    overflow: hidden;
}

.admin-products-card-head {
    padding: 24px;
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: center;
    border-bottom: 1px solid rgba(17,17,17,0.08);
    flex-wrap: wrap;
}

.admin-products-card-head h2 {
    margin: 0 0 6px;
    font-family: var(--font-heading);
    font-size: 1.6rem;
}

.admin-products-card-head p {
    margin: 0;
    color: var(--color-text-soft);
}

.admin-products-count {
    color: var(--color-text-soft);
    font-weight: 800;
}

.admin-products-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.admin-products-table {
    width: 100%;
    min-width: 1180px;
    border-collapse: collapse;
}

.admin-products-table th,
.admin-products-table td {
    padding: 16px 24px;
    text-align: left;
    border-bottom: 1px solid rgba(17,17,17,0.06);
    vertical-align: middle;
}

.admin-products-table th {
    font-size: 0.84rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--color-text-soft);
}

.admin-products-table td {
    font-weight: 600;
    color: var(--color-text);
}

.admin-products-name-cell {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 240px;
}

.admin-products-thumb {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    overflow: hidden;
    background: rgba(17,17,17,0.06);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-soft);
    font-size: 0.76rem;
    font-weight: 800;
    flex-shrink: 0;
}

.admin-products-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.admin-products-name {
    display: block;
    font-size: 0.98rem;
    margin-bottom: 4px;
}

.admin-products-slug {
    display: block;
    color: var(--color-text-soft);
    font-size: 0.85rem;
    font-weight: 600;
}

.admin-products-stock {
    display: inline-flex;
    align-items: center;
    min-height: 32px;
    padding: 0 12px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 800;
}

.admin-products-stock-in {
    background: rgba(34, 110, 52, 0.10);
    color: #225c31;
}

.admin-products-stock-low {
    background: rgba(171, 121, 54, 0.12);
    color: #8c6126;
}

.admin-products-stock-out {
    background: rgba(180, 35, 24, 0.08);
    color: #8f1f14;
}

.admin-products-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 32px;
    padding: 0 12px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 800;
}

.admin-products-badge-featured {
    background: rgba(17,17,17,0.1);
    color: #111;
}

.admin-products-badge-active {
    background: rgba(34, 110, 52, 0.10);
    color: #225c31;
}

.admin-products-badge-inactive {
    background: rgba(17,17,17,0.06);
    color: #555;
}

.admin-products-badge-muted {
    background: rgba(17,17,17,0.06);
    color: #666;
}

.admin-products-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}

.admin-products-action {
    min-height: 36px;
    padding: 0 14px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-weight: 800;
    font-size: 0.88rem;
    border: none;
    cursor: pointer;
}

.admin-products-action-edit {
    background: rgba(17,17,17,0.06);
    color: #111;
}

.admin-products-action-view {
    background: rgba(17,17,17,0.10);
    color: #111;
}

.admin-products-action-delete {
    background: rgba(180, 35, 24, 0.08);
    color: #8f1f14;
}

.admin-products-delete-form {
    margin: 0;
}

.admin-products-empty {
    padding: 32px 24px;
    color: var(--color-text-soft);
}

@media (max-width: 768px) {
    .admin-products-section {
        padding: 42px 0 64px;
    }

    .admin-products-card-head,
    .admin-products-table th,
    .admin-products-table td {
        padding-left: 16px;
        padding-right: 16px;
    }
}
</style>

<?php require __DIR__ . '/../app/Views/partials/footer.php'; ?>