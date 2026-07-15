<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

$pageTitle = 'Register';
$currentPage = '';

$errors = [];
$successMessage = '';

$fullName = '';
$email = '';
$termsChecked = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $termsChecked = isset($_POST['terms']);

    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    }

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($confirmPassword === '') {
        $errors[] = 'Confirm password is required.';
    } elseif ($password !== '' && $password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$termsChecked) {
        $errors[] = 'You must agree to the store terms and privacy policy.';
    }

    if (empty($errors)) {
    try {
        $db = Database::getInstance();

        $email = strtolower($email);

        $checkStmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $checkStmt->execute([$email]);

        if ($checkStmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $insertStmt = $db->prepare('
                INSERT INTO users (full_name, email, password_hash)
                VALUES (?, ?, ?)
            ');

            $insertStmt->execute([$fullName, $email, $passwordHash]);

            $successMessage = 'Your account has been created successfully. You can now sign in.';
            $fullName = '';
            $email = '';
            $termsChecked = false;
        }
    } catch (Throwable $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }
}

}

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="auth-page-section register-page">
    <div class="container">
        <div class="auth-shell">
            <div class="auth-side">
                <span class="auth-kicker">Create Account</span>
                <h1>Start your RarityByAdel account</h1>
                <p>Create your account to enjoy faster checkout, order updates, and a smoother shopping journey.</p>
            </div>

            <div class="auth-box">
                <h2>Register</h2>

                <?php if (!empty($errors)): ?>
                    <div class="auth-error-message">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($successMessage !== ''): ?>
                    <div class="auth-success-message">
                        <?= e($successMessage) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="auth-clean-form" novalidate>
                    <div class="auth-field">
                        <label for="register_name">Full Name</label>
                        <input
                            type="text"
                            id="register_name"
                            name="full_name"
                            placeholder="Enter your full name"
                            value="<?= e($fullName) ?>"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="register_email">Email Address</label>
                        <input
                            type="email"
                            id="register_email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?= e($email) ?>"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="register_password">Password</label>
                        <input
                            type="password"
                            id="register_password"
                            name="password"
                            placeholder="Create a password"
                            required
                            minlength="8"
                        >
                    </div>

                    <div class="auth-field">
                        <label for="register_confirm_password">Confirm Password</label>
                        <input
                            type="password"
                            id="register_confirm_password"
                            name="confirm_password"
                            placeholder="Confirm your password"
                            required
                            minlength="8"
                        >
                    </div>

                    <label class="auth-check">
                        <input
                            type="checkbox"
                            name="terms"
                            value="1"
                            <?= $termsChecked ? 'checked' : '' ?>
                            required
                        >
                        <span>I agree to the store terms and privacy policy.</span>
                    </label>

                    <button type="submit" class="btn btn-dark auth-main-btn">Create Account</button>
                </form>

                <div class="auth-divider-line">
                    <span>or</span>
                </div>

                <a href="https://accounts.google.com/AccountChooser" target="_blank" rel="noopener noreferrer" class="auth-google-btn">
                    <span class="google-mark">G</span>
                    <span>Continue with Google</span>
                </a>

                <p class="auth-bottom-text">Already have an account? <a href="<?= url('login.php') ?>">Sign in</a></p>
            </div>
        </div>
    </div>
</section>

<style>
.register-page {
    padding: 60px 0 80px;
    background: linear-gradient(180deg, #fffaf6 0%, #f7efe9 100%);
}

.register-page .auth-shell {
    display: grid;
    grid-template-columns: 1fr 430px;
    gap: 30px;
    align-items: stretch;
}

.register-page .auth-side {
    background: linear-gradient(180deg, #111111 0%, #241f1b 100%);
    color: #fff;
    border-radius: 28px;
    padding: 42px 36px;
    box-shadow: var(--shadow-soft);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.register-page .auth-kicker {
    display: inline-block;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.78rem;
    color: rgba(255,255,255,0.72);
    font-weight: 800;
}

.register-page .auth-side h1 {
    margin: 0 0 14px;
    font-family: var(--font-heading);
    font-size: clamp(2.4rem, 4vw, 4.2rem);
    line-height: 0.95;
    max-width: 11ch;
}

.register-page .auth-side p {
    margin: 0;
    max-width: 42ch;
    color: rgba(255,255,255,0.76);
    line-height: 1.7;
}

.register-page .auth-box {
    background: rgba(255,255,255,0.92);
    border: 1px solid rgba(17,17,17,0.08);
    border-radius: 28px;
    padding: 34px 30px;
    box-shadow: var(--shadow-soft);
}

.register-page .auth-box h2 {
    margin: 0 0 22px;
    font-family: var(--font-heading);
    font-size: 2.1rem;
    line-height: 1;
}

.register-page .auth-error-message {
    margin-bottom: 18px;
    padding: 14px 16px;
    border-radius: 16px;
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.18);
    color: #8f1f14;
    font-weight: 700;
}

.register-page .auth-error-message ul {
    margin: 0;
    padding-left: 18px;
}

.register-page .auth-error-message li + li {
    margin-top: 6px;
}

.register-page .auth-success-message {
    margin-bottom: 18px;
    padding: 14px 16px;
    border-radius: 16px;
    background: rgba(39, 128, 72, 0.08);
    border: 1px solid rgba(39, 128, 72, 0.18);
    color: #20613a;
    font-weight: 700;
}

.register-page .auth-clean-form {
    display: grid;
    gap: 18px;
}

.register-page .auth-field {
    display: grid;
    gap: 8px;
}

.register-page .auth-field label {
    font-weight: 800;
    font-size: 0.95rem;
}

.register-page .auth-field input {
    width: 100%;
    height: 54px;
    border-radius: 16px;
    border: 1px solid rgba(17,17,17,0.1);
    background: #fff;
    padding: 0 16px;
    outline: none;
}

.register-page .auth-field input:focus {
    border-color: rgba(17,17,17,0.22);
    box-shadow: 0 0 0 4px rgba(17,17,17,0.05);
}

.register-page .auth-check {
    display: inline-flex;
    align-items: flex-start;
    gap: 10px;
    color: var(--color-text-soft);
    font-size: 0.94rem;
    line-height: 1.5;
}

.register-page .auth-check input {
    margin-top: 2px;
}

.register-page .auth-main-btn {
    width: 100%;
    justify-content: center;
    min-height: 54px;
}

.register-page .auth-divider-line {
    position: relative;
    margin: 22px 0;
    text-align: center;
}

.register-page .auth-divider-line::before {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 1px;
    background: rgba(17,17,17,0.08);
}

.register-page .auth-divider-line span {
    position: relative;
    z-index: 1;
    background: rgba(255,255,255,0.92);
    padding: 0 14px;
    color: var(--color-text-soft);
}

.register-page .auth-google-btn {
    width: 100%;
    min-height: 54px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    border-radius: 16px;
    border: 1px solid rgba(17,17,17,0.1);
    background: #fff;
    font-weight: 700;
}

.register-page .google-mark {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.92rem;
    font-weight: 800;
    color: #fff;
    background: #db4437;
    flex-shrink: 0;
}

.register-page .auth-bottom-text {
    margin: 18px 0 0;
    text-align: center;
    color: var(--color-text-soft);
}

.register-page .auth-bottom-text a {
    font-weight: 800;
    color: var(--color-text);
}

@media (max-width: 900px) {
    .register-page .auth-shell {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .register-page {
        padding: 42px 0 64px;
    }

    .register-page .auth-side,
    .register-page .auth-box {
        padding: 24px 20px;
        border-radius: 22px;
    }

    .register-page .auth-box h2 {
        font-size: 1.9rem;
    }
}
</style>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>