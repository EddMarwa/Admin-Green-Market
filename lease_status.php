<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to view this page!!!");
}

$user_id = $_SESSION['uid'];
$item_id = $_GET['item_id'] ?? null;
if (!$item_id || !is_numeric($item_id)) {
    die("Invalid product selection.");
}

// Fetch lease details
$query = $conn->prepare("SELECT i.Name, l.lease_duration, l.start_date, l.End_Date, l.security_deposit, l.Total_Cost, l.payment_method, l.status FROM leases l JOIN items i ON l.item_id = i.Item_ID WHERE l.user_id = ? AND l.item_id = ? LIMIT 1");
$query->bind_param("ii", $user_id, $item_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $lease = $result->fetch_assoc();
} else {
    die("Lease details not found.");
}
$query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lease Status - <?= htmlspecialchars($lease['Name']) ?></title>
    <link rel="stylesheet" href="layout/css/front.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e8f5e9;
            text-align: center;
            padding: 20px;
        }
        .status-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        h2 {
            color: #388e3c;
        }
        p {
            font-size: 18px;
            margin: 10px 0;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #388e3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background-color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <h2>Lease Summary</h2>
        <p><strong>Item:</strong> <?= htmlspecialchars($lease['Name']) ?></p>
        <p><strong>Lease Duration:</strong> <?= $lease['lease_duration'] ?> months</p>
        <p><strong>Start Date:</strong> <?= $lease['start_date'] ?></p>
        <p><strong>End Date:</strong> <?= $lease['End_Date'] ?></p>
        <p><strong>Security Deposit:</strong> KES <?= number_format($lease['security_deposit'], 2) ?></p>
        <p><strong>Total Cost:</strong> KES <?= number_format($lease['Total_Cost'], 2) ?></p>
        <p><strong>Payment Method:</strong> <?= ucfirst($lease['payment_method']) ?></p>
        <p><strong>Status:</strong> <span style="color: <?= ($lease['status'] == 'approved' || $lease['status'] == 'active') ? 'green' : 'red'; ?>; font-weight: bold;"> <?= ucfirst($lease['status']) ?> </span></p>
        <a href="index.php" class="back-btn">Back to Home</a>
    </div>
</body>
</html>
