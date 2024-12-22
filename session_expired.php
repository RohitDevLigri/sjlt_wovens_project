<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired</title>
    <link rel="stylesheet" href="css/session_expired.css"> <!-- Link to your CSS file -->
</head>
<body>
    <div class="container">
        <h1>You are not Logged In</h1>
        <p>If you have not logged in, you must do so before you are permitted to access any of the available modules.</p>
        <p>If you had previously logged in, you are probably seeing this message because no activity was detected from your browser and you were automatically logged out for security reasons.</p>
        <p>To log in, click the button below:</p>
        <a href="login.php"><button>Login Again</button></a>
    </div>
    
</body>
</html>
