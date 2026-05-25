<?php
$merchantId = $_POST['merchant_id'] ?? '';
$orderId = $_POST['order_id'] ?? '';
$amount = $_POST['amount'] ?? '';
$currency = $_POST['currency'] ?? 'EUR';
$returnUrl = $_POST['return_url'] ?? '';
$secret = 'super_secret_bank_key_2026';

function clean($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$successHash = hash('sha256', $merchantId . $orderId . $amount . 'success' . $secret);
$failHash = hash('sha256', $merchantId . $orderId . $amount . 'fail' . $secret);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fake Bank Gateway</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f6f8;
            font-size: 14px;
        }

        .payment-wrapper {
            max-width: 820px;
            margin: 25px auto;
        }

        .payment-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .payment-title {
            font-size: 25px;
            font-weight: 700;
        }

        .order-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 14px;
        }

        .method-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
        }

        .method-btn {
            flex: 1;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .method-btn.active {
            border-color: #d45b83;
            background: #fff5f8;
            color: #d45b83;
        }

        .method-btn img {
            height: 22px;
            margin-left: 6px;
        }

        .card-logos {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .card-logos img {
            height: 28px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 4px 8px;
        }

        .visa-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 28px;
            min-width: 58px;
            background: #fff;
            color: #1a1f71;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 4px 10px;
            font-weight: 900;
            font-size: 17px;
            font-style: italic;
            letter-spacing: -1px;
            margin-left: 6px;
            line-height: 1;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .form-control,
        .form-select {
            height: 38px;
            font-size: 14px;
        }

        .secure-box {
            background: #f1fff7;
            border: 1px solid #cfeedd;
            border-radius: 10px;
            padding: 10px;
            font-size: 13px;
        }

        .btn-pay {
            background: #198754;
            color: #fff;
            font-weight: 600;
            padding: 10px 25px;
        }

        .btn-pay:hover {
            background: #157347;
            color: #fff;
        }

        .paypal-box {
            display: none;
            border: 1px solid #e1e1e1;
            border-radius: 12px;
            padding: 20px;
            background: #fafafa;
        }

        @media (max-width: 576px) {
            .payment-wrapper {
                margin: 10px;
            }

            .method-tabs {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<div class="payment-wrapper">
    <div class="card payment-card">
        <div class="card-body p-4">

            <h2 class="payment-title mb-2">Fake Bank Gateway</h2>
            <p class="text-muted mb-3">Local test payment gateway for TULIPS FlowerShop.</p>

            <div class="order-box mb-3">
                <div><strong>Merchant ID:</strong> <?= clean($merchantId) ?></div>
                <div><strong>Order ID:</strong> <?= clean($orderId) ?></div>
                <div><strong>Amount:</strong> <?= clean($amount) ?> <?= clean($currency) ?></div>
            </div>

            <div class="method-tabs">
                <button type="button" class="method-btn active" id="cardBtn" onclick="showPaymentMethod('card')">
                    Pay with Credit Card
                    <span class="visa-logo">VISA</span>
                </button>

                <button type="button" class="method-btn" id="paypalBtn" onclick="showPaymentMethod('paypal')">
                    Pay with PayPal
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal">
                </button>
            </div>

            <form method="POST" action="bank_callback.php" id="cardPaymentForm">

                <input type="hidden" name="merchant_id" value="<?= clean($merchantId) ?>">
                <input type="hidden" name="order_id" value="<?= clean($orderId) ?>">
                <input type="hidden" name="amount" value="<?= clean($amount) ?>">
                <input type="hidden" name="status" value="success">
                <input type="hidden" name="transaction_id" value="TRX<?= rand(100000, 999999) ?>">
                <input type="hidden" name="hash" value="<?= $successHash ?>">

                <div id="cardBox">
                    <div class="row g-3 align-items-end mb-2">
                        <div class="col-md-7">
                            <label class="form-label">Card Number</label>
                            <input type="text" class="form-control" id="cardNumber"
                                   placeholder="1234 5678 9012 3456" maxlength="19" required>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Accepted Cards</label>
                            <div class="card-logos">
                                <span class="visa-logo">VISA</span>
                                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="MasterCard">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-4">
                            <label class="form-label">Expiry Date</label>
                            <input type="text" class="form-control" id="expiryDate"
                                   placeholder="MM/YY" maxlength="5" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">CVV</label>
                            <input type="password" class="form-control" id="cvv"
                                   placeholder="123" maxlength="4" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Name on Card</label>
                            <input type="text" class="form-control" id="cardName"
                                   placeholder="Full name" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <select class="form-select" required>
                                <option value="">Select Country</option>
                                <option value="Kosovo">Kosovo</option>
                                <option value="Albania">Albania</option>
                                <option value="North Macedonia">North Macedonia</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" placeholder="Prishtina" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Billing Address</label>
                            <input type="text" class="form-control" placeholder="Street address" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">ZIP / Postal</label>
                            <input type="text" class="form-control" placeholder="10000" required>
                        </div>
                    </div>
                </div>

                <div id="paypalBox" class="paypal-box mb-3">
                    <div class="text-center">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg"
                             alt="PayPal" style="height:45px;" class="mb-3">

                        <h5>Pay with PayPal</h5>
                        <p class="text-muted mb-2">
                            This is a simulated PayPal payment option.
                        </p>

                        <input type="email" class="form-control mb-3"
                               placeholder="PayPal email address">
                    </div>
                </div>

                <div class="secure-box mb-3">
                    🔒 Safe and secured simulated payment gateway. Card data is not saved.
                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <small class="text-muted">
                        This form is only for school project simulation.
                    </small>

                    <div class="d-flex gap-2">
                        <?php if (!empty($returnUrl)): ?>
                            <a href="<?= clean($returnUrl) ?>" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        <?php endif; ?>

                        <button type="button" class="btn btn-danger" onclick="simulateFail()">
                            Simulate Fail
                        </button>

                        <button type="submit" class="btn btn-pay" id="payButton">
                            Pay Now
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
const cardInput = document.getElementById('cardNumber');
const expiryInput = document.getElementById('expiryDate');
const cvvInput = document.getElementById('cvv');

function showPaymentMethod(method) {
    const cardBox = document.getElementById('cardBox');
    const paypalBox = document.getElementById('paypalBox');
    const cardBtn = document.getElementById('cardBtn');
    const paypalBtn = document.getElementById('paypalBtn');
    const payButton = document.getElementById('payButton');

    if (method === 'card') {
        cardBox.style.display = 'block';
        paypalBox.style.display = 'none';

        cardBtn.classList.add('active');
        paypalBtn.classList.remove('active');

        payButton.innerText = 'Pay Now';
    }

    if (method === 'paypal') {
        cardBox.style.display = 'none';
        paypalBox.style.display = 'block';

        paypalBtn.classList.add('active');
        cardBtn.classList.remove('active');

        payButton.innerText = 'Pay with PayPal';
    }
}

cardInput.addEventListener('input', function () {
    let value = this.value.replace(/\D/g, '').substring(0, 16);
    this.value = value.replace(/(.{4})/g, '$1 ').trim();
});

expiryInput.addEventListener('input', function () {
    let value = this.value.replace(/\D/g, '').substring(0, 4);

    if (value.length >= 3) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }

    this.value = value;
});

cvvInput.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').substring(0, 4);
});

function simulateFail() {
    const form = document.getElementById('cardPaymentForm');
    form.querySelector('input[name="status"]').value = 'fail';
    form.querySelector('input[name="hash"]').value = '<?= $failHash ?>';
    form.submit();
}
</script>

</body>
</html>