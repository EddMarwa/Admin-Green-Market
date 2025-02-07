<?php
$mpesaResponse = file_get_contents('php://input');
$logFile = "mpesa_log.txt";
file_put_contents($logFile, $mpesaResponse, FILE_APPEND);
?>
