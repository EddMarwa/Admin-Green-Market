<?php
session_start();
include 'config.php';

if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to lease an item.");
}

$user_id = $_SESSION['uid'];
$item_id = $_POST['item_id'] ?? null;
$lease_months = $_POST['lease_months'] ?? 0;
$lease_days = $_POST['lease_days'] ?? 0;
$start_date = $_POST['start_date'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;
$security_deposit = $_POST['security_deposit'] ?? 0;

if (!$item_id || !$start_date || !$payment_method) {
    die("Invalid lease request.");
}

// Fetch product price
$query = $conn->prepare("SELECT Price FROM items WHERE Item_ID = ?");
$query->bind_param("i", $item_id);
$query->execute();
$result = $query->get_result();
$product = $result->fetch_assoc();
$query->close();

if (!$product) {
    die("Product not found.");
}

$product_price = $product['Price'];
$total_cost = ($lease_months * $product_price) + ($lease_days * ($product_price / 30)) + $security_deposit;

// Save lease details in the database
$stmt = $conn->prepare("INSERT INTO leases (user_id, item_id, lease_months, lease_days, start_date, total_cost, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("iiiiid", $user_id, $item_id, $lease_months, $lease_days, $start_date, $total_cost);
$stmt->execute();
$lease_id = $stmt->insert_id;
$stmt->close();

// Initiate STK Push for payment
header("Location: stk_push.php?lease_id=$lease_id&amount=$total_cost");
exit;
?>
