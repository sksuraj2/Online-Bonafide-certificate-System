# Online Bonafide Certificate System

A web-based system for managing bonafide certificate applications for educational institutions.

## 🎓 Features

### Student Portal
- User registration and login
- Apply for bonafide certificates
- Track application status
- View approved certificates
- Download certificates in PDF format
- User profile management

### Admin Panel
- Dashboard with statistics
- Manage students
- View all certificates
- Approve/Reject applications
- Pending requests management
- System settings

## 💻 Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Libraries**: Font Awesome, Animate.css

## 📋 Prerequisites

- XAMPP (or any PHP development environment)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

## 🚀 Installation

### Quick Start Guide

1. **Clone the repository**
   ```bash
   git clone https://github.com/sksuraj2/Online-Bonafide-certificate-System.git
   cd Online-Bonafide-certificate-System
   ```
   
   Or download ZIP:
   ```
   https://github.com/sksuraj2/Online-Bonafide-certificate-System/archive/refs/heads/main.zip
   ```

2. **Quick Verification (Optional but Recommended)**
   
   Run the command-line verification script to check prerequisites:
   
   **For Linux/Mac:**
   ```bash
   ./verify_installation.sh
   ```
   
   **For Windows:**
   ```cmd
   verify_installation.bat
   ```
   
   This will verify:
   - Git installation
   - PHP version and extensions
   - MySQL availability
   - Required files presence

3. **Move to XAMPP htdocs** (or your web server directory)
   
   **For Windows:**
   ```bash
   cd C:\xampp\htdocs
   ```
   
   **For Linux/Mac:**
   ```bash
   cd /opt/lampp/htdocs
   # or
   cd /Applications/XAMPP/htdocs
   ```

3. **Start Apache & MySQL**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services
   - Ensure both services are running (green indicators)

4. **Configure Database**
   - Update database credentials in `connection.php` if needed:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "bonafide";
   ```

5. **Create Database & Tables**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `bonafide`
   - Or run the setup script: `http://localhost/Online-Bonafide-certificate-System/setup_database.php`

6. **Verify Installation**
   - **⭐ Run the system verification script:**
   ```
   http://localhost/Online-Bonafide-certificate-System/verify_system.php
   ```
   - This will check all system requirements and configurations
   - Follow any recommendations or fixes suggested by the verification script

7. **Access the Application**
   - Open browser and navigate to: 
   ```
   http://localhost/Online-Bonafide-certificate-System/login.php
   ```

## 👤 Default Login

### Admin Login
- Navigate to: `http://localhost/Bonafide/login.php`
- Create admin account or use default credentials (if set)

### Student Login
- Register as new student
- Login with registered credentials

## 📁 Project Structure

```
Online-Bonafide-certificate-System/
├── admin.php                 # Admin dashboard
├── admin_approved.php        # Approved applications
├── admin_certificates.php    # All certificates view
├── admin_pending.php         # Pending requests
├── admin_settings.php        # System settings
├── admin_students.php        # Student management
├── user.php                  # User dashboard
├── user_certificates.php     # User certificates page
├── form.php                  # Application form
├── login.php                 # Login page
├── register.php              # Registration page
├── profile.php               # User profile
├── generate_certificate.php  # Certificate generator
├── connection.php            # Database connection
├── submit_form.php           # Form submission handler
├── logout.php                # Logout handler
├── setup_database.php        # Database setup script
├── verify_system.php         # System verification script
└── README.md                 # Documentation
```

## 🔐 Security Features

- Session management
- Password hashing
- SQL injection prevention (prepared statements)
- XSS protection with `htmlspecialchars()`
- Admin authentication
- Input validation

## ✅ System Verification

After installation, verify your setup is working correctly:

### Automated Verification
Run the system verification script:
```
http://localhost/Online-Bonafide-certificate-System/verify_system.php
```

This will automatically check:
- ✓ PHP version and required extensions
- ✓ All required files are present
- ✓ Database connection
- ✓ Database tables exist
- ✓ Web server configuration
- ✓ Git repository setup

### Manual Verification Steps

1. **Check PHP Version**
   ```bash
   php -v
   # Should show PHP 7.4 or higher
   ```

2. **Check MySQL Service**
   - Open XAMPP Control Panel
   - Verify MySQL is running (green indicator)
   - Or visit: `http://localhost/phpmyadmin`

3. **Test Database Connection**
   ```
   http://localhost/Online-Bonafide-certificate-System/check_db.php
   ```

4. **Verify Clone URL**
   The official repository clone URL:
   ```bash
   git clone https://github.com/sksuraj2/Online-Bonafide-certificate-System.git
   ```
   
   To verify the remote URL in your cloned repository:
   ```bash
   cd Online-Bonafide-certificate-System
   git remote -v
   # Should show: origin  https://github.com/sksuraj2/Online-Bonafide-certificate-System
   ```

## 🔧 Troubleshooting

### Common Issues and Solutions

#### Issue 1: "Connection failed" error
**Solution:**
- Ensure MySQL service is running in XAMPP
- Verify database name is `bonafide`
- Check `connection.php` has correct credentials
- Create database using: `http://localhost/Online-Bonafide-certificate-System/setup_database.php`

#### Issue 2: "404 Not Found" when accessing pages
**Solution:**
- Verify you're using correct URL: `http://localhost/Online-Bonafide-certificate-System/`
- Check the folder is in htdocs directory
- Ensure Apache is running in XAMPP

#### Issue 3: "Table doesn't exist" errors
**Solution:**
- Run database setup: `http://localhost/Online-Bonafide-certificate-System/setup_database.php`
- Or manually create tables using phpMyAdmin
- Check database name in `connection.php` matches actual database

#### Issue 4: Clone URL not working
**Solution:**
- Verify you have git installed: `git --version`
- Use HTTPS URL: `https://github.com/sksuraj2/Online-Bonafide-certificate-System.git`
- If behind proxy, configure git proxy settings
- Alternative: Download ZIP from GitHub

#### Issue 5: PHP version too old
**Solution:**
- Update XAMPP to latest version (includes PHP 8.x)
- Or install PHP 7.4+ separately
- Check version: `php -v`

#### Issue 6: Missing PHP extensions
**Solution:**
- Enable required extensions in `php.ini`:
  - `extension=mysqli`
  - `extension=mbstring`
- Restart Apache after changes

### Getting Help

If you encounter issues not listed above:
1. Run `verify_system.php` for detailed diagnostics
2. Check error logs in XAMPP: `xampp/apache/logs/error.log`
3. Enable error display in PHP (for development):
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
4. Create an issue on GitHub with error details

## 🎯 Future Enhancements

- Email notifications
- Digital signatures on certificates
- QR code verification
- Mobile app
- Multi-language support
- Payment gateway integration
- Advanced reporting

## 🐛 Known Issues

- None currently reported

### Testing on Different Systems

This system has been verified to work on:
- ✅ Windows with XAMPP
- ✅ Linux with LAMP stack
- ✅ macOS with XAMPP/MAMP

To test on your system:
1. Clone using the official URL: `https://github.com/sksuraj2/Online-Bonafide-certificate-System.git`
2. Run `verify_system.php` to check compatibility
3. Report any issues on GitHub

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 👨‍💻 Author

sksuraj2

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

## 📞 Support

For support, create an issue on GitHub.

---

**Note**: This is an educational project. For production use, additional security measures and testing are recommended.
