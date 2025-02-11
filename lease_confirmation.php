<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lease Confirmation</title>
    <link rel="stylesheet" href="layout/css/front.css">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .confirmation-container {
            width: 50%;
            margin: auto;
            text-align: center;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-top: 50px;
        }
        h2 {
            color: #2a9d8f;
        }
        .success-message {
            font-size: 18px;
            color: green;
            margin: 10px 0;
        }
        .lease-details {
            text-align: left;
            padding: 10px;
        }
        .button {
            background-color: #2a9d8f;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .button:hover {
            background-color: #21867a;
        }
    </style>
</head>
<body>

<div class="confirmation-container">
    <h2>Lease Successful</h2>
    <p class="success-message">Your lease has been successfully placed and is awaiting admin approval.</p>
    <div class="lease-details">
        <p><strong>Start Date:</strong> <?= htmlspecialchars($_GET['start_date'] ?? 'Not Provided') ?></p>
        <p><strong>Estimated End Date:</strong> <?= htmlspecialchars($_GET['end_date'] ?? 'Not Provided') ?></p>
        <p><strong>Total Cost:</strong> KES <?= number_format($_GET['total_cost'] ?? 0, 2) ?></p>
    </div>
    <a href="index.php" class="button">Back to Home</a>
</div>

</body>
</html>
