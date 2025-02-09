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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Link to your main CSS file -->
    <style>
        /* Global Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #81C784;
            color: #333;
        }

        .checkout-container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
        }

        .checkout-container h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333;
        }

        .product-summary {
            text-align: center;
            margin-bottom: 20px;
        }

        .product-summary h3 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 10px;
        }

        .product-summary p {
            font-size: 1.2rem;
            color: #555;
        }

        .form-group {
            margin: 20px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        input[type="text"] {
            padding: 12px;
            width: 100%;
            max-width: 350px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1rem;
            margin-bottom: 20px;
            outline: none;
        }

        input[type="text"]:focus {
            border-color: #28a745;
        }

        .btn {
            padding: 12px 30px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #218838;
        }

        .footer {
            text-align: center;
            font-size: 1rem;
            color: #777;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="checkout-container">
    <h2>Checkout</h2>
    <div class="product-summary">
        <h3><?= htmlspecialchars($item_name) ?></h3>
        <p>Price: KES <?= htmlspecialchars($item_price) ?></p>
        <!-- Display Product Image if Available -->
        <img src="images/<?= $item_id ?>.jpg" alt="Product Image">
        
    </div>

    <form action="index.php" method="POST">
        <div class="form-group">
            <label for="phone">Enter M-Pesa Phone Number:</label>
            <input type="text" name="phone" required placeholder="07XXXXXXXX" pattern="[0-9]{10}">
        </div>

        <input type="hidden" name="item_id" value="<?= htmlspecialchars($item_id) ?>">
        <input type="hidden" name="item_name" value="<?= htmlspecialchars($item_name) ?>">
        <input type="hidden" name="item_price" value="<?= htmlspecialchars($item_price) ?>">

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
