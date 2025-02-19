<?php
session_start();
include 'config.php';
require 'vendor/autoload.php'; // Ensure Endroid QR Code library is installed

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    die("Error: You must be logged in to view this page!");
}

// Retrieve payment details
$lease_id = $_GET['lease_id'] ?? null;
$query = $conn->prepare("SELECT * FROM payments WHERE lease_id = ?");
$query->bind_param("i", $lease_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    die("Error: Payment details not found.");
}
$payment = $result->fetch_assoc();
$query->close();

// Generate QR Code
$qrData = "Lease ID: {$payment['lease_id']}\nAmount: KES " . number_format($payment['amount'], 2) . "\nDate: {$payment['payment_date']}";
$qrCode = QrCode::create($qrData)
    ->setEncoding(new Encoding('UTF-8'))
    ->setErrorCorrectionLevel(ErrorCorrectionLevel::Medium)
    ->setSize(150)
    ->setMargin(10);
$writer = new PngWriter();
$qrImage = $writer->write($qrCode)->getString();
$qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrImage); // Embed QR as Base64
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0fff4;
            text-align: center;
            padding: 20px;
        }
        .receipt {
            border: 2px solid #4CAF50;
            padding: 20px;
            max-width: 400px;
            margin: auto;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 128, 0, 0.2);
            border-radius: 10px;
        }
        h2 {
            color: #4CAF50;
        }
        .details {
            text-align: left;
            font-size: 16px;
            margin-top: 10px;
        }
        .qr-code {
            margin-top: 10px;
        }
        button {
            margin-top: 15px;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <div class="receipt">
        <h2>Payment Receipt</h2>
        <div class="details">
            <p><strong>Lease ID:</strong> <?= htmlspecialchars($payment['lease_id']) ?></p>
            <p><strong>Phone Number:</strong> <?= htmlspecialchars($payment['phone_number']) ?></p>
            <p><strong>Amount Paid:</strong> KES <?= number_format($payment['amount'], 2) ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($payment['payment_method']) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($payment['payment_date']) ?></p>
        </div>

        <div class="qr-code">
            <img src="<?= $qrCodeBase64 ?>" alt="QR Code">
            <p><small>Scan to verify</small></p>
        </div>

        <button onclick="downloadReceipt()">Download Receipt</button>
        <button onclick="window.location.href='index.php'">Back to Home</button>
    </div>

    <script>
        function downloadReceipt() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            doc.setTextColor(76, 175, 80);
            doc.setFontSize(18);
            doc.text("Payment Receipt", 20, 20);
            doc.setFontSize(14);
            doc.setTextColor(0, 0, 0);
            
            doc.text("Lease ID: <?= $payment['lease_id'] ?>", 20, 40);
            doc.text("Phone Number: <?= $payment['phone_number'] ?>", 20, 50);
            doc.text("Amount Paid: KES <?= number_format($payment['amount'], 2) ?>", 20, 60);
            doc.text("Payment Method: <?= $payment['payment_method'] ?>", 20, 70);
            doc.text("Date: <?= $payment['payment_date'] ?>", 20, 80);

            // Add QR code
            const qrImg = "<?= $qrCodeBase64 ?>";
            const img = new Image();
            img.src = qrImg;
            img.onload = function() {
                doc.addImage(img, 'PNG', 70, 90, 60, 60);
                doc.text("Scan to verify", 75, 160);
                doc.save("Receipt_<?= $payment['lease_id'] ?>.pdf");
            };
        }
    </script>

</body>
</html>
