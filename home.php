<?php
include('auth_check.php');
include('dbconnect.php');

// Prevent back button issues by setting cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Retrieve the user's role from the session or set to 'guest' if not defined
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'guest';

include('navbar.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
<div class="main-content">
    <h1>Welcome to the Sales Order Dashboard</h1>
    <p>Your role: <?= htmlspecialchars($role) ?></p>
</div>
</body>
</html>
