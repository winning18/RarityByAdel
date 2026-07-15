<?php
require __DIR__ . '/config/config.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear only user-related data (if you keep other session keys)
unset($_SESSION['user']);

// Optionally clear all session data and destroy the session
// $_SESSION = [];
// session_unset();
// session_destroy();

// Optionally pass a logout flag/message to the login page
header('Location: ' . url('login.php?logged_out=1'));
exit;