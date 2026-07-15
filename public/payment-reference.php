<?php
declare(strict_types=1);

require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/mailer.php';

$pageTitle = 'Payment Reference';
$currentPage = '';

$db = Database::getInstance();

$errors = [];
$order = null;

$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

$form = [
    'payment_reference' => '',
    'payer_name' => '',
];

function money_format_gh(float $amount): string
{
    return 'GHS ' . number_format($amount, 2);
}

function email_h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function product_email_image_url(?string $image): string
{
    if (!$image) {
        return '';
    }

    $image = trim($image);
    if ($image === '') {
        return '';
    }

    if (preg_match('~^https?://~i', $image)) {
        return $image;
    }

    $basename = basename(str_replace('\\', '/', $image));
    return APP_URL . '/uploads/products/' . rawurlencode($basename);
}

function build_payment_reference_email(array $order, array $items): string
{
    $customerName = (string)($order['customer_name'] ?? 'Valued Customer');
    $orderNumber = (string)($order['order_number'] ?? '');
    $paymentMethod = (string)($order['payment_method'] ?? '');
    $payerName = (string)($order['payer_name'] ?? '');
    $paymentReference = (string)($order['payment_reference'] ?? '');
    $shippingAddress = (string)($order['shipping_address'] ?? '');
    $notes = (string)($order['notes'] ?? '');
    $subtotal = (float)($order['subtotal'] ?? 0);
    $shippingFee = (float)($order['shipping_fee'] ?? 0);
    $totalAmount = (float)($order['total_amount'] ?? 0);
    $paymentStatus = (string)($order['payment_status'] ?? 'pending');
    $orderStatus = (string)($order['order_status'] ?? 'pending');
    $createdAt = !empty($order['created_at']) ? date('F d, Y \a\t h:i A', strtotime((string)$order['created_at'])) : '';
    $submittedAt = !empty($order['payment_submitted_at']) ? date('F d, Y \a\t h:i A', strtotime((string)$order['payment_submitted_at'])) : '';

    $itemsHtml = '';
    foreach ($items as $item) {
        $qty = (int)($item['quantity'] ?? 1);
        $unitPrice = (float)($item['unit_price'] ?? 0);
        $lineTotal = (float)($item['line_total'] ?? ($qty * $unitPrice));
        $productName = (string)($item['product_name'] ?? 'Product');
        $imageUrl = product_email_image_url($item['product_image'] ?? null);

        $thumb = $imageUrl !== ''
            ? '<img src="' . email_h($imageUrl) . '" alt="' . email_h($productName) . '" style="width:64px;height:64px;object-fit:cover;border-radius:16px;display:block;">'
            : '<div style="width:64px;height:64px;border-radius:16px;background:#dfc9ba;color:#6f5648;font-size:11px;line-height:64px;text-align:center;">Item</div>';

        $itemsHtml .= '
        <div style="display:grid;grid-template-columns:64px 1fr auto;gap:14px;align-items:center;padding:14px 0;border-top:1px solid #e3d2c6;">
            <div>' . $thumb . '</div>
            <div>
                <div style="font-size:14px;color:#201713;margin-bottom:4px;font-weight:700;">' . email_h($productName) . '</div>
                <div style="font-size:12px;color:#6f5648;">Qty ' . $qty . ' · Unit ' . email_h(money_format_gh($unitPrice)) . '</div>
            </div>
            <div style="font-size:14px;font-weight:700;color:#201713;text-align:right;">' . email_h(money_format_gh($lineTotal)) . '</div>
        </div>';
    }

    if ($itemsHtml === '') {
        $itemsHtml = '<p style="margin:0;font-size:13px;line-height:1.75;color:#4a3a31;">No items were found for this order.</p>';
    }

    return '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmation</title>
</head>
<body style="margin:0;padding:36px 16px;background:#0f0c0a;font-family:Arial,Helvetica,sans-serif;color:#2a211c;">
    <div style="max-width:760px;margin:0 auto;">
        <div style="border-radius:28px;overflow:hidden;border:1px solid #3f322a;background:#f2e5da;box-shadow:0 30px 80px rgba(0,0,0,0.38);">
            <div style="padding:30px 28px;background:linear-gradient(180deg,#15100d 0%,#241a15 100%);border-bottom:1px solid #3f322a;">
                <div style="font-size:18px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:#f7efe8;margin-bottom:8px;">' . email_h(APP_NAME) . '</div>
                <div style="font-size:11px;color:#d8b89c;letter-spacing:0.18em;text-transform:uppercase;">Luxury order confirmation</div>
            </div>

            <div style="padding:30px 28px;background:#f2e5da;">
                <div style="font-size:11px;color:#8d6f5d;letter-spacing:0.16em;text-transform:uppercase;margin-bottom:10px;">Dear ' . email_h($customerName) . ',</div>
                <h1 style="margin:0 0 10px;font-size:30px;line-height:1.25;color:#1f1713;">Your payment reference has been received.</h1>
                <p style="margin:0 0 24px;font-size:14px;line-height:1.75;color:#4a3a31;max-width:640px;">
                    Thank you for shopping with ' . email_h(APP_NAME) . '. We’ve received your order details and payment reference.
                    Our team will review the submission and confirm the next order update shortly.
                </p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                    <div style="background:#f7ede5;border:1px solid #d8c1b1;border-radius:20px;padding:18px;">
                        <div style="font-size:11px;color:#8d6f5d;letter-spacing:0.16em;text-transform:uppercase;margin-bottom:8px;">Order details</div>
                        <div style="font-size:14px;line-height:1.7;color:#241a15;">
                            <strong>Order No:</strong> ' . email_h($orderNumber) . '<br>
                            <strong>Placed:</strong> ' . email_h($createdAt) . '<br>
                            <strong>Payment method:</strong> ' . email_h(ucfirst($paymentMethod)) . '<br>
                            <strong>Payer name:</strong> ' . email_h($payerName) . '<br>
                            <strong>Payment ref:</strong> ' . email_h($paymentReference) . '<br>
                            <strong>Submitted:</strong> ' . email_h($submittedAt) . '<br>
                        </div>
                        <div style="margin-top:14px;">
                            <div style="display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;background:#1d1511;color:#f1dfd1;border:1px solid #4b3a31;">Payment ' . email_h($paymentStatus) . '</div>
                            <div style="margin-top:8px;font-size:12px;color:#6c5547;">Order status: ' . email_h(ucfirst($orderStatus)) . '</div>
                        </div>
                    </div>

                    <div style="background:#f7ede5;border:1px solid #d8c1b1;border-radius:20px;padding:18px;">
                        <div style="font-size:11px;color:#8d6f5d;letter-spacing:0.16em;text-transform:uppercase;margin-bottom:8px;">Shipping details</div>
                        <div style="font-size:14px;line-height:1.7;color:#241a15;">
                            <strong>Customer:</strong> ' . email_h($customerName) . '<br>
                            <strong>Address:</strong> ' . nl2br(email_h($shippingAddress)) . '
                        </div>
                    </div>
                </div>

                <div style="background:#f7ede5;border:1px solid #d8c1b1;border-radius:20px;padding:18px;margin-bottom:18px;">
                    <h2 style="margin:0 0 4px;font-size:16px;color:#1f1713;">Ordered items</h2>
                    <p style="margin:0 0 14px;font-size:13px;color:#8d6f5d;">A preview of the items included in this order.</p>
                    ' . $itemsHtml . '
                    <div style="margin-top:14px;padding-top:12px;border-top:1px dashed #cfb7a7;">
                        <div style="display:flex;justify-content:space-between;gap:12px;font-size:13px;color:#4a3a31;margin-bottom:8px;"><span>Subtotal</span><span>' . email_h(money_format_gh($subtotal)) . '</span></div>
                        <div style="display:flex;justify-content:space-between;gap:12px;font-size:13px;color:#4a3a31;margin-bottom:8px;"><span>Shipping</span><span>' . email_h(money_format_gh($shippingFee)) . '</span></div>
                        <div style="display:flex;justify-content:space-between;gap:12px;font-size:16px;font-weight:700;color:#1d1511;margin-top:10px;"><span>Total</span><span>' . email_h(money_format_gh($totalAmount)) . '</span></div>
                    </div>
                </div>

                ' . ($notes !== '' ? '
                <div style="background:#f7ede5;border:1px solid #d8c1b1;border-radius:20px;padding:18px;margin-bottom:18px;">
                    <h2 style="margin:0 0 4px;font-size:16px;color:#1f1713;">Customer note</h2>
                    <p style="margin:0;font-size:13px;line-height:1.75;color:#4a3a31;">' . nl2br(email_h($notes)) . '</p>
                </div>' : '') . '

                <div style="background:#f7ede5;border:1px solid #d8c1b1;border-radius:20px;padding:18px;margin-bottom:18px;">
                    <h2 style="margin:0 0 4px;font-size:16px;color:#1f1713;">Need assistance?</h2>
                    <p style="margin:0;font-size:13px;line-height:1.75;color:#4a3a31;">Reply to this email or contact our support team.</p>
                    <div style="margin-top:10px;font-size:13px;line-height:1.9;color:#5d493d;">
                        <a href="mailto:' . email_h(BUSINESS_EMAIL) . '" style="color:#1c1410;text-decoration:none;font-weight:600;">' . email_h(BUSINESS_EMAIL) . '</a>
                        &nbsp;·&nbsp;
                        <a href="tel:+233551812055" style="color:#1c1410;text-decoration:none;font-weight:600;">' . email_h(BUSINESS_PHONE) . '</a>
                        &nbsp;·&nbsp;
                        WhatsApp: +233 222 330 949
                    </div>
                </div>
            </div>

            <div style="padding:20px 28px 26px;border-top:1px solid #d8c1b1;color:#7e6455;font-size:12px;line-height:1.7;background:#efe0d4;">
                This is your ' . email_h(APP_NAME) . ' payment reference confirmation email.
            </div>
        </div>
    </div>
</body>
</html>';
}

if ($orderId <= 0) {
    $_SESSION['error_message'] = 'Invalid order reference.';
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
            customer_phone,
            shipping_address,
            notes,
            subtotal,
            shipping_fee,
            total_amount,
            payment_method,
            payment_status,
            order_status,
            payer_name,
            payment_reference,
            payment_submitted_at,
            created_at
        FROM orders
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['error_message'] = 'Order not found.';
        header('Location: ' . url('clothings.php'));
        exit;
    }

    $form['payment_reference'] = trim((string)($order['payment_reference'] ?? ''));
    $form['payer_name'] = trim((string)($order['payer_name'] ?? ''));
} catch (Throwable $e) {
    $_SESSION['error_message'] = 'Unable to load payment page right now.';
    header('Location: ' . url('clothings.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['payment_reference'] = trim((string)($_POST['payment_reference'] ?? ''));
    $form['payer_name'] = trim((string)($_POST['payer_name'] ?? ''));

    if ($form['payment_reference'] === '') {
        $errors[] = 'Payment reference number is required.';
    }

    if ($form['payer_name'] === '') {
        $errors[] = 'Payer name is required.';
    }

    if (empty($errors)) {
        try {
            $update = $db->prepare("
                UPDATE orders
                SET
                    payer_name = :payer_name,
                    payment_reference = :payment_reference,
                    payment_submitted_at = NOW(),
                    payment_status = 'pending'
                WHERE id = :id
                LIMIT 1
            ");

            $update->execute([
                'payer_name' => $form['payer_name'],
                'payment_reference' => $form['payment_reference'],
                'id' => $orderId,
            ]);

            $reloadOrder = $db->prepare("
                SELECT *
                FROM orders
                WHERE id = :id
                LIMIT 1
            ");
            $reloadOrder->execute(['id' => $orderId]);
            $freshOrder = $reloadOrder->fetch();

            $itemsStmt = $db->prepare("
                SELECT
                    oi.*,
                    p.image AS product_image
                FROM order_items oi
                LEFT JOIN products p ON p.id = oi.product_id
                WHERE oi.order_id = :order_id
                ORDER BY oi.id ASC
            ");
            $itemsStmt->execute(['order_id' => $orderId]);
            $items = $itemsStmt->fetchAll();

            if ($freshOrder && !empty($freshOrder['customer_email'])) {
                $subject = 'We received your payment reference - ' . (string)$freshOrder['order_number'];
                $htmlBody = build_payment_reference_email($freshOrder, $items);
                $altBody = 'Your payment reference has been received for order ' . (string)$freshOrder['order_number'] . '.';

                $mailResult = rarity_send_mail(
                    (string)$freshOrder['customer_email'],
                    (string)$freshOrder['customer_name'],
                    $subject,
                    $htmlBody,
                    $altBody
                );

                if (!$mailResult['success']) {
                    error_log('RarityByAdel payment reference email failed for order ID ' . $orderId . ': ' . $mailResult['message']);
                }
            }

            $_SESSION['last_order_id'] = (int)$orderId;
            $_SESSION['last_order_number'] = (string)$freshOrder['order_number'];
            $_SESSION['success_message'] = 'Payment reference submitted successfully.';

            header('Location: ' . url('order-success.php'));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Unable to save payment reference right now.';
        }
    }
}

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="payment-reference-section">
    <div class="container">
        <div class="payment-reference-grid">
            <div class="payment-reference-card">
                <span class="section-tag">Payment Step</span>
                <h1>Complete your payment</h1>
                <p>Your order has been created successfully. Please complete payment and submit your payment reference number for confirmation.</p>

                <?php if (!empty($errors)): ?>
                    <div class="catalog-notice" style="margin-bottom: 20px;">
                        <ul style="margin: 0; padding-left: 18px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="reference-summary">
                    <div class="reference-line">
                        <span>Order Number</span>
                        <strong><?= e((string)$order['order_number']) ?></strong>
                    </div>
                    <div class="reference-line">
                        <span>Payment Method</span>
                        <strong><?= e(ucfirst((string)$order['payment_method'])) ?></strong>
                    </div>
                    <div class="reference-line">
                        <span>Total Amount</span>
                        <strong>GHS <?= number_format((float)$order['total_amount'], 2) ?></strong>
                    </div>
                </div>

                <div class="payment-guide-box">
                    <h2>What to do next</h2>
                    <ol>
                        <li>Make payment using the selected method.</li>
                        <li>Keep your payment transaction reference.</li>
                        <li>Enter the reference number below and submit it.</li>
                        <li>Our team will verify your payment and update your order status.</li>
                    </ol>
                </div>

                <form action="" method="POST" class="reference-form">
                    <div class="form-field">
                        <h1>MoMo Number: 055 181 2055 | Name: Adelaide Kwakye Sei</h1>
                        <label for="payment_reference">Payment Reference Number</label>
                        <input
                            type="text"
                            id="payment_reference"
                            name="payment_reference"
                            placeholder="Enter your payment reference number"
                            value="<?= e($form['payment_reference']) ?>"
                            required
                        >
                    </div>

                    <div class="form-field"><br>
                        <label for="payer_name">Payer Name</label>
                        <input
                            type="text"
                            id="payer_name"
                            name="payer_name"
                            placeholder="Enter the name used for payment"
                            value="<?= e($form['payer_name']) ?>"
                            required
                        >
                    </div>
                    <br>
                    <button type="submit" class="btn btn-dark reference-submit-btn">Submit Reference</button>
                </form>
            </div>

            <aside class="payment-status-card"><br>
                <span class="section-tag">Order Status</span>
                <h2>Pending payment confirmation</h2>
                <p>Your order is currently waiting for payment verification. Once your reference is reviewed, your order status will be updated.</p>

                <div class="status-steps">
                    <div class="status-step active">
                        <span>1</span>
                        <div>
                            <strong>Order placed</strong>
                            <p>Your order has been received successfully.</p>
                        </div>
                    </div>

                    <div class="status-step active">
                        <span>2</span>
                        <div>
                            <strong>Awaiting payment reference</strong>
                            <p>Submit your payment reference after completing payment.</p>
                        </div>
                    </div>

                    <div class="status-step">
                        <span>3</span>
                        <div>
                            <strong>Payment confirmation</strong>
                            <p>Admin will verify your payment details.</p>
                        </div>
                    </div>

                    <div class="status-step">
                        <span>4</span>
                        <div>
                            <strong>Processing and delivery</strong>
                            <p>Your order will move forward after confirmation.</p>
                        </div>
                    </div>
                </div>

                <div class="status-help-box">
                    <p>Need help? Contact us via <a href="mailto:<?= e(BUSINESS_EMAIL) ?>"><?= e(BUSINESS_EMAIL) ?></a> or <a href="tel:+233551812055"><?= e(BUSINESS_PHONE) ?></a>.</p>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>