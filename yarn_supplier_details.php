<?php
// Include database connection
include 'dbconnect.php';
include 'navbar.php';

// Initialize the database connection
$db = new Connection();
$conn = $db->getConnection();

// Initialize a variable to store the success message
$successMessage = '';
$errorMessage = '';

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
        $successMessage = "Yarn Supplier Details Recorded Successfully!";
    } else {
       $errorMessage ="<p class='error-message'>Error: " . implode(", ", $stmt->errorInfo()) . "</p>";
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
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" id="contact_person" name="contact_person" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_no">Contact No</label>
                        <input type="number" id="contact_no" name="contact_no" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="supplier_gstin">Supplier GSTIN</label>
                        <input type="text" id="supplier_gstin" name="supplier_gstin" required>
                    </div>
                </div>
                <div class="form-row">
                    <button type="submit" class="submit-btn">Submit</button>
                </div>
            </form>
        </div>
    </body>
</html>
