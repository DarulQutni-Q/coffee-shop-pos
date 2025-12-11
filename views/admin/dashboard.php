<?php
require_once __DIR__ . '/../../config/auth.php';
requireAdmin();

// Get statistics (SQLite compatible)
$totalProducts = fetchOne("SELECT COUNT(*) as total FROM products")['total'];
$totalUsers = fetchOne("SELECT COUNT(*) as total FROM users")['total'];
$todayTransactions = fetchOne("SELECT COUNT(*) as total FROM transactions WHERE DATE(created_at) = CURDATE()")['total'];
$todayRevenue = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM transactions WHERE DATE(created_at) = CURDATE() AND status = 'completed'")['total'];

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../layout/header.php';
?>

<style>
:root {
    --primary-color: #4361ee;
    --secondary-color: #3f37c9;
    --success-color: #4cc9f0;
    --warning-color: #f72585;
    --text-primary: #2b2d42;
    --text-secondary: #8d99ae;
    --bg-card: #ffffff;
}

.dashboard-container {
    padding: 20px;
}

.welcome-section {
    margin-bottom: 2rem;
    border-bottom: 2px solid #edf2f4;
    padding-bottom: 1rem;
}

.welcome-title {
    color: var(--text-primary);
    font-weight: 700;
    font-size: 1.8rem;
}

.welcome-subtitle {
    color: var(--text-secondary);
    font-size: 1rem;
}

.stat-card {
    background: var(--bg-card);
    border: none;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    transition: transform 0.2s, box-shadow 0.2s;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
}

.stat-card.blue::before { background-color: var(--primary-color); }
.stat-card.purple::before { background-color: var(--secondary-color); }
.stat-card.cyan::before { background-color: var(--success-color); }
.stat-card.pink::before { background-color: var(--warning-color); }

.stat-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
    line-height: 1.2;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-icon-wrapper {
    position: absolute;
    right: 1.5rem;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.1;
    font-size: 3.5rem;
    color: var(--text-primary);
}

.action-card {
    background: var(--bg-card);
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.action-header {
    padding: 1.5rem 1.5rem 0.5rem;
    border: none;
    background: transparent;
}

.action-title {
    color: var(--text-primary);
    font-weight: 700;
    font-size: 1.2rem;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border: 2px solid transparent;
    border-radius: 10px;
    color: var(--text-primary);
    text-decoration: none;
    transition: all 0.2s;
    height: 100%;
}

.action-btn:hover {
    background: #fff;
    border-color: var(--primary-color);
    color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.15);
    transform: translateY(-2px);
}

.action-btn i {
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}

.action-btn span {
    font-weight: 600;
    font-size: 0.9rem;
}
</style>

<div class="dashboard-container">
    <div class="welcome-section">
        <h1 class="welcome-title">Dashboard Overview</h1>
        <p class="welcome-subtitle">Selamat datang kembali, <?= htmlspecialchars(getCurrentUser()['full_name']) ?> 👋</p>
    </div>
    
    <div class="row g-4 mb-5">
        <!-- Transaksi Card -->
        <div class="col-md-3">
            <div class="stat-card blue">
                <div class="stat-value"><?= $todayTransactions ?></div>
                <div class="stat-label">Transaksi Hari Ini</div>
                <div class="stat-icon-wrapper">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>
        
        <!-- Revenue Card -->
        <div class="col-md-3">
            <div class="stat-card purple">
                <div class="stat-value">Rp <?= number_format($todayRevenue/1000, 0, ',', '.') ?>k</div>
                <div class="stat-label">Revenue Hari Ini</div>
                <div class="stat-icon-wrapper">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
        
        <!-- Product Card -->
        <div class="col-md-3">
            <div class="stat-card cyan">
                <div class="stat-value"><?= $totalProducts ?></div>
                <div class="stat-label">Total Menu</div>
                <div class="stat-icon-wrapper">
                    <i class="bi bi-cup-hot"></i>
                </div>
            </div>
        </div>
        
        <!-- Users Card -->
        <div class="col-md-3">
            <div class="stat-card pink">
                <div class="stat-value"><?= $totalUsers ?></div>
                <div class="stat-label">Total Staff</div>
                <div class="stat-icon-wrapper">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="action-card">
                <div class="action-header">
                    <h5 class="action-title"><i class="bi bi-grid-fill me-2 text-primary"></i>Quick Actions</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <a href="/transactions" class="action-btn">
                                <i class="bi bi-clock-history"></i>
                                <span>Riwayat Transaksi</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="/admin/products" class="action-btn">
                                <i class="bi bi-box-seam"></i>
                                <span>Kelola Menu</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="/admin/reports" class="action-btn">
                                <i class="bi bi-graph-up-arrow"></i>
                                <span>Laporan Penjualan</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="/admin/users" class="action-btn">
                                <i class="bi bi-person-gear"></i>
                                <span>Kelola Users</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
