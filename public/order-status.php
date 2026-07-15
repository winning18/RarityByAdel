<?php
declare(strict_types=1);

require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

$pageTitle = 'Order Status';
$currentPage = '';

$db = Database::getInstance();

$order = null;
$orderItems = [];
$errors = [];
$searchedOrderNumber = '';
$hasSearched = false;

if (isset($_GET['order_number'])) {
    $searchedOrderNumber = trim((string) $_GET['order_number']);
    $hasSearched = $searchedOrderNumber !== '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchedOrderNumber = trim((string) ($_POST['order_number'] ?? ''));
    $hasSearched = true;

    if ($searchedOrderNumber === '') {
        $errors[] = 'Please enter your order number.';
    }

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                SELECT *
                FROM orders
                WHERE order_number = :order_number
                LIMIT 1
            ");
            $stmt->execute(['order_number' => $searchedOrderNumber]);
            $order = $stmt->fetch();

            if (!$order) {
                $errors[] = 'We could not find an order with that number.';
            } else {
                $itemsStmt = $db->prepare("
                    SELECT
                        oi.*,
                        p.image AS product_image
                    FROM order_items oi
                    LEFT JOIN products p ON p.id = oi.product_id
                    WHERE oi.order_id = :order_id
                    ORDER BY oi.id ASC
                ");
                $itemsStmt->execute(['order_id' => (int) $order['id']]);
                $orderItems = $itemsStmt->fetchAll();
            }
        } catch (Throwable $e) {
            $errors[] = 'Unable to load your order right now. Please try again shortly.';
        }
    }
} elseif ($hasSearched && $searchedOrderNumber !== '') {
    try {
        $stmt = $db->prepare("
            SELECT *
            FROM orders
            WHERE order_number = :order_number
            LIMIT 1
        ");
        $stmt->execute(['order_number' => $searchedOrderNumber]);
        $order = $stmt->fetch();

        if (!$order) {
            $errors[] = 'We could not find an order with that number.';
        } else {
            $itemsStmt = $db->prepare("
                SELECT
                    oi.*,
                    p.image AS product_image
                FROM order_items oi
                LEFT JOIN products p ON p.id = oi.product_id
                WHERE oi.order_id = :order_id
                ORDER BY oi.id ASC
            ");
            $itemsStmt->execute(['order_id' => (int) $order['id']]);
            $orderItems = $itemsStmt->fetchAll();
        }
    } catch (Throwable $e) {
        $errors[] = 'Unable to load your order right now. Please try again shortly.';
    }
}

function order_status_badge_class(string $status): string
{
    return match ($status) {
        'pending' => 'order-status-pill-pending',
        'processing' => 'order-status-pill-processing',
        'shipped' => 'order-status-pill-shipped',
        'delivered' => 'order-status-pill-delivered',
        'cancelled' => 'order-status-pill-cancelled',
        default => 'order-status-pill-pending',
    };
}

function order_status_step_active(string $currentStatus, string $step): bool
{
    $order = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    $currentIndex = array_search($currentStatus, $order, true);
    $stepIndex = array_search($step, $order, true);

    if ($currentIndex === false || $stepIndex === false) {
        return false;
    }

    return $currentIndex >= $stepIndex;
}

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="order-status-section">
    <div class="container">
        <div class="order-status-shell">
            <header class="order-status-header">
                <div>
                    <span class="order-status-kicker">Track your order</span>
                    <h1>Order status</h1>
                    <p>Enter your order number to see the latest status and details of your RarityByAdel order.</p>
                </div>
            </header>

            <div class="order-status-search-card">
                <form method="POST" class="order-status-search-form">
                    <label for="order_number">Order Number</label>
                    <div class="order-status-search-row">
                        <input
                            type="text"
                            id="order_number"
                            name="order_number"
                            placeholder="e.g. RBA-20240611-0012"
                            value="<?= e($searchedOrderNumber) ?>"
                            required
                        >
                        <button type="submit" class="btn btn-dark order-status-search-btn">Check Status</button>
                    </div>
                    <p class="order-status-hint">
                        You can find your order number in your confirmation email or on the success page right after checkout.
                    </p>
                </form>

                <?php if (!empty($errors)): ?>
                    <div class="order-status-alert">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($order && empty($errors)): ?>
                <div class="order-status-layout">
                    <div class="order-status-main">
                        <div class="order-status-card">
                            <div class="order-status-card-header">
                                <div>
                                    <span class="order-status-kicker">Order summary</span>
                                    <h2>Order #<?= e((string) $order['order_number']) ?></h2>
                                    <p>Placed on <?= e(date('M d, Y h:i A', strtotime((string) $order['created_at']))) ?></p>
                                </div>

                                <div class="order-status-pills">
                                    <span class="order-status-pill <?= e(order_status_badge_class((string) $order['order_status'])) ?>">
                                        <?= e(ucfirst((string) $order['order_status'])) ?>
                                    </span>
                                    <span class="order-status-pill order-status-pill-payment">
                                        Payment: <?= e(ucfirst((string) $order['payment_status'])) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="order-status-details-grid">
                                <div>
                                    <h3>Customer</h3>
                                    <p><?= e((string) $order['customer_name']) ?><br><?= e((string) $order['customer_email']) ?></p>
                                </div>
                                <div>
                                    <h3>Shipping</h3>
                                    <p><?= nl2br(e((string) $order['shipping_address'])) ?></p>
                                </div>
                                <div>
                                    <h3>Payment</h3>
                                    <p>
                                        Method: <?= e(ucfirst((string) $order['payment_method'])) ?><br>
                                        Status: <?= e(ucfirst((string) $order['payment_status'])) ?>
                                    </p>
                                </div>
                                <div>
                                    <h3>Totals</h3>
                                    <p>
                                        Subtotal: GHS <?= number_format((float) $order['subtotal'], 2) ?><br>
                                        Shipping: GHS <?= number_format((float) $order['shipping_fee'], 2) ?><br>
                                        <strong>Total: GHS <?= number_format((float) $order['total_amount'], 2) ?></strong>
                                    </p>
                                </div>
                            </div>

                            <?php if (!empty($order['notes'])): ?>
                                <div class="order-status-notes">
                                    <h3>Order note</h3>
                                    <p><?= nl2br(e((string) $order['notes'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="order-status-card">
                            <h2>Items in this order</h2>

                            <?php if (!empty($orderItems)): ?>
                                <div class="order-status-items">
                                    <?php foreach ($orderItems as $item): ?>
                                        <article class="order-status-item">
                                            <div class="order-status-item-thumb">
                                                <?php
                                                    $image = (string) ($item['product_image'] ?? '');
                                                    if ($image !== '') {
                                                        $basename = basename(str_replace('\\', '/', $image));
                                                        $imageUrl = url('uploads/products/' . rawurlencode($basename));
                                                    } else {
                                                        $imageUrl = '';
                                                    }
                                                ?>
                                                <?php if ($imageUrl !== ''): ?>
                                                    <img src="<?= e($imageUrl) ?>" alt="<?= e((string) $item['product_name']) ?>">
                                                <?php else: ?>
                                                    <div class="order-status-item-thumb-fallback">Item</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="order-status-item-main">
                                                <h3><?= e((string) $item['product_name']) ?></h3>
                                                <p>Qty <?= (int) $item['quantity'] ?> · GHS <?= number_format((float) $item['unit_price'], 2) ?></p>
                                            </div>
                                            <div class="order-status-item-total">
                                                GHS <?= number_format((float) ($item['line_total'] ?? ($item['quantity'] * $item['unit_price'])), 2) ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="order-status-empty-items">No items were found for this order.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <aside class="order-status-aside">
                        <div class="order-status-card order-status-timeline-card">
                            <span class="order-status-kicker">Order progress</span>
                            <h2>Current status</h2>
                            <p>Your order moves through the steps below from the moment you place it to final delivery.</p>

                            <?php
                                $currentStatus = (string) $order['order_status'];
                            ?>
                            <div class="order-status-steps">
                                <div class="order-status-step <?= order_status_step_active($currentStatus, 'pending') ? 'active' : '' ?>">
                                    <span>1</span>
                                    <div>
                                        <strong>Order placed</strong>
                                        <p>Your order has been received successfully.</p>
                                    </div>
                                </div>

                                <div class="order-status-step <?= order_status_step_active($currentStatus, 'processing') ? 'active' : '' ?>">
                                    <span>2</span>
                                    <div>
                                        <strong>Processing</strong>
                                        <p>We’re preparing your items for shipment.</p>
                                    </div>
                                </div>

                                <div class="order-status-step <?= order_status_step_active($currentStatus, 'shipped') ? 'active' : '' ?>">
                                    <span>3</span>
                                    <div>
                                        <strong>Shipped</strong>
                                        <p>Your order is on the way to you.</p>
                                    </div>
                                </div>

                                <div class="order-status-step <?= order_status_step_active($currentStatus, 'delivered') ? 'active' : '' ?>">
                                    <span>4</span>
                                    <div>
                                        <strong>Delivered</strong>
                                        <p>Your order has been delivered.</p>
                                    </div>
                                </div>

                                <div class="order-status-step <?= $currentStatus === 'cancelled' ? 'active cancelled' : '' ?>">
                                    <span>✕</span>
                                    <div>
                                        <strong>Cancelled</strong>
                                        <p>If this was unexpected, please contact support.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="order-status-card order-status-help-card">
                            <h2>Need help?</h2>
                            <p>If something doesn’t look right about your order status, our team is here to help.</p>
                            <p>
                                Email: <a href="mailto:<?= e(BUSINESS_EMAIL) ?>"><?= e(BUSINESS_EMAIL) ?></a><br>
                                Phone: <a href="tel:+233551812055"><?= e(BUSINESS_PHONE) ?></a>
                            </p>
                        </div>
                    </aside>
                </div>
            <?php elseif ($hasSearched && empty($errors)): ?>
                <p class="order-status-no-results">We couldn’t find any order with that order number.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.order-status-section {
    padding: 40px 0 72px;
    background: #f7f1eb;
}

.order-status-shell {
    display: grid;
    gap: 24px;
}

.order-status-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 20px;
    flex-wrap: wrap;
}

.order-status-kicker {
    display: inline-block;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 0.76rem;
    font-weight: 800;
    color: #8a7768;
}

.order-status-header h1 {
    margin: 0 0 10px;
    font-family: "Cormorant Garamond", serif;
    font-size: clamp(2rem, 4vw, 3.1rem);
    line-height: 1;
    color: #161616;
}

.order-status-header p {
    margin: 0;
    color: #6f6257;
    max-width: 58ch;
}

.order-status-search-card {
    background: rgba(255,255,255,0.96);
    border-radius: 26px;
    padding: 22px 20px;
    border: 1px solid rgba(17,17,17,0.08);
    box-shadow: 0 14px 34px rgba(17,17,17,0.04);
}

.order-status-search-form {
    display: grid;
    gap: 10px;
}

.order-status-search-form label {
    font-weight: 800;
    color: #111;
}

.order-status-search-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.order-status-search-row input {
    flex: 1 1 220px;
    min-height: 48px;
    padding: 0 14px;
    border-radius: 999px;
    border: 1px solid rgba(17,17,17,0.14);
    font: inherit;
}

.order-status-search-btn {
    min-height: 48px;
    padding: 0 20px;
    border-radius: 999px;
    font-weight: 800;

    /* primary look – match your cart checkout button */
    border: 1px solid #111111;
    background: #111111;
    color: #ffffff;
}

/* Optional hover/focus */
.order-status-search-btn:hover,
.order-status-search-btn:focus-visible {
    background: #2a211d;
    border-color: #2a211d;
}
    
    

.order-status-hint {
    margin: 4px 0 0;
    font-size: 0.82rem;
    color: #8a7768;
}

.order-status-alert {
    margin-top: 12px;
    padding: 12px 14px;
    border-radius: 18px;
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.18);
    color: #8f1f14;
}

.order-status-alert ul {
    margin: 0;
    padding-left: 18px;
}

.order-status-layout {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
    gap: 20px;
    align-items: flex-start;
}

.order-status-main,
.order-status-aside {
    display: grid;
    gap: 18px;
}

.order-status-card {
    background: rgba(255,255,255,0.96);
    border-radius: 26px;
    padding: 20px 18px;
    border: 1px solid rgba(17,17,17,0.08);
    box-shadow: 0 14px 34px rgba(17,17,17,0.04);
}

.order-status-card-header {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    margin-bottom: 16px;
}

.order-status-card-header h2 {
    margin: 0 0 6px;
    font-size: 1.4rem;
    color: #161616;
}

.order-status-card-header p {
    margin: 0;
    color: #6f6257;
    font-size: 0.9rem;
}

.order-status-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.order-status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.order-status-pill-payment {
    background: rgba(17,17,17,0.06);
    color: #111;
}

.order-status-pill-pending {
    background: #fff4da;
    color: #8a6b20;
}

.order-status-pill-processing {
    background: #e0f3ff;
    color: #225a82;
}

.order-status-pill-shipped {
    background: #e5f9ef;
    color: #216a3a;
}

.order-status-pill-delivered {
    background: #e3f3ff;
    color: #1f4f7f;
}

.order-status-pill-cancelled {
    background: #fde6e3;
    color: #9e2819;
}

.order-status-details-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-top: 10px;
}

.order-status-details-grid h3 {
    margin: 0 0 4px;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8a7768;
}

.order-status-details-grid p {
    margin: 0;
    font-size: 0.9rem;
    color: #2c211a;
}

.order-status-notes {
    margin-top: 18px;
    padding-top: 12px;
    border-top: 1px solid rgba(17,17,17,0.08);
}

.order-status-notes h3 {
    margin: 0 0 4px;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8a7768;
}

.order-status-notes p {
    margin: 0;
    font-size: 0.9rem;
    color: #2c211a;
}

.order-status-items {
    display: grid;
    gap: 10px;
}

.order-status-item {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    gap: 12px;
    align-items: center;
    padding: 10px 0;
    border-top: 1px solid rgba(17,17,17,0.06);
}

.order-status-item:first-of-type {
    border-top: none;
}

.order-status-item-thumb {
    width: 60px;
    height: 60px;
    border-radius: 18px;
    overflow: hidden;
    background: #dfc9ba;
}

.order-status-item-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.order-status-item-thumb-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    color: #6f5648;
}

.order-status-item-main h3 {
    margin: 0 0 2px;
    font-size: 0.96rem;
    color: #161616;
}

.order-status-item-main p {
    margin: 0;
    font-size: 0.84rem;
    color: #6f6257;
}

.order-status-item-total {
    font-size: 0.9rem;
    font-weight: 700;
    color: #161616;
}

.order-status-empty-items {
    margin: 0;
    font-size: 0.9rem;
    color: #6f6257;
}

.order-status-timeline-card h2 {
    margin: 6px 0 8px;
}

.order-status-steps {
    margin-top: 12px;
    display: grid;
    gap: 10px;
}

.order-status-step {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    gap: 10px;
    align-items: flex-start;
    padding: 8px 10px;
    border-radius: 16px;
    background: rgba(255,255,255,0.7);
    border: 1px solid transparent;
}

.order-status-step span:first-child {
    width: 26px;
    height: 26px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 800;
    background: #efe1d6;
    color: #5c4334;
}

.order-status-step strong {
    display: block;
    font-size: 0.9rem;
    color: #161616;
}

.order-status-step p {
    margin: 2px 0 0;
    font-size: 0.84rem;
    color: #6f6257;
}

.order-status-step.active {
    border-color: rgba(17,17,17,0.16);
    background: #f7ede5;
}

.order-status-step.active span:first-child {
    background: #161616;
    color: #fff;
}

.order-status-step.cancelled span:first-child {
    background: #fde6e3;
    color: #9e2819;
}

.order-status-help-card a {
    color: #111;
    font-weight: 600;
    text-decoration: none;
}

.order-status-no-results {
    margin: 14px 0 0;
    font-size: 0.9rem;
    color: #6f6257;
}


    
    
    
    @media (max-width: 960px) {
    .order-status-layout {
        grid-template-columns: minmax(0, 1fr);
    }

    .order-status-details-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    /* Stack details in a single column */
    .order-status-details-grid {
        grid-template-columns: 1fr;
    }

    /* Slightly tighter cards on small screens */
    .order-status-card {
        padding: 16px 14px;
        border-radius: 18px;
    }

    /* Search row: vertical, compact */
    .order-status-search-row {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }

    /* Compact input: no giant pill, normal field */
    .order-status-search-row input {
        display: block;
        width: 100% !important;
        box-sizing: border-box;

        height: 38px !important;
        min-height: 0 !important;
        padding: 0 10px !important;

        border-radius: 8px !important;
        border: 1px solid rgba(17,17,17,0.18) !important;

        font-size: 0.95rem !important;
        line-height: 1.2 !important;
        background-color: #ffffff !important;
    }

    /* Button: full-width but slim */
    .order-status-search-row .order-status-search-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;

        width: 100% !important;
        box-sizing: border-box;

        height: 42px !important;
        min-height: 0 !important;

        border-radius: 999px !important;
        font-size: 0.95rem !important;
    }

    /* Items list layout */
    .order-status-item {
        grid-template-columns: auto minmax(0, 1fr);
        grid-template-rows: auto auto;
    }

    .order-status-item-total {
        grid-column: 2 / -1;
        justify-self: flex-end;
    }
}
</style>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>