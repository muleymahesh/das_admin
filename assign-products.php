<?php
include 'server.php';

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
if (!$user_id) {
    echo "Invalid user ID.";
    exit;
}

// Get all product IDs
$products = $conn->query("SELECT id FROM products");
if (!$products || $products->num_rows == 0) {
    echo "No products found.";
    exit;
}

$inserted = 0;
while ($product = $products->fetch_assoc()) {
    $pid = $product['id'];

    // Check if already assigned
    $check = $conn->query("SELECT id FROM user_products WHERE user_id = $user_id AND product_id = $pid");
    if ($check && $check->num_rows == 0) {
        // Assign product with default quantity = 0
        $conn->query("INSERT INTO user_products (user_id, product_id, quantity) VALUES ($user_id, $pid, 0)");
        $inserted++;
    }
}

echo "$inserted products assigned successfully.";
