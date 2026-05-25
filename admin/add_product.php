<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once 'auth.php';

ensureStoreSchema($pdo);

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
    $image = sanitize($_POST['image'] ?? '');

    // Handle uploaded image file
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
            }
        }
    }

    if (!$message && $name && $description && $price > 0 && $category && $image) {
        $stmt = $pdo->prepare(
            "INSERT INTO products (name, description, price, image, category, stock) VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $name,
            $description,
            $price,
            $image,
            $category,
            $stock
        ]);

        $message = 'Product added successfully!';
    } elseif (!$message) {
        $message = 'Please fill all fields correctly.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 700px;">
        <div class="card-body p-4">
            <h2 class="mb-4">Add New Product</h2>

            <?php if ($message): ?>
                <div class="alert alert-info">
                    <?= sanitize($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Price (€)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" min="0" value="20" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <option value="Tulips">Tulips</option>
                        <option value="Bouquets">Bouquets</option>
                        <option value="Arrangements">Arrangements</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Image Path (optional)</label>
                    <input type="text" name="image" class="form-control" placeholder="assets/img/example.jpg">
                    <div class="mt-2">
                        <label class="form-label">Or upload image</label>
                        <input type="file" name="image_file" class="form-control">
                        <small class="text-muted">Uploaded file will be stored in `assets/img`.</small>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    Add Product
                </button>

                <a href="../products.php" class="btn btn-secondary ms-2">
                    View Products
                </a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
