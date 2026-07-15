<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

// Ensure session is active for cart usage
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pageTitle = 'Clothings';
$currentPage = 'clothings';

$db = Database::getInstance();
$products = [];
$cartNotice = '';

// Load products
try {
    $stmt = $db->prepare("
        SELECT
            p.id,
            p.name,
            p.slug,
            p.price,
            p.image,
            p.stock_quantity,
            p.is_featured,
            p.created_at,
            c.name AS category_name
        FROM products p
        INNER JOIN categories c ON c.id = p.category_id
        WHERE p.is_active = 1
          AND (
                LOWER(c.name) = 'clothing'
                OR LOWER(c.name) = 'clothings'
              )
        ORDER BY p.created_at DESC, p.id DESC
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $stockQty = (int) ($row['stock_quantity'] ?? 0);

        if ($stockQty <= 0) {
            $stockLabel = 'Out of stock';
        } elseif ($stockQty <= 3) {
            $stockLabel = 'Low stock';
        } else {
            $stockLabel = 'In stock';
        }

        $tag = 'New';
        if ((int) ($row['is_featured'] ?? 0) === 1) {
            $tag = 'Featured';
        } elseif ($stockQty <= 3 && $stockQty > 0) {
            $tag = 'Popular';
        }

        // Default fallback image
        $imagePath = 'https://picsum.photos/seed/rarity-fallback-' . (int) $row['id'] . '/800/1000';

        if (!empty($row['image'])) {
            // Product images live under /uploads/products
            $diskImagePath = __DIR__ . '/uploads/products/' . $row['image'];

            if (is_file($diskImagePath)) {
                $imagePath = url('uploads/products/' . $row['image']);
            }
        }

        $products[] = [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'slug' => (string) ($row['slug'] ?? ''),
            'price' => (float) $row['price'],
            'image' => $imagePath,
            'stock' => $stockLabel,
            'stock_quantity' => $stockQty,
            'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            'tag' => $tag,
        ];
    }
} catch (Throwable $e) {
    $products = [];
}

// Handle add-to-cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_cart') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $selectedSize = trim((string) ($_POST['selected_size'] ?? ''));

    if ($productId > 0) {
        foreach ($products as $product) {
            if ((int) $product['id'] === $productId) {
                if (($product['stock_quantity'] ?? 0) <= 0) {
                    $cartNotice = $product['name'] . ' is currently out of stock.';
                    break;
                }

                if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }

                $allowedSizes = $product['sizes'] ?? [];
                if ($selectedSize === '' || !in_array($selectedSize, $allowedSizes, true)) {
                    $selectedSize = $allowedSizes[0] ?? 'M';
                }

                // Keyed by product + size, consistent with variant carts
                $cartKey = $productId . '_' . $selectedSize;

                if (isset($_SESSION['cart'][$cartKey])) {
                    $_SESSION['cart'][$cartKey]['qty'] = (int) ($_SESSION['cart'][$cartKey]['qty'] ?? 0) + 1;
                } else {
                    $_SESSION['cart'][$cartKey] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'qty' => 1,
                        'size' => $selectedSize,
                        'image' => $product['image'],
                        'stock' => $product['stock'],
                    ];
                }

                $cartNotice = $product['name'] . ' (' . $selectedSize . ') added to cart.';
                break;
            }
        }
    }
}

require __DIR__ . '/app/Views/partials/header.php';
?>

<section class="catalog-hero">
    <div class="container">
        <span class="section-tag">Clothings</span>
        <h1>Curated clothing with a premium point of view</h1>
        <p>Browse refined fashion pieces designed for bold elegance, modern femininity, and elevated everyday style.</p>
    </div>
</section>

<section class="catalog-section">
    <div class="container catalog-layout">
        <aside class="filters-panel">
            <div class="filters-header">
                <h2>Filters</h2>
                <button type="button" class="clear-filters">Clear all</button>
            </div>

            <div class="filter-group">
                <label for="catalog-search">Search</label>
                <input type="text" id="catalog-search" placeholder="Search by name..." />
            </div>

            <div class="filter-group">
                <span>Size</span>
                <div class="filter-options">
                    <button type="button">XS</button>
                    <button type="button">S</button>
                    <button type="button">M</button>
                    <button type="button">L</button>
                    <button type="button">XL</button>
                    <button type="button">XXL</button>
                </div>
            </div>

            <div class="filter-group">
                <span>Availability</span>
                <div class="filter-checks">
                    <label><input type="checkbox"> In stock</label>
                    <label><input type="checkbox"> Low stock</label>
                </div>
            </div>

            <div class="filter-group">
                <span>Price</span>
                <div class="filter-checks">
                    <label><input type="checkbox"> Under GHS 400</label>
                    <label><input type="checkbox"> GHS 400 - GHS 500</label>
                    <label><input type="checkbox"> Above GHS 500</label>
                </div>
            </div>
        </aside>

        <div class="catalog-content">
            <?php if ($cartNotice !== ''): ?>
                <div class="catalog-notice"><?= e($cartNotice) ?></div>
            <?php endif; ?>

            <div class="catalog-toolbar">
                <p>Showing <strong><?= count($products) ?></strong> products</p>
                <select>
                    <option>Sort: Featured</option>
                    <option>Price: Low to High</option>
                    <option>Price: High to Low</option>
                    <option>Newest</option>
                </select>
            </div>

            <?php if (empty($products)): ?>
                <div class="catalog-notice">No clothing products available right now.</div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <article class="product-card">
                            <a href="<?= url('product.php') . '?id=' . (int) $product['id'] ?>" class="product-image-wrap catalog-product-image-wrap">
                                <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" class="catalog-product-image">
                                <span class="product-badge"><?= e($product['tag']) ?></span>
                            </a>

                            <div class="product-info">
                                <div class="product-topline">
                                    <h3>
                                        <a href="<?= url('product.php') . '?id=' . (int) $product['id'] ?>">
                                            <?= e($product['name']) ?>
                                        </a>
                                    </h3>
                                    <span class="product-price">GHS <?= number_format((float) $product['price'], 2) ?></span>
                                </div>

                                <p class="product-stock"><?= e($product['stock']) ?></p>

                                <form method="POST" class="catalog-cart-form">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                    <input
                                        type="hidden"
                                        name="selected_size"
                                        value="M"
                                        class="selected-size-input"
                                    >

                                    <div class="product-sizes size-toggle-group">
                                        <?php foreach ($product['sizes'] as $size): ?>
                                            <button
                                                type="button"
                                                class="size-btn <?= $size === 'M' ? 'active' : '' ?>"
                                                data-size="<?= e($size) ?>"
                                                <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>
                                            >
                                                <?= e($size) ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="product-actions">
                                        <a href="<?= url('product.php') . '?id=' . (int) $product['id'] ?>" class="mini-btn">View</a>
                                        <button
                                            type="submit"
                                            class="mini-btn dark-btn"
                                            <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>
                                        >
                                            <?= $product['stock_quantity'] <= 0 ? 'Out of stock' : 'Add to cart' ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
/* your existing styles unchanged */
</style>

<script>
document.querySelectorAll('.catalog-cart-form').forEach(form => {
    const sizeButtons = form.querySelectorAll('.size-btn');
    const sizeInput = form.querySelector('.selected-size-input');

    sizeButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.disabled) return;

            sizeButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            if (sizeInput) {
                sizeInput.value = button.getAttribute('data-size') || 'M';
            }
        });
    });
});
</script>

<?php require __DIR__ . '/app/Views/partials/footer.php'; ?>