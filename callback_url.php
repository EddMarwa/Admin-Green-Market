<?php
header("Content-Type: application/json");

$response = file_get_contents("php://input");
$logFile = "mpesa_response.log";
file_put_contents($logFile, $response, FILE_APPEND);

$responseData = json_decode($response);

if (isset($responseData->Body->stkCallback->ResultCode) && $responseData->Body->stkCallback->ResultCode == 0) {
    $mpesaReceiptNumber = $responseData->Body->stkCallback->CallbackMetadata->Item[1]->Value;
    file_put_contents($logFile, "Payment Successful! Receipt: $mpesaReceiptNumber\n", FILE_APPEND);
}
?>
