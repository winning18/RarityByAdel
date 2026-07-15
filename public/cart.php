<?php
require __DIR__ . '/config/config.php';

$pageTitle = 'Shopping Cart';
$currentPage = '';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $itemId = (int) ($_POST['item_id'] ?? 0);

    if ($action === 'update_qty' && $itemId > 0) {
        $newQty = max(1, (int) ($_POST['qty'] ?? 1));

        foreach ($_SESSION['cart'] as $key => $cartItem) {
            if ((int) ($cartItem['id'] ?? 0) === $itemId) {
                $_SESSION['cart'][$key]['qty'] = $newQty;
                break;
            }
        }

        header('Location: ' . url('cart.php'));
        exit;
    }

    if ($action === 'remove_item' && $itemId > 0) {
        foreach ($_SESSION['cart'] as $key => $cartItem) {
            if ((int) ($cartItem['id'] ?? 0) === $itemId) {
                unset($_SESSION['cart'][$key]);
                break;
            }
        }

        $_SESSION['cart'] = array_values($_SESSION['cart']);
        header('Location: ' . url('cart.php'));
        exit;
    }
}

$cartItems = array_values($_SESSION['cart']);

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 0));
}

$shipping = !empty($cartItems) ? 35 : 0;
$total = $subtotal + $shipping;

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="cart-page-section">
    <div class="container">
        <div class="cart-heading">
            <span class="section-tag">Shopping Cart</span>
            <h1>Review your selected pieces</h1>
            <p>Confirm your items, sizes, and quantities before proceeding to secure checkout.</p>
        </div>

        <div class="cart-layout" id="cartLayout">
            <div class="cart-items-panel">
                <div id="cartItemsWrap">
                    <?php foreach ($cartItems as $item): ?>
                        <article
                            class="cart-item-card"
                            data-cart-item
                            data-item-id="<?= (int) ($item['id'] ?? 0) ?>"
                            data-price="<?= e((string) ($item['price'] ?? 0)) ?>"
                        >
                            <a href="<?= url('product.php') . '?id=' . (int) ($item['id'] ?? 0) ?>" class="cart-item-image">
                                <img src="<?= e($item['image'] ?? '') ?>" alt="<?= e($item['name'] ?? '') ?>">
                            </a>

                            <div class="cart-item-info">
                                <div class="cart-item-top">
                                    <div>
                                        <h3><a href="<?= url('product.php') . '?id=' . (int) ($item['id'] ?? 0) ?>"><?= e($item['name'] ?? '') ?></a></h3>
                                        <p class="cart-item-meta">Size: <?= e($item['size'] ?? 'M') ?> · <?= e($item['stock'] ?? 'In stock') ?></p>
                                    </div>
                                    <strong class="item-top-price">GHS <?= number_format(((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 0)), 2) ?></strong>
                                </div>

                                <div class="cart-item-bottom">
                                    <form method="POST" class="cart-qty-form quantity-selector cart-qty-selector">
                                        <input type="hidden" name="action" value="update_qty">
                                        <input type="hidden" name="item_id" value="<?= (int) ($item['id'] ?? 0) ?>">

                                        <button type="button" class="qty-btn" data-action="minus" aria-label="Decrease quantity">-</button>
                                        <input
                                            type="text"
                                            name="qty"
                                            class="cart-qty-input"
                                            value="<?= e((string) ($item['qty'] ?? 1)) ?>"
                                            readonly
                                            aria-label="Product quantity"
                                        >
                                        <button type="button" class="qty-btn" data-action="plus" aria-label="Increase quantity">+</button>
                                    </form>

                                    <div class="cart-item-actions">
                                        <form method="POST" class="cart-remove-form">
                                            <input type="hidden" name="action" value="remove_item">
                                            <input type="hidden" name="item_id" value="<?= (int) ($item['id'] ?? 0) ?>">
                                            <button type="submit" class="remove-item-btn">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="cart-empty-state" id="cartEmptyState" style="<?= empty($cartItems) ? '' : 'display: none;' ?>">
                    <h3>Your cart is currently empty</h3>
                    <p>Add your favorite fashion pieces to continue shopping.</p>
                    <a href="<?= url('clothings.php') ?>" class="btn btn-dark">Shop Clothings</a>
                </div>

                <div class="cart-links-row" id="cartLinksRow" style="<?= empty($cartItems) ? 'display: none;' : '' ?>">
                    <a href="<?= url('clothings.php') ?>" class="btn btn-outline">Continue Shopping</a>
                </div>
            </div>

            <aside class="cart-summary-card" id="cartSummaryCard">
                <span class="section-tag">Order Summary</span>
                <h2>Cart total</h2>

                <div class="summary-line">
                    <span>Subtotal</span>
                    <strong id="cartSubtotal">GHS <?= number_format($subtotal, 2) ?></strong>
                </div>

                <div class="summary-line">
                    <span>Shipping</span>
                    <strong id="cartShipping">GHS <?= number_format($shipping, 2) ?></strong>
                </div>

                <div class="summary-line total-line">
                    <span>Total</span>
                    <strong id="cartTotal">GHS <?= number_format($total, 2) ?></strong>
                </div>

                <a href="<?= url('checkout.php') ?>" class="btn btn-dark cart-checkout-btn" id="cartCheckoutBtn" style="<?= empty($cartItems) ? 'pointer-events: none; opacity: .6;' : '' ?>">Proceed to Checkout</a>

                <div class="cart-note">
                    <p>Payment methods available: MoMo, Card, and Bank transfer.</p>
                </div>
            </aside>
        </div>
    </div>
</section>

<style>
.cart-item-actions {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.cart-qty-form,
.cart-remove-form {
    margin: 0;
}

.cart-empty-state {
    padding: 42px 28px;
    border-radius: 24px;
    border: 1px solid rgba(17,17,17,0.08);
    background: rgba(255,255,255,0.88);
    box-shadow: var(--shadow-soft);
    text-align: center;
}

.cart-empty-state h3 {
    margin: 0 0 10px;
    font-family: var(--font-heading);
    font-size: 1.8rem;
}

.cart-empty-state p {
    margin: 0 0 20px;
    color: var(--color-text-soft);
}

.remove-item-btn {
    border: 0;
    background: transparent;
    color: #b42318;
    font-weight: 800;
    cursor: pointer;
}

.remove-item-btn:hover {
    opacity: 0.8;
}
    
    
    
    .cart-checkout-btn {
    width: 100%;
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 54px;
    padding: 0 20px;

    border-radius: 999px;
    border: 1px solid #111111;
    background: #111111;
    color: #ffffff;

    font-weight: 800;
    text-decoration: none; /* in case it's an <a> */
}

/* Optional hover/focus state */
.cart-checkout-btn:hover,
.cart-checkout-btn:focus-visible {
    background: #2a211d;
    border-color: #2a211d;
}
    
</style>

<script>
(function () {
    const cartItemsWrap = document.getElementById('cartItemsWrap');

    if (!cartItemsWrap) return;

    cartItemsWrap.addEventListener('click', function (event) {
        const qtyBtn = event.target.closest('.qty-btn');
        if (!qtyBtn) return;

        const form = qtyBtn.closest('.cart-qty-form');
        if (!form) return;

        const input = form.querySelector('.cart-qty-input');
        if (!input) return;

        let qty = Number(input.value) || 1;
        const action = qtyBtn.getAttribute('data-action');

        if (action === 'plus') qty += 1;
        if (action === 'minus') qty = Math.max(1, qty - 1);

        input.value = qty;
        form.submit();
    });
})();
</script>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>