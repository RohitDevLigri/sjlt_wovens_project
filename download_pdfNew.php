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
require_once('/Users/DRahul/RaHuL/PHP Project/Production_Report/vendor/autoload.php');

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

// Prepare the SQL query for Shift A and B separately
$sql_a = "SELECT * FROM Production_Report WHERE Shift = 'A' AND 1=1";
$sql_b = "SELECT * FROM Production_Report WHERE Shift = 'B' AND 1=1";

$params_a = [];
$params_b = [];

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

// Apply filters to the Shift A query
if (!empty($date)) {
    $sql_a .= " AND Date = :date";
    $params_a[':date'] = $date;
} else {
    if (!empty($from_date) && !empty($to_date)) {
        $sql_a .= " AND Date BETWEEN :from_date AND :to_date";
        $params_a[':from_date'] = $from_date;
        $params_a[':to_date'] = $to_date;
    } elseif (!empty($from_date)) {
        $sql_a .= " AND Date >= :from_date";
        $params_a[':from_date'] = $from_date;
    } elseif (!empty($to_date)) {
        $sql_a .= " AND Date <= :to_date";
        $params_a[':to_date'] = $to_date;
    }
}

// Apply filters to the Shift B query similarly
if (!empty($date)) {
    $sql_b .= " AND Date = :date";
    $params_b[':date'] = $date;
} else {
    if (!empty($from_date) && !empty($to_date)) {
        $sql_b .= " AND Date BETWEEN :from_date AND :to_date";
        $params_b[':from_date'] = $from_date;
        $params_b[':to_date'] = $to_date;
    } elseif (!empty($from_date)) {
        $sql_b .= " AND Date >= :from_date";
        $params_b[':from_date'] = $from_date;
    } elseif (!empty($to_date)) {
        $sql_b .= " AND Date <= :to_date";
        $params_b[':to_date'] = $to_date;
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

// Prepare and execute the Shift A query
try {
    $stmt_a = $conn->prepare($sql_a);
    $stmt_a->execute($params_a); // Apply params for Shift A
} catch (PDOException $e) {
    // Log and handle error
    error_log("Error executing Shift A query: " . $e->getMessage());
    die("Error fetching data for Shift A.");
}

// Apply ordering by date in ascending order
$sql_a .= " ORDER BY Date ASC";
$sql_b .= " ORDER BY Date ASC";

// Prepare and execute the Shift B query
try {
    $stmt_b = $conn->prepare($sql_b);
    $stmt_b->execute($params_b); // Apply params for Shift B
} catch (PDOException $e) {
    // Log and handle error
    error_log("Error executing Shift B query: " . $e->getMessage());
    die("Error fetching data for Shift B.");
}

// Initialize totals
$total_picks = $total_length = $total_percentage = $total_f_stops = 0;
$total_fsph = $total_atpfs = $total_fcmpx = 0;
$total_w_stops = $total_wsph = $total_atpws = $total_wcmpx = 0;
$total_b_stops = $total_bsph = $total_atpbs = $total_bcmpx = 0;
$total_speed = 0;
$total_rows =0; 

// Fetch the results for Shift A and B
$shift_a_data = $stmt_a->fetchAll(PDO::FETCH_ASSOC);
$shift_b_data = $stmt_b->fetchAll(PDO::FETCH_ASSOC);

// Create a new PDF document
$pdf = new TCPDF();

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('SJLT WOVENS MILLS (P) LTD');
$pdf->SetTitle('Production Report');
$pdf->SetSubject('Production Report PDF');

// Determine which data to display and in what order
if (!empty($shift_a_data)) {
    // Add a page for Shift A
    $pdf->AddPage();

    // Set font for header
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Production Report - Shift B', 0, 1, 'C');

    // Add the date range
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(10);
    $pdf->Cell(0, 10, "From Date: $formatted_from_date  To Date: $formatted_to_date", 0, 1, 'C');
    $pdf->Ln(5);

    // Add table headers for Shift A
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(30, 10, 'Date', 1);
    $pdf->Cell(30, 10, 'Sort Code', 1);
    $pdf->Cell(30, 10, 'Construction', 1);
    $pdf->Cell(30, 10, 'Machine', 1);
    $pdf->Cell(30, 10, 'Warp Set No', 1);
    $pdf->Cell(30, 10, 'Weft Lot No', 1);
    $pdf->Cell(30, 10, 'Picks', 1);
    $pdf->Cell(30, 10, 'Length', 1);
    $pdf->Cell(30, 10, '%', 1);
    $pdf->Cell(30, 10, 'F Stops', 1);
    $pdf->Cell(30, 10, 'FSPH', 1);
    $pdf->Cell(30, 10, 'ATPFS(Sec)', 1);
    $pdf->Cell(30, 10, 'FCMPX', 1);
    $pdf->Cell(30, 10, 'W Stops', 1);
    $pdf->Cell(30, 10, 'WSPH', 1);
    $pdf->Cell(30, 10, 'ATPWS', 1);
    $pdf->Cell(30, 10, 'WCMPX', 1);
    $pdf->Cell(30, 10, 'B Stops', 1);
    $pdf->Cell(30, 10, 'BSPH', 1);
    $pdf->Cell(30, 10, 'ATPBS', 1);
    $pdf->Cell(30, 10, 'BCMPX', 1);
    $pdf->Cell(30, 10, 'SPEED', 1);
    $pdf->Ln();

    // Add Shift A data
    $pdf->SetFont('helvetica', '', 10);
    foreach ($shift_a_data as $row) {
            $pdf->Cell(30, 10, handle_null(date('d/m/Y', strtotime($row['Date']))), 1);
            $pdf->Cell(30, 10, handle_null($row['Sort_code']), 1);
            $pdf->Cell(30, 10, handle_null($row['Construction']), 1);
            $pdf->Cell(30, 10, handle_null($row['Machine']), 1);
            $pdf->Cell(30, 10, handle_null($row['Warp_Set_No']), 1);
            $pdf->Cell(30, 10, handle_null($row['Weft_Lot_No']), 1);
            $pdf->Cell(30, 10, handle_null($row['Picks']), 1);
            $pdf->Cell(30, 10, handle_null($row['Length']), 1);
            $pdf->Cell(30, 10, handle_null($row['Percentage']), 1);
            $pdf->Cell(30, 10, handle_null($row['F_Stops']), 1);
            $pdf->Cell(30, 10, handle_null($row['Fsph']), 1);
            $pdf->Cell(30, 10, handle_null($row['Atpfs']), 1);
            $pdf->Cell(30, 10, handle_null($row['Fcmpx']), 1);
            $pdf->Cell(30, 10, handle_null($row['W_Stops']), 1);
            $pdf->Cell(30, 10, handle_null($row['Wsph']), 1);
            $pdf->Cell(30, 10, handle_null($row['Atpws']), 1);
            $pdf->Cell(30, 10, handle_null($row['Wcmpx']), 1);
            $pdf->Cell(30, 10, handle_null($row['B_Stops']), 1);
            $pdf->Cell(30, 10, handle_null($row['Bsph']), 1);
            $pdf->Cell(30, 10, handle_null($row['Atpbs']), 1);
            $pdf->Cell(30, 10, handle_null($row['Bcmpx']), 1);
            $pdf->Cell(30, 10, handle_null($row['Speed']), 1);
            $pdf->Ln();

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
     // After processing all rows for Shift A
     $pdf->Cell(30, 10, 'Totals', 1);
     $pdf->Cell(30, 10, '', 1); // Placeholder for Sort Code
     $pdf->Cell(30, 10, '', 1); // Placeholder for Construction
     $pdf->Cell(30, 10, '', 1); // Placeholder for Machine
     $pdf->Cell(30, 10, '', 1); // Placeholder for Warp Set No
     $pdf->Cell(30, 10, '', 1); // Placeholder for Weft Lot No
     $pdf->Cell(30, 10, number_format($total_picks, 0), 1);
     $pdf->Cell(30, 10, number_format($total_length, 2), 1);
     $pdf->Cell(30, 10, number_format($total_percentage, 2), 1);
     $pdf->Cell(30, 10, number_format($total_f_stops, 2), 1);
     $pdf->Cell(30, 10, number_format($total_fsph, 2), 1);
     $pdf->Cell(30, 10, number_format($total_atpfs, 2), 1);
     $pdf->Cell(30, 10, number_format($total_fcmpx, 2), 1);
     $pdf->Cell(30, 10, number_format($total_w_stops, 2), 1);
     $pdf->Cell(30, 10, number_format($total_wsph, 2), 1);
     $pdf->Cell(30, 10, number_format($total_atpws, 2), 1);
     $pdf->Cell(30, 10, number_format($total_wcmpx, 2), 1);
     $pdf->Cell(30, 10, number_format($total_b_stops, 2), 1);
     $pdf->Cell(30, 10, number_format($total_bsph, 2), 1);
     $pdf->Cell(30, 10, number_format($total_atpbs, 2), 1);
     $pdf->Cell(30, 10, number_format($total_bcmpx, 2), 1);
     $pdf->Cell(30, 10, number_format($total_speed, 2), 1);
     $pdf->Ln();

    // Check if there's Shift B data
    if (!empty($shift_b_data)) {
        // Add a page for Shift B
        $pdf->AddPage();

        // Set font for Shift B header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Production Report - Shift A', 0, 1, 'C');

        // Add the date range for Shift B
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Ln(10);
        $pdf->Cell(0, 10, "From Date: $formatted_from_date  To Date: $formatted_to_date", 0, 1, 'C');
        $pdf->Ln(5);

        // Add table headers for Shift B
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(30, 10, 'Date', 1);
        $pdf->Cell(30, 10, 'Sort Code', 1);
        $pdf->Cell(30, 10, 'Construction', 1);
        $pdf->Cell(30, 10, 'Machine', 1);
        $pdf->Cell(30, 10, 'Warp Set No', 1);
        $pdf->Cell(30, 10, 'Weft Lot No', 1);
        $pdf->Cell(30, 10, 'Picks', 1);
        $pdf->Cell(30, 10, 'Length', 1);
        $pdf->Cell(30, 10, '%', 1);
        $pdf->Cell(30, 10, 'F Stops', 1);
        $pdf->Cell(30, 10, 'FSPH', 1);
        $pdf->Cell(30, 10, 'ATPFS(Sec)', 1);
        $pdf->Cell(30, 10, 'FCMPX', 1);
        $pdf->Cell(30, 10, 'W Stops', 1);
        $pdf->Cell(30, 10, 'WSPH', 1);
        $pdf->Cell(30, 10, 'ATPWS', 1);
        $pdf->Cell(30, 10, 'WCMPX', 1);
        $pdf->Cell(30, 10, 'B Stops', 1);
        $pdf->Cell(30, 10, 'BSPH', 1);
        $pdf->Cell(30, 10, 'ATPBS', 1);
        $pdf->Cell(30, 10, 'BCMPX', 1);
        $pdf->Cell(30, 10, 'SPEED', 1);
        $pdf->Ln();

        // Add Shift B data
        $pdf->SetFont('helvetica', '', 10);
        foreach ($shift_a_data as $row) {
                $pdf->Cell(30, 10, handle_null(date('d/m/Y', strtotime($row['Date']))), 1);
                $pdf->Cell(30, 10, handle_null($row['Sort_code']), 1);
                $pdf->Cell(30, 10, handle_null($row['Construction']), 1);
                $pdf->Cell(30, 10, handle_null($row['Machine']), 1);
                $pdf->Cell(30, 10, handle_null($row['Warp_Set_No']), 1);
                $pdf->Cell(30, 10, handle_null($row['Weft_Lot_No']), 1);
                $pdf->Cell(30, 10, handle_null($row['Picks']), 1);
                $pdf->Cell(30, 10, handle_null($row['Length']), 1);
                $pdf->Cell(30, 10, handle_null($row['Percentage']), 1);
                $pdf->Cell(30, 10, handle_null($row['F_Stops']), 1);
                $pdf->Cell(30, 10, handle_null($row['Fsph']), 1);
                $pdf->Cell(30, 10, handle_null($row['Atpfs']), 1);
                $pdf->Cell(30, 10, handle_null($row['Fcmpx']), 1);
                $pdf->Cell(30, 10, handle_null($row['W_Stops']), 1);
                $pdf->Cell(30, 10, handle_null($row['Wsph']), 1);
                $pdf->Cell(30, 10, handle_null($row['Atpws']), 1);
                $pdf->Cell(30, 10, handle_null($row['Wcmpx']), 1);
                $pdf->Cell(30, 10, handle_null($row['B_Stops']), 1);
                $pdf->Cell(30, 10, handle_null($row['Bsph']), 1);
                $pdf->Cell(30, 10, handle_null($row['Atpbs']), 1);
                $pdf->Cell(30, 10, handle_null($row['Bcmpx']), 1);
                $pdf->Cell(30, 10, handle_null($row['Speed']), 1);
                $pdf->Ln();


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
    }
    // After processing all rows for Shift A
    $pdf->Cell(30, 10, 'Totals', 1);
    $pdf->Cell(30, 10, '', 1); // Placeholder for Sort Code
    $pdf->Cell(30, 10, '', 1); // Placeholder for Construction
    $pdf->Cell(30, 10, '', 1); // Placeholder for Machine
    $pdf->Cell(30, 10, '', 1); // Placeholder for Warp Set No
    $pdf->Cell(30, 10, '', 1); // Placeholder for Weft Lot No
    $pdf->Cell(30, 10, number_format($total_picks, 0), 1);
    $pdf->Cell(30, 10, number_format($total_length, 2), 1);
    $pdf->Cell(30, 10, number_format($total_percentage, 2), 1);
    $pdf->Cell(30, 10, number_format($total_f_stops, 2), 1);
    $pdf->Cell(30, 10, number_format($total_fsph, 2), 1);
    $pdf->Cell(30, 10, number_format($total_atpfs, 2), 1);
    $pdf->Cell(30, 10, number_format($total_fcmpx, 2), 1);
    $pdf->Cell(30, 10, number_format($total_w_stops, 2), 1);
    $pdf->Cell(30, 10, number_format($total_wsph, 2), 1);
    $pdf->Cell(30, 10, number_format($total_atpws, 2), 1);
    $pdf->Cell(30, 10, number_format($total_wcmpx, 2), 1);
    $pdf->Cell(30, 10, number_format($total_b_stops, 2), 1);
    $pdf->Cell(30, 10, number_format($total_bsph, 2), 1);
    $pdf->Cell(30, 10, number_format($total_atpbs, 2), 1);
    $pdf->Cell(30, 10, number_format($total_bcmpx, 2), 1);
    $pdf->Cell(30, 10, number_format($total_speed, 2), 1);
    $pdf->Ln();

} elseif (!empty($shift_b_data)) {
    // Add a page for Shift B
    $pdf->AddPage();

    // Set font for Shift B header
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Production Report - Shift A', 0, 1, 'C');

    // Add the date range for Shift B
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln(10);
    $pdf->Cell(0, 10, "From Date: $formatted_from_date  To Date: $formatted_to_date", 0, 1, 'C');
    $pdf->Ln(5);

    // Add table headers for Shift B
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(30, 10, 'Date', 1);
    $pdf->Cell(30, 10, 'Sort Code', 1);
    $pdf->Cell(30, 10, 'Construction', 1);
    $pdf->Cell(30, 10, 'Machine', 1);
    $pdf->Cell(30, 10, 'Warp Set No', 1);
    $pdf->Cell(30, 10, 'Weft Lot No', 1);
    $pdf->Cell(30, 10, 'Picks', 1);
    $pdf->Cell(30, 10, 'Length', 1);
    $pdf->Cell(30, 10, '%', 1);
    $pdf->Cell(30, 10, 'F Stops', 1);
    $pdf->Cell(30, 10, 'FSPH', 1);
    $pdf->Cell(30, 10, 'ATPFS(Sec)', 1);
    $pdf->Cell(30, 10, 'FCMPX', 1);
    $pdf->Cell(30, 10, 'W Stops', 1);
    $pdf->Cell(30, 10, 'WSPH', 1);
    $pdf->Cell(30, 10, 'ATPWS', 1);
    $pdf->Cell(30, 10, 'WCMPX', 1);
    $pdf->Cell(30, 10, 'B Stops', 1);
    $pdf->Cell(30, 10, 'BSPH', 1);
    $pdf->Cell(30, 10, 'ATPBS', 1);
    $pdf->Cell(30, 10, 'BCMPX', 1);
    $pdf->Cell(30, 10, 'SPEED', 1);
    $pdf->Ln();

    // Add Shift B data
    $pdf->SetFont('helvetica', '', 10);
    foreach ($shift_a_data as $row) {
            $pdf->Cell(30, 10, handle_null(date('d/m/Y', strtotime($row['Date']))), 1);
            $pdf->Cell(30, 10, handle_null($row['Sort_code']), 1);
            $pdf->Cell(30, 10, handle_null($row['Construction']), 1);
            $pdf->Cell(30, 10, handle_null($row['Machine']), 1);
            $pdf->Cell(30, 10, handle_null($row['Warp_Set_No']), 1);
            $pdf->Cell(30, 10, handle_null($row['Weft_Lot_No']), 1);
            $pdf->Cell(30, 10, handle_null($row['Picks']), 1);
            $pdf->Cell(30, 10, handle_null($row['Length']), 1);
            $pdf->Cell(30, 10, handle_null($row['Percentage']), 1);
            $pdf->Cell(30, 10, handle_null($row['F_Stops']), 1);
            $pdf->Cell(30, 10, handle_null($row['Fsph']), 1);
            $pdf->Cell(30, 10, handle_null($row['Atpfs']), 1);
            $pdf->Cell(30, 10, handle_null($row['Fcmpx']), 1);
            $pdf->Cell(30, 10, handle_null($row['W_Stops']), 1);
            $pdf->Cell(30, 10, handle_null($row['Wsph']), 1);
            $pdf->Cell(30, 10, handle_null($row['Atpws']), 1);
            $pdf->Cell(30, 10, handle_null($row['Wcmpx']), 1);
            $pdf->Cell(30, 10, handle_null($row['B_Stops']), 1);
            $pdf->Cell(30, 10, handle_null($row['Bsph']), 1);
            $pdf->Cell(30, 10, handle_null($row['Atpbs']), 1);
            $pdf->Cell(30, 10, handle_null($row['Bcmpx']), 1);
            $pdf->Cell(30, 10, handle_null($row['Speed']), 1);
            $pdf->Ln();


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
    // After processing all rows for Shift A
    $pdf->Cell(30, 10, 'Totals', 1);
    $pdf->Cell(30, 10, '', 1); // Placeholder for Sort Code
    $pdf->Cell(30, 10, '', 1); // Placeholder for Construction
    $pdf->Cell(30, 10, '', 1); // Placeholder for Machine
    $pdf->Cell(30, 10, '', 1); // Placeholder for Warp Set No
    $pdf->Cell(30, 10, '', 1); // Placeholder for Weft Lot No
    $pdf->Cell(30, 10, number_format($total_picks, 0), 1);
    $pdf->Cell(30, 10, number_format($total_length, 2), 1);
    $pdf->Cell(30, 10, number_format($total_percentage, 2), 1);
    $pdf->Cell(30, 10, number_format($total_f_stops, 2), 1);
    $pdf->Cell(30, 10, number_format($total_fsph, 2), 1);
    $pdf->Cell(30, 10, number_format($total_atpfs, 2), 1);
    $pdf->Cell(30, 10, number_format($total_fcmpx, 2), 1);
    $pdf->Cell(30, 10, number_format($total_w_stops, 2), 1);
    $pdf->Cell(30, 10, number_format($total_wsph, 2), 1);
    $pdf->Cell(30, 10, number_format($total_atpws, 2), 1);
    $pdf->Cell(30, 10, number_format($total_wcmpx, 2), 1);
    $pdf->Cell(30, 10, number_format($total_b_stops, 2), 1);
    $pdf->Cell(30, 10, number_format($total_bsph, 2), 1);
    $pdf->Cell(30, 10, number_format($total_atpbs, 2), 1);
    $pdf->Cell(30, 10, number_format($total_bcmpx, 2), 1);
    $pdf->Cell(30, 10, number_format($total_speed, 2), 1);
    $pdf->Ln();
}
else {
    // If no data for both shifts
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'No Production Data Available for Selected Dates', 0, 1, 'C');
}

// Close and output PDF document
$pdf->Output('production_report.pdf', 'D');

// Disable output buffering and end the script
ob_end_flush();

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
            <a href="download_pdf.php?from_date=2024-09-01&to_date=" target="_blank" rel="noopener noreferrer">PDF with From Date Only</a>
            <a href="download_pdf.php?to_date=2024-09-03&from_date=" target="_blank" rel="noopener noreferrer">PDF with To Date Only</a>
            <a href="download_pdf.php?from_date=2024-09-01&to_date=2024-09-03" target="_blank" rel="noopener noreferrer">PDF with Both Dates</a>
            <a href="download_pdf.php?date=2024-09-02" target="_blank" rel="noopener noreferrer">PDF with Exact Date</a>
        </div>
    </div>
</body>
</html>
