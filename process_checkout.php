<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to proceed.");
}

$user_id = $_SESSION['uid'];
$item_id = $_POST['item_id'] ?? null;
$item_name = $_POST['item_name'] ?? null;
$item_price = $_POST['item_price'] ?? null;
$phone = $_POST['phone'] ?? null;
$isLease = ($_POST['lease'] ?? 'no') === 'yes';
$leaseDuration = $_POST['lease_duration'] ?? null;

// Validate input
if (!$item_id || !$item_price || !$phone) {
    die("Error: Missing required fields.");
}

// Convert phone number to correct format
$phone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($phone) != 10 || substr($phone, 0, 1) !== '0') {
    die("Error: Invalid phone number format.");
}

// Start Payment Process (Simulated for now)
$transactionID = uniqid('TXN_'); // Generate a unique transaction ID
$paymentStatus = "Pending"; // Assume pending until processed

// Insert transaction record
$stmt = $conn->prepare("INSERT INTO transactions (User_ID, Item_ID, Amount, Phone, Transaction_ID, Payment_Status) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iidsss", $user_id, $item_id, $item_price, $phone, $transactionID, $paymentStatus);

if ($stmt->execute()) {
    // Redirect to confirmation page
    header("Location: payment_confirmation.php?txn_id=$transactionID&status=success");
} else {
    echo "Error: Payment could not be processed.";
}

$stmt->close();
$conn->close();
?>
