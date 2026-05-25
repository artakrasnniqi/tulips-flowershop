<?php
return [
    'merchant_id' => 'TULIPS123456',
    'merchant_secret' => 'super_secret_bank_key_2026',
    'currency' => 'EUR',

    // Test URL e bankës (simulim)
    'bank_gateway_url' => 'http://localhost/tulips_flowershop/fake_bank_gateway.php',
    // URL ku kthehet klienti pas pagesës
    'return_url' => 'http://localhost/tulips_flowershop/payment_return.php',

    // URL ku banka dërgon callback server-to-server
    'callback_url' => 'http://localhost/tulips_flowershop/bank_callback.php',
    // Allow insecure (HTTP) gateway for local testing. Set to false in production.
    'allow_insecure' => true,
];

