# Quick Start Guide - Online Bonafide Certificate System

This guide will help you get the system up and running quickly on your machine.

## Prerequisites Check

Before starting, ensure you have:
- [ ] XAMPP (or LAMP/MAMP) installed
- [ ] Git installed (for cloning)
- [ ] PHP 7.4 or higher
- [ ] MySQL 5.7 or higher
- [ ] A web browser

## Installation Steps

### Step 1: Clone the Repository

Open your terminal/command prompt and run:

```bash
git clone https://github.com/sksuraj2/Online-Bonafide-certificate-System.git
cd Online-Bonafide-certificate-System
```

**Alternative:** Download as ZIP from:
`https://github.com/sksuraj2/Online-Bonafide-certificate-System/archive/refs/heads/main.zip`

### Step 2: Verify Prerequisites

Run the verification script to check your system:

**Linux/Mac:**
```bash
./verify_installation.sh
```

**Windows:**
```cmd
verify_installation.bat
```

This will check if all required components are installed.

### Step 3: Move to Web Server Directory

Copy or move the folder to your web server's document root:

**Windows (XAMPP):**
```
C:\xampp\htdocs\Online-Bonafide-certificate-System
```

**Linux (LAMP):**
```
/var/www/html/Online-Bonafide-certificate-System
```

**Mac (XAMPP):**
```
/Applications/XAMPP/htdocs/Online-Bonafide-certificate-System
```

### Step 4: Start Web Server Services

1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service
4. Verify both show green/running status

### Step 5: Create Database

**Option A - Using phpMyAdmin:**
1. Open browser: `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Enter database name: `bonafide`
4. Click "Create"

**Option B - Using Setup Script:**
1. Open browser: `http://localhost/Online-Bonafide-certificate-System/setup_database.php`
2. This will automatically create database and tables

### Step 6: Verify System Setup

Open: `http://localhost/Online-Bonafide-certificate-System/verify_system.php`

This comprehensive verification will check:
- ✓ PHP version and extensions
- ✓ Database connection
- ✓ Database tables
- ✓ Required files
- ✓ Web server configuration

Fix any issues reported before proceeding.

### Step 7: Access the Application

**User Registration:**
```
http://localhost/Online-Bonafide-certificate-System/register.php
```

**Login Page:**
```
http://localhost/Online-Bonafide-certificate-System/login.php
```

**Admin Dashboard:**
```
http://localhost/Online-Bonafide-certificate-System/admin.php
```

## Quick Troubleshooting

### Issue: "Connection failed" error
**Fix:**
1. Check MySQL is running in XAMPP
2. Create database named `bonafide`
3. Verify `connection.php` has correct credentials

### Issue: 404 Not Found
**Fix:**
1. Verify folder is in `htdocs` directory
2. Check folder name is correct
3. Ensure Apache is running

### Issue: "Table doesn't exist"
**Fix:**
Run: `http://localhost/Online-Bonafide-certificate-System/setup_database.php`

### Issue: PHP errors displayed
**Fix:**
1. Check PHP version: `php -v` (should be 7.4+)
2. Enable required extensions in `php.ini`
3. Restart Apache after changes

## Testing the Clone URL

To verify the clone URL works on another system:

1. **On the new system**, open terminal/command prompt
2. Run:
   ```bash
   git clone https://github.com/sksuraj2/Online-Bonafide-certificate-System.git
   ```
3. Verify the clone completed successfully
4. Check remote URL:
   ```bash
   cd Online-Bonafide-certificate-System
   git remote -v
   ```
5. Should show: `origin  https://github.com/sksuraj2/Online-Bonafide-certificate-System`

## Default Configuration

### Database Settings (connection.php)
```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bonafide";
```

### Required PHP Extensions
- mysqli
- mbstring  
- json
- session

## Support

If you encounter any issues:
1. Check the [Troubleshooting section](README.md#-troubleshooting) in README.md
2. Run `verify_system.php` for detailed diagnostics
3. Create an issue on GitHub with:
   - Error message
   - PHP version
   - OS and web server details
   - Screenshot of the error

## Next Steps

After successful installation:
1. Register as a new student user
2. Login and explore the student portal
3. Apply for a bonafide certificate
4. Access admin panel to manage applications
5. Configure system settings as needed

---

**Repository:** https://github.com/sksuraj2/Online-Bonafide-certificate-System

**Documentation:** See [README.md](README.md) for complete details
