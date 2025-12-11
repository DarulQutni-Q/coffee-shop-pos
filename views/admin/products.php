<?php
require_once __DIR__ . '/../../controllers/ProductController.php';
$pageTitle = 'Manajemen Produk';
include __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-box"></i> Manajemen Produk</h2>
        <?php if (isAdmin()): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetProductForm()">
            <i class="bi bi-plus-circle"></i> Tambah Produk
        </button>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <?php if (isAdmin()): ?>
                            <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                            <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge bg-<?= $product['status'] === 'tersedia' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($product['status']) ?>
                                </span>
                            </td>
                            <?php if (isAdmin()): ?>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editProduct(<?= $product['id'] ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-<?= $product['status'] === 'tersedia' ? 'secondary' : 'success' ?>" 
                                        onclick="toggleStatus(<?= $product['id'] ?>)">
                                    <i class="bi bi-toggle-<?= $product['status'] === 'tersedia' ? 'on' : 'off' ?>"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $product['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if (isAdmin()): ?>
<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">Tambah Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" id="productId" name="id">
                    <input type="hidden" id="productAction" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="productName" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="productName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryId" class="form-label">Kategori</label>
                        <select class="form-select" id="categoryId" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price" class="form-label">Harga</label>
                        <input type="number" class="form-control" id="price" name="price" min="0" step="1000" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="habis">Habis</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveProduct()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
function resetProductForm() {
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('productAction').value = 'add';
    document.getElementById('productModalTitle').textContent = 'Tambah Produk';
}

function editProduct(id) {
    fetch('/admin/products', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get&id=' + id
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('productId').value = data.product.id;
            document.getElementById('productName').value = data.product.name;
            document.getElementById('categoryId').value = data.product.category_id;
            document.getElementById('price').value = data.product.price;
            document.getElementById('status').value = data.product.status;
            document.getElementById('productAction').value = 'update';
            document.getElementById('productModalTitle').textContent = 'Edit Produk';
            new bootstrap.Modal(document.getElementById('productModal')).show();
        }
    });
}

function saveProduct() {
    const form = document.getElementById('productForm');
    const formData = new FormData(form);
    
    fetch('/admin/products', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            location.reload();
        }
    });
}

function deleteProduct(id) {
    if (confirm('Yakin ingin menghapus produk ini?')) {
        fetch('/admin/products', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=delete&id=' + id
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

function toggleStatus(id) {
    fetch('/admin/products', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=toggle_status&id=' + id
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            location.reload();
        }
    });
}
</script>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
