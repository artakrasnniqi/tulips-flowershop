<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$orderNumber = $_GET['order_id'] ?? '';

if ($orderNumber === '') {
    die('Order not found.');
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Order not found.');
}

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="info-card p-4">
            <span class="badge-soft">Payment Status</span>
            <h1 class="mt-2 mb-4">Order Payment Result</h1>

            <p><strong>Order Number:</strong> <?= htmlspecialchars($order['order_number']) ?></p>
            <p><strong>Payment Status:</strong> <?= htmlspecialchars($order['payment_status']) ?></p>
            <p><strong>Order Status:</strong> <?= htmlspecialchars($order['order_status']) ?></p>

            <?php if ($order['payment_status'] === 'paid'): ?>
                <div class="alert alert-success">Payment completed successfully.</div>
            <?php elseif ($order['payment_status'] === 'failed'): ?>
                <div class="alert alert-danger">Payment failed or was declined.</div>
            <?php else: ?>
                <div class="alert alert-warning">Payment is still pending bank confirmation.</div>
            <?php endif; ?>

            <a href="index.php" class="btn btn-primary-soft mt-3">Back to Home</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>