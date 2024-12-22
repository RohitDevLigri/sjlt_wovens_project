<?php  
// Include database connection
include('dbconnect.php');

// Load PHPSpreadsheet library using Composer's autoloader
require 'vendor/autoload.php';

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use PHPSpreadsheet classes
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Instantiate the Connection class and get the connection
$database = new Connection();
$conn = $database->getConnection();

// Check if the connection is established
if (!$conn) {
    die("Database connection failed.");
}

// Get search parameters from the query string (optional filters)
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$sort_code = isset($_GET['sort_code']) ? $_GET['sort_code'] : '';
$construction = isset($_GET['construction']) ? $_GET['construction'] : '';
$machine = isset($_GET['machine']) ? $_GET['machine'] : '';
$warp_set_no = isset($_GET['warp_set_no']) ? $_GET['warp_set_no'] : '';
$weft_lot_no = isset($_GET['weft_lot_no']) ? $_GET['weft_lot_no'] : '';
$picks = isset($_GET['picks']) ? $_GET['picks'] : '';
$length = isset($_GET['length']) ? $_GET['length'] : '';
$percentage = isset($_GET['percentage']) ? $_GET['percentage'] : '';
$f_stops = isset($_GET['f_stops']) ? $_GET['f_stops'] : '';
$fsph = isset($_GET['fsph']) ? $_GET['fsph'] : '';
$atpfs = isset($_GET['atpfs']) ? $_GET['atpfs'] : '';
$fcmpx = isset($_GET['fcmpx']) ? $_GET['fcmpx'] : '';
$w_stops = isset($_GET['w_stops']) ? $_GET['w_stops'] : '';
$wsph = isset($_GET['wsph']) ? $_GET['wsph'] : '';
$atpws = isset($_GET['atpws']) ? $_GET['atpws'] : '';
$wcmpx = isset($_GET['wcmpx']) ? $_GET['wcmpx'] : '';
$b_stops = isset($_GET['b_stops']) ? $_GET['b_stops'] : '';
$bsph = isset($_GET['bsph']) ? $_GET['bsph'] : '';
$atpbs = isset($_GET['atpbs']) ? $_GET['atpbs'] : '';
$bcmpx = isset($_GET['bcmpx']) ? $_GET['bcmpx'] : '';
$speed = isset($_GET['speed']) ? $_GET['speed'] : '';

// Function to handle null or empty values
function handle_null($value) {
    return isset($value) && $value !== '' ? $value : 'N/A';
}

// Check if any search or sort filters are applied
if (
    empty($from_date) && empty($to_date) && empty($date) && empty($sort_code) &&
    empty($construction) && empty($machine) && empty($warp_set_no) && empty($weft_lot_no) &&
    empty($picks) && empty($length) && empty($percentage) && empty($f_stops) &&
    empty($fsph) && empty($atpfs) && empty($fcmpx) && empty($w_stops) &&
    empty($wsph) && empty($atpws) && empty($wcmpx) && empty($b_stops) &&
    empty($bsph) && empty($atpbs) && empty($bcmpx) && empty($speed)
) {
    // If no filters are applied, do not proceed with the Excel download
    die('No filters applied. Please apply a filter to download data.');
}

// Prepare the SQL query with placeholders for prepared statements
$query = "SELECT * FROM Production_Report WHERE 1=1";
$params = [];

// Date filters
if (!empty($from_date) && !empty($to_date)) {
    $query .= " AND Date BETWEEN ? AND ?";
    $params[] = $from_date;
    $params[] = $to_date;
} elseif (!empty($from_date)) {
    $query .= " AND Date >= ?";
    $params[] = $from_date;
} elseif (!empty($to_date)) {
    $query .= " AND Date <= ?";
    $params[] = $to_date;
}

if (!empty($date)) {
    $query .= " AND Date = ?";
    $params[] = $date;
}

// Sort code filter
if (!empty($sort_code)) {
    $query .= " AND Sort_code = ?";
    $params[] = $sort_code;
}

// Other filters
if (!empty($construction)) {
    $query .= " AND Construction = ?";
    $params[] = $construction;
}
if (!empty($machine)) {
    $query .= " AND Machine = ?";
    $params[] = $machine;
}
if (!empty($warp_set_no)) {
    $query .= " AND Warp_Set_No = ?";
    $params[] = $warp_set_no;
}
if (!empty($weft_lot_no)) {
    $query .= " AND Weft_Lot_No = ?";
    $params[] = $weft_lot_no;
}
if (!empty($picks)) {
    $query .= " AND Picks = ?";
    $params[] = $picks;
}
if (!empty($length)) {
    $query .= " AND Length = ?";
    $params[] = $length;
}
if (!empty($percentage)) {
    $query .= " AND Percentage = ?";
    $params[] = $percentage;
}
if (!empty($f_stops)) {
    $query .= " AND F_Stops = ?";
    $params[] = $f_stops;
}
if (!empty($fsph)) {
    $query .= " AND Fsph = ?";
    $params[] = $fsph;
}
if (!empty($atpfs)) {
    $query .= " AND Atpfs = ?";
    $params[] = $atpfs;
}
if (!empty($fcmpx)) {
    $query .= " AND Fcmpx = ?";
    $params[] = $fcmpx;
}
if (!empty($w_stops)) {
    $query .= " AND W_Stops = ?";
    $params[] = $w_stops;
}
if (!empty($wsph)) {
    $query .= " AND Wsph = ?";
    $params[] = $wsph;
}
if (!empty($atpws)) {
    $query .= " AND Atpws = ?";
    $params[] = $atpws;
}
if (!empty($wcmpx)) {
    $query .= " AND Wcmpx = ?";
    $params[] = $wcmpx;
}
if (!empty($b_stops)) {
    $query .= " AND B_Stops = ?";
    $params[] = $b_stops;
}
if (!empty($bsph)) {
    $query .= " AND Bsph = ?";
    $params[] = $bsph;
}
if (!empty($atpbs)) {
    $query .= " AND Atpbs = ?";
    $params[] = $atpbs;
}
if (!empty($bcmpx)) {
    $query .= " AND Bcmpx = ?";
    $params[] = $bcmpx;
}
if (!empty($speed)) {
    $query .= " AND Speed = ?";
    $params[] = $speed;
}

// **Add Ordering Clause to Sort Data in Ascending Order by Date**
$query .= " ORDER BY Date ASC";

// Prepare and execute the statement
$stmt = $conn->prepare($query);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the result has rows
if (empty($result)) {
    die('No records found for the applied filters.');
}

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column headers
$headers = [
    'Date', 'Sort Code', 'Construction', 'Machine', 'Warp Set No', 'Weft Lot No',
    'Picks', 'Length', 'Percentage', 'F Stops', 'FSPH', 'ATPFS', 'FCMPX', 'W Stops', 'WSPH', 'ATPWS', 'WCMPX',
    'B Stops', 'BSPH', 'ATPBS', 'BCMPX', 'Speed'
];

// Set header row
$columnLetter = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($columnLetter . '1', $header);
    $columnLetter++;
}

// Bold the header row
$headerRange = 'A1:' . chr(ord('A') + count($headers) - 1) . '1';
$sheet->getStyle($headerRange)->getFont()->setBold(true);

// Initialize totals and counters
$total_picks = $total_length = $total_percentage = $total_f_stops = 0;
$total_fsph = $total_atpfs = $total_fcmpx = $total_w_stops = 0;
$total_wsph = $total_atpws = $total_wcmpx = 0;
$total_b_stops = $total_bsph = $total_atpbs = $total_bcmpx = 0;
$total_speed = 0;

// Add data rows
$rowNumber = 2; // Start from the second row
foreach ($result as $row) {
    $columnLetter = 'A';
    
    // Format the date to DD/MM/YYYY
    $formatted_date = date('d/m/Y', strtotime($row['Date']));
    
    // Set data into columns
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($formatted_date));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Sort_code']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Construction']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Machine']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Warp_Set_No']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Weft_Lot_No']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Picks']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Length']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Percentage']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['F_Stops']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Fsph']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Atpfs']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Fcmpx']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['W_Stops']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Wsph']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Atpws']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Wcmpx']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['B_Stops']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Bsph']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Atpbs']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Bcmpx']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Speed']));
    
    // Accumulate totals for each column
    $total_picks += isset($row['Picks']) ? (int)$row['Picks'] : 0;
    $total_length += isset($row['Length']) ? (float)$row['Length'] : 0;
    $total_percentage += isset($row['Percentage']) ? (float)$row['Percentage'] : 0;
    $total_f_stops += isset($row['F_Stops']) ? (float)$row['F_Stops'] : 0;
    $total_fsph += isset($row['Fsph']) ? (float)$row['Fsph'] : 0;
    $total_atpfs += isset($row['Atpfs']) ? (float)$row['Atpfs'] : 0;
    $total_fcmpx += isset($row['Fcmpx']) ? (float)$row['Fcmpx'] : 0;
    $total_w_stops += isset($row['W_Stops']) ? (float)$row['W_Stops'] : 0;
    $total_wsph += isset($row['Wsph']) ? (float)$row['Wsph'] : 0;
    $total_atpws += isset($row['Atpws']) ? (float)$row['Atpws'] : 0;
    $total_wcmpx += isset($row['Wcmpx']) ? (float)$row['Wcmpx'] : 0;
    $total_b_stops += isset($row['B_Stops']) ? (float)$row['B_Stops'] : 0;
    $total_bsph += isset($row['Bsph']) ? (float)$row['Bsph'] : 0;
    $total_atpbs += isset($row['Atpbs']) ? (float)$row['Atpbs'] : 0;
    $total_bcmpx += isset($row['Bcmpx']) ? (float)$row['Bcmpx'] : 0;
    $total_speed += isset($row['Speed']) ? (float)$row['Speed'] : 0;
    
    $rowNumber++;
}

// Set total row
$sheet->mergeCells('A' . $rowNumber . ':E' . $rowNumber); // Merge cells for the "Totals" label
$sheet->setCellValue('F' . $rowNumber, 'Totals');
$sheet->setCellValue('G' . $rowNumber, $total_picks);
$sheet->setCellValue('H' . $rowNumber, $total_length);
$sheet->setCellValue('I' . $rowNumber, $total_percentage);
$sheet->setCellValue('J' . $rowNumber, $total_f_stops);
$sheet->setCellValue('K' . $rowNumber, $total_fsph);
$sheet->setCellValue('L' . $rowNumber, $total_atpfs);
$sheet->setCellValue('M' . $rowNumber, $total_fcmpx);
$sheet->setCellValue('N' . $rowNumber, $total_w_stops);
$sheet->setCellValue('O' . $rowNumber, $total_wsph);
$sheet->setCellValue('P' . $rowNumber, $total_atpws);
$sheet->setCellValue('Q' . $rowNumber, $total_wcmpx);
$sheet->setCellValue('R' . $rowNumber, $total_b_stops);
$sheet->setCellValue('S' . $rowNumber, $total_bsph);
$sheet->setCellValue('T' . $rowNumber, $total_atpbs);
$sheet->setCellValue('U' . $rowNumber, $total_bcmpx);
$sheet->setCellValue('V' . $rowNumber, $total_speed);

// Style the total row
$sheet->getStyle('A' . $rowNumber . ':V' . $rowNumber)->getFont()->setBold(true);
$sheet->getStyle('A' . $rowNumber . ':V' . $rowNumber)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFEFEFEF'); // Light grey background

// Set auto column width for all columns
foreach (range('A', 'V') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Create the Excel file
$writer = new Xlsx($spreadsheet);
$filename = 'Production_Report_' . date('Ymd') . '.xlsx';

// Redirect output to a client’s web browser (Excel)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Write the file to the output
$writer->save('php://output');
exit;
?>
