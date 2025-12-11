@echo off
REM Coffee Shop POS - Run Script for Windows (MySQL Version)
REM This script automatically sets up MySQL and starts the server

echo ================================================================
echo          Coffee Shop POS - MySQL Version (Windows)
echo ================================================================
echo.

REM Check PHP
echo Checking requirements...
echo.
echo 1. Checking PHP...
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP is not installed or not in PATH
    echo.
    echo Please install PHP from: https://windows.php.net/download/
    echo Make sure to add PHP to your PATH
    pause
    exit /b 1
)

for /f "tokens=*" %%i in ('php -v ^| findstr /R "^PHP"') do set PHP_VERSION=%%i
echo [OK] %PHP_VERSION%

REM Check MySQL PDO extension
echo.
echo 2. Checking MySQL PDO extension...
php -m | findstr /C:"pdo_mysql" >nul
if %errorlevel% neq 0 (
    echo [ERROR] PDO MySQL extension not found
    echo.
    echo Enable in php.ini:
    echo   1. Find php.ini file
    echo   2. Uncomment: extension=pdo_mysql
    echo   3. Restart
    pause
    exit /b 1
)
echo [OK] PDO MySQL extension found

echo.
echo ================================================================
echo.

REM Check if MySQL service is running (XAMPP/WAMP)
echo [SETUP] Checking MySQL setup...
echo.
echo 1. Checking MySQL service...
echo    Make sure MySQL is running in XAMPP/WAMP/MAMP
echo.

REM Check if database exists
echo 2. Checking database...
mysql -h 127.0.0.1 -u root -e "USE coffee_shop_pos" 2>nul
if %errorlevel% neq 0 (
    echo    Database not found. Creating database...
    
    REM Create database
    mysql -h 127.0.0.1 -u root < database_mysql.sql 2>nul
    if %errorlevel% equ 0 (
        echo    [OK] Database created successfully
    ) else (
        echo    [ERROR] Failed to create database
        echo.
        echo    Please ensure:
        echo      1. MySQL is running
        echo      2. mysql command is in PATH
        echo      3. Root user has no password (or update .env)
        pause
        exit /b 1
    )
    
    REM Migrate data from SQLite if exists
    if exist "database\coffee_shop_pos.db" (
        echo.
        echo 3. Migrating data from SQLite...
        php migrate_sqlite_to_mysql.php
        if %errorlevel% equ 0 (
            echo    [OK] Data migration completed
        ) else (
            echo    [WARNING] Migration failed, using default data
        )
    )
) else (
    echo    [OK] Database exists
)

echo.
echo [OK] MySQL setup completed!
echo.

REM Verify connection
echo Verifying database connection...
php -r "require 'config/database.php';" 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Cannot connect to MySQL database
    echo.
    echo Please check:
    echo   1. MySQL service is running (XAMPP/WAMP)
    echo   2. Credentials in .env file are correct
    echo   3. Database 'coffee_shop_pos' exists
    pause
    exit /b 1
)
echo [OK] Database connection successful

echo.
echo ================================================================
echo   Starting Application Server
echo ================================================================
echo.
echo [APP] Access: http://localhost:8000
echo.
echo [LOGIN] Credentials:
echo    Admin  - Username: admin  ^| Password: admin123
echo    Kasir  - Username: kasir  ^| Password: kasir123
echo.
echo [DB] phpMyAdmin: http://localhost/phpmyadmin
echo    Username: root ^| Password: (empty)
echo    Database: coffee_shop_pos
echo.
echo Press Ctrl+C to stop the server
echo.
echo ================================================================
echo.

cd public
php -S localhost:8000
