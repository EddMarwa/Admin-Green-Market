<?php
include 'config.php';

$mpesaResponse = file_get_contents('php://input');
$logFile = 'mpesa_response.log';
file_put_contents($logFile, $mpesaResponse, FILE_APPEND);

$response = json_decode($mpesaResponse, true);
$checkoutRequestID = $response['Body']['stkCallback']['CheckoutRequestID'] ?? null;
$resultCode = $response['Body']['stkCallback']['ResultCode'] ?? null;
$amount = $response['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'] ?? 0;

if ($resultCode == 0) {
    $query = $conn->prepare("UPDATE leases SET payment_status = 'completed' WHERE id = ?");
    $query->bind_param("i", $_SESSION['lease_id']);
    $query->execute();
    $query->close();
} else {
    file_put_contents($logFile, "Payment failed.\n", FILE_APPEND);
}


if ($response['ResponseCode'] == "0") { 
    header("Location: receipt.php?lease_id=" . $lease_id);
    exit();
} else {
    die("STK Push failed. Please try again.");
}
?>
