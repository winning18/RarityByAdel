<?php
declare(strict_types=1);

require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

// Ensure session is active before reading $_SESSION
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Redirect guests to user login
if (empty($_SESSION['user']['id'])) {
    header('Location: ' . url('login.php'));
    exit;
}

$db = Database::getInstance();
$userId = (int) $_SESSION['user']['id'];
$success = '';
$error = '';

// Load current user
$stmt = $db->prepare("
    SELECT id, full_name, email, phone, address, created_at, updated_at
    FROM users
    WHERE id = :id
    LIMIT 1
");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    // User record missing; reset session and send to login
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    header('Location: ' . url('login.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($fullName === '') {
        $error = 'Full name is required.';
    } else {
        $update = $db->prepare("
            UPDATE users
            SET full_name = :full_name,
                phone = :phone,
                address = :address,
                updated_at = NOW()
            WHERE id = :id
            LIMIT 1
        ");

        $update->execute([
            'full_name' => $fullName,
            'phone' => $phone !== '' ? $phone : null,
            'address' => $address !== '' ? $address : null,
            'id' => $userId,
        ]);

        // Keep session user name in sync
        $_SESSION['user']['name'] = $fullName;

        // Reload fresh user data
        $stmt = $db->prepare("
            SELECT id, full_name, email, phone, address, created_at, updated_at
            FROM users
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        $success = 'Profile updated successfully.';
    }
}

$pageTitle = 'My Profile';
$currentPage = '';

require __DIR__ . '/app/Views/partials/header.php';
?>

<style>
    .account-page {
        padding: 56px 0 80px;
        background: #f3ede8;
    }

    .account-shell {
        max-width: 900px;
        margin: 0 auto;
    }

    .account-card {
        background: #ffffff;
        border: 1px solid #e6dbd1;
        border-radius: 24px;
        padding: 32px;
        box-shadow: 0 14px 34px rgba(34, 24, 19, 0.06);
    }

    .account-header {
        margin-bottom: 24px;
    }

    .account-header h1 {
        margin: 0 0 10px;
        font-family: 'Cormorant Garamond', serif;
        font-size: 3rem;
        line-height: 0.95;
        color: #201713;
    }

    .account-header p {
        margin: 0;
        max-width: 580px;
        color: #6f5e53;
        font-size: 0.98rem;
        line-height: 1.7;
    }

    .account-alert {
        margin-bottom: 20px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .account-alert-success {
        background: #e8f6ec;
        color: #24613c;
    }

    .account-alert-error {
        background: #fdeceb;
        color: #8c2c2c;
    }

    .account-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 22px;
        align-items: start;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        margin: 0 0 8px;
        color: #2b1d18;
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .form-group input,
    .form-group textarea {
        display: block;
        width: 100%;
        min-width: 0;
        margin: 0;
        padding: 14px 16px;
        border: 1px solid #d8cabf;
        border-radius: 14px;
        background: #fffdfa;
        color: #2b1d18;
        font-size: 0.98rem;
        line-height: 1.5;
        box-sizing: border-box;
        -webkit-appearance: none;
        appearance: none;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .form-group textarea {
        min-height: 140px;
        resize: vertical;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        border-color: #8b5e3c;
        box-shadow: 0 0 0 4px rgba(139, 94, 60, 0.12);
    }

    .form-group input[readonly] {
        background: #f3ede7;
        color: #77675d;
        cursor: not-allowed;
    }

    .account-actions {
        margin-top: 24px;
        display: flex;
        justify-content: flex-start;
    }

    .account-btn {
        border: none;
        border-radius: 999px;
        background: #16110f;
        color: #ffffff;
        padding: 14px 24px;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .account-btn:hover {
        transform: translateY(-1px);
        opacity: 0.95;
    }

    .account-meta {
        display: grid;
        gap: 10px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #eee3d9;
        color: #6f5e53;
        font-size: 0.92rem;
    }

    .account-meta span {
        color: #2b1d18;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .account-page {
            padding: 36px 0 60px;
        }

        .account-card {
            padding: 22px;
            border-radius: 18px;
        }

        .account-header h1 {
            font-size: 2.3rem;
        }

        .account-form-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .form-group-full {
            grid-column: auto;
        }

        .account-actions {
            display: block;
        }

        .account-btn {
            width: 100%;
        }
    }
</style>

<main id="main-content">
    <section class="account-page">
        <div class="container">
            <div class="account-shell">
                <div class="account-card">
                    <div class="account-header">
                        <h1>My Profile</h1>
                        <p>Manage your personal details for smoother checkout and better order updates.</p>
                    </div>

                    <?php if ($success !== ''): ?>
                        <div class="account-alert account-alert-success"><?= e($success) ?></div>
                    <?php endif; ?>

                    <?php if ($error !== ''): ?>
                        <div class="account-alert account-alert-error"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="post" class="account-form">
                        <div class="account-form-grid">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    value="<?= e($user['full_name'] ?? '') ?>"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input
                                    type="email"
                                    id="email"
                                    value="<?= e($user['email'] ?? '') ?>"
                                    readonly
                                >
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input
                                    type="text"
                                    id="phone"
                                    name="phone"
                                    value><?= e($user['phone'] ?? '') ?>
                                >
                            </div>

                            <div class="form-group form-group-full">
                                <label for="address">Address</label>
                                <textarea
                                    id="address"
                                    name="address"
                                    rows="5"
                                ><?= e($user['address'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="account-actions">
                            <button type="submit" class="account-btn">Update Profile</button>
                        </div>
                    </form>

                    <div class="account-meta">
                        <div><span>Member since:</span> <?= e($user['created_at'] ?? '') ?></div>
                        <div><span>Last updated:</span> <?= e($user['updated_at'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>