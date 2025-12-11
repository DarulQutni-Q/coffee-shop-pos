<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/TransactionController.php';
requireLogin();

$transactionId = $_GET['id'] ?? 0;
$transaction = getTransactionById($transactionId, !isAdmin());

if (!$transaction) {
    header('Location: /transactions');
    exit;
}

$pageTitle = 'Detail Transaksi';
include __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-receipt"></i> Detail Transaksi</h2>
        <a href="/transactions" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Transaksi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Kode Transaksi:</th>
                            <td><?= htmlspecialchars($transaction['transaction_code']) ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal:</th>
                            <td><?= date('d/m/Y H:i:s', strtotime($transaction['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <th>Kasir:</th>
                            <td><?= htmlspecialchars($transaction['kasir_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-<?= $transaction['status'] === 'completed' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($transaction['status']) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Pembayaran</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Total:</th>
                            <td>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <th>Jumlah Bayar:</th>
                            <td>Rp <?= number_format($transaction['payment_amount'], 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <th>Kembalian:</th>
                            <td class="text-success">Rp <?= number_format($transaction['change_amount'], 0, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Item Transaksi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaction['items'] as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total:</th>
                            <th>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="/transactions/receipt?id=<?= $transaction['id'] ?>" class="btn btn-primary" target="_blank">
            <i class="bi bi-printer"></i> Print Struk
        </a>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
