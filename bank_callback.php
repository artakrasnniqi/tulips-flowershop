<?php
require_once 'config/db.php';

$config = require 'config/payment_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$merchantId = $_POST['merchant_id'] ?? '';
$orderNumber = $_POST['order_id'] ?? '';
$amount = $_POST['amount'] ?? '';
$status = $_POST['status'] ?? '';
$transactionId = $_POST['transaction_id'] ?? '';
$receivedHash = $_POST['hash'] ?? '';

if ($merchantId === '' || $orderNumber === '' || $amount === '' || $status === '' || $receivedHash === '') {
    http_response_code(400);
    exit('Missing required fields');
}

$expectedHashString = $merchantId . $orderNumber . $amount . $status . $config['merchant_secret'];
$expectedHash = hash('sha256', $expectedHashString);

if (!hash_equals($expectedHash, $receivedHash)) {
    http_response_code(403);
    exit('Invalid hash');
}

$stmt = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    exit('Order not found');
}

if ($status === 'success') {
    $newPaymentStatus = 'paid';
    $newOrderStatus = 'processing';
} else {
    $newPaymentStatus = 'failed';
    $newOrderStatus = 'new';
}

$responseJson = json_encode($_POST, JSON_UNESCAPED_UNICODE);

$update = $pdo->prepare("
    UPDATE orders
    SET payment_status = ?, order_status = ?, bank_transaction_id = ?, bank_response = ?
    WHERE order_number = ?
");

$update->execute([
    $newPaymentStatus,
    $newOrderStatus,
    $transactionId,
    $responseJson,
    $orderNumber
]);

header("Location: payment_success.php?order=" . urlencode($orderNumber));
exit;