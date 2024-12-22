<?php
// Include database connection
include 'dbconnect.php'; 
include 'navbar.php';

// Initialize the $conn variable
$db = new Connection();
$conn = $db->getConnection(); // Retrieve the connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("INSERT INTO yarn_purchase_order (yarn_purchase, sales_order_no, yarn_purchase_order_no, warp_count, warp_composition, qty, yarn_mill, 
        delivery_schedule, price_per_kg, invoice_address, delivery_address, weft_count, weft_composition, qty_weft, 
        delivery_schedule_weft, price_per_kg_weft, yarn_mill_weft, invoice_address_weft, delivery_address_weft) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bindParam(1, $_POST['yarn_purchase']);
    $stmt->bindParam(2, $_POST['sales_order_no']);
    $stmt->bindParam(3, $_POST['yarn_purchase_order_no']);
    $stmt->bindParam(4, $_POST['warp_count']);
    $stmt->bindParam(5, $_POST['warp_composition']);
    $stmt->bindParam(6, $_POST['qty']);
    $stmt->bindParam(7, $_POST['yarn_mill']);
    $stmt->bindParam(8, $_POST['delivery_schedule']);
    $stmt->bindParam(9, $_POST['price_per_kg']);
    $stmt->bindParam(10, $_POST['invoice_address']);
    $stmt->bindParam(11, $_POST['delivery_address']);
    $stmt->bindParam(12, $_POST['weft_count']);
    $stmt->bindParam(13, $_POST['weft_composition']);
    $stmt->bindParam(14, $_POST['qty_weft']);
    $stmt->bindParam(15, $_POST['delivery_schedule_weft']);
    $stmt->bindParam(16, $_POST['price_per_kg_weft']);
    $stmt->bindParam(17, $_POST['yarn_mill_weft']);
    $stmt->bindParam(18, $_POST['invoice_address_weft']);
    $stmt->bindParam(19, $_POST['delivery_address_weft']);

    if ($stmt->execute()) {
        echo "<p class='success-message' id='successMessage'>Data Successfully Inserted!</p>";
        echo "<script>
            setTimeout(function() {
                document.getElementById('successMessage').style.display = 'none';
            }, 10000); // Hide the success message after 10 seconds
        </script>";
    } else {
        echo "<p class='error-message'>Error: " . $stmt->errorInfo()[2] . "</p>";
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
</head>
<body>

<div class="container">
    <h2>Yarn Purchase Order</h2>

    <form method="POST">
        <!-- Row 1 -->
        <div class="form-row">
            <div class="form-group">
                <label for="yarn_purchase">Yarn Purchase:</label>
                <select id="yarn_purchase" name="yarn_purchase" required>
                    <option value="">Select</option>
                    <option value="Option1">Option 1</option>
                    <option value="Option2">Option 2</option>
                    <option value="Option3">Option 3</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sales_order_no">Sales Order No:</label>
                <input type="text" id="sales_order_no" name="sales_order_no" required>
            </div>
            <div class="form-group">
                <label for="yarn_purchase_order_no">Yarn Purchase Order No:</label>
                <input type="text" id="yarn_purchase_order_no" name="yarn_purchase_order_no" required>
            </div>
            <div class="form-group">
                <label for="warp_count">Warp Count:</label>
                <input type="number" id="warp_count" name="warp_count" required>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="form-row">
            <div class="form-group">
                <label for="warp_composition">Warp Composition:</label>
                <input type="text" id="warp_composition" name="warp_composition" required>
            </div>
            <div class="form-group">
                <label for="qty">Warp Qty:</label>
                <input type="number" id="qty" name="qty" required>
            </div>
            <div class="form-group">
                <label for="yarn_mill">Yarn Mill:</label>
                <input type="text" id="yarn_mill" name="yarn_mill" required>
            </div>
            <div class="form-group">
                <label for="delivery_schedule">Delivery Schedule:</label>
                <input type="date" id="delivery_schedule" name="delivery_schedule" required>
            </div>
        </div>

        <!-- Row 3 -->
        <div class="form-row">
            <div class="form-group">
                <label for="price_per_kg">Price / Kg:</label>
                <input type="number" step="0.01" id="price_per_kg" name="price_per_kg" required>
            </div>
            <div class="form-group">
                <label for="invoice_address">Invoice Address:</label>
                <input type="text" id="invoice_address" name="invoice_address" required>
            </div>
            <div class="form-group">
                <label for="delivery_address">Delivery Address:</label>
                <input type="text" id="delivery_address" name="delivery_address" required>
            </div>
            <div class="form-group">
                <label for="weft_count">Weft Count:</label>
                <input type="number" id="weft_count" name="weft_count" required>
            </div>
        </div>

        <!-- Row 4 -->
        <div class="form-row">
            <div class="form-group">
                <label for="weft_composition">Weft Composition:</label>
                <input type="text" id="weft_composition" name="weft_composition" required>
            </div>
            <div class="form-group">
                <label for="qty_weft">Weft Qty:</label>
                <input type="number" id="qty_weft" name="qty_weft" required>
            </div>
            <div class="form-group">
                <label for="delivery_schedule_weft">Weft Delivery Schedule:</label>
                <input type="date" id="delivery_schedule_weft" name="delivery_schedule_weft" required>
            </div>
            <div class="form-group">
                <label for="price_per_kg_weft">Weft Price / Kg:</label>
                <input type="number" step="0.01" id="price_per_kg_weft" name="price_per_kg_weft" required>
            </div>
        </div>

        <!-- Row 5 -->
        <div class="form-row">
            <div class="form-group">
                <label for="yarn_mill_weft">Weft Yarn Mill:</label>
                <input type="text" id="yarn_mill_weft" name="yarn_mill_weft" required>
            </div>
            <div class="form-group">
                <label for="invoice_address_weft">Weft Invoice Address:</label>
                <input type="text" id="invoice_address_weft" name="invoice_address_weft" required>
            </div>
            <div class="form-group">
                <label for="delivery_address_weft">Weft Delivery Address:</label>
                <input type="text" id="delivery_address_weft" name="delivery_address_weft" required>
            </div>
            <div class="form-group">
                <label for="space"> </label>
            </div>
        </div>
        <button type="submit" class="submit-btn">Submit</button>
    </form>
</div>

</body>
</html>
