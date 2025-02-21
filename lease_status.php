<?php
session_start();
include 'config.php';
require 'vendor/autoload.php'; // Ensure you have the PHP QR Code library
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to view this page!");
}

// Get lease ID from URL
$lease_id = $_GET['lease_id'] ?? null;
if (!$lease_id || !is_numeric($lease_id)) {
    die("Invalid lease selection.");
}

// Fetch lease details
$query = $conn->prepare("SELECT id, user_id, item_id, start_date, End_Date, Total_Cost, security_deposit, payment_method, status, lease_months, lease_days, payment_status FROM leases WHERE id = ?");
$query->bind_param("i", $lease_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $lease = $result->fetch_assoc();
} else {
    die("Lease not found.");
}
$query->close();

// Fetch item details using item_id
$item_query = $conn->prepare("SELECT name, price FROM items WHERE id = ?");
$item_query->bind_param("i", $lease['item_id']);
$item_query->execute();
$item_result = $item_query->get_result();
$item = $item_result->fetch_assoc();
$item_query->close();

// Generate QR Code with lease details
$qrData = "Lease ID: {$lease['id']}\nItem: {$item['name']}\nStart: {$lease['start_date']}\nDuration: {$lease['lease_months']} months, {$lease['lease_days']} days\nStatus: {$lease['payment_status']}";
$qrCode = QrCode::create($qrData)->setSize(150);
$writer = new PngWriter();
$qrOutput = $writer->write($qrCode)->getString();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lease Status</title>
    <link rel="stylesheet" href="layout/css/front.css">
    <style>
        .receipt-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 2px solid #4CAF50;
            border-radius: 10px;
            background-color: #f9f9f9;
            text-align: center;
        }
        .receipt-container img {
            width: 150px;
            height: 150px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <h2>Lease Receipt</h2>
        <p><strong>Item:</strong> <?= htmlspecialchars($item['name']) ?></p>
        <p><strong>Lease Start:</strong> <?= htmlspecialchars($lease['start_date']) ?></p>
        <p><strong>Duration:</strong> <?= htmlspecialchars($lease['lease_months']) ?> months, <?= htmlspecialchars($lease['lease_days']) ?> days</p>
        <p><strong>Status:</strong> <?= htmlspecialchars($lease['payment_status']) ?></p>
        <p><strong>Total Cost:</strong> KES <?= number_format($lease['Total_Cost'], 2) ?></p>
        <h3>Scan for Lease Details</h3>
        <img src="data:image/png;base64,<?= base64_encode($qrOutput) ?>" alt="QR Code">
    </div>
</body>
</html>
