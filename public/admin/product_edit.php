<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/require_admin.php';
// remove the database.php line entirely

$pageTitle = 'Edit Product';
$currentPage = 'products';

$db = Database::getInstance();
$errors = [];
$categories = [];
$product = null;

function make_slug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
    return trim((string) $value, '-');
}

function make_unique_slug_for_update(PDO $db, string $baseSlug, int $productId): string
{
    $baseSlug = make_slug($baseSlug);

    if ($baseSlug === '') {
        $baseSlug = 'product';
    }

    $stmt = $db->prepare("SELECT id FROM products WHERE slug = :slug AND id != :id LIMIT 1");
    $stmt->execute([
        'slug' => $baseSlug,
        'id' => $productId,
    ]);

    if (!$stmt->fetch()) {
        return $baseSlug;
    }

    $counter = 2;
    while (true) {
        $candidate = $baseSlug . '-' . $counter;

        $stmt = $db->prepare("SELECT id FROM products WHERE slug = :slug AND id != :id LIMIT 1");
        $stmt->execute([
            'slug' => $candidate,
            'id' => $productId,
        ]);

        if (!$stmt->fetch()) {
            return $candidate;
        }

        $counter++;
    }
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . url('admin/products.php'));
    exit;
}

try {
    $stmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error_message'] = 'Product not found.';
        header('Location: ' . url('admin/products.php'));
        exit;
    }

    $stmt = $db->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (Throwable $e) {
    $_SESSION['error_message'] = 'Unable to load product.';
    header('Location: ' . url('admin/products.php'));
    exit;
}

$form = [
    'name' => (string) $product['name'],
    'slug' => (string) $product['slug'],
    'category_id' => (string) $product['category_id'],
    'description' => (string) ($product['description'] ?? ''),
    'price' => (string) $product['price'],
    'stock_quantity' => (string) $product['stock_quantity'],
    'is_featured' => (string) $product['is_featured'],
    'is_active' => (string) $product['is_active'],
];

$currentImage = (string) ($product['image'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['name'] = trim((string) ($_POST['name'] ?? ''));
    $form['slug'] = trim((string) ($_POST['slug'] ?? ''));
    $form['category_id'] = trim((string) ($_POST['category_id'] ?? ''));
    $form['description'] = trim((string) ($_POST['description'] ?? ''));
    $form['price'] = trim((string) ($_POST['price'] ?? ''));
    $form['stock_quantity'] = trim((string) ($_POST['stock_quantity'] ?? ''));
    $form['is_featured'] = isset($_POST['is_featured']) ? '1' : '0';
    $form['is_active'] = isset($_POST['is_active']) ? '1' : '0';

    if ($form['name'] === '') {
        $errors[] = 'Product name is required.';
    }

    if ($form['slug'] === '') {
        $form['slug'] = make_slug($form['name']);
    } else {
        $form['slug'] = make_slug($form['slug']);
    }

    if ($form['slug'] === '') {
        $errors[] = 'Product slug is required.';
    }

    if ($form['category_id'] === '' || !ctype_digit($form['category_id'])) {
        $errors[] = 'Please select a valid category.';
    } else {
        $checkCategory = $db->prepare("SELECT id FROM categories WHERE id = :id LIMIT 1");
        $checkCategory->execute(['id' => (int) $form['category_id']]);

        if (!$checkCategory->fetch()) {
            $errors[] = 'Selected category does not exist.';
        }
    }

    if ($form['price'] === '' || !is_numeric($form['price']) || (float) $form['price'] < 0) {
        $errors[] = 'Please enter a valid price.';
    }

    if ($form['stock_quantity'] === '' || !ctype_digit($form['stock_quantity'])) {
        $errors[] = 'Please enter a valid stock quantity.';
    }

    $imageName = $currentImage;
    $oldImageToDelete = null;

    if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed.';
        } else {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxFileSize = 5 * 1024 * 1024;

            $originalName = (string) ($_FILES['image']['name'] ?? '');
            $tmpName = (string) ($_FILES['image']['tmp_name'] ?? '');
            $fileSize = (int) ($_FILES['image']['size'] ?? 0);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions, true)) {
                $errors[] = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
            }

            if ($fileSize <= 0 || $fileSize > $maxFileSize) {
                $errors[] = 'Image must be less than 5MB.';
            }

            if (!is_uploaded_file($tmpName)) {
                $errors[] = 'Invalid image upload.';
            }

            $imageInfo = @getimagesize($tmpName);
            if ($imageInfo === false) {
                $errors[] = 'Uploaded file must be a valid image.';
            } else {
                $mimeType = (string) ($imageInfo['mime'] ?? '');
                if (!in_array($mimeType, $allowedMimeTypes, true)) {
                    $errors[] = 'Invalid image type uploaded.';
                }
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../uploads/products/';

                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                    $errors[] = 'Failed to create product upload directory.';
                } else {
                    $newName = time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
                    $targetPath = $uploadDir . $newName;

                    if (!move_uploaded_file($tmpName, $targetPath)) {
                        $errors[] = 'Failed to save uploaded image.';
                    } else {
                        $imageName = $newName;

                        if ($currentImage !== '' && is_file($uploadDir . $currentImage)) {
                            $oldImageToDelete = $uploadDir . $currentImage;
                        }
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $finalSlug = make_unique_slug_for_update($db, $form['slug'], $id);

            $stmt = $db->prepare("
                UPDATE products
                SET
                    category_id = :category_id,
                    name = :name,
                    slug = :slug,
                    description = :description,
                    price = :price,
                    stock_quantity = :stock_quantity,
                    image = :image,
                    is_featured = :is_featured,
                    is_active = :is_active,
                    updated_at = NOW()
                WHERE id = :id
                LIMIT 1
            ");

            $stmt->execute([
                'category_id' => (int) $form['category_id'],
                'name' => $form['name'],
                'slug' => $finalSlug,
                'description' => $form['description'] !== '' ? $form['description'] : null,
                'price' => number_format((float) $form['price'], 2, '.', ''),
                'stock_quantity' => (int) $form['stock_quantity'],
                'image' => $imageName,
                'is_featured' => (int) $form['is_featured'],
                'is_active' => (int) $form['is_active'],
                'id' => $id,
            ]);

            if ($oldImageToDelete && is_file($oldImageToDelete)) {
                @unlink($oldImageToDelete);
            }

            $_SESSION['success_message'] = 'Product updated successfully.';
            header('Location: ' . url('admin/products.php'));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Unable to update product right now.';
        }
    }
}

require __DIR__ . '/../app/Views/partials/admin_header.php';
?>

<section class="admin-product-edit-section">
    <div class="container">
        <div class="admin-product-edit-shell">
            <div class="admin-product-edit-header">
                <div>
                    <span class="admin-product-edit-kicker">Admin Panel</span>
                    <h1>Edit Product</h1>
                    <p>Update product details, pricing, stock, and visibility.</p>
                </div>
                <a href="<?= url('admin/products.php') ?>" class="admin-product-edit-back">Back to Products</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="admin-product-edit-alert admin-product-edit-alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="admin-product-edit-card">
                <form action="" method="POST" enctype="multipart/form-data" class="admin-product-edit-form">
                    <div class="admin-product-edit-grid">
                        <div class="admin-product-edit-field admin-product-edit-field-full">
                            <label for="name">Product Name</label>
                            <input type="text" id="name" name="name" value="<?= e($form['name']) ?>" required>
                        </div>

                        <div class="admin-product-edit-field">
                            <label for="slug">Slug</label>
                            <input type="text" id="slug" name="slug" value="<?= e($form['slug']) ?>" required>
                        </div>

                        <div class="admin-product-edit-field">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= e((string) $category['id']) ?>" <?= $form['category_id'] === (string) $category['id'] ? 'selected' : '' ?>>
                                        <?= e((string) $category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="admin-product-edit-field">
                            <label for="price">Price</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" value="<?= e($form['price']) ?>" required>
                        </div>

                        <div class="admin-product-edit-field">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" min="0" step="1" value="<?= e($form['stock_quantity']) ?>" required>
                        </div>

                        <div class="admin-product-edit-field admin-product-edit-field-full">
                            <label for="image">Product Image</label>
                            <?php if ($currentImage !== ''): ?>
                                <div class="admin-product-edit-image-preview">
                                    <img src="<?= e(asset('uploads/products/' . $currentImage)) ?>" alt="<?= e($form['name']) ?>">
                                    <span>Current image</span>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <small>Accepted formats: JPG, JPEG, PNG, WEBP. Maximum size: 5MB.</small>
                        </div>

                        <div class="admin-product-edit-field admin-product-edit-field-full">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="6"><?= e($form['description']) ?></textarea>
                        </div>

                        <div class="admin-product-edit-switches admin-product-edit-field-full">
                            <label class="admin-product-edit-check">
                                <input type="checkbox" name="is_featured" value="1" <?= $form['is_featured'] === '1' ? 'checked' : '' ?>>
                                <span>Featured product</span>
                            </label>

                            <label class="admin-product-edit-check">
                                <input type="checkbox" name="is_active" value="1" <?= $form['is_active'] === '1' ? 'checked' : '' ?>>
                                <span>Active / visible on storefront</span>
                            </label>
                        </div>
                    </div>

                    <div class="admin-product-edit-actions">
                        <a href="<?= url('admin/products.php') ?>" class="admin-product-edit-btn admin-product-edit-btn-secondary">Cancel</a>
                        <button type="submit" class="admin-product-edit-btn admin-product-edit-btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.admin-product-edit-section {
    padding: 56px 0 80px;
    background: linear-gradient(180deg, #fffaf6 0%, #f7efe9 100%);
}

.admin-product-edit-shell {
    display: grid;
    gap: 24px;
}

.admin-product-edit-header {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 20px;
    flex-wrap: wrap;
}

.admin-product-edit-kicker {
    display: inline-block;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--color-text-soft);
}

.admin-product-edit-header h1 {
    margin: 0 0 10px;
    font-family: var(--font-heading);
    font-size: clamp(2rem, 4vw, 3.2rem);
    line-height: 0.98;
}

.admin-product-edit-header p {
    margin: 0;
    color: var(--color-text-soft);
}

.admin-product-edit-back {
    min-height: 46px;
    padding: 0 18px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-weight: 800;
    background: rgba(17,17,17,0.06);
    color: #111;
}

.admin-product-edit-alert {
    padding: 16px 18px;
    border-radius: 18px;
}

.admin-product-edit-alert ul {
    margin: 0;
    padding-left: 18px;
}

.admin-product-edit-alert-error {
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.18);
    color: #8f1f14;
}

.admin-product-edit-card {
    background: rgba(255,255,255,0.96);
    border: 1px solid rgba(17,17,17,0.08);
    border-radius: 28px;
    box-shadow: var(--shadow-soft);
    padding: 28px;
}

.admin-product-edit-form {
    display: grid;
    gap: 24px;
}

.admin-product-edit-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.admin-product-edit-field {
    display: grid;
    gap: 8px;
}

.admin-product-edit-field-full {
    grid-column: 1 / -1;
}

.admin-product-edit-field label {
    font-weight: 800;
    color: #111;
}

.admin-product-edit-field input,
.admin-product-edit-field select,
.admin-product-edit-field textarea,
.admin-product-edit-field small {
    width: 100%;
}

.admin-product-edit-field input,
.admin-product-edit-field select,
.admin-product-edit-field textarea {
    min-height: 52px;
    padding: 14px 16px;
    border-radius: 16px;
    border: 1px solid rgba(17,17,17,0.12);
    background: #fff;
    color: #111;
    font: inherit;
}

.admin-product-edit-field textarea {
    min-height: 140px;
    resize: vertical;
}

.admin-product-edit-field small {
    color: var(--color-text-soft);
    font-size: 0.88rem;
}

.admin-product-edit-image-preview {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.admin-product-edit-image-preview img {
    width: 72px;
    height: 72px;
    border-radius: 18px;
    object-fit: cover;
    display: block;
}

.admin-product-edit-image-preview span {
    font-size: 0.86rem;
    color: var(--color-text-soft);
}

.admin-product-edit-switches {
    display: flex;
    gap: 18px;
    flex-wrap: wrap;
}

.admin-product-edit-check {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-height: 48px;
    padding: 0 14px;
    border-radius: 999px;
    background: rgba(17,17,17,0.05);
    font-weight: 700;
    color: #111;
}

.admin-product-edit-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.admin-product-edit-btn {
    min-height: 48px;
    padding: 0 18px;
    border: none;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    font-weight: 800;
    cursor: pointer;
}

.admin-product-edit-btn-primary {
    background: #111;
    color: #fff;
}

.admin-product-edit-btn-secondary {
    background: rgba(17,17,17,0.06);
    color: #111;
}

@media (max-width: 768px) {
    .admin-product-edit-section {
        padding: 42px 0 64px;
    }

    .admin-product-edit-card {
        padding: 20px;
        border-radius: 22px;
    }

    .admin-product-edit-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require __DIR__ . '/../app/Views/partials/footer.php'; ?>