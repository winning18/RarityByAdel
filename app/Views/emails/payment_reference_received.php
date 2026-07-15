<?php
/**
 * Payment reference received email
 *
 * Expected variables:
 * - $order
 * - $orderItems
 *
 * Required order keys:
 * - order_number
 * - created_at
 * - customer_name
 * - payment_method
 * - payment_reference
 * - payer_name
 * - payment_status
 * - shipping_address
 * - total_amount
 * - subtotal
 * - shipping_fee
 */

$order = $order ?? [];
$orderItems = $orderItems ?? [];

ob_start();
?>

<div style="margin-bottom:28px; text-align:center;">
    <div style="display:inline-block; padding:8px 14px; border-radius:999px; background:#efe3d8; color:#7b5f4f; font-size:12px; line-height:1; letter-spacing:0.12em; text-transform:uppercase; font-weight:bold;">
        Payment Reference Received
    </div>
</div>

<h1 style="margin:0 0 14px; text-align:center; font-family:Georgia, 'Times New Roman', serif; font-size:34px; line-height:1.15; color:#111111; font-weight:normal;">
    We’ve received your payment reference
</h1>

<p style="margin:0 0 10px; font-size:16px; line-height:1.8; color:#3d342e; text-align:center;">
    Dear <?= e((string) ($order['customer_name'] ?? 'Customer')) ?>,
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
                    <td style="padding:8px 0; font-size:14px; color:#111111; font-weight:bold;" align="right">#<?= e((string) ($order['order_number'] ?? '')) ?></td>
                </tr>
                <tr>
                    <td style="padding:8px 0; font-size:14px; color:#7b6c60;">Date</td>
                    <td style="padding:8px 0; font-size:14px; color:#111111; font-weight:bold;" align="right">
                        <?= !empty($order['created_at']) ? e(date('M d, Y h:i A', strtotime((string) $order['created_at']))) : '—' ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 0; font-size:14px; color:#7b6c60;">Payment Method</td>
                    <td style="padding:8px 0; font-size:14px; color:#111111; font-weight:bold;" align="right"><?= e(ucfirst((string) ($order['payment_method'] ?? ''))) ?></td>
                </tr>
                <tr>
                    <td style="padding:8px 0; font-size:14px; color:#7b6c60;">Payer Name</td>
                    <td style="padding:8px 0; font-size:14px; color:#111111; font-weight:bold;" align="right"><?= e((string) ($order['payer_name'] ?? '—')) ?></td>
                </tr>
                <tr>
                    <td style="padding:8px 0; font-size:14px; color:#7b6c60;">Payment Reference</td>
                    <td style="padding:8px 0; font-size:14px; color:#111111; font-weight:bold;" align="right"><?= e((string) ($order['payment_reference'] ?? '—')) ?></td>
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
    <?php
    $productImage = '';
    if (!empty($item['image'])) {
        $productImage = (string) $item['image'];
    }
    ?>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:14px; background:#ffffff; border:1px solid #eadfd6; border-radius:18px;">
        <tr>
            <td style="padding:16px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td width="88" valign="top" style="padding-right:14px;">
                            <?php if ($productImage !== ''): ?>
                                <img src="<?= e($productImage) ?>" alt="<?= e((string) ($item['product_name'] ?? 'Product image')) ?>" width="72" height="88" style="display:block; width:72px; height:88px; object-fit:cover; border:0; border-radius:12px; background:#efe6de;">
                            <?php else: ?>
                                <div style="width:72px; height:88px; border-radius:12px; background:#efe6de;"></div>
                            <?php endif; ?>
                        </td>
                        <td valign="top">
                            <div style="font-size:16px; line-height:1.6; color:#111111; font-weight:bold; margin-bottom:4px;">
                                <?= e((string) ($item['product_name'] ?? 'Product')) ?>
                            </div>
                            <?php if (!empty($item['variant_label'])): ?>
                                <div style="font-size:13px; line-height:1.7; color:#7b6c60; margin-bottom:2px;">
                                    <?= e((string) $item['variant_label']) ?>
                                </div>
                            <?php endif; ?>
                            <div style="font-size:13px; line-height:1.7; color:#7b6c60;">
                                Qty: <?= e((string) ($item['quantity'] ?? 1)) ?>
                            </div>
                        </td>
                        <td valign="top" align="right">
                            <div style="font-size:15px; line-height:1.6; color:#111111; font-weight:bold;">
                                ₵<?= e(number_format((float) ($item['line_total'] ?? 0), 2)) ?>
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
                    <td align="right" style="padding:6px 0; font-size:14px; color:#111111;">₵<?= e(number_format((float) ($order['subtotal'] ?? 0), 2)) ?></td>
                </tr>
                <tr>
                    <td style="padding:6px 0; font-size:14px; color:#7b6c60;">Shipping</td>
                    <td align="right" style="padding:6px 0; font-size:14px; color:#111111;">₵<?= e(number_format((float) ($order['shipping_fee'] ?? 0), 2)) ?></td>
                </tr>
                <tr>
                    <td style="padding:12px 0 0; font-size:16px; color:#111111; font-weight:bold; border-top:1px solid #eadfd6;">Total</td>
                    <td align="right" style="padding:12px 0 0; font-size:16px; color:#111111; font-weight:bold; border-top:1px solid #eadfd6;">₵<?= e(number_format((float) ($order['total_amount'] ?? 0), 2)) ?></td>
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
            <div style="font-size:14px; line-height:1.9; color:#5b4e46;">
                <?= !empty($order['shipping_address']) ? nl2br(e((string) $order['shipping_address'])) : 'No shipping address provided.' ?>
            </div>
        </td>
    </tr>
</table>

<p style="margin:0; font-size:14px; line-height:1.9; color:#5b4e46; text-align:center;">
    We’ll send you another update as soon as your payment is verified and your order moves to the next stage.
</p>

<?php
$contentHtml = ob_get_clean();

$emailTitle = 'Your RarityByAdel order is awaiting payment verification';
$preheader = 'We’ve received your payment reference and your order is now awaiting verification.';

require __DIR__ . '/layout.php';