<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// This file is in htdocs/admin, so __DIR__ is .../htdocs/admin
// Go up one level for shared config and database
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Admin Login';
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
            } elseif ((int) $user['is_admin'] !== 1) {
                $loginError = 'You do not have admin access.';
            } else {
                session_regenerate_id(true);

                $_SESSION['user'] = [
                    'id' => (int) $user['id'],
                    'name' => $user['full_name'],
                    'email' => $user['email'],
                    'is_admin' => (int) $user['is_admin']
                ];

                header('Location: ' . url('admin/dashboard.php'));
                exit;
            }
        } catch (Throwable $e) {
            $loginError = 'Something went wrong while signing in. Please try again.';
        }
    }
}

// Shared public header (not the admin_header)
require __DIR__ . '/../app/Views/partials/header.php';
?>

<section class="auth-page-section login-page">
    <div class="container">
        <div class="auth-shell">
            <div class="auth-side">
                <span class="auth-kicker">Restricted Access</span>
                <h1>Admin portal login</h1>
                <p>Sign in with an administrator account to manage users and protected store operations.</p>
            </div>

            <div class="auth-box">
                <h2>Admin Login</h2>

                <?php if ($loginError !== ''): ?>
                    <div class="auth-error-message"><?= e($loginError) ?></div>
                <?php endif; ?>

                <form action="" method="POST" class="auth-clean-form" novalidate>
                    <div class="auth-field">
                        <label for="admin_login_email">Email Address</label>
                        <input
                            type="email"
                            id="admin_login_email"
                            name="email"
                            placeholder="Enter your admin email"
                            value="<?= e($emailValue) ?>"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label for="admin_login_password">Password</label>
                        <input
                            type="password"
                            id="admin_login_password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-dark auth-main-btn">Sign In as Admin</button>
                </form>

                <p class="auth-bottom-text">Customer account? <a href="<?= url('login.php') ?>">Go to customer login</a></p>
            </div>
        </div>
    </div>
</section>

<style>
/* your CSS unchanged */
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

/login-page .auth-side p {
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

.login-page .auth-main-btn {
    width: 100%;
    justify-content: center;
    min-height: 54px;
}

.login-page .auth-bottom-text {
    margin: 18px 0 0;
    text-align: center;
    color: var(--color-text-soft);
}

.login-page .auth-bottom-text a {
    font-weight: 800;
    color: var(--color-text);
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

<?php require __DIR__ . '/../app/Views/partials/footer.php'; ?>