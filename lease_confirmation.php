<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to view this page.");
}

$user_id = $_SESSION['uid'];

// Fetch the most recent lease for the logged-in user
$query = $conn->prepare("
    SELECT l.*, i.Name AS Item_Name, i.Image, i.Price 
    FROM leases l 
    JOIN items i ON l.Item_ID = i.Item_ID 
    WHERE l.User_ID = ? 
    ORDER BY l.id DESC LIMIT 1");

$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    die("No lease found.");
}

$lease = $result->fetch_assoc();
$query->close();
$conn->close();

// Use default image if none is found
$imagePath = 'images/' . ($lease['Image'] ?: 'img.jpg');
if (!file_exists($imagePath) || empty($lease['Image'])) {
    $imagePath = 'images/img.jpg';
}

// Extract lease details with fallback values
$item_id = $lease['Item_ID'] ?? null;
$item_name = $lease['Item_Name'] ?? 'N/A';
$item_price = isset($lease['Price']) ? floatval($lease['Price']) : 0; // Ensure it's a float
$leaseMonths = isset($lease['Lease_Months']) ? intval($lease['Lease_Months']) : 0;
$leaseDays = isset($lease['Lease_Days']) ? intval($lease['Lease_Days']) : 0;
$startDate = $lease['start_date'] ?? 'N/A';
$endDate = $lease['End_Date'] ?? 'N/A';
$paymentMethod = $lease['payment_method'] ?? 'N/A';
$status = $lease['status'] ?? 'Pending';

// Convert months to days (assuming 30 days per month)
$totalLeaseDays = ($leaseMonths * 30) + $leaseDays;

// Ensure totalLeaseDays is valid
if ($totalLeaseDays <= 0) {
    $totalLeaseDays = 1; // Default to 1 day to prevent division errors
}

// Lease Price Calculation (Daily Rate: 3.33% of item price)
$dailyRate = 0.0333 * $item_price;
$leaseCost = $totalLeaseDays * $dailyRate;

// Security Deposit (50% of the item price)
$securityDeposit = 0.5 * $item_price;

// Total Cost (Lease Cost + Security Deposit)
$totalCost = $leaseCost + $securityDeposit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lease Confirmation</title>
    <link rel="stylesheet" href="layout/css/front.css">
</head>
<body>

<div class="confirmation-container">
    <h2>Lease Confirmation</h2>
    
    <div class="lease-summary">
        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($item_name) ?>">
        <p><strong>Item:</strong> <?= htmlspecialchars($item_name) ?></p>
        <p><strong>Start Date:</strong> <?= htmlspecialchars($startDate) ?></p>
        <p><strong>End Date:</strong> <?= htmlspecialchars($endDate) ?></p>
        <p><strong>Lease Duration:</strong> <?= $leaseMonths ?> months, <?= $leaseDays ?> days (<?= $totalLeaseDays ?> days)</p>
        <p><strong>Estimated Lease Cost:</strong> KES <?= number_format($leaseCost, 2) ?></p>
        <p><strong>Security Deposit:</strong> KES <?= number_format($securityDeposit, 2) ?> (Refundable)</p>
        <p><strong>Total Cost:</strong> KES <?= number_format($totalCost, 2) ?></p>
        <p><strong>Payment Method:</strong> <?= ucfirst($paymentMethod) ?></p>
        <p><strong>Status:</strong> <span class="lease-status"><?= htmlspecialchars($status) ?></span></p>
    </div>

    <a href="checkout.php?item_id=<?= urlencode($item_id) ?>&item_name=<?= urlencode($item_name) ?>&item_price=<?= urlencode($item_price) ?>&lease=yes&lease_duration=<?= urlencode($totalLeaseDays) ?>" class="btn">Proceed To Pay</a>
</div>

<style>
    /* General Page Styles */
    body {
        font-family: 'Roboto', sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    /* Main Container */
    .confirmation-container {
        background: #ffffff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        width: 90%;
        max-width: 500px;
        text-align: center;
    }

    /* Heading */
    .confirmation-container h2 {
        color: #333;
        font-size: 24px;
        margin-bottom: 15px;
    }

    /* Lease Summary Section */
    .lease-summary {
        text-align: left;
        margin-bottom: 20px;
    }

    /* Lease Item Image */
    .lease-summary img {
        width: 100%;
        max-height: 250px;
        object-fit: cover;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    /* Lease Details */
    .lease-summary p {
        font-size: 16px;
        color: #555;
        margin: 8px 0;
    }

    /* Highlighted Cost Details */
    .lease-summary p strong {
        color: #222;
    }

    /* Lease Status */
    .lease-status {
        font-weight: bold;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
    }

    /* Pending Status */
    .lease-status {
        background-color: #ffcc00;
        color: #333;
    }

    /* Paid Status */
    .lease-status.paid {
        background-color: #28a745;
        color: white;
    }

    /* Unpaid Status */
    .lease-status.unpaid {
        background-color: #dc3545;
        color: white;
    }

    /* Proceed to Pay Button */
    .btn {
        display: block;
        width: 100%;
        background-color: #28a745;
        color: white;
        padding: 12px;
        text-align: center;
        border-radius: 5px;
        text-decoration: none;
        font-size: 16px;
        font-weight: 500;
        transition: background 0.3s ease-in-out;
    }

    .btn:hover {
        background-color: #218838;
    }
</style>

</body>
</html>
