<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to view this page!!!");
}

$user_id = $_SESSION['uid'];
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

$imagePath = 'images/' . ($product['Image'] ?: 'img.jpg');
if (!file_exists($imagePath) || empty($product['Image'])) {
    $imagePath = 'images/img.jpg';
}

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
        <p>Price: <strong>KES <span id="item_price" data-price="<?= $product['Price'] ?>">
            <?= number_format($product['Price'], 2) ?></span></strong> per month</p>
    </div>

    <form action="process_lease.php" method="POST">
        <input type="hidden" name="item_id" value="<?= $item_id ?>">
        <input type="hidden" id="hidden_price" value="<?= $product['Price'] ?>">

        <div class="form-group">
            <label>Lease Duration:</label>
            <div class="lease-options">
                <div>
                    <label>Months</label>
                    <input type="number" id="lease_months" name="lease_months" min="0" max="24" value="0">
                </div>
                <div>
                    <label>Days</label>
                    <input type="number" id="lease_days" name="lease_days" min="0" max="30" value="0">
                </div>
            </div>
        </div>  

        <div class="form-group">
            <label for="start_date">Lease Start Date:</label>
            <input type="date" name="start_date" id="start_date" required min="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
            <label for="security_deposit">Security Deposit (Refundable):</label>
            <input type="text" name="security_deposit" id="security_deposit" value="<?= number_format($securityDeposit, 2) ?>" readonly>
        </div>

        <div class="form-group">
            <label for="total_cost">Total Cost:</label>
            <input type="text" id="total_cost" name="total_cost" readonly>
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="mpesa">M-Pesa</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="credit_card">Credit Card</option>
            </select>
        </div>

        <div class="form-group">
            <label for="phone">Enter M-Pesa Phone Number:</label>
            <input type="text" name="phone" id="phone" placeholder="07XXXXXXXX" required pattern="^07\d{8}$">
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

        let productPrice = parseFloat(document.getElementById('hidden_price').value);
        let pricePerDay = productPrice / 30;
        let defaultDeposit = productPrice * 0.5;

        securityDepositInput.value = defaultDeposit.toFixed(2);

        function updateCosts() {
            let months = parseInt(leaseMonths.value) || 0;
            let days = parseInt(leaseDays.value) || 0;

            if (months < 0) leaseMonths.value = 0;
            if (days < 0) leaseDays.value = 0;

            if (months === 0 && days === 0) {
                leaseDays.value = 1;
                days = 1;
            }

            let leasingCost = (months * productPrice) + (days * pricePerDay);
            let totalCost = leasingCost + defaultDeposit;
            totalCostDisplay.value = "KES " + totalCost.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        leaseMonths.addEventListener('input', updateCosts);
        leaseDays.addEventListener('input', updateCosts);

        updateCosts();
    });
</script>
</body>  
</html>
