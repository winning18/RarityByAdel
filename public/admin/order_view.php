<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/require_admin.php';
// remove the config/database line entirely

$pageTitle = 'Order Details';
$currentPage = 'orders';

$db = Database::getInstance();
$order = null;
$orderItems = [];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid order selected.';
    header('Location: ' . url('admin/orders.php'));
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT
            o.*,
            u.full_name AS user_full_name,
            u.email AS user_email
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        WHERE o.id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['error_message'] = 'Order not found.';
        header('Location: ' . url('admin/orders.php'));
        exit;
    }

    $stmt = $db->prepare("
        SELECT
            id,
            product_id,
            product_name,
            unit_price,
            quantity,
            line_total
        FROM order_items
        WHERE order_id = :order_id
        ORDER BY id ASC
    ");
    $stmt->execute(['order_id' => $id]);
    $orderItems = $stmt->fetchAll();
} catch (Throwable $e) {
    $_SESSION['error_message'] = 'Unable to load order details.';
    header('Location: ' . url('admin/orders.php'));
    exit;
}

function orderBadgeClass(string $status): string
{
    return match ($status) {
        'paid', 'delivered' => 'badge-paid',
        'failed', 'cancelled', 'refunded' => 'badge-danger',
        'processing', 'shipped' => 'badge-progress',
        default => 'badge-pending',
    };
}

require __DIR__ . '/../app/Views/partials/admin_header.php';
?>

<section class="admin-order-view-section">
    <div class="container">
        <div class="admin-order-view-shell">
            <div class="admin-order-view-header">
                <div>
                    <span class="admin-order-view-kicker">Admin Panel</span>
                    <h1>Order #<?= e((string) $order['order_number']) ?></h1>
                    <p>Review customer information, ordered items, payment submission, and delivery details.</p>
                </div>

                <div class="admin-order-view-header-actions">
                    <a href="<?= url('admin/orders.php') ?>" class="admin-order-view-btn admin-order-view-btn-light">Back to Orders</a>
                    <a href="<?= url('admin/order_edit.php?id=' . (int) $order['id']) ?>" class="admin-order-view-btn admin-order-view-btn-dark">Manage Order</a>
                </div>
            </div>

            <div class="admin-order-view-top-grid">
                <div class="admin-order-view-card">
                    <h2>Order Summary</h2>
                    <div class="admin-order-view-summary">
                        <div>
                            <span>Order Number</span>
                            <strong>#<?= e((string) $order['order_number']) ?></strong>
                        </div>
                        <div>
                            <span>Date</span>
                            <strong><?= e(date('M d, Y h:i A', strtotime((string) $order['created_at']))) ?></strong>
                        </div>
                        <div>
                            <span>Order Status</span>
                            <strong>
                                <span class="admin-order-view-badge <?= e(orderBadgeClass((string) $order['order_status'])) ?>">
                                    <?= e(ucfirst((string) $order['order_status'])) ?>
                                </span>
                            </strong>
                        </div>
                        <div>
                            <span>Payment Status</span>
                            <strong>
                                <span class="admin-order-view-badge <?= e(orderBadgeClass((string) $order['payment_status'])) ?>">
                                    <?= e(ucfirst((string) $order['payment_status'])) ?>
                                </span>
                            </strong>
                        </div>
                        <div>
                            <span>Payment Method</span>
                            <strong><?= e(ucfirst((string) $order['payment_method'])) ?></strong>
                        </div>
                        <div>
                            <span>Total Amount</span>
                            <strong>₵<?= e(number_format((float) $order['total_amount'], 2)) ?></strong>
                        </div>
                    </div>
                </div>

                <div class="admin-order-view-card">
                    <h2>Customer Details</h2>
                    <div class="admin-order-view-details">
                        <div>
                            <span>Name</span>
                            <strong><?= e((string) $order['customer_name']) ?></strong>
                        </div>
                        <div>
                            <span>Email</span>
                            <strong><?= e((string) $order['customer_email']) ?></strong>
                        </div>
                        <div>
                            <span>Phone</span>
                            <strong><?= e((string) ($order['customer_phone'] ?: '—')) ?></strong>
                        </div>
                        <div>
                            <span>Linked Account</span>
                            <strong><?= e((string) ($order['user_full_name'] ?: 'Guest Checkout')) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-order-view-grid">
                <div class="admin-order-view-card">
                    <h2>Ordered Items</h2>

                    <?php if (empty($orderItems)): ?>
                        <p class="admin-order-view-empty">No order items found.</p>
                    <?php else: ?>
                        <div class="admin-order-view-table-wrap">
                            <table class="admin-order-view-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Unit Price</th>
                                        <th>Qty</th>
                                        <th>Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td>
                                                <strong><?= e((string) $item['product_name']) ?></strong><br>
                                                <span class="admin-order-view-muted">Product ID: <?= e((string) $item['product_id']) ?></span>
                                            </td>
                                            <td>₵<?= e(number_format((float) $item['unit_price'], 2)) ?></td>
                                            <td><?= e((string) $item['quantity']) ?></td>
                                            <td>₵<?= e(number_format((float) $item['line_total'], 2)) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="admin-order-view-totals">
                            <div><span>Subtotal</span><strong>₵<?= e(number_format((float) $order['subtotal'], 2)) ?></strong></div>
                            <div><span>Shipping</span><strong>₵<?= e(number_format((float) $order['shipping_fee'], 2)) ?></strong></div>
                            <div class="grand-total"><span>Total</span><strong>₵<?= e(number_format((float) $order['total_amount'], 2)) ?></strong></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="admin-order-view-side">
                    <div class="admin-order-view-card">
                        <h2>Payment Submission</h2>
                        <div class="admin-order-view-details">
                            <div>
                                <span>Payer Name</span>
                                <strong><?= e((string) (!empty($order['payer_name']) ? $order['payer_name'] : 'Not submitted')) ?></strong>
                            </div>
                            <div>
                                <span>Reference</span>
                                <strong><?= e((string) (!empty($order['payment_reference']) ? $order['payment_reference'] : 'Not submitted')) ?></strong>
                            </div>
                            <div>
                                <span>Submitted At</span>
                                <strong>
                                    <?= !empty($order['payment_submitted_at'])
                                        ? e(date('M d, Y h:i A', strtotime((string) $order['payment_submitted_at'])))
                                        : 'Not submitted' ?>
                                </strong>
                            </div>
                            <div>
                                <span>Expected Amount</span>
                                <strong>₵<?= e(number_format((float) $order['total_amount'], 2)) ?></strong>
                            </div>
                            <div>
                                <span>Method</span>
                                <strong><?= e(ucfirst((string) $order['payment_method'])) ?></strong>
                            </div>
                            <div>
                                <span>Status</span>
                                <strong>
                                    <span class="admin-order-view-badge <?= e(orderBadgeClass((string) $order['payment_status'])) ?>">
                                        <?= e(ucfirst((string) $order['payment_status'])) ?>
                                    </span>
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="admin-order-view-card">
                        <h2>Shipping Address</h2>
                        <div class="admin-order-view-text-block">
                            <?= !empty($order['shipping_address']) ? nl2br(e((string) $order['shipping_address'])) : 'No shipping address provided.' ?>
                        </div>
                    </div>

                    <div class="admin-order-view-card">
                        <h2>Customer Notes</h2>
                        <div class="admin-order-view-text-block">
                            <?= !empty($order['notes']) ? nl2br(e((string) $order['notes'])) : 'No notes provided.' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.admin-order-view-section {
    padding: 40px 0 72px;
    background: #f7f1eb;
}

.admin-order-view-shell {
    display: grid;
    gap: 24px;
}

.admin-order-view-header {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 20px;
    flex-wrap: wrap;
}

.admin-order-view-kicker {
    display: inline-block;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 0.76rem;
    font-weight: 800;
    color: #8a7768;
}

.admin-order-view-header h1 {
    margin: 0 0 10px;
    font-family: "Cormorant Garamond", serif;
    font-size: clamp(2rem, 4vw, 3.2rem);
    line-height: 1;
    color: #161616;
}

.admin-order-view-header p {
    margin: 0;
    color: #6f6257;
    max-width: 60ch;
}

.admin-order-view-header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.admin-order-view-btn {
    min-height: 42px;
    padding: 0 16px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-weight: 800;
}

.admin-order-view-btn-light {
    background: rgba(17,17,17,0.06);
    color: #111;
}

.admin-order-view-btn-dark {
    background: #111;
    color: #fff;
}

.admin-order-view-top-grid {
    display: grid;
    grid-template-columns: 1.25fr 1fr;
    gap: 24px;
}

.admin-order-view-grid {
    display: grid;
    grid-template-columns: 1.4fr 0.9fr;
    gap: 24px;
}

.admin-order-view-side {
    display: grid;
    gap: 24px;
}

.admin-order-view-card {
    background: rgba(255,255,255,0.96);
    border: 1px solid rgba(17,17,17,0.08);
    border-radius: 28px;
    padding: 24px;
    box-shadow: 0 18px 40px rgba(17,17,17,0.06);
}

.admin-order-view-card h2 {
    margin: 0 0 18px;
    font-size: 1.5rem;
    font-family: "Cormorant Garamond", serif;
    color: #161616;
}

.admin-order-view-summary,
.admin-order-view-details {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.admin-order-view-summary span,
.admin-order-view-details span,
.admin-order-view-totals span {
    display: block;
    margin-bottom: 6px;
    font-size: 0.84rem;
    color: #8a7768;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
}

.admin-order-view-summary strong,
.admin-order-view-details strong,
.admin-order-view-totals strong {
    color: #161616;
}

.admin-order-view-badge {
    min-height: 32px;
    padding: 0 12px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    font-weight: 800;
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

.admin-order-view-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.admin-order-view-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-order-view-table th,
.admin-order-view-table td {
    padding: 14px 0;
    text-align: left;
    border-bottom: 1px solid rgba(17,17,17,0.07);
    vertical-align: top;
}

.admin-order-view-table th {
    color: #8a7768;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.8rem;
}

.admin-order-view-muted {
    color: #8a7768;
    font-size: 0.88rem;
}

.admin-order-view-totals {
    margin-top: 18px;
    display: grid;
    gap: 12px;
}

.admin-order-view-totals > div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.admin-order-view-totals .grand-total {
    padding-top: 12px;
    border-top: 1px solid rgba(17,17,17,0.08);
}

.admin-order-view-text-block {
    color: #3f372f;
    line-height: 1.7;
}

.admin-order-view-empty {
    color: #6f6257;
    margin: 0;
}

@media (max-width: 992px) {
    .admin-order-view-top-grid,
    .admin-order-view-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .admin-order-view-summary,
    .admin-order-view-details {
        grid-template-columns: 1fr;
    }

    .admin-order-view-card {
        padding: 20px;
        border-radius: 22px;
    }
}
</style>

<?php require __DIR__ . '/../app/Views/partials/footer.php'; ?>