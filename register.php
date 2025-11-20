<?php
session_start();
include 'connection.php';

$error_msg = '';
$success_msg = '';

if (isset($_POST['register'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if(empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_msg = "All fields are required!";
    } elseif($password !== $confirm_password) {
        $error_msg = "Passwords do not match!";
    } elseif(strlen($password) < 6) {
        $error_msg = "Password must be at least 6 characters!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format!";
    } else {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if($check_result->num_rows > 0) {
            $error_msg = "Email already exists! Please use a different email.";
        } else {
            // Insert new user (in production, hash the password)
            // Note: column name is PASSWORD (uppercase) in database
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, PASSWORD, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $full_name, $email, $password);
            
            if ($stmt->execute()) {
                $success_msg = "Registration successful! Redirecting to login...";
                header("refresh:2;url=login.php");
            } else {
                $error_msg = "Registration failed: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bonafide Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
            padding: 50px 40px;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .register-header .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #00b894, #55efc4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(0, 184, 148, 0.3);
        }

        .register-header .icon i {
            font-size: 2.5rem;
            color: white;
        }

        .register-header h2 {
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .register-header p {
            color: #636e72;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            font-weight: 500;
            color: #2d3436;
            margin-bottom: 8px;
            display: block;
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #00b894;
            font-size: 1.1rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e9ecef;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #00b894;
            box-shadow: 0 0 0 0.2rem rgba(0, 184, 148, 0.15);
            outline: none;
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #00b894, #55efc4);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 184, 148, 0.4);
        }

        .btn-register i {
            margin-left: 8px;
        }

        .alert-custom {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.5s ease;
        }

        .alert-success {
            background: #d4edda;
            border: 2px solid #00b894;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            border: 2px solid #e74c3c;
            color: #721c24;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            color: #636e72;
            position: relative;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #636e72;
        }

        .login-link a {
            color: #00b894;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .login-link a:hover {
            color: #55efc4;
            text-decoration: underline;
        }

        .back-to-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-home a {
            color: #00b894;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .back-to-home a:hover {
            color: #55efc4;
        }

        .back-to-home i {
            margin-right: 5px;
        }

        .password-strength {
            font-size: 0.85rem;
            margin-top: 5px;
            color: #636e72;
        }

        @media (max-width: 768px) {
            .register-container {
                padding: 40px 25px;
            }

            .register-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2>Create Account</h2>
            <p>Join us today!</p>
        </div>

        <?php if($error_msg): ?>
            <div class="alert-custom alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <?php if($success_msg): ?>
            <div class="alert-custom alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <div class="input-group-custom">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           placeholder="Enter your full name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-group-custom">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="Enter your email" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group-custom">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Create a password" minlength="6" required>
                </div>
                <small class="password-strength">Must be at least 6 characters</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-group-custom">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm your password" minlength="6" required>
                </div>
            </div>

            <button type="submit" name="register" class="btn-register">
                Create Account <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="divider">
            <span>OR</span>
        </div>

        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login Here</a></p>
        </div>

        <div class="back-to-home">
            <a href="user.php">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        confirmPassword.addEventListener('input', function() {
            if(password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
