<?php
// admin_account.php

include('auth_check.php'); // Includes session and role checks
include('navbar.php');      // Includes the navigation bar

// Ensure the user is an admin
if (!is_admin()) {
    // If the user is not an admin, redirect to unauthorized access page
    header('Location: unauthorized.php');
    exit;
}

// Fetch admin details from the database if needed
include('dbconnect.php');
$database = new Connection();
$conn = $database->getConnection();

$admin_id = $_SESSION['user_id']; // Assuming you have user_id stored in session

$sql = "SELECT fullname, gender, mobile, role, username, email FROM users WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $admin_id);
$stmt->execute();

$admin = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account</title>
    <link rel="stylesheet" href="css/admin_account.css">
    <!-- Optional: Include the shared CSS file if needed -->
    <link rel="stylesheet" href="css/login_register.css">
    
    <!-- JavaScript for Popup Handling -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Automatically hide success message after 3 seconds
            var successMsg = document.getElementById('successMessage');
            if (successMsg) {
                setTimeout(function() {
                    successMsg.style.display = 'none';
                }, 3000); // 3000 milliseconds = 3 seconds
            }
        });
    </script>
</head>
<body>
    <div class="main-content">
    <div class="admin-content">
        <?php if (!empty($success_message)): ?>
            <div class="success-message" id="successMessage">
                <p><?= htmlspecialchars($success_message) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($register_errors)): ?>
            <div class="error-message">
                <?php foreach ($register_errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h1>Admin Account</h1>
        <div class="admin-details">
            <p><strong>Full Name:</strong> <?= htmlspecialchars($admin['fullname']) ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($admin['gender']) ?></p>
            <p><strong>Mobile:</strong> <?= htmlspecialchars($admin['mobile']) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($admin['role'])) ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($admin['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($admin['email']) ?></p>
        </div>
        <a href="register.php" class="register-link">Register New User</a>
    </div>
    </div>
</body>
</html>
