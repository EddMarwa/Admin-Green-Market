<?php
session_start();
include 'config.php'; // Ensure database connection is included

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to lease a product.");
}

$user_id = $_SESSION['user_id']; // Get logged-in user ID

// Get form data
$item_id = $_POST['item_id'] ?? null;
$lease_months = (int) ($_POST['lease_months'] ?? 0);
$lease_days = (int) ($_POST['lease_days'] ?? 0);
$start_date = $_POST['start_date'] ?? null;
$security_deposit = $_POST['security_deposit'] ?? 0;
$payment_method = $_POST['payment_method'] ?? null;

// Validate input
if (!$item_id || !$start_date || !$payment_method || ($lease_months == 0 && $lease_days == 0)) {
    die("Invalid lease request. Please ensure all fields are filled correctly.");
}

// Fetch product price
$query = $conn->prepare("SELECT Price FROM items WHERE Item_ID = ?");
$query->bind_param("i", $item_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    die("Product not found.");
}

$product = $result->fetch_assoc();
$product_price = $product['Price'];
$query->close();

// Calculate lease cost
$price_per_day = $product_price / 30;
$total_cost = ($lease_months * $product_price) + ($lease_days * $price_per_day);
$security_deposit = $total_cost * 0.5; // 50% refundable deposit

// Calculate lease end date
$lease_end_date = date('Y-m-d', strtotime("+{$lease_months} months +{$lease_days} days", strtotime($start_date)));

// Insert lease record into the database
$stmt = $conn->prepare("INSERT INTO leases (User_ID, Item_ID, Start_Date, End_Date, Total_Cost, Security_Deposit, Payment_Method, Status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("iissdds", $user_id, $item_id, $start_date, $lease_end_date, $total_cost, $security_deposit, $payment_method);

if ($stmt->execute()) {
    header("Location: lease_confirmation.php?success=1"); // Redirect on success
} else {
    echo "Error: Unable to process lease request.";
}

$stmt->close();
$conn->close();
?>
