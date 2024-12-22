<?php

session_start();

// Update last activity time on any page interaction
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    $_SESSION['LAST_ACTIVITY'] = time(); // Update activity timestamp
}

include('dbconnect.php');

// Instantiate the Connection class and get the PDO connection
$database = new Connection();
$conn = $database->getConnection();

// Prevent back button from accessing the login page after login by setting cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to home page if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: home.php');
    exit;
}

$login_error = '';

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['login_username']);
    $password = trim($_POST['login_password']);

    // Check user credentials using prepared statements to prevent SQL injection
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['LAST_ACTIVITY'] = time(); // Set initial activity timestamp

            // Redirect to home page after successful login
            header('Location: home.php');
            exit;
        } else {
            $login_error = "Invalid username or password.";
        }
    } else {
        $login_error = "Invalid username or password.";
    }
}

include('navbar.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    
    <!-- Link to the shared CSS file -->
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="main-content">
        <?php if (!empty($login_error)): ?>
            <div class="error-message">
                <p><?= htmlspecialchars($login_error) ?></p>
            </div>
        <?php endif; ?>

        <div class="forms-container">
            <!-- Login Form -->
            <div class="login-form-box">
                <h2>Login</h2>
                <form action="login.php" method="post">
                    <div class="form-group">
                        <label for="login_username">Username:</label>
                        <input type="text" id="login_username" name="login_username" required>
                    </div>
                    <div class="form-group">
                        <label for="login_password">Password:</label>
                        <input type="password" id="login_password" name="login_password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="login">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
