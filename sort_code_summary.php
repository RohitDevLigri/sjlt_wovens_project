<?php
// Start output buffering
ob_start();

require_once 'dbconnect_master.php';
$connection = new MasterConnection();
$conn = $connection->getConnection();

$message = ''; // Initialize the message variable

// Fetch all sort_code details from the database
try {
    $stmt = $conn->query("SELECT * FROM sort_code");
    $sortss = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $sql = "DELETE FROM sort_code WHERE s1_no IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($selected_ids);
            $message = '<div class="success">Selected sort code deleted successfully!</div>';

            // Redirect to the same page to refresh the data
            header("Location: " . $_SERVER['PHP_SELF']);
            exit; // Always call exit after header redirection to stop further execution
        } else {
            $message = '<div class="error">No sort code selected for deletion.</div>';
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
    <title>Sort Code Summary</title>
    <?php include 'navbar.php';?>
</head>
<body>
    <main>
        <div class="container">
            <h1>Sort Code Summary</h1>
            <?php if (!empty($message)) echo $message; ?>

        <!-- Buttons for select all, add sort_code, and delete -->
        <div class="buttons-container">
            <button type="button" onclick="location.href='create_sort_code.php';" class="buyerBtn">Add</button> <!-- Add sort_code Button -->
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
                        <th>Sort Code</th>
                        <th>Warp Count</th>
                        <th>Warp Count Unit</th>
                        <th>Weft Count</th>
                        <th>Warp Count Unit</th>
                        <th>Epi</th>
                        <th>Ppi</th>
                        <th>Ply</th>
                        <th>Weave</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sortss as $sort): ?>
                        <tr>
                            <td><input type="checkbox" class="sort-checkbox" name="selected_ids[]" value="<?php echo $sort['s1_no']; ?>" onclick="toggleSelectAllButton()"></td>
                            <td><?php echo htmlspecialchars($sort['s1_no']); ?></td>
                            <td><?php echo htmlspecialchars($sort['sort_code']); ?></td>
                            <td><?php echo htmlspecialchars($sort['warp_count']); ?></td>
                            <td><?php echo htmlspecialchars($sort['warp_count_unit']); ?></td>
                            <td><?php echo htmlspecialchars($sort['weft_count']); ?></td>
                            <td><?php echo htmlspecialchars($sort['weft_count_unit']); ?></td>
                            <td><?php echo htmlspecialchars($sort['epi']); ?></td>
                            <td><?php echo htmlspecialchars($sort['ppi']); ?></td>
                            <td><?php echo htmlspecialchars($sort['ply']); ?></td>
                            <td><?php echo htmlspecialchars($sort['weave']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>
        // Toggle select all checkboxes
        function toggleSelectAll(source) {
            const checkboxes = document.querySelectorAll('.sort-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
        }

        // Handle form submission with selected checkbox values
        document.querySelector("form").addEventListener("submit", function(event) {
            const selectedCheckboxes = document.querySelectorAll('.sort-checkbox:checked');
            const selectedIds = [];
            selectedCheckboxes.forEach(checkbox => selectedIds.push(checkbox.value));
            
            // If no checkboxes are selected, prevent form submission
            if (selectedIds.length === 0) {
                event.preventDefault();
                alert("Please select at least one sort to delete.");
                return;
            }

            // Set the hidden input value with the selected IDs
            document.getElementById('selected_ids').value = selectedIds.join(',');
        });
    </script>
</body>
</html>
