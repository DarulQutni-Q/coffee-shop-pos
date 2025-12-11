<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role']
    ];
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

// Check if user is kasir
function isKasir() {
    return isLoggedIn() && $_SESSION['role'] === 'kasir';
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /');
        exit;
    }
}

// Require admin role
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /dashboard');
        exit;
    }
}

// Logout user
function logout() {
    session_destroy();
    header('Location: /');
    exit;
}

// Login user
function login($username, $password) {
    $user = fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Username atau password salah'];
    }
    
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Username atau password salah'];
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    
    return ['success' => true, 'user' => $user];
}

// Redirect based on role
function redirectToDashboard() {
    if (isAdmin()) {
        header('Location: /admin/dashboard');
    } else {
        header('Location: /kasir/pos');
    }
    exit;
}
