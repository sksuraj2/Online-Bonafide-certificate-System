# Bonafide Certificate System

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

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/bonafide-certificate-system.git
   ```

2. **Move to XAMPP htdocs**
   ```bash
   cd C:\xampp\htdocs
   ```

3. **Import Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `bonafide`
   - Import the SQL file (if provided) or run `setup_database.php`

4. **Configure Database**
   - Update database credentials in `connection.php`:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "bonafide";
   ```

5. **Start Apache & MySQL**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

6. **Access the Application**
   - Open browser and navigate to: `http://localhost/Bonafide`

## 👤 Default Login

### Admin Login
- Navigate to: `http://localhost/Bonafide/login.php`
- Create admin account or use default credentials (if set)

### Student Login
- Register as new student
- Login with registered credentials

## 📁 Project Structure

```
Bonafide/
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
├── config/                   # Configuration files
└── uploads/                  # Uploaded files
```

## 🔐 Security Features

- Session management
- Password hashing
- SQL injection prevention (prepared statements)
- XSS protection with `htmlspecialchars()`
- Admin authentication
- Input validation

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

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 👨‍💻 Author

Your Name

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

## 📞 Support

For support, email your.email@example.com

---

**Note**: This is an educational project. For production use, additional security measures and testing are recommended.
