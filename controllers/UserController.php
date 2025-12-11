<?php
require_once __DIR__ . '/../config/auth.php';

// Require admin access
requireAdmin();

// Get all users
function getAllUsers() {
    return fetchAll("SELECT id, username, full_name, role, created_at FROM users ORDER BY created_at DESC");
}

// Get user by ID
function getUserById($id) {
    return fetchOne("SELECT id, username, full_name, role FROM users WHERE id = ?", [$id]);
}

// Add new user
function addUser($username, $password, $full_name, $role) {
    // Check if username already exists
    $existing = fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
    if ($existing) {
        return ['success' => false, 'message' => 'Username sudah digunakan'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $result = executeQuery(
        "INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)",
        [$username, $hashedPassword, $full_name, $role]
    );
    
    if ($result['success']) {
        return ['success' => true, 'message' => 'User berhasil ditambahkan'];
    } else {
        return ['success' => false, 'message' => 'Gagal menambahkan user: ' . $result['error']];
    }
}

// Update user
function updateUser($id, $username, $full_name, $role, $password = null) {
    // Check if username already exists (except current user)
    $existing = fetchOne("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $id]);
    if ($existing) {
        return ['success' => false, 'message' => 'Username sudah digunakan'];
    }
    
    if (!empty($password)) {
        // Update with new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $result = executeQuery(
            "UPDATE users SET username = ?, password = ?, full_name = ?, role = ? WHERE id = ?",
            [$username, $hashedPassword, $full_name, $role, $id]
        );
    } else {
        // Update without changing password
        $result = executeQuery(
            "UPDATE users SET username = ?, full_name = ?, role = ? WHERE id = ?",
            [$username, $full_name, $role, $id]
        );
    }
    
    if ($result['success']) {
        return ['success' => true, 'message' => 'User berhasil diupdate'];
    } else {
        return ['success' => false, 'message' => 'Gagal mengupdate user: ' . $result['error']];
    }
}

// Delete user
function deleteUser($id) {
    // Don't allow deleting current user
    if ($id == $_SESSION['user_id']) {
        return ['success' => false, 'message' => 'Tidak dapat menghapus user yang sedang login'];
    }
    
    $result = executeQuery("DELETE FROM users WHERE id = ?", [$id]);
    
    if ($result['success']) {
        return ['success' => true, 'message' => 'User berhasil dihapus'];
    } else {
        return ['success' => false, 'message' => 'Gagal menghapus user: ' . $result['error']];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add':
            $result = addUser(
                $_POST['username'],
                $_POST['password'],
                $_POST['full_name'],
                $_POST['role']
            );
            echo json_encode($result);
            exit;
            
        case 'update':
            $result = updateUser(
                $_POST['id'],
                $_POST['username'],
                $_POST['full_name'],
                $_POST['role'],
                !empty($_POST['password']) ? $_POST['password'] : null
            );
            echo json_encode($result);
            exit;
            
        case 'delete':
            $result = deleteUser($_POST['id']);
            echo json_encode($result);
            exit;
            
        case 'get':
            $user = getUserById($_POST['id']);
            echo json_encode(['success' => true, 'user' => $user]);
            exit;
    }
}

// Get users for display
$users = getAllUsers();
