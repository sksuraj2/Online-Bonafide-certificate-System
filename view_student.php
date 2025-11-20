<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_students.php");
    exit();
}

$user_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    header("Location: admin_students.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM form WHERE email = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $student['email']);
$stmt->execute();
$applications = $stmt->get_result();

$total_apps = $applications->num_rows;
$pending_apps = $conn->query("SELECT COUNT(*) as count FROM form WHERE email='{$student['email']}' AND (status='pending' OR status IS NULL)")->fetch_assoc()['count'];
$approved_apps = $conn->query("SELECT COUNT(*) as count FROM form WHERE email='{$student['email']}' AND status='approved'")->fetch_assoc()['count'];
$rejected_apps = $conn->query("SELECT COUNT(*) as count FROM form WHERE email='{$student['email']}' AND status='rejected'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Details - Admin</title>
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
    .info-card { background: var(--card-bg); border-radius: 16px; padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border-color); margin-bottom: 1.5rem; }
    .stat-card { background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white; border-radius: 16px; padding: 1.5rem; text-align: center; box-shadow: var(--shadow-md); }
    .table thead th { background: var(--light-bg); border: none; font-weight: 600; padding: 1rem; font-size: 0.875rem; text-transform: uppercase; }
    .table tbody td { padding: 1rem; border: none; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
    .badge { padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; font-size: 0.75rem; }
    .btn { border-radius: 8px; font-weight: 500; padding: 0.5rem 1rem; }
    .info-label { font-weight: 600; color: #64748b; margin-bottom: 0.25rem; }
    .info-value { font-size: 1.1rem; color: var(--text-primary); margin-bottom: 1rem; }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="sidebar-header"><h4><i class="fa-solid fa-graduation-cap"></i> Admin Panel</h4></div>
    <nav class="sidebar-nav">
      <a href="admin.php"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
      <a href="admin_students.php" class="active"><i class="fa-solid fa-user-graduate"></i><span>Students</span></a>
      <a href="admin_certificates.php"><i class="fa-solid fa-file-lines"></i><span>Certificates</span></a>
      <a href="admin_pending.php"><i class="fa-solid fa-clock"></i><span>Pending Requests</span></a>
      <a href="admin_approved.php"><i class="fa-solid fa-check"></i><span>Approved</span></a>
      <a href="admin_settings.php"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
      <hr style="border-color: rgba(255,255,255,0.2); margin: 1rem 0;">
      <a href="javascript:void(0);" onclick="logout()"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
    </nav>
  </div>

  <div class="main-content">
    <div class="top-navbar">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0 h4"><i class="fa-solid fa-user me-2"></i> Student Details</h1>
        <a href="admin_students.php" class="btn btn-outline-primary"><i class="fa-solid fa-arrow-left me-2"></i> Back</a>
      </div>
    </div>

    <div class="info-card">
      <h5 class="mb-4"><i class="fa-solid fa-user-circle me-2"></i> Personal Information</h5>
      <div class="row">
        <div class="col-md-6">
          <div class="info-label">Full Name</div>
          <div class="info-value"><?php echo htmlspecialchars($student['full_name']); ?></div>
        </div>
        <div class="col-md-6">
          <div class="info-label">Email</div>
          <div class="info-value"><?php echo htmlspecialchars($student['email']); ?></div>
        </div>
        <div class="col-md-6">
          <div class="info-label">Phone</div>
          <div class="info-value"><?php echo isset($student['phone']) ? htmlspecialchars($student['phone']) : 'N/A'; ?></div>
        </div>
        <div class="col-md-6">
          <div class="info-label">Joined On</div>
          <div class="info-value"><?php echo date('F d, Y', strtotime($student['created_at'])); ?></div>
        </div>
        <div class="col-md-12">
          <div class="info-label">Address</div>
          <div class="info-value"><?php echo isset($student['address']) ? htmlspecialchars($student['address']) : 'N/A'; ?></div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-md-3">
        <div class="stat-card">
          <h6 class="mb-2">Total Applications</h6>
          <h3 class="mb-0"><?php echo $total_apps; ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
          <h6 class="mb-2">Pending</h6>
          <h3 class="mb-0"><?php echo $pending_apps; ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
          <h6 class="mb-2">Approved</h6>
          <h3 class="mb-0"><?php echo $approved_apps; ?></h3>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
          <h6 class="mb-2">Rejected</h6>
          <h3 class="mb-0"><?php echo $rejected_apps; ?></h3>
        </div>
      </div>
    </div>

    <div class="info-card">
      <h5 class="mb-4"><i class="fa-solid fa-file-lines me-2"></i> Application History</h5>
      <?php if ($total_apps > 0): ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>#</th><th>Application ID</th><th>Purpose</th><th>Course</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php 
            $i = 1;
            $applications->data_seek(0);
            while($app = $applications->fetch_assoc()) { 
              $app_id = 'BON' . date('Y', strtotime($app['created_at'])) . str_pad($app['id'], 5, '0', STR_PAD_LEFT);
              $status = isset($app['status']) ? $app['status'] : 'pending';
              $badge_class = $status == 'approved' ? 'success' : ($status == 'rejected' ? 'danger' : 'warning');
            ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><strong><?php echo $app_id; ?></strong></td>
              <td><?php echo htmlspecialchars($app['purpose']); ?></td>
              <td><?php echo htmlspecialchars($app['course']); ?></td>
              <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
              <td><span class="badge bg-<?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span></td>
              <td><a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-info"><i class="fa-solid fa-eye"></i></a></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="alert alert-info"><i class="fa-solid fa-info-circle me-2"></i> No applications found for this student.</div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function logout() { if(confirm('Are you sure you want to logout?')) window.location.href = "logout.php"; }
  </script>
</body>
</html>
