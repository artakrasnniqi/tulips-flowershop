<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

ensureStoreSchema($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid form submission. Please try again.');
        redirect('products.php');
    }

    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    $result = addProductToCart($pdo, $productId, $quantity);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    redirect($result['success'] ? 'cart.php' : 'products.php');
}

$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

include 'includes/header.php';

$success = getFlash('success');
$error = getFlash('error');

?>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <span class="badge-soft">Catalog</span>
                <h1 class="mt-2">Browse our flowers</h1>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?= sanitize($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>

        <div class="filter-bar p-3 mb-4">
            <form class="row g-3" method="GET">
                <div class="col-md-6">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search tulips, bouquets, arrangements..."
                        value="<?= htmlspecialchars($search) ?>"
                    >
                </div>

                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="">All categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary-soft">Filter</button>
                </div>
            </form>
        </div>

        <div class="row g-4">
            <?php if (empty($products)): ?>
                <div class="col-12">
                    <div class="info-card p-4 text-center">
                        <h4>No products found</h4>
                        <p class="mb-0 text-secondary">Try a different keyword or category filter.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-lg-4 d-flex">
                    <div class="product-card p-3 w-100 d-flex flex-column">
                        <img
                            src="<?= htmlspecialchars($product['image']) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>"
                            class="product-thumb"
                        >

                        <div class="p-2 pt-3 d-flex flex-column flex-grow-1">
                            <span class="badge-soft align-self-start mb-2">
                                <?= htmlspecialchars($product['category']) ?>
                            </span>

                            <h4><?= htmlspecialchars($product['name']) ?></h4>

                            <p class="text-secondary product-desc">
                                <?= htmlspecialchars(substr($product['description'], 0, 120)) ?>...
                            </p>

                            <div class="mt-auto">
                                <div class="mb-3">
                                    <span class="price-tag"><?= formatPrice($product['price']) ?></span>
                                    <span class="ms-2 small <?= (int)$product['stock'] > 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= sanitize(stockLabel($product['stock'])) ?>
                                    </span>
                                </div>

                                <div class="d-grid gap-2">
                                    <a href="product.php?id=<?= (int)$product['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                        View Product
                                    </a>

                                    <form method="POST" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                                        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button
                                            type="submit"
                                            name="add_to_cart"
                                            value="1"
                                            class="btn btn-primary-soft btn-sm w-100"
                                            <?= (int)$product['stock'] <= 0 ? 'disabled' : '' ?>
                                        >
                                            <?= (int)$product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
