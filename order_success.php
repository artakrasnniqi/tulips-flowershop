<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId <= 0) {
    die('Order not found.');
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Order not found.');
}

unset($_SESSION['cart']);

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="info-card p-4">
            <span class="badge-soft">Order Confirmed</span>
            <h1 class="mt-2 mb-4">Thank you for your order</h1>

            <p><strong>Order Number:</strong> <?= htmlspecialchars($order['order_number']) ?></p>
            <p><strong>Payment Method:</strong> Cash on Delivery</p>
            <p><strong>Status:</strong> Your order has been received successfully.</p>

            <a href="index.php" class="btn btn-primary-soft mt-3">Back to Home</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>