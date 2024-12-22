<?php 
// auth_check.php

session_start(); // Start the session

// Define timeout duration (in seconds)
$timeout_duration = 1800; // 30 minutes

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // User is not logged in, redirect to session expired page
    header('Location: session_expired.php');
    exit;
}

// Check if the session has timed out
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    // Session timed out, destroy the session and redirect to session expired page
    session_unset();     // Unset session variables
    session_destroy();   // Destroy the session
    header('Location: session_expired.php?error=timeout');
    exit;
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

/**
 * Check if the logged-in user is an admin.
 *
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
?>
