<?php
declare(strict_types=1);

/**
 * Expected variables:
 * - $order
 * - $items
 * - $statusLabel
 * - $headline
 * - $message
 */

if (!function_exists('orderEmailH')) {
    function orderEmailH($value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('orderEmailMoney')) {
    function orderEmailMoney(float $amount): string
    {
        return 'GHS ' . number_format($amount, 2);
    }
}

if (!function_exists('orderEmailProductImageUrl')) {
    function orderEmailProductImageUrl(?string $image): string
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
}

$customerName = (string)($order['customer_name'] ?? 'Valued Customer');
$orderNumber = (string)($order['order_number'] ?? '');
$shippingAddress = (string)($order['shipping_address'] ?? '');
$subtotal = (float)($order['subtotal'] ?? 0);
$shippingFee = (float)($order['shipping_fee'] ?? 0);
$totalAmount = (float)($order['total_amount'] ?? 0);
$orderStatus = (string)($order['order_status'] ?? '');
$paymentStatus = (string)($order['payment_status'] ?? '');
$notes = (string)($order['notes'] ?? '');
$createdAt = !empty($order['created_at']) ? date('F d, Y \a\t h:i A', strtotime((string)$order['created_at'])) : '';

$itemsHtml = '';
foreach ($items as $item) {
    $qty = (int)($item['quantity'] ?? 1);
    $unitPrice = (float)($item['unit_price'] ?? 0);
    $lineTotal = (float)($item['line_total'] ?? ($qty * $unitPrice));
    $productName = (string)($item['product_name'] ?? 'Product');
    $imageUrl = orderEmailProductImageUrl($item['product_image'] ?? null);

    $thumb = $imageUrl !== ''
        ? '<img src="' . orderEmailH($imageUrl) . '" alt="' . orderEmailH($productName) . '" style="width:64px;height:64px;object-fit:cover;border-radius:16px;display:block;">'
        : '<div style="width:64px;height:64px;border-radius:16px;background:#dfc9ba;color:#6f5648;font-size:11px;line-height:64px;text-align:center;">Item</div>';

    $itemsHtml .= '
    <div style="display:grid;grid-template-columns:64px 1fr auto;gap:14px;align-items:center;padding:14px 0;border-top:1px solid #e3d2c6;">
        <div>' . $thumb . '</div>
        <div>
            <div style="font-size:14px;color:#201713;margin-bottom:4px;font-weight:700;">' . orderEmailH($productName) . '</div>
            <div style="font-size:12px;color:#6f5648;">Qty ' . $qty . ' · Unit ' . orderEmailH(orderEmailMoney($unitPrice)) . '</div>
        </div>
        <div style="font-size:14px;font-weight:700;color:#201713;text-align:right;">' . orderEmailH(orderEmailMoney($lineTotal)) . '</div>
    </div>';
}

if ($itemsHtml === '') {
    $itemsHtml = '<p style="margin:0;font-size:13px;line-height:1.75;color:#4a3a31;">No items were found for this order.</p>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= orderEmailH($statusLabel) ?></title>
</head>
<body style="margin:0;padding:36px 16px;background:#0f0c0a;font-family:Arial,Helvetica,sans-serif;color:#2a211c;">
    <div style="max-width:760px;margin:0 auto;">
        <div style="border-radius:28px;overflow:hidden;border:1px solid #3f322a;background:#f2e5da;box-shadow:0 30px 80px rgba(0,0,0,0.38);">
            <div style="padding:30px 28px;background:linear-gradient(180deg,#15100d 0%,#241a15 100%);border-bottom:1px solid #3f322a;">
                <div style="font-size:18px;font-weight:700;letter-spacing:0.16em;text-transform:uppercase;color:#f7efe8;margin-bottom:8px;"><?= orderEmailH(APP_NAME) ?></div>
                <div style="font-size:11px;color:#d8b89c;letter-spacing:0.18em;text-transform:uppercase;"><?= orderEmailH($statusLabel) ?></div>
            </div>

            <div style="padding:30px 28px;background:#f2e5da;">
                <div style="font-size:11px;color:#8d6f5d;letter-spacing:0.16em;text-transform:uppercase;margin-bottom:10px;">Dear <?= orderEmailH($customerName) ?>,</div>
                <h1 style="margin:0 0 10px;font-size:30px;line-height:1.25;color:#1f1713;"><?= orderEmailH($headline) ?></h1>
                <p style="margin:0 0 24px;font-size:14px;line-height:1.75;color:#4a3a31;max-width:640px;"><?= nl2br(orderEmailH($message)) ?></p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                    <div style="background:#f7ede5;border:1px solid #d8c1b1;border-radius:20px;padding:18px;">
                        <div style="font-size:11px;color:#8d6f5d;letter-spacing:0.16em;text-transform:uppercase;margin-bottom:8px;">Order details</div>
                        <div style="font-size:14px;line-height:1.7;color:#241a15;">
                            <strong>Order No:</strong> <?= orderEmailH($orderNumber) ?><br>
                            <strong>Placed:</strong> <?= orderEmailH($createdAt) ?><br>
                            <strong>Order status:</strong> <?= orderEmailH(ucfirst($orderStatus)) ?><br>
                            <strong>Payment status:</strong> <?= orderEmailH(ucfirst($paymentStatus)) ?><br>
                        </div>
                    </div>

                    <div style="background:#f7ede5;border:1px solid #d8c1b1;border-radius:20px;padding:18px;">
                        <div style="font-size:11px;color:#8d6f5d;letter-spacing:0.16em;text-transform:uppercase;margin-bottom:8px;">Shipping details</div>
                        <div style="font-size:14px;line-height:1.7;color:#241a15;">
                            <strong>Customer:</strong> <?= orderEmailH($customerName) ?><br>
                            <strong>Address:</strong> <?= nl2br(orderEmailH($shippingAddress)) ?>
                        </div>
                    </div>
                </div>

                <div style="background:#f7ede5;border:1px solid #d8c1b1;border-radius:20px;padding:18px;margin-bottom:18px;">
                    <h2 style="margin:0 0 4px;font-size:16px;color:#1f1713;">Ordered items</h2>
                    <p style="margin:0 0 14px;font-size:13px;color:#8d6f5d;">A summary of your order items.</p>
                    <?= $itemsHtml ?>
                    <div style="margin-top:14px;padding-top:12px;border-top:1px dashed #cfb7a7;">
                        <div style="display:flex;justify-content:space-between;gap:12px;font-size:13px;color:#4a3a31;margin-bottom:8px;"><span>Subtotal</span><span><?= orderEmailH(orderEmailMoney($subtotal)) ?></span></div>
                        <div style="display:flex;justify-content:space-between;gap:12px;font-size:13px;color:#4a3a31;margin-bottom:8px;"><span>Shipping</span><span><?= orderEmailH(orderEmailMoney($shippingFee)) ?></span></div>
                        <div style="display:flex;justify-content:space-between;gap:12px;font-size:16px;font-weight:700;color:#1d1511;margin-top:10px;"><span>Total</span><span><?= orderEmailH(orderEmailMoney($totalAmount)) ?></span></div>
                    </div>
                </div>

                <?php if ($notes !== ''): ?>
                    <div style="background:#f7ede5;border:1px solid #d8c1b1;border-radius:20px;padding:18px;margin-bottom:18px;">
                        <h2 style="margin:0 0 4px;font-size:16px;color:#1f1713;">Order note</h2>
                        <p style="margin:0;font-size:13px;line-height:1.75;color:#4a3a31;"><?= nl2br(orderEmailH($notes)) ?></p>
                    </div>
                <?php endif; ?>

                <div style="background:#f7ede5;border:1px solid #d8c1b1;border-radius:20px;padding:18px;margin-bottom:18px;">
                    <h2 style="margin:0 0 4px;font-size:16px;color:#1f1713;">Need assistance?</h2>
                    <p style="margin:0;font-size:13px;line-height:1.75;color:#4a3a31;">Reply to this email or contact our support team.</p>
                    <div style="margin-top:10px;font-size:13px;line-height:1.9;color:#5d493d;">
                        <a href="mailto:<?= orderEmailH(BUSINESS_EMAIL) ?>" style="color:#1c1410;text-decoration:none;font-weight:600;"><?= orderEmailH(BUSINESS_EMAIL) ?></a>
                        &nbsp;·&nbsp;
                        <a href="tel:+233551812055" style="color:#1c1410;text-decoration:none;font-weight:600;"><?= orderEmailH(BUSINESS_PHONE) ?></a>
                        &nbsp;·&nbsp;
                        WhatsApp: +233 222 330 949
                    </div>
                </div>
            </div>

            <div style="padding:20px 28px 26px;border-top:1px solid #d8c1b1;color:#7e6455;font-size:12px;line-height:1.7;background:#efe0d4;">
                This is your <?= orderEmailH(APP_NAME) ?> order update email.
            </div>
        </div>
    </div>
</body>
</html>