<?php
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../../controllers/TransactionController.php';
$pageTitle = 'Point of Sale';
include __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <h2 class="mb-4"><i class="bi bi-cart"></i> Point of Sale</h2>
    
    <div class="row">
        <!-- Products Section -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Produk</h5>
                </div>
                <div class="card-body">
                    <!-- Category Filter -->
                    <div class="btn-group mb-3" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="filterCategory(null)">
                            Semua
                        </button>
                        <?php foreach ($categories as $category): ?>
                        <button type="button" class="btn btn-outline-primary" onclick="filterCategory(<?= $category['id'] ?>)">
                            <?= htmlspecialchars($category['name']) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Products Grid -->
                    <div class="row g-3" id="productsGrid">
                        <?php foreach ($products as $product): ?>
                        <div class="col-md-4 product-item" data-category="<?= $product['category_id'] ?>">
                            <div class="card h-100 product-card" onclick="addToCart(<?= htmlspecialchars(json_encode($product)) ?>)">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="text-muted mb-2"><?= htmlspecialchars($product['category_name']) ?></p>
                                    <h4 class="text-primary">Rp <?= number_format($product['price'], 0, ',', '.') ?></h4>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cart Section -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-cart3"></i> Keranjang</h5>
                </div>
                <div class="card-body">
                    <div id="cartItems" class="mb-3">
                        <p class="text-muted text-center">Keranjang kosong</p>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total:</strong>
                            <strong id="totalAmount">Rp 0</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label">Jumlah Bayar</label>
                        <input type="number" class="form-control" id="paymentAmount" min="0" step="1000" 
                               oninput="calculateChange()">
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>Kembalian:</strong>
                            <strong id="changeAmount" class="text-success">Rp 0</strong>
                        </div>
                    </div>
                    
                    <button class="btn btn-success w-100" id="checkoutBtn" onclick="checkout()" disabled>
                        <i class="bi bi-check-circle"></i> Checkout
                    </button>
                    <button class="btn btn-outline-danger w-100 mt-2" onclick="clearCart()">
                        <i class="bi bi-trash"></i> Kosongkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];
let currentCategory = null;

function filterCategory(categoryId) {
    currentCategory = categoryId;
    const buttons = document.querySelectorAll('.btn-group button');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    const items = document.querySelectorAll('.product-item');
    items.forEach(item => {
        if (categoryId === null || item.dataset.category == categoryId) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function addToCart(product) {
    const existingItem = cart.find(item => item.id === product.id);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1
        });
    }
    
    updateCart();
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCart();
}

function updateQuantity(productId, quantity) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.quantity = parseInt(quantity);
        if (item.quantity <= 0) {
            removeFromCart(productId);
        } else {
            updateCart();
        }
    }
}

function updateCart() {
    const cartItemsDiv = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = '<p class="text-muted text-center">Keranjang kosong</p>';
        document.getElementById('totalAmount').textContent = 'Rp 0';
        document.getElementById('checkoutBtn').disabled = true;
        return;
    }
    
    let html = '';
    let total = 0;
    
    cart.forEach(item => {
        const subtotal = item.price * item.quantity;
        total += subtotal;
        
        html += `
            <div class="cart-item mb-2 p-2 border rounded">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong>${item.name}</strong>
                    <button class="btn btn-sm btn-danger" onclick="removeFromCart(${item.id})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="input-group input-group-sm" style="width: 100px;">
                        <button class="btn btn-outline-secondary" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                        <input type="number" class="form-control text-center" value="${item.quantity}" 
                               onchange="updateQuantity(${item.id}, this.value)" min="1">
                        <button class="btn btn-outline-secondary" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                    </div>
                    <span>Rp ${formatNumber(subtotal)}</span>
                </div>
            </div>
        `;
    });
    
    cartItemsDiv.innerHTML = html;
    document.getElementById('totalAmount').textContent = 'Rp ' + formatNumber(total);
    document.getElementById('checkoutBtn').disabled = false;
    
    calculateChange();
}

function calculateChange() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const payment = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const change = payment - total;
    
    document.getElementById('changeAmount').textContent = 'Rp ' + formatNumber(Math.max(0, change));
    
    if (payment >= total && total > 0) {
        document.getElementById('checkoutBtn').disabled = false;
    } else {
        document.getElementById('checkoutBtn').disabled = true;
    }
}

function clearCart() {
    if (confirm('Yakin ingin mengosongkan keranjang?')) {
        cart = [];
        document.getElementById('paymentAmount').value = '';
        updateCart();
    }
}

function checkout() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const payment = parseFloat(document.getElementById('paymentAmount').value) || 0;
    
    if (payment < total) {
        alert('Jumlah pembayaran kurang!');
        return;
    }
    
    if (cart.length === 0) {
        alert('Keranjang kosong!');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'checkout');
    formData.append('items', JSON.stringify(cart));
    formData.append('payment_amount', payment);
    
    fetch('/kasir/pos', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Transaksi berhasil!\nKode: ' + data.transaction_code);
            // Open receipt in new window
            window.open('/transactions/receipt?id=' + data.transaction_id, '_blank');
            // Clear cart
            cart = [];
            document.getElementById('paymentAmount').value = '';
            updateCart();
        } else {
            alert('Gagal: ' + data.message);
        }
    });
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
