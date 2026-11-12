# Coffee Shop POS - MySQL Version

Aplikasi Point of Sale (POS) untuk coffee shop menggunakan **PHP native + MySQL**. Sekarang bisa dikelola dengan **phpMyAdmin**!

## ✨ Keunggulan Versi MySQL

- ✅ **phpMyAdmin Support** - Kelola database dengan interface visual
- ✅ **Better Performance** - Optimal untuk multiple users
- ✅ **Professional** - Standard database untuk production
- ✅ **Easy Management** - View, edit, export data dengan mudah
- ✅ **Auto Setup** - Database otomatis dibuat saat pertama kali run

Pastikan XAMPP/WAMP/MAMP sudah running!

### Akses Aplikasi

Buka browser: **http://localhost:8000**

**Login Credentials (demo only):**
- **Admin**: username `admin` password `admin123`
- **Kasir**: username `kasir` password `kasir123`

> Peringatan: kredensial di atas hanya untuk demo lokal. Ganti password default sebelum aplikasi dipakai bersama atau di-demo ke orang lain.

## 🗄️ phpMyAdmin Access

Sekarang Anda bisa melihat dan mengelola database dengan phpMyAdmin!

**Cara Akses:**
1. Buka browser: `http://localhost/phpmyadmin`
2. Login:
   - Username: `root`
   - Password: (kosong, atau password MySQL Anda)
3. Pilih database: `coffee_shop_pos`

**Apa yang bisa dilakukan di phpMyAdmin:**
- ✅ View semua data (users, products, transactions)
- ✅ Edit data langsung
- ✅ Export database (backup)
- ✅ Import data
- ✅ Run SQL queries
- ✅ Lihat struktur tables

## 📂 Struktur Project

```
coffee-shop-pos/
├── assets/                # CSS & JavaScript
├── config/                # Database & Auth config (MySQL)
├── controllers/           # Business logic
├── views/                 # HTML templates
├── public/                # Entry point (index.php)
├── database/              # SQLite backup (legacy)
├── database_mysql.sql     # MySQL schema
├── migrate_sqlite_to_mysql.php  # Migration script
├── setup_mysql.sh         # Setup script
├── run.sh                 # Run script
├── .env                   # MySQL credentials
└── README.md
```

## 💾 Database

**Database Name**: `coffee_shop_pos`

**Tables:**
- `users` - User accounts (admin, kasir)
- `categories` - Product categories
- `products` - Menu items
- `transactions` - Sales transactions
- `transaction_items` - Transaction details

**Backup Database:**
```bash
# Using mysqldump
mysqldump -u root coffee_shop_pos > backup_$(date +%Y%m%d).sql

# Using phpMyAdmin
# Go to Export tab and download
```

## 🎯 Fitur Lengkap

### Admin
- ✅ Dashboard dengan statistik
- ✅ Kelola Users (CRUD)
- ✅ Kelola Produk (CRUD)
- ✅ Lihat semua transaksi
- ✅ Laporan penjualan dengan filter
- ✅ Refund transaksi

### Kasir
- ✅ Interface POS
- ✅ Shopping cart
- ✅ Kalkulasi otomatis
- ✅ Print struk
- ✅ Lihat transaksi sendiri

## 🛠️ Troubleshooting

### Linux: MySQL Service Not Running

Script akan otomatis start MySQL, tapi jika gagal:
```bash
sudo systemctl start mysql
sudo systemctl enable mysql  # Auto-start on boot
```

### Windows: MySQL Not Running

Pastikan XAMPP/WAMP/MAMP sudah running:
- XAMPP: Start "Apache" dan "MySQL" di XAMPP Control Panel
- WAMP: Klik icon WAMP, pilih "Start All Services"

### Error: PDO extension not found

**Linux:**
```bash
# Ubuntu/Debian
sudo apt install php-mysql

# Arch Linux
sudo pacman -S php-mysql
```

**Windows:**
Edit `php.ini` dan uncomment:
```
extension=pdo_mysql
```

### Error: Port 8000 already in use

Gunakan port lain:
```bash
cd public
php -S localhost:8001
```

### Reset Database

**Linux:**
```bash
mysql -u root -e "DROP DATABASE coffee_shop_pos;"
./run.sh  # Will recreate automatically
```

**Windows:**
```cmd
mysql -u root -e "DROP DATABASE coffee_shop_pos;"
run.bat
```

### Migration from SQLite

Jika Anda punya data di SQLite dan ingin migrate ke MySQL:
```bash
./setup_mysql.sh
```

Lihat [MIGRATION_GUIDE.md](MIGRATION_GUIDE.md) untuk detail lengkap.

## 📝 Configuration

Edit `.env` file untuk MySQL credentials:
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=coffee_shop_pos
DB_USER=root
DB_PASS=
```

## 🔒 Keamanan

- ✅ Password hashing (bcrypt)
- ✅ Prepared statements (SQL injection protection)
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Foreign key constraints

## 📱 Browser Support

- Chrome/Chromium ✅
- Firefox ✅
- Edge ✅
- Safari ✅

## 🎓 Development

Aplikasi ini dibuat dengan:
- **PHP 8.3** (compatible dengan PHP 7.4+)
- **MySQL 8.0** (compatible dengan MySQL 5.7+)
- **Bootstrap 5**
- **Vanilla JavaScript**

---

**Selamat menggunakan Coffee Shop POS dengan MySQL + phpMyAdmin!** ☕

Jika ada pertanyaan atau issue, silakan hubungi developer.

