<?php
session_start();
include 'config.php'; // Ensure this file exists

// Validate and fetch required parameters
$item_id = $_GET['item_id'] ?? null;
$item_name = $_GET['item_name'] ?? null;
$item_price = $_GET['item_price'] ?? null;
$isLease = isset($_GET['lease']) && $_GET['lease'] === 'yes';
$leaseDuration = $_GET['lease_duration'] ?? null;

// Validate required parameters
if (!$item_id || !$item_name || !$item_price || (!is_numeric($item_price) || $item_price <= 0)) {
    die("Error: Invalid product details.");
}

// Validate lease duration if leasing
if ($isLease) {
    if (!is_numeric($leaseDuration) || $leaseDuration <= 0) {
        die("Error: Invalid lease duration.");
    }

    // Lease price calculation
    $dailyRate = 0.0333 * $item_price;  // 3.33% per day
    $leasePrice = $leaseDuration * $dailyRate;
    $securityDeposit = 0.5 * $leasePrice; // 50% security deposit
} else {
    $leasePrice = $item_price; // Standard purchase price
    $securityDeposit = 0; // No deposit for purchases
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="layout/css/front.css">
</head>
<body>

<div class="checkout-container">
    <h2>Checkout</h2>

    <div class="product-summary">
        <h3><?= htmlspecialchars($item_name) ?></h3>
        <p><strong>Price:</strong> KES <?= number_format($leasePrice, 2) ?></p>

        <?php if ($isLease): ?>
            <p><strong>Lease Duration:</strong> <?= htmlspecialchars($leaseDuration) ?> days</p>
            <p><strong>Security Deposit:</strong> KES <?= number_format($securityDeposit, 2) ?> (Refundable)</p>
        <?php endif; ?>

        <img src="images/<?= htmlspecialchars($item_name) ?>.jpg" alt="Product Image">
    </div>

    <form action="process_checkout.php" method="POST">
        <div class="form-group">
            <label for="phone">Enter M-Pesa Phone Number:</label>
            <input type="text" name="phone" required placeholder="07XXXXXXXX" pattern="[0-9]{10}">
        </div>

        <!-- Hidden Inputs for Processing -->
        <input type="hidden" name="item_id" value="<?= htmlspecialchars($item_id) ?>">
        <input type="hidden" name="item_name" value="<?= htmlspecialchars($item_name) ?>">
        <input type="hidden" name="item_price" value="<?= htmlspecialchars($leasePrice) ?>">
        <input type="hidden" name="lease" value="<?= $isLease ? 'yes' : 'no' ?>">
        <input type="hidden" name="lease_duration" value="<?= htmlspecialchars($leaseDuration) ?>">
        <input type="hidden" name="security_deposit" value="<?= htmlspecialchars($securityDeposit) ?>">

        <div class="form-group">
            <button type="submit" class="btn">Confirm & Pay</button>
        </div>
    </form>
</div>

<div class="footer">
    <p>Powered by Green Market &copy; 2025</p>
</div>

</body>
</html>
