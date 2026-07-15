<?php
require_once dirname(__DIR__, 3) . '/config/config.php';

$pageTitle = $pageTitle ?? 'Admin';
$currentPage = $currentPage ?? '';

function adminNavActive(string $page, string $currentPage): string
{
    return $page === $currentPage ? 'active' : '';
}

$currentUser = $_SESSION['user'] ?? null;
$adminName = (string) ($currentUser['name'] ?? $currentUser['full_name'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= APP_NAME ?></title>
    <meta name="description" content="RarityByAdel admin dashboard.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') . '?v=' . time() ?>">
    <style>
        .admin-header {
            background: #111;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .admin-header .container {
            min-height: 82px;
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 20px;
            padding-top: 14px;
            padding-bottom: 14px;
        }

        .admin-brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }

        .admin-brand-badge {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: rgba(255,255,255,0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            letter-spacing: 0.06em;
        }

        .admin-brand-copy strong {
            display: block;
            font-size: 1rem;
            line-height: 1.1;
        }

        .admin-brand-copy span {
            display: block;
            font-size: 0.76rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.66);
        }

        .admin-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .admin-nav a {
            min-height: 42px;
            padding: 0 14px;
            border-radius: 999px;
            color: rgba(255,255,255,0.82);
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .admin-nav a.active,
        .admin-nav a:hover {
            color: #fff;
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.1);
        }

        .admin-meta {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .admin-chip {
            min-height: 42px;
            padding: 0 14px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            background: rgba(255,255,255,0.08);
            color: #fff;
            font-weight: 700;
            white-space: nowrap;
        }

        .admin-meta-link {
            min-height: 42px;
            padding: 0 14px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-weight: 700;
            white-space: nowrap;
        }

        .admin-meta-link.store-link {
            color: rgba(255,255,255,0.88);
            border: 1px solid rgba(255,255,255,0.12);
        }

        .admin-meta-link.logout-link {
            background: #fff;
            color: #111;
        }

        @media (max-width: 1100px) {
            .admin-header .container {
                grid-template-columns: 1fr;
                align-items: start;
            }

            .admin-nav,
            .admin-meta {
                justify-content: flex-start;
            }
        }

        @media (max-width: 640px) {
            .admin-nav {
                gap: 8px;
            }

            .admin-nav a,
            .admin-chip,
            .admin-meta-link {
                min-height: 40px;
                padding: 0 12px;
                font-size: 0.92rem;
            }
        }
    </style>
</head>
<body class="admin-layout">
<a href="#main-content" class="skip-link">Skip to content</a>

<header class="admin-header">
    <div class="container">
        <a href="<?= url('admin/dashboard.php') ?>" class="admin-brand" aria-label="Admin dashboard home">
            <span class="admin-brand-badge">RA</span>
            <span class="admin-brand-copy">
                <strong>RarityByAdel</strong>
                <span>Admin Panel</span>
            </span>
        </a>

        <nav class="admin-nav" aria-label="Admin navigation">
            <a class="<?= adminNavActive('dashboard', $currentPage) ?>" href="<?= url('admin/dashboard.php') ?>">Dashboard</a>
            <a class="<?= adminNavActive('products', $currentPage) ?>" href="<?= url('admin/products.php') ?>">Products</a>
            <a class="<?= adminNavActive('orders', $currentPage) ?>" href="<?= url('admin/orders.php') ?>">Orders</a>
            <a class="<?= adminNavActive('users', $currentPage) ?>" href="<?= url('admin/users.php') ?>">Users</a>
        </nav>

        <div class="admin-meta">
            <span class="admin-chip"><?= e($adminName) ?></span>
            <a href="<?= url('index.php') ?>" class="admin-meta-link store-link">Storefront</a>
            <a href="<?= url('admin/logout.php') ?>" class="admin-meta-link logout-link">Logout</a>
        </div>
    </div>
</header>

<main id="main-content">