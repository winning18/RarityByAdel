<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/require_admin.php';
// remove the database.php line entirely

$pageTitle = 'Add Product';
$currentPage = 'products';

$db = Database::getInstance();

$errors = [];
$categories = [];

$form = [
    'name' => '',
    'slug' => '',
    'category_id' => '',
    'description' => '',
    'price' => '',
    'stock_quantity' => '',
    'is_featured' => '0',
    'is_active' => '1',
];

function make_slug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
    return trim((string) $value, '-');
}

function make_unique_slug(PDO $db, string $baseSlug): string
{
    $baseSlug = make_slug($baseSlug);

    if ($baseSlug === '') {
        $baseSlug = 'product';
    }

    $stmt = $db->prepare("SELECT slug FROM products WHERE slug = :slug LIMIT 1");
    $stmt->execute(['slug' => $baseSlug]);

    if (!$stmt->fetch()) {
        return $baseSlug;
    }

    $counter = 2;
    while (true) {
        $candidate = $baseSlug . '-' . $counter;
        $stmt = $db->prepare("SELECT slug FROM products WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $candidate]);

        if (!$stmt->fetch()) {
            return $candidate;
        }

        $counter++;
    }
}

try {
    $stmt = $db->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (Throwable $e) {
    $errors[] = 'Unable to load categories.';
}

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

    $imageName = null;

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
                    $imageName = time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
                    $targetPath = $uploadDir . $imageName;

                    if (!move_uploaded_file($tmpName, $targetPath)) {
                        $errors[] = 'Failed to save uploaded image.';
                        $imageName = null;
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $finalSlug = make_unique_slug($db, $form['slug']);

            $stmt = $db->prepare("
                INSERT INTO products (
                    category_id,
                    name,
                    slug,
                    description,
                    price,
                    stock_quantity,
                    image,
                    is_featured,
                    is_active
                ) VALUES (
                    :category_id,
                    :name,
                    :slug,
                    :description,
                    :price,
                    :stock_quantity,
                    :image,
                    :is_featured,
                    :is_active
                )
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
            ]);

            $_SESSION['success_message'] = 'Product created successfully.';
            header('Location: ' . url('admin/products.php'));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Unable to save product right now.';
        }
    }
}

require __DIR__ . '/../app/Views/partials/admin_header.php';
?>

<section class="admin-product-create-section">
    <div class="container">
        <div class="admin-product-create-shell">
            <div class="admin-product-create-header">
                <div>
                    <span class="admin-product-create-kicker">Admin Panel</span>
                    <h1>Add Product</h1>
                    <p>Create a new product and assign it to the correct category.</p>
                </div>
                <a href="<?= url('admin/products.php') ?>" class="admin-product-create-back">Back to Products</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="admin-product-create-alert admin-product-create-alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="admin-product-create-card">
                <form action="" method="POST" enctype="multipart/form-data" class="admin-product-create-form">
                    <div class="admin-product-create-grid">
                        <div class="admin-product-create-field admin-product-create-field-full">
                            <label for="name">Product Name</label>
                            <input type="text" id="name" name="name" value="<?= e($form['name']) ?>" placeholder="Enter product name" required>
                        </div>

                        <div class="admin-product-create-field">
                            <label for="slug">Slug</label>
                            <input type="text" id="slug" name="slug" value="<?= e($form['slug']) ?>" placeholder="auto-generated-if-empty">
                        </div>

                        <div class="admin-product-create-field">
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

                        <div class="admin-product-create-field">
                            <label for="price">Price</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" value="<?= e($form['price']) ?>" placeholder="0.00" required>
                        </div>

                        <div class="admin-product-create-field">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" min="0" step="1" value="<?= e($form['stock_quantity']) ?>" placeholder="0" required>
                        </div>

                        <div class="admin-product-create-field admin-product-create-field-full">
                            <label for="image">Product Image</label>
                            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <small>Accepted formats: JPG, JPEG, PNG, WEBP. Maximum size: 5MB.</small>
                        </div>

                        <div class="admin-product-create-field admin-product-create-field-full">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="6" placeholder="Write product description..."><?= e($form['description']) ?></textarea>
                        </div>

                        <div class="admin-product-create-switches admin-product-create-field-full">
                            <label class="admin-product-create-check">
                                <input type="checkbox" name="is_featured" value="1" <?= $form['is_featured'] === '1' ? 'checked' : '' ?>>
                                <span>Featured product</span>
                            </label>

                            <label class="admin-product-create-check">
                                <input type="checkbox" name="is_active" value="1" <?= $form['is_active'] === '1' ? 'checked' : '' ?>>
                                <span>Active / visible on storefront</span>
                            </label>
                        </div>
                    </div>

                    <div class="admin-product-create-actions">
                        <a href="<?= url('admin/products.php') ?>" class="admin-product-create-btn admin-product-create-btn-secondary">Cancel</a>
                        <button type="submit" class="admin-product-create-btn admin-product-create-btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
.admin-product-create-section {
    padding: 56px 0 80px;
    background: linear-gradient(180deg, #fffaf6 0%, #f7efe9 100%);
}

.admin-product-create-shell {
    display: grid;
    gap: 24px;
}

.admin-product-create-header {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 20px;
    flex-wrap: wrap;
}

.admin-product-create-kicker {
    display: inline-block;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--color-text-soft);
}

.admin-product-create-header h1 {
    margin: 0 0 10px;
    font-family: var(--font-heading);
    font-size: clamp(2rem, 4vw, 3.2rem);
    line-height: 0.98;
}

.admin-product-create-header p {
    margin: 0;
    color: var(--color-text-soft);
}

.admin-product-create-back {
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

.admin-product-create-alert {
    padding: 16px 18px;
    border-radius: 18px;
}

.admin-product-create-alert ul {
    margin: 0;
    padding-left: 18px;
}

.admin-product-create-alert-error {
    background: rgba(180, 35, 24, 0.08);
    border: 1px solid rgba(180, 35, 24, 0.18);
    color: #8f1f14;
}

.admin-product-create-card {
    background: rgba(255,255,255,0.96);
    border: 1px solid rgba(17,17,17,0.08);
    border-radius: 28px;
    box-shadow: var(--shadow-soft);
    padding: 28px;
}

.admin-product-create-form {
    display: grid;
    gap: 24px;
}

.admin-product-create-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.admin-product-create-field {
    display: grid;
    gap: 8px;
}

.admin-product-create-field-full {
    grid-column: 1 / -1;
}

.admin-product-create-field label {
    font-weight: 800;
    color: #111;
}

.admin-product-create-field input,
.admin-product-create-field select,
.admin-product-create-field textarea,
.admin-product-create-field small {
    width: 100%;
}

.admin-product-create-field input,
.admin-product-create-field select,
.admin-product-create-field textarea {
    min-height: 52px;
    padding: 14px 16px;
    border-radius: 16px;
    border: 1px solid rgba(17,17,17,0.12);
    background: #fff;
    color: #111;
    font: inherit;
}

.admin-product-create-field textarea {
    min-height: 140px;
    resize: vertical;
}

.admin-product-create-field small {
    color: var(--color-text-soft);
    font-size: 0.88rem;
}

.admin-product-create-switches {
    display: flex;
    gap: 18px;
    flex-wrap: wrap;
}

.admin-product-create-check {
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

.admin-product-create-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.admin-product-create-btn {
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

.admin-product-create-btn-primary {
    background: #111;
    color: #fff;
}

.admin-product-create-btn-secondary {
    background: rgba(17,17,17,0.06);
    color: #111;
}

@media (max-width: 768px) {
    .admin-product-create-section {
        padding: 42px 0 64px;
    }

    .admin-product-create-card {
        padding: 20px;
        border-radius: 22px;
    }

    .admin-product-create-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require __DIR__ . '/../app/Views/partials/footer.php'; ?>