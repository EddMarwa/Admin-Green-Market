<?php
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to view this page!");
}

$user_id = $_SESSION['uid'];

// Fetch lease details for the logged-in user
$query = $conn->prepare("
    SELECT l.id, l.start_date, l.lease_months, l.lease_days, l.total_cost, l.payment_status, i.Name, i.Image 
    FROM leases l
    JOIN items i ON l.item_id = i.Item_ID
    WHERE l.user_id = ?
    ORDER BY l.start_date DESC
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

$leases = [];
while ($row = $result->fetch_assoc()) {
    $leases[] = $row;
}
$query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lease Status</title>
    <link rel="stylesheet" href="layout/css/front.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #1c900d;
            color: #fff;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
            color: #333;
        }
        h2 {
            color: #1c900d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background: #1c900d;
            color: #fff;
        }
        .product-img {
            width: 50px;
            height: auto;
            border-radius: 5px;
        }
        .status-completed {
            color: green;
            font-weight: bold;
        }
        .status-pending {
            color: orange;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Lease Status</h2>
    
    <?php if (count($leases) > 0): ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Lease Duration</th>
                <th>Start Date</th>
                <th>Total Cost (KES)</th>
                <th>Payment Status</th>
            </tr>
            <?php foreach ($leases as $lease): ?>
                <tr>
                    <td>
                        <img src="images/<?= htmlspecialchars($lease['Image']) ?: 'img.jpg' ?>" class="product-img" alt="<?= htmlspecialchars($lease['Name']) ?>">
                        <?= htmlspecialchars($lease['Name']) ?>
                    </td>
                    <td><?= $lease['lease_months'] ?> months, <?= $lease['lease_days'] ?> days</td>
                    <td><?= htmlspecialchars($lease['start_date']) ?></td>
                    <td><?= number_format($lease['total_cost'], 2) ?></td>
                    <td class="<?= $lease['payment_status'] === 'completed' ? 'status-completed' : 'status-pending' ?>">
                        <?= ucfirst($lease['payment_status']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No lease records found.</p>
    <?php endif; ?>
</div>

</body>
</html>
