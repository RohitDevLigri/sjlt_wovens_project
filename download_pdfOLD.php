<?php
// Enable output buffering
ob_start();

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to a file instead of output (ensure 'error_log.txt' is writable)
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// Include database connection
include('dbconnect.php');

// Create a new instance of the Connection class and get the PDO connection
$database = new Connection();
$conn = $database->getConnection(); // Get the PDO connection

// Ensure that the connection is established
if (!$conn) {
    die("Database connection failed.");
}

// Include TCPDF library using Composer's autoloader
require_once(__DIR__ . '../vendor/autoload.php'); // Ensure the path is correct

// Function to handle null or empty values
function handle_null($value) {
    return isset($value) && $value !== '' ? $value : 'N/A';
}

// Function to validate date format (YYYY-MM-DD)
function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Get search filter parameters from the URL
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

// Validate date formats
if (!empty($from_date) && !validate_date($from_date)) {
    die('Invalid From Date format. Please use YYYY-MM-DD.');
}

if (!empty($to_date) && !validate_date($to_date)) {
    die('Invalid To Date format. Please use YYYY-MM-DD.');
}

if (!empty($date) && !validate_date($date)) {
    die('Invalid Date format. Please use YYYY-MM-DD.');
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
    // If no filters are applied, do not proceed with the PDF download
    die('No filters applied. Please apply a filter to download data.');
}

// Fetch the earliest and latest dates from the database
try {
    $date_query = "SELECT MIN(Date) AS first_date, MAX(Date) AS last_date FROM Production_Report";
    $date_stmt = $conn->prepare($date_query);
    $date_stmt->execute();
    $date_result = $date_stmt->fetch(PDO::FETCH_ASSOC);
    $first_date_in_db = $date_result['first_date'] ?? null;
    $last_date_in_db = $date_result['last_date'] ?? null;

    if (!$first_date_in_db || !$last_date_in_db) {
        die("No date records found in the database.");
    }
} catch (PDOException $e) {
    // Log and handle error
    error_log("Error fetching date range: " . $e->getMessage());
    die("Error fetching data.");
}

// Determine display_from_date and display_to_date based on input
if (!empty($date)) {
    // If exact date is provided, set both From Date and To Date to this date
    $display_from_date = $date;
    $display_to_date = $date;
} elseif (!empty($from_date) && empty($to_date)) {
    // If from_date is provided and to_date is not, set to_date to the latest date in the database
    $display_from_date = $from_date;
    $display_to_date = $last_date_in_db;
} elseif (empty($from_date) && !empty($to_date)) {
    // If to_date is provided and from_date is not, set from_date to the earliest date in the database
    $display_from_date = $first_date_in_db;
    $display_to_date = $to_date;
} elseif (!empty($from_date) && !empty($to_date)) {
    // If both dates are provided, use them as is
    $display_from_date = $from_date;
    $display_to_date = $to_date;
} else {
    // If neither date is provided (shouldn't reach here due to earlier check), set both to 'N/A'
    $display_from_date = 'N/A';
    $display_to_date = 'N/A';
}

// Format the display dates for the PDF
$formatted_from_date = ($display_from_date !== 'N/A') ? date('d/m/Y', strtotime($display_from_date)) : 'N/A';
$formatted_to_date = ($display_to_date !== 'N/A') ? date('d/m/Y', strtotime($display_to_date)) : 'N/A';

// Prepare the SQL query based on the filters
$sql = "SELECT * FROM Production_Report WHERE 1=1";
$params = [];

// Apply exact date filter if 'date' is provided
if (!empty($date)) {
    $sql .= " AND Date = :date";
    $params[':date'] = $date;
} else {
    // Apply date range filters
    if (!empty($from_date) && !empty($to_date)) {
        $sql .= " AND Date BETWEEN :from_date AND :to_date";
        $params[':from_date'] = $from_date;
        $params[':to_date'] = $to_date;
    } elseif (!empty($from_date)) {
        $sql .= " AND Date >= :from_date";
        $params[':from_date'] = $from_date;
    } elseif (!empty($to_date)) {
        $sql .= " AND Date <= :to_date";
        $params[':to_date'] = $to_date;
    }
}

// Apply other filters
if (!empty($sort_code)) {
    $sql .= " AND Sort_code = :sort_code";
    $params[':sort_code'] = $sort_code;
}
if (!empty($construction)) {
    $sql .= " AND Construction = :construction";
    $params[':construction'] = $construction;
}
if (!empty($machine)) {
    $sql .= " AND Machine = :machine";
    $params[':machine'] = $machine;
}
if (!empty($warp_set_no)) {
    $sql .= " AND Warp_Set_No = :warp_set_no";
    $params[':warp_set_no'] = $warp_set_no;
}
if (!empty($weft_lot_no)) {
    $sql .= " AND Weft_Lot_No = :weft_lot_no";
    $params[':weft_lot_no'] = $weft_lot_no;
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
if (!empty($f_stops)) {
    $sql .= " AND F_Stops = :f_stops";
    $params[':f_stops'] = $f_stops;
}
if (!empty($fsph)) {
    $sql .= " AND Fsph = :fsph";
    $params[':fsph'] = $fsph;
}
if (!empty($atpfs)) {
    $sql .= " AND Atpfs = :atpfs";
    $params[':atpfs'] = $atpfs;
}
if (!empty($fcmpx)) {
    $sql .= " AND Fcmpx = :fcmpx";
    $params[':fcmpx'] = $fcmpx;
}
if (!empty($w_stops)) {
    $sql .= " AND W_Stops = :w_stops";
    $params[':w_stops'] = $w_stops;
}
if (!empty($wsph)) {
    $sql .= " AND Wsph = :wsph";
    $params[':wsph'] = $wsph;
}
if (!empty($atpws)) {
    $sql .= " AND Atpws = :atpws";
    $params[':atpws'] = $atpws;
}
if (!empty($wcmpx)) {
    $sql .= " AND Wcmpx = :wcmpx";
    $params[':wcmpx'] = $wcmpx;
}
if (!empty($b_stops)) {
    $sql .= " AND B_Stops = :b_stops";
    $params[':b_stops'] = $b_stops;
}
if (!empty($bsph)) {
    $sql .= " AND Bsph = :bsph";
    $params[':bsph'] = $bsph;
}
if (!empty($atpbs)) {
    $sql .= " AND Atpbs = :atpbs";
    $params[':atpbs'] = $atpbs;
}
if (!empty($bcmpx)) {
    $sql .= " AND Bcmpx = :bcmpx";
    $params[':bcmpx'] = $bcmpx;
}
if (!empty($speed)) {
    $sql .= " AND Speed = :speed";
    $params[':speed'] = $speed;
}

// Apply ordering by date in ascending order
$sql .= " ORDER BY Date ASC";

// Prepare and execute the query
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
} catch (PDOException $e) {
    // Log and handle error
    error_log("Error executing query: " . $e->getMessage());
    die("Error fetching data.");
}

// Initialize totals
$total_picks = $total_length = $total_percentage = $total_f_stops = 0;
$total_fsph = $total_atpfs = $total_fcmpx = 0;
$total_w_stops = $total_wsph = $total_atpws = $total_wcmpx = 0;
$total_b_stops = $total_bsph = $total_atpbs = $total_bcmpx = 0;
$total_speed = 0;
$total_rows = 0;

// Create new PDF document using TCPDF
$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('SJLT WOVENS MILLS (P) LTD');
$pdf->SetTitle('Production Report');
$pdf->SetSubject('Production Report PDF');
$pdf->SetKeywords('Production, Report, PDF');

// Set header data (Logo, title, etc.)
$logoPath = '../images/SJLT-logo.png'; // Ensure the path is correct
$logoWidth = 15; // Width of the logo in mm

if (file_exists($logoPath)) {
    // Add logo to the header (logo path, logo width, header title, header string)
    $pdf->SetHeaderData($logoPath, $logoWidth, 'Loom Production Report', 'SJLT WOVENS MILLS (P) LTD');
} else {
    // If logo doesn't exist, set header without logo
    $pdf->SetHeaderData('', 0, 'Loom Production Report', 'SJLT WOVENS MILLS (P) LTD');
}

// Set header and footer fonts
$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

// Set margins
$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Display From Date and To Date before the table
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, "From Date: $formatted_from_date   To Date: $formatted_to_date", 0, 1, 'C');

// Build the HTML content for the PDF
$html = '<h2 style="text-align: center;">Production Report</h2>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" style="font-size: 7px; border-collapse: collapse;">';
$html .= '<tr style="font-weight: bold; text-align: center;">
    <th width="50">Date</th>
    <th width="50">Sort Code</th>
    <th width="80">Construction</th>
    <th width="50">Machine</th>
    <th width="50">Warp Set No</th>
    <th width="50">Weft Lot No</th>
    <th width="40">Picks</th>
    <th width="40">Length</th>
    <th width="40">%</th>
    <th width="40">F Stops</th>
    <th width="40">Fsph</th>
    <th width="40">Atpfs</th>
    <th width="40">Fcmpx</th>
    <th width="40">W Stops</th>
    <th width="40">Wsph</th>
    <th width="40">Atpws</th>
    <th width="40">Wcmpx</th>
    <th width="40">B Stops</th>
    <th width="40">Bsph</th>
    <th width="40">Atpbs</th>
    <th width="40">Bcmpx</th>
    <th width="40">Speed</th>
</tr>';

// Check if rows were returned
if ($stmt->rowCount() == 0) {
    // Handle case when no records are found
    $html .= '<tr><td colspan="21" style="text-align: center;">No records found for the specified date range.</td></tr>';
} else {
    // Fetch the data from the database
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format the date to DD/MM/YYYY
        $formatted_date = date('d/m/Y', strtotime($row['Date']));

        // Build each row of data with formatted date
        $html .= '<tr>
            <td>' . handle_null($formatted_date) . '</td>
            <td>' . handle_null($row['Sort_code']) . '</td>
            <td>' . handle_null($row['Construction']) . '</td>
            <td>' . handle_null($row['Machine']) . '</td>
            <td>' . handle_null($row['Warp_Set_No']) . '</td>
            <td>' . handle_null($row['Weft_Lot_No']) . '</td>
            <td>' . handle_null($row['Picks']) . '</td>
            <td>' . handle_null($row['Length']) . '</td>
            <td>' . handle_null($row['Percentage']) . '</td>
            <td>' . handle_null($row['F_Stops']) . '</td>
            <td>' . handle_null($row['Fsph']) . '</td>
            <td>' . handle_null($row['Atpfs']) . '</td>
            <td>' . handle_null($row['Fcmpx']) . '</td>
            <td>' . handle_null($row['W_Stops']) . '</td>
            <td>' . handle_null($row['Wsph']) . '</td>
            <td>' . handle_null($row['Atpws']) . '</td>
            <td>' . handle_null($row['Wcmpx']) . '</td>
            <td>' . handle_null($row['B_Stops']) . '</td>
            <td>' . handle_null($row['Bsph']) . '</td>
            <td>' . handle_null($row['Atpbs']) . '</td>
            <td>' . handle_null($row['Bcmpx']) . '</td>
            <td>' . handle_null($row['Speed']) . '</td>
        </tr>';

        // Update totals
        $total_picks += $row['Picks'] ?? 0;
        $total_length += $row['Length'] ?? 0;
        $total_percentage += $row['Percentage'] ?? 0;
        $total_f_stops += $row['F_Stops'] ?? 0;
        $total_fsph += $row['Fsph'] ?? 0;
        $total_atpfs += $row['Atpfs'] ?? 0;
        $total_fcmpx += $row['Fcmpx'] ?? 0;
        $total_w_stops += $row['W_Stops'] ?? 0;
        $total_wsph += $row['Wsph'] ?? 0;
        $total_atpws += $row['Atpws'] ?? 0;
        $total_wcmpx += $row['Wcmpx'] ?? 0;
        $total_b_stops += $row['B_Stops'] ?? 0;
        $total_bsph += $row['Bsph'] ?? 0;
        $total_atpbs += $row['Atpbs'] ?? 0;
        $total_bcmpx += $row['Bcmpx'] ?? 0;
        $total_speed += $row['Speed'] ?? 0;

        $total_rows++; // Increment total rows counter
    }

    // Add totals row to the table
    $html .= '<tr style="font-weight: bold; text-align: center;">
        <td colspan="6">Totals</td>
        <td>' . number_format($total_picks, 0) . '</td>
        <td>' . number_format($total_length, 2) . '</td>
        <td>' . number_format($total_percentage, 2) . '</td>
        <td>' . number_format($total_f_stops, 2) . '</td>
        <td>' . number_format($total_fsph, 2) . '</td>
        <td>' . number_format($total_atpfs, 2) . '</td>
        <td>' . number_format($total_fcmpx, 2) . '</td>
        <td>' . number_format($total_w_stops, 2) . '</td>
        <td>' . number_format($total_wsph, 2) . '</td>
        <td>' . number_format($total_atpws, 2) . '</td>
        <td>' . number_format($total_wcmpx, 2) . '</td>
        <td>' . number_format($total_b_stops, 2) . '</td>
        <td>' . number_format($total_bsph, 2) . '</td>
        <td>' . number_format($total_atpbs, 2) . '</td>
        <td>' . number_format($total_bcmpx, 2) . '</td>
        <td>' . number_format($total_speed, 2) . '</td>
    </tr>';
}

// Close the HTML table
$html .= '</table>';

// Write the HTML content to the PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output the PDF document to the browser
$pdf->Output('production_report.pdf', 'I');

// Close the database connection
$conn = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Download PDF</title>
    <style>
        /* Basic styling for the dropdown */
        .dropdown {
            position: relative;
            display: inline-block;
            margin: 50px;
        }

        .btn-download {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 220px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid #ddd;
        }

        .dropdown-content a:last-child {
            border-bottom: none;
        }

        .dropdown-content a:hover {background-color: #f1f1f1}

        .dropdown:hover .dropdown-content {display: block;}

        .dropdown:hover .btn-download {background-color: #3e8e41;}
    </style>
</head>
<body>
    <div class="dropdown">
        <button type="button" class="btn-download">Download <span class="arrow">&#x25BC;</span></button>
        <div class="dropdown-content">
            <!-- 
                Example links for testing:
                1. Only From Date
                2. Only To Date
                3. Both From and To Dates
                4. Exact Date
            -->
            <a href="download_pdf.php?from_date=2024-09-01&to_date=" target="_blank" rel="noopener noreferrer">PDF with From Date Only</a>
            <a href="download_pdf.php?to_date=2024-09-03&from_date=" target="_blank" rel="noopener noreferrer">PDF with To Date Only</a>
            <a href="download_pdf.php?from_date=2024-09-01&to_date=2024-09-03" target="_blank" rel="noopener noreferrer">PDF with Both Dates</a>
            <a href="download_pdf.php?date=2024-09-02" target="_blank" rel="noopener noreferrer">PDF with Exact Date</a>
        </div>
    </div>
</body>
</html>