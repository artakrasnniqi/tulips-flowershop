<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$config = require 'config/payment_config.php';
ensureStoreSchema($pdo);

function jsonFail($message, $status = 400) {
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonFail('Invalid request', 405);
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    jsonFail('Invalid form submission. Please refresh and try again.');
}

$idem = $_POST['idempotency_token'] ?? '';
if ($idem === '' || is_idempotency_token_used($idem)) {
    jsonFail('This checkout was already submitted. Please refresh your cart.');
}

normalizeCart($pdo);
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    jsonFail('Cart is empty');
}

$customerName = trim($_POST['customer_name'] ?? '');
$customerEmail = trim($_POST['customer_email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$city = trim($_POST['city'] ?? '');
$address = trim($_POST['address'] ?? '');
$paymentMethod = $_POST['payment_method'] ?? '';

if (strlen($customerName) < 3 || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
    jsonFail('Please enter valid customer details');
}
if (!preg_match('/^[0-9+\s\-]{8,20}$/', $phone) || strlen($city) < 2 || strlen($address) < 5) {
    jsonFail('Please enter valid delivery details');
}
if ($paymentMethod !== 'card') {
    jsonFail('Invalid payment method');
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
if (!$isHttps && empty($config['allow_insecure'])) {
    jsonFail('Card payments require HTTPS');
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

    $ins = $pdo->prepare("
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
        ) VALUES (?, ?, ?, ?, ?, ?, 'standard', 'card', 'pending', 'new', ?)
    ");
    $ins->execute([$orderNumber, $customerName, $customerEmail, $phone, $address, $city, $total]);
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
    error_log('create_order_ajax error: ' . $e->getMessage());
    jsonFail($e instanceof RuntimeException ? $e->getMessage() : 'Database error');
}

$merchantId = $config['merchant_id'];
$merchantSecret = $config['merchant_secret'];
$amount = number_format($total, 2, '.', '');
$currency = $config['currency'];
$returnUrl = $config['return_url'] . '?order_id=' . urlencode($orderNumber);
$callbackUrl = $config['callback_url'];
$hash = hash('sha256', $merchantId . $orderNumber . $amount . $currency . $merchantSecret);

echo json_encode([
    'success' => true,
    'order_id' => $orderId,
    'order_number' => $orderNumber,
    'merchant_id' => $merchantId,
    'amount' => $amount,
    'currency' => $currency,
    'return_url' => $returnUrl,
    'callback_url' => $callbackUrl,
    'hash' => $hash,
    'bank_gateway_url' => $config['bank_gateway_url']
]);
