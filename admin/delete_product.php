<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    setFlash('import_status', 'Invalid form submission. Please try again.');
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('import_status', 'Invalid product id.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    setFlash('import_status', 'Product not found.');
    header('Location: index.php');
    exit;
}

// delete row
$d = $pdo->prepare('DELETE FROM products WHERE id = ?');
$d->execute([$id]);

// remove image file if stored locally
if (!empty($product['image']) && strpos($product['image'], 'assets/img/') === 0) {
    $path = __DIR__ . '/../' . $product['image'];
    if (file_exists($path)) @unlink($path);
}

setFlash('import_status', 'Product deleted.');
header('Location: index.php');
exit;
