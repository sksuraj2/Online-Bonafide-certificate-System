@echo off
REM Installation Verification Script for Online Bonafide Certificate System
REM This script helps verify that the system was cloned and set up correctly on Windows

echo ========================================
echo Online Bonafide Certificate System
echo Installation Verification Script
echo ========================================
echo.

setlocal enabledelayedexpansion
set PASSED=0
set FAILED=0
set WARNINGS=0

REM Check 1: Git installation
echo Checking Git installation...
where git >nul 2>&1
if %errorlevel% equ 0 (
    for /f "tokens=*" %%i in ('git --version') do set GIT_VERSION=%%i
    echo [OK] !GIT_VERSION!
    set /a PASSED+=1
) else (
    echo [ERROR] Git not found
    echo   Install git to clone the repository
    set /a FAILED+=1
)

REM Check 2: PHP installation
echo Checking PHP installation...
where php >nul 2>&1
if %errorlevel% equ 0 (
    for /f "tokens=*" %%i in ('php -v ^| findstr /C:"PHP"') do set PHP_VERSION=%%i
    echo [OK] !PHP_VERSION!
    set /a PASSED+=1
    
    REM Check PHP version
    for /f %%i in ('php -r "echo PHP_VERSION;"') do set PHP_VER=%%i
    echo   PHP Version: !PHP_VER!
    set /a PASSED+=1
) else (
    echo [ERROR] PHP not found
    echo   Install PHP 7.4+ to run the system (XAMPP recommended)
    set /a FAILED+=1
)

REM Check 3: MySQL
echo Checking MySQL installation...
where mysql >nul 2>&1
if %errorlevel% equ 0 (
    for /f "tokens=*" %%i in ('mysql --version') do set MYSQL_VERSION=%%i
    echo [OK] !MYSQL_VERSION!
    set /a PASSED+=1
) else (
    echo [WARNING] MySQL client not found in PATH
    echo   Ensure MySQL is installed (may be in XAMPP)
    set /a WARNINGS+=1
)

REM Check 4: Required files
echo Checking required files:
set FILES=connection.php login.php register.php admin.php user.php form.php setup_database.php verify_system.php README.md

for %%f in (%FILES%) do (
    echo   - %%f
    if exist "%%f" (
        echo     [OK] Found
        set /a PASSED+=1
    ) else (
        echo     [ERROR] Missing
        set /a FAILED+=1
    )
)

REM Check 5: Git repository
echo Checking Git repository...
if exist ".git" (
    echo [OK] Git repository initialized
    set /a PASSED+=1
    
    for /f "tokens=*" %%i in ('git config --get remote.origin.url 2^>nul') do set REMOTE_URL=%%i
    if defined REMOTE_URL (
        echo   Remote URL: !REMOTE_URL!
        echo !REMOTE_URL! | findstr /C:"sksuraj2/Online-Bonafide-certificate-System" >nul
        if !errorlevel! equ 0 (
            echo   [OK] Correct repository
            set /a PASSED+=1
        ) else (
            echo   [WARNING] Different repository URL
            set /a WARNINGS+=1
        )
    )
) else (
    echo [WARNING] Not a git repository
    echo   Clone from: git clone https://github.com/sksuraj2/Online-Bonafide-certificate-System.git
    set /a WARNINGS+=1
)

REM Summary
echo.
echo ========================================
echo Summary
echo ========================================
echo Passed: %PASSED%
echo Warnings: %WARNINGS%
echo Failed: %FAILED%
echo.

if %FAILED% equ 0 (
    echo [SUCCESS] System verification completed successfully!
    echo.
    echo Next steps:
    echo 1. Ensure XAMPP is running
    echo 2. Place this folder in htdocs directory: C:\xampp\htdocs\
    echo 3. Visit: http://localhost/Online-Bonafide-certificate-System/verify_system.php
    echo 4. Run setup_database.php to create database tables
    echo 5. Access the application via login.php
    echo.
    pause
    exit /b 0
) else (
    echo [ERROR] System verification found errors!
    echo.
    echo Please fix the issues above before proceeding.
    echo See README.md for detailed installation instructions.
    echo.
    pause
    exit /b 1
)
