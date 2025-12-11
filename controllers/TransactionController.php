<?php
require_once __DIR__ . '/../config/auth.php';

// Require login
requireLogin();

// Generate transaction code
function generateTransactionCode() {
    $date = date('Ymd');
    $lastTrx = fetchOne(
        "SELECT transaction_code FROM transactions 
         WHERE transaction_code LIKE ? 
         ORDER BY id DESC LIMIT 1",
        ["TRX-{$date}-%"]
    );
    
    if ($lastTrx) {
        $lastNumber = intval(substr($lastTrx['transaction_code'], -3));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    return sprintf("TRX-%s-%03d", $date, $newNumber);
}

// Create transaction
function createTransaction($items, $paymentAmount) {
    $conn = getDBConnection();
    
    // Start transaction (PDO method)
    $conn->beginTransaction();
    
    try {
        // Calculate total
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }
        
        // Validate payment
        if ($paymentAmount < $totalAmount) {
            throw new Exception('Jumlah pembayaran kurang');
        }
        
        $changeAmount = $paymentAmount - $totalAmount;
        $transactionCode = generateTransactionCode();
        $userId = $_SESSION['user_id'];
        
        // Insert transaction
        $result = executeQuery(
            "INSERT INTO transactions (transaction_code, user_id, total_amount, payment_amount, change_amount) 
             VALUES (?, ?, ?, ?, ?)",
            [$transactionCode, $userId, $totalAmount, $paymentAmount, $changeAmount]
        );
        
        if (!$result['success']) {
            throw new Exception($result['error']);
        }
        
        $transactionId = getLastInsertId();
        
        // Insert transaction items
        foreach ($items as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $result = executeQuery(
                "INSERT INTO transaction_items (transaction_id, product_id, product_name, quantity, price, subtotal) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$transactionId, $item['id'], $item['name'], $item['quantity'], $item['price'], $subtotal]
            );
            
            if (!$result['success']) {
                throw new Exception($result['error']);
            }
        }
        
        // Commit transaction (PDO method)
        $conn->commit();
        
        return [
            'success' => true, 
            'message' => 'Transaksi berhasil',
            'transaction_id' => $transactionId,
            'transaction_code' => $transactionCode
        ];
        
    } catch (Exception $e) {
        // Rollback on error (PDO method)
        $conn->rollBack();
        return ['success' => false, 'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()];
    }
}

// Get transactions
function getTransactions($userId = null, $startDate = null, $endDate = null) {
    $sql = "SELECT t.*, u.full_name as kasir_name 
            FROM transactions t 
            JOIN users u ON t.user_id = u.id 
            WHERE 1=1";
    $params = [];
    
    if ($userId) {
        $sql .= " AND t.user_id = ?";
        $params[] = $userId;
    }
    
    if ($startDate) {
        $sql .= " AND DATE(t.created_at) >= ?";
        $params[] = $startDate;
    }
    
    if ($endDate) {
        $sql .= " AND DATE(t.created_at) <= ?";
        $params[] = $endDate;
    }
    
    $sql .= " ORDER BY t.created_at DESC";
    
    return fetchAll($sql, $params);
}

// Get transaction by ID
function getTransactionById($id, $checkOwner = false) {
    $transaction = fetchOne(
        "SELECT t.*, u.full_name as kasir_name 
         FROM transactions t 
         JOIN users u ON t.user_id = u.id 
         WHERE t.id = ?",
        [$id]
    );
    
    if (!$transaction) {
        return null;
    }
    
    // Check if kasir can only view own transactions
    if ($checkOwner && isKasir() && $transaction['user_id'] != $_SESSION['user_id']) {
        return null;
    }
    
    // Get transaction items
    $items = fetchAll(
        "SELECT * FROM transaction_items WHERE transaction_id = ?",
        [$id]
    );
    
    $transaction['items'] = $items;
    
    return $transaction;
}

// Refund transaction
function refundTransaction($id) {
    // Check if transaction exists and not already refunded
    $transaction = fetchOne("SELECT * FROM transactions WHERE id = ? AND status = 'completed'", [$id]);
    
    if (!$transaction) {
        return ['success' => false, 'message' => 'Transaksi tidak ditemukan atau sudah di-refund'];
    }
    
    $result = executeQuery("UPDATE transactions SET status = 'refunded' WHERE id = ?", [$id]);
    
    if ($result['success']) {
        return ['success' => true, 'message' => 'Transaksi berhasil di-refund'];
    } else {
        return ['success' => false, 'message' => 'Gagal refund transaksi: ' . $result['error']];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'checkout':
            $items = json_decode($_POST['items'], true);
            $paymentAmount = floatval($_POST['payment_amount']);
            
            if (empty($items)) {
                echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
                exit;
            }
            
            $result = createTransaction($items, $paymentAmount);
            echo json_encode($result);
            exit;
            
        case 'refund':
            if (!isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
                exit;
            }
            
            $result = refundTransaction($_POST['id']);
            echo json_encode($result);
            exit;
    }
}

// Get transactions for display
if (isAdmin()) {
    $transactions = getTransactions();
} else {
    $transactions = getTransactions($_SESSION['user_id']);
}
