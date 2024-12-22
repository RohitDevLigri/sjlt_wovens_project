<?php
// Include database connection
include 'dbconnect.php';
include 'navbar.php';

// Initialize the database connection
$db = new Connection();
$conn = $db->getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prepare the SQL statement with placeholders
    $stmt = $conn->prepare("INSERT INTO yarn_supplier_details (supplier_name, address, contact_person, contact_no, supplier_gstin) 
                            VALUES (:supplier_name, :address, :contact_person, :contact_no, :supplier_gstin)");

    // Bind the parameters
    $stmt->bindParam(':supplier_name', $_POST['supplier_name']);
    $stmt->bindParam(':address', $_POST['address']);
    $stmt->bindParam(':contact_person', $_POST['contact_person']);
    $stmt->bindParam(':contact_no', $_POST['contact_no']);
    $stmt->bindParam(':supplier_gstin', $_POST['supplier_gstin']);

    // Execute the query and check for errors
    if ($stmt->execute()) {
        echo "<p class='success-message'>Yarn supplier details recorded successfully!</p>";
    } else {
        echo "<p class='error-message'>Error: " . implode(", ", $stmt->errorInfo()) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yarn Suppliers Details</title>
    <link rel="stylesheet" href="css/yarn_suppliers_details.css">
</head>
<body>

<div class="container">
    <form method="POST">
        <h2>Yarn Supplier Details</h2>
        <div class="form-row">
            <div class="form-group">
                <label for="supplier_name">Supplier Name</label>
                <input type="text" id="supplier_name" name="supplier_name" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" required>
            </div>
            <div class="form-group">
                <label for="contact_person">Contact Person</label>
                <input type="text" id="contact_person" name="contact_person" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="contact_no">Contact No</label>
                <input type="number" id="contact_no" name="contact_no" required>
            </div>
            <div class="form-group">
                <label for="supplier_gstin">Supplier GSTIN</label>
                <input type="text" id="supplier_gstin" name="supplier_gstin" required>
            </div>
        </div>
        
        <button type="submit">Submit</button>
    </form>
</div>

</body>
</html>
