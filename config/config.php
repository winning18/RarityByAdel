<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'RarityByAdel');
define('APP_ENV', 'development');

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('ASSETS_PATH', BASE_PATH . '/assets');

define('APP_URL', 'http://localhost/RarityByAdel/public');
define('ASSET_URL', APP_URL . '/../assets');

define('BUSINESS_PHONE', '+233 551 812 055');
define('BUSINESS_EMAIL', 'hello@raritybyadel.com');

define('SOCIAL_INSTAGRAM', '#');
define('SOCIAL_TIKTOK', '#');
define('SOCIAL_SNAPCHAT', '#');

date_default_timezone_set('Africa/Accra');

function asset(string $path): string
{
    return ASSET_URL . '/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return APP_URL . '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}