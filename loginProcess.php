<?php
/**
 * Login Processing Page - Munch Healthy Bakery
 * This file processes the login form submission
 */

require_once 'DBconfig.php';

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage('login.php', 'Invalid request method', 'error');
    exit();
}

// Get and sanitize form data
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate required fields
if (empty($email) || empty($password)) {
    redirectWithMessage('login.php', 'Email and password are required', 'error');
    exit();
}

try {
    // FIRST: Check if user is blocked (in blockeduser table)
    $sql = "SELECT id, firstName, lastName, emailAddress FROM blockeduser WHERE emailAddress = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // User is blocked - get their name for the message
        $blockedUser = $stmt->fetch();
        $message = "Account for " . $blockedUser['firstName'] . " " . $blockedUser['lastName'] . 
                   " has been blocked. Please contact administrator.";
        redirectWithMessage('login.php', $message, 'error');
        exit();
    }
    
    // SECOND: Get user from user table by email
    $sql = "SELECT id, userType, firstName, lastName, emailAddress, password, photoFileName 
            FROM user WHERE emailAddress = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // No user found with this email
        redirectWithMessage('login.php', 'Invalid email or password', 'error');
        exit();
    }
    
    $user = $stmt->fetch();
    
    // Verify password using password_verify() 
    if (!password_verify($password, $user['password'])) {
        // Password incorrect
        redirectWithMessage('login.php', 'Invalid email or password', 'error');
        exit();
    }
    
    // Login successful - set session variables
    // Session is already started in DBconfig.php
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = $user['userType'];
    $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
    $_SESSION['user_email'] = $user['emailAddress'];
    $_SESSION['user_photo'] = $user['photoFileName'];
    $_SESSION['success_message'] = 'Welcome back, ' . $user['firstName'] . '!';
    
    // Redirect based on user type as specified
    if ($user['userType'] === 'admin') {
        // Admin user - redirect to admin page
        header('Location: admin.php');
    } else {
        // Regular user - redirect to user page
        header('Location: user.php');
    }
    exit();
    
} catch (PDOException $e) {
    // Log error for debugging
    error_log("Login error: " . $e->getMessage() . " - Email: $email");
    
    // Show user-friendly message
    redirectWithMessage('login.php', 'An error occurred during login. Please try again.', 'error');
    exit();
}
?>