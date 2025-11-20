<?php
session_start();
include 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$success_msg = '';
$error_msg = '';

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle profile update
if (isset($_POST['update_profile'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $new_email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);

    if(empty($full_name) || empty($new_email)) {
        $error_msg = "Name and email are required!";
    } elseif(!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format!";
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE email = ?");
        $update_stmt->bind_param("sssss", $full_name, $new_email, $phone, $address, $email);
        
        if($update_stmt->execute()) {
            $_SESSION['email'] = $new_email;
            $_SESSION['full_name'] = $full_name;
            $success_msg = "Profile updated successfully!";
            $email = $new_email;
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
        } else {
            $error_msg = "Failed to update profile!";
        }
        $update_stmt->close();
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = "All password fields are required!";
    } elseif($new_password !== $confirm_password) {
        $error_msg = "New passwords do not match!";
    } elseif(strlen($new_password) < 6) {
        $error_msg = "Password must be at least 6 characters!";
    } elseif($current_password !== $user['PASSWORD']) {
        $error_msg = "Current password is incorrect!";
    } else {
        $pass_stmt = $conn->prepare("UPDATE users SET PASSWORD = ? WHERE email = ?");
        $pass_stmt->bind_param("ss", $new_password, $email);
        
        if($pass_stmt->execute()) {
            $success_msg = "Password changed successfully!";
        } else {
            $error_msg = "Failed to change password!";
        }
        $pass_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Bonafide Certificate System</title>
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
            padding: 20px;
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid #667eea;
            color: #667eea;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }

        .back-button:hover {
            background: #667eea;
            color: white;
            transform: translateX(-5px);
        }

        .back-button i {
            margin-right: 8px;
        }

        .profile-container {
            max-width: 900px;
            margin: 80px auto 40px;
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

        .profile-header {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .profile-avatar i {
            font-size: 4rem;
            color: white;
        }

        .profile-header h2 {
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 5px;
        }

        .profile-header p {
            color: #636e72;
            font-size: 1rem;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            border: none;
        }

        .card-title {
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: #667eea;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: 500;
            color: #2d3436;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            outline: none;
        }

        .btn-update {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-update i {
            margin-right: 8px;
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

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .info-item i {
            color: #667eea;
            font-size: 1.3rem;
            width: 30px;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 0.85rem;
            color: #636e72;
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            color: #2d3436;
        }

        @media (max-width: 768px) {
            .profile-container {
                margin-top: 60px;
            }

            .card {
                padding: 20px;
            }

            .back-button {
                padding: 8px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <a href="user.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
            <small class="text-muted">Member since: <?php echo date('F Y', strtotime($user['created_at'] ?? 'now')); ?></small>
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

        <!-- Profile Information -->
        <div class="card">
            <h4 class="card-title">
                <i class="fas fa-user-edit"></i>
                Profile Information
            </h4>
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   placeholder="Enter your phone number">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" 
                                   placeholder="Enter your address">
                        </div>
                    </div>
                </div>
                <button type="submit" name="update_profile" class="btn-update">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="card">
            <h4 class="card-title">
                <i class="fas fa-lock"></i>
                Change Password
            </h4>
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="current_password">Current Password *</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" 
                                   placeholder="Enter current password">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="new_password">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   placeholder="Enter new password" minlength="6">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm new password" minlength="6">
                        </div>
                    </div>
                </div>
                <button type="submit" name="change_password" class="btn-update">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </form>
        </div>

        <!-- Account Information -->
        <div class="card">
            <h4 class="card-title">
                <i class="fas fa-info-circle"></i>
                Account Information
            </h4>
            <div class="info-item">
                <i class="fas fa-id-badge"></i>
                <div class="info-content">
                    <div class="info-label">User ID</div>
                    <div class="info-value">#<?php echo $user['id']; ?></div>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-calendar-alt"></i>
                <div class="info-content">
                    <div class="info-label">Account Created</div>
                    <div class="info-value"><?php echo date('F d, Y', strtotime($user['created_at'] ?? 'now')); ?></div>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <div class="info-content">
                    <div class="info-label">Email Status</div>
                    <div class="info-value">
                        <span class="badge bg-success">Verified</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');

        if(confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if(newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            });
        }
    </script>
</body>
</html>
