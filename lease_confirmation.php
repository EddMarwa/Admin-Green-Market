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
    SELECT l.*, i.Name AS Item_Name, i.Image 
    FROM leases l 
    JOIN items i ON l.Item_ID = i.Item_ID 
    WHERE l.User_ID = ? 
    ORDER BY l.id DESC LIMIT 1");  // Use Lease_ID instead of item_id

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

// Handle missing keys to prevent warnings
$item_id = $lease['Item_ID'] ?? null;
$item_name = $lease['Item_Name'] ?? 'N/A';
$leaseDuration = ($lease['Lease_Months'] ?? 0) . ' months, ' . ($lease['Lease_Days'] ?? 0) . ' days';
$startDate = $lease['start_date'] ?? 'N/A';
$endDate = $lease['End_Date'] ?? 'N/A';
$totalCost = $lease['Total_Cost'] ?? 0;
$securityDeposit = $lease['security_deposit'] ?? 0;
$paymentMethod = $lease['payment_method'] ?? 'N/A';
$status = $lease['status'] ?? 'Pending';

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
        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item_name) ?>">
        <p><strong>Item:</strong> <?= htmlspecialchars($item_name) ?></p>
        <p><strong>Start Date:</strong> <?= htmlspecialchars($startDate) ?></p>
        <p><strong>End Date:</strong> <?= htmlspecialchars($endDate) ?></p>
        <p><strong>Total Cost:</strong> KES <?= number_format($totalCost, 2) ?></p>
        <p><strong>Security Deposit:</strong> KES <?= number_format($securityDeposit, 2) ?> (Refundable)</p>
        <p><strong>Payment Method:</strong> <?= ucfirst($paymentMethod) ?></p>
        <p><strong>Status:</strong> <span class="lease-status"><?= htmlspecialchars($status) ?></span></p>
    </div>

    <a href="checkout.php?item_id=<?= urlencode($item_id) ?>&item_name=<?= urlencode($item_name) ?>&item_price=<?= urlencode($totalCost) ?>&lease=yes&lease_duration=<?= urlencode($leaseDuration) ?>" class="btn">Proceed To Pay</a>

</div>

<style>
/* Green Market Themed Lease Confirmation Page */
.confirmation-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 20px;
    background: #f4f4f4;
    border-radius: 10px;
    text-align: center;
}

.lease-summary {
    margin-top: 20px;
    background: #fff;
    padding: 15px;
    border-radius: 8px;
}

.lease-summary img {
    max-width: 150px;
    border-radius: 5px;
    margin-bottom: 10px;
}

p {
    font-size: 16px;
    color: #333;
    margin: 5px 0;
}

.lease-status {
    font-weight: bold;
    color: #27ae60;
}

.btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: #27ae60;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.btn:hover {
    background: #218c53;
}
</style>

</body>
</html>
