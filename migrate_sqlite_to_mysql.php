<?php
/**
 * SQLite to MySQL Migration Script
 * This script migrates all data from SQLite to MySQL database
 */

// Load environment variables
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Error: .env file not found!\n");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

loadEnv(__DIR__ . '/.env');

// Database configurations
$sqliteDb = __DIR__ . '/database/coffee_shop_pos.db';
$mysqlHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$mysqlPort = $_ENV['DB_PORT'] ?? '3306';
$mysqlDb = $_ENV['DB_NAME'] ?? 'coffee_shop_pos';
$mysqlUser = $_ENV['DB_USER'] ?? 'root';
$mysqlPass = $_ENV['DB_PASS'] ?? '';

echo "=== SQLite to MySQL Migration Script ===\n\n";

// Check if SQLite database exists
if (!file_exists($sqliteDb)) {
    die("Error: SQLite database not found at: $sqliteDb\n");
}

try {
    // Connect to SQLite
    echo "1. Connecting to SQLite database...\n";
    $sqlite = new PDO('sqlite:' . $sqliteDb);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ SQLite connected\n\n";
    
    // Connect to MySQL
    echo "2. Connecting to MySQL database...\n";
    $mysql = new PDO(
        "mysql:host=$mysqlHost;port=$mysqlPort;dbname=$mysqlDb;charset=utf8mb4",
        $mysqlUser,
        $mysqlPass
    );
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ MySQL connected\n\n";
    
    // Disable foreign key checks temporarily
    $mysql->exec('SET FOREIGN_KEY_CHECKS=0');
    
    // Clear existing data
    echo "3. Clearing existing MySQL data...\n";
    $mysql->exec('TRUNCATE TABLE transaction_items');
    $mysql->exec('TRUNCATE TABLE transactions');
    $mysql->exec('TRUNCATE TABLE products');
    $mysql->exec('TRUNCATE TABLE categories');
    $mysql->exec('TRUNCATE TABLE users');
    echo "   ✓ Tables cleared\n\n";
    
    // Migrate categories
    echo "4. Migrating categories...\n";
    $categories = $sqlite->query('SELECT * FROM categories ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $mysql->prepare('INSERT INTO categories (id, name, created_at) VALUES (?, ?, ?)');
    foreach ($categories as $cat) {
        $stmt->execute([$cat['id'], $cat['name'], $cat['created_at']]);
    }
    echo "   ✓ Migrated " . count($categories) . " categories\n\n";
    
    // Migrate users
    echo "5. Migrating users...\n";
    $users = $sqlite->query('SELECT * FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $mysql->prepare('INSERT INTO users (id, username, password, full_name, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($users as $user) {
        $stmt->execute([
            $user['id'],
            $user['username'],
            $user['password'],
            $user['full_name'],
            $user['role'],
            $user['created_at'],
            $user['updated_at']
        ]);
    }
    echo "   ✓ Migrated " . count($users) . " users\n\n";
    
    // Migrate products
    echo "6. Migrating products...\n";
    $products = $sqlite->query('SELECT * FROM products ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $mysql->prepare('INSERT INTO products (id, name, category_id, price, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($products as $product) {
        $stmt->execute([
            $product['id'],
            $product['name'],
            $product['category_id'],
            $product['price'],
            $product['status'],
            $product['created_at'],
            $product['updated_at']
        ]);
    }
    echo "   ✓ Migrated " . count($products) . " products\n\n";
    
    // Migrate transactions
    echo "7. Migrating transactions...\n";
    $transactions = $sqlite->query('SELECT * FROM transactions ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $mysql->prepare('INSERT INTO transactions (id, transaction_code, user_id, total_amount, payment_amount, change_amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($transactions as $trans) {
        $stmt->execute([
            $trans['id'],
            $trans['transaction_code'],
            $trans['user_id'],
            $trans['total_amount'],
            $trans['payment_amount'],
            $trans['change_amount'],
            $trans['status'],
            $trans['created_at']
        ]);
    }
    echo "   ✓ Migrated " . count($transactions) . " transactions\n\n";
    
    // Migrate transaction items
    echo "8. Migrating transaction items...\n";
    $items = $sqlite->query('SELECT * FROM transaction_items ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $mysql->prepare('INSERT INTO transaction_items (id, transaction_id, product_id, product_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($items as $item) {
        $stmt->execute([
            $item['id'],
            $item['transaction_id'],
            $item['product_id'],
            $item['product_name'],
            $item['quantity'],
            $item['price'],
            $item['subtotal']
        ]);
    }
    echo "   ✓ Migrated " . count($items) . " transaction items\n\n";
    
    // Re-enable foreign key checks
    $mysql->exec('SET FOREIGN_KEY_CHECKS=1');
    
    // Verify data
    echo "9. Verifying migration...\n";
    $verification = [
        'categories' => $mysql->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
        'users' => $mysql->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'products' => $mysql->query('SELECT COUNT(*) FROM products')->fetchColumn(),
        'transactions' => $mysql->query('SELECT COUNT(*) FROM transactions')->fetchColumn(),
        'transaction_items' => $mysql->query('SELECT COUNT(*) FROM transaction_items')->fetchColumn(),
    ];
    
    echo "   MySQL Data Count:\n";
    foreach ($verification as $table => $count) {
        echo "   - $table: $count\n";
    }
    
    echo "\n=== Migration Completed Successfully! ===\n\n";
    echo "Next steps:\n";
    echo "1. Test the application with MySQL\n";
    echo "2. Access phpMyAdmin to view your data\n";
    echo "3. Backup SQLite database is still available at: $sqliteDb\n\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
