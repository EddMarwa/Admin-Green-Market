<?php
session_start();
include 'config.php';

if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to proceed.");
}



$user_id = $_SESSION['uid'];
$item_id = $_POST['item_id'] ?? null;
$lease_months = $_POST['lease_months'] ?? 0;
$lease_days = $_POST['lease_days'] ?? 0;
$start_date = $_POST['start_date'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';
$phone = $_POST['phone'] ?? '';

if (!$item_id || !is_numeric($item_id)) {
    die("Invalid lease request.");
}

// Validate phone number format (should be 07XXXXXXXX)
if (!preg_match('/^07\d{8}$/', $phone)) {
    die("Error: Invalid phone number format. Use 07XXXXXXXX.");
}

// Convert phone number to Safaricom format (07XXXXXXXX → 2547XXXXXXXX)
$phone = "254" . substr($phone, 1);

// Calculate total lease cost
$query = $conn->prepare("SELECT Price FROM items WHERE Item_ID = ?");
$query->bind_param("i", $item_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $productPrice = $product['Price'];
} else {
    die("Error: Item not found.");
}

$query->close();

$pricePerDay = $productPrice / 30;
$totalCost = ($lease_months * $productPrice) + ($lease_days * $pricePerDay);
$securityDeposit = $productPrice * 0.5;

if ($payment_method === 'mpesa') {
    header("Location: stk_push.php?lease_id=$item_id&amount=$totalCost&phone=$phone");
    exit();
} else {
    die("Currently, only M-Pesa payments are supported.");
}
?>  
