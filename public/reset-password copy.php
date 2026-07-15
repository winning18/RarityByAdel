<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

$pageTitle = 'Reset Password';
$currentPage = '';

$errorMessage = '';
$successMessage = '';

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$password = '';
$confirmPassword = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($token === '') {
        $errorMessage = 'Invalid or missing password reset token.';
    } elseif ($password === '') {
        $errorMessage = 'New password is required.';
    } elseif (strlen($password) < 8) {
        $errorMessage = 'New password must be at least 8 characters.';
    } elseif ($confirmPassword === '') {
        $errorMessage = 'Please confirm your new password.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match.';
    } else {
        /*
         * Demo behavior:
         * In production, verify the token, ensure it is not expired,
         * update the stored password hash, and invalidate the token.
         */
        $successMessage = 'Your password has been reset successfully. You can now sign in with your new password.';
        $password = '';
        $confirmPassword = '';
    }
}

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="auth-page-section reset-page">
    <div class="container">
        <div class="auth-shell">
            <div class="auth-side">
                <span class="auth-kicker">New Password</span>
                <h1>Create a new password</h1>
                <p>Choose a strong new password for your account. Make sure it is easy for you to remember and hard for others to guess.</p>
            </div>

            <div class="auth-box">
                <h2>Reset Password</h2>
                <p class="auth-intro-text">Enter your new password below to complete the reset process.</p>

                <?php if ($errorMessage !== ''): ?>
                    <div class="auth-error-message"><?= e($errorMessage) ?></div>
                <?php endif; ?>

                <?php if ($successMessage !== ''): ?>
                    <div class="auth-success-message"><?= e($successMessage) ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="auth-clean-form" novalidate>
                    <input type="hidden" name="token" value="<?= e($token) ?>">

                    <div class="auth-field">
                        <label for="reset_password">New Password</label>
                        <input
                            type="password"
                            id="reset_password"
                            name="password"
                            placeholder="Enter your new password"
                            required
                            minlength="8"
                        >
                    </div>

                    <div class="auth-field">
                        <label for="reset_confirm_password">Confirm New Password</label>
                        <input
                            type="password"
                            id="reset_confirm_password"
                            name="confirm_password"
                            placeholder="Confirm your new password"
                            required
                            minlength="8"
                        >
                    </div>

                    <button type="submit" class="btn btn-dark auth-main-btn">Update Password</button>
                </form>

                <p class="auth-bottom-text">
                    Back to <a href="<?= url('login.php') ?>">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</section>

<style>
.reset-page {
    padding: 60px 0 80px;
    background: linear-gradient(180deg, #fffaf6 0%, #f7efe9 100%);
}

.reset-page .auth-shell {
    display: grid;
    grid-template-columns: 1fr 430px;
    gap: 30px;
    align-items: stretch;
}

.reset-page .auth-side {
    background: linear-gradient(180deg, #111111 0%, #241f1b 100%);
    color: #fff;
    border-radius: 28px;
    padding: 42px 36px;
    box-shadow: var(--shadow-soft);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.reset-page .auth-kicker {
    display: inline-block;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.78rem;
    color: rgba(255,255,255,0.72);
    font-weight: 800;
}

.reset-page .auth-side h1 {
    margin: 0 0 14px;
    font-family: var(--font-heading);
    font-size: clamp(2.4rem, 4vw, 4.2rem);
    line-height: 0.95;
    max-width: 10ch;
}

.reset-page .auth-side p {
    margin: 0;
    max-width: 42ch;
    color: rgba(255,255,255,0.76);
    line-height: 1.7;
}

.reset-page .auth-box {
    background: rgba(255,255,255,0.92);
    border: 1px solid rgba(17,17,17,0.08);
    border-radius: 28px;
    padding: 34px 30px;
    box-shadow: var(--shadow-soft);
}

.reset-page .auth-box h2 {
    margin: 0 0 12px;
    font-family: var(--font-heading);
    font-size: 2.1rem;
    line-height: 1;
}

.reset-page .auth-intro-text {
    margin: 0 0 22px;
    color: var(--color-text-soft);
    line-height: 1.6;
}

.reset-page .auth-error-message {
    margin-bottom: 18px;
    padding: 14px 16px;
    border-radius: 16px;
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.18);
    color: #8f1f14;
    font-weight: 700;
}

.reset-page .auth-success-message {
    margin-bottom: 18px;
    padding: 14px 16px;
    border-radius: 16px;
    background: rgba(39, 128, 72, 0.08);
    border: 1px solid rgba(39, 128, 72, 0.18);
    color: #20613a;
    font-weight: 700;
    line-height: 1.5;
}

.reset-page .auth-clean-form {
    display: grid;
    gap: 18px;
}

.reset-page .auth-field {
    display: grid;
    gap: 8px;
}

.reset-page .auth-field label {
    font-weight: 800;
    font-size: 0.95rem;
}

.reset-page .auth-field input {
    width: 100%;
    height: 54px;
    border-radius: 16px;
    border: 1px solid rgba(17,17,17,0.1);
    background: #fff;
    padding: 0 16px;
    outline: none;
}

.reset-page .auth-field input:focus {
    border-color: rgba(17,17,17,0.22);
    box-shadow: 0 0 0 4px rgba(17,17,17,0.05);
}

.reset-page .auth-main-btn {
    width: 100%;
    justify-content: center;
    min-height: 54px;
}

.reset-page .auth-bottom-text {
    margin: 18px 0 0;
    text-align: center;
    color: var(--color-text-soft);
}

.reset-page .auth-bottom-text a {
    font-weight: 800;
    color: var(--color-text);
}

@media (max-width: 900px) {
    .reset-page .auth-shell {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .reset-page {
        padding: 42px 0 64px;
    }

    .reset-page .auth-side,
    .reset-page .auth-box {
        padding: 24px 20px;
        border-radius: 22px;
    }

    .reset-page .auth-box h2 {
        font-size: 1.9rem;
    }
}
</style>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>