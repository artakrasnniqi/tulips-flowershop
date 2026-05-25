<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once 'auth.php';

ensureStoreSchema($pdo);

$success = null;
$errors = [];
$importStatus = getFlash('import_status');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    }

    $name = sanitize($_POST['name'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = max(0, (int)($_POST['stock'] ?? 0));
    $image = sanitize($_POST['image'] ?? '');

    if ($name === '' || $category === '' || $description === '' || $price <= 0 || $image === '') {
        $errors[] = 'All fields are required and price must be valid.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, description, price, image, stock) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $description, $price, $image, $stock]);
        $success = 'Product added successfully.';
    }
}

$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
$productCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TULIPS FlowerShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <span class="badge-soft">Admin Panel</span>
                <h1 class="mt-2">Manage products</h1>
                <p class="text-secondary mb-0">Total products: <strong><?= $productCount ?></strong></p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form method="POST" action="import_products.php" class="m-0">
                    <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                    <button type="submit" class="btn btn-outline-secondary">Import Bouquets</button>
                </form>
                <a href="../index.php" class="btn btn-outline-soft">Back to shop</a>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?= sanitize($success) ?></div><?php endif; ?>
        <?php if ($importStatus): ?><div class="alert alert-info"><?= sanitize($importStatus) ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="alert alert-danger"><?= sanitize(implode(' ', $errors)) ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="admin-card p-4">
                    <h4>Add Product</h4>
                    <form method="POST" class="row g-3 mt-1">
                        <div class="col-12"><input type="text" name="name" class="form-control" placeholder="Product name" required></div>
                        <div class="col-12"><input type="text" name="category" class="form-control" placeholder="Category" required></div>
                        <div class="col-12"><input type="number" name="price" class="form-control" step="0.01" placeholder="Price" required></div>
                        <div class="col-12"><input type="number" name="stock" class="form-control" min="0" value="20" placeholder="Stock" required></div>
                        <div class="col-12"><input type="text" name="image" class="form-control" placeholder="Image path or URL" required></div>
                        <div class="col-12"><textarea name="description" rows="4" class="form-control" placeholder="Description" required></textarea></div>
                        <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                        <div class="col-12"><button class="btn btn-primary-soft">Save Product</button></div>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="admin-card p-4">
                    <h4>Current Products</h4>
                    <div class="table-responsive mt-3">
                        <table class="table align-middle">
                            <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= sanitize($product['name']) ?></td>
                                        <td><?= sanitize($product['category']) ?></td>
                                                <td><?= formatPrice($product['price']) ?></td>
                                                <td>
                                                    <span class="<?= (int)$product['stock'] > 0 ? 'text-success' : 'text-danger' ?>">
                                                        <?= (int)$product['stock'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="edit_product.php?id=<?= (int)$product['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                    <form method="POST" action="delete_product.php" class="d-inline-block ms-1" onsubmit="return confirm('Delete this product?');">
                                                        <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                                                        <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
