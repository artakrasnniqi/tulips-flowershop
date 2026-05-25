<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

ensureStoreSchema($pdo);

$stockMessages = normalizeCart($pdo);
if (!empty($stockMessages)) {
    setFlash('checkout_error', implode(' ', $stockMessages));
}

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    include 'includes/header.php';
    echo '<section class="py-5"><div class="container"><div class="alert alert-warning">Your cart is empty.</div><a class="btn btn-primary-soft" href="products.php">Browse Catalog</a></div></section>';
    include 'includes/footer.php';
    exit;
}

$items = getCartItems($pdo);
$total = getCartTotal($pdo);
$checkoutError = getFlash('checkout_error');
$checkoutSuccess = getFlash('checkout_success');
$idempotencyToken = create_idempotency_token();

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <?php if ($checkoutError): ?><div class="alert alert-danger"><?= sanitize($checkoutError) ?></div><?php endif; ?>
        <?php if ($checkoutSuccess): ?><div class="alert alert-success"><?= sanitize($checkoutSuccess) ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="info-card p-4">
                    <span class="badge-soft">Checkout</span>
                    <h1 class="mt-2 mb-4">Complete your order</h1>

                    <form id="checkoutForm" action="process_checkout.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= sanitize(csrf_token()) ?>">
                        <input type="hidden" name="idempotency_token" value="<?= sanitize($idempotencyToken) ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="customer_name" class="form-control" required minlength="3" maxlength="255" placeholder="Full name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="customer_email" class="form-control" required maxlength="255" placeholder="example@email.com">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-control" required minlength="8" maxlength="20" pattern="[0-9+\s-]{8,20}" placeholder="+383 44 123 456">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" required minlength="2" maxlength="120" placeholder="Prishtina">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Delivery Address</label>
                                <textarea name="address" class="form-control" rows="3" required minlength="5" maxlength="500" placeholder="Street, building, apartment number"></textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Payment Method</h5>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" checked required>
                            <label class="form-check-label" for="card">Pay by Card</label>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod">
                            <label class="form-check-label" for="cod">Cash on Delivery</label>
                        </div>

                        <button type="submit" class="btn btn-primary-soft w-100">
                            Continue
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="info-card p-4">
                    <h4 class="mb-3">Order Summary</h4>

                    <?php foreach ($items as $item): ?>
                        <div class="d-flex justify-content-between gap-3 mb-3">
                            <div>
                                <strong><?= sanitize($item['name']) ?></strong><br>
                                <small class="text-secondary">Qty: <?= (int)$item['quantity'] ?></small>
                            </div>
                            <div class="text-nowrap">
                                <?= formatPrice($item['subtotal']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span><?= formatPrice($total) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('checkoutForm').addEventListener('submit', function (e) {
    const method = document.querySelector('input[name="payment_method"]:checked').value;
    if (method !== 'card') return;

    e.preventDefault();

    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Processing...';
    }

    fetch('create_order_ajax.php', {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin'
    }).then(function (res) {
        return res.json();
    }).then(function (json) {
        if (!json.success) {
            alert(json.error || 'Could not create order');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Continue';
            }
            return;
        }

        const bankForm = document.createElement('form');
        bankForm.method = 'POST';
        bankForm.action = json.bank_gateway_url;

        const fields = {
            merchant_id: json.merchant_id,
            order_id: json.order_number,
            amount: json.amount,
            currency: json.currency,
            return_url: json.return_url,
            callback_url: json.callback_url,
            hash: json.hash
        };

        Object.keys(fields).forEach(function (key) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key];
            bankForm.appendChild(input);
        });

        document.body.appendChild(bankForm);
        bankForm.submit();
    }).catch(function () {
        alert('Network error creating order');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Continue';
        }
    });
});
</script>
