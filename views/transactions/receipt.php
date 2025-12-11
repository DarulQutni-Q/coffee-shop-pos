<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/TransactionController.php';
requireLogin();

$transactionId = $_GET['id'] ?? 0;
$transaction = getTransactionById($transactionId, !isAdmin());

if (!$transaction) {
    die('Transaksi tidak ditemukan');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - <?= htmlspecialchars($transaction['transaction_code']) ?></title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            max-width: 300px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .receipt {
            border: 1px solid #000;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .header h2 {
            margin: 0;
            font-size: 18px;
        }
        
        .info {
            margin-bottom: 15px;
            font-size: 12px;
        }
        
        .items {
            margin-bottom: 15px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
        }
        
        .item {
            margin-bottom: 5px;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        
        .totals {
            margin-bottom: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .total-row.grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        
        .footer {
            text-align: center;
            font-size: 12px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        
        .print-btn {
            margin: 20px auto;
            display: block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        Print Struk
    </button>
    
    <div class="receipt">
        <div class="header">
            <h2>COFFEE SHOP POS</h2>
            <p style="margin: 5px 0;">Jl. Contoh No. 123</p>
            <p style="margin: 5px 0;">Telp: 0812-3456-7890</p>
        </div>
        
        <div class="info">
            <div>No: <?= htmlspecialchars($transaction['transaction_code']) ?></div>
            <div>Tanggal: <?= date('d/m/Y H:i:s', strtotime($transaction['created_at'])) ?></div>
            <div>Kasir: <?= htmlspecialchars($transaction['kasir_name']) ?></div>
        </div>
        
        <div class="items">
            <?php foreach ($transaction['items'] as $item): ?>
            <div class="item">
                <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                <div class="item-detail">
                    <span><?= $item['quantity'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                    <span>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="totals">
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></span>
            </div>
            <div class="total-row">
                <span>Bayar:</span>
                <span>Rp <?= number_format($transaction['payment_amount'], 0, ',', '.') ?></span>
            </div>
            <div class="total-row">
                <span>Kembalian:</span>
                <span>Rp <?= number_format($transaction['change_amount'], 0, ',', '.') ?></span>
            </div>
        </div>
        
        <div class="footer">
            <p>Terima Kasih</p>
            <p>Selamat Menikmati</p>
        </div>
    </div>
</body>
</html>
