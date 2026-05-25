<?php
require_once 'config/db.php';
$config = require 'config/payment_config.php';

$orderNumber = $_GET['order_id'] ?? '';
$status = $_GET['status'] ?? 'success';
$amount = $_GET['amount'] ?? '0.00';

if ($orderNumber === '') {
    die('Missing order_id');
}

$merchantId = $config['merchant_id'];
$transactionId = 'TRX' . rand(100000, 999999);

$hashString = $merchantId . $orderNumber . $amount . $status . $config['merchant_secret'];
$hash = hash('sha256', $hashString);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Bank Callback</title>
</head>
<body onload="document.forms[0].submit()">
    <form method="POST" action="bank_callback.php">
        <input type="hidden" name="merchant_id" value="<?= htmlspecialchars($merchantId) ?>">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderNumber) ?>">
        <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
        <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
        <input type="hidden" name="transaction_id" value="<?= htmlspecialchars($transactionId) ?>">
        <input type="hidden" name="hash" value="<?= htmlspecialchars($hash) ?>">
    </form>
</body>
</html>