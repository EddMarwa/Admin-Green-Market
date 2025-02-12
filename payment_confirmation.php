<?php
session_start();
include 'config.php';

$transactionID = $_GET['txn_id'] ?? null;
$status = $_GET['status'] ?? 'failed';

if (!$transactionID) {
    die("Error: Invalid transaction.");
}

// Fetch payment details
$stmt = $conn->prepare("SELECT * FROM transactions WHERE Transaction_ID = ?");
$stmt->bind_param("s", $transactionID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Error: Transaction not found.");
}

$transaction = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <link rel="stylesheet" href="layout/css/front.css">
</head>
<body>

<div class="confirmation-container">
    <h2>Payment <?= htmlspecialchars($status) ?></h2>
    <p>Transaction ID: <?= htmlspecialchars($transaction['Transaction_ID']) ?></p>
    <p>Amount Paid: KES <?= number_format($transaction['Amount'], 2) ?></p>
    <p>Phone Number: <?= htmlspecialchars($transaction['Phone']) ?></p>
    <p>Status: <?= htmlspecialchars($transaction['Payment_Status']) ?></p>

    <a href="index.php" class="btn">Return to Home</a>
</div>

</body>
</html>
