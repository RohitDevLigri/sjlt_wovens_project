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
$loomdate = isset($_GET['loomdate']) ? $_GET['loomdate'] : '';
$machine = isset($_GET['machine']) ? $_GET['machine'] : '';
$serial = isset($_GET['serial']) ? $_GET['serial'] : '';
$nr = isset($_GET['nr']) ? $_GET['nr'] : '';
$shift = isset($_GET['shift']) ? $_GET['shift'] : '';
$time = isset($_GET['time']) ? $_GET['time'] : '';
$picks = isset($_GET['picks']) ? $_GET['picks'] : '';
$length = isset($_GET['length']) ? $_GET['length'] : '';
$percentage = isset($_GET['percentage']) ? $_GET['percentage'] : '';
$weaver_percentage = isset($_GET['weaver_percentage']) ? $_GET['weaver_percentage'] : '';
$stops = isset($_GET['stops']) ? $_GET['stops'] : '';
$stops_cmpx = isset($_GET['stops_cmpx']) ? $_GET['stops_cmpx'] : '';
$stops_time = isset($_GET['stops_time']) ? $_GET['stops_time'] : '';
$filling = isset($_GET['filling']) ? $_GET['filling'] : '';
$filling_cmpx = isset($_GET['filling_cmpx']) ? $_GET['filling_cmpx'] : '';
$filling_time = isset($_GET['filling_time']) ? $_GET['filling_time'] : '';
$warp = isset($_GET['warp']) ? $_GET['warp'] : '';
$warp_cmpx = isset($_GET['warp_cmpx']) ? $_GET['warp_cmpx'] : '';
$warp_time = isset($_GET['warp_time']) ? $_GET['warp_time'] : '';
$bobbin = isset($_GET['bobbin']) ? $_GET['bobbin'] : '';
$bobbin_cmpx = isset($_GET['bobbin_cmpx']) ? $_GET['bobbin_cmpx'] : '';
$bobbin_time = isset($_GET['bobbin_time']) ? $_GET['bobbin_time'] : '';
$hand = isset($_GET['hand']) ? $_GET['hand'] : '';
$hand_cmpx = isset($_GET['hand_cmpx']) ? $_GET['hand_cmpx'] : '';
$hand_time = isset($_GET['hand_time']) ? $_GET['hand_time'] : '';
$other = isset($_GET['other']) ? $_GET['other'] : '';
$other_cmpx = isset($_GET['other_cmpx']) ? $_GET['other_cmpx'] : '';
$other_time = isset($_GET['other_time']) ? $_GET['other_time'] : '';
$starts = isset($_GET['starts']) ? $_GET['starts'] : '';
$speed = isset($_GET['speed']) ? $_GET['speed'] : '';

// Function to handle null or empty values
function handle_null($value) {
    return isset($value) && $value !== '' ? $value : 'N/A';
}

// Check if any search or sort filters are applied
if (
    empty($from_date) && 
    empty($to_date) && 
    empty($date) && 
    empty($loomdate) && 
    empty($machine) && 
    empty($serial) && 
    empty($nr) && 
    empty($shift) && 
    empty($time) && 
    empty($picks) && 
    empty($length) && 
    empty($percentage) && 
    empty($weaver_percentage) && 
    empty($stops) && 
    empty($stopscmpx) && 
    empty($stopstime) && 
    empty($filling) && 
    empty($fillingcmpx) && 
    empty($fillingtime) && 
    empty($warp) && 
    empty($warpcmpx) && 
    empty($warptime) && 
    empty($bobbin) && 
    empty($bobbincmpx) && 
    empty($bobbintime) && 
    empty($hand) && 
    empty($handcmpx) && 
    empty($handtime) && 
    empty($other) && 
    empty($othercmpx) && 
    empty($othertime) && 
    empty($starts) && 
    empty($speed)
) {
    // If no filters are applied, do not proceed with the PDF download
    die('No filters applied. Please apply a filter to download data.');
}


// Prepare the SQL query with placeholders for prepared statements
$query = "SELECT * FROM Productiondata WHERE 1=1";
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

// Apply other filters
if (!empty($loomdate)) {
    $sql .= " AND Loomdate = :loomdate";
    $params[':loomdate'] = $loomdate;
}
if (!empty($machine)) {
    $sql .= " AND Machine = :machine";
    $params[':machine'] = $machine;
}
if (!empty($serial)) {
    $sql .= " AND Serial = :serial";
    $params[':serial'] = $serial;
}
if (!empty($nr)) {
    $sql .= " AND NR = :nr";
    $params[':nr'] = $nr;
}
if (!empty($shift)) {
    $sql .= " AND Shift = :shift";
    $params[':shift'] = $shift;
}
if (!empty($time)) {
    $sql .= " AND Time = :time";
    $params[':time'] = $time;
}
if (!empty($picks)) {
    $sql .= " AND Picks = :picks";
    $params[':picks'] = $picks;
}
if (!empty($length)) {
    $sql .= " AND Length = :length";
    $params[':length'] = $length;
}
if (!empty($percentage)) {
    $sql .= " AND Percentage = :percentage";
    $params[':percentage'] = $percentage;
}
if (!empty($weaver_percentage)) {
    $sql .= " AND Weaver_Percentage = :weaver_percentage";
    $params[':weaver_percentage'] = $weaver_percentage;
}
if (!empty($stops)) {
    $sql .= " AND Stops = :stops";
    $params[':stops'] = $stops;
}
if (!empty($stopscmpx)) {
    $sql .= " AND StopsCMPX = :stopscmpx";
    $params[':stopscmpx'] = $stopscmpx;
}
if (!empty($stopstime)) {
    $sql .= " AND StopsTime = :stopstime";
    $params[':stopstime'] = $stopstime;
}
if (!empty($filling)) {
    $sql .= " AND Filling = :filling";
    $params[':filling'] = $filling;
}
if (!empty($fillingcmpx)) {
    $sql .= " AND FillingCMPX = :fillingcmpx";
    $params[':fillingcmpx'] = $fillingcmpx;
}
if (!empty($fillingtime)) {
    $sql .= " AND FillingTime = :fillingtime";
    $params[':fillingtime'] = $fillingtime;
}
if (!empty($warp)) {
    $sql .= " AND Warp = :warp";
    $params[':warp'] = $warp;
}
if (!empty($warpcmpx)) {
    $sql .= " AND WarpCMPX = :warpcmpx";
    $params[':warpcmpx'] = $warpcmpx;
}
if (!empty($warptime)) {
    $sql .= " AND WarpTime = :warptime";
    $params[':warptime'] = $warptime;
}
if (!empty($bobbin)) {
    $sql .= " AND Bobbin = :bobbin";
    $params[':bobbin'] = $bobbin;
}
if (!empty($bobbincmpx)) {
    $sql .= " AND BobbinCMPX = :bobbincmpx";
    $params[':bobbincmpx'] = $bobbincmpx;
}
if (!empty($bobbintime)) {
    $sql .= " AND BobbinTime = :bobbintime";
    $params[':bobbintime'] = $bobbintime;
}
if (!empty($hand)) {
    $sql .= " AND Hand = :hand";
    $params[':hand'] = $hand;
}
if (!empty($handcmpx)) {
    $sql .= " AND HandCMPX = :handcmpx";
    $params[':handcmpx'] = $handcmpx;
}
if (!empty($handtime)) {
    $sql .= " AND HandTime = :handtime";
    $params[':handtime'] = $handtime;
}
if (!empty($other)) {
    $sql .= " AND Other = :other";
    $params[':other'] = $other;
}
if (!empty($othercmpx)) {
    $sql .= " AND OtherCMPX = :othercmpx";
    $params[':othercmpx'] = $othercmpx;
}
if (!empty($othertime)) {
    $sql .= " AND OtherTime = :othertime";
    $params[':othertime'] = $othertime;
}
if (!empty($starts)) {
    $sql .= " AND Starts = :starts";
    $params[':starts'] = $starts;
}
if (!empty($speed)) {
    $sql .= " AND Speed = :speed";
    $params[':speed'] = $speed;
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
    'Date',
    'Loomdate',
    'Machine',
    'Serial',
    'NR',
    'Shift',
    'Time',
    'Picks',
    'Length',
    'Percentage',
    'Weaver_Percentage',
    'Stops',
    'StopsCMPX',
    'StopsTime',
    'Filling',
    'FillingCMPX',
    'FillingTime',
    'Warp',
    'WarpCMPX',
    'WarpTime',
    'Bobbin',
    'BobbinCMPX',
    'BobbinTime',
    'Hand',
    'HandCMPX',
    'HandTime',
    'Other',
    'OtherCMPX',
    'OtherTime',
    'Starts',
    'Speed',
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
$sheet->getStyle('A1')->getFont()->setBold(true);

// Initialize sums and counts for totals and averages
$total_picks = $total_length = $total_percentage = $total_weaver_percentage = 0;
$total_stops = $total_stops_cmpx = $total_stops_time = 0;
$total_filling = $total_filling_cmpx = $total_filling_time = 0;
$total_warp = $total_warp_cmpx = $total_warp_time = 0;
$total_bobbin = $total_bobbin_cmpx = $total_bobbin_time = 0;
$total_hand = $total_hand_cmpx = $total_hand_time = 0;
$total_other = $total_other_cmpx = $total_other_time = 0;
$total_starts = $total_speed = 0;
$total_rows = 0;

// Add data rows
$rowNumber = 2; // Start from the second row
foreach ($result as $row) {
    $columnLetter = 'A';
    
    // Format the date to DD/MM/YYYY
    $formatted_date = date('d/m/Y', strtotime($row['Date']));
    
    // Set data into columns
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($formatted_date));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Loomdate']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Machine']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Serial']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['NR']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Shift']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Time']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Picks']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Length']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Percentage']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Weaver_Percentage']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Stops']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['StopsCMPX']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['StopsTime']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Filling']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['FillingCMPX']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['FillingTime']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Warp']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['WarpCMPX']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['WarpTime']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Bobbin']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['BobbinCMPX']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['BobbinTime']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Hand']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['HandCMPX']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['HandTime']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Other']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['OtherCMPX']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['OtherTime']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Starts']));
    $sheet->setCellValue($columnLetter++ . $rowNumber, handle_null($row['Speed']));
    
    
    // Accumulate totals for each column
    $total_picks += $row['Picks'] ?? 0;
    $total_length += $row['Length'] ?? 0;
    $total_percentage += $row['Percentage'] ?? 0;
    $total_weaver_percentage += $row['Weaver_Percentage'] ?? 0;
    $total_stops += $row['Stops'] ?? 0;
    $total_stops_cmpx += $row['StopsCMPX'] ?? 0;
    $total_stops_time += $row['StopsTime'] ?? 0;
    $total_filling += $row['Filling'] ?? 0;
    $total_filling_cmpx += $row['FillingCMPX'] ?? 0;
    $total_filling_time += $row['FillingTime'] ?? 0;
    $total_warp += $row['Warp'] ?? 0;
    $total_warp_cmpx += $row['WarpCMPX'] ?? 0;
    $total_warp_time += $row['WarpTime'] ?? 0;
    $total_bobbin += $row['Bobbin'] ?? 0;
    $total_bobbin_cmpx += $row['BobbinCMPX'] ?? 0;
    $total_bobbin_time += $row['BobbinTime'] ?? 0;
    $total_hand += $row['Hand'] ?? 0;
    $total_hand_cmpx += $row['HandCMPX'] ?? 0;
    $total_hand_time += $row['HandTime'] ?? 0;
    $total_other += $row['Other'] ?? 0;
    $total_other_cmpx += $row['OtherCMPX'] ?? 0;
    $total_other_time += $row['OtherTime'] ?? 0;
    $total_starts += $row['Starts'] ?? 0;
    $total_speed += $row['Speed'] ?? 0;

    $total_rows++;
}

// Set total row
$sheet->mergeCells('A' . $rowNumber . ':F' . $rowNumber); // Merge cells for the "Totals" label
$sheet->setCellValue('G' . $rowNumber, 'Totals');
$sheet->setCellValue('H' . $rowNumber, $total_picks);
$sheet->setCellValue('I' . $rowNumber, $total_length);
$sheet->setCellValue('J' . $rowNumber, $total_percentage);
$sheet->setCellValue('K' . $rowNumber, $total_weaver_percentage);
$sheet->setCellValue('L' . $rowNumber, $total_stops);
$sheet->setCellValue('M' . $rowNumber, $total_stopsCMPX);
$sheet->setCellValue('N' . $rowNumber, $total_stopsTime);
$sheet->setCellValue('O' . $rowNumber, $total_filling);
$sheet->setCellValue('P' . $rowNumber, $total_fillingCMPX);
$sheet->setCellValue('Q' . $rowNumber, $total_fillingTime);
$sheet->setCellValue('R' . $rowNumber, $total_warp);
$sheet->setCellValue('S' . $rowNumber, $total_warpCMPX);
$sheet->setCellValue('T' . $rowNumber, $total_warpTime);
$sheet->setCellValue('U' . $rowNumber, $total_bobbin);
$sheet->setCellValue('V' . $rowNumber, $total_bobbinCMPX);
$sheet->setCellValue('W' . $rowNumber, $total_bobbinTime);
$sheet->setCellValue('X' . $rowNumber, $total_hand);
$sheet->setCellValue('Y' . $rowNumber, $total_handCMPX);
$sheet->setCellValue('Z' . $rowNumber, $total_handTime);
$sheet->setCellValue('AA' . $rowNumber, $total_other);
$sheet->setCellValue('AB' . $rowNumber, $total_otherCMPX);
$sheet->setCellValue('AC' . $rowNumber, $total_otherTime);
$sheet->setCellValue('AD' . $rowNumber, $total_starts);
$sheet->setCellValue('AE' . $rowNumber, $total_speed);
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
