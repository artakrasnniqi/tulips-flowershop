<?php
require_once 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge-soft">Delivery</span>
            <h1 class="fw-bold mt-3">Fresh flowers, delivered with care</h1>
            <p class="lead text-secondary mx-auto mb-0" style="max-width: 720px;">
                Choose your bouquet, add the delivery details, and we prepare every order so it arrives fresh, neat, and gift-ready.
            </p>
        </div>

        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="info-card p-4 h-100">
                    <div class="feature-icon mx-auto mb-3"><i class="bi bi-truck"></i></div>
                    <h4>Free Local Delivery</h4>
                    <p class="text-secondary mb-0">
                        Local flower orders include free delivery within our standard service area.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-card p-4 h-100">
                    <div class="feature-icon mx-auto mb-3"><i class="bi bi-clock-history"></i></div>
                    <h4>Same-Day Option</h4>
                    <p class="text-secondary mb-0">
                        Place your order early and we will do our best to deliver it the same day.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-card p-4 h-100">
                    <div class="feature-icon mx-auto mb-3"><i class="bi bi-flower2"></i></div>
                    <h4>Freshly Prepared</h4>
                    <p class="text-secondary mb-0">
                        Every bouquet is arranged shortly before dispatch and packed for safe travel.
                    </p>
                </div>
            </div>
        </div>

        <div class="info-card p-4 p-md-5 mt-5">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <span class="badge-soft">How it works</span>
                    <h2 class="section-title mt-2 mb-0">Simple delivery steps</h2>
                </div>
                <a href="products.php" class="btn btn-primary-soft">Shop Flowers</a>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="delivery-step h-100">
                        <span>1</span>
                        <h5>Choose flowers</h5>
                        <p>Select a bouquet or arrangement from the catalog.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="delivery-step h-100">
                        <span>2</span>
                        <h5>Add details</h5>
                        <p>Enter the address, phone number, city, and payment method at checkout.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="delivery-step h-100">
                        <span>3</span>
                        <h5>We deliver</h5>
                        <p>Your order is prepared, packed, and delivered fresh to the recipient.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-lg-6">
                <div class="info-card p-4 h-100">
                    <h4>Delivery Notes</h4>
                    <ul class="text-secondary mb-0 ps-3">
                        <li>Please include a clear address and phone number.</li>
                        <li>Same-day delivery depends on product availability and order time.</li>
                        <li>If a flower is unavailable, we may contact you for a suitable replacement.</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="info-card p-4 h-100">
                    <h4>Need Help?</h4>
                    <p class="text-secondary">
                        For special delivery instructions, events, or urgent orders, send us a message before checkout.
                    </p>
                    <a href="contact.php" class="btn btn-outline-soft">Contact Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>
