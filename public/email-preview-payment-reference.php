<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

$db = Database::getInstance();

function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function money($amount): string
{
    return number_format((float)$amount, 2);
}

function badgeLabel(string $status): string
{
    $status = trim(strtolower($status));
    return match ($status) {
        'paid' => 'Payment Confirmed',
        'failed' => 'Payment Failed',
        'refunded' => 'Refunded',
        default => 'Payment Pending Review',
    };
}

function productImageUrl(?string $image): string
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

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . '/RarityByAdel/public/uploads/products/' . rawurlencode($basename);
}

$appName = defined('APP_NAME') ? APP_NAME : 'RarityByAdel';
$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

if ($orderId <= 0) {
    $stmt = $db->query("SELECT id FROM orders ORDER BY id DESC LIMIT 1");
    $latest = $stmt->fetch();
    if ($latest) {
        $orderId = (int)$latest['id'];
    }
}

if ($orderId <= 0) {
    die('No order found for preview.');
}

$stmt = $db->prepare("
    SELECT *
    FROM orders
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    die('Order not found.');
}

$itemsStmt = $db->prepare("
    SELECT
        oi.*,
        p.image AS product_image
    FROM order_items oi
    LEFT JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();

$subtotal      = (float)($order['subtotal'] ?? 0);
$shippingFee   = (float)($order['shipping_fee'] ?? 0);
$totalAmount   = (float)($order['total_amount'] ?? 0);
$paymentStatus = (string)($order['payment_status'] ?? 'pending');
$orderStatus   = (string)($order['order_status'] ?? 'pending');

$orderNumber   = (string)($order['order_number'] ?? ('RBA-' . $orderId));
$customerName  = (string)($order['customer_name'] ?? 'Valued Customer');
$customerEmail = (string)($order['customer_email'] ?? '');
$customerPhone = (string)($order['customer_phone'] ?? '');
$shippingAddress = (string)($order['shipping_address'] ?? '');
$notes         = (string)($order['notes'] ?? '');
$paymentMethod = (string)($order['payment_method'] ?? '');
$payerName     = (string)($order['payer_name'] ?? '');
$paymentReference = (string)($order['payment_reference'] ?? '');

$createdAt = !empty($order['created_at']) ? date('F d, Y \a\t h:i A', strtotime((string)$order['created_at'])) : '';
$submittedAt = !empty($order['payment_submitted_at']) ? date('F d, Y \a\t h:i A', strtotime((string)$order['payment_submitted_at'])) : '';
$paidAt = !empty($order['paid_at']) ? date('F d, Y \a\t h:i A', strtotime((string)$order['paid_at'])) : '';

$mainStatusLabel = badgeLabel($paymentStatus);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= h($appName) ?> · Email Preview</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 36px 16px;
            background: #0f0c0a;
            font-family: Arial, Helvetica, sans-serif;
            color: #2a211c;
        }

        .wrap {
            max-width: 760px;
            margin: 0 auto;
        }

        .email-shell {
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid #3f322a;
            background: #f2e5da;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.38);
        }

        .email-header {
            padding: 30px 28px;
            background: linear-gradient(180deg, #15100d 0%, #241a15 100%);
            border-bottom: 1px solid #3f322a;
        }

        .brand {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #f7efe8;
            margin-bottom: 8px;
        }

        .brand-sub {
            font-size: 11px;
            color: #d8b89c;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .email-body {
            padding: 30px 28px;
            background: #f2e5da;
        }

        .eyebrow {
            font-size: 11px;
            color: #8d6f5d;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 30px;
            line-height: 1.25;
            color: #1f1713;
        }

        .lead {
            margin: 0 0 24px;
            font-size: 14px;
            line-height: 1.75;
            color: #4a3a31;
            max-width: 640px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 18px;
        }

        .card {
            background: #f7ede5;
            border: 1px solid #d8c1b1;
            border-radius: 20px;
            padding: 18px;
        }

        .label {
            font-size: 11px;
            color: #8d6f5d;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .value {
            font-size: 14px;
            line-height: 1.7;
            color: #241a15;
        }

        .status-wrap {
            margin-top: 14px;
        }

        .status-main {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            background: #1d1511;
            color: #f1dfd1;
            border: 1px solid #4b3a31;
        }

        .status-sub {
            margin-top: 8px;
            font-size: 12px;
            color: #6c5547;
        }

        .items-card,
        .note-card,
        .cta-card,
        .help-card {
            background: #f7ede5;
            border: 1px solid #d8c1b1;
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 18px;
        }

        .section-title {
            margin: 0 0 4px;
            font-size: 16px;
            color: #1f1713;
        }

        .section-sub {
            margin: 0 0 14px;
            font-size: 13px;
            color: #8d6f5d;
        }

        .item {
            display: grid;
            grid-template-columns: 64px 1fr auto;
            gap: 14px;
            align-items: center;
            padding: 14px 0;
            border-top: 1px solid #e3d2c6;
        }

        .item:first-of-type {
            border-top: none;
            padding-top: 4px;
        }

        .thumb {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            overflow: hidden;
            background: #dfc9ba;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6f5648;
            font-size: 11px;
            text-transform: uppercase;
        }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .item-name {
            font-size: 14px;
            color: #201713;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .item-meta {
            font-size: 12px;
            color: #6f5648;
        }

        .item-price {
            font-size: 14px;
            font-weight: 700;
            color: #201713;
            text-align: right;
        }

        .totals {
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px dashed #cfb7a7;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 13px;
            color: #4a3a31;
            margin-bottom: 8px;
        }

        .totals-row.final {
            margin-top: 10px;
            font-size: 16px;
            font-weight: 700;
            color: #1d1511;
        }

        .note-text,
        .cta-text,
        .help-text {
            margin: 0;
            font-size: 13px;
            line-height: 1.75;
            color: #4a3a31;
        }

        .cta-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 999px;
            text-decoration: none;
            background: #1c1410;
            color: #f6ece4;
            border: 1px solid #1c1410;
            font-size: 13px;
            font-weight: 700;
        }

        .help-links {
            margin-top: 10px;
            font-size: 13px;
            line-height: 1.9;
            color: #5d493d;
        }

        .help-links a {
            color: #1c1410;
            text-decoration: none;
            font-weight: 600;
        }

        .footer {
            padding: 20px 28px 26px;
            border-top: 1px solid #d8c1b1;
            color: #7e6455;
            font-size: 12px;
            line-height: 1.7;
            background: #efe0d4;
        }

        @media (max-width: 680px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .item {
                grid-template-columns: 64px 1fr;
            }

            .item-price {
                grid-column: 2 / 3;
                text-align: left;
            }

            .email-header,
            .email-body,
            .footer {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="email-shell">
        <div class="email-header">
            <div class="brand"><?= h($appName) ?></div>
            <div class="brand-sub">Luxury order confirmation preview</div>
        </div>

        <div class="email-body">
            <div class="eyebrow">Dear <?= h($customerName) ?>,</div>
            <h1>Your payment reference has been received.</h1>
            <p class="lead">
                Thank you for shopping with <?= h($appName) ?>. We’ve received your order details and payment reference.
                Our team will review the submission and confirm the next order update shortly.
            </p>

            <div class="grid">
                <div class="card">
                    <div class="label">Order details</div>
                    <div class="value">
                        <strong>Order No:</strong> <?= h($orderNumber) ?><br>
                        <strong>Placed:</strong> <?= h($createdAt) ?><br>
                        <strong>Payment method:</strong> <?= h($paymentMethod ?: 'Not specified') ?><br>
                        <?php if ($payerName !== ''): ?>
                            <strong>Payer name:</strong> <?= h($payerName) ?><br>
                        <?php endif; ?>
                        <?php if ($paymentReference !== ''): ?>
                            <strong>Payment ref:</strong> <?= h($paymentReference) ?><br>
                        <?php endif; ?>
                        <?php if ($submittedAt !== ''): ?>
                            <strong>Submitted:</strong> <?= h($submittedAt) ?><br>
                        <?php endif; ?>
                        <?php if ($paidAt !== ''): ?>
                            <strong>Paid at:</strong> <?= h($paidAt) ?><br>
                        <?php endif; ?>
                    </div>

                    <div class="status-wrap">
                        <div class="status-main"><?= h($mainStatusLabel) ?></div>
                        <div class="status-sub">Order status: <?= h(ucfirst($orderStatus)) ?></div>
                    </div>
                </div>

                <div class="card">
                    <div class="label">Customer & shipping</div>
                    <div class="value">
                        <strong>Name:</strong> <?= h($customerName) ?><br>
                        <strong>Email:</strong> <?= h($customerEmail) ?><br>
                        <?php if ($customerPhone !== ''): ?>
                            <strong>Phone:</strong> <?= h($customerPhone) ?><br>
                        <?php endif; ?>
                        <strong>Address:</strong> <?= nl2br(h($shippingAddress)) ?><br>
                    </div>
                </div>
            </div>

            <div class="items-card">
                <h2 class="section-title">Ordered items</h2>
                <p class="section-sub">A preview of the items included in this order.</p>

                <?php if ($items): ?>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $qty = (int)($item['quantity'] ?? 1);
                        $unitPrice = (float)($item['unit_price'] ?? 0);
                        $lineTotal = (float)($item['line_total'] ?? ($qty * $unitPrice));
                        $imageUrl = productImageUrl($item['product_image'] ?? null);
                        ?>
                        <div class="item">
                            <div class="thumb">
                                <?php if ($imageUrl !== ''): ?>
                                    <img src="<?= h($imageUrl) ?>" alt="<?= h($item['product_name'] ?? 'Product') ?>">
                                <?php else: ?>
                                    Item
                                <?php endif; ?>
                            </div>

                            <div>
                                <div class="item-name"><?= h($item['product_name'] ?? 'Product') ?></div>
                                <div class="item-meta">
                                    Qty <?= $qty ?> · Unit ₵<?= money($unitPrice) ?>
                                </div>
                            </div>

                            <div class="item-price">₵<?= money($lineTotal) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="note-text">No items were found for this order.</p>
                <?php endif; ?>

                <div class="totals">
                    <div class="totals-row">
                        <span>Subtotal</span>
                        <span>₵<?= money($subtotal) ?></span>
                    </div>
                    <div class="totals-row">
                        <span>Shipping</span>
                        <span>₵<?= money($shippingFee) ?></span>
                    </div>
                    <div class="totals-row final">
                        <span>Total</span>
                        <span>₵<?= money($totalAmount) ?></span>
                    </div>
                </div>
            </div>

            <?php if ($notes !== ''): ?>
                <div class="note-card">
                    <h2 class="section-title">Customer note</h2>
                    <p class="note-text"><?= nl2br(h($notes)) ?></p>
                </div>
            <?php endif; ?>

            <div class="cta-card">
                <h2 class="section-title">Support follow-up</h2>
                <p class="cta-text">
                    Customers can reply to support if they need help updating payment details
                    or confirming their payment submission.
                </p>

                <div class="cta-actions">
                    <a class="btn-primary" href="mailto:hello@raritybyadel.com?subject=Payment%20Reference%20for%20<?= rawurlencode($orderNumber) ?>">
                        Reply to support
                    </a>
                </div>
            </div>

            <div class="help-card">
                <h2 class="section-title">Need assistance?</h2>
                <p class="help-text">
                    Reply to this email or contact our support team.
                </p>
                <div class="help-links">
                    <a href="mailto:hello@raritybyadel.com">hello@raritybyadel.com</a>
                    &nbsp;·&nbsp;
                    <a href="tel:+233551812055">+233 551 812 055</a>
                    &nbsp;·&nbsp;
                    WhatsApp: +233 222 330 949
                </div>
            </div>
        </div>

        <div class="footer">
            This is a preview of the RarityByAdel payment reference confirmation email using the project’s luxury nude-and-black presentation style.
        </div>
    </div>
</div>
</body>
</html>