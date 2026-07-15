<?php
// This file is in htdocs/admin, so __DIR__ is .../htdocs/admin

// Load shared config once (for url(), asset(), etc.)
require_once __DIR__ . '/../config/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Clear all session data
$_SESSION = [];

// If cookies are used for sessions, clear the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Redirect back to admin login
header('Location: ' . url('admin/login.php'));
exit;