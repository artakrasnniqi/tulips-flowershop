<?php
require_once 'includes/functions.php';
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = 'Thank you for contacting TULIPS FlowerShop. We will get back to you soon.';
}
include 'includes/header.php';
?>
<section class="py-5">
    <div class="container">
        <div class="mb-4">
            <span class="badge-soft">Contact</span>
            <h1 class="mt-2">We’d love to hear from you</h1>
        </div>

        <?php if ($message): ?><div class="alert alert-success"><?= sanitize($message) ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="contact-card p-4 h-100">
                    <h4>Visit or call us</h4>
                    <p class="text-secondary">Questions about bouquets, weddings, events, or delivery? Reach out anytime.</p>
                    <p><i class="bi bi-geo-alt"></i> 14 Bloom Avenue, Garden District</p>
                    <p><i class="bi bi-telephone"></i> +1 (555) 210-4490</p>
                    <p><i class="bi bi-envelope"></i> hello@tulipsflowershop.com</p>
                    <p class="mb-0"><i class="bi bi-clock"></i> Mon-Sat: 9:00 AM - 7:00 PM</p>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="contact-card p-4">
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message</label>
                            <textarea name="message" rows="5" class="form-control" required></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary-soft">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>