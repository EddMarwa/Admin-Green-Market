<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to view this page!!!");
}

$user_id = $_SESSION['uid'];
$lease_id = $_GET['lease_id'] ?? null; // Corrected parameter

// Validates lease_ids properly
if (!$lease_id || !ctype_digit($lease_id)) {  
    die("Invalid product selection.");
}


// Fetch lease details
$query = $conn->prepare("
    SELECT i.Name 
    FROM leases l 
    JOIN items i ON l.item_id = i.Item_ID 
    WHERE l.user_id = ?
    LIMIT 1
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $lease = $result->fetch_assoc();
} else {
    die("Lease details not found.");
}
$query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <style>
        body {
            background-color: #e8f5e9;
            color: #2e7d32;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .message {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 3000);
    </script>
</head>
<body>
    <div class="message">Directing to home page!</div>
</body>
</html>
