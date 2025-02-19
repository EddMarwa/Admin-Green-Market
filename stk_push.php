<?php
session_start();
include 'config.php';

if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to make a payment.");
}

$lease_id = $_GET['lease_id'] ?? null;
$amount = $_GET['amount'] ?? null;

if (!$lease_id || !$amount || !is_numeric($amount)) {
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

// Fetch user phone number securely
$query = $conn->prepare("SELECT phone FROM users WHERE UserID = ?");
$query->bind_param("i", $_SESSION['uid']);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$query->close();

if (!$user || empty($user['phone'])) {
    die("Error: Phone number not found. Please update your profile.");
}

// Format phone number to Safaricom standard (convert 07xx to 2547xx)
$phone = preg_replace('/^0/', '254', trim($user['phone']));

// Get M-Pesa access token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode("$consumerKey:$consumerSecret")]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch));
curl_close($ch);

if (!isset($response->access_token)) {
    error_log("M-Pesa Token Error: " . json_encode($response));
    die("Failed to obtain M-Pesa access token.");
}

$access_token = $response->access_token;

// Prepare STK push request data
$stkData = [
    "BusinessShortCode" => $shortCode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => (int)$amount,
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
    error_log("STK Push Failed: " . json_encode($response));
    die("STK Push failed. Please try again later.");
}
?>
