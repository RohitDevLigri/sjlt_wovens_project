<?php
// Start output buffering
ob_start();

require_once 'dbconnect_master.php';
$connection = new MasterConnection();
$conn = $connection->getConnection();

$message = ''; // Initialize the message variable

// Fetch all agent details from the database
try {
    $stmt = $conn->query("SELECT * FROM agent_details");
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $sql = "DELETE FROM agent_details WHERE s1_no IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($selected_ids);
            $message = '<div class="success">Selected agent details deleted successfully!</div>';

            // Redirect to the same page to refresh the data
            header("Location: " . $_SERVER['PHP_SELF']);
            exit; // Always call exit after header redirection to stop further execution
        } else {
            $message = '<div class="error">No agent details selected for deletion.</div>';
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
    <title>Agent Summary</title>
    <?php include 'navbar.php';?>
</head>
<body>
    <main>
        <div class="container">
            <h1>Agent Summary</h1>
            <?php if (!empty($message)) echo $message; ?>

        <!-- Buttons for select all, add agent, and delete -->
        <div class="buttons-container">
            <button type="button" onclick="location.href='create_agent_details.php';" class="buyerBtn">Add</button> <!-- Add agent Button -->
            <form method="POST" action="" style="display:inline;">
                <button type="submit" name="delete" class="buyerBtn">Delete</button>
                <input type="hidden" name="selected_ids" id="selected_ids">
            </form>
        </div>
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="toggleSelectAll(this)"></th>
                        <th>S1 No</th>
                        <th>Agent Code</th>
                        <th>Agent Name</th>
                        <th>Invoice Address</th>
                        <th>Contact Number</th>
                        <th>Contact Person</th>
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
                    <?php foreach ($agents as $agent): ?>
                        <tr>
                            <td><input type="checkbox" class="agent-checkbox" name="selected_ids[]" value="<?php echo $agent['s1_no']; ?>" onclick="toggleSelectAllButton()"></td>
                            <td><?php echo htmlspecialchars($agent['s1_no']); ?></td>
                            <td><?php echo htmlspecialchars($agent['agent_code']); ?></td>
                            <td><?php echo htmlspecialchars($agent['agent_name']); ?></td>
                            <td><?php echo htmlspecialchars($agent['invoice_address']); ?></td>
                            <td><?php echo htmlspecialchars($agent['contact_no']); ?></td>
                            <td><?php echo htmlspecialchars($agent['contact_person']); ?></td>
                            <td><?php echo htmlspecialchars($agent['address']); ?></td>
                            <td><?php echo htmlspecialchars($agent['city_pin']); ?></td>
                            <td><?php echo htmlspecialchars($agent['state']); ?></td>
                            <td><?php echo htmlspecialchars($agent['country']); ?></td>
                            <td><?php echo htmlspecialchars($agent['category']); ?></td>
                            <td><?php echo htmlspecialchars($agent['phone']); ?></td>
                            <td><?php echo htmlspecialchars($agent['fax']); ?></td>
                            <td><?php echo htmlspecialchars($agent['email']); ?></td>
                            <td><?php echo htmlspecialchars($agent['web_site']); ?></td>
                            <td><?php echo htmlspecialchars($agent['currency']); ?></td>
                            <td><?php echo htmlspecialchars($agent['gstin']); ?></td>
                            <td><?php echo htmlspecialchars($agent['pan_number']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>
        // Toggle select all checkboxes
        function toggleSelectAll(source) {
            const checkboxes = document.querySelectorAll('.agent-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
        }

        // Handle form submission with selected checkbox values
        document.querySelector("form").addEventListener("submit", function(event) {
            const selectedCheckboxes = document.querySelectorAll('.agent-checkbox:checked');
            const selectedIds = [];
            selectedCheckboxes.forEach(checkbox => selectedIds.push(checkbox.value));
            
            // If no checkboxes are selected, prevent form submission
            if (selectedIds.length === 0) {
                event.preventDefault();
                alert("Please select at least one agent to delete.");
                return;
            }

            // Set the hidden input value with the selected IDs
            document.getElementById('selected_ids').value = selectedIds.join(',');
        });
    </script>
</body>
</html>
