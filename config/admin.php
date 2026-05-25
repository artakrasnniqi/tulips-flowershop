<?php
// Admin credentials (development only). Password stored hashed for now.
// Change to secure storage (DB or environment) for production.
return [
    'username' => 'admin',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),
];
