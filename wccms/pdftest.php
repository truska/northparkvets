<?php
ob_start(); // Start output buffering

include ('setting/main-top-files.php');
require_once('tcpdf/tcpdf.php');

// Query data
$query = "SELECT * FROM `products`";
$result = $conn->query($query);

// Create a new PDF instance
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('North Park');
$pdf->SetTitle('Test');
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

// Add table headers
$html = '<table border="1" cellpadding="4">
<tr>
    <th>ID</th>
    <th>SKU</th>
    <th>Product</th>
</tr>';
// Add data rows
while ($row = $result->fetch_assoc()) {
    $html .= '<tr>
        <td>' . $row['id'] . '</td>
        <td>' . $row['sku'] . '</td>
        <td>' . $row['product_type'] . '</td>
    </tr>';
}

$html .= '</table>';
// Output the HTML as a table in PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output the PDF
ob_end_clean(); // Clear any previous output
$pdf->Output('database.pdf', 'I');

?>