<?php

declare(strict_types=1);


require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/database.php';

if (empty($_SESSION['user']['id'])) {
    header('Location: ' . url('login.php'));
    exit;
}

$db = Database::getInstance();
$userId = (int) $_SESSION['user']['id'];
$orderId = (int) ($_GET['id'] ?? 0);

if ($orderId <= 0) {
    header('Location: ' . url('orders.php'));
    exit;
}

$stmt = $db->prepare("
    SELECT id, order_number, total_amount, payment_status, order_status, created_at
    FROM orders
    WHERE id = :id AND user_id = :user_id
    LIMIT 1
");
$stmt->execute([
    'id' => $orderId,
    'user_id' => $userId,
]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . url('orders.php'));
    exit;
}

$itemsStmt = $db->prepare("
    SELECT 
        oi.product_id,
        oi.quantity,
        oi.unit_price,
        (oi.quantity * oi.unit_price) AS total_price,
        p.name AS product_name,
        p.slug AS product_slug,
        p.image AS product_image
    FROM order_items oi
    LEFT JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = :order_id
    ORDER BY oi.id ASC
");

$itemsStmt->execute(['order_id' => $orderId]);
$orderItems = $itemsStmt->fetchAll();

$pageTitle = 'Order Details';
$currentPage = '';

require __DIR__ . '/app/Views/partials/header.php';
?>

<style>
    .account-page {
        padding: 56px 0 80px;
        background: #f3ede8;
    }

    .account-shell {
        max-width: 1000px;
        margin: 0 auto;
    }

    .account-card {
        background: #ffffff;
        border: 1px solid #e6dbd1;
        border-radius: 24px;
        padding: 32px;
        box-shadow: 0 14px 34px rgba(34, 24, 19, 0.06);
    }

    .account-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .account-header h1 {
        margin: 0 0 8px;
        font-family: 'Cormorant Garamond', serif;
        font-size: 3rem;
        line-height: 0.95;
        color: #201713;
    }

    .account-header p {
        margin: 0;
        color: #6f5e53;
        font-size: 0.96rem;
        line-height: 1.7;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #2b1d18;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.92rem;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .order-meta-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .order-meta-card {
        background: #fbf8f5;
        border: 1px solid #eee3d9;
        border-radius: 18px;
        padding: 16px;
    }

    .order-meta-label {
        display: block;
        margin-bottom: 6px;
        color: #7c6a5f;
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .order-meta-value {
        color: #2b1d18;
        font-size: 0.98rem;
        font-weight: 700;
        line-height: 1.5;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border: 1px solid transparent;
    }

    .status-pill--paid,
    .status-pill--delivered {
        background: #e8f6ec;
        color: #24613c;
        border-color: #c6e5cf;
    }

    .status-pill--pending,
    .status-pill--processing {
        background: #fff4d9;
        color: #7a5d00;
        border-color: #f2dfb3;
    }

    .status-pill--failed,
    .status-pill--cancelled {
        background: #fdeceb;
        color: #8c2c2c;
        border-color: #f2c5c2;
    }

    .order-items {
        display: grid;
        gap: 16px;
    }

    .order-item {
        display: grid;
        grid-template-columns: 92px 1fr auto;
        gap: 18px;
        align-items: center;
        background: #fffdfa;
        border: 1px solid #eee3d9;
        border-radius: 18px;
        padding: 16px;
    }

    .order-item-image {
        width: 92px;
        height: 92px;
        border-radius: 16px;
        overflow: hidden;
        background: #f4ede7;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .order-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .order-item-placeholder {
        color: #8b776b;
        font-size: 0.85rem;
        text-align: center;
        padding: 8px;
    }

    .order-item-title {
        margin: 0 0 6px;
        color: #241914;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.4;
    }

    .order-item-title a {
        color: inherit;
        text-decoration: none;
    }

    .order-item-title a:hover {
        text-decoration: underline;
    }

    .order-item-meta {
        color: #6f5e53;
        font-size: 0.92rem;
        line-height: 1.7;
    }

    .order-item-price {
        text-align: right;
        color: #2b1d18;
    }

    .order-item-price strong {
        display: block;
        font-size: 1rem;
    }

    .order-item-price span {
        display: block;
        margin-top: 4px;
        color: #7c6a5f;
        font-size: 0.85rem;
    }

    .order-actions {
        margin-top: 24px;
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .order-action-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 0.92rem;
        font-weight: 700;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .order-action-link--primary {
        background: #16110f;
        color: #ffffff;
    }

    .order-action-link--secondary {
        background: #f4ede7;
        color: #241914;
        border: 1px solid #e1d3c7;
    }

    .order-action-link:hover {
        transform: translateY(-1px);
        opacity: 0.95;
    }

    @media (max-width: 768px) {
        .account-page {
            padding: 36px 0 60px;
        }

        .account-card {
            padding: 22px;
            border-radius: 18px;
        }

        .account-header h1 {
            font-size: 2.3rem;
        }

        .order-meta-grid {
            grid-template-columns: 1fr 1fr;
        }

        .order-item {
            grid-template-columns: 1fr;
            align-items: flex-start;
        }

        .order-item-image {
            width: 100%;
            height: 220px;
        }

        .order-item-price {
            text-align: left;
        }

        .order-actions {
            flex-direction: column;
        }

        .order-action-link {
            width: 100%;
        }
    }
</style>

<?php
$paymentStatus = strtolower((string) ($order['payment_status'] ?? ''));
$orderStatus = strtolower((string) ($order['order_status'] ?? ''));

$paymentClass = 'status-pill';
$orderClass = 'status-pill';

if (in_array($paymentStatus, ['paid'], true)) {
    $paymentClass .= ' status-pill--paid';
} elseif (in_array($paymentStatus, ['pending'], true)) {
    $paymentClass .= ' status-pill--pending';
} elseif (in_array($paymentStatus, ['failed', 'cancelled'], true)) {
    $paymentClass .= ' status-pill--failed';
}

if (in_array($orderStatus, ['delivered', 'completed'], true)) {
    $orderClass .= ' status-pill--delivered';
} elseif (in_array($orderStatus, ['pending', 'processing'], true)) {
    $orderClass .= ' status-pill--pending';
} elseif (in_array($orderStatus, ['cancelled'], true)) {
    $orderClass .= ' status-pill--cancelled';
}
?>

<main id="main-content">
    <section class="account-page">
        <div class="container">
            <div class="account-shell">
                <div class="account-card">
                    <div class="account-header">
                        <div>
                            <a class="back-link" href="<?= e(url('orders.php')) ?>">← Back to My Orders</a>
                            <h1>Order Details</h1>
                            <p>Review the items you purchased and the current progress of this order.</p>
                        </div>
                    </div>

                    <div class="order-meta-grid">
                        <div class="order-meta-card">
                            <span class="order-meta-label">Order Number</span>
                            <div class="order-meta-value"><?= e($order['order_number'] ?? '') ?></div>
                        </div>

                        <div class="order-meta-card">
                            <span class="order-meta-label">Total</span>
                            <div class="order-meta-value">GHS <?= e(number_format((float) ($order['total_amount'] ?? 0), 2)) ?></div>
                        </div>

                        <div class="order-meta-card">
                            <span class="order-meta-label">Payment</span>
                            <div class="order-meta-value">
                                <span class="<?= e($paymentClass) ?>"><?= e(ucfirst($paymentStatus ?: '')) ?></span>
                            </div>
                        </div>

                        <div class="order-meta-card">
                            <span class="order-meta-label">Order Status</span>
                            <div class="order-meta-value">
                                <span class="<?= e($orderClass) ?>"><?= e(ucfirst($orderStatus ?: '')) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="order-meta-card" style="margin-bottom: 24px;">
                        <span class="order-meta-label">Placed On</span>
                        <div class="order-meta-value"><?= e($order['created_at'] ?? '') ?></div>
                    </div>

                    <div class="order-items">
                      <div class="order-items">
    <?php if (!$orderItems): ?>
        <div class="order-meta-card">
            <div class="order-meta-value">No items were found for this order.</div>
        </div>
    <?php else: ?>
        <?php foreach ($orderItems as $item): ?>
            ...
        <?php endforeach; ?>
    <?php endif; ?>
</div>
                        <?php foreach ($orderItems as $item): ?>
                            <?php
                            $productName = (string) ($item['product_name'] ?? 'Product');
                            $productSlug = (string) ($item['product_slug'] ?? '');
                            $productImage = trim((string) ($item['product_image'] ?? ''));
                            $productUrl = $productSlug !== '' ? url('product.php?slug=' . urlencode($productSlug)) : '';
                            ?>
                            <article class="order-item">
                                <div class="order-item-image">
                                    <?php if ($productImage !== ''): ?>
                                        
                                        <img src="uploads/product/<?= e($productImage) ?>" alt="<?= e($productName) ?>">
                                    <?php else: ?>
                                        <div class="order-item-placeholder">No image</div>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <h2 class="order-item-title">
                                        <?php if ($productUrl !== ''): ?>
                                            <a href="<?= e($productUrl) ?>"><?= e($productName) ?></a>
                                        <?php else: ?>
                                            <?= e($productName) ?>
                                        <?php endif; ?>
                                    </h2>
                                    <div class="order-item-meta">
                                        Quantity: <?= e((string) ($item['quantity'] ?? 0)) ?><br>
                                        Unit price: GHS <?= e(number_format((float) ($item['unit_price'] ?? 0), 2)) ?>
                                    </div>
                                </div>

                                <div class="order-item-price">
                                    <strong>GHS <?= e(number_format((float) ($item['total_price'] ?? 0), 2)) ?></strong>
                                    <span>Item subtotal</span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-actions">
                        <a class="order-action-link order-action-link--primary" href="<?= e(url('order-status.php?order_number=' . urlencode((string) ($order['order_number'] ?? '')))) ?>">
                            Track This Order
                        </a>
                        <a class="order-action-link order-action-link--secondary" href="<?= e(url('orders.php')) ?>">
                            Back to Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>