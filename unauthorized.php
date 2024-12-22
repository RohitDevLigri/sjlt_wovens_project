<?php
// unauthorized.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <link rel="stylesheet" href="css/login_register.css"> <!-- Use your existing CSS file -->
</head>
<body>
    <div class="main-content">
        <h2>Unauthorized Access</h2>
        <p>You do not have the necessary permissions to access this page.</p>
        <p>Please <a href="home.php">return to the home page</a> or contact the administrator if you believe this is an error.</p>
    </div>
</body>
</html>
