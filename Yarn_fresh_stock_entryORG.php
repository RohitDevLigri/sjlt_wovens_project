<?php
include 'dbconnect.php'; // Include the database connection class
include 'navbar.php';

// Initialize a variable to store the success message
$successMessage = '';
$errorMessage = '';

// Create a new database connection
$db = new Connection();
$conn = $db->getConnection(); // Fetch the PDO connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $purchase_order_no = $_POST['purchase_order_no'];
    $warp_weft_count = $_POST['warp_weft_count'];
    $warp_weft_composition = $_POST['warp_weft_composition'];
    $warp_qty = $_POST['qty'];
    $received_qty = $_POST['received_qty'];
    $lot_no = $_POST['lot_no'];
    $mill_name = $_POST['mill_name'];
    $order_qty = $_POST['order_qty'];
    $yarn_received_qty_till_date = $_POST['yarn_received_qty_till_date'];
    $date = $_POST['date'];

    // Validate required fields
    if (empty($purchase_order_no) || empty($warp_weft_count) || empty($warp_weft_composition) || empty($warp_qty) || empty($received_qty) || empty($lot_no) || empty($mill_name) || empty($order_qty) || empty($yarn_received_qty_till_date) || empty($date)) {
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
            $sql = "INSERT INTO yarn_fresh_stock_entry (purchase_order_no, warp_weft_count, warp_weft_composition, warp_qty, received_qty, lot_no, mill_name, order_qty, yarn_received_qty_till_date, date) 
                    VALUES (:purchase_order_no, :warp_weft_count, :warp_weft_composition, :warp_qty, :received_qty, :lot_no, :mill_name, :order_qty, :yarn_received_qty_till_date, :date)";

            try {
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':purchase_order_no', $purchase_order_no);
                $stmt->bindParam(':warp_weft_count', $warp_weft_count);
                $stmt->bindParam(':warp_weft_composition', $warp_weft_composition);
                $stmt->bindParam(':warp_qty', $warp_qty);
                $stmt->bindParam(':received_qty', $received_qty);
                $stmt->bindParam(':lot_no', $lot_no);
                $stmt->bindParam(':mill_name', $mill_name);
                $stmt->bindParam(':order_qty', $order_qty);
                $stmt->bindParam(':yarn_received_qty_till_date', $yarn_received_qty_till_date);
                $stmt->bindParam(':date', $date);

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
    <script>
         // Hide the success message after 10 seconds
         document.addEventListener("DOMContentLoaded", function() {
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 10000); // 10000 ms = 10 seconds
            }
        });
    </script>
</head>
    <body>
        <!-- Display Success Message -->
        <?php if ($successMessage): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php elseif ($errorMessage): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
        <?php endif; ?>
        <div class="container">
            <h2>Yarn Fresh Stock Entry</h2>
            <form action="" method="POST" id="yarnStockForm">
                <!-- First Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="purchase_order_no">Yarn Purchase Order No</label>
                        <select id="purchase_order_no" name="purchase_order_no" required>
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
                        <label for="warp_weft_count">Warp/Weft Count</label>
                        <input type="number" id="warp_weft_count" name="warp_weft_count" required>
                    </div>
                </div>

                <!-- Second Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="warp_weft_composition">Warp/Weft Composition</label>
                        <input type="text" id="warp_weft_composition" name="warp_weft_composition" required>
                    </div>
                    <div class="form-group">
                        <label for="qty">Quantity (Qty)</label>
                        <input type="number" id="qty" name="qty" required>
                    </div>
                </div>

                <!-- Third Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="received_qty">Received Qty</label>
                        <input type="number" id="received_qty" name="received_qty" required>
                    </div>
                    <div class="form-group">
                        <label for="lot_no">Lot No</label>
                        <input type="text" id="lot_no" name="lot_no" required>
                    </div>
                </div>

                <!-- Fourth Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="mill_name">Mill Name</label>
                        <input type="text" id="mill_name" name="mill_name" required>
                    </div>
                    <div class="form-group">
                        <label for="order_qty">Order Qty</label>
                        <input type="number" id="order_qty" name="order_qty" required>
                    </div>
                </div>

                <!-- Fifth Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="yarn_received_qty_till_date">Yarn Received QTY Till Date</label>
                        <input type="number" id="yarn_received_qty_till_date" name="yarn_received_qty_till_date" required>
                    </div>
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" required>
                    </div>
                </div>
                <button type="submit" class="submit-btn">Submit</button>
            </form>
        </div>
    </body>
</html>
