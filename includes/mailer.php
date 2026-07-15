<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/mail.php';

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

function rarity_send_mail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $altBody = ''
): array {
    if (!file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
        error_log('RarityByAdel mail skipped: vendor/autoload.php not found.');

        return [
            'success' => false,
            'message' => 'Mailer dependency not installed yet.',
        ];
    }

    if (
        !class_exists(\PHPMailer\PHPMailer\PHPMailer::class) ||
        !class_exists(\PHPMailer\PHPMailer\Exception::class)
    ) {
        error_log('RarityByAdel mail skipped: PHPMailer classes not available.');

        return [
            'success' => false,
            'message' => 'PHPMailer is not available.',
        ];
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = MAIL_PORT;
        $mail->Timeout = 30;
        $mail->CharSet = 'UTF-8';

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addReplyTo(MAIL_REPLY_TO_EMAIL, MAIL_REPLY_TO_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody !== '' ? $altBody : trim(strip_tags($htmlBody));

        $mail->send();

        return [
            'success' => true,
            'message' => 'Email sent successfully.',
        ];
    } catch (\Throwable $e) {
        error_log('RarityByAdel mail error: ' . $e->getMessage());

        return [
            'success' => false,
            'message' => $e->getMessage(),
        ];
    }
}