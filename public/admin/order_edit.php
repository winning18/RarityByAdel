<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/require_admin.php';
require __DIR__ . '/../includes/mailer.php';

$pageTitle = 'Manage Order';
$currentPage = 'orders';

$db = Database::getInstance();
$order = null;
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid order selected.';
    header('Location: ' . url('admin/orders.php'));
    exit;
}

$orderStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
$paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];

function buildOrderStatusEmailHtml(array $order, array $items, string $statusLabel, string $headline, string $message): string
{
    ob_start();
    require __DIR__ . '/../app/Views/emails/order_status_update.php';
    return (string) ob_get_clean();
}

function orderStatusEmailMeta(string $status): ?array
{
    return match ($status) {
        'shipped' => [
            'subject_prefix' => 'Your order has been shipped',
            'status_label' => 'Shipping confirmation',
            'headline' => 'Your order is on the way.',
            'message' => "Good news — your order has been shipped.\nWe’ll keep you updated as it moves toward delivery.",
        ],
        'delivered' => [
            'subject_prefix' => 'Your order has been delivered',
            'status_label' => 'Delivery confirmation',
            'headline' => 'Your order has been delivered.',
            'message' => "Your order has been marked as delivered.\nWe hope you love your purchase from RarityByAdel.",
        ],
        'cancelled' => [
            'subject_prefix' => 'Your order has been cancelled',
            'status_label' => 'Order cancelled',
            'headline' => 'Your order has been cancelled.',
            'message' => "Your order has been cancelled.\nIf you need any help or this was unexpected, please contact our support team.",
        ],
        default => null,
    };
}

try {
    $stmt = $db->prepare("
        SELECT *
        FROM orders
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['error_message'] = 'Order not found.';
        header('Location: ' . url('admin/orders.php'));
        exit;
    }
} catch (Throwable $e) {
    $_SESSION['error_message'] = 'Unable to load order.';
    header('Location: ' . url('admin/orders.php'));
    exit;
}

$errors = [];

$form = [
    'order_status' => (string) $order['order_status'],
    'payment_status' => (string) $order['payment_status'],
    'payment_method' => (string) $order['payment_method'],
    'notes' => (string) ($order['notes'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $originalOrderStatus = (string) $order['order_status'];

    $form['order_status'] = trim((string) ($_POST['order_status'] ?? ''));
    $form['payment_status'] = trim((string) ($_POST['payment_status'] ?? ''));
    $form['payment_method'] = trim((string) ($_POST['payment_method'] ?? ''));
    $form['notes'] = trim((string) ($_POST['notes'] ?? ''));

    if (!in_array($form['order_status'], $orderStatuses, true)) {
        $errors[] = 'Please select a valid order status.';
    }

    if (!in_array($form['payment_status'], $paymentStatuses, true)) {
        $errors[] = 'Please select a valid payment status.';
    }

    if ($form['payment_method'] === '') {
        $errors[] = 'Payment method is required.';
    }

    if (empty($errors)) {
        try {
            $paidAt = null;

            if ($form['payment_status'] === 'paid') {
                $paidAt = !empty($order['paid_at']) ? $order['paid_at'] : date('Y-m-d H:i:s');
            }

            $stmt = $db->prepare("
                UPDATE orders
                SET
                    order_status = :order_status,
                    payment_status = :payment_status,
                    payment_method = :payment_method,
                    notes = :notes,
                    paid_at = :paid_at,
                    updated_at = NOW()
                WHERE id = :id
                LIMIT 1
            ");

            $stmt->execute([
                'order_status' => $form['order_status'],
                'payment_status' => $form['payment_status'],
                'payment_method' => $form['payment_method'],
                'notes' => $form['notes'] !== '' ? $form['notes'] : null,
                'paid_at' => $paidAt,
                'id' => $id,
            ]);

            $newStatus = $form['order_status'];

            if ($newStatus !== $originalOrderStatus) {
                $emailMeta = orderStatusEmailMeta($newStatus);

                if ($emailMeta !== null) {
                    $reload = $db->prepare("
                        SELECT *
                        FROM orders
                        WHERE id = :id
                        LIMIT 1
                    ");
                    $reload->execute(['id' => $id]);
                    $freshOrder = $reload->fetch();

                    $itemsStmt = $db->prepare("
                        SELECT
                            oi.*,
                            p.image AS product_image
                        FROM order_items oi
                        LEFT JOIN products p ON p.id = oi.product_id
                        WHERE oi.order_id = :order_id
                        ORDER BY oi.id ASC
                    ");
                    $itemsStmt->execute(['order_id' => $id]);
                    $items = $itemsStmt->fetchAll();

                    if ($freshOrder && !empty($freshOrder['customer_email'])) {
                        $subject = $emailMeta['subject_prefix'] . ' - ' . (string) $freshOrder['order_number'];
                        $htmlBody = buildOrderStatusEmailHtml(
                            $freshOrder,
                            $items,
                            $emailMeta['status_label'],
                            $emailMeta['headline'],
                            $emailMeta['message']
                        );
                        $altBody = $emailMeta['headline'] . ' Order: ' . (string) $freshOrder['order_number'];

                        $mailResult = rarity_send_mail(
                            (string) $freshOrder['customer_email'],
                            (string) $freshOrder['customer_name'],
                            $subject,
                            $htmlBody,
                            $altBody
                        );

                        if (!$mailResult['success']) {
                            error_log('RarityByAdel order status email failed for order ID ' . $id . ': ' . $mailResult['message']);
                        }
                    }
                }
            }

            $_SESSION['success_message'] = 'Order updated successfully.';
            header('Location: ' . url('admin/order_view.php?id=' . $id));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Unable to update the order right now.';
        }
    }
}

require __DIR__ . '/../app/Views/partials/admin_header.php';
?>

<section class="admin-order-edit-section">
    <div class="container">
        <div class="admin-order-edit-shell">
            <div class="admin-order-edit-header">
                <div>
                    <span class="admin-order-edit-kicker">Admin Panel</span>
                    <h1>Manage Order #<?= e((string) $order['order_number']) ?></h1>
                    <p>Update fulfillment, payment state, and internal notes for this order.</p>
                </div>

                <div class="admin-order-edit-header-actions">
                    <a href="<?= url('admin/orders.php') ?>" class="admin-order-edit-btn admin-order-edit-btn-light">Back to Orders</a>
                    <a href="<?= url('admin/order_view.php?id=' . (int) $order['id']) ?>" class="admin-order-edit-btn admin-order-edit-btn-dark">View Order</a>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="admin-order-edit-alert admin-order-edit-alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="admin-order-edit-card">
                <form method="POST" class="admin-order-edit-form">
                    <div class="admin-order-edit-grid">
                        <div class="admin-order-edit-field">
                            <label for="order_status">Order Status</label>
                            <select name="order_status" id="order_status" required>
                                <?php foreach ($orderStatuses as $status): ?>
                                    <option value="<?= e($status) ?>" <?= $form['order_status'] === $status ? 'selected' : '' ?>>
                                        <?= e(ucfirst($status)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-order-edit-field">
                            <label for="payment_status">Payment Status</label>
                            <select name="payment_status" id="payment_status" required>
                                <?php foreach ($paymentStatuses as $status): ?>
                                    <option value="<?= e($status) ?>" <?= $form['payment_status'] === $status ? 'selected' : '' ?>>
                                        <?= e(ucfirst($status)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-order-edit-field admin-order-edit-field-full">
                            <label for="payment_method">Payment Method</label>
                            <input type="text" name="payment_method" id="payment_method" value="<?= e($form['payment_method']) ?>" required>
                        </div>

                        <div class="admin-order-edit-field">
                            <label>Payer Name</label>
                            <div class="admin-order-edit-static-field"><?= e((string) (!empty($order['payer_name']) ? $order['payer_name'] : 'Not submitted')) ?></div>
                        </div>

                        <div class="admin-order-edit-field">
                            <label>Payment Reference</label>
                            <div class="admin-order-edit-static-field"><?= e((string) (!empty($order['payment_reference']) ? $order['payment_reference'] : 'Not submitted')) ?></div>
                        </div>

                        <div class="admin-order-edit-field">
                            <label>Reference Submitted</label>
                            <div class="admin-order-edit-static-field">
                                <?= !empty($order['payment_submitted_at'])
                                    ? e(date('M d, Y h:i A', strtotime((string) $order['payment_submitted_at'])))
                                    : 'Not submitted' ?>
                            </div>
                        </div>

                        <div class="admin-order-edit-field">
                            <label>Paid At</label>
                            <div class="admin-order-edit-static-field">
                                <?= !empty($order['paid_at'])
                                    ? e(date('M d, Y h:i A', strtotime((string) $order['paid_at'])))
                                    : 'Not paid yet' ?>
                            </div>
                        </div>

                        <div class="admin-order-edit-field admin-order-edit-field-full">
                            <label for="notes">Internal / Customer Notes</label>
                            <textarea name="notes" id="notes" rows="7"><?= e($form['notes']) ?></textarea>
                        </div>
                    </div>

                    <div class="admin-order-edit-summary">
                        <div><span>Customer</span><strong><?= e((string) $order['customer_name']) ?></strong></div>
                        <div><span>Email</span><strong><?= e((string) $order['customer_email']) ?></strong></div>
                        <div><span>Total</span><strong>₵<?= e(number_format((float) $order['total_amount'], 2)) ?></strong></div>
                        <div><span>Placed On</span><strong><?= e(date('M d, Y h:i A', strtotime((string) $order['created_at']))) ?></strong></div>
                    </div>

                    <div class="admin-order-edit-actions">
                        <a href="<?= url('admin/order_view.php?id=' . (int) $order['id']) ?>" class="admin-order-edit-btn admin-order-edit-btn-light">Cancel</a>
                        <button type="submit" class="admin-order-edit-btn admin-order-edit-btn-dark">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.admin-order-edit-section {
    padding: 40px 0 72px;
    background: #f7f1eb;
}

.admin-order-edit-shell {
    display: grid;
    gap: 24px;
}

.admin-order-edit-header {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 20px;
    flex-wrap: wrap;
}

.admin-order-edit-kicker {
    display: inline-block;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 0.76rem;
    font-weight: 800;
    color: #8a7768;
}

.admin-order-edit-header h1 {
    margin: 0 0 10px;
    font-family: "Cormorant Garamond", serif;
    font-size: clamp(2rem, 4vw, 3.1rem);
    line-height: 1;
    color: #161616;
}

.admin-order-edit-header p {
    margin: 0;
    color: #6f6257;
    max-width: 58ch;
}

.admin-order-edit-header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.admin-order-edit-btn {
    min-height: 42px;
    padding: 0 16px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-weight: 800;
    border: none;
    cursor: pointer;
}

.admin-order-edit-btn-light {
    background: rgba(17,17,17,0.06);
    color: #111;
}

.admin-order-edit-btn-dark {
    background: #111;
    color: #fff;
}

.admin-order-edit-alert {
    padding: 16px 18px;
    border-radius: 18px;
}

.admin-order-edit-alert ul {
    margin: 0;
    padding-left: 18px;
}

.admin-order-edit-alert-error {
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.18);
    color: #8f1f14;
}

.admin-order-edit-card {
    background: rgba(255,255,255,0.96);
    border: 1px solid rgba(17,17,17,0.08);
    border-radius: 28px;
    padding: 24px;
    box-shadow: 0 18px 40px rgba(17,17,17,0.06);
}

.admin-order-edit-form {
    display: grid;
    gap: 24px;
}

.admin-order-edit-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.admin-order-edit-field {
    display: grid;
    gap: 8px;
}

.admin-order-edit-field-full {
    grid-column: 1 / -1;
}

.admin-order-edit-field label {
    font-weight: 800;
    color: #111;
}

.admin-order-edit-field input,
.admin-order-edit-field select,
.admin-order-edit-field textarea,
.admin-order-edit-static-field {
    width: 100%;
    min-height: 52px;
    padding: 14px 16px;
    border-radius: 16px;
    border: 1px solid rgba(17,17,17,0.12);
    background: #fff;
    color: #111;
    font: inherit;
}

.admin-order-edit-static-field {
    display: flex;
    align-items: center;
    background: #faf7f3;
}

.admin-order-edit-field textarea {
    min-height: 160px;
    resize: vertical;
}

.admin-order-edit-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    padding-top: 8px;
    border-top: 1px solid rgba(17,17,17,0.08);
}

.admin-order-edit-summary span {
    display: block;
    margin-bottom: 6px;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #8a7768;
    font-weight: 700;
}

.admin-order-edit-summary strong {
    color: #161616;
}

.admin-order-edit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

@media (max-width: 900px) {
    .admin-order-edit-grid,
    .admin-order-edit-summary {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .admin-order-edit-card {
        padding: 20px;
        border-radius: 22px;
    }
}
</style>

<?php require __DIR__ . '/../app/Views/partials/footer.php'; ?>