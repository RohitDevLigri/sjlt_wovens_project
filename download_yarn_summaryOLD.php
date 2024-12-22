<?php
require_once(__DIR__ . '../vendor/autoload.php');

// Fetch data from the database (example code, replace with your actual database query)
$poDate = '2024-11-01'; // Example data
$poNumber = 'PO123456';
$deliveryDate = '2024-11-15';
$paymentTerms = '30 Days';
$priceBasis = 'FOB';
$supplierName = 'ABC Supplier';
$supplierAddress = '123 Supplier Street';
$contactPerson = 'John Doe';
$contactNo = '+91 1234567890';
$supplierGstin = '33ABCDE1234F1Z5';
$deliveryAddress = '456 Delivery Lane';
$products = [
    ['sno' => 1, 'hsn_code' => '5205', 'product_name' => 'Cotton Yarn', 'yarn_count' => '20/1', 'warp_weft' => 'Warp', 'qty' => 100, 'rate' => 250, 'total_amount' => 25000],
    ['sno' => 2, 'hsn_code' => '5206', 'product_name' => 'Polyester Yarn', 'yarn_count' => '30/1', 'warp_weft' => 'Weft', 'qty' => 200, 'rate' => 300, 'total_amount' => 60000]
];

// Totals
$totalAmountBeforeTax = 85000;
$igst = 15300;
$cgst = 7650;
$sgst = 7650;
$totalGst = $igst + $cgst + $sgst;
$totalValue = $totalAmountBeforeTax + $totalGst;

// Initialize TCPDF
$pdf = new TCPDF();
$pdf->AddPage();

// Company Logo (Replace 'path/to/your/logo.png' with your actual logo path)
$pdf->Image('path/to/your/logo.png', 10, 10, 20, 20);

// Title
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'SJLT WOVENS PVT LTD', 0, 1, 'C');
$pdf->Cell(0, 10, 'YARN PURCHASE ORDER', 0, 1, 'C');

// Company Info and PO Details
$pdf->SetFont('helvetica', '', 10);
$pdf->SetLeftMargin(10);
$pdf->SetRightMargin(10);

$pdf->MultiCell(95, 5, "Office Address:\nSJLT WOVENS PVT LTD\nNH-7, Namakkal Karur Main Road,\nPillaikalathur, Paramathi,\nNamakkal, Tamil Nadu - 637207\nEmail: weaving@sjlt.in\nPhone: +91 9500988297\nGSTIN: 33ABDCS6945H1Z6\nCIN No: U17100TN2020PTC135456", 1, 'L');

$pdf->MultiCell(95, 5, "PO Number: $poNumber\nPO Date: $poDate\nDelivery Date: $deliveryDate\nPayment Terms: $paymentTerms\nPrice Basis: $priceBasis", 1, 'R');

$pdf->Ln();

// Supplier and Delivery Address
$pdf->MultiCell(95, 5, "Supplier Name: $supplierName\nAddress: $supplierAddress\nContact Person: $contactPerson\nContact No: $contactNo\nSupplier GSTIN: $supplierGstin", 1, 'L');
$pdf->MultiCell(95, 5, "Delivery Address:\n$deliveryAddress", 1, 'R');
$pdf->Ln();

// Product Table Header
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(20, 10, 'SNo', 1);
$pdf->Cell(30, 10, 'HSN Code', 1);
$pdf->Cell(40, 10, 'Product Name', 1);
$pdf->Cell(30, 10, 'Yarn Count', 1);
$pdf->Cell(30, 10, 'Warp / Weft', 1);
$pdf->Cell(20, 10, 'Qty', 1);
$pdf->Cell(20, 10, 'Rate', 1);
$pdf->Cell(30, 10, 'Total Amount', 1);
$pdf->Ln();

// Loop through products
$pdf->SetFont('helvetica', '', 10);
foreach ($products as $product) {
    $pdf->Cell(20, 10, $product['sno'], 1);
    $pdf->Cell(30, 10, $product['hsn_code'], 1);
    $pdf->Cell(40, 10, $product['product_name'], 1);
    $pdf->Cell(30, 10, $product['yarn_count'], 1);
    $pdf->Cell(30, 10, $product['warp_weft'], 1);
    $pdf->Cell(20, 10, $product['qty'], 1);
    $pdf->Cell(20, 10, $product['rate'], 1);
    $pdf->Cell(30, 10, $product['total_amount'], 1);
    $pdf->Ln();
}

// Totals Section
$pdf->Ln(10);
$pdf->Cell(160, 10, 'Total Amount Before Tax:', 1);
$pdf->Cell(30, 10, 'Rs. ' . number_format($totalAmountBeforeTax, 2), 1, 1, 'R');
$pdf->Cell(160, 10, 'IGST:', 1);
$pdf->Cell(30, 10, 'Rs. ' . number_format($igst, 2), 1, 1, 'R');
$pdf->Cell(160, 10, 'CGST:', 1);
$pdf->Cell(30, 10, 'Rs. ' . number_format($cgst, 2), 1, 1, 'R');
$pdf->Cell(160, 10, 'SGST:', 1);
$pdf->Cell(30, 10, 'Rs. ' . number_format($sgst, 2), 1, 1, 'R');
$pdf->Cell(160, 10, 'Total GST:', 1);
$pdf->Cell(30, 10, 'Rs. ' . number_format($totalGst, 2), 1, 1, 'R');
$pdf->Cell(160, 10, 'Total Value:', 1);
$pdf->Cell(30, 10, 'Rs. ' . number_format($totalValue, 2), 1, 1, 'R');

// Terms and Conditions
$pdf->Ln(10);
$pdf->MultiCell(0, 5, "Terms and Condition:\n1. Must send order confirmation & acknowledgement with company's stamp within 48 hours otherwise it will be considered accepted.\n2. Must deliver within ship window else PO will get cancelled.\n3. Pre-Production samples must be approved before bulk production.\n4. Original invoices to be sent to office address only.\n5. All payment subject to goods shipped as per our product specification and quality parameter.\n6. All disputes subject to Namakkal Jurisdiction Only.", 1, 'L');

// Footer
$pdf->Ln(10);
$pdf->Cell(60, 10, 'Prepared By', 0, 0);
$pdf->Cell(60, 10, 'Checked By', 0, 0);
$pdf->Cell(60, 10, 'For SJLT WOVENS PVT LTD', 0, 1, 'R');
$pdf->Ln(5);
$pdf->Cell(0, 10, 'Authorised Signatory', 0, 1, 'R');

// Output the PDF
$pdf->Output('purchase_order.pdf', 'D');
?>