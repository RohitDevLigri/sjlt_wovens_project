<?php  
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('dbconnect.php');

// Instantiate the Connection class and get the PDO connection
$database = new Connection();
$conn = $database->getConnection();

// Prevent back button from accessing the login page after login by setting cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if the user is already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    // If logged in, redirect to the home page
    header('Location: home.php');
    exit;
}

$login_error = '';
$register_errors = [];
$success_message = '';

// Handle Login Form Submission
if (isset($_POST['login'])) {
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

// Handle Registration Form Submission
if (isset($_POST['register'])) {
    // Get form data
    $fullname = trim($_POST['fullname']);
    $gender = trim($_POST['gender']);
    $mobile = trim($_POST['mobile']);
    $role = trim($_POST['role']);
    $username = trim($_POST['reg_username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate form data
    if (empty($fullname)) {
        $register_errors[] = "Full name is required.";
    }

    if (empty($gender)) {
        $register_errors[] = "Gender is required.";
    }

    if (empty($mobile)) {
        $register_errors[] = "Mobile number is required.";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $register_errors[] = "Mobile number must be 10 digits.";
    }

    if (empty($role)) {
        $register_errors[] = "Role is required.";
    } elseif (!in_array($role, ['admin', 'user'])) {
        $register_errors[] = "Invalid role selected.";
    }

    if (empty($username)) {
        $register_errors[] = "Username is required.";
    }

    if (empty($email)) {
        $register_errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $register_errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $register_errors[] = "Password must be at least 6 characters.";
    }

    if (empty($confirm_password)) {
        $register_errors[] = "Confirm password is required.";
    } elseif ($password !== $confirm_password) {
        $register_errors[] = "Passwords do not match.";
    }

    // Check if there are no errors before inserting into the database
    if (empty($register_errors)) {
        // Check if the user already exists
        $sql = "SELECT * FROM users WHERE email = :email OR username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $register_errors[] = "User with this email or username already exists!";
        } else {
            // Insert new user into database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password
            $sql = "INSERT INTO users (fullname, gender, mobile, role, username, email, password) 
                    VALUES (:fullname, :gender, :mobile, :role, :username, :email, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':fullname', $fullname);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':mobile', $mobile);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);

            if ($stmt->execute()) {
                $success_message = "User successfully registered!";
            } else {
                $register_errors[] = "Error: Could not execute the registration.";
            }
        }
    }
}

// Include navbar after form handling
include('navbar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Registration</title>
    <link rel="stylesheet" href="css/login_register.css">
</head>
<body>
    <div class="main-content">
        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <p><?= htmlspecialchars($success_message) ?></p>
            </div>
        <?php endif; ?>

        <div class="forms-container">
            <!-- Login Form -->
            <div class="login-form-box">
                <h2>Login</h2>
                <form action="login_register.php" method="post">
                    <div class="form-group">
                        <label for="login_username">Username:</label>
                        <input type="text" id="login_username" name="login_username" required>
                    </div>
                    <div class="form-group">
                        <label for="login_password">Password:</label>
                        <input type="password" id="login_password" name="login_password" required>
                    </div>
                    <?php if (!empty($login_error)): ?>
                        <p class="error-message"><?= htmlspecialchars($login_error) ?></p>
                    <?php endif; ?>
                    <div class="form-group">
                        <button type="submit" name="login">Login</button>
                    </div>
                </form>
            </div>

            <!-- Registration Form -->
            <div class="register-form-box">
                <h2>Register</h2>
                <form action="login_register.php" method="post">
                    
                <div class="form-group all-form"> 
                        <div class="name-field">
                            <label for="fullname">Full Name:</label>
                            <input type="text" id="fullname" name="fullname" value="<?= isset($fullname) ? htmlspecialchars($fullname) : ''; ?>" required>
                        </div>
                        <div class="gender-field">
                            <label for="gender">Gender:</label>
                            <label for="male">
                                <input type="radio" id="male" name="gender" value="Male" <?= (isset($gender) && $gender == 'Male') ? 'checked' : ''; ?> required> Male
                            </label>
                            <label for="female">
                                <input type="radio" id="female" name="gender" value="Female" <?= (isset($gender) && $gender == 'Female') ? 'checked' : ''; ?> required> Female
                            </label>
                            <label for="other">
                                <input type="radio" id="other" name="gender" value="Other" <?= (isset($gender) && $gender == 'Other') ? 'checked' : ''; ?> required> Other
                            </label>
                        </div>

                        <div class="role-field">
                            <label for="role">Role:</label>
                            <select id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin" <?= (isset($role) && $role == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="user" <?= (isset($role) && $role == 'user') ? 'selected' : ''; ?>>User</option>
                            </select>
                        </div>

                        <div class="mobile-field">
                            <label for="mobile">Mobile:</label>
                            <input type="text" id="mobile" name="mobile" value="<?= isset($mobile) ? htmlspecialchars($mobile) : ''; ?>" required>
                        </div>

                        <div class="username-field">
                            <label for="reg_username">Username:</label>
                            <input type="text" id="reg_username" name="reg_username" value="<?= isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                        </div>

                        <div class="email-field">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>

                        <div class="password-field">
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" required>
                        </div>

                        <div class="confirm-password-field">
                            <label for="confirm_password">Confirm Password:</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    <?php if (!empty($register_errors)): ?>
                        <div class="error-messages">
                            <?php foreach ($register_errors as $error): ?>
                                <p class="error-message"><?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <button type="submit" name="register">Signup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
