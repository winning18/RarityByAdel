<?php
require_once dirname(__DIR__, 3) . '/config/config.php';


$pageTitle = $pageTitle ?? APP_NAME;
$currentPage = $currentPage ?? '';

function navActive(string $page, string $currentPage): string
{
    return $page === $currentPage ? 'active' : '';
}

$sessionCart = $_SESSION['cart'] ?? [];
$cartCount = 0;

if (is_array($sessionCart)) {
    foreach ($sessionCart as $cartItem) {
        $cartCount += (int) ($cartItem['qty'] ?? 0);
    }
}

$currentUser = $_SESSION['user'] ?? null;
$isLoggedIn = !empty($currentUser);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= APP_NAME ?></title>
    <meta name="description" content="RarityByAdel is a premium Ghanaian fashion and jewelry brand.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') . '?v=' . time() ?>">
</head>
<body>
<a href="#main-content" class="skip-link">Skip to content</a>

<header class="site-header site-header-clean">
    <div class="topbar">
        <div class="container topbar-inner">
            <a href="tel:+233551812055" class="topbar-phone"><?= e(BUSINESS_PHONE) ?></a>
            <p class="topbar-text">Premium Ghanaian fashion and jewelry brand</p>
        </div>
    </div>

    <div class="navbar-wrap">
        <div class="container navbar">
            <a href="<?= url('index.php') ?>" class="brand-mark" aria-label="RarityByAdel home">
                <span class="brand-icon">RA</span>
                <span class="brand-text">RarityByAdel</span>
            </a>

            <nav class="site-nav" id="site-nav" aria-label="Primary navigation">
                <a class="<?= navActive('home', $currentPage) ?>" href="<?= url('index.php') ?>">Home</a>
                <a class="<?= navActive('clothings', $currentPage) ?>" href="<?= url('clothings.php') ?>">Clothing</a>
                <a class="<?= navActive('jewelry', $currentPage) ?>" href="<?= url('jewelry.php') ?>">Jewelry</a>
                <a class="<?= navActive('about', $currentPage) ?>" href="<?= url('about.php') ?>">About</a>
                <a class="<?= navActive('contact', $currentPage) ?>" href="<?= url('contact.php') ?>">Contact</a>
                <a class="<?= navActive('faq', $currentPage) ?>" href="<?= url('faq.php') ?>">FAQ</a>

                <div class="header-actions nav-actions-mobile">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= e(url('profile.php')) ?>" class="header-link-pill">My Profile</a>
                        <a href="<?= e(url('orders.php')) ?>" class="header-link-pill">My Orders</a>
                        <a href="<?= e(url('logout.php')) ?>" class="header-link-pill">Sign Out</a>
                    <?php else: ?>
                        <a href="<?= url('register.php') ?>" class="header-link-pill">Sign Up</a>
                        <a href="<?= url('login.php') ?>" class="header-link-pill header-link-pill-primary">Login</a>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="header-actions header-actions-desktop">
                <a href="<?= url('cart.php') ?>" class="icon-link cart-link" aria-label="Cart">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="9" cy="20" r="1.6"></circle>
                        <circle cx="18" cy="20" r="1.6"></circle>
                        <path d="M3 4h2l2.4 10.2a1 1 0 0 0 1 .8h8.8a1 1 0 0 0 1-.8L20 7H7"></path>
                    </svg>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-count" data-cart-count><?= e((string) $cartCount) ?></span>
                    <?php endif; ?>
                </a>

                <?php if ($isLoggedIn): ?>
                    <a href="<?= e(url('profile.php')) ?>" class="header-link-pill">My Profile</a>
                    <a href="<?= e(url('orders.php')) ?>" class="header-link-pill">My Orders</a>
                    <a href="<?= e(url('logout.php')) ?>" class="header-link-pill">Sign Out</a>
                <?php else: ?>
                    <a href="<?= url('register.php') ?>" class="header-link-pill">Sign Up</a>
                    <a href="<?= url('login.php') ?>" class="header-link-pill header-link-pill-primary">Login</a>
                <?php endif; ?>
            </div>

            <div class="header-mobile-tools">
                <a href="<?= url('cart.php') ?>" class="icon-link cart-link" aria-label="Cart">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="9" cy="20" r="1.6"></circle>
                        <circle cx="18" cy="20" r="1.6"></circle>
                        <path d="M3 4h2l2.4 10.2a1 1 0 0 0 1 .8h8.8a1 1 0 0 0 1-.8L20 7H7"></path>
                    </svg>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-count" data-cart-count><?= e((string) $cartCount) ?></span>
                    <?php endif; ?>
                </a>

                <button
                    class="nav-toggle"
                    type="button"
                    aria-label="Toggle navigation"
                    aria-expanded="false"
                    aria-controls="site-nav"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </div>
</header>