<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

$pageTitle = 'Checkout';
$currentPage = '';

$db = Database::getInstance();

$errors = [];
$successMessage = '';
$orderItems = [];
$subtotal = 0.00;
$shipping = 35.00;
$total = 0.00;

$form = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'city' => '',
    'address' => '',
    'notes' => '',
    'payment_method' => 'momo',
];

$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart)) {
    $cart = [];
}

foreach ($cart as $item) {
    $name = (string) ($item['name'] ?? 'Product');
    $size = trim((string) ($item['size'] ?? 'M'));
    $qty = max(1, (int) ($item['qty'] ?? 1));
    $price = (float) ($item['price'] ?? 0);

    $lineTotal = $price * $qty;
    $subtotal += $lineTotal;

    $orderItems[] = [
        'id' => (int) ($item['id'] ?? 0),
        'name' => $name,
        'meta' => 'Size ' . $size . ' · Qty ' . $qty,
        'price' => $lineTotal,
        'unit_price' => $price,
        'qty' => $qty,
        'size' => $size,
    ];
}

if (empty($orderItems)) {
    $shipping = 0.00;
}

$total = $subtotal + $shipping;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $form['email'] = trim((string) ($_POST['email'] ?? ''));
    $form['phone'] = trim((string) ($_POST['phone'] ?? ''));
    $form['city'] = trim((string) ($_POST['city'] ?? ''));
    $form['address'] = trim((string) ($_POST['address'] ?? ''));
    $form['notes'] = trim((string) ($_POST['notes'] ?? ''));
    $form['payment_method'] = trim((string) ($_POST['payment_method'] ?? 'momo'));

    if (empty($orderItems)) {
        $errors[] = 'Your cart is empty.';
    }

    if ($form['full_name'] === '') {
        $errors[] = 'Full name is required.';
    }

    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($form['phone'] === '' || !preg_match('/^[0-9]{10}$/', $form['phone'])) {
        $errors[] = 'Please enter a valid 10-digit phone number.';
    }

    if ($form['city'] === '') {
        $errors[] = 'City / Region is required.';
    }

    if ($form['address'] === '') {
        $errors[] = 'Street address is required.';
    }

    $allowedPaymentMethods = ['momo', 'card', 'bank'];
    if (!in_array($form['payment_method'], $allowedPaymentMethods, true)) {
        $form['payment_method'] = 'momo';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $stockCheckStmt = $db->prepare("
                SELECT id, name, stock_quantity, is_active
                FROM products
                WHERE id = :id
                LIMIT 1
            ");

            foreach ($orderItems as $item) {
                $stockCheckStmt->execute(['id' => $item['id']]);
                $productRow = $stockCheckStmt->fetch();

                if (!$productRow) {
                    throw new RuntimeException('One of the products no longer exists.');
                }

                if ((int) $productRow['is_active'] !== 1) {
                    throw new RuntimeException($productRow['name'] . ' is no longer available.');
                }

                if ((int) $productRow['stock_quantity'] < (int) $item['qty']) {
                    throw new RuntimeException('Insufficient stock for ' . $productRow['name'] . '.');
                }
            }

            $orderNumber = 'RBA-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(2)));

            $insertOrder = $db->prepare("
                INSERT INTO orders (
                    user_id,
                    order_number,
                    customer_name,
                    customer_email,
                    customer_phone,
                    shipping_address,
                    notes,
                    subtotal,
                    shipping_fee,
                    total_amount,
                    payment_method,
                    payment_status,
                    order_status
                ) VALUES (
                    :user_id,
                    :order_number,
                    :customer_name,
                    :customer_email,
                    :customer_phone,
                    :shipping_address,
                    :notes,
                    :subtotal,
                    :shipping_fee,
                    :total_amount,
                    :payment_method,
                    :payment_status,
                    :order_status
                )
            ");

            $shippingAddress = $form['address'] . ', ' . $form['city'];

            $insertOrder->execute([
                'user_id' => $_SESSION['user']['id'] ?? null,
                'order_number' => $orderNumber,
                'customer_name' => $form['full_name'],
                'customer_email' => $form['email'],
                'customer_phone' => $form['phone'] !== '' ? $form['phone'] : null,
                'shipping_address' => $shippingAddress,
                'notes' => $form['notes'] !== '' ? $form['notes'] : null,
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'shipping_fee' => number_format($shipping, 2, '.', ''),
                'total_amount' => number_format($total, 2, '.', ''),
                'payment_method' => $form['payment_method'],
                'payment_status' => 'pending',
                'order_status' => 'pending',
            ]);

            $orderId = (int) $db->lastInsertId();

            $insertItem = $db->prepare("
                INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_name,
                    unit_price,
                    quantity,
                    line_total
                ) VALUES (
                    :order_id,
                    :product_id,
                    :product_name,
                    :unit_price,
                    :quantity,
                    :line_total
                )
            ");

            $updateStock = $db->prepare("
                UPDATE products
                SET stock_quantity = stock_quantity - :qty
                WHERE id = :id
                LIMIT 1
            ");

            foreach ($orderItems as $item) {
                $lineTotal = $item['unit_price'] * $item['qty'];

                $productNameForOrder = $item['name'];
                if ($item['size'] !== '') {
                    $productNameForOrder .= ' (Size ' . $item['size'] . ')';
                }

                $insertItem->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'product_name' => $productNameForOrder,
                    'unit_price' => number_format((float) $item['unit_price'], 2, '.', ''),
                    'quantity' => (int) $item['qty'],
                    'line_total' => number_format((float) $lineTotal, 2, '.', ''),
                ]);

                $updateStock->execute([
                    'qty' => (int) $item['qty'],
                    'id' => (int) $item['id'],
                ]);
            }

            $db->commit();

            $_SESSION['last_order_id'] = $orderId;
            $_SESSION['last_order_number'] = $orderNumber;
            $_SESSION['success_message'] = 'Order placed successfully. Your order number is ' . $orderNumber . '.';

            unset($_SESSION['cart']);

            echo '<script>window.location.href="' . e(url('payment-reference.php?order_id=' . $orderId)) . '";</script>';
exit;
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            $errors[] = $e->getMessage() !== '' ? $e->getMessage() : 'Unable to place your order right now.';
        }
    }
}

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="checkout-page-section">
    <div class="container">
        <div class="checkout-heading">
            <span class="section-tag">Checkout</span>
            <h1>Complete your order</h1>
            <p>Enter delivery details, review your order, and select your preferred payment method.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="catalog-notice" style="margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 18px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="checkout-layout">
            <div class="checkout-form-card">
                <form action="" method="POST" class="checkout-form">
                    <div class="checkout-block">
                        <h2>Customer Information</h2>
                        <div class="form-grid two-columns">
                            <div class="form-field">
                                <label for="full_name">Full Name</label>
                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    placeholder="Enter your full name"
                                    value="<?= e($form['full_name']) ?>"
                                    required
                                >
                            </div>

                            <div class="form-field">
                                <label for="email">Email Address</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="Enter your email"
                                    value="<?= e($form['email']) ?>"
                                    required
                                >
                            </div>

                            <div class="form-field">
                                <label for="phone">Phone Number</label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    placeholder="Enter your 10-digit phone number"
                                    inputmode="numeric"
                                    pattern="[0-9]{10}"
                                    maxlength="10"
                                    minlength="10"
                                    value="<?= e($form['phone']) ?>"
                                    required
                                >
                            </div>

                            <div class="form-field">
                                <label for="city">City / Region</label>
                                <input
                                    type="text"
                                    id="city"
                                    name="city"
                                    placeholder="Enter city or region"
                                    value="<?= e($form['city']) ?>"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <div class="checkout-block">
                        <h2>Delivery Address</h2>
                        <div class="form-grid">
                            <div class="form-field">
                                <label for="address">Street Address</label>
                                <input
                                    type="text"
                                    id="address"
                                    name="address"
                                    placeholder="House number, street, area"
                                    value="<?= e($form['address']) ?>"
                                    required
                                >
                            </div>

                            <div class="form-field">
                                <label for="notes">Order Notes <span style="color: var(--color-text-soft); font-weight: 500;">(Optional)</span></label>
                                <textarea
                                    id="notes"
                                    name="notes"
                                    rows="4"
                                    placeholder="Optional delivery notes"
                                ><?= e($form['notes']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-block">
                        <h2>Payment Method</h2>

                        <div class="payment-options">
                            <label class="payment-option <?= $form['payment_method'] === 'momo' ? 'active' : '' ?>">
                                <input type="radio" name="payment_method" value="momo" <?= $form['payment_method'] === 'momo' ? 'checked' : '' ?> required>
                                <span class="payment-content">
                                    <strong>Mobile Money</strong>
                                    <small>Default payment method</small>
                                </span>
                            </label>

                            <label class="payment-option <?= $form['payment_method'] === 'card' ? 'active' : '' ?>">
                                <input type="radio" name="payment_method" value="card" <?= $form['payment_method'] === 'card' ? 'checked' : '' ?> required>
                                <span class="payment-content">
                                    <strong>Card</strong>
                                    <small>Visa / Mastercard</small>
                                </span>
                            </label>

                            <label class="payment-option <?= $form['payment_method'] === 'bank' ? 'active' : '' ?>">
                                <input type="radio" name="payment_method" value="bank" <?= $form['payment_method'] === 'bank' ? 'checked' : '' ?> required>
                                <span class="payment-content">
                                    <strong>Bank Transfer</strong>
                                    <small>Direct bank payment</small>
                                </span>
                            </label>
                        </div>

                        <div class="payment-panel <?= $form['payment_method'] === 'momo' ? 'active' : '' ?>" data-method-panel="momo">
                            <label>MoMo Number</label>
                            <input type="text" value="Will be enabled later" disabled>
                        </div>

                        <div class="payment-panel <?= $form['payment_method'] === 'card' ? 'active' : '' ?>" data-method-panel="card">
                            <p>Card payment fields will be enabled in the next payment integration phase.</p>
                        </div>

                        <div class="payment-panel <?= $form['payment_method'] === 'bank' ? 'active' : '' ?>" data-method-panel="bank">
                            <p>Bank transfer instructions will be shown after order submission.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark place-order-btn" <?= empty($orderItems) ? 'disabled' : '' ?>>
                        <?= empty($orderItems) ? 'Cart is Empty' : 'Place Order' ?>
                    </button>
                </form>
            </div>

            <aside class="checkout-summary-card">
                <span class="section-tag">Your Order</span>
                <h2>Order summary</h2>

                <div class="checkout-items">
                    <?php if (empty($orderItems)): ?>
                        <div class="checkout-item-row">
                            <div>
                                <h3>Your cart is empty</h3>
                                <p>Add products before checking out.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orderItems as $item): ?>
                            <div class="checkout-item-row">
                                <div>
                                    <h3><?= e($item['name']) ?></h3>
                                    <p><?= e($item['meta']) ?></p>
                                </div>
                                <strong>GHS <?= number_format((float) $item['price'], 2) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="summary-line">
                    <span>Subtotal</span>
                    <strong>GHS <?= number_format((float) $subtotal, 2) ?></strong>
                </div>

                <div class="summary-line">
                    <span>Shipping</span>
                    <strong>GHS <?= number_format((float) $shipping, 2) ?></strong>
                </div>

                <div class="summary-line total-line">
                    <span>Total</span>
                    <strong>GHS <?= number_format((float) $total, 2) ?></strong>
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('.payment-option input[type="radio"]').forEach(input => {
    input.addEventListener('change', () => {
        document.querySelectorAll('.payment-option').forEach(option => option.classList.remove('active'));
        document.querySelectorAll('.payment-panel').forEach(panel => panel.classList.remove('active'));

        const parent = input.closest('.payment-option');
        if (parent) parent.classList.add('active');

        const panel = document.querySelector('[data-method-panel="' + input.value + '"]');
        if (panel) panel.classList.add('active');
    });
});
</script>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>