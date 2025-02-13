<?php
session_start();
include 'config.php';
include 'mpesa_api.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phone = $_POST['phone'] ?? '';
    $item_id = $_POST['item_id'] ?? '';
    $item_name = $_POST['item_name'] ?? '';
    $amount = $_POST['item_price'] ?? '';

    if (!$phone || !$item_id || !$amount || !is_numeric($amount)) {
        die("Invalid payment details");
    }

    // Call M-Pesa STK Push
    $response = stkPushRequest($phone, $amount, $item_id, $item_name);

    if ($response['ResponseCode'] == "0") {
        header("Location: order_confirmation.php?success=1&item_id=$item_id");
        exit;
    } else {
        echo "Payment failed: " . $response['errorMessage'];
    }
}
?>
