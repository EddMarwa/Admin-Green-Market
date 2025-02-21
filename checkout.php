<?php 
session_start();
include 'config.php';    

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to view this page!!!");
}
$user_id = $_SESSION['uid'];

// Validate and fetch required parameters securely
$item_id = filter_input(INPUT_GET, 'item_ID', FILTER_VALIDATE_INT);
$item_name = filter_input(INPUT_GET, 'item_name', FILTER_SANITIZE_STRING);
$item_price = filter_input(INPUT_GET, 'item_price', FILTER_VALIDATE_FLOAT);
$isLease = filter_input(INPUT_GET, 'lease', FILTER_SANITIZE_STRING) === 'yes';
$leaseDuration = filter_input(INPUT_GET, 'lease_duration', FILTER_VALIDATE_INT) ?? 0;

// Validate required parameters
if (!$item_id || !$item_name || !$item_price || $item_price <= 0) {
    die("Error: Invalid product details.");
}

// Fetch product image from `items` table
$query = $conn->prepare("SELECT Image FROM items WHERE item_id = ?");
$query->bind_param("i", $item_id);
$query->execute();
$result = $query->get_result();
$product = $result->fetch_assoc();

// Check if an image exists; otherwise, use default image
$imageFile = (!empty($product['Image']) && file_exists("images/" . $product['Image'])) ? 
             "Images/" . $product['Image'] : 
             "images/img.jpg"; 

// Validate lease duration if leasing
if ($isLease) {
    if ($leaseDuration <= 0) {
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
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: rgb(28, 144, 13);
        }
        .checkout-container {
            width: 50%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }
        .product-summary img {
            width: 200px;
            height: auto;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background: green;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
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

        <img src="<?= htmlspecialchars($imageFile) ?>" alt="Product Image">
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
        <input type="hidden" name="security_deposit" value="<?= htmlspecialchars($securityDeposit) ?>">

        <div class="form-group">
            <button type="submit">Confirm & Pay</button>
        </div>
    </form>  
</div>

</body>
</html>
