<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

ensureStoreSchema($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid form submission. Please try again.');
        redirect('cart.php');
    }

    if (isset($_POST['update_cart']) && !empty($_POST['quantities'])) {
        $productIds = array_map('intval', array_keys($_POST['quantities']));
        $stockById = [];
        if (!empty($productIds)) {
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $stmt = $pdo->prepare("SELECT id, stock FROM products WHERE id IN ($placeholders)");
            $stmt->execute($productIds);
            foreach ($stmt->fetchAll() as $row) {
                $stockById[(int)$row['id']] = (int)$row['stock'];
            }
        }

        foreach ($_POST['quantities'] as $productId => $quantity) {
            $productId = (int)$productId;
            $quantity = max(0, (int)$quantity);
            $stock = $stockById[$productId] ?? 0;
            if ($quantity === 0) {
                unset($_SESSION['cart'][$productId]);
            } elseif ($stock <= 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                $_SESSION['cart'][$productId] = min($quantity, $stock);
            }
        }
        setFlash('success', 'Cart updated successfully.');
        redirect('cart.php');
    }

    if (isset($_POST['remove_item'])) {
        $productId = (int)$_POST['remove_item'];
        unset($_SESSION['cart'][$productId]);
        setFlash('success', 'Item removed from cart.');
        redirect('cart.php');
    }
}

$stockMessages = normalizeCart($pdo);
if (!empty($stockMessages)) {
    setFlash('error', implode(' ', $stockMessages));
}

$items = getCartItems($pdo);
$total = getCartTotal($pdo);
$success = getFlash('success');
$error = getFlash('error');

include 'includes/header.php';
?>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <span class="badge-soft">Shopping Cart</span>
                <h1 class="mt-2">Your floral selections</h1>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?= sanitize($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="info-card p-5 text-center">
                <i class="bi bi-bag-heart display-4 text-secondary"></i>
                <h3 class="mt-3">Your cart is empty</h3>
                <p class="text-secondary">Add beautiful flowers to continue shopping.</p>
                <a href="products.php" class="btn btn-primary-soft">Browse Catalog</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                        <div class="table-responsive">
                            <table class="table table-cart align-middle">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr data-price="<?= (float)$item['price'] ?>">
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="<?= sanitize($item['image']) ?>" width="72" height="72" class="rounded-4 object-fit-cover" alt="<?= sanitize($item['name']) ?>">
                                                    <div>
                                                        <strong><?= sanitize($item['name']) ?></strong><br>
                                                        <small class="text-secondary"><?= sanitize($item['category']) ?></small>
                                                        <br><small class="<?= (int)$item['stock'] > 0 ? 'text-success' : 'text-danger' ?>"><?= sanitize(stockLabel($item['stock'])) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= formatPrice($item['price']) ?></td>
                                            <td>
                                                <input type="number" class="form-control qty-input js-qty" name="quantities[<?= (int)$item['id'] ?>]" value="<?= (int)$item['quantity'] ?>" min="1" max="<?= (int)$item['stock'] ?>">
                                            </td>
                                            <td class="js-subtotal"><?= formatPrice($item['subtotal']) ?></td>
                                            <td>
                                                <button class="btn btn-outline-danger btn-sm" type="submit" name="remove_item" value="<?= (int)$item['id'] ?>">Remove</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" name="update_cart" class="btn btn-outline-soft">Update Cart</button>
                    </form>
                </div>
                <div class="col-lg-4">
                    <div class="summary-box p-4 sticky-top" style="top: 96px;">
                        <h4>Order Summary</h4>
                        <div class="d-flex justify-content-between mt-3">
                            <span>Items</span>
                            <strong><?= count($items) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span>Delivery</span>
                            <strong>Calculated at checkout</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5">
                            <span>Total</span>
                            <strong class="js-cart-total"><?= formatPrice($total) ?></strong>
                        </div>
                        <a href="checkout.php" class="btn btn-checkout w-100 mt-4">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
