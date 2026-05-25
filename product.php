<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

ensureStoreSchema($pdo);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect('products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid form submission. Please try again.');
        redirect('product.php?id=' . $id);
    }

    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $result = addProductToCart($pdo, $id, $quantity);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    redirect($result['success'] ? 'cart.php' : 'product.php?id=' . $id);
}

include 'includes/header.php';
$error = getFlash('error');
?>
<section class="py-5">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-lg-6">
                <img class="detail-image" src="<?= sanitize($product['image']) ?>" alt="<?= sanitize($product['name']) ?>">
            </div>
            <div class="col-lg-6">
                <span class="badge-soft mb-3 d-inline-block"><?= sanitize($product['category']) ?></span>
                <h1><?= sanitize($product['name']) ?></h1>
                <p class="price-tag fs-3 mt-3"><?= formatPrice($product['price']) ?></p>
                <p class="<?= (int)$product['stock'] > 0 ? 'text-success' : 'text-danger' ?> fw-semibold">
                    <?= sanitize(stockLabel($product['stock'])) ?>
                </p>
                <p class="text-secondary mt-3"><?= sanitize($product['description']) ?></p>
                <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
                <div class="info-card p-4 mt-4">
                    <ul class="mb-0 text-secondary">
                        <li>Freshly prepared by expert florists</li>
                        <li>Gift-ready wrapping included</li>
                        <li>Suitable for same-day local delivery</li>
                    </ul>
                </div>
                <form method="POST" class="row g-3 mt-3">
                    <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                    <div class="col-sm-4">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" max="<?= (int)$product['stock'] ?>" value="1" <?= (int)$product['stock'] <= 0 ? 'disabled' : '' ?>>
                    </div>
                    <div class="col-sm-8 d-flex align-items-end">
                        <button class="btn btn-primary-soft w-100" <?= (int)$product['stock'] <= 0 ? 'disabled' : '' ?>>
                            <?= (int)$product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
