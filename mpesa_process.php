<?php
// Safaricom Daraja API Credentials
$consumerKey = 'kvhNgfy78Ze7ccdCJeDA446vQdq8sa1U9ToD3eoj1VQHV9KU'; 
$consumerSecret = 'UifCLQCB30AvDzmBUXvOdpGxc5C0GThfmOpE9NM9cldkbg663W0Y2OlvUDniNYSH'; 
$shortcode = '174379'; 
$passkey = 'YOUR_PASSKEY';
$callbackURL = 'https://yourdomain.com/mpesa_callback.php'; 

// Get item details from URL parameters
$itemID = $_GET['item_id'];
$itemName = $_GET['item_name'];
$itemPrice = $_GET['item_price'];

// User's Phone Number (Replace with session-stored phone number or input form)
$phoneNumber = '2547XXXXXXXX';

// Get Access Token
$authURL = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
$credentials = base64_encode("$consumerKey:$consumerSecret");

$ch = curl_init($authURL);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$response = json_decode(curl_exec($ch));
$accessToken = $response->access_token;
curl_close($ch);  

// Generate Timestamp and Password
$timestamp = date('YmdHis');
$password = base64_encode($shortcode . $passkey . $timestamp);

// STK Push Request
$stkURL = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";
$stkHeader = ["Content-Type:application/json", "Authorization:Bearer $accessToken"];

$requestData = [
 "BusinessShortCode": "174379",    
   "Password": "MTc0Mzc5YmZiMjc5ZjlhYTliZGJjZjE1OGU5N2RkNzFhNDY3Y2QyZTBjODkzMDU5YjEwZjc4ZTZiNzJhZGExZWQyYzkxOTIwMTYwMjE2MTY1NjI3",    
   "Timestamp":"20160216165627",    
   "TransactionType": "CustomerPayBillOnline",    
   "Amount": "1",    
   "PartyA":"254708374149",    
   "PartyB":"174379",    
   "PhoneNumber":"254708374149",    
   "CallBackURL": "https://mydomain.com/pat",    
   "AccountReference":"Test",    
   "TransactionDesc":"Test"
];

$ch = curl_init($stkURL);
curl_setopt($ch, CURLOPT_HTTPHEADER, $stkHeader);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

// Redirect back to checkout with response
if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
    echo "STK Push sent to $phoneNumber. Enter your M-Pesa PIN to complete payment.";
} else {
    echo "Error: " . $response['errorMessage'];
}
?>
