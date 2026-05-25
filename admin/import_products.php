<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once 'auth.php';

ensureStoreSchema($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    setFlash('import_status', 'Invalid import request. Please try again.');
    redirect('index.php');
}

$basePath = realpath(__DIR__ . '/../assets/img');
if (!$basePath) {
    die('Image folder not found.');
}

$extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$images = [];
foreach ($extensions as $ext) {
    foreach (glob($basePath . '/*.' . $ext, GLOB_BRACE) as $file) {
        $images[] = $file;
    }
}

sort($images);

$imported = 0;
$skipped = 0;

foreach ($images as $imagePath) {
    $filename = basename($imagePath);
    if (!preg_match('/bouquet/i', $filename)) {
        continue;
    }

    $relativePath = 'assets/img/' . $filename;
    $stmt = $pdo->prepare('SELECT 1 FROM products WHERE image = ?');
    $stmt->execute([$relativePath]);
    if ($stmt->fetch()) {
        $skipped++;
        continue;
    }

    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = preg_replace('/^\d+_*/', '', $name);
    $name = preg_replace('/[-_]+/', ' ', $name);
    $name = preg_replace('/\s+/', ' ', trim($name));
    $name = ucwords(strtolower($name));

    if (stripos($name, 'Bouquet') === false) {
        $name = 'Bouquet ' . $name;
    }

    $description = 'Fresh bouquet featuring ' . strtolower($name) . '. Perfect for special occasions and memorable gifting.';
    $price = 29.99 + (abs(crc32($filename)) % 100) * 0.99;
    $price = number_format(min(149.99, max(29.99, $price)), 2, '.', '');

    $stock = 8 + (abs(crc32($filename)) % 18);
    $insert = $pdo->prepare('INSERT INTO products (name, category, description, price, image, stock) VALUES (?, ?, ?, ?, ?, ?)');
    $insert->execute([$name, 'Bouquets', $description, $price, $relativePath, $stock]);
    $imported++;
}

setFlash('import_status', "Imported {$imported} bouquet product(s), skipped {$skipped} already existing.");
redirect('index.php');
