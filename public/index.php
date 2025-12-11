<?php
// Simple router for Coffee Shop POS
date_default_timezone_set('Asia/Jakarta');
session_start();

// Get the request URI
$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// Remove leading slash
$requestPath = trim($requestPath, '/');

// Include auth config
require_once __DIR__ . '/../config/auth.php';

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

// Route handling
switch ($requestPath) {
    case '':
    case 'index.php':
        // Login page or redirect to dashboard if already logged in
        if (isLoggedIn()) {
            redirectToDashboard();
        } else {
            require __DIR__ . '/../views/login.php';
        }
        break;
        
    case 'dashboard':
        requireLogin();
        redirectToDashboard();
        break;
        
    // Admin routes
    case 'admin/dashboard':
        require __DIR__ . '/../views/admin/dashboard.php';
        break;
        
    case 'admin/users':
        require __DIR__ . '/../views/admin/users.php';
        break;
        
    case 'admin/products':
        require __DIR__ . '/../views/admin/products.php';
        break;
        
    case 'admin/reports':
        require __DIR__ . '/../views/admin/reports.php';
        break;
        
    // Kasir routes
    case 'kasir/dashboard':
        require __DIR__ . '/../views/kasir/dashboard.php';
        break;
        
    case 'kasir/pos':
        require __DIR__ . '/../views/kasir/pos.php';
        break;
        
    // Transaction routes
    case 'transactions':
        require __DIR__ . '/../views/transactions/history.php';
        break;
        
    case 'transactions/detail':
        require __DIR__ . '/../views/transactions/detail.php';
        break;
        
    case 'transactions/receipt':
        require __DIR__ . '/../views/transactions/receipt.php';
        break;
        
    // 404 Not Found
    default:
        http_response_code(404);
        echo '<h1>404 - Page Not Found</h1>';
        echo '<p><a href="/">Go to Home</a></p>';
        break;
}
