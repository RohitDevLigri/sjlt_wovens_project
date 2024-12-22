<?php
include('auth_check.php'); // Check if user is logged in and session is valid
include('dbconnect.php');
include('navbar.php');

// Get the user's role from the session
$user_role = $_SESSION['user_role'] ?? 'guest'; // Default to 'guest' if not set

// Create a new instance of the connection class and get the PDO connection
$connection = new Connection();
$conn = $connection->getConnection();

function canEdit($role, $confirmed) {
    if ($role === 'admin') {
        return true;
    } elseif ($role === 'user' && $confirmed !== 'Approved') {
        return true;
    }
    return false;
}

function canDelete($role) {
    return $role === 'admin';
}

// Build SQL query with optional filters
$filters = [];
$sql = "SELECT 
            id,
            warp_count,
            weft_count,
            warp_composition,
            weft_composition,
            delivery_address,
            order_qty,
            yarn_mill,
            yarn_available_qty,	
            yarn_received_qty,
            yarn_issues_qty
        FROM yarn_purchase_order
        WHERE 1=1";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);

// Execute the query
$stmt->execute();

// Fetch the results
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yarn Summary</title>
    <link rel="stylesheet" href="css/yarn_summary.css">
</head>
<body>
    <main>
        <?php if ($user_role === 'admin' || $user_role === 'user'): ?>
            <a href="create_new_sales_order.php" class="create-new-order">Create New Sales Order</a>
        <?php endif; ?>
        <section class="sales-order-summary">
            <h2>Yarn Summary</h2>
            <a href="download_invoice.php" class="download-link">
                <img src="images/animated-gif-in-pdf-12.gif" alt="PDF Icon">
            </a>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Yarn Po No</th>
                            <th>Warp Count</th>
                            <th>Weft Count</th>
                            <th>Warp Composition</th>
                            <th>Weft Composition</th>
                            <th>Order Status</th>
                            <th>Order Qty</th>
                            <th>Mill Name</th>
                            <th>Yarn Available Qty</th>
                            <th>Yarn Received Qty</th>
                            <th>Yarn Issues Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($result)): ?>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['warp_count']); ?></td>
                                    <td><?php echo htmlspecialchars($row['weft_count']); ?></td>
                                    <td><?php echo htmlspecialchars($row['warp_composition'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['weft_composition']); ?></td>
                                    <td><?php echo htmlspecialchars($row['delivery_address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['order_qty']); ?></td>
                                    <td><?php echo htmlspecialchars($row['yarn_mill']); ?></td>
                                    <td><?php echo htmlspecialchars($row['yarn_available_qty']); ?></td>
                                    <td><?php echo htmlspecialchars($row['yarn_received_qty']); ?></td>
                                    <td><?php echo htmlspecialchars($row['yarn_issues_qty']); ?></td>
                                    <!-- <td>
                                        <a href="view_order.php?order_no=<?php echo htmlspecialchars($row['sales_order_no']); ?>">View</a>
                                        <?php if (canEdit($user_role, $row['confirmed'])): ?>
                                            <a href="edit_order.php?order_no=<?php echo htmlspecialchars($row['sales_order_no']); ?>">Edit</a>
                                        <?php endif; ?>
                                        <?php if (canDelete($user_role)): ?>
                                            <a href="delete_order.php?order_no=<?php echo htmlspecialchars($row['sales_order_no']); ?>" onclick="return confirm('Are you sure you want to delete this order?');">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['confirmed']); ?></td>
                                    <td><?php echo htmlspecialchars($row['order_status']); ?></td>
                                    <td>
                                        <a href="work_order.php?order_no=<?php echo htmlspecialchars($row['sales_order_no']); ?>">View Work Order</a>
                                    </td> -->
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11">No sales orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>

<?php
// Close the connection (optional, as PDO automatically closes)
$conn = null;
?>
