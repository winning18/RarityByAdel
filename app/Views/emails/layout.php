<?php
/**
 * Shared email layout for RarityByAdel transactional emails.
 *
 * Expected variables:
 * - $emailTitle
 * - $preheader
 * - $contentHtml
 */

$emailTitle = $emailTitle ?? 'RarityByAdel';
$preheader = $preheader ?? '';
$contentHtml = $contentHtml ?? '';

$brandName = 'RarityByAdel';
$supportEmail = 'hello@raritybyadel.com';
$supportPhone = '+233 551 812 055';
$whatsApp = '+233 222 330 949';
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
                                <?= e($brandName) ?>
                            </div>
                            <div style="margin-top:10px; font-size:12px; line-height:1.6; letter-spacing:0.18em; text-transform:uppercase; color:#d9c8bc;">
                                Refined pieces, thoughtfully delivered
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;">
                            <?= $contentHtml ?>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f6eee7; border:1px solid #eadfd6; border-radius:18px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <div style="font-size:13px; line-height:1.8; color:#4f443d; text-align:center;">
                                            Need assistance? Reply to this email or contact our support team.<br>
                                            <a href="mailto:<?= e($supportEmail) ?>" style="color:#111111; text-decoration:none; font-weight:bold;"><?= e($supportEmail) ?></a> ·
                                            <a href="tel:+233551812055" style="color:#111111; text-decoration:none; font-weight:bold;"><?= e($supportPhone) ?></a> ·
                                            WhatsApp: <?= e($whatsApp) ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 34px; text-align:center;">
                            <div style="font-size:12px; line-height:1.8; color:#7c6d61;">
                                You are receiving this email because an order was placed with <?= e($brandName) ?>.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>