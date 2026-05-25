<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once 'auth.php';

ensureStoreSchema($pdo);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('import_status', 'Invalid product id.');
    header('Location: index.php');
    exit;
}

// Fetch existing product
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    setFlash('import_status', 'Product not found.');
    header('Location: index.php');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $message = 'Invalid form submission. Please try again.';
    }

    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = max(0, (int)($_POST['stock'] ?? 0));
    $category = sanitize($_POST['category'] ?? '');
    $image = sanitize($_POST['image'] ?? $product['image']);

    // Handle uploaded image
    if (!$message && !empty($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image_file']['tmp_name'];
        $orig = basename($_FILES['image_file']['name']);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed, true)) {
            $newName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig);
            $dest = __DIR__ . '/../assets/img/' . $newName;
            if (move_uploaded_file($tmp, $dest)) {
                $image = 'assets/img/' . $newName;
                // remove old file if local
                if (!empty($product['image']) && strpos($product['image'], 'assets/img/') === 0) {
                    $oldPath = __DIR__ . '/../' . $product['image'];
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
            }
        }
    }

    if (!$message && $name && $description && $price > 0 && $category && $image) {
        $u = $pdo->prepare('UPDATE products SET name = ?, description = ?, price = ?, image = ?, category = ?, stock = ? WHERE id = ?');
        $u->execute([$name, $description, $price, $image, $category, $stock, $id]);
        setFlash('import_status', 'Product updated.');
        header('Location: index.php');
        exit;
    } elseif (!$message) {
        $message = 'Please fill all fields correctly.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="p-4">
<div class="container">
    <div class="card mx-auto" style="max-width:700px;">
        <div class="card-body">
            <h4 class="mb-3">Edit Product</h4>
            <?php if ($message): ?><div class="alert alert-danger"><?= sanitize($message) ?></div><?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" value="<?= sanitize($product['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <input name="category" class="form-control" value="<?= sanitize($product['category']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Price (€)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= (float)$product['price'] ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" min="0" value="<?= (int)$product['stock'] ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" required><?= sanitize($product['description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image Path</label>
                    <input name="image" class="form-control" value="<?= sanitize($product['image']) ?>">
                    <div class="mt-2">
                        <label class="form-label">Or upload new image</label>
                        <input type="file" name="image_file" class="form-control">
                    </div>
                    <?php if (!empty($product['image'])): ?>
                        <div class="mt-2">
                            <img src="../<?= ltrim($product['image'], '/') ?>" alt="preview" style="max-width:120px; height:auto;">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Save</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
