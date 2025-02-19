<?php
session_start();
include 'config.php';
$shortCode = '174379'; // Test Paybill Number


// M-Pesa API credentials
$consumerKey = "kvhNgfy78Ze7ccdCJeDA446vQdq8sa1U9ToD3eoj1VQHV9KU";  // Replace with actual Consumer Key
$consumerSecret = "UifCLQCB30AvDzmBUXvOdpGxc5C0GThfmOpE9NM9cldkbg663W0Y2OlvUDniNYSH";  // Replace with actual Consumer Secret
$shortcode = "174379"; // Test Paybill Number
$passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919"; 
$callbackUrl = 'https://your-ngrok-url.com/stk_callback.php'; // Use your actual ngrok domain

// Validate user session
if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to make a payment.");
}

// Retrieve payment details
$lease_id = $_GET['lease_id'] ?? null;
$amount = $_GET['amount'] ?? null;
$phone = $_GET['phone'] ?? '';

if (!$lease_id || !$amount || !is_numeric($amount)) {
    die("Invalid payment request.");
}

// Validate phone number format
if (!preg_match('/^2547\d{8}$/', $phone)) {
    die("Error: Invalid Safaricom phone number.");
}

// Generate timestamp & password
$timestamp = date('YmdHis');
$password = base64_encode($shortCode . $passkey . $timestamp);

// Get M-Pesa access token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode("$consumerKey:$consumerSecret"),
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch));
curl_close($ch);

if (!isset($response->access_token)) {
    error_log("M-Pesa Token Error: " . json_encode($response));
    die("Failed to obtain M-Pesa access token.");
}

$access_token = $response->access_token;

// Prepare STK push request
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
    "TransactionDesc" => "Lease payment for Lease ID: $lease_id"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token",
    "Content-Type: application/json"
]);
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
