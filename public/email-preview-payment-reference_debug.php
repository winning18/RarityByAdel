<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

$pageTitle = 'Email Preview - Payment Reference Received';

$db = Database::getInstance();

$sampleImage = '';
try {
    $stmt = $db->query("SELECT image FROM products WHERE image IS NOT NULL AND image != '' ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch();

    if ($row && !empty($row['image'])) {
        $diskImagePath = dirname(__DIR__) . '/public/uploads/products/' . $row['image'];
        if (is_file($diskImagePath)) {
            $sampleImage = url('uploads/products/' . $row['image']);
        }
    }
} catch (Throwable $e) {
    $sampleImage = '';
}

$order = [
    'order_number' => 'RBA-20260611-1001',
    'created_at' => date('Y-m-d H:i:s'),
    'customer_name' => 'Ama Serwaa',
    'payment_method' => 'momo',
    'payment_reference' => 'MOMO-45829317',
    'payer_name' => 'Ama Serwaa',
    'payment_status' => 'pending',
    'shipping_address' => "Ama Serwaa\n+233 551 812 055\nEast Legon\nAccra\nGhana",
    'subtotal' => 420.00,
    'shipping_fee' => 30.00,
    'total_amount' => 450.00,
];

$orderItems = [
    [
        'product_name' => 'Midnight Draped Dress',
        'quantity' => 1,
        'line_total' => 280.00,
        'variant_label' => 'Color: Black · Size: M',
        'image' => $sampleImage,
    ],
    [
        'product_name' => 'Sculpted Gold Earrings',
        'quantity' => 1,
        'line_total' => 140.00,
        'variant_label' => 'Finish: Gold',
        'image' => $sampleImage,
    ],
];

$emailTitle = 'Your RarityByAdel order is awaiting payment verification';
$preheader = 'We’ve received your payment reference and your order is now awaiting verification.';

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($emailTitle) ?></title>
</head>
<body style="margin:0; padding:0; background-color:#f4ede7; font-family:Arial, Helvetica, sans-serif; color:#111111;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
        <?= e($preheader) ?>
    </div>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f4ede7; margin:0; padding:0;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:640px; background-color:#fcf8f4; border:1px solid #e6dbd2; border-radius:24px; overflow:hidden;">
                    <tr>
                        <td style="padding:36px 32px 22px; text-align:center; background-color:#111111;">
                            <div style="font-family:Georgia, 'Times New Roman', serif; font-size:30px; line-height:1.1; color:#f7efe8; letter-spacing:0.04em;">
                                RarityByAdel
                            </div>
                            <div style="margin-top:10px; font-size:12px; line-height:1.6; letter-spacing:0.18em; text-transform:uppercase; color:#d9c8bc;">
                                Refined pieces, thoughtfully delivered
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;">
                            <div style="margin-bottom:28px; text-align:center;">
                                <div style="display:inline-block; padding:8px 14px; border-radius:999px; background:#efe3d8; color:#7b5f4f; font-size:12px; line-height:1; letter-spacing:0.12em; text-transform:uppercase; font-weight:bold;">
                                    Payment Reference Received
                                </div>
                            </div>

                            <h1 style="margin:0 0 14px; text-align:center; font-family:Georgia, 'Times New Roman', serif; font-size:34px; line-height:1.15; color:#111111; font-weight:normal;">
                                We’ve received your payment reference
                            </h1>

                            <p style="margin:0 0 10px; font-size:16px; line-height:1.8; color:#3d342e; text-align:center;">
                                Dear <?= e((string) $order['customer_name']) ?>,
                            </p>

                            <p style="margin:0 auto 28px; max-width:520px; font-size:15px; line-height:1.9; color:#5b4e46; text-align:center;">
                                Thank you for shopping with RarityByAdel. We’ve received your order and payment reference details successfully.
                                Our team will now review your payment and confirm your order once verification is complete.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:26px; background:#ffffff; border:1px solid #eadfd6; border-radius:18px;">
                                <tr>
                                    <td style="padding:22px 22px 8px;">
                                        <div style="font-size:18px; line-height:1.4; color:#111111; font-weight:bold; margin-bottom:14px;">
                                            Order Summary
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 22px 22px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td style="padding:8px 0; font-size:14px; color:#7b6c60;">Order Number</td>
                                                <td style="padding:8px 0; font-size:14px; color:#111111; font-weight:bold;" align="right">#<?= e((string) $order['order_number']) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0; font-size:14px; color:#7b6c60;">Date</td>
                                                <td style="padding:8px 0; font-size:14px; color:#111111; font-weight:bold;" align="right"><?= e(date('M d, Y h:i A', strtotime((string) $order['created_at']))) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0; font-size:14px; color:#7b6c60;">Payment Method</td>
                                                <td style="padding:8px 0; font-size:14px; color:#111111; font-weight:bold;" align="right"><?= e(ucfirst((string) $order['payment_method'])) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0; font-size:14px; color:#7b6c60;">Payer Name</td>
                                                <td style="padding:8px 0; font-size:14px; color:#111111; font-weight:bold;" align="right"><?= e((string) $order['payer_name']) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0; font-size:14px; color:#7b6c60;">Payment Reference</td>
                                                <td style="padding:8px 0; font-size:14px; color:#111111; font-weight:bold;" align="right"><?= e((string) $order['payment_reference']) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0; font-size:14px; color:#7b6c60;">Status</td>
                                                <td style="padding:8px 0;" align="right">
                                                    <span style="display:inline-block; padding:7px 12px; border-radius:999px; background:#f1e7de; color:#6d5646; font-size:12px; line-height:1; font-weight:bold; letter-spacing:0.06em; text-transform:uppercase;">
                                                        Awaiting Verification
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-bottom:16px; font-size:18px; line-height:1.4; color:#111111; font-weight:bold;">
                                Items in your order
                            </div>

                            <?php foreach ($orderItems as $item): ?>
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:14px; background:#ffffff; border:1px solid #eadfd6; border-radius:18px;">
                                    <tr>
                                        <td style="padding:16px;">
                                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <td width="88" valign="top" style="padding-right:14px;">
                                                        <?php if (!empty($item['image'])): ?>
                                                            <img src="<?= e((string) $item['image']) ?>" alt="<?= e((string) $item['product_name']) ?>" width="72" height="88" style="display:block; width:72px; height:88px; object-fit:cover; border:0; border-radius:12px; background:#efe6de;">
                                                        <?php else: ?>
                                                            <div style="width:72px; height:88px; border-radius:12px; background:#efe6de;"></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td valign="top">
                                                        <div style="font-size:16px; line-height:1.6; color:#111111; font-weight:bold; margin-bottom:4px;">
                                                            <?= e((string) $item['product_name']) ?>
                                                        </div>
                                                        <?php if (!empty($item['variant_label'])): ?>
                                                            <div style="font-size:13px; line-height:1.7; color:#7b6c60; margin-bottom:2px;">
                                                                <?= e((string) $item['variant_label']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div style="font-size:13px; line-height:1.7; color:#7b6c60;">
                                                            Qty: <?= e((string) $item['quantity']) ?>
                                                        </div>
                                                    </td>
                                                    <td valign="top" align="right">
                                                        <div style="font-size:15px; line-height:1.6; color:#111111; font-weight:bold;">
                                                            ₵<?= e(number_format((float) $item['line_total'], 2)) ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            <?php endforeach; ?>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:10px; margin-bottom:26px; background:#fffdfa; border:1px solid #eadfd6; border-radius:18px;">
                                <tr>
                                    <td style="padding:20px 22px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td style="padding:6px 0; font-size:14px; color:#7b6c60;">Subtotal</td>
                                                <td align="right" style="padding:6px 0; font-size:14px; color:#111111;">₵<?= e(number_format((float) $order['subtotal'], 2)) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0; font-size:14px; color:#7b6c60;">Shipping</td>
                                                <td align="right" style="padding:6px 0; font-size:14px; color:#111111;">₵<?= e(number_format((float) $order['shipping_fee'], 2)) ?></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:12px 0 0; font-size:16px; color:#111111; font-weight:bold; border-top:1px solid #eadfd6;">Total</td>
                                                <td align="right" style="padding:12px 0 0; font-size:16px; color:#111111; font-weight:bold; border-top:1px solid #eadfd6;">₵<?= e(number_format((float) $order['total_amount'], 2)) ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#ffffff; border:1px solid #eadfd6; border-radius:18px; margin-bottom:24px;">
                                <tr>
                                    <td style="padding:22px;">
                                        <div style="font-size:18px; line-height:1.4; color:#111111; font-weight:bold; margin-bottom:12px;">
                                            Shipping Address
                                        </div>
                                        <div style="font-size:14px; line-height:1.9; color:#5b4e46; white-space:pre-line;">
                                            <?= e((string) $order['shipping_address']) ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0; font-size:14px; line-height:1.9; color:#5b4e46; text-align:center;">
                                We’ll send you another update as soon as your payment is verified and your order moves to the next stage.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f6eee7; border:1px solid #eadfd6; border-radius:18px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <div style="font-size:13px; line-height:1.8; color:#4f443d; text-align:center;">
                                            Need assistance? Reply to this email or contact our support team.<br>
                                            <a href="mailto:hello@raritybyadel.com" style="color:#111111; text-decoration:none; font-weight:bold;">hello@raritybyadel.com</a> ·
                                            <a href="tel:+233551812055" style="color:#111111; text-decoration:none; font-weight:bold;">+233 551 812 055</a> ·
                                            WhatsApp: +233 222 330 949
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 34px; text-align:center;">
                            <div style="font-size:12px; line-height:1.8; color:#7c6d61;">
                                You are receiving this email because an order was placed with RarityByAdel.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php
echo ob_get_clean();