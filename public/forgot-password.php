<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

$pageTitle = 'Forgot Password';
$currentPage = '';

$errorMessage = '';
$successMessage = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($email === '') {
        $errorMessage = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } else {
        /*
         * Test/demo behavior:
         * In production, generate a reset token and send a password reset email.
         * For security, keep the response generic instead of revealing
         * whether the email exists in the system.
         */
        $successMessage = 'If an account exists for that email, a password reset link has been sent.';
        $email = '';
    }
}

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="auth-page-section forgot-page">
    <div class="container">
        <div class="auth-shell">
            <div class="auth-side">
                <span class="auth-kicker">Account Recovery</span>
                <h1>Reset your password</h1>
                <p>Enter your email address and we’ll send you instructions to reset your password and regain access to your account.</p>
            </div>

            <div class="auth-box">
                <h2>Forgot Password</h2>
                <p class="auth-intro-text">Enter the email linked to your account.</p>

                <?php if ($errorMessage !== ''): ?>
                    <div class="auth-error-message"><?= e($errorMessage) ?></div>
                <?php endif; ?>

                <?php if ($successMessage !== ''): ?>
                    <div class="auth-success-message"><?= e($successMessage) ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="auth-clean-form" novalidate>
                    <div class="auth-field">
                        <label for="forgot_email">Email Address</label>
                        <input
                            type="email"
                            id="forgot_email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?= e($email) ?>"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-dark auth-main-btn">Send Reset Link</button>
                </form>

                <p class="auth-bottom-text">
                    Remembered your password? <a href="<?= url('login.php') ?>">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</section>

<style>
.forgot-page {
    padding: 60px 0 80px;
    background: linear-gradient(180deg, #fffaf6 0%, #f7efe9 100%);
}

.forgot-page .auth-shell {
    display: grid;
    grid-template-columns: 1fr 430px;
    gap: 30px;
    align-items: stretch;
}

.forgot-page .auth-side {
    background: linear-gradient(180deg, #111111 0%, #241f1b 100%);
    color: #fff;
    border-radius: 28px;
    padding: 42px 36px;
    box-shadow: var(--shadow-soft);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.forgot-page .auth-kicker {
    display: inline-block;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.78rem;
    color: rgba(255,255,255,0.72);
    font-weight: 800;
}

.forgot-page .auth-side h1 {
    margin: 0 0 14px;
    font-family: var(--font-heading);
    font-size: clamp(2.4rem, 4vw, 4.2rem);
    line-height: 0.95;
    max-width: 10ch;
}

.forgot-page .auth-side p {
    margin: 0;
    max-width: 42ch;
    color: rgba(255,255,255,0.76);
    line-height: 1.7;
}

.forgot-page .auth-box {
    background: rgba(255,255,255,0.92);
    border: 1px solid rgba(17,17,17,0.08);
    border-radius: 28px;
    padding: 34px 30px;
    box-shadow: var(--shadow-soft);
}

.forgot-page .auth-box h2 {
    margin: 0 0 12px;
    font-family: var(--font-heading);
    font-size: 2.1rem;
    line-height: 1;
}

.forgot-page .auth-intro-text {
    margin: 0 0 22px;
    color: var(--color-text-soft);
    line-height: 1.6;
}

.forgot-page .auth-error-message {
    margin-bottom: 18px;
    padding: 14px 16px;
    border-radius: 16px;
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.18);
    color: #8f1f14;
    font-weight: 700;
}

.forgot-page .auth-success-message {
    margin-bottom: 18px;
    padding: 14px 16px;
    border-radius: 16px;
    background: rgba(39, 128, 72, 0.08);
    border: 1px solid rgba(39, 128, 72, 0.18);
    color: #20613a;
    font-weight: 700;
    line-height: 1.5;
}

.forgot-page .auth-clean-form {
    display: grid;
    gap: 18px;
}

.forgot-page .auth-field {
    display: grid;
    gap: 8px;
}

.forgot-page .auth-field label {
    font-weight: 800;
    font-size: 0.95rem;
}

.forgot-page .auth-field input {
    width: 100%;
    height: 54px;
    border-radius: 16px;
    border: 1px solid rgba(17,17,17,0.1);
    background: #fff;
    padding: 0 16px;
    outline: none;
}

.forgot-page .auth-field input:focus {
    border-color: rgba(17,17,17,0.22);
    box-shadow: 0 0 0 4px rgba(17,17,17,0.05);
}

.forgot-page .auth-main-btn {
    width: 100%;
    justify-content: center;
    min-height: 54px;
}

.forgot-page .auth-bottom-text {
    margin: 18px 0 0;
    text-align: center;
    color: var(--color-text-soft);
}

.forgot-page .auth-bottom-text a {
    font-weight: 800;
    color: var(--color-text);
}

@media (max-width: 900px) {
    .forgot-page .auth-shell {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .forgot-page {
        padding: 42px 0 64px;
    }

    .forgot-page .auth-side,
    .forgot-page .auth-box {
        padding: 24px 20px;
        border-radius: 22px;
    }

    .forgot-page .auth-box h2 {
        font-size: 1.9rem;
    }
}
</style>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>