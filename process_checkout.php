<?php
session_start();

date_default_timezone_set('Africa/Nairobi');

// M-Pesa API credentials
$consumerKey = "kvhNgfy78Ze7ccdCJeDA446vQdq8sa1U9ToD3eoj1VQHV9KU";  // Replace with actual Consumer Key
$consumerSecret = "UifCLQCB30AvDzmBUXvOdpGxc5C0GThfmOpE9NM9cldkbg663W0Y2OlvUDniNYSH";  // Replace with actual Consumer Secret
$shortcode = "174379"; // Test Paybill Number
$passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919"; // Replace with actual Passkey

// Get form data
$phone = $_POST['phone'];
$item_name = $_POST['item_name'];
$item_price = $_POST['item_price'];

// Ensure phone number starts with 254
$phone = preg_replace('/^0/', '254', $phone);

// Generate Timestamp & Password
$timestamp = date('YmdHis');
$password = base64_encode($shortcode . $passkey . $timestamp);

// Get Access Token
$credentials = base64_encode("$consumerKey:$consumerSecret");
$token_url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Basic $credentials"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);
$accessToken = json_decode($response)->access_token;

// STK Push API request
$stkPushUrl = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";
$callbackURL = "https://81b8-105-163-157-45.ngrok-free.app/callback_url.php"; // Replace with your live callback URL

$requestData = [
    "BusinessShortCode" => $shortcode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => $item_price,
    "PartyA" => $phone,
    "PartyB" => $shortcode,
    "PhoneNumber" => $phone,
    "CallBackURL" => $callbackURL,
    "AccountReference" => $item_name,
    "TransactionDesc" => "Payment for $item_name"
];

$ch = curl_init($stkPushUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer $accessToken"));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response);


if (isset($responseData->ResponseCode) && $responseData->ResponseCode == "0") {
    echo "STK Push sent successfully. Check your phone to complete the payment.";
} else {
    echo "Error: " . $responseData->errorMessage;
}
?>
