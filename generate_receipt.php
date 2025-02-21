<?php
require_once('vendor/autoload.php');
include 'config.php';

// Validate Transaction ID
$txn_id = $_GET['txn_id'] ?? '';
if (!$txn_id) {
    die("Error: Invalid Transaction!");
}

// Fetch Transaction Details
$query = $conn->prepare("SELECT * FROM transactions WHERE mpesa_receipt_number = ?");
$query->bind_param("s", $txn_id);
$query->execute();
$result = $query->get_result();
$transaction = $result->fetch_assoc();

if (!$transaction) {
    die("Error: Transaction not found!");
}

// Create PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Website');
$pdf->SetTitle('Payment Receipt');
$pdf->AddPage();

$pdf->SetFont('helvetica', '', 12);
$html = "
    <h2>Payment Receipt</h2>
    <hr>
    <p><strong>Transaction ID:</strong> {$transaction['mpesa_receipt_number']}</p>
    <p><strong>Phone Number:</strong> {$transaction['phone']}</p>
    <p><strong>Amount Paid:</strong> KES " . number_format($transaction['amount'], 2) . "</p>
    <p><strong>Payment Date:</strong> {$transaction['transaction_date']}</p>
    <p><strong>Status:</strong> Success</p>
";

$pdf->writeHTML($html);
$pdf->Output("receipt_$txn_id.pdf", 'D'); // 'D' forces download
?>
