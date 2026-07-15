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

$stmt = $db->prepare("
    SELECT id, order_number, total_amount, payment_status, order_status, created_at
    FROM orders
    WHERE user_id = :user_id
    ORDER BY created_at DESC
");
$stmt->execute(['user_id' => $userId]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders';
$currentPage = '';

require __DIR__ . '/app/Views/partials/header.php';
?>

<style>
    .account-page {
        padding: 56px 0 80px;
        background: #f3ede8;
    }

    .account-shell {
        max-width: 1000px;
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
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }

    .account-header h1 {
        margin: 0;
        font-family: 'Cormorant Garamond', serif;
        font-size: 3rem;
        line-height: 0.95;
        color: #201713;
    }

    .account-header p {
        margin: 4px 0 0;
        max-width: 460px;
        color: #6f5e53;
        font-size: 0.98rem;
        line-height: 1.7;
    }

    .orders-summary {
        margin-bottom: 12px;
        color: #6f5e53;
        font-size: 0.94rem;
    }

    .orders-summary strong {
        color: #2b1d18;
    }

    .orders-table-wrapper {
        margin-top: 10px;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid #eee3d9;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 100%;
        background: #ffffff;
    }

    .orders-table thead {
        background: #f5eee7;
    }

    .orders-table th,
    .orders-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f0e5dd;
        text-align: left;
        font-size: 0.94rem;
        vertical-align: top;
    }

    .orders-table th {
        font-weight: 700;
        color: #3a2a21;
    }

    .orders-table tbody tr:last-child td {
        border-bottom: none;
    }

    .orders-empty {
        margin-top: 8px;
        padding: 18px 16px;
        border-radius: 16px;
        background: #f9f4ef;
        color: #6f5e53;
        font-size: 0.95rem;
    }

    .orders-empty strong {
        color: #2b1d18;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        border: 1px solid transparent;
    }

    .status-pill--paid,
    .status-pill--delivered {
        background: #e8f6ec;
        color: #24613c;
        border-color: #c6e5cf;
    }

    .status-pill--pending,
    .status-pill--processing {
        background: #fff4d9;
        color: #7a5d00;
        border-color: #f2dfb3;
    }

    .status-pill--failed,
    .status-pill--cancelled {
        background: #fdeceb;
        color: #8c2c2c;
        border-color: #f2c5c2;
    }

    .order-total {
        font-weight: 700;
        color: #2b1d18;
    }

    .order-date {
        font-size: 0.88rem;
        color: #7a685d;
    }

    .order-action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .order-actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border-radius: 999px;
        background: #16110f;
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .order-actions a:hover {
        transform: translateY(-1px);
        opacity: 0.95;
    }

    .order-action-secondary {
        background: #f4ede7 !important;
        color: #241914 !important;
        border: 1px solid #dfd1c4;
    }

    @media (max-width: 768px) {
        .account-page {
            padding: 36px 0 60px;
        }

        .account-card {
            padding: 22px;
            border-radius: 18px;
        }

        .account-header {
            gap: 6px;
        }

        .account-header h1 {
            font-size: 2.3rem;
        }

        .orders-table-wrapper {
            border-radius: 16px;
        }

        .orders-table,
        .orders-table thead,
        .orders-table tbody,
        .orders-table th,
        .orders-table td,
        .orders-table tr {
            display: block;
            width: 100%;
        }

        .orders-table thead {
            display: none;
        }

        .orders-table tr {
            border-bottom: 1px solid #f0e5dd;
            padding: 10px 12px;
        }

        .orders-table tr:last-child {
            border-bottom: none;
        }

        .orders-table td {
            border: none;
            padding: 6px 0;
        }

        .orders-table td::before {
            content: attr(data-label);
            display: block;
            font-weight: 700;
            color: #3a2a21;
            font-size: 0.82rem;
            margin-bottom: 2px;
        }

        .order-action-group {
            flex-direction: column;
        }

        .order-actions a {
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
                        <div>
                            <h1>My Orders</h1>
                            <p>Review your recent orders, payment status, and delivery progress in one place.</p>
                        </div>
                    </div>

                    <?php if (!$orders): ?>
                        <div class="orders-empty">
                            <strong>No orders yet.</strong> When you place an order, it will appear here with its latest status.
                        </div>
                    <?php else: ?>
                        <p class="orders-summary">
                            <strong><?= e((string) count($orders)) ?></strong> order<?= count($orders) === 1 ? '' : 's' ?> found.
                        </p>

                        <div class="orders-table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <?php
                                        $paymentStatus = strtolower((string) ($order['payment_status'] ?? ''));
                                        $orderStatus = strtolower((string) ($order['order_status'] ?? ''));
                                        $paymentClass = 'status-pill';
                                        $orderClass = 'status-pill';

                                        if (in_array($paymentStatus, ['paid'], true)) {
                                            $paymentClass .= ' status-pill--paid';
                                        } elseif (in_array($paymentStatus, ['pending'], true)) {
                                            $paymentClass .= ' status-pill--pending';
                                        } elseif (in_array($paymentStatus, ['failed', 'cancelled'], true)) {
                                            $paymentClass .= ' status-pill--failed';
                                        }

                                        if (in_array($orderStatus, ['delivered', 'completed'], true)) {
                                            $orderClass .= ' status-pill--delivered';
                                        } elseif (in_array($orderStatus, ['pending', 'processing'], true)) {
                                            $orderClass .= ' status-pill--pending';
                                        } elseif (in_array($orderStatus, ['cancelled'], true)) {
                                            $orderClass .= ' status-pill--cancelled';
                                        }

                                        $orderNumber = (string) ($order['order_number'] ?? '');
                                        ?>
                                        <tr>
                                            <td data-label="Order">
                                                <div><?= e($orderNumber) ?></div>
                                            </td>
                                            <td data-label="Total">
                                                <div class="order-total">
                                                    GHS <?= e(number_format((float) ($order['total_amount'] ?? 0), 2)) ?>
                                                </div>
                                            </td>
                                            <td data-label="Payment">
                                                <span class="<?= e($paymentClass) ?>">
                                                    <?= e(ucfirst($paymentStatus ?: '')) ?>
                                                </span>
                                            </td>
                                            <td data-label="Status">
                                                <span class="<?= e($orderClass) ?>">
                                                    <?= e(ucfirst($orderStatus ?: '')) ?>
                                                </span>
                                            </td>
                                            <td data-label="Date">
                                                <div class="order-date">
                                                    <?= e($order['created_at'] ?? '') ?>
                                                </div>
                                            </td>
                                            <td data-label="Actions" class="order-actions">
                                                <div class="order-action-group">
                                                    <a href="<?= e(url('order-status.php?order_number=' . urlencode($orderNumber))) ?>">Track</a>
                                                    <a href="<?= e(url('order-details.php?id=' . (int) ($order['id'] ?? 0))) ?>" class="order-action-secondary">View Details</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>