<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM form WHERE status = 'approved' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Certificates - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root { --primary-color: #6366f1; --secondary-color: #8b5cf6; --success-color: #10b981; --light-bg: #f8fafc; --card-bg: #ffffff; --text-primary: #1e293b; --border-color: #e2e8f0; --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1); --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1); }
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
    .table-container { background: var(--card-bg); border-radius: 16px; padding: 2rem; box-shadow: var(--shadow-md); border: 1px solid var(--border-color); }
    .table thead th { background: var(--light-bg); border: none; font-weight: 600; padding: 1rem; font-size: 0.875rem; text-transform: uppercase; }
    .table tbody td { padding: 1rem; border: none; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
    .badge { padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; font-size: 0.75rem; }
    .btn { border-radius: 8px; font-weight: 500; padding: 0.5rem 1rem; }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="sidebar-header"><h4><i class="fa-solid fa-graduation-cap"></i> Admin Panel</h4></div>
    <nav class="sidebar-nav">
      <a href="admin.php"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
      <a href="admin_students.php"><i class="fa-solid fa-user-graduate"></i><span>Students</span></a>
      <a href="admin_certificates.php" class="active"><i class="fa-solid fa-file-lines"></i><span>Certificates</span></a>
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
        <h1 class="mb-0 h4"><i class="fa-solid fa-file-lines me-2"></i> All Certificates</h1>
        <a href="admin.php" class="btn btn-outline-primary"><i class="fa-solid fa-arrow-left me-2"></i> Back</a>
      </div>
    </div>

    <div class="table-container">
      <h4 class="mb-4">Certificate Applications</h4>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Application ID</th>
              <th>Student Name</th>
              <th>Course</th>
              <th>Department</th>
              <th>Date</th>
              <th>Certificate</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $i = 1;
            if($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) { 
                $app_id = 'BON' . date('Y', strtotime($row['created_at'])) . str_pad($row['id'], 5, '0', STR_PAD_LEFT);
            ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><strong><?php echo $app_id; ?></strong></td>
              <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
              <td><?php echo htmlspecialchars($row['course']); ?></td>
              <td><?php echo htmlspecialchars($row['department']); ?></td>
              <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
              <td>
                <a href="generate_certificate.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" target="_blank" title="View Certificate">
                  <i class="fa-solid fa-certificate"></i> View Certificate
                </a>
              </td>
            </tr>
            <?php }} else { echo '<tr><td colspan="7" class="text-center">No certificates available</td></tr>'; } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function logout() { if(confirm('Are you sure you want to logout?')) window.location.href = "logout.php"; }
  </script>
</body>
</html>