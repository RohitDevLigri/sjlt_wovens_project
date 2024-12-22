<?php
ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

include('dbconnect.php');

$database = new Connection();
$conn = $database->getConnection();

if (!$conn) {
    die("Database connection failed.");
}

require 'vendor/autoload.php';
// require_once('/Users/DRahul/RaHuL/PHP Project/Production_Report/vendor/autoload.php');

function handle_null($value) {
    return isset($value) && $value !== '' ? $value : 'N/A';
}

function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Pagination variables
$limit = 10;
$page_a = isset($_GET['page_a']) && is_numeric($_GET['page_a']) ? (int)$_GET['page_a'] : 1;
$offset_a = ($page_a - 1) * $limit;

$page_b = isset($_GET['page_b']) && is_numeric($_GET['page_b']) ? (int)$_GET['page_b'] : 1;
$offset_b = ($page_b - 1) * $limit;

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

if (!empty($from_date) && !validate_date($from_date)) {
    die('Invalid From Date format. Please use YYYY-MM-DD.');
}
if (!empty($to_date) && !validate_date($to_date)) {
    die('Invalid To Date format. Please use YYYY-MM-DD.');
}
if (!empty($date) && !validate_date($date)) {
    die('Invalid Date format. Please use YYYY-MM-DD.');
}

if (
    empty($from_date) && empty($to_date) && empty($date) && empty($sort_code) &&
    empty($construction) && empty($machine) && empty($warp_set_no) && empty($weft_lot_no) &&
    empty($picks) && empty($length) && empty($percentage) && empty($f_stops) &&
    empty($fsph) && empty($atpfs) && empty($fcmpx) && empty($w_stops) &&
    empty($wsph) && empty($atpws) && empty($wcmpx) && empty($b_stops) &&
    empty($bsph) && empty($atpbs) && empty($bcmpx) && empty($speed)
) {
    die('No filters applied. Please apply a filter to download data.');
}

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
    error_log("Error fetching date range: " . $e->getMessage());
    die("Error fetching data.");
}

// Determine display_from_date and display_to_date based on input
if (!empty($date)) {
    // If only date is provided, use it for both From Date and To Date
    $display_from_date = $display_to_date = $date;
} elseif (!empty($from_date) && empty($to_date)) {
    // If only from_date is provided, set to_date to the latest date in the database
    $display_from_date = $from_date;
    $display_to_date = $last_date_in_db;
} elseif (empty($from_date) && !empty($to_date)) {
    // If only to_date is provided, set from_date to the earliest date in the database
    $display_from_date = $first_date_in_db;
    $display_to_date = $to_date;
} elseif (!empty($from_date) && !empty($to_date)) {
    // If both dates are provided, use them as is
    $display_from_date = $from_date;
    $display_to_date = $to_date;
} else {
    // If neither date is provided, default to 'N/A'
    $display_from_date = $display_to_date = 'N/A';
}

$sql_a = "SELECT * FROM Production_Report WHERE Shift = 'A' AND 1=1";
$sql_b = "SELECT * FROM Production_Report WHERE Shift = 'B' AND 1=1";
$params_a = [];
$params_b = [];

$date_a = "SELECT * FROM Production_Report WHERE Shift = 'A' AND 1=1";
$params_date_a = [];
$date_b = "SELECT * FROM Production_Report WHERE Shift = 'B' AND 1=1";
$params_date_b = [];

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
if (!empty($sort_code)) {
    $sql_a .= " AND Sort_code = :sort_code";
    $params_a[':sort_code'] = $sort_code;

    $date_a .= " AND Sort_code = :sort_code";
    $params_date_a[':sort_code'] = $sort_code;

    $sql_b .= " AND Sort_code = :sort_code";
    $params_b[':sort_code'] = $sort_code;

    $date_b .= " AND Sort_code = :sort_code";
    $params_date_b[':sort_code'] = $sort_code;
}
if (!empty($construction)) {
    $sql_a .= " AND Construction = :construction";
    $params_a [':construction'] = $construction;

    $date_a .= " AND Construction = :construction";
    $params_date_a [':construction'] = $construction;

    $sql_b .= " AND Construction = :construction";
    $params_b[':construction'] = $construction;

    $date_b .= " AND Construction = :construction";
    $params_date_b [':construction'] = $construction;
}
if (!empty($machine)) {
    $sql_a .= " AND Machine = :machine";
    $params_a [':machine'] = $machine;

    $date_a .= " AND Machine = :machine";
    $params_date_a[':machine'] = $machine;

    $sql_b .= " AND Machine = :machine";
    $params_b [':machine'] = $machine;

    $date_b .= " AND Machine = :machine";
    $params_date_b [':machine'] = $machine;
}
if (!empty($warp_set_no)) {
    $sql_a .= " AND Warp_Set_No = :warp_set_no";
    $params_a [':warp_set_no'] = $warp_set_no;

    $date_a .= " AND Warp_Set_No = :warp_set_no";
    $params_date_a [':warp_set_no'] = $warp_set_no;

    $sql_b .= " AND Warp_Set_No = :warp_set_no";
    $params_b [':warp_set_no'] = $warp_set_no;

    $date_b .= " AND Warp_Set_No = :warp_set_no";
    $params_date_b [':warp_set_no'] = $warp_set_no;
}
if (!empty($weft_lot_no)) {
    $sql_a .= " AND Weft_Lot_No = :weft_lot_no";
    $params_a[':weft_lot_no'] = $weft_lot_no;

    $date_a .= " AND Weft_Lot_No = :weft_lot_no";
    $params_date_a [':weft_lot_no'] = $weft_lot_no;

    $sql_b .= " AND Weft_Lot_No = :weft_lot_no";
    $params_b[':weft_lot_no'] = $weft_lot_no;

    $date_b .= " AND Weft_Lot_No = :weft_lot_no";
    $params_date_b [':weft_lot_no'] = $weft_lot_no;

}
if (!empty($picks)) {
    $sql_a .= " AND Picks = :picks";
    $params_a[':picks'] = $picks;

    $date_a .= " AND Picks = :picks";
    $params_date_a[':picks'] = $picks;

    $sql_b .= " AND Picks = :picks";
    $params_b[':picks'] = $picks;

    $date_b .= " AND Picks = :picks";
    $params_date_b[':picks'] = $picks;
}
if (!empty($length)) {
    $sql_a .= " AND Length = :length";
    $params_a[':length'] = $length;

    $date_a .= " AND Length = :length";
    $params_date_a[':length'] = $length;

    $sql_b .= " AND Length = :length";
    $params_b[':length'] = $length;

    $date_b .= " AND Length = :length";
    $params_date_b[':length'] = $length;
}
if (!empty($percentage)) {
    $sql_a .= " AND Percentage = :percentage";
    $params_a[':percentage'] = $percentage;

    $date_a .= " AND Percentage = :percentage";
    $params_date_a[':percentage'] = $percentage;

    $sql_b .= " AND Percentage = :percentage";
    $params_b[':percentage'] = $percentage;

    $date_b .= " AND Percentage = :percentage";
    $params_date_b[':percentage'] = $percentage;
}
if (!empty($f_stops)) {
    $sql_a .= " AND F_Stops = :f_stops";
    $params_a[':f_stops'] = $f_stops;

    $date_a .= " AND F_Stops = :f_stops";
    $params_date_a[':f_stops'] = $f_stops;

    $sql_b .= " AND F_Stops = :f_stops";
    $params_b[':f_stops'] = $f_stops;

    $date_b .= " AND F_Stops = :f_stops";
    $params_date_b[':f_stops'] = $f_stops;
}
if (!empty($fsph)) {
    $sql_a.= " AND Fsph = :fsph";
    $params_a[':fsph'] = $fsph;

    $date_a .= " AND Fsph = :fsph";
    $params_date_a[':fsph'] = $fsph;

    $sql_b .= " AND Fsph = :fsph";
    $params_b[':fsph'] = $fsph;

    $date_b .= " AND Fsph = :fsph";
    $params_date_b[':fsph'] = $fsph;
}
if (!empty($atpfs)) {
    $sql_a .= " AND Atpfs = :atpfs";
    $params_a[':atpfs'] = $atpfs;

    $date_a .= " AND Atpfs = :atpfs";
    $params_date_a[':atpfs'] = $atpfs;

    $sql_b .= " AND Atpfs = :atpfs";
    $params_b[':atpfs'] = $atpfs;

    $date_b .= " AND Atpfs = :atpfs";
    $params_date_b[':atpfs'] = $atpfs;
}
if (!empty($fcmpx)) {
    $sql_a .= " AND Fcmpx = :fcmpx";
    $params_a[':fcmpx'] = $fcmpx;

    $date_a .= " AND Fcmpx = :fcmpx";
    $params_date_a[':fcmpx'] = $fcmpx;

    $sql_b .= " AND Fcmpx = :fcmpx";
    $params_b[':fcmpx'] = $fcmpx;

    $date_b .= " AND Fcmpx = :fcmpx";
    $params_date_b[':fcmpx'] = $fcmpx;
}
if (!empty($w_stops)) {
    $sql_a .= " AND W_Stops = :w_stops";
    $params_a[':w_stops'] = $w_stops;

    $date_a .=" AND W_Stops = :w_stops";
    $params_date_a[':w_stops'] = $w_stops;

    $sql_b .= " AND W_Stops = :w_stops";
    $params_b[':w_stops'] = $w_stops;

    $date_b .=" AND W_Stops = :w_stops";
    $params_date_b[':w_stops'] = $w_stops;
}
if (!empty($wsph)) {
    $sql_a .= " AND Wsph = :wsph";
    $params_a[':wsph'] = $wsph;

    $date_a .= " AND Wsph = :wsph";
    $params_date_a[':wsph'] = $wsph;

    $sql_b .= " AND Wsph = :wsph";
    $params_b[':wsph'] = $wsph;

    $date_b .= " AND Wsph = :wsph";
    $params_date_b[':wsph'] = $wsph;
}
if (!empty($atpws)) {
    $sql_a .= " AND Atpws = :atpws";
    $params_a[':atpws'] = $atpws;

    $date_a .= " AND Atpws = :atpws";
    $params_date_a[':atpws'] = $atpws;

    $sql_b.= " AND Atpws = :atpws";
    $params_b[':atpws'] = $atpws;

    $date_b .= " AND Atpws = :atpws";
    $params_date_b[':atpws'] = $atpws;
}
if (!empty($wcmpx)) {
    $sql_a .= " AND Wcmpx = :wcmpx";
    $params_a[':wcmpx'] = $wcmpx;

    $date_a .= " AND Wcmpx = :wcmpx";
    $params_date_a[':wcmpx'] = $wcmpx;

    $sql_b .= " AND Wcmpx = :wcmpx";
    $params_b[':wcmpx'] = $wcmpx;

    $date_b .= " AND Wcmpx = :wcmpx";
    $params_date_b[':wcmpx'] = $wcmpx;
}
if (!empty($b_stops)) {
    $sql_a .= " AND B_Stops = :b_stops";
    $params_a[':b_stops'] = $b_stops;

    $date_a .= " AND B_Stops = :b_stops";
    $params_date_a[':b_stops'] = $b_stops;

    $sql_b .= " AND B_Stops = :b_stops";
    $params_b[':b_stops'] = $b_stops;

    $date_b .= " AND B_Stops = :b_stops";
    $params_date_b[':b_stops'] = $b_stops;
}
if (!empty($bsph)) {
    $sql_a .= " AND Bsph = :bsph";
    $params_a[':bsph'] = $bsph;

    $date_a .= " AND Bsph = :bsph";
    $params_date_a[':bsph'] = $bsph;

    $sql_b .= " AND Bsph = :bsph";
    $params_b[':bsph'] = $bsph;

    $date_b .= " AND Bsph = :bsph";
    $params_date_b[':bsph'] = $bsph;
}
if (!empty($atpbs)) {
    $sql_a .= " AND Atpbs = :atpbs";
    $params_a[':atpbs'] = $atpbs;

    $date_a .= " AND Atpbs = :atpbs";
    $params_date_a[':atpbs'] = $atpbs;

    $sql_b .= " AND Atpbs = :atpbs";
    $params_b[':atpbs'] = $atpbs;

    $date_b .= " AND Atpbs = :atpbs";
    $params_date_b[':atpbs'] = $atpbs;
}
if (!empty($bcmpx)) {
    $sql_a .= " AND Bcmpx = :bcmpx";
    $params_a[':bcmpx'] = $bcmpx;

    $date_a .= " AND Bcmpx = :bcmpx";
    $params_date_a[':bcmpx'] = $bcmpx;

    $sql_b .= " AND Bcmpx = :bcmpx";
    $params_b[':bcmpx'] = $bcmpx;

    $date_b .= " AND Bcmpx = :bcmpx";
    $params_date_b[':bcmpx'] = $bcmpx;
}
if (!empty($speed)) {
    $sql_a .= " AND Speed = :speed";
    $params_a[':speed'] = $speed;

    $date_a .= " AND Speed = :speed";
    $params_date_a[':speed'] = $speed;

    $sql_b .= " AND Speed = :speed";
    $params_b[':speed'] = $speed;

    $date_b .= " AND Speed = :speed";
    $params_date_b[':speed'] = $speed;
}

// Limit and offset for pagination for Shift A
$sql_a .= " ORDER BY Date ASC LIMIT :limit OFFSET :offset";
$params[':limit'] = $limit;
$params[':offset'] = $offset_a;

// Limit and offset for pagination for Shift B
$sql_b .= " ORDER BY Date ASC LIMIT :limit OFFSET :offset";
$params[':limit'] = $limit;
$params[':offset'] = $offset_b;

try {
    $stmt_a = $conn->prepare($sql_a);
    $stmt_a->execute($params);

    $stmt_b = $conn->prepare($sql_b);
    $stmt_b->execute($params);

    $rows_a = $stmt_a->fetchAll(PDO::FETCH_ASSOC);
    $rows_b = $stmt_b->fetchAll(PDO::FETCH_ASSOC);

    // Count total rows for Shift A to calculate total pages
    $count_stmt_a = $conn->prepare("SELECT COUNT(*) FROM Production_Report WHERE Shift = 'A' AND 1=1");
    $count_stmt_a->execute();
    $total_rows_a = $count_stmt_a->fetchColumn();
    $total_pages_a = ceil($total_rows_a / $limit);

    // Count total rows for Shift B to calculate total pages
    $count_stmt_b = $conn->prepare("SELECT COUNT(*) FROM Production_Report WHERE Shift = 'B' AND 1=1");
    $count_stmt_b->execute();
    $total_rows_b = $count_stmt_b->fetchColumn();
    $total_pages_b = ceil($total_rows_b / $limit);

} catch (PDOException $e) {
    error_log("Error executing query: " . $e->getMessage());
    die("Error fetching data.");
}

// Helper function to format date, with a fallback for null values
function formatDate($date, $fallback) {
    // Check if both $date and $fallback are not null or empty strings
    if (!empty($date)) {
        return date('d/m/Y', strtotime($date));
    } elseif (!empty($fallback)) {
        return date('d/m/Y', strtotime($fallback));
    }
    // Return a default value if both are invalid (optional)
    return 'N/A'; // Or any default value you prefer
}

// Determine Shift A dates
$formatted_from_date_a = ($display_from_date !== 'N/A') 
    ? formatDate($display_from_date, $min_date_a) 
    : formatDate($min_date_a, $min_date_a);
$formatted_to_date_a = ($display_to_date !== 'N/A') 
    ? formatDate($display_to_date, $max_date_a) 
    : formatDate($max_date_a, $max_date_a);

// If min_date_a and max_date_a are the same, ensure formatted dates are the same
if ($min_date_a === $max_date_a) {
    $formatted_from_date_a = $formatted_to_date_a = formatDate($min_date_a, $min_date_a);
}

// Determine Shift B dates
$formatted_from_date_b = ($display_from_date !== 'N/A') 
    ? formatDate($display_from_date, $min_date_b) 
    : formatDate($min_date_b, $min_date_b);
$formatted_to_date_b = ($display_to_date !== 'N/A') 
    ? formatDate($display_to_date, $max_date_b) 
    : formatDate($max_date_b, $max_date_b);

// If min_date_b and max_date_b are the same, ensure formatted dates are the same
if ($min_date_b === $max_date_b) {
    $formatted_from_date_b = $formatted_to_date_b = formatDate($min_date_b, $min_date_b);
}

$total_picks = $total_length = $total_percentage = $total_f_stops = 0;
$total_fsph = $total_atpfs = $total_fcmpx = 0;
$total_w_stops = $total_wsph = $total_atpws = $total_wcmpx = 0;
$total_b_stops = $total_bsph = $total_atpbs = $total_bcmpx = 0;
$total_speed = 0;
$total_rows =0; 

$shift_a_data = $stmt_a->fetchAll(PDO::FETCH_ASSOC);
$shift_b_data = $stmt_b->fetchAll(PDO::FETCH_ASSOC);

$pdf = new TCPDF('L');
$pdf->AddPage();

$current_date = date('d/m/Y');

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 10, 'Production Report - Date: ' . $current_date , 0, 1, 'C');
$pdf->Ln(1);

// Set date range for Shift A
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, "From Date: $formatted_from_date_a  To Date: $formatted_to_date_a  Shift: A - Page $page_a of $total_pages_a", 0, 1, 'C');
$pdf->Ln(3);

// Generate Shift A Table
$html = generateHTMLTable($shift_a_data); // Function to create the HTML table
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(3); // Add space between Shift A and Shift B

// Pagination links for Shift A
if ($total_pages_a > 1) {
    $pdf->SetFont('helvetica', 'I', 8);
    $pagination_a = "Page: ";
    for ($i = 1; $i <= $total_pages_a; $i++) {
        $pagination_a .= ($i == $page_a) ? "<b>$i</b> " : "<a href='?page_a=$i&from_date=$from_date&to_date=$to_date'>[$i]</a> ";
    }
    $pdf->Cell(0, 5, $pagination_a, 0, 1, 'C');
}

// Start a new page for Shift B
$pdf->AddPage();

// Set date range for Shift B
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, "From Date: $formatted_from_date_b  To Date: $formatted_to_date_b  Shift: B - Page $page_b of $total_pages_b", 0, 1, 'C');
$pdf->Ln(3);

// Generate Shift B Table
$html = generateHTMLTable($shift_b_data); // Function to create the HTML table
$pdf->writeHTML($html, true, false, true, false, '');

// Pagination links for Shift B
if ($total_pages_b > 1) {
    $pdf->SetFont('helvetica', 'I', 8);
    $pagination_b = "Page: ";
    for ($i = 1; $i <= $total_pages_b; $i++) {
        $pagination_b .= ($i == $page_b) ? "<b>$i</b> " : "<a href='?page_b=$i&from_date=$from_date&to_date=$to_date'>[$i]</a> ";
    }
    $pdf->Cell(0, 5, $pagination_b, 0, 1, 'C');
}

$pdf->Output('production_report.pdf', 'I');
function generateHTMLTable($data) {
    $html = '
    <style>
        table { width: 100%; border-collapse: collapse; font-size: 5px;  }
        th, td { border: 1px solid #000; padding: 12px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .total-row { font-weight: bold; }
    </style>
    <table>
        <thead>
            <tr>
                <th><b>Date</b></th><th><b>Sort Code</b></th><th><b>Construction</b></th>
                <th><b>Machine</b></th><th><b>Warp Set No</b></th><th><b>Weft Lot No</b></th>
                <th><b>Picks</b></th><th><b>Length</b></th><th><b>%</b></th><th><b>F Stops</b></th>
                <th><b>FSPH</b></th><th><b>ATPFS</b></th><th><b>FCMPX</b></th><th><b>W Stops</b></th>
                <th><b>WSPH</b></th><th><b>ATPWS</b></th><th><b>WCMPX</b></th><th><b>B Stops</b></th>
                <th><b>BSPH</b></th><th><b>ATPBS</b></th><th><b>BCMPX</b></th><th><b>Speed</b></th>
            </tr>
        </thead>
        <tbody>';

    $totals = [
        'Picks' => 0, 'Length' => 0, 'Percentage' => 0,
        'F_Stops' => 0, 'Fsph' => 0, 'Atpfs' => 0, 'Fcmpx' => 0,
        'W_Stops' => 0, 'Wsph' => 0, 'Atpws' => 0, 'Wcmpx' => 0,
        'B_Stops' => 0, 'Bsph' => 0, 'Atpbs' => 0, 'Bcmpx' => 0,
        'Speed' => 0
    ];
    $row_count = count($data);
    
    foreach ($data as $row) {
        $html .= '<tr>
            <td>' . date('d/m/Y', strtotime($row['Date'])) . '</td>
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
        foreach ($totals as $key => &$total) {
            $total += $row[$key] ?? 0;
        }
    }
    $html .= '<tr class="total-row">
    <td colspan="6">Total</td>
    <td>' . $totals['Picks'] . '</td>
    <td>' . $totals['Length'] . '</td>
    <td>' . ($row_count > 0 ? round($totals['Percentage'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['F_Stops'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['Fsph'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['Atpfs'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['Fcmpx'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['W_Stops'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['Wsph'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['Atpws'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['Wcmpx'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['B_Stops'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['Bsph'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['Atpbs'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['Bcmpx'] / $row_count, 2) : 0) . '</td>
    <td>' . ($row_count > 0 ? round($totals['Speed'] / $row_count, 2) : 0) . '</td>
</tr>';
    $html .= '</tbody></table>';
    return $html;
}
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('production_report.pdf', 'I');
?>