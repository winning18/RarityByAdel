<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

// Ensure session is active for cart usage
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pageTitle = 'Product Details';
$currentPage = 'clothings';

$db = Database::getInstance();
$notice = '';
$product = null;

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($productId <= 0) {
    header('Location: ' . url('clothings.php'));
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT
            p.id,
            p.name,
            p.slug,
            p.description,
            p.price,
            p.stock_quantity,
            p.image,
            p.is_active,
            p.created_at,
            c.name AS category_name
        FROM products p
        INNER JOIN categories c ON c.id = p.category_id
        WHERE p.id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $productId]);
    $row = $stmt->fetch();

    if (!$row || (int) $row['is_active'] !== 1) {
        $_SESSION['error_message'] = 'Product not found.';
        header('Location: ' . url('clothings.php'));
        exit;
    }

    $stockQty = (int) ($row['stock_quantity'] ?? 0);

    if ($stockQty <= 0) {
        $availability = 'Out of stock';
    } elseif ($stockQty <= 3) {
        $availability = 'Low stock';
    } else {
        $availability = 'In stock';
    }

    $imageUrl = 'https://picsum.photos/seed/product-' . (int) $row['id'] . '/900/1100';

    if (!empty($row['image'])) {
        // Products images live under /htdocs/uploads/products
        $diskImagePath = __DIR__ . '/uploads/products/' . $row['image'];

        if (is_file($diskImagePath)) {
            $imageUrl = url('uploads/products/' . $row['image']);
        }
    }

    $defaultSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    $product = [
        'id' => (int) $row['id'],
        'name' => (string) $row['name'],
        'price' => (float) $row['price'],
        'availability' => $availability,
        'stock_quantity' => $stockQty,
        'sku' => 'RBA-' . str_pad((string) $row['id'], 4, '0', STR_PAD_LEFT),
        'description' => (string) ($row['description'] ?: 'This product is available now in our premium clothing collection.'),
        'fit' => 'Tailored fit with a clean waistline and soft movement through the lower silhouette.',
        'material' => 'Premium fashion fabric',
        'sizes' => $defaultSizes,
        'images' => [$imageUrl, $imageUrl, $imageUrl, $imageUrl],
        'category_name' => (string) $row['category_name'],
    ];
} catch (Throwable $e) {
    $_SESSION['error_message'] = 'Unable to load product right now.';
    header('Location: ' . url('clothings.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product) {
    $action = trim((string) ($_POST['action'] ?? ''));
    $selectedSize = trim((string) ($_POST['selected_size'] ?? 'M'));
    $selectedQty = max(1, (int) ($_POST['qty'] ?? 1));

    if (!in_array($selectedSize, $product['sizes'], true)) {
        $selectedSize = 'M';
    }

    if (($product['stock_quantity'] ?? 0) <= 0) {
        $notice = $product['name'] . ' is currently out of stock.';
    } elseif ($action === 'add_to_cart' || $action === 'buy_now') {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $cartKey = $product['id'] . '_' . $selectedSize;
        $existingQty = isset($_SESSION['cart'][$cartKey]['qty']) ? (int) $_SESSION['cart'][$cartKey]['qty'] : 0;
        $finalQty = min($existingQty + $selectedQty, (int) $product['stock_quantity']);

        $_SESSION['cart'][$cartKey] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'qty' => $finalQty,
            'size' => $selectedSize,
            'image' => $product['images'][0],
            'stock' => $product['availability']
        ];

        if ($action === 'buy_now') {
            header('Location: ' . url('checkout.php'));
            exit;
        }

        $notice = $product['name'] . ' (' . $selectedSize . ') added to cart.';
    }
}

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="product-page-section">
    <div class="container">
        <div class="product-page-grid">
            <div class="product-gallery">
                <div class="product-main-image product-main-image-fixed">
                    <img id="mainProductImage" src="<?= e($product['images'][0]) ?>" alt="<?= e($product['name']) ?>">
                </div>

                <div class="product-thumbs">
                    <?php foreach ($product['images'] as $index => $image): ?>
                        <button type="button" class="thumb-btn <?= $index === 0 ? 'active' : '' ?>" data-image="<?= e($image) ?>">
                            <img src="<?= e($image) ?>" alt="<?= e($product['name']) ?> thumbnail <?= $index + 1 ?>">
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="product-details">
                <span class="section-tag"><?= e($product['category_name']) ?></span>
                <h1><?= e($product['name']) ?></h1>

                <div class="product-price-row">
                    <span class="single-price">GHS <?= number_format((float) $product['price'], 2) ?></span>
                    <span class="single-stock"><?= e($product['availability']) ?></span>
                </div>

                <?php if ($notice !== ''): ?>
                    <div class="product-notice"><?= e($notice) ?></div>
                <?php endif; ?>

                <p class="product-description"><?= e($product['description']) ?></p>

                <div class="product-meta">
                    <div><strong>SKU:</strong> <?= e($product['sku']) ?></div>
                    <div><strong>Material:</strong> <?= e($product['material']) ?></div>
                    <div><strong>Fit:</strong> <?= e($product['fit']) ?></div>
                </div>

                <form method="POST" id="productPurchaseForm">
                    <input type="hidden" name="action" id="productActionInput" value="add_to_cart">
                    <input type="hidden" name="selected_size" id="selectedSizeInput" value="M">
                    <input type="hidden" name="qty" id="selectedQtyInput" value="1">

                    <div class="selection-block">
                        <div class="selection-header">
                            <span>Select size</span>
                            <button type="button" class="size-guide-toggle">Size Chart</button>
                        </div>

                        <div class="size-options">
                            <?php foreach ($product['sizes'] as $size): ?>
                                <button
                                    type="button"
                                    class="size-btn <?= $size === 'M' ? 'active' : '' ?>"
                                    data-size="<?= e($size) ?>"
                                    <?= ($product['stock_quantity'] ?? 0) <= 0 ? 'disabled' : '' ?>
                                >
                                    <?= e($size) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="selection-block">
                        <span>Quantity</span>
                        <div class="quantity-selector">
                            <button type="button" class="qty-btn" data-action="minus" <?= ($product['stock_quantity'] ?? 0) <= 0 ? 'disabled' : '' ?>>-</button>
                            <input type="text" id="quantityInput" value="1" readonly>
                            <button type="button" class="qty-btn" data-action="plus" <?= ($product['stock_quantity'] ?? 0) <= 0 ? 'disabled' : '' ?>>+</button>
                        </div>
                    </div>

                    <div class="product-cta-row">
                        <button
                            type="submit"
                            class="btn btn-dark add-cart-btn"
                            data-submit-action="add_to_cart"
                            <?= ($product['stock_quantity'] ?? 0) <= 0 ? 'disabled' : '' ?>
                        >
                            <?= ($product['stock_quantity'] ?? 0) <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
                        </button>

                        <button
                            type="submit"
                            class="btn btn-outline"
                            data-submit-action="buy_now"
                            <?= ($product['stock_quantity'] ?? 0) <= 0 ? 'disabled' : '' ?>
                        >
                            Buy Now
                        </button>
                    </div>
                </form>

                <div class="product-notes">
                    <p>International sizing support is available to help customers choose the most suitable fit before purchase.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="size-chart-modal" id="sizeChartModal">
    <div class="size-chart-backdrop"></div>
    <div class="size-chart-dialog">
        <button type="button" class="size-chart-close" aria-label="Close size chart">&times;</button>
        <span class="section-tag">International Size Chart</span>
        <h2>Clothing size guide</h2>

        <div class="size-table-wrap">
            <table class="size-table">
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>UK</th>
                        <th>US</th>
                        <th>EU</th>
                        <th>Bust (cm)</th>
                        <th>Waist (cm)</th>
                        <th>Hips (cm)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>XS</td><td>6</td><td>2</td><td>34</td><td>80</td><td>62</td><td>86</td></tr>
                    <tr><td>S</td><td>8</td><td>4</td><td>36</td><td>84</td><td>66</td><td>90</td></tr>
                    <tr><td>M</td><td>10</td><td>6</td><td>38</td><td>88</td><td>70</td><td>94</td></tr>
                    <tr><td>L</td><td>12</td><td>8</td><td>40</td><td>94</td><td>76</td><td>100</td></tr>
                    <tr><td>XL</td><td>14</td><td>10</td><td>42</td><td>100</td><td>82</td><td>106</td></tr>
                    <tr><td>XXL</td><td>16</td><td>12</td><td>44</td><td>106</td><td>88</td><td>112</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.product-main-image-fixed {
    aspect-ratio: 9 / 11;
    overflow: hidden;
    border-radius: 24px;
    background: #f4efe8;
}

.product-main-image-fixed img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.product-thumbs .thumb-btn img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.product-notice {
    margin: 16px 0 18px;
    padding: 14px 16px;
    border-radius: 16px;
    background: rgba(17, 17, 17, 0.05);
    border: 1px solid rgba(17, 17, 17, 0.08);
    color: var(--color-text);
    font-weight: 700;
}
</style>

<script>
(function () {
    const mainProductImage = document.getElementById('mainProductImage');
    const thumbButtons = document.querySelectorAll('.thumb-btn');
    const sizeButtons = document.querySelectorAll('.size-btn');
    const qtyButtons = document.querySelectorAll('.qty-btn');
    const quantityInput = document.getElementById('quantityInput');
    const selectedQtyInput = document.getElementById('selectedQtyInput');
    const selectedSizeInput = document.getElementById('selectedSizeInput');
    const productActionInput = document.getElementById('productActionInput');
    const actionButtons = document.querySelectorAll('[data-submit-action]');
    const sizeGuideToggle = document.querySelector('.size-guide-toggle');
    const sizeChartModal = document.getElementById('sizeChartModal');
    const sizeChartClose = document.querySelector('.size-chart-close');
    const sizeChartBackdrop = document.querySelector('.size-chart-backdrop');
    const maxQty = <?= (int) ($product['stock_quantity'] ?? 0) ?>;

    thumbButtons.forEach(button => {
        button.addEventListener('click', () => {
            thumbButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            if (mainProductImage) {
                mainProductImage.src = button.getAttribute('data-image') || '';
            }
        });
    });

    sizeButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.disabled) return;

            sizeButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            if (selectedSizeInput) {
                selectedSizeInput.value = button.getAttribute('data-size') || 'M';
            }
        });
    });

    qtyButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (!quantityInput || maxQty <= 0) return;

            let qty = Number(quantityInput.value) || 1;
            const action = button.getAttribute('data-action');

            if (action === 'plus') qty += 1;
            if (action === 'minus') qty = Math.max(1, qty - 1);

            qty = Math.min(qty, maxQty || 1);

            quantityInput.value = qty;
            if (selectedQtyInput) {
                selectedQtyInput.value = qty;
            }
        });
    });

    actionButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (productActionInput) {
                productActionInput.value = button.getAttribute('data-submit-action') || 'add_to_cart';
            }
        });
    });

    if (sizeGuideToggle && sizeChartModal) {
        sizeGuideToggle.addEventListener('click', () => {
            sizeChartModal.classList.add('open');
        });
    }

    if (sizeChartClose && sizeChartModal) {
        sizeChartClose.addEventListener('click', () => {
            sizeChartModal.classList.remove('open');
        });
    }

    if (sizeChartBackdrop && sizeChartModal) {
        sizeChartBackdrop.addEventListener('click', () => {
            sizeChartModal.classList.remove('open');
        });
    }
})();
</script>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>