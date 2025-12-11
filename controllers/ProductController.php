<?php
require_once __DIR__ . '/../config/auth.php';

// Require login
requireLogin();

// Get all categories
function getAllCategories() {
    return fetchAll("SELECT * FROM categories ORDER BY name");
}

// Get all products with category info
function getAllProducts($categoryId = null) {
    if ($categoryId) {
        return fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.category_id = ?
             ORDER BY p.name",
            [$categoryId]
        );
    } else {
        return fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             ORDER BY c.name, p.name"
        );
    }
}

// Get available products for POS
function getAvailableProducts($categoryId = null) {
    if ($categoryId) {
        return fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.status = 'tersedia' AND p.category_id = ?
             ORDER BY p.name",
            [$categoryId]
        );
    } else {
        return fetchAll(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.status = 'tersedia'
             ORDER BY c.name, p.name"
        );
    }
}

// Get product by ID
function getProductById($id) {
    return fetchOne("SELECT * FROM products WHERE id = ?", [$id]);
}

// Add new product
function addProduct($name, $categoryId, $price, $status) {
    $result = executeQuery(
        "INSERT INTO products (name, category_id, price, status) VALUES (?, ?, ?, ?)",
        [$name, $categoryId, $price, $status]
    );
    
    if ($result['success']) {
        return ['success' => true, 'message' => 'Produk berhasil ditambahkan'];
    } else {
        return ['success' => false, 'message' => 'Gagal menambahkan produk: ' . $result['error']];
    }
}

// Update product
function updateProduct($id, $name, $categoryId, $price, $status) {
    $result = executeQuery(
        "UPDATE products SET name = ?, category_id = ?, price = ?, status = ? WHERE id = ?",
        [$name, $categoryId, $price, $status, $id]
    );
    
    if ($result['success']) {
        return ['success' => true, 'message' => 'Produk berhasil diupdate'];
    } else {
        return ['success' => false, 'message' => 'Gagal mengupdate produk: ' . $result['error']];
    }
}

// Delete product
function deleteProduct($id) {
    // Check if product is used in transactions
    $used = fetchOne("SELECT id FROM transaction_items WHERE product_id = ? LIMIT 1", [$id]);
    if ($used) {
        return ['success' => false, 'message' => 'Produk tidak dapat dihapus karena sudah digunakan dalam transaksi'];
    }
    
    $result = executeQuery("DELETE FROM products WHERE id = ?", [$id]);
    
    if ($result['success']) {
        return ['success' => true, 'message' => 'Produk berhasil dihapus'];
    } else {
        return ['success' => false, 'message' => 'Gagal menghapus produk: ' . $result['error']];
    }
}

// Toggle product status
function toggleProductStatus($id) {
    $product = getProductById($id);
    if (!$product) {
        return ['success' => false, 'message' => 'Produk tidak ditemukan'];
    }
    
    $newStatus = $product['status'] === 'tersedia' ? 'habis' : 'tersedia';
    $result = executeQuery("UPDATE products SET status = ? WHERE id = ?", [$newStatus, $id]);
    
    if ($result['success']) {
        return ['success' => true, 'message' => 'Status produk berhasil diubah'];
    } else {
        return ['success' => false, 'message' => 'Gagal mengubah status: ' . $result['error']];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // Only admin can modify products
    if (!isAdmin() && in_array($_POST['action'], ['add', 'update', 'delete', 'toggle_status'])) {
        echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'add':
            $result = addProduct(
                $_POST['name'],
                $_POST['category_id'],
                $_POST['price'],
                $_POST['status']
            );
            echo json_encode($result);
            exit;
            
        case 'update':
            $result = updateProduct(
                $_POST['id'],
                $_POST['name'],
                $_POST['category_id'],
                $_POST['price'],
                $_POST['status']
            );
            echo json_encode($result);
            exit;
            
        case 'delete':
            $result = deleteProduct($_POST['id']);
            echo json_encode($result);
            exit;
            
        case 'toggle_status':
            $result = toggleProductStatus($_POST['id']);
            echo json_encode($result);
            exit;
            
        case 'get':
            $product = getProductById($_POST['id']);
            echo json_encode(['success' => true, 'product' => $product]);
            exit;
            
        case 'get_by_category':
            $products = getAvailableProducts($_POST['category_id'] ?? null);
            echo json_encode(['success' => true, 'products' => $products]);
            exit;
    }
}

// Get data for display
$categories = getAllCategories();
$products = isAdmin() ? getAllProducts() : getAvailableProducts();
