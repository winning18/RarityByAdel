<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

$pageTitle = 'Login';
$currentPage = '';

$loginError = '';
$emailValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailValue = strtolower(trim((string) ($_POST['email'] ?? '')));
    $passwordValue = (string) ($_POST['password'] ?? '');

    if ($emailValue === '' || $passwordValue === '') {
        $loginError = 'Please enter both your email address and password.';
    } else {
        try {
            $db = Database::getInstance();

            $stmt = $db->prepare('SELECT id, full_name, email, password_hash, is_admin FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$emailValue]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($passwordValue, $user['password_hash'])) {
                $loginError = 'Invalid email or password.';
            } else {
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id' => (int) $user['id'],
                    'name' => $user['full_name'],
                    'email' => $user['email'],
                    'is_admin' => (int) $user['is_admin']
                ];

                header('Location: ' . url('index.php'));
                exit;
            }
        } catch (Throwable $e) {
            $loginError = 'Something went wrong while signing in. Please try again.';
        }
    }
}

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="auth-page-section login-page">
    <div class="container">
        <div class="auth-shell">
            <div class="auth-side">
                <span class="auth-kicker">Welcome Back</span>
                <h1>Sign in to your account</h1>
                <p>Access your orders, continue shopping, and manage your premium experience with ease.</p>
            </div>

            <div class="auth-box">
                <h2>Customer Login</h2>

                <?php if ($loginError !== ''): ?>
                    <div class="auth-error-message"><?= e($loginError) ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="auth-clean-form" novalidate>
                    <div class="auth-field">
                        <label for="login_email">Email Address</label>
                        <input
                            type="email"
                            id="login_email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?= e($emailValue) ?>"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="login_password">Password</label>
                        <input
                            type="password"
                            id="login_password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <div class="auth-meta-row">
                        <label class="auth-check">
                            <input type="checkbox" name="remember_me">
                            <span>Remember me</span>
                        </label>

                        <a href="<?= url('forgot-password.php') ?>" class="auth-link">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-dark auth-main-btn">Sign In</button>
                </form>

                <div class="auth-divider-line">
                    <span>or</span>
                </div>

                <a href="https://accounts.google.com/" target="_blank" rel="noopener noreferrer" class="auth-google-btn">
                    <span class="google-mark">G</span>
                    <span>Continue with Google</span>
                </a>

                <p class="auth-bottom-text">Don’t have an account? <a href="<?= url('register.php') ?>">Create one</a></p>
                <p class="auth-bottom-text">Admin access? <a href="<?= url('admin/login.php') ?>">Go to admin login</a></p>
            </div>
        </div>
    </div>
</section>

<style>
.login-page {
    padding: 60px 0 80px;
    background: linear-gradient(180deg, #fffaf6 0%, #f7efe9 100%);
}

.login-page .auth-shell {
    display: grid;
    grid-template-columns: 1fr 430px;
    gap: 30px;
    align-items: stretch;
}

.login-page .auth-side {
    background: linear-gradient(180deg, #111111 0%, #241f1b 100%);
    color: #fff;
    border-radius: 28px;
    padding: 42px 36px;
    box-shadow: var(--shadow-soft);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-page .auth-kicker {
    display: inline-block;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.78rem;
    color: rgba(255,255,255,0.72);
    font-weight: 800;
}

.login-page .auth-side h1 {
    margin: 0 0 14px;
    font-family: var(--font-heading);
    font-size: clamp(2.4rem, 4vw, 4.2rem);
    line-height: 0.95;
    max-width: 10ch;
}

.login-page .auth-side p {
    margin: 0;
    max-width: 42ch;
    color: rgba(255,255,255,0.76);
    line-height: 1.7;
}

.login-page .auth-box {
    background: rgba(255,255,255,0.92);
    border: 1px solid rgba(17,17,17,0.08);
    border-radius: 28px;
    padding: 34px 30px;
    box-shadow: var(--shadow-soft);
}

.login-page .auth-box h2 {
    margin: 0 0 22px;
    font-family: var(--font-heading);
    font-size: 2.1rem;
    line-height: 1;
}

.login-page .auth-error-message {
    margin-bottom: 18px;
    padding: 14px 16px;
    border-radius: 16px;
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.18);
    color: #8f1f14;
    font-weight: 700;
}

.login-page .auth-clean-form {
    display: grid;
    gap: 18px;
}

.login-page .auth-field {
    display: grid;
    gap: 8px;
}

.login-page .auth-field label {
    font-weight: 800;
    font-size: 0.95rem;
}

.login-page .auth-field input {
    width: 100%;
    height: 54px;
    border-radius: 16px;
    border: 1px solid rgba(17,17,17,0.1);
    background: #fff;
    padding: 0 16px;
    outline: none;
}

.login-page .auth-field input:focus {
    border-color: rgba(17,17,17,0.22);
    box-shadow: 0 0 0 4px rgba(17,17,17,0.05);
}

.login-page .auth-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.login-page .auth-check {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: var(--color-text-soft);
    font-size: 0.94rem;
}

.login-page .auth-link,
.login-page .auth-bottom-text a {
    font-weight: 800;
    color: var(--color-text);
}


    
    .login-page .auth-main-btn {
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 54px;

    /* primary look */
    border-radius: 999px;
    border: 1px solid #111111;
    background: #111111;
    color: #ffffff;
    font-weight: 800;
}

/* Optional hover/focus state */
.login-page .auth-main-btn:hover,
.login-page .auth-main-btn:focus-visible {
    background: #2a211d;
    border-color: #2a211d;
}
    
    

.login-page .auth-divider-line {
    position: relative;
    margin: 22px 0;
    text-align: center;
}

.login-page .auth-divider-line::before {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 1px;
    background: rgba(17,17,17,0.08);
}

.login-page .auth-divider-line span {
    position: relative;
    z-index: 1;
    background: rgba(255,255,255,0.92);
    padding: 0 14px;
    color: var(--color-text-soft);
}

.login-page .auth-google-btn {
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

.login-page .google-mark {
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

.login-page .auth-bottom-text {
    margin: 18px 0 0;
    text-align: center;
    color: var(--color-text-soft);
}

@media (max-width: 900px) {
    .login-page .auth-shell {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .login-page {
        padding: 42px 0 64px;
    }

    .login-page .auth-side,
    .login-page .auth-box {
        padding: 24px 20px;
        border-radius: 22px;
    }

    .login-page .auth-box h2 {
        font-size: 1.9rem;
    }
}
</style>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>