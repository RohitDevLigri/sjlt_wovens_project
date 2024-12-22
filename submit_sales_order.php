<?php
include('dbconnect.php');

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to escape input data
function escape_input($data, $conn) {
    return htmlspecialchars($conn->real_escape_string($data));
}

// Print out POST data to debug form submission
echo '<pre>';
var_dump($_POST);  // Print all POST data
echo '</pre>';

// Retrieve and escape POST data
$sales_order_no = escape_input($_POST['sales_order_no'], $conn);
$order_type = escape_input($_POST['order_type'], $conn);
$price = floatval($_POST['price']);  // Assuming this is a float or decimal
$currency_type = escape_input($_POST['currency_type'], $conn);
$payment_terms = escape_input($_POST['payment_terms'], $conn);
$buyer_id = intval($_POST['buyer_id']);  // Assuming this is an integer
$date_of_confirmation = escape_input($_POST['date_of_confirmation'], $conn);
$agent_id = intval($_POST['agent_id']);  // Assuming this is an integer
$order_details = escape_input($_POST['order_details'], $conn);
$fibre_id = intval($_POST['fibre_id']);  // Assuming this is an integer
$type_of_selvedge = escape_input($_POST['type_of_selvedge'], $conn);
$selvedge_id = intval($_POST['selvedge_id']);  // Assuming this is an integer
$selvedge_width = floatval($_POST['selvedge_width']);  // Assuming this is a decimal
$selvedge_weave = escape_input($_POST['selvedge_weave'], $conn);
$inspection_type = escape_input($_POST['inspection_type'], $conn);
$inspection_standard = escape_input($_POST['inspection_standard'], $conn);
$piece_length = escape_input($_POST['piece_length'], $conn);  // Assuming this is a string
$packing_type = escape_input($_POST['packing_type'], $conn);
$freight = floatval($_POST['freight']);  // Assuming this is a decimal
$invoice_address = escape_input($_POST['invoice_address'], $conn);
$delivery_address = escape_input($_POST['delivery_address'], $conn);
$commission = floatval($_POST['commission']);  // Assuming this is a decimal
$action = escape_input($_POST['action'], $conn);
$confirmed = escape_input($_POST['confirmed'], $conn);  // Assuming this is a string or integer (0/1)
$edit = escape_input($_POST['edit'], $conn);  // Assuming this is a string or integer
$order_quantity = intval($_POST['order_qty']);  // Assuming this is an integer
$order_status = escape_input($_POST['order_status'], $conn);  // Assuming this is a string

// Prepare SQL statement
$sql = "INSERT INTO salesorder (
    sales_order_no, order_type, price, currency_type, payment_terms,
    buyer_id, date_of_confirmation, agent_id, order_details, fibre_id,
    type_of_selvedge, selvedge_id, selvedge_width, selvedge_weave, inspection_type,
    inspection_standard, piece_length, packing_type, freight, invoice_address,
    delivery_address, commission, action, confirmed, edit, order_quantity, order_status
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
)";

// Initialize prepared statement
$stmt = $conn->prepare($sql);

// Check if prepare() failed
if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}

// Bind parameters
$stmt->bind_param(
    "sssissisiisssssssississi",  // Corrected type specifiers for the fields
    $sales_order_no, $order_type, $price, $currency_type, $payment_terms,
    $buyer_id, $date_of_confirmation, $agent_id, $order_details, $fibre_id,
    $type_of_selvedge, $selvedge_id, $selvedge_width, $selvedge_weave, $inspection_type,
    $inspection_standard, $piece_length, $packing_type, $freight, $invoice_address,
    $delivery_address, $commission, $action, $confirmed, $edit, $order_quantity, $order_status
);

// Execute the statement
if ($stmt->execute()) {
    echo "Data saved successfully.";
} else {
    echo "Error: " . htmlspecialchars($stmt->error);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
