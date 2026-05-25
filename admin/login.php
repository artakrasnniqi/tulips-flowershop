<?php
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = require __DIR__ . '/../config/admin.php';
    $user = sanitize($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stored = $config['password'] ?? null;

    // If stored password looks like a hash (starts with $), treat it as hashed
    $isHashed = is_string($stored) && strlen($stored) > 0 && $stored[0] === '$';

    $authenticated = false;
    if ($user === ($config['username'] ?? '')) {
        if ($isHashed) {
            if (password_verify($pass, $stored)) {
                $authenticated = true;
            }
        } else {
            // legacy plaintext password — accept and migrate to hashed storage
            if ($pass === $stored) {
                $authenticated = true;
                // hash and persist for future logins
                $newHash = password_hash($pass, PASSWORD_DEFAULT);
                $out = var_export([
                    'username' => $config['username'],
                    'password' => $newHash,
                ], true);
                $php = "<?php\n// Admin credentials (hashed) - update as needed.\nreturn {$out};\n";
                @file_put_contents(__DIR__ . '/../config/admin.php', $php);
            }
        }
    }

    if ($authenticated) {
        $_SESSION['admin_logged_in'] = true;
        setFlash('import_status', 'Welcome, admin.');
        header('Location: index.php');
        exit;
    }

    $error = 'Invalid credentials.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex align-items-center" style="min-height:100vh;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-3">Admin Login</h4>
                    <?php if ($error): ?><div class="alert alert-danger"><?= sanitize($error) ?></div><?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-primary-soft">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
