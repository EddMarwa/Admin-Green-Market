<?php
session_start();
include 'config.php'; // Ensure this file exists

// Check if item_id is provided in the URL
if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];
    $item_name = $_GET['item_name'];
    $item_price = $_GET['item_price'];

    // Check if this is a lease transaction
    $isLease = isset($_GET['lease']) && $_GET['lease'] == 'yes';
    $leaseDuration = $isLease ? $_GET['lease_duration'] : null;

    // Calculate lease price if leasing (Example: 10% of price per week)
    if ($isLease) {
        $dailyRate = 0.0333 * $item_price;
        $weeklyRate = 0.1 * $item_price;
        $leasePrice = $leaseDuration * $dailyRate;
    } else {
        $leasePrice = $item_price; // Standard price for purchase
    }
} else {
    echo "No product selected.";
    exit;
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
        <p>
            Price: KES <?= htmlspecialchars($leasePrice) ?> 
            <?php if ($isLease): ?>
                <br>Lease Duration: <?= htmlspecialchars($leaseDuration) ?> days
            <?php endif; ?>
        </p>
        <img src="images/<?= $item_id ?>.jpg" alt="Product Image">
    </div>

    <form action="process_checkout.php" method="POST">
        <div class="form-group">
            <label for="phone">Enter M-Pesa Phone Number:</label>
            <input type="text" name="phone" required placeholder="07XXXXXXXX" pattern="[0-9]{10}">
        </div>

        <input type="hidden" name="item_id" value="<?= htmlspecialchars($item_id) ?>">
        <input type="hidden" name="item_name" value="<?= htmlspecialchars($item_name) ?>">
        <input type="hidden" name="item_price" value="<?= htmlspecialchars($leasePrice) ?>">
        <input type="hidden" name="lease" value="<?= $isLease ? 'yes' : 'no' ?>">
        <input type="hidden" name="lease_duration" value="<?= htmlspecialchars($leaseDuration) ?>">

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
