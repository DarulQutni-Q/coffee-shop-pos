# SQLite to MySQL Migration Guide

## Overview

This guide will help you migrate your Coffee Shop POS application from SQLite to MySQL database.

## Prerequisites

- MySQL/MariaDB installed and running
- PHP MySQL extension (pdo_mysql)
- Root access to MySQL (or appropriate credentials)

## Migration Steps

### Step 1: Start MySQL Service

```bash
sudo systemctl start mysql
sudo systemctl enable mysql  # Auto-start on boot
```

### Step 2: Run Setup Script

The setup script will automatically:
- Create the MySQL database
- Create all tables with proper schema
- Migrate all data from SQLite to MySQL

```bash
cd /home/darulqutni/joki
./setup_mysql.sh
```

### Step 3: Verify Migration

After setup completes, verify the data:

```bash
mysql -u root -p coffee_shop_pos -e "
SELECT 
  (SELECT COUNT(*) FROM users) as users,
  (SELECT COUNT(*) FROM categories) as categories,
  (SELECT COUNT(*) FROM products) as products,
  (SELECT COUNT(*) FROM transactions) as transactions,
  (SELECT COUNT(*) FROM transaction_items) as items;
"
```

Expected output:
- users: 2
- categories: 3
- products: 21
- transactions: 4
- transaction_items: 7

### Step 4: Run Application

```bash
./run.sh
```

Access at: http://localhost:8000

## Accessing phpMyAdmin

Now you can view and manage your database using phpMyAdmin:

1. **Open phpMyAdmin** in your browser:
   - URL: `http://localhost/phpmyadmin`
   - Or: `http://localhost:8080/phpmyadmin` (depends on your setup)

2. **Login with MySQL credentials**:
   - Username: `root`
   - Password: (empty, or your MySQL root password)

3. **Select database**: Click on `coffee_shop_pos` in the left sidebar

4. **Browse tables**: You can now view and edit all tables:
   - users
   - categories
   - products
   - transactions
   - transaction_items

## Manual Migration (Alternative)

If you prefer to migrate manually:

### 1. Create Database

```bash
mysql -u root < database_mysql.sql
```

### 2. Migrate Data

```bash
php migrate_sqlite_to_mysql.php
```

## Rollback to SQLite

If you need to rollback to SQLite:

1. Restore original `config/database.php`:
   ```bash
   git checkout config/database.php
   ```

2. Your SQLite database is still intact at:
   ```
   database/coffee_shop_pos.db
   ```

## Differences: SQLite vs MySQL

| Feature | SQLite | MySQL |
|---------|--------|-------|
| **Server** | File-based | Client-server |
| **Data Types** | TEXT, INTEGER, REAL | VARCHAR, INT, DECIMAL |
| **Auto Increment** | AUTOINCREMENT | AUTO_INCREMENT |
| **Management** | File operations | phpMyAdmin, MySQL CLI |
| **Performance** | Good for small apps | Better for concurrent users |
| **Backup** | Copy .db file | mysqldump |

## Backup MySQL Database

### Using mysqldump

```bash
# Backup
mysqldump -u root coffee_shop_pos > backup_$(date +%Y%m%d).sql

# Restore
mysql -u root coffee_shop_pos < backup_20251212.sql
```

### Using phpMyAdmin

1. Go to phpMyAdmin
2. Select `coffee_shop_pos` database
3. Click **Export** tab
4. Choose format (SQL recommended)
5. Click **Go** to download

## Troubleshooting

### MySQL Service Not Running

```bash
sudo systemctl status mysql
sudo systemctl start mysql
```

### Can't Connect to MySQL

Check credentials in `.env` file:
```bash
cat .env
```

### phpMyAdmin Not Found

Install phpMyAdmin:
```bash
sudo apt install phpmyadmin
```

### Permission Denied

Grant permissions:
```bash
mysql -u root -e "GRANT ALL PRIVILEGES ON coffee_shop_pos.* TO 'root'@'localhost';"
```

## Support

If you encounter any issues during migration, please check:
1. MySQL service is running
2. Credentials in `.env` are correct
3. PHP MySQL extension is installed
4. SQLite database file exists and has data

---

**Migration completed successfully?** 🎉

You can now enjoy using phpMyAdmin to manage your Coffee Shop POS database!
