<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

ensureStoreSchema($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid form submission. Please try again.');
        redirect('index.php');
    }

    $result = addProductToCart($pdo, (int)($_POST['product_id'] ?? 0), 1);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    redirect($result['success'] ? 'cart.php' : 'index.php');
}

$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 3");
$featuredProducts = $stmt->fetchAll();

include 'includes/header.php';
$success = getFlash('success');
$error = getFlash('error');
?>
<section class="home-banner">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="home-banner-badge mb-3">Fresh blooms, beautifully delivered</span>
                <h1 class="display-4 fw-bold mt-3">Elegant flowers for every moment.</h1>
                <p class="lead text-secondary mt-3">Discover premium tulips, hand-tied bouquets, and floral arrangements crafted for birthdays, anniversaries, events, and everyday joy.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="products.php" class="btn btn-primary-soft">Shop Now</a>
                    <a href="delivery.php" class="btn btn-outline-soft">Delivery Info</a>
                </div>
                <div class="row g-3 mt-4">
                    <div class="col-sm-4">
                        <div class="home-stat-card p-3 text-center">
                            <h4 class="mb-1">50+</h4>
                            <small>Floral designs</small>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="home-stat-card p-3 text-center">
                            <h4 class="mb-1">Same Day</h4>
                            <small>Local delivery</small>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="home-stat-card p-3 text-center">
                            <h4 class="mb-1">Fresh</h4>
                            <small>Daily sourced blooms</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="home-banner-photo">
                    <img
                        class="home-banner-image"
                        src="assets/img/highquality-flower-shop-with-large-sign-modern-storefront_976564-6835.avif"
                        alt="Modern flower shop storefront"
                    >
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="info-card p-4 h-100">
                    <div class="feature-icon mx-auto mb-3"><i class="bi bi-flower2"></i></div>
                    <h4>Curated Collections</h4>
                    <p class="mb-0 text-secondary">Seasonal bouquets and signature arrangements designed to leave a lasting impression.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card p-4 h-100">
                    <div class="feature-icon mx-auto mb-3"><i class="bi bi-truck"></i></div>
                    <h4>Reliable Delivery</h4>
                    <p class="mb-0 text-secondary">Flexible delivery slots, careful packaging, and clear delivery guidance for every order.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card p-4 h-100">
                    <div class="feature-icon mx-auto mb-3"><i class="bi bi-heart"></i></div>
                    <h4>Made with Care</h4>
                    <p class="mb-0 text-secondary">Each arrangement is prepared by florists who focus on freshness, color balance, and style.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <?php if ($success): ?><div class="alert alert-success"><?= sanitize($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <span class="badge-soft">Featured collection</span>
                <h2 class="section-title mt-2">Best-selling flowers</h2>
            </div>
            <a href="products.php" class="btn btn-outline-soft">View All Products</a>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="product-card p-3 h-100">
                        <img src="<?= sanitize($product['image']) ?>" alt="<?= sanitize($product['name']) ?>">
                        <div class="p-2 pt-3">
                            <h4><?= sanitize($product['name']) ?></h4>
                            <p class="text-secondary"><?= sanitize(substr($product['description'], 0, 100)) ?>...</p>
                            <p class="small <?= (int)$product['stock'] > 0 ? 'text-success' : 'text-danger' ?> mb-3">
                                <?= sanitize(stockLabel($product['stock'])) ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <span class="price-tag"><?= formatPrice($product['price']) ?></span>
                                <a href="product.php?id=<?= (int)$product['id'] ?>" class="btn btn-primary-soft btn-sm">Details</a>
                                <form method="POST" class="m-0">
                                    <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                                    <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                    <button type="submit" name="add_to_cart" value="1" class="btn btn-outline-soft btn-sm" <?= (int)$product['stock'] <= 0 ? 'disabled' : '' ?>>
                                        <?= (int)$product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
