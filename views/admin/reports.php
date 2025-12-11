<?php
require_once __DIR__ . '/../../controllers/ReportController.php';
$pageTitle = 'Laporan Penjualan';
include __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <h2 class="mb-4"><i class="bi bi-graph-up"></i> Laporan Penjualan</h2>
    
    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/admin/reports" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Pendapatan</h6>
                    <h2>Rp <?= number_format($totalRevenue, 0, ',', '.') ?></h2>
                    <small>Periode: <?= date('d/m/Y', strtotime($startDate)) ?> - <?= date('d/m/Y', strtotime($endDate)) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Transaksi</h6>
                    <h2><?= $totalTransactions ?></h2>
                    <small>Periode: <?= date('d/m/Y', strtotime($startDate)) ?> - <?= date('d/m/Y', strtotime($endDate)) ?></small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Daily Sales -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar3"></i> Penjualan Harian</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dailySales)): ?>
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada data</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($dailySales as $sale): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($sale['date'])) ?></td>
                            <td><?= $sale['transaction_count'] ?></td>
                            <td>Rp <?= number_format($sale['total_revenue'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Top Selling Products -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-trophy"></i> Produk Terlaris</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ranking</th>
                            <th>Nama Produk</th>
                            <th>Jumlah Terjual</th>
                            <th>Total Pendapatan</th>
                            <th>Jumlah Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($topProducts as $index => $product): ?>
                        <tr>
                            <td>
                                <?php if ($index === 0): ?>
                                <i class="bi bi-trophy-fill text-warning"></i>
                                <?php elseif ($index === 1): ?>
                                <i class="bi bi-trophy-fill text-secondary"></i>
                                <?php elseif ($index === 2): ?>
                                <i class="bi bi-trophy-fill text-danger"></i>
                                <?php else: ?>
                                <?= $index + 1 ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product['product_name']) ?></td>
                            <td><?= $product['total_quantity'] ?></td>
                            <td>Rp <?= number_format($product['total_revenue'], 0, ',', '.') ?></td>
                            <td><?= $product['transaction_count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Sales by Category -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Penjualan per Kategori</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Jumlah Terjual</th>
                            <th>Total Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($salesByCategory)): ?>
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada data</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($salesByCategory as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['category_name']) ?></td>
                            <td><?= $category['total_quantity'] ?></td>
                            <td>Rp <?= number_format($category['total_revenue'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
