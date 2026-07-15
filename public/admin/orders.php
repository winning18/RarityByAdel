<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/require_admin.php';
// remove the database.php line entirely

$pageTitle = 'Manage Orders';
$currentPage = 'orders';

$orders = [];
$loadError = '';

try {
    $db = Database::getInstance();

    $stmt = $db->query("
        SELECT
            id,
            order_number,
            customer_name,
            customer_email,
            customer_phone,
            total_amount,
            payment_method,
            payment_status,
            payer_name,
            payment_reference,
            payment_submitted_at,
            order_status,
            created_at
        FROM orders
        ORDER BY created_at DESC, id DESC
    ");

    $orders = $stmt->fetchAll();
} catch (Throwable $e) {
    $loadError = 'Unable to load orders right now.';
}

require __DIR__ . '/../app/Views/partials/admin_header.php';
?>

<section class="admin-orders-section">
    <div class="container">
        <div class="admin-orders-shell">
            <div class="admin-orders-header">
                <div>
                    <span class="admin-orders-kicker">Admin Panel</span>
                    <h1>Manage Orders</h1>
                    <p>Monitor customer purchases, payment states, and fulfillment progress.</p>
                </div>
            </div>

            <?php if ($loadError !== ''): ?>
                <div class="admin-orders-alert admin-orders-alert-error"><?= e($loadError) ?></div>
            <?php endif; ?>

            <div class="admin-orders-card">
                <div class="admin-orders-card-head">
                    <div>
                        <h2>Recent Orders</h2>
                        <p>All customer orders in one place.</p>
                    </div>
                    <span class="admin-orders-count"><?= e((string) count($orders)) ?> total</span>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="admin-orders-empty">
                        <p>No orders found yet.</p>
                    </div>
                <?php else: ?>
                    <div class="admin-orders-table-wrap">
                        <table class="admin-orders-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Payment Method</th>
                                    <th>Payment Info</th>
                                    <th>Payment Status</th>
                                    <th>Order Status</th>
                                    <th>Date</th>
                                    <th class="admin-orders-sticky-col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <?php
                                    $paymentStatus = (string) $order['payment_status'];
                                    $orderStatus = (string) $order['order_status'];

                                    $paymentClass = match ($paymentStatus) {
                                        'paid' => 'badge-paid',
                                        'failed', 'refunded' => 'badge-danger',
                                        default => 'badge-pending',
                                    };

                                    $orderClass = match ($orderStatus) {
                                        'delivered' => 'badge-paid',
                                        'cancelled' => 'badge-danger',
                                        'processing', 'shipped' => 'badge-progress',
                                        default => 'badge-pending',
                                    };
                                    ?>
                                    <tr>
                                        <td>
                                            <strong class="admin-orders-order-number">#<?= e((string) $order['order_number']) ?></strong>
                                        </td>
                                        <td>
                                            <div class="admin-orders-customer">
                                                <strong><?= e((string) $order['customer_name']) ?></strong>
                                                <span><?= e((string) $order['customer_email']) ?></span>
                                                <?php if (!empty($order['customer_phone'])): ?>
                                                    <span><?= e((string) $order['customer_phone']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>₵<?= e(number_format((float) $order['total_amount'], 2)) ?></td>
                                        <td><?= e(ucfirst((string) $order['payment_method'])) ?></td>
                                        <td>
                                            <div class="admin-orders-payment-info">
                                                <?php if (!empty($order['payer_name'])): ?>
                                                    <strong><?= e((string) $order['payer_name']) ?></strong>
                                                <?php else: ?>
                                                    <strong class="admin-orders-muted">No payer name</strong>
                                                <?php endif; ?>

                                                <?php if (!empty($order['payment_reference'])): ?>
                                                    <span>Ref: <?= e((string) $order['payment_reference']) ?></span>
                                                <?php else: ?>
                                                    <span class="admin-orders-muted">No reference submitted</span>
                                                <?php endif; ?>

                                                <?php if (!empty($order['payment_submitted_at'])): ?>
                                                    <span class="admin-orders-submitted-at">
                                                        Submitted: <?= e(date('M d, Y h:i A', strtotime((string) $order['payment_submitted_at']))) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="admin-orders-badge <?= e($paymentClass) ?>">
                                                <?= e(ucfirst($paymentStatus)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="admin-orders-badge <?= e($orderClass) ?>">
                                                <?= e(ucfirst($orderStatus)) ?>
                                            </span>
                                        </td>
                                        <td><?= e(date('M d, Y', strtotime((string) $order['created_at']))) ?></td>
                                        <td class="admin-orders-sticky-col">
                                            <div class="admin-orders-actions">
                                                <a href="<?= url('admin/order_view.php?id=' . (int) $order['id']) ?>" class="admin-orders-action">View</a>
                                                <a href="<?= url('admin/order_edit.php?id=' . (int) $order['id']) ?>" class="admin-orders-action admin-orders-action-dark">Manage</a>
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
.admin-orders-section {
    padding: 40px 0 72px;
    background: #f7f1eb;
}

.admin-orders-section .container {
    max-width: 1440px;
}

.admin-orders-shell {
    display: grid;
    gap: 24px;
}

.admin-orders-header h1 {
    margin: 0 0 10px;
    font-family: "Cormorant Garamond", serif;
    font-size: clamp(2rem, 4vw, 3.2rem);
    line-height: 1;
    color: #161616;
}

.admin-orders-kicker {
    display: inline-block;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 0.76rem;
    font-weight: 800;
    color: #8a7768;
}

.admin-orders-header p {
    margin: 0;
    color: #6f6257;
    max-width: 58ch;
}

.admin-orders-alert {
    padding: 14px 16px;
    border-radius: 16px;
    font-weight: 700;
}

.admin-orders-alert-error {
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.18);
    color: #8f1f14;
}

.admin-orders-card {
    background: rgba(255,255,255,0.96);
    border: 1px solid rgba(17,17,17,0.08);
    border-radius: 28px;
    overflow: hidden;
    box-shadow: 0 18px 40px rgba(17,17,17,0.06);
}

.admin-orders-card-head {
    padding: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    border-bottom: 1px solid rgba(17,17,17,0.07);
    flex-wrap: wrap;
}

.admin-orders-card-head h2 {
    margin: 0 0 6px;
    font-size: 1.5rem;
    font-family: "Cormorant Garamond", serif;
}

.admin-orders-card-head p {
    margin: 0;
    color: #6f6257;
}

.admin-orders-count {
    color: #6f6257;
    font-weight: 800;
}

.admin-orders-table-wrap {
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
}

.admin-orders-table {
    width: 100%;
    min-width: 1320px;
    border-collapse: separate;
    border-spacing: 0;
}

.admin-orders-table th,
.admin-orders-table td {
    padding: 16px 24px;
    text-align: left;
    border-bottom: 1px solid rgba(17,17,17,0.06);
    vertical-align: middle;
    background: #fff;
}

.admin-orders-table th {
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8a7768;
    white-space: nowrap;
}

.admin-orders-order-number {
    color: #111;
}

.admin-orders-customer,
.admin-orders-payment-info {
    display: grid;
    gap: 4px;
}

.admin-orders-customer strong,
.admin-orders-payment-info strong {
    color: #111;
}

.admin-orders-customer span,
.admin-orders-payment-info span {
    color: #6f6257;
    font-size: 0.9rem;
}

.admin-orders-muted {
    color: #9b8d82 !important;
}

.admin-orders-submitted-at {
    font-size: 0.82rem;
}

.admin-orders-badge {
    min-height: 32px;
    padding: 0 12px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    font-weight: 800;
    white-space: nowrap;
}

.badge-paid {
    background: rgba(34, 110, 52, 0.10);
    color: #225c31;
}

.badge-danger {
    background: rgba(180, 35, 24, 0.08);
    color: #8f1f14;
}

.badge-pending {
    background: rgba(17,17,17,0.06);
    color: #555;
}

.badge-progress {
    background: rgba(171, 121, 54, 0.12);
    color: #8c6126;
}

.admin-orders-sticky-col {
    position: sticky;
    right: 0;
    z-index: 3;
    background: #fffdfb !important;
    box-shadow: -12px 0 18px rgba(17,17,17,0.05);
}

thead .admin-orders-sticky-col {
    z-index: 4;
    background: #fdf9f5 !important;
}

.admin-orders-actions {
    display: flex;
    gap: 8px;
    flex-wrap: nowrap;
    white-space: nowrap;
}

.admin-orders-action {
    min-height: 36px;
    padding: 0 14px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 800;
    background: rgba(17,17,17,0.06);
    color: #111;
    white-space: nowrap;
}

.admin-orders-action-dark {
    background: #111;
    color: #fff;
}

.admin-orders-empty {
    padding: 28px 24px;
    color: #6f6257;
}

@media (max-width: 768px) {
    .admin-orders-section {
        padding: 32px 0 60px;
    }

    .admin-orders-card-head,
    .admin-orders-table th,
    .admin-orders-table td {
        padding-left: 16px;
        padding-right: 16px;
    }

    .admin-orders-section .container {
        max-width: 100%;
    }

    .admin-orders-table {
        min-width: 1180px;
    }
}
</style>

<?php require __DIR__ . '/../app/Views/partials/footer.php'; ?>