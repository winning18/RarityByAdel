<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/require_admin.php';
// remove the database.php line entirely

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('admin/products.php'));
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$mode = isset($_POST['mode']) && $_POST['mode'] === 'hard' ? 'hard' : 'soft';

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid product.';
    header('Location: ' . url('admin/products.php'));
    exit;
}

$db = Database::getInstance();

try {
    $stmt = $db->prepare("
        SELECT id, image, is_active
        FROM products
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error_message'] = 'Product not found.';
        header('Location: ' . url('admin/products.php'));
        exit;
    }

    if ($mode === 'hard') {
        $db->beginTransaction();

        $deleteItems = $db->prepare("DELETE FROM order_items WHERE product_id = :id");
        $deleteItems->execute(['id' => $id]);

        $deleteProduct = $db->prepare("DELETE FROM products WHERE id = :id LIMIT 1");
        $deleteProduct->execute(['id' => $id]);

        $db->commit();

        if (!empty($product['image'])) {
            $uploadDir = __DIR__ . '/../uploads/products/';
            $path = $uploadDir . $product['image'];

            if (is_file($path)) {
                @unlink($path);
            }
        }

        $_SESSION['success_message'] = 'Product permanently deleted.';
    } else {
        $update = $db->prepare("
            UPDATE products
            SET is_active = 0, updated_at = NOW()
            WHERE id = :id
            LIMIT 1
        ");
        $update->execute(['id' => $id]);

        $_SESSION['success_message'] = 'Product hidden from storefront.';
    }
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    $_SESSION['error_message'] = 'Unable to delete product right now.';
}

header('Location: ' . url('admin/products.php'));
exit;