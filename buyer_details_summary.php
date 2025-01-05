<?php
// Start output buffering
ob_start();

require_once 'dbconnect_master.php';
$connection = new MasterConnection();
$conn = $connection->getConnection();

$message = ''; // Initialize the message variable

// Fetch all buyer details from the database
try {
    $stmt = $conn->query("SELECT * FROM buyer_details");
    $buyers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = '<div class="error">Error fetching data: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    try {
        // Ensure $_POST['selected_ids'] is an array
        $selected_ids = isset($_POST['selected_ids']) ? explode(',', $_POST['selected_ids']) : [];
        
        if (!empty($selected_ids)) {
            // Delete the selected buyer details from the database
            $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
            $sql = "DELETE FROM buyer_details WHERE s1_no IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($selected_ids);
            $message = '<div class="success">Selected buyer details deleted successfully!</div>';

            // Redirect to the same page to refresh the data
            header("Location: " . $_SERVER['PHP_SELF']);
            exit; // Always call exit after header redirection to stop further execution
        } else {
            $message = '<div class="error">No buyer details selected for deletion.</div>';
        }
    } catch (PDOException $e) {
        $message = '<div class="error">Error deleting data: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/agent_buyer_sort_summary.css">
    <title>Buyer Summary</title>
    <?php include 'navbar.php';?>
</head>
<body>
    <main>
        <div class="container">
            <h1>Buyer Summary</h1>
            <?php if (!empty($message)) echo $message; ?>

            <!-- Buttons for select all, add buyer, and delete -->
            <div class="buttons-container">
                <button type="button" onclick="location.href='create_buyer_details.php';" class="buyerBtn">Add</button> <!-- Add Buyer Button -->
                <form method="POST" action="" style="display:inline;">
                    <button type="submit" name="delete" class="buyerBtn">Delete</button>
                    <input type="hidden" name="selected_ids" id="selected_ids">
                </form>
                <button type="button" onclick="window.location.href='dashboard.php'" class="buyerBtn">Back</button>
            </div>

            <!-- Table to display buyer details -->
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="toggleSelectAll(this)"></th> <!-- Select All Checkbox -->
                        <th>S1 No</th>
                        <th>Buyer Code</th>
                        <th>Buyer Name</th>
                        <th>Invoice Address</th>
                        <th>Contact Number</th>
                        <th>Contact Person</th>
                        <th>Delivery Address</th>
                        <th>Address</th>
                        <th>City Pin</th>
                        <th>State</th>
                        <th>Country</th>
                        <th>Category</th>
                        <th>Phone</th>
                        <th>Fax</th>
                        <th>Email</th>
                        <th>Website</th>
                        <th>Currency</th>
                        <th>GSTIN</th>
                        <th>Pan Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($buyers as $buyer): ?>
                        <tr>
                            <td><input type="checkbox" class="buyer-checkbox" name="selected_ids[]" value="<?php echo $buyer['s1_no']; ?>" onclick="toggleSelectAllButton()"></td>
                            <td><?php echo htmlspecialchars($buyer['s1_no']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['buyer_code']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['buyer_name']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['invoice_address']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['contact_no']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['contact_person']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['delivery_address']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['address']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['city_pin']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['state']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['country']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['category']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['phone']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['fax']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['email']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['web_site']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['currency']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['gstin']); ?></td>
                            <td><?php echo htmlspecialchars($buyer['pan_number']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Move your script to the bottom -->
    <script>
        // Toggle select all checkboxes
        function toggleSelectAll(source) {
            const checkboxes = document.querySelectorAll('.buyer-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
        }

        // Handle form submission with selected checkbox values
        document.querySelector("form").addEventListener("submit", function(event) {
            const selectedCheckboxes = document.querySelectorAll('.buyer-checkbox:checked');
            const selectedIds = [];
            selectedCheckboxes.forEach(checkbox => selectedIds.push(checkbox.value));
            
            // If no checkboxes are selected, prevent form submission
            if (selectedIds.length === 0) {
                event.preventDefault();
                alert("Please select at least one buyer to delete.");
                return;
            }

            // Set the hidden input value with the selected IDs
            document.getElementById('selected_ids').value = selectedIds.join(',');
        });
    </script>
</body>
</html>
