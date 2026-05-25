<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$token = $_GET['token'] ?? '';
if (!is_string($token) || strlen($token) < 10) {
    http_response_code(400);
    echo 'Invalid confirmation token.';
    exit;
}

// Find confirmation
$stmt = $pdo->prepare('SELECT oc.id as oc_id, oc.order_id, oc.used, o.payment_method, o.order_number, o.total_amount FROM order_confirmations oc JOIN orders o ON o.id = oc.order_id WHERE oc.token = ?');
$stmt->execute([$token]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    http_response_code(404);
    echo 'Confirmation token not found.';
    exit;
}

if ($row['used']) {
    echo 'This confirmation link has already been used.';
    exit;
}

// Mark confirmation used
$u = $pdo->prepare('UPDATE order_confirmations SET used = 1 WHERE id = ?');
$u->execute([$row['oc_id']]);

$orderId = (int)$row['order_id'];
$orderNumber = $row['order_number'];
$paymentMethod = $row['payment_method'];
$total = (float)$row['total_amount'];

if ($paymentMethod === 'cod') {
    // update order status and payment_status
    $pdo->prepare('UPDATE orders SET payment_status = ?, order_status = ? WHERE id = ?')
        ->execute(['cod_pending', 'processing', $orderId]);
    // Optionally clear session cart (best-effort)
    if (isset($_SESSION['cart'])) unset($_SESSION['cart']);
    header('Location: order_success.php?order_id=' . urlencode($orderId));
    exit;
}

// For card payments, redirect to bank gateway using same logic as process_checkout
$config = require 'config/payment_config.php';
$merchantId = $config['merchant_id'];
$merchantSecret = $config['merchant_secret'];
$amount = number_format($total, 2, '.', '');
$currency = $config['currency'];
$returnUrl = $config['return_url'] . '?order_id=' . urlencode($orderNumber);
$callbackUrl = $config['callback_url'];
$hashString = $merchantId . $orderNumber . $amount . $currency . $merchantSecret;
$hash = hash('sha256', $hashString);

// show auto-post form to bank gateway
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to Bank...</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>#manualLink{display:none;margin-top:18px}</style>
</head>
<body>
    <p>Redirecting to bank for payment... If you are not redirected, use the button below.</p>

    <form id="bankForm" name="bankForm" method="POST" action="<?= htmlspecialchars($config['bank_gateway_url']) ?>">
        <input type="hidden" name="merchant_id" value="<?= htmlspecialchars($merchantId) ?>">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderNumber) ?>">
        <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
        <input type="hidden" name="currency" value="<?= htmlspecialchars($currency) ?>">
        <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">
        <input type="hidden" name="callback_url" value="<?= htmlspecialchars($callbackUrl) ?>">
        <input type="hidden" name="hash" value="<?= htmlspecialchars($hash) ?>">
        <noscript>
            <p><strong>JavaScript is required to auto-redirect.</strong></p>
            <button type="submit">Continue to bank</button>
        </noscript>
    </form>

    <div id="manualLink">
        <form method="POST" action="<?= htmlspecialchars($config['bank_gateway_url']) ?>">
            <input type="hidden" name="merchant_id" value="<?= htmlspecialchars($merchantId) ?>">
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderNumber) ?>">
            <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
            <input type="hidden" name="currency" value="<?= htmlspecialchars($currency) ?>">
            <input type="hidden" name="return_url" value="<?= htmlspecialchars($returnUrl) ?>">
            <input type="hidden" name="callback_url" value="<?= htmlspecialchars($callbackUrl) ?>">
            <input type="hidden" name="hash" value="<?= htmlspecialchars($hash) ?>">
            <button type="submit">Click here to continue to bank</button>
        </form>
    </div>

    <script>
        (function(){
            try {
                var f = document.getElementById('bankForm');
                if (f) {
                    f.submit();
                    // If after 2s still on page, show manual link
                    setTimeout(function(){
                        document.getElementById('manualLink').style.display = 'block';
                    }, 2000);
                } else {
                    document.getElementById('manualLink').style.display = 'block';
                }
            } catch (e) {
                document.getElementById('manualLink').style.display = 'block';
            }
        })();
    </script>
</body>
</html>
