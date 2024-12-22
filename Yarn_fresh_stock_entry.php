<?php
include 'dbconnect.php'; // Include the database connection class
include 'navbar.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize a variable to store the success message
$successMessage = '';
$errorMessage = '';

// Create a new database connection
$db = new Connection();
$conn = $db->getConnection(); // Fetch the PDO connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $purchase_order_no = $_POST['purchase_order_no'];
    $warp_count = $_POST['warp_count'];
    $warp_composition = $_POST['warp_composition'];
    $warp_received_qty = $_POST['warp_received_qty'];
    $warp_lot_no = $_POST['warp_lot_no'];
    $warp_mill_name = $_POST['warp_mill_name'];
    $warp_order_qty = $_POST['warp_order_qty'];
    $warp_yarn_received_qty_till_date = $_POST['warp_yarn_received_qty_till_date'];
    $warp_date = $_POST['warp_date'];
    $weft_count = $_POST['weft_count'];
    $weft_composition = $_POST['weft_composition'];
    $weft_received_qty = $_POST['weft_received_qty'];
    $weft_lot_no = $_POST['weft_lot_no'];
    $weft_mill_name = $_POST['weft_mill_name'];
    $weft_order_qty = $_POST['weft_order_qty'];
    $weft_date = $_POST['weft_date'];
    $weft_yarn_received_qty_till_date = $_POST['weft_yarn_received_qty_till_date'];

    // Validate required fields
    if (empty($purchase_order_no) || empty($warp_count) || empty($warp_composition) || 
        empty($warp_received_qty) || empty($warp_lot_no) || empty($warp_mill_name) || 
        empty($warp_order_qty) || empty($warp_yarn_received_qty_till_date) || 
        empty($warp_date) || empty($weft_count) || empty($weft_composition) || 
        empty($weft_received_qty) || empty($weft_lot_no) || empty($weft_mill_name) || 
        empty($weft_order_qty) || empty($weft_date) || empty($weft_yarn_received_qty_till_date)) {
        $errorMessage = "<p class='error-message'>Error: All fields are required.</p>";
    } else {
        // Validate if the purchase_order_no exists
        $check_sql = "SELECT id FROM yarn_purchase_order WHERE id = :purchase_order_no";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':purchase_order_no', $purchase_order_no);
        $check_stmt->execute();

        if ($check_stmt->rowCount() == 0) {
            $errorMessage = "<p class='error-message'>Error: Invalid Purchase Order No.</p>";
        } else {
            // Prepare SQL query to insert data into the database
            $sql = "INSERT INTO yarn_fresh_stock_entry (purchase_order_no, warp_count, warp_composition, warp_received_qty, warp_lot_no, warp_mill_name, warp_order_qty, warp_yarn_received_qty_till_date, warp_date, weft_count, weft_composition, weft_received_qty, weft_lot_no, weft_mill_name, weft_order_qty, weft_date, weft_yarn_received_qty_till_date) 
                    VALUES (:purchase_order_no, :warp_count, :warp_composition, :warp_received_qty, :warp_lot_no, :warp_mill_name, :warp_order_qty, :warp_yarn_received_qty_till_date, :warp_date, :weft_count, :weft_composition, :weft_received_qty, :weft_lot_no, :weft_mill_name, :weft_order_qty, :weft_date, :weft_yarn_received_qty_till_date)";

            try {
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':purchase_order_no', $purchase_order_no);
                $stmt->bindParam(':warp_count', $warp_count);
                $stmt->bindParam(':warp_composition', $warp_composition);
                $stmt->bindParam(':warp_received_qty', $warp_received_qty);
                $stmt->bindParam(':warp_lot_no', $warp_lot_no);
                $stmt->bindParam(':warp_mill_name', $warp_mill_name);
                $stmt->bindParam(':warp_order_qty', $warp_order_qty);
                $stmt->bindParam(':warp_yarn_received_qty_till_date', $warp_yarn_received_qty_till_date);
                $stmt->bindParam(':warp_date', $warp_date);
                $stmt->bindParam(':weft_count', $weft_count);
                $stmt->bindParam(':weft_composition', $weft_composition);
                $stmt->bindParam(':weft_received_qty', $weft_received_qty);
                $stmt->bindParam(':weft_lot_no', $weft_lot_no);
                $stmt->bindParam(':weft_mill_name', $weft_mill_name);
                $stmt->bindParam(':weft_order_qty', $weft_order_qty);
                $stmt->bindParam(':weft_yarn_received_qty_till_date', $weft_yarn_received_qty_till_date);
                $stmt->bindParam(':weft_date', $weft_date);

                if ($stmt->execute()) {
                    $successMessage = "Stock Entry Successfully Saved!";
                } else {
                    $errorMessage = "<p class='error-message'>Error: " . implode(", ", $stmt->errorInfo()) . "</p>";
                }
            } catch (PDOException $e) {
                $errorMessage = "<p class='error-message'>Error: " . $e->getMessage() . "</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yarn Fresh Stock Entry</title>
    <link rel="stylesheet" href="css/yarn_fresh_stock_entry.css">
    <style>
        /* Additional styling specific to Yarn_Purchase_Order */
        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }
    </style>
</head>
    <body>
        <div class="container">
            <h2>Yarn Fresh Stock Entry</h2>
            <?php if (isset($success_message)) { ?>
                <div class="message success-message"><?php echo $success_message; ?></div>
            <?php } ?>
            <?php if (isset($error_message)) { ?>
                <div class="message error-message"><?php echo $error_message; ?></div>
            <?php } ?>
            <form method="POST" action="">
                <div class="form-row1 order-type1">
                    <label><input type="radio" name="yarn_type" value="warp" onclick="showSection('warp')" > Warp</label>
                    <label><input type="radio" name="yarn_type" value="weft" onclick="showSection('weft')"> Weft</label>
                    <label><input type="radio" name="yarn_type" value="both" onclick="showSection('both')"> Both</label>
                </div>
                <!-- Warp Section -->
                <div class="form-row order-type" >
                    <div id="warp-section" class="form-section">
                        <!-- Row 1 -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sales_order_no">Sales Order No:</label>
                                <select id="sales_order_no" name="sales_order_no">
                                    <option value="">Select Sales Order No:</option>
                                    <option value="S01">S01</option>
                                    <option value="S02">S02</option>
                                    <option value="S03">S03</option>
                                    <option value="S04">S04</option>
                                    <option value="S05">S06</option>
                                    <option value="S06">S06</option>
                                    <option value="S07">S07</option>
                                    <option value="S08">S08</option>
                                    <option value="S09">S09</option>
                                    <option value="S010">S010</option>
                                    <option value="S011">S011</option>
                                    <option value="S012">S012</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="purchase_order_no">Yarn Purchase Order No:</label>
                                <select id="purchase_order_no" name="purchase_order_no">
                                    <option value="">Select Yarn Purchase</option>
                                    <?php
                                    // Fetch purchase order numbers from yarn_purchase_order
                                       $query = "SELECT id FROM yarn_purchase_order";
                                    $stmt = $conn->query($query);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        // Generate the option value and text
                                        $formatted_value = "S0" . $row['id'] . "/YP0" . $row['id'];
                                        echo "<option value='$formatted_value'>$formatted_value</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                    <label for="space"> </label>
                            </div>
                            <div class="form-group">
                                    <label for="space"> </label>
                            </div>
                        </div>
                        <!-- Row 2 -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="warp_count">Warp Count:</label>
                                <input type="number" id="warp_count" name="warp_count" >
                            </div>
                            <div class="form-group">
                                <label for="warp_composition">Warp Composition:</label>
                                <input type="text" id="warp_composition" name="warp_composition" >
                            </div>
                            <div class="form-group">
                                <label for="warp_order_qty">Order Qty:</label>
                                <input type="number" id="warp_order_qty" name="warp_order_qty" >
                            </div>
                            <div class="form-group">
                                <label for="warp_received_qty">Received Qty:</label>
                                <input type="text" id="warp_received_qty" name="warp_received_qty" >
                            </div>
                        </div>
                        <!-- Row 3 -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="warp_lot_no">Lot No:</label>
                                <input type="text" id="warp_lot_no" name="warp_lot_no" >
                            </div>
                            <div class="form-group">
                                <label for="warp_mill_name">Mill Name:</label>
                                <input type="text" id="warp_mill_name" name="warp_mill_name" >
                            </div>
                            <div class="form-group">
                                <label for="warp_yarn_received_qty_till_date">Yarn Received Qty Till Date:</label>
                                <input type="text" id="warp_yarn_received_qty_till_date" name="warp_yarn_received_qty_till_date" >
                            </div>
                            <div class="form-group">
                                <label for="warp_date">Date:</label>
                                <input type="text" id="warp_date" name="warp_date" >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weft Section -->
                <div id="weft-section" class="form-section">
                    <!-- Row 1 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sales_order_no">Sales Order No:</label>
                            <select id="sales_order_no" name="sales_order_no" >
                            <option value="">Select Sales Order No:</option>
                                <option value="S01">S01</option>
                                <option value="S02">S02</option>
                                <option value="S03">S03</option>
                                <option value="S04">S04</option>
                                <option value="S05">S06</option>
                                <option value="S06">S06</option>
                                <option value="S07">S07</option>
                                <option value="S08">S08</option>
                                <option value="S09">S09</option>
                                <option value="S010">S010</option>
                                <option value="S011">S011</option>
                                <option value="S012">S012</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="purchase_order_no">Yarn Purchase Order No:</label>
                            <select id="purchase_order_no" name="purchase_order_no" >
                                    <option value="">Select Yarn Purchase</option>
                                    <?php
                                    // Fetch purchase order numbers from yarn_purchase_order
                                    $query = "SELECT id FROM yarn_purchase_order";
                                    $stmt = $conn->query($query);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . "'>S0" . $row['id']  . "/YP0" .$row['id'] ."</option>";
                                    }
                                    ?>
                                </select>               
                        </div>
                        <div class="form-group">
                                <label for="space"> </label>
                        </div>
                        <div class="form-group">
                                <label for="space"> </label>
                        </div>
                    </div>
                    <!-- Row 2 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="weft_count">Weft Count:</label>
                            <input type="number" id="weft_count" name="weft_count" >
                        </div>
                        <div class="form-group">
                            <label for="weft_composition">Weft Composition:</label>
                            <input type="text" id="weft_composition" name="weft_composition" >
                        </div>
                        <div class="form-group">
                            <label for="weft_order_qty">Order Qty:</label>
                            <input type="number" id="weft_order_qty" name="weft_order_qty" >
                        </div>
                        <div class="form-group">
                            <label for="weft_received_qty">Received Qty:</label>
                            <input type="text" id="weft_received_qty" name="weft_received_qty" >
                        </div>
                    </div>
                    <!-- Row 3 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="weft_lot_no">Lot No:</label>
                            <input type="text" id="weft_lot_no" name="weft_lot_no" >
                        </div>
                        <div class="form-group">
                            <label for="weft_mill_name">Mill Name:</label>
                            <input type="text" id="weft_mill_name" name="weft_mill_name" >
                        </div>
                        <div class="form-group">
                            <label for="weft_yarn_received_qty_till_date">Yarn Received Qty Till Date:</label>
                            <input type="text" id="weft_yarn_received_qty_till_date" name="weft_yarn_received_qty_till_date" >
                        </div>
                        <div class="form-group">
                            <label for="weft_date">Date:</label>
                            <input type="text" id="weft_date" name="weft_date" >
                        </div>
                    </div>
                </div>
                <!-- Both Section -->
                <div id="both-section" class="form-section">
                    <!-- Row 1 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sales_order_no">Sales Order No:</label>
                            <select id="sales_order_no" name="sales_order_no" >
                            <option value="">Select Sales Order No:</option>
                                <option value="S01">S01</option>
                                <option value="S02">S02</option>
                                <option value="S03">S03</option>
                                <option value="S04">S04</option>
                                <option value="S05">S06</option>
                                <option value="S06">S06</option>
                                <option value="S07">S07</option>
                                <option value="S08">S08</option>
                                <option value="S09">S09</option>
                                <option value="S010">S010</option>
                                <option value="S011">S011</option>
                                <option value="S012">S012</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="purchase_order_no">Yarn Purchase Order No:</label>
                            <select id="purchase_order_no" name="purchase_order_no" >
                                    <option value="">Select Yarn Purchase</option>
                                    <?php
                                    // Fetch purchase order numbers from yarn_purchase_order
                                    $query = "SELECT id FROM yarn_purchase_order";
                                    $stmt = $conn->query($query);
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . "'>S0" . $row['id']  . "/YP0" .$row['id'] ."</option>";
                                    }
                                    ?>
                                </select>                
                        </div>
                        <div class="form-group">
                                <label for="space"> </label>
                        </div>
                        <div class="form-group">
                                <label for="space"> </label>
                        </div>
                    </div>
                    <!-- Row 2 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="warp_count">Warp Count:</label>
                            <input type="number" id="warp_count" name="warp_count" >
                        </div>
                        <div class="form-group">
                            <label for="warp_composition">Warp Composition:</label>
                            <input type="text" id="warp_composition" name="warp_composition" >
                        </div>
                        <div class="form-group">
                            <label for="warp_order_qty">Order Qty:</label>
                            <input type="number" id="warp_order_qty" name="warp_order_qty" >
                        </div>
                        <div class="form-group">
                            <label for="warp_received_qty">Received Qty:</label>
                            <input type="text" id="warp_received_qty" name="warp_received_qty" >
                        </div>
                    </div>
                    <!-- Row 3 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="warp_lot_no">Lot No:</label>
                            <input type="text" id="warp_lot_no" name="warp_lot_no" >
                        </div>
                        <div class="form-group">
                            <label for="warp_mill_name">Mill Name:</label>
                            <input type="text" id="warp_mill_name" name="warp_mill_name" >
                        </div>
                        <div class="form-group">
                            <label for="warp_yarn_received_qty_till_date">Yarn Received Qty Till Date:</label>
                            <input type="text" id="warp_yarn_received_qty_till_date" name="warp_yarn_received_qty_till_date" >
                        </div>
                        <div class="form-group">
                            <label for="warp_date">Date:</label>
                            <input type="text" id="warp_date" name="warp_date" >
                        </div>
                    </div>
                    <!-- Row 4 -->
                    <!-- Weft Inputs -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="weft_count">Weft Count:</label>
                            <input type="number" id="weft_count" name="weft_count" >
                        </div>
                        <div class="form-group">
                            <label for="weft_composition">Weft Composition:</label>
                            <input type="text" id="weft_composition" name="weft_composition" >
                        </div>
                        <div class="form-group">
                            <label for="weft_order_qty">Order Qty:</label>
                            <input type="number" id="weft_order_qty" name="weft_order_qty" >
                        </div>
                        <div class="form-group">
                            <label for="weft_received_qty">Received Qty:</label>
                            <input type="text" id="weft_received_qty" name="weft_received_qty" >
                        </div>
                    </div>
                    <!-- Row 5 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="weft_lot_no">Lot No:</label>
                            <input type="text" id="weft_lot_no" name="weft_lot_no" >
                        </div>
                        <div class="form-group">
                            <label for="weft_mill_name">Mill Name:</label>
                            <input type="text" id="weft_mill_name" name="weft_mill_name" >
                        </div>
                        <div class="form-group">
                            <label for="weft_yarn_received_qty_till_date">Yarn Received Qty Till Date:</label>
                            <input type="text" id="weft_yarn_received_qty_till_date" name="weft_yarn_received_qty_till_date" >
                        </div>
                        <div class="form-group">
                            <label for="weft_date">Date:</label>
                            <input type="text" id="weft_date" name="weft_date" >
                        </div>
                    </div>
                </div>
                    <button type="submit" class="submit-btn">Submit</button>
            </form>
        </div>
        <script>
            function showSection(type) {
            // Hide all sections
                document.querySelectorAll('.form-section').forEach(section => {
                    section.style.display = 'none'; // Hide all sections
                });

                // Show the selected section
                if (type === 'warp') {
                    document.getElementById('warp-section').style.display = 'block';
                } else if (type === 'weft') {
                    document.getElementById('weft-section').style.display = 'block';
                } else if (type === 'both') {
                    document.getElementById('warp-section').style.display = 'block';
                    document.getElementById('weft-section').style.display = 'block';
                }
            }
        </script>
    </body>
</html>
