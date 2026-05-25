<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$orderNumber = $_GET['order'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container text-center">
        <div class="card shadow-sm border-0 mx-auto p-5" style="max-width:650px;border-radius:24px;">
            <div style="font-size:64px;">🌷</div>

            <h2 class="mt-3">Payment Successful</h2>
            <p class="text-muted">Your flower order payment was completed successfully.</p>

            <?php if ($order): ?>
                <div class="bg-light text-start rounded-4 p-4 my-4">
                    <p><strong>Order:</strong> <?= htmlspecialchars($order['order_number']) ?></p>
                    <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
                    <p><strong>Total:</strong> <?= formatPrice($order['total_amount']) ?></p>
                    <p><strong>Payment Status:</strong> <?= htmlspecialchars($order['payment_status']) ?></p>
                    <p><strong>Order Status:</strong> <?= htmlspecialchars($order['order_status']) ?></p>
                </div>
            <?php endif; ?>

            <a href="index.php" class="btn btn-primary-soft">Back to Home</a>
            <a href="products.php" class="btn btn-outline-secondary ms-2">Continue Shopping</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>