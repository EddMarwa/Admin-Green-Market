<?php
session_start();
include 'config.php'; // Ensure database connection

// Get item_id from URL (basic validation)
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
$item_name = isset($_GET['item_name']) ? urldecode($_GET['item_name']) : 'Unknown';
$item_price = isset($_GET['item_price']) ? floatval($_GET['item_price']) : 0;

if ($item_id == 0) {
    die("Invalid item selection.");
}

// Fetch product details from the database
$query = $conn->prepare("SELECT Name, Price, Image FROM items WHERE Item_ID = ?");
$query->bind_param("i", $item_id);
$query->execute();
$result = $query->get_result();
$product = $result->fetch_assoc();
$query->close();

// Use default values if product is missing
if (!$product) {
    $product = [
        'Name' => $item_name,
        'Price' => $item_price,
        'Image' => 'img.jpg' // Default image
    ];
}

$imagePath = 'images/' . $product['Image'];
if (empty($product['Image']) || !file_exists($imagePath)) {
    $imagePath = 'images/img.jpg'; // Placeholder image
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lease Product</title>
    <link rel="stylesheet" href="layout/css/front.css">
    <style>
        .lease-container { max-width: 500px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
        .lease-summary img { max-width: 100%; height: auto; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .lease-btn { width: 100%; padding: 10px; background-color: green; color: white; border: none; cursor: pointer; }
        .lease-btn:hover { background-color: darkgreen; }
        #total_cost { font-weight: bold; color: blue; }
    </style>
</head>
<body>

<div class="lease-container">
    <h2>Lease Product</h2>

    <div class="lease-summary">
        <h3><?= htmlspecialchars($product['Name']) ?></h3>
        <p>Monthly Price: <b>KES <?= number_format($product['Price'], 2) ?></b></p>
        <img src="<?= $imagePath ?>" alt="Product Image">
    </div>

    <form action="process_lease.php" method="POST" class="lease-form">
        <input type="hidden" name="item_id" value="<?= $item_id ?>">

        <!-- Lease Duration -->
        <div class="form-group">
            <label for="lease_months">Lease Duration:</label>
            <div style="display: flex; gap: 10px;">
                <input type="number" name="lease_months" id="lease_months" min="0" max="24" placeholder="Months">
                <input type="number" name="lease_days" id="lease_days" min="0" max="30" placeholder="Days">
            </div>
        </div>

        <!-- Lease Start Date -->
        <div class="form-group">
            <label for="start_date">Lease Start Date:</label>
            <input type="date" name="start_date" id="start_date" required>
        </div>

        <!-- Security Deposit -->
        <div class="form-group">
            <label for="security_deposit">Security Deposit (Refundable):</label>
            <input type="number" name="security_deposit" id="security_deposit" placeholder="5000" required>
        </div>

        <!-- Payment Method -->
        <div class="form-group">
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="mpesa">M-Pesa</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="credit_card">Credit Card</option>
            </select>
        </div>

        <!-- Live Cost Calculation -->
        <p>Estimated Total Cost: <span id="total_cost">KES 0.00</span></p>

        <!-- Lease Agreement -->
        <div class="form-group">
            <label>
                <input type="checkbox" name="agree_terms" required>
                I agree to the lease terms and conditions.
            </label>
        </div>

        <!-- Submit Lease Request -->
        <div class="form-group">
            <button type="submit" class="lease-btn">Confirm Lease</button>
        </div>
    </form>
</div>

<div class="lease-footer">
    <p>Powered by Green Market &copy; 2025</p>
</div>

<script>
    // Set minimum lease start date to today
    document.getElementById('start_date').min = new Date().toISOString().split('T')[0];

    // Dynamic Lease Cost Calculation
    function updateTotalCost() {
        let months = parseInt(document.getElementById('lease_months').value) || 0;
        let days = parseInt(document.getElementById('lease_days').value) || 0;
        let pricePerMonth = <?= $product['Price'] ?>;
        
        let pricePerDay = pricePerMonth / 30; // Calculate daily price
        let totalCost = (months * pricePerMonth) + (days * pricePerDay);
        
        document.getElementById('total_cost').textContent = "KES " + totalCost.toFixed(2);
    }

    document.getElementById('lease_months').addEventListener('input', updateTotalCost);
    document.getElementById('lease_days').addEventListener('input', updateTotalCost);
</script>

</body>
</html>
