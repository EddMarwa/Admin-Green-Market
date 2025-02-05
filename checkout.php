<?php
session_start();
include 'config.php'; // Database connection

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $query = "SELECT * FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($result);
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
        <h3><?= $product['name'] ?></h3>
        <p>Price: KES <?= $product['price'] ?></p>
        <img src="<?= $product['image'] ?>" alt="Product Image" width="100">
    </div>

    <form action="process_payment.php" method="POST">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <label for="phone">Enter M-Pesa Phone Number:</label>
        <input type="text" name="phone" required placeholder="07XXXXXXXX">
        <button type="submit" class="btn btn-success">Confirm & Pay</button>
    </form>
</div>

</body>
</html>
