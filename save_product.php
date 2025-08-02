<?php
include 'server.php';

$name       = $_POST['name'];
$SKU        = $_POST['SKU'];
$cat_id     = $_POST['cat_id'];
$Price      = $_POST['Price'];
$quantity   = $_POST['quantity'];
$commission = $_POST['commission'];
$off_cash   = $_POST['off_cash'];
$Description= $_POST['Description'];
$status     = $_POST['status'];

if (isset($_POST['save'])) {
    $sql = "INSERT INTO products (name, SKU, cat_id, Price, quantity, commission, off_cash, Description, status, created_at, updated_at)
            VALUES ('$name', '$SKU', '$cat_id', '$Price', '$quantity', '$commission', '$off_cash', '$Description', '$status', NOW(), NOW())";
    if (!$conn->query($sql)) {
        error_log("Database Error [SAVE]: " . $conn->error);
    }
} elseif (isset($_POST['update'])) {
    $id = $_POST['id'];
    $sql = "UPDATE products SET 
            name='$name', SKU='$SKU', cat_id='$cat_id', Price='$Price', quantity='$quantity',
            commission='$commission', off_cash='$off_cash', Description='$Description',
            status='$status', updated_at=NOW() WHERE id=$id";
if (!$conn->query($sql)) {
        error_log("Database Error [update]: " . $conn->error);
    }
}
header("Location: products.php");
