<?php
session_start();
require('../../libs/fpdf.php');
include "../connect.php";

$conn = new mysqli('localhost', 'root', '', 'smc_nstpms');

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $student = $conn->query("SELECT last_name, first_name, middle_name FROM user WHERE user_id = $user_id")->fetch_assoc();
    $last_name = $student['last_name'];
    $first_name = $student['first_name'];
    $middle_name = $student['middle_name'];

    // Set up PDF certificate
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, "Certificate of Completion", 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "This certifies that", 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->Cell(0, 10, $last_name . ', ' . $first_name . ' ' . $middle_name, 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "has successfully completed the required activities.", 0, 1, 'C');
    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, "Registrar, NSTP Program", 0, 1, 'C');

    // Output the PDF for download
    $pdf->Output("D", "Certificate_$last_name,$first_name.pdf");
} else {
    echo "Error: No student selected.";
}
?>
