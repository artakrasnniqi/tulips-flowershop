<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$config = require 'config/payment_config.php';
ensureStoreSchema($pdo);

function checkoutFail($message) {
    setFlash('checkout_error', $message);
    header('Location: checkout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    checkoutFail('Invalid request.');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    checkoutFail('Invalid form submission. Please try again.');
}

$idem = $_POST['idempotency_token'] ?? '';
if ($idem === '' || is_idempotency_token_used($idem)) {
    checkoutFail('This checkout form was already submitted. Please review your cart and try again.');
}

normalizeCart($pdo);
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    checkoutFail('Cart is empty.');
}

$customerName = trim($_POST['customer_name'] ?? '');
$customerEmail = trim($_POST['customer_email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$city = trim($_POST['city'] ?? '');
$address = trim($_POST['address'] ?? '');
$paymentMethod = $_POST['payment_method'] ?? '';

if (strlen($customerName) < 3 || strlen($customerName) > 255) {
    checkoutFail('Please enter a valid name.');
}
if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    checkoutFail('Please enter a valid email address.');
}
if (!preg_match('/^[0-9+\s\-]{8,20}$/', $phone)) {
    checkoutFail('Please enter a valid phone number.');
}
if (strlen($city) < 2 || strlen($city) > 120 || strlen($address) < 5 || strlen($address) > 500) {
    checkoutFail('Please enter a valid city and address.');
}
if (!in_array($paymentMethod, ['card', 'cod'], true)) {
    checkoutFail('Invalid payment method.');
}

if ($paymentMethod === 'card') {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if (!$isHttps && empty($config['allow_insecure'])) {
        checkoutFail('Card payments require HTTPS.');
    }
}

$productIds = array_map('intval', array_keys($cart));
$placeholders = implode(',', array_fill(0, count($productIds), '?'));

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($products) !== count($productIds)) {
        throw new RuntimeException('Some products are no longer available.');
    }

    $total = 0;
    foreach ($products as $product) {
        $qty = max(1, (int)$cart[$product['id']]);
        if ((int)$product['stock'] < $qty) {
            throw new RuntimeException($product['name'] . ' does not have enough stock.');
        }
        $total += (float)$product['price'] * $qty;
    }

    $orderNumber = 'TULIPS-' . time() . '-' . random_int(1000, 9999);
    $paymentStatus = $paymentMethod === 'cod' ? 'cod_pending' : 'pending';

    $orderStmt = $pdo->prepare("
        INSERT INTO orders (
            order_number,
            customer_name,
            customer_email,
            phone,
            address,
            city,
            delivery_option,
            payment_method,
            payment_status,
            order_status,
            total_amount
        ) VALUES (?, ?, ?, ?, ?, ?, 'standard', ?, ?, 'new', ?)
    ");
    $orderStmt->execute([
        $orderNumber,
        $customerName,
        $customerEmail,
        $phone,
        $address,
        $city,
        $paymentMethod,
        $paymentStatus,
        $total
    ]);

    $orderId = (int)$pdo->lastInsertId();
    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
    $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');

    foreach ($products as $product) {
        $qty = max(1, (int)$cart[$product['id']]);
        $itemStmt->execute([$orderId, $product['id'], $qty, $product['price']]);
        $stockStmt->execute([$qty, $product['id'], $qty]);
        if ($stockStmt->rowCount() !== 1) {
            throw new RuntimeException($product['name'] . ' does not have enough stock.');
        }
    }

    $pdo->commit();
    mark_idempotency_token_used($idem);
    unset($_SESSION['cart']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Checkout error: ' . $e->getMessage());
    checkoutFail($e instanceof RuntimeException ? $e->getMessage() : 'There was an error processing your order. Please try again.');
}

if ($paymentMethod === 'cod') {
    header('Location: order_success.php?order_id=' . urlencode((string)$orderId));
    exit;
}

$merchantId = $config['merchant_id'];
$merchantSecret = $config['merchant_secret'];
$amount = number_format($total, 2, '.', '');
$currency = $config['currency'];
$returnUrl = $config['return_url'] . '?order_id=' . urlencode($orderNumber);
$callbackUrl = $config['callback_url'];
$hash = hash('sha256', $merchantId . $orderNumber . $amount . $currency . $merchantSecret);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to Bank...</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
    <p>Redirecting to bank for payment...</p>

    <form id="bankForm" method="POST" action="<?= htmlspecialchars($config['bank_gateway_url']) ?>">
        <input type="hidden" name="merchant_id" value="<?= htmlspecialchars($merchantId) ?>">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderNumber) ?>">
        <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
        <input type="hidden" name="currency" value="<?= htmlspecialchars($currency) ?>">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">
        <input type="hidden" name="callback_url" value="<?= htmlspecialchars($callbackUrl) ?>">
        <input type="hidden" name="hash" value="<?= htmlspecialchars($hash) ?>">
        <noscript><button type="submit">Continue to bank</button></noscript>
    </form>

    <script>document.getElementById('bankForm').submit();</script>
</body>
</html>
