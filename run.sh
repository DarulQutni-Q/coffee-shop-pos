#!/bin/bash

# Coffee Shop POS - Run Script (MySQL Version)
# This script automatically sets up MySQL and starts the server

echo "╔════════════════════════════════════════════════════════════╗"
echo "║         Coffee Shop POS - MySQL Version                   ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Function to check and setup MySQL
setup_mysql() {
    echo -e "${BLUE}[SETUP] Checking Database...${NC}"
    echo ""
    
    # 1. Check if MySQL is accessible (XAMPP or System)
    # Using -h 127.0.0.1 forces TCP connection, avoiding socket mismatch issues
    if mysql -h 127.0.0.1 -u root -e "SELECT 1" &>/dev/null; then
        echo -e "${GREEN}   ✓ MySQL is running${NC}"
    else
        # Not running, try to detect XAMPP
        if [ -f "/opt/lampp/lampp" ]; then
            echo -e "${YELLOW}   ⚠ MySQL is not running.${NC}"
            echo -e "${BLUE}   It seems you have XAMPP installed.${NC}"
            echo ""
            echo "   Please start XAMPP MySQL:"
            echo "   ${YELLOW}sudo /opt/lampp/lampp startmysql${NC}"
            echo ""
            echo "   Or start full XAMPP:"
            echo "   ${YELLOW}sudo /opt/lampp/lampp start${NC}"
            exit 1
        elif command -v systemctl &>/dev/null; then
            # Try system MySQL as fallback
            echo -e "${YELLOW}   ⚠ MySQL is not running. Starting System MySQL...${NC}"
            sudo systemctl start mysql
            if [ $? -ne 0 ]; then
                echo -e "${RED}   ✗ Failed to start System MySQL${NC}"
                exit 1
            fi
        else
             echo -e "${RED}   ✗ MySQL is not running and cannot be started automatically${NC}"
             exit 1
        fi
    fi
    
    # 2. Check Database Existence
    echo ""
    echo "2. Checking database 'coffee_shop_pos'..."
    if ! mysql -h 127.0.0.1 -u root -e "USE coffee_shop_pos" 2>/dev/null; then
        echo -e "${YELLOW}   ⚠ Database not found. Creating database...${NC}"
        
        # Create database
        if mysql -h 127.0.0.1 -u root < database_mysql.sql 2>/dev/null; then
            echo -e "${GREEN}   ✓ Database created successfully${NC}"
            
            # Migrate data from SQLite if exists
            if [ -f "database/coffee_shop_pos.db" ]; then
                echo ""
                echo "3. Migrating data from SQLite..."
                php migrate_sqlite_to_mysql.php
                if [ $? -eq 0 ]; then
                    echo -e "${GREEN}   ✓ Data migration completed${NC}"
                else
                    echo -e "${YELLOW}   ⚠ Migration failed, using default data${NC}"
                fi
            fi
        else
            echo -e "${RED}   ✗ Failed to create database${NC}"
            echo "   Check if you have a password set for root user."
            echo "   If yes, update .env file."
            exit 1
        fi
    else
        echo -e "${GREEN}   ✓ Database exists${NC}"
    fi
    
    echo ""
    echo -e "${GREEN}✓ Setup completed!${NC}"
    echo ""
}

# Check PHP
echo "Checking requirements..."
echo ""
echo "1. Checking PHP..."
if ! command -v php &> /dev/null; then
    echo -e "${RED}   ✗ PHP is not installed${NC}"
    echo ""
    echo "Install PHP:"
    echo "  Ubuntu/Debian: sudo apt install php php-mysql"
    echo "  Arch Linux: sudo pacman -S php php-mysql"
    exit 1
fi
PHP_VERSION=$(php -v | head -n 1)
echo -e "${GREEN}   ✓ $PHP_VERSION${NC}"

# Check MySQL PDO extension
echo ""
echo "2. Checking MySQL PDO extension..."
if ! php -m | grep -q "pdo_mysql"; then
    echo -e "${RED}   ✗ PDO MySQL extension not found${NC}"
    echo ""
    echo "Install MySQL extension:"
    echo "  Ubuntu/Debian: sudo apt install php-mysql"
    echo "  Arch Linux: sudo pacman -S php-mysql"
    exit 1
fi
echo -e "${GREEN}   ✓ PDO MySQL extension found${NC}"

echo ""
echo "════════════════════════════════════════════════════════════"
echo ""

# Setup MySQL (auto-setup if needed)
setup_mysql

# Verify connection
echo "Verifying database connection..."
if ! php -r "require 'config/database.php';" 2>/dev/null; then
    echo -e "${RED}✗ Cannot connect to MySQL database${NC}"
    echo ""
    echo "Please check:"
    echo "  1. MySQL service is running: sudo systemctl status mysql"
    echo "  2. Credentials in .env file are correct"
    exit 1
fi
echo -e "${GREEN}✓ Database connection successful${NC}"

echo ""
echo "════════════════════════════════════════════════════════════"
echo "  Starting Application Server"
echo "════════════════════════════════════════════════════════════"
echo ""
echo -e "📱 Access: ${YELLOW}http://localhost:8000${NC}"
echo ""
echo "🔐 Login Credentials:"
echo "   Admin  → Username: ${YELLOW}admin${NC}  | Password: ${YELLOW}admin123${NC}"
echo "   Kasir  → Username: ${YELLOW}kasir${NC}  | Password: ${YELLOW}kasir123${NC}"
echo ""
echo "🗄️  phpMyAdmin: ${YELLOW}http://localhost/phpmyadmin${NC}"
echo "   Username: ${YELLOW}root${NC} | Password: ${YELLOW}(empty)${NC}"
echo "   Database: ${YELLOW}coffee_shop_pos${NC}"
echo ""
echo "Press ${RED}Ctrl+C${NC} to stop the server"
echo ""
echo "════════════════════════════════════════════════════════════"
echo ""

cd public
php -S localhost:8000
