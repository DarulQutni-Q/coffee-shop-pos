<?php
require_once __DIR__ . '/../config/auth.php';

// Require admin access
requireAdmin();

// Get daily sales report
function getDailySales($startDate = null, $endDate = null) {
    $sql = "SELECT 
                DATE(created_at) as date,
                COUNT(*) as transaction_count,
                SUM(total_amount) as total_revenue
            FROM transactions
            WHERE status = 'completed'";
    $params = [];
    
    if ($startDate) {
        $sql .= " AND DATE(created_at) >= ?";
        $params[] = $startDate;
    }
    
    if ($endDate) {
        $sql .= " AND DATE(created_at) <= ?";
        $params[] = $endDate;
    }
    
    $sql .= " GROUP BY DATE(created_at) ORDER BY date DESC";
    
    return fetchAll($sql, $params);
}

// Get total revenue
function getTotalRevenue($startDate = null, $endDate = null) {
    $sql = "SELECT SUM(total_amount) as total FROM transactions WHERE status = 'completed'";
    $params = [];
    
    if ($startDate) {
        $sql .= " AND DATE(created_at) >= ?";
        $params[] = $startDate;
    }
    
    if ($endDate) {
        $sql .= " AND DATE(created_at) <= ?";
        $params[] = $endDate;
    }
    
    $result = fetchOne($sql, $params);
    return $result['total'] ?? 0;
}

// Get total transactions
function getTotalTransactions($startDate = null, $endDate = null) {
    $sql = "SELECT COUNT(*) as total FROM transactions WHERE status = 'completed'";
    $params = [];
    
    if ($startDate) {
        $sql .= " AND DATE(created_at) >= ?";
        $params[] = $startDate;
    }
    
    if ($endDate) {
        $sql .= " AND DATE(created_at) <= ?";
        $params[] = $endDate;
    }
    
    $result = fetchOne($sql, $params);
    return $result['total'] ?? 0;
}

// Get top selling products
function getTopSellingProducts($startDate = null, $endDate = null, $limit = 10) {
    $sql = "SELECT 
                ti.product_name,
                SUM(ti.quantity) as total_quantity,
                SUM(ti.subtotal) as total_revenue,
                COUNT(DISTINCT ti.transaction_id) as transaction_count
            FROM transaction_items ti
            JOIN transactions t ON ti.transaction_id = t.id
            WHERE t.status = 'completed'";
    $params = [];
    
    if ($startDate) {
        $sql .= " AND DATE(t.created_at) >= ?";
        $params[] = $startDate;
    }
    
    if ($endDate) {
        $sql .= " AND DATE(t.created_at) <= ?";
        $params[] = $endDate;
    }
    
    $sql .= " GROUP BY ti.product_name ORDER BY total_quantity DESC LIMIT ?";
    $params[] = $limit;
    
    return fetchAll($sql, $params);
}

// Get sales by category
function getSalesByCategory($startDate = null, $endDate = null) {
    $sql = "SELECT 
                c.name as category_name,
                SUM(ti.quantity) as total_quantity,
                SUM(ti.subtotal) as total_revenue
            FROM transaction_items ti
            JOIN transactions t ON ti.transaction_id = t.id
            JOIN products p ON ti.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE t.status = 'completed'";
    $params = [];
    
    if ($startDate) {
        $sql .= " AND DATE(t.created_at) >= ?";
        $params[] = $startDate;
    }
    
    if ($endDate) {
        $sql .= " AND DATE(t.created_at) <= ?";
        $params[] = $endDate;
    }
    
    $sql .= " GROUP BY c.id, c.name ORDER BY total_revenue DESC";
    
    return fetchAll($sql, $params);
}

// Handle filter
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Get report data
$dailySales = getDailySales($startDate, $endDate);
$totalRevenue = getTotalRevenue($startDate, $endDate);
$totalTransactions = getTotalTransactions($startDate, $endDate);
$topProducts = getTopSellingProducts($startDate, $endDate);
$salesByCategory = getSalesByCategory($startDate, $endDate);
