<?php
// register.php

include('auth_check.php'); // Includes session and role checks
include('dbconnect.php');   // Database connection
include('navbar.php');      // Navigation bar

// Ensure the user is an admin
if (!is_admin()) {
    // If the user is not an admin, redirect to unauthorized access page
    header('Location: unauthorized.php');
    exit;
}

// Instantiate the Connection class and get the PDO connection
$database = new Connection();
$conn = $database->getConnection();

$register_errors = [];
$success_message = '';

// Handle Registration Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
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
                // Clear form fields after successful registration
                $fullname = $gender = $mobile = $role = $username = $email = '';
            } else {
                $register_errors[] = "Error: Could not execute the registration.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New User</title>
    
    <!-- Link to the shared CSS file -->
    <link rel="stylesheet" href="css/register.css">
    
    <!-- Optional: Link to admin-specific CSS -->
    <link rel="stylesheet" href="css/admin_account.css">
    
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
    <div class="register-content">
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

        <div class="forms-container">
            <!-- Registration Form -->
            <div class="register-form-box">
                <h2>Register New User</h2>
                <form action="register.php" method="post">
                    <div class="form-group all-form"> 
                        <div class="name-field">
                            <label for="fullname">Full Name:</label>
                            <input type="text" id="fullname" name="fullname" value="<?= isset($fullname) ? htmlspecialchars($fullname) : ''; ?>" required>
                        </div>
                        <div class="gender-field">
                            <label>Gender:</label>
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
                    <div class="form-group">
                        <button type="submit" name="register">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
