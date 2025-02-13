<?php



function stkPushRequest($phone, $amount, $item_id, $item_name) {
    $consumerKey = "YOUR_CONSUMER_KEY";
    $consumerSecret = "YOUR_CONSUMER_SECRET";
    $shortCode = "YOUR_PAYBILL";
    $passkey = "YOUR_PASSKEY";
    $callbackURL = "https://yourdomain.com/callback.php";

    $timestamp = date("YmdHis");
    $password = base64_encode($shortCode . $passkey . $timestamp);
    
    // Access Token
    $authURL = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
    $credentials = base64_encode("$consumerKey:$consumerSecret");
    $headers = ["Authorization: Basic $credentials"];
    
    $ch = curl_init($authURL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $accessToken = $response['access_token'];
    
    // STK Push Request
    $stkURL = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";
    $headers = ["Authorization: Bearer $accessToken"];
    $payload = [
        "BusinessShortCode" => $shortCode,
        "Password" => $password,
        "Timestamp" => $timestamp,
        "TransactionType" => "CustomerPayBillOnline",
        "Amount" => $amount,
        "PartyA" => $phone,
        "PhoneNumber" => $phone,
        "CallBackURL" => $callbackURL,
        "AccountReference" => $item_id,
        "TransactionDesc" => $item_name
    ];

    $ch = curl_init($stkURL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    return json_decode(curl_exec($ch), true);
}
?>
