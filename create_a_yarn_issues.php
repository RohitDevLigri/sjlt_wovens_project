<?php
// Include database connection
include 'dbconnect.php';
include 'navbar.php';

// Initialize the $conn variable
$db = new Connection();
$conn = $db->getConnection(); // Retrieve the connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Use the correct query with placeholders matching the table schema
        $stmt = $conn->prepare("
            INSERT INTO yarn_issues (
                purchase_order_no, warp_weft_count, warp_weft_composition, 
                ordered_qty, available_qty, lot_no, mill_name, 
                issue_qty, issue_date
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Bind the parameters correctly
        $stmt->bindParam(1, $_POST['purchase_order_no']);
        $stmt->bindParam(2, $_POST['warp_weft_count']);
        $stmt->bindParam(3, $_POST['warp_weft_composition']);
        $stmt->bindParam(4, $_POST['ordered_qty']);
        $stmt->bindParam(5, $_POST['available_qty']);
        $stmt->bindParam(6, $_POST['lot_no']);
        $stmt->bindParam(7, $_POST['mill_name']);
        $stmt->bindParam(8, $_POST['issue_qty']);
        $stmt->bindParam(9, $_POST['issue_date']);

        // Execute the query
        if ($stmt->execute()) {
            echo "<div class='success-message'>Issue Recorded Successfully!</div>";
        } else {
            echo "<div class='error-message'>Error: " . $stmt->errorInfo()[2] . "</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='error-message'>Error: " . $e->getMessage() . "</div>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Yarn Issues</title>
    <link rel="stylesheet" href="css/create_yarn_issues.css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const successMessage = document.querySelector(".success-message");
            const errorMessage = document.querySelector(".error-message");

            if (successMessage || errorMessage) {
                setTimeout(() => {
                    if (successMessage) successMessage.style.display = "none";
                    if (errorMessage) errorMessage.style.display = "none";
                }, 10000); // 10 seconds in milliseconds
            }
        });
    </script>
</head>
<body>

<div class="container">
    <h2>Yarn Issues</h2>

    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="purchase_order_no">Yarn Purchase Order No</label>
                <input type="number" id="purchase_order_no" name="purchase_order_no" required>
            </div>
            <div class="form-group">
                <label for="warp_weft_count">Warp/Weft Count</label>
                <input type="number" id="warp_weft_count" name="warp_weft_count" required>
            </div>
            <div class="form-group">
                <label for="warp_weft_composition">Warp/Weft Composition</label>
                <input type="number" id="warp_weft_composition" name="warp_weft_composition" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="ordered_qty">Ordered Qty</label>
                <input type="text" id="ordered_qty" name="ordered_qty" required>
            </div>
            <div class="form-group">
                <label for="available_qty">Available Qty</label>
                <input type="number" id="available_qty" name="available_qty" required>
            </div>
            <div class="form-group">
                <label for="lot_no">Lot No</label>
                <input type="number" id="lot_no" name="lot_no" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="mill_name">Mill Name</label>
                <input type="text" id="mill_name" name="mill_name" required>
            </div>
            <div class="form-group">
                <label for="issue_qty">Issue Qty</label>
                <input type="number" id="issue_qty" name="issue_qty" required>
            </div>
            <div class="form-group">
                <label for="issue_date">Issue Date</label>
                <input type="number" id="issue_date" name="issue_date" required>
            </div>
        </div>

        <button type="submit" class="submit-btn">Submit</button>
    </form>
</div>

</body>
</html>
