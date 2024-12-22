<?php
// user_account.php

include('auth_check.php'); // Includes session and role checks
include('navbar.php');      // Includes the navigation bar

// Ensure the user is NOT an admin
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'admin') {
    // Regular user; proceed
} else {
    // If the user is an admin trying to access user_account.php, redirect to admin_account.php
    header('Location: admin_account.php');
    exit;
}

// Fetch user details from the database if needed
// Example:
include('dbconnect.php');
$database = new Connection();
$conn = $database->getConnection();

$user_id = $_SESSION['user_id']; // Assuming you have user_id stored in session

$sql = "SELECT fullname, gender, mobile, role, username, email FROM users WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $user_id);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Account</title>
    <link rel="stylesheet" href="css/user_account.css"> <!-- Create this CSS file as needed -->
</head>
<body>
    <div class="main-content">
    <div class="user-content">
        <h1>User Account</h1>
        <div class="user-details">
            <p><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname']) ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($user['gender']) ?></p>
            <p><strong>Mobile:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>
    </div>
</body>
</html>
