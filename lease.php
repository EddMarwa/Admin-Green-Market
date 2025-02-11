<?php
session_start();
include 'config.php';

// Fetch product details
$item_id = $_GET['item_id'] ?? null;
if (!$item_id || !is_numeric($item_id)) {
    die("Invalid product selection.");
}

$query = $conn->prepare("SELECT Name, Price, Image FROM items WHERE Item_ID = ?");
$query->bind_param("i", $item_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    die("Product not found.");
}
$query->close();

// Check if image exists, else use default placeholder
$imagePath = 'images/' . ($product['Image'] ?: 'img.jpg');
if (!file_exists($imagePath) || empty($product['Image'])) {
    $imagePath = 'images/img.jpg'; // Fallback image
}

// Calculate initial security deposit (50% of price)
$securityDeposit = $product['Price'] * 0.5;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lease <?= htmlspecialchars($product['Name']) ?></title>
    <link rel="stylesheet" href="layout/css/front.css">
</head>
<body>

<div class="lease-container">
    <h2>Lease <?= htmlspecialchars($product['Name']) ?></h2>
    
    <div class="lease-summary">
        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['Name']) ?>">
        <p>Price: <strong>KES <span id="item_price" data-price="<?= $product['Price'] ?>"><?= number_format($product['Price'], 2) ?></span></strong> per month</p>
<input type="hidden" id="hidden_price" value="<?= $product['Price'] ?>">


    </div>

    <form action="process_lease.php" method="POST">
        <input type="hidden" name="item_id" value="<?= $item_id ?>">
        <input type="hidden" id="item_price" value="<?= $product['Price'] ?>">

        <div class="form-group">
            <label>Lease Duration:</label>
            <div class="lease-options">
                <input type="number" id="lease_months" name="lease_months" min="0" max="24" placeholder="Months">
                <input type="number" id="lease_days" name="lease_days" min="0" max="30" placeholder="Days">
            </div>
        </div>

        <div class="form-group">
            <label for="start_date">Lease Start Date:</label>
            <input type="date" name="start_date" id="start_date" required>
        </div>

        <div class="form-group">
            <label for="security_deposit">Security Deposit (Refundable):</label>
            <input type="number" name="security_deposit" id="security_deposit" value="<?= $securityDeposit ?>" readonly>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="mpesa">M-Pesa</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="credit_card">Credit Card</option>
            </select>
        </div>

        <p>Total Estimated Cost: <span class="total-cost" id="total_cost">KES 0.00</span></p>

        <div class="form-group">
            <label>
                <input type="checkbox" name="agree_terms" required> I agree to the lease terms and conditions.
            </label>
        </div>

        <button type="submit" class="lease-btn">Confirm Lease</button>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let leaseMonths = document.getElementById('lease_months');
        let leaseDays = document.getElementById('lease_days');
        let totalCostDisplay = document.getElementById('total_cost');
        let securityDepositInput = document.getElementById('security_deposit');

        let productPrice = <?= $product['Price'] ?>; // Directly fetch product price from PHP
        let pricePerDay = productPrice / 30; // Calculate daily price

        function updateCosts() {
    let months = parseInt(leaseMonths.value) || 0;
    let days = parseInt(leaseDays.value) || 0;

    // Prevent lease duration from being zero
    if (months === 0 && days === 0) {
        leaseDays.value = 1; // Default to 1 day
        days = 1;
    }

    let totalCost = (months * productPrice) + (days * pricePerDay);
    let securityDeposit = totalCost * 0.5; // Dynamic deposit

    totalCostDisplay.textContent = "KES " + totalCost.toFixed(2);
    securityDepositInput.value = securityDeposit.toFixed(2);
}


        // Trigger updates when user inputs values
        leaseMonths.addEventListener('input', updateCosts);
        leaseDays.addEventListener('input', updateCosts);

        // Initialize cost on page load
        updateCosts();
    });
</script>
</body>
</html>
