<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sanitize($value) {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

function formatPrice($price, $currency = '€') {
    return $currency . number_format((float)$price, 2);
}

function ensureStoreSchema(PDO $pdo) {
    static $checked = false;
    if ($checked) {
        return;
    }

    $columnExists = function ($table, $column) use ($pdo) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        return (bool)$stmt->fetch();
    };

    if (!$columnExists('products', 'stock')) {
        $pdo->exec("ALTER TABLE products ADD stock INT NOT NULL DEFAULT 20 AFTER image");
    }

    if (!$columnExists('orders', 'order_number')) {
        $pdo->exec("ALTER TABLE orders ADD order_number VARCHAR(80) NULL UNIQUE AFTER id");
    }
    if (!$columnExists('orders', 'customer_email')) {
        $pdo->exec("ALTER TABLE orders ADD customer_email VARCHAR(255) NULL AFTER customer_name");
    }
    if (!$columnExists('orders', 'city')) {
        $pdo->exec("ALTER TABLE orders ADD city VARCHAR(120) NULL AFTER address");
    }
    if (!$columnExists('orders', 'payment_status')) {
        $pdo->exec("ALTER TABLE orders ADD payment_status VARCHAR(50) NOT NULL DEFAULT 'pending' AFTER payment_method");
    }

    try {
        $pdo->exec("ALTER TABLE orders MODIFY delivery_option VARCHAR(100) NOT NULL DEFAULT 'standard'");
    } catch (Exception $e) {
        // Existing databases may already have a compatible definition.
    }

    $checked = true;
}

function stockLabel($stock) {
    $stock = (int)$stock;
    if ($stock <= 0) {
        return 'Out of stock';
    }
    if ($stock <= 5) {
        return 'Only ' . $stock . ' left';
    }
    return 'In stock';
}

function addProductToCart(PDO $pdo, $productId, $quantity) {
    ensureStoreSchema($pdo);

    $productId = (int)$productId;
    $quantity = max(1, (int)$quantity);
    if ($productId <= 0) {
        return ['success' => false, 'message' => 'Invalid product selected.'];
    }

    $stmt = $pdo->prepare('SELECT id, name, stock FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        return ['success' => false, 'message' => 'Product not found.'];
    }

    $stock = (int)$product['stock'];
    if ($stock <= 0) {
        return ['success' => false, 'message' => $product['name'] . ' is currently out of stock.'];
    }

    $currentQty = (int)($_SESSION['cart'][$productId] ?? 0);
    $available = $stock - $currentQty;
    if ($available <= 0) {
        return ['success' => false, 'message' => 'You already have all available stock for ' . $product['name'] . ' in your cart.'];
    }

    $addedQty = min($quantity, $available);
    $_SESSION['cart'][$productId] = $currentQty + $addedQty;

    if ($addedQty < $quantity) {
        return ['success' => true, 'message' => 'Only ' . $addedQty . ' more item(s) were available for ' . $product['name'] . '.'];
    }

    return ['success' => true, 'message' => $product['name'] . ' added to cart.'];
}

function normalizeCart(PDO $pdo) {
    ensureStoreSchema($pdo);

    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        return [];
    }

    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_UNIQUE);

    $messages = [];
    foreach ($cart as $productId => $quantity) {
        $productId = (int)$productId;
        $quantity = max(1, (int)$quantity);

        if (empty($products[$productId])) {
            unset($_SESSION['cart'][$productId]);
            $messages[] = 'An unavailable product was removed from your cart.';
            continue;
        }

        $stock = (int)$products[$productId]['stock'];
        if ($stock <= 0) {
            unset($_SESSION['cart'][$productId]);
            $messages[] = $products[$productId]['name'] . ' is out of stock and was removed from your cart.';
            continue;
        }

        if ($quantity > $stock) {
            $_SESSION['cart'][$productId] = $stock;
            $messages[] = $products[$productId]['name'] . ' quantity was reduced to available stock.';
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }

    return array_unique($messages);
}

function getCartCount() {
    return array_sum($_SESSION['cart'] ?? []);
}

function getCartItems(PDO $pdo) {
    ensureStoreSchema($pdo);
    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        return [];
    }

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders) ORDER BY created_at DESC");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $items = [];
    foreach ($products as $product) {
        if ((int)$product['stock'] <= 0) {
            continue;
        }
        $product['quantity'] = max(1, min((int)$product['stock'], (int)($cart[$product['id']] ?? 1)));
        $product['subtotal'] = $product['quantity'] * (float)$product['price'];
        $items[] = $product;
    }

    return $items;
}

function getCartTotal(PDO $pdo) {
    $total = 0;
    foreach (getCartItems($pdo) as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

function getFlash($key) {
    if (!empty($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// CSRF helpers
function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $stored = $_SESSION['csrf_token'] ?? '';
    if (!is_string($token) || !is_string($stored)) return false;
    return hash_equals($stored, $token);
}

// Idempotency helpers (simple session-based)
function create_idempotency_token() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $t = bin2hex(random_bytes(16));
    $_SESSION['idempotency_tokens'][$t] = false;
    return $t;
}

function is_idempotency_token_used($t) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['idempotency_tokens'][$t]);
}

function mark_idempotency_token_used($t) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['idempotency_tokens'][$t] = true;
}
?>
