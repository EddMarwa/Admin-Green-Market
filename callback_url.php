<?php
include 'config.php';

// Read JSON response from M-Pesa
$data = file_get_contents("php://input");
$mpesaResponse = json_decode($data, true);

if (!$mpesaResponse) {
    die("Invalid response");
}

// Extract payment details
$merchantRequestId = $mpesaResponse['Body']['stkCallback']['MerchantRequestID'] ?? '';
$checkoutRequestId = $mpesaResponse['Body']['stkCallback']['CheckoutRequestID'] ?? '';
$resultCode = $mpesaResponse['Body']['stkCallback']['ResultCode'] ?? '';
$resultDesc = $mpesaResponse['Body']['stkCallback']['ResultDesc'] ?? '';

// Check if payment was successful
if ($resultCode == 0) {
    $amount = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'] ?? 0;
    $mpesaReceiptNumber = $mpesaResponse['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'] ?? '';
    $transactionDate = date("Y-m-d H:i:s");

    // Get user details (If needed, retrieve user ID from session)
    session_start();
    $user_id = $_SESSION['uid'] ?? null;
    $phone = $_SESSION['phone'] ?? '';

    // Store transaction in database
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, phone, amount, mpesa_receipt_number, status, transaction_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdsss", $user_id, $phone, $amount, $mpesaReceiptNumber, $resultDesc, $transactionDate);
    $stmt->execute();

    // Redirect to Receipt Page
    header("Location: receipt.php?txn_id=$mpesaReceiptNumber");
    exit;
} else {
    // Payment Failed
    die("Payment Failed: $resultDesc");
}
?>
