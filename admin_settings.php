<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['change_admin_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $email = $_SESSION['email'];
        $stmt = $conn->prepare("SELECT PASSWORD FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['PASSWORD'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET PASSWORD = ? WHERE email = ?");
                $stmt->bind_param("ss", $hashed_password, $email);
                if ($stmt->execute()) {
                    $message = "Password changed successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error changing password.";
                    $message_type = "danger";
                }
            } else {
                $message = "New passwords do not match!";
                $message_type = "danger";
            }
        } else {
            $message = "Current password is incorrect!";
            $message_type = "danger";
        }
    }
    
    if (isset($_POST['backup_database'])) {
        $message = "Database backup functionality coming soon!";
        $message_type = "info";
    }
}

$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_applications = $conn->query("SELECT COUNT(*) as count FROM form")->fetch_assoc()['count'];
$pending_count = $conn->query("SELECT COUNT(*) as count FROM form WHERE status='pending' OR status IS NULL")->fetch_assoc()['count'];
$approved_count = $conn->query("SELECT COUNT(*) as count FROM form WHERE status='approved'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Settings - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root { --primary-color: #6366f1; --secondary-color: #8b5cf6; --light-bg: #f8fafc; --card-bg: #ffffff; --text-primary: #1e293b; --border-color: #e2e8f0; --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1); --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1); }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: "Inter", sans-serif; background-color: var(--light-bg); color: var(--text-primary); }
    .sidebar { width: 280px; background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); height: 100vh; position: fixed; top: 0; left: 0; color: white; padding: 2rem 0; z-index: 1000; box-shadow: var(--shadow-xl); }
    .sidebar-header { text-align: center; margin-bottom: 2rem; padding: 0 1.5rem; }
    .sidebar-header h4 { font-weight: 700; font-size: 1.5rem; }
    .sidebar-nav { padding: 0 1rem; }
    .sidebar-nav a { color: rgba(255, 255, 255, 0.9); text-decoration: none; display: flex; align-items: center; padding: 0.875rem 1rem; margin: 0.25rem 0; border-radius: 12px; transition: all 0.3s ease; font-weight: 500; }
    .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255, 255, 255, 0.15); color: white; transform: translateX(4px); }
    .sidebar-nav a i { width: 24px; margin-right: 0.75rem; font-size: 1.1rem; }
    .main-content { margin-left: 280px; padding: 2rem; min-height: 100vh; }
    .top-navbar { background: var(--card-bg); border-radius: 16px; padding: 1rem 1.5rem; margin-bottom: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border-color); }
    .settings-card { background: var(--card-bg); border-radius: 16px; padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border-color); margin-bottom: 1.5rem; }
    .stat-card { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white; border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: var(--shadow-md); }
    .form-label { font-weight: 600; margin-bottom: 0.5rem; }
    .btn { border-radius: 8px; font-weight: 500; padding: 0.5rem 1.5rem; }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="sidebar-header"><h4><i class="fa-solid fa-graduation-cap"></i> Admin Panel</h4></div>
    <nav class="sidebar-nav">
      <a href="admin.php"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
      <a href="admin_students.php"><i class="fa-solid fa-user-graduate"></i><span>Students</span></a>
      <a href="admin_certificates.php"><i class="fa-solid fa-file-lines"></i><span>Certificates</span></a>
      <a href="admin_pending.php"><i class="fa-solid fa-clock"></i><span>Pending Requests</span></a>
      <a href="admin_approved.php"><i class="fa-solid fa-check"></i><span>Approved</span></a>
      <a href="admin_settings.php" class="active"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
      <hr style="border-color: rgba(255,255,255,0.2); margin: 1rem 0;">
      <a href="javascript:void(0);" onclick="logout()"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
    </nav>
  </div>

  <div class="main-content">
    <div class="top-navbar">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0 h4"><i class="fa-solid fa-gear me-2"></i> System Settings</h1>
        <a href="admin.php" class="btn btn-outline-primary"><i class="fa-solid fa-arrow-left me-2"></i> Back</a>
      </div>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
      <?php echo $message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-3">
        <div class="stat-card">
          <h6 class="mb-2">Total Users</h6>
          <h3 class="mb-0"><?php echo $total_users; ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h6 class="mb-2">Total Applications</h6>
          <h3 class="mb-0"><?php echo $total_applications; ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h6 class="mb-2">Pending</h6>
          <h3 class="mb-0"><?php echo $pending_count; ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <h6 class="mb-2">Approved</h6>
          <h3 class="mb-0"><?php echo $approved_count; ?></h3>
        </div>
      </div>
    </div>

    <div class="settings-card">
      <h5 class="mb-3"><i class="fa-solid fa-lock me-2"></i> Change Admin Password</h5>
      <form method="POST">
        <div class="mb-3">
          <label for="current_password" class="form-label">Current Password</label>
          <input type="password" class="form-control" id="current_password" name="current_password" required>
        </div>
        <div class="mb-3">
          <label for="new_password" class="form-label">New Password</label>
          <input type="password" class="form-control" id="new_password" name="new_password" required>
        </div>
        <div class="mb-3">
          <label for="confirm_password" class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" name="change_admin_password" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i> Update Password</button>
      </form>
    </div>

    <div class="settings-card">
      <h5 class="mb-3"><i class="fa-solid fa-database me-2"></i> Database Management</h5>
      <p class="text-muted mb-3">Create a backup of your database to protect your data.</p>
      <form method="POST">
        <button type="submit" name="backup_database" class="btn btn-success"><i class="fa-solid fa-download me-2"></i> Backup Database</button>
      </form>
    </div>

    <div class="settings-card">
      <h5 class="mb-3"><i class="fa-solid fa-envelope me-2"></i> Email Configuration</h5>
      <p class="text-muted">Email settings for notifications (Coming soon)</p>
      <button class="btn btn-secondary" disabled><i class="fa-solid fa-cog me-2"></i> Configure Email</button>
    </div>

    <div class="settings-card">
      <h5 class="mb-3"><i class="fa-solid fa-bell me-2"></i> Notification Settings</h5>
      <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
        <label class="form-check-label" for="emailNotifications">Email Notifications</label>
      </div>
      <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" id="newApplications" checked>
        <label class="form-check-label" for="newApplications">New Application Alerts</label>
      </div>
      <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" id="statusUpdates" checked>
        <label class="form-check-label" for="statusUpdates">Status Update Notifications</label>
      </div>
      <button class="btn btn-primary mt-3"><i class="fa-solid fa-save me-2"></i> Save Preferences</button>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function logout() { if(confirm('Are you sure you want to logout?')) window.location.href = "logout.php"; }
  </script>
</body>
</html>
