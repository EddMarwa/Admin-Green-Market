<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to view this receipt!");
}
$user_id = $_SESSION['uid'];

// Get Transaction ID
$txn_id = $_GET['txn_id'] ?? '';

if (!$txn_id) {
    die("Error: Invalid Transaction!");
}

// Fetch Transaction Details
$query = $conn->prepare("SELECT * FROM transactions WHERE mpesa_receipt_number = ? AND user_id = ?");
$query->bind_param("si", $txn_id, $user_id);
$query->execute();
$result = $query->get_result();
$transaction = $result->fetch_assoc();

if (!$transaction) {
    die("Error: Transaction not found!");
}

// Set receipt data
$amount = number_format($transaction['amount'], 2);
$mpesa_receipt_number = htmlspecialchars($transaction['mpesa_receipt_number']);
$transaction_date = htmlspecialchars($transaction['transaction_date']);
$phone = htmlspecialchars($transaction['phone']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link rel="stylesheet" href="layout/css/front.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        .receipt-container {
            width: 60%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        .receipt-header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .receipt-info {
            text-align: left;
            font-size: 16px;
        }
        .btn-download {
            margin-top: 20px;
            display: inline-block;
            background: green;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="receipt-header">Payment Receipt</div>
    <hr>
    <div class="receipt-info">
        <p><strong>Transaction ID:</strong> <?= $mpesa_receipt_number ?></p>
        <p><strong>Phone Number:</strong> <?= $phone ?></p>
        <p><strong>Amount Paid:</strong> KES <?= $amount ?></p>
        <p><strong>Payment Date:</strong> <?= $transaction_date ?></p>
        <p><strong>Status:</strong> <span style="color:green; font-weight:bold;">Success</span></p>
    </div>
    
    <a href="generate_receipt.php?txn_id=<?= $mpesa_receipt_number ?>" class="btn-download">Download PDF</a>
</div>

</body>
</html>
