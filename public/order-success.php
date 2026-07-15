<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

$pageTitle = 'Order Submitted';
$currentPage = '';

$db = Database::getInstance();

$orderId = isset($_SESSION['last_order_id']) ? (int) $_SESSION['last_order_id'] : 0;
$orderNumber = isset($_SESSION['last_order_number']) ? (string) $_SESSION['last_order_number'] : '';
$order = null;

if ($orderId <= 0) {
    $_SESSION['error_message'] = 'No recent order was found.';
    header('Location: ' . url('clothings.php'));
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT
            id,
            order_number,
            customer_name,
            customer_email,
            total_amount,
            payment_method,
            payment_status,
            order_status,
            created_at
        FROM orders
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['error_message'] = 'Order confirmation could not be loaded.';
        header('Location: ' . url('clothings.php'));
        exit;
    }

    $orderNumber = (string) ($order['order_number'] ?? $orderNumber);
} catch (Throwable $e) {
    $_SESSION['error_message'] = 'Unable to load your order confirmation right now.';
    header('Location: ' . url('clothings.php'));
    exit;
}

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="order-success-section">
    <div class="container">
        <div class="order-success-card">
            <div class="success-icon-wrap">
                <div class="success-icon">&#10003;</div>
            </div>

            <span class="section-tag">Submission Successful</span>
            <h1>Your order has been placed successfully</h1>
            <p>Thank you for shopping with RarityByAdel. Your order is now awaiting payment confirmation from our team.</p>

            <div class="success-order-meta">
                <div class="reference-line">
                    <span>Order Number</span>
                    <strong><?= e($orderNumber) ?></strong>
                </div>
                <div class="reference-line">
                    <span>Status</span>
                    <strong><?= e(ucfirst((string) $order['order_status'])) ?></strong>
                </div>
                <div class="reference-line">
                    <span>Payment</span>
                    <strong><?= e(ucfirst((string) $order['payment_status'])) ?></strong>
                </div>
                <div class="reference-line">
                    <span>Total</span>
                    <strong>GHS <?= number_format((float) $order['total_amount'], 2) ?></strong>
                </div>
            </div>

            <div class="success-next-steps">
                <h2>What happens next</h2>
                <ul>
                    <li>An order confirmation email can be sent to the customer inbox in the next phase.</li>
                    <li>Payment will be reviewed by admin.</li>
                    <li>Once confirmed, the order status will move to processing.</li>
                    <li>Shipping and delivery updates can be tracked from admin order management.</li>
                </ul>
            </div>

            <div class="success-actions">
                <a href="<?= url('clothings.php') ?>" class="btn btn-outline">Continue Shopping</a>
                <a href="<?= url('contact.php') ?>" class="btn btn-dark">Contact Support</a>
            </div>
        </div>
        <br>
    </div>
</section>

<?php
unset($_SESSION['last_order_id'], $_SESSION['last_order_number']);
require __DIR__ . '/app/Views/partials/footer.php';
?>