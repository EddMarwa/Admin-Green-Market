<?php
session_start();
include 'config.php';

$lease_id = $_GET['lease_id'] ?? null;
$amount = $_GET['amount'] ?? null;

if (!$lease_id || !$amount) {
    die("Invalid payment request.");
}

// Safaricom M-Pesa credentials
$consumerKey = 'YOUR_CONSUMER_KEY';
$consumerSecret = 'YOUR_CONSUMER_SECRET';
$shortCode = 'YOUR_SHORTCODE';
$passkey = 'YOUR_PASSKEY';
$callbackUrl = 'https://yourwebsite.com/stk_callback.php';
$timestamp = date('YmdHis');
$password = base64_encode($shortCode . $passkey . $timestamp);

// Get user phone number
$query = $conn->prepare("SELECT phone FROM users WHERE id = ?");
$query->bind_param("i", $_SESSION['uid']);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$query->close();

if (!$user) {
    die("User not found.");
}

$phone = $user['phone'];
$phone = preg_replace('/^0/', '254', $phone); // Convert 07xx to 2547xx

// Get M-Pesa access token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode("$consumerKey:$consumerSecret")]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch));
curl_close($ch);

if (!isset($response->access_token)) {
    die("Failed to obtain access token.");
}

$access_token = $response->access_token;

// Initiate STK push
$stkData = [
    "BusinessShortCode" => $shortCode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => $amount,
    "PartyA" => $phone,
    "PartyB" => $shortCode,
    "PhoneNumber" => $phone,
    "CallBackURL" => $callbackUrl,
    "AccountReference" => "Lease Payment",
    "TransactionDesc" => "Lease payment for Item ID: $lease_id"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token", "Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch));
curl_close($ch);

if (isset($response->CheckoutRequestID)) {
    $_SESSION['checkout_request_id'] = $response->CheckoutRequestID;
    echo "<script>alert('STK Push Sent. Enter your M-Pesa PIN to complete payment.'); window.location.href='lease_status.php?lease_id=$lease_id';</script>";
} else {
    die("STK Push failed.");
}
?>
