<?php
// Include database connection
include 'dbconnect.php'; 

// Initialize the $conn variable
$db = new Connection();
$conn = $db->getConnection(); // Retrieve the PDO connection

$yarn_type = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sales_order_no = $_POST['sales_order_no'] ?? null;
    $yarn_purchase_order_no = $_POST['yarn_purchase_order_no'] ?? null;

    // Check if yarn_type is set
    if (isset($_POST['yarn_type'])) {
        $yarn_type = $_POST['yarn_type']; // Set yarn_type based on form input
    }

    // Debug: Check the POST data to ensure it's being passed correctly
echo '<pre>';
print_r($_POST);
echo '</pre>';

$warp_count = isset($_POST['warp_count']) ? $_POST['warp_count'] : null;
$warp_composition = isset($_POST['warp_composition']) ? $_POST['warp_composition'] : null;
$warp_qty = isset($_POST['warp_qty']) ? $_POST['warp_qty'] : null;
$warp_yarn_mill = isset($_POST['warp_yarn_mill']) ? $_POST['warp_yarn_mill'] : null;
$warp_delivery_schedule = isset($_POST['warp_delivery_schedule']) ? $_POST['warp_delivery_schedule'] : null;
$warp_price_per_kg = isset($_POST['warp_price_per_kg']) ? $_POST['warp_price_per_kg'] : null;
$warp_invoice_address = isset($_POST['warp_invoice_address']) ? $_POST['warp_invoice_address'] : null;
$warp_delivery_address = isset($_POST['warp_delivery_address']) ? $_POST['warp_delivery_address'] : null;
$warp_price_basis = isset($_POST['warp_price_basis']) ? $_POST['warp_price_basis'] : null;
$warp_payment_terms = isset($_POST['warp_payment_terms']) ? $_POST['warp_payment_terms'] : null;

// Check if variables are set properly
echo 'sales_order_no: ' . $sales_order_no . '<br>';
echo 'yarn_purchase_order_no: ' . $yarn_purchase_order_no . '<br>';
echo 'warp_count: ' . $warp_count . '<br>';
echo 'warp_composition: ' . $warp_composition . '<br>';
echo 'warp_qty: ' . $warp_qty . '<br>';
echo 'warp_yarn_mill: ' . $warp_yarn_mill . '<br>';
echo 'warp_delivery_schedule: ' . $warp_delivery_schedule . '<br>';
echo 'warp_price_per_kg: ' . $warp_price_per_kg . '<br>';

// Check if weft fields are set
$weft_count = isset($_POST['weft_count']) ? $_POST['weft_count'] : null;
$weft_composition = isset($_POST['weft_composition']) ? $_POST['weft_composition'] : null;
$weft_qty = isset($_POST['weft_qty']) ? $_POST['weft_qty'] : null;
$weft_yarn_mill = isset($_POST['weft_yarn_mill']) ? $_POST['weft_yarn_mill'] : null;
$weft_delivery_schedule = isset($_POST['weft_delivery_schedule']) ? $_POST['weft_delivery_schedule'] : null;
$weft_price_per_kg = isset($_POST['weft_price_per_kg']) ? $_POST['weft_price_per_kg'] : null;
$weft_invoice_address = isset($_POST['weft_invoice_address']) ? $_POST['weft_invoice_address'] : null;
$weft_delivery_address = isset($_POST['weft_delivery_address']) ? $_POST['weft_delivery_address'] : null;
$weft_price_basis = isset($_POST['weft_price_basis']) ? $_POST['weft_price_basis'] : null;
$weft_payment_terms = isset($_POST['weft_payment_terms']) ? $_POST['weft_payment_terms'] : null;


    if (empty($warp_count) || empty($warp_composition) || empty($warp_qty)) {
        echo "<p>Error: Required fields for Warp are missing.</p>";
    }
    // Prepare SQL based on the data available (warp, weft, or both)
    elseif ($yarn_type == 'warp') {
        echo "Inserting only warp data";
        // Insert only warp data
        $sql = "INSERT INTO yarn_purchase_order 
            (sales_order_no, yarn_purchase_order_no, warp_count, warp_composition, warp_qty, warp_yarn_mill, warp_delivery_schedule, warp_price_per_kg, warp_invoice_address, warp_delivery_address, warp_price_basis, warp_payment_terms) 
            VALUES 
            (:sales_order_no, :yarn_purchase_order_no, :warp_count, :warp_composition, :warp_qty, :warp_yarn_mill, :warp_delivery_schedule, :warp_price_per_kg, :warp_invoice_address, :warp_delivery_address, :warp_price_basis, :warp_payment_terms)";
        
        $stmt = $conn->prepare($sql);
        
        // Bind values for warp data
        $stmt->bindValue(':sales_order_no', $sales_order_no);
        $stmt->bindValue(':yarn_purchase_order_no', $yarn_purchase_order_no);
        $stmt->bindValue(':warp_count', $warp_count);
        $stmt->bindValue(':warp_composition', $warp_composition);
        $stmt->bindValue(':warp_qty', $warp_qty);
        $stmt->bindValue(':warp_yarn_mill', $warp_yarn_mill);
        $stmt->bindValue(':warp_delivery_schedule', $warp_delivery_schedule);
        $stmt->bindValue(':warp_price_per_kg', $warp_price_per_kg);
        $stmt->bindValue(':warp_invoice_address', $warp_invoice_address);
        $stmt->bindValue(':warp_delivery_address', $warp_delivery_address);
        $stmt->bindValue(':warp_price_basis', $warp_price_basis);
        $stmt->bindValue(':warp_payment_terms', $warp_payment_terms);

        if ($stmt->execute()) {
            echo "<p>Warp Data Successfully Inserted!</p>";
        } else {
            echo "<p>Error: " . implode(", ", $stmt->errorInfo()) . "</p>";
        }
    } elseif ($yarn_type == 'weft') {
        echo "Inserting only weft data";
        // Insert only weft data
        $sql = "INSERT INTO yarn_purchase_order 
            (sales_order_no, yarn_purchase_order_no, weft_count, weft_composition, weft_qty, weft_yarn_mill, weft_delivery_schedule, weft_price_per_kg, weft_invoice_address, weft_delivery_address, weft_price_basis, weft_payment_terms) 
            VALUES 
            (:sales_order_no, :yarn_purchase_order_no, :weft_count, :weft_composition, :weft_qty, :weft_yarn_mill, :weft_delivery_schedule, :weft_price_per_kg, :weft_invoice_address, :weft_delivery_address, :weft_price_basis, :weft_payment_terms)";
        
        $stmt = $conn->prepare($sql);
        
        // Bind values for weft data
        $stmt->bindValue(':sales_order_no', $sales_order_no);
        $stmt->bindValue(':yarn_purchase_order_no', $yarn_purchase_order_no);
        $stmt->bindValue(':weft_count', $weft_count);
        $stmt->bindValue(':weft_composition', $weft_composition);
        $stmt->bindValue(':weft_qty', $weft_qty);
        $stmt->bindValue(':weft_yarn_mill', $weft_yarn_mill);
        $stmt->bindValue(':weft_delivery_schedule', $weft_delivery_schedule);
        $stmt->bindValue(':weft_price_per_kg', $weft_price_per_kg);
        $stmt->bindValue(':weft_invoice_address', $weft_invoice_address);
        $stmt->bindValue(':weft_delivery_address', $weft_delivery_address);
        $stmt->bindValue(':weft_price_basis', $weft_price_basis);
        $stmt->bindValue(':weft_payment_terms', $weft_payment_terms);

        if ($stmt->execute()) {
            echo "<p>Weft Data Successfully Inserted!</p>";
        } else {
            echo "<p>Error: " . implode(", ", $stmt->errorInfo()) . "</p>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Yarn Purchase Order</title>
    <link rel="stylesheet" href="css/create_yarn_purchase_order.css">
    <style>
        /* Additional styling specific to Yarn_Purchase_Order */
        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }
    </style>
    <script>
    function showSection(type) {
        // Hide all sections
        document.querySelectorAll('.form-section').forEach(function(section) {
            section.style.display = 'none';
        });

        // Show the selected section
        if (type === 'warp') {
            document.getElementById('warp-section').style.display = 'block';
        } else if (type === 'weft') {
            document.getElementById('weft-section').style.display = 'block';
        }
    }
</script>
</head>
<body>
    <div class="container">
        <h2>Yarn Purchase Order</h2>
        <?php if (isset($success_message)) { ?>
            <div class="message success-message"><?php echo $success_message; ?></div>
        <?php } ?>
        <?php if (isset($error_message)) { ?>
            <div class="message error-message"><?php echo $error_message; ?></div>
        <?php } ?>

        <form method="POST" action="">
            <div class="form-row1 order-type1">
                <label></label>
                <label>
                    <input type="radio" name="yarn_type" value="warp" onclick="showSection('warp')" > Warp
                </label>
                <label>
                    <input type="radio" name="yarn_type" value="weft" onclick="showSection('weft')"> Weft
                </label>
            </div>
        <!-- Warp Section -->
        <div class="form-row order-type">
            <div id="warp-section" class="form-section">
                        <h3></h3>
                        <!-- Row 1 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sales_order_no">Sales Order No:</label>
                            <select id="sales_order_no" name="sales_order_no">
                                <option value="">Select Sales Order No:</option>
                                <?php
                                // Fetch sales order numbers from sales_order table
                                $query = "SELECT sales_order_no FROM sales_order";
                                $stmt = $conn->query($query);
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    // Use the desired format for both value and display
                                    $sales_order_no = $row['sales_order_no'];
                                    echo "<option value='" . $sales_order_no . "'>" . $sales_order_no . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="yarn_purchase_order_no">Yarn Purchase Order No:</label>
                            <select id="yarn_purchase_order_no" name="yarn_purchase_order_no">
                                <option value="">Select Yarn Purchase</option>
                                <?php
                                // Fetch purchase order numbers from yarn_purchase_order
                                $query = "SELECT id FROM yarn_purchase_order";
                                $stmt = $conn->query($query);
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    // Use the desired format for both value and display
                                    $purchase_order_no = "S0" . $row['id'] . "/YP0" . $row['id'];
                                    echo "<option value='" . $purchase_order_no . "'>" . $purchase_order_no . "</option>";
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
                            <label for="warp_qty">Warp Qty:</label>
                            <input type="number" id="warp_qty" name="warp_qty" >
                        </div>
                        <div class="form-group">
                                <label for="space"> </label>
                        </div>
                    </div>
                <!-- Row 3 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="warp_payment_terms">Payment Terms:</label>
                            <input type="text" id="warp_payment_terms" name="warp_payment_terms" >
                        </div>
                        <div class="form-group">
                            <label for="warp_price_basis">Price Basis:</label>
                            <input type="text" id="warp_price_basis" name="warp_price_basis" >
                        </div>
                        <div class="form-group">
                            <label for="warp_yarn_mill">Yarn Mill:</label>
                            <input type="text" id="warp_yarn_mill" name="warp_yarn_mill" >
                        </div>
                        <div class="form-group">
                            <label for="warp_price_per_kg">Price/Kg:</label>
                            <input type="number" step="0.01" id="warp_price_per_kg" name="warp_price_per_kg" >
                        </div>
                    </div>
                <!-- Row 4 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="warp_delivery_schedule">Delivery Schedule:</label>
                            <input type="text" id="warp_delivery_schedule" name="warp_delivery_schedule" >
                        </div>
                        <div class="form-group">
                            <label for="warp_invoice_address">Invoice Address:</label>
                            <input type="text" id="warp_invoice_address" name="warp_invoice_address" >
                        </div>
                        <div class="form-group">
                            <label for="warp_delivery_address">Delivery Address:</label>
                            <input type="text" id="warp_delivery_address" name="warp_delivery_address" >
                        </div>
                        <div class="form-group">
                                <label for="space"> </label>
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
                            <label for="yarn_purchase_order_no">Yarn Purchase Order No:</label>
                            <select id="yarn_purchase_order_no" name="yarn_purchase_order_no">
                                <option value="">Select Yarn Purchase</option>
                                <?php
                                // Fetch purchase order numbers from yarn_purchase_order
                                $query = "SELECT id FROM yarn_purchase_order";
                                $stmt = $conn->query($query);
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    // Use the desired format for both value and display
                                    $purchase_order_no = "S0" . $row['id'] . "/YP0" . $row['id'];
                                    echo "<option value='" . $purchase_order_no . "'>" . $purchase_order_no . "</option>";
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
                            <label for="weft_qty">Weft Qty:</label>
                            <input type="number" id="weft_qty" name="weft_qty" >
                        </div>
                        <div class="form-group">
                                <label for="space"> </label>
                        </div>
                    </div>
                <!-- Row 3 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="weft_payment_terms">Payment Terms:</label>
                            <input type="text" id="weft_payment_terms" name="weft_payment_terms" >
                        </div>
                        <div class="form-group">
                            <label for="weft_price_basis">Price Basis:</label>
                            <input type="text" id="weft_price_basis" name="weft_price_basis" >
                        </div>
                        <div class="form-group">
                            <label for="weft_yarn_mill">Yarn Mill:</label>
                            <input type="text" id="weft_yarn_mill" name="weft_yarn_mill" >
                        </div>
                        <div class="form-group">
                            <label for="weft_price_per_kg">Price / Kg:</label>
                            <input type="number" step="0.01" id="weft_price_per_kg" name="weft_price_per_kg" >
                        </div>
                    </div>
                <!-- Row 4 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="weft_delivery_schedule">Delivery Schedule:</label>
                            <input type="text" id="weft_delivery_schedule" name="weft_delivery_schedule" >
                        </div>
                        <div class="form-group">
                            <label for="weft_invoice_address">Invoice Address:</label>
                            <input type="text" id="weft_invoice_address" name="weft_invoice_address" >
                        </div>
                        <div class="form-group">
                            <label for="weft_delivery_address">Delivery Address:</label>
                            <input type="text" id="weft_delivery_address" name="weft_delivery_address" >
                        </div>
                        <div class="form-group">
                                <label for="space"> </label>
                        </div>
                    </div>
            </div>
    </div>
                <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>
</body>
</html>
