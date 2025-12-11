<?php
require_once __DIR__ . '/../../controllers/TransactionController.php';
$pageTitle = 'Riwayat Transaksi';
include __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <h2 class="mb-4"><i class="bi bi-receipt"></i> Riwayat Transaksi</h2>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <?php if (isAdmin()): ?>
                            <th>Kasir</th>
                            <?php endif; ?>
                            <th>Total</th>
                            <th>Bayar</th>
                            <th>Kembalian</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="<?= isAdmin() ? 8 : 7 ?>" class="text-center">Tidak ada transaksi</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($transaction['transaction_code']) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?></td>
                            <?php if (isAdmin()): ?>
                            <td><?= htmlspecialchars($transaction['kasir_name']) ?></td>
                            <?php endif; ?>
                            <td>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($transaction['payment_amount'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($transaction['change_amount'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge bg-<?= $transaction['status'] === 'completed' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($transaction['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="/transactions/detail?id=<?= $transaction['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                <a href="/transactions/receipt?id=<?= $transaction['id'] ?>" class="btn btn-sm btn-secondary" target="_blank">
                                    <i class="bi bi-printer"></i> Print
                                </a>
                                <?php if (isAdmin() && $transaction['status'] === 'completed'): ?>
                                <button class="btn btn-sm btn-danger" onclick="refundTransaction(<?= $transaction['id'] ?>)">
                                    <i class="bi bi-arrow-counterclockwise"></i> Refund
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if (isAdmin()): ?>
<script>
function refundTransaction(id) {
    if (confirm('Yakin ingin refund transaksi ini?')) {
        fetch('/transactions', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=refund&id=' + id
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            }
        });
    }
}
</script>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
