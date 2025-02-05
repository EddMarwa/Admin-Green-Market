<?php
session_start();
include 'config.php'; // Ensure this file exists

// Check if item_id is provided in the URL
if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];
    $item_name = $_GET['item_name'];
    $item_price = $_GET['item_price'];
} else {
    echo "No product selected.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="checkout-container">
        <h2>Checkout</h2>
        <div class="product-summary">
            <h3><?= htmlspecialchars($item_name) ?></h3>
            <p>Price: KES <?= htmlspecialchars($item_price) ?></p>
        </div>
        
        <form action="process_payment.php" method="POST">
            <input type="hidden" name="item_id" value="<?= htmlspecialchars($item_id) ?>">
            <label for="phone">Enter M-Pesa Phone Number:</label>
            <input type="text" name="phone" required placeholder="07XXXXXXXX">
            <button type="submit" class="btn btn-success">Confirm & Pay</button>
        </form>
    </div>
</body>
</html>
