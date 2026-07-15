<?php
// This file is in htdocs/admin, so __DIR__ is .../htdocs/admin

// Load shared config and database only once per request
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Require a logged-in user
if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    header('Location: ' . url('admin/login.php'));
    exit;
}

// Require admin flag
if ((int) ($_SESSION['user']['is_admin'] ?? 0) !== 1) {
    http_response_code(403);
    exit('You do not have permission to access this area.');
}