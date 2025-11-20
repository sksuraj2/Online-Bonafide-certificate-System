<?php
session_start();
include 'connection.php';

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

// Get statistics
$total_users_query = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $total_users_query->fetch_assoc()['total'];

$total_forms_query = $conn->query("SELECT COUNT(*) as total FROM form");
$total_forms = $total_forms_query->fetch_assoc()['total'];

// Check if status column exists
$check_column = $conn->query("SHOW COLUMNS FROM form LIKE 'status'");
if($check_column->num_rows == 0) {
    // Add status column if it doesn't exist
    $conn->query("ALTER TABLE form ADD COLUMN status VARCHAR(20) DEFAULT 'pending' AFTER signature");
}

$pending_forms_query = $conn->query("SELECT COUNT(*) as total FROM form WHERE status = 'pending' OR status IS NULL");
$pending_forms = $pending_forms_query->fetch_assoc()['total'];

$approved_forms_query = $conn->query("SELECT COUNT(*) as total FROM form WHERE status = 'approved'");
$approved_forms = $approved_forms_query->fetch_assoc()['total'];

// Get recent form submissions
$recent_forms = $conn->query("SELECT * FROM form ORDER BY created_at DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Bonafide Certificate System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #6366f1;
      --primary-dark: #4f46e5;
      --secondary-color: #8b5cf6;
      --success-color: #10b981;
      --warning-color: #f59e0b;
      --danger-color: #ef4444;
      --info-color: #06b6d4;
      --light-bg: #f8fafc;
      --dark-bg: #0f172a;
      --card-bg: #ffffff;
      --text-primary: #1e293b;
      --text-secondary: #64748b;
      --border-color: #e2e8f0;
      --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
      --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
      --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
      --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Inter", sans-serif;
      background-color: var(--light-bg);
      color: var(--text-primary);
      line-height: 1.6;
      overflow-x: hidden;
    }

    /* Sidebar Styles */
    .sidebar {
      width: 280px;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      color: white;
      padding: 2rem 0;
      z-index: 1000;
      transition: all 0.3s ease;
      box-shadow: var(--shadow-xl);
    }

    .sidebar.collapsed {
      width: 80px;
    }

    .sidebar-header {
      text-align: center;
      margin-bottom: 2rem;
      padding: 0 1.5rem;
    }

    .sidebar-header h4 {
      font-weight: 700;
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
      transition: all 0.3s ease;
    }

    .sidebar.collapsed .sidebar-header h4 {
      opacity: 0;
      transform: translateX(-20px);
    }

    .sidebar-nav {
      padding: 0 1rem;
    }

    .sidebar-nav a {
      color: rgba(255, 255, 255, 0.9);
      text-decoration: none;
      display: flex;
      align-items: center;
      padding: 0.875rem 1rem;
      margin: 0.25rem 0;
      border-radius: 12px;
      transition: all 0.3s ease;
      font-weight: 500;
      position: relative;
      overflow: hidden;
    }

    .sidebar-nav a::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.1);
      transition: left 0.3s ease;
    }

    .sidebar-nav a:hover::before {
      left: 0;
    }

    .sidebar-nav a:hover,
    .sidebar-nav a.active {
      background: rgba(255, 255, 255, 0.15);
      color: white;
      transform: translateX(4px);
    }

    .sidebar-nav a i {
      width: 24px;
      margin-right: 0.75rem;
      font-size: 1.1rem;
      transition: all 0.3s ease;
    }

    .sidebar.collapsed .sidebar-nav a span {
      opacity: 0;
      transform: translateX(-20px);
    }

    .sidebar.collapsed .sidebar-nav a {
      justify-content: center;
      padding: 0.875rem;
    }

    .sidebar.collapsed .sidebar-nav a i {
      margin-right: 0;
    }

    /* Main Content */
    .main-content {
      margin-left: 280px;
      padding: 2rem;
      min-height: 100vh;
      transition: all 0.3s ease;
    }

    .main-content.expanded {
      margin-left: 80px;
    }

    /* Mobile Sidebar Toggle */
    .sidebar-toggle {
      display: none;
      background: var(--primary-color);
      border: none;
      color: white;
      padding: 0.75rem;
      border-radius: 8px;
      font-size: 1.2rem;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: var(--shadow-md);
    }

    .sidebar-toggle:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }

    .top-navbar {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 1rem 1.5rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow-md);
      border: 1px solid var(--border-color);
    }

    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: var(--primary-color);
    }

    .navbar-actions {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    /* Dashboard Cards */
    .stats-card {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 2rem;
      box-shadow: var(--shadow-md);
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .stats-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }

    .stats-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-xl);
    }

    .stats-card .icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }

    .stats-card .icon.primary { background: rgba(99, 102, 241, 0.1); color: var(--primary-color); }
    .stats-card .icon.success { background: rgba(16, 185, 129, 0.1); color: var(--success-color); }
    .stats-card .icon.warning { background: rgba(245, 158, 11, 0.1); color: var(--warning-color); }
    .stats-card .icon.info { background: rgba(6, 182, 212, 0.1); color: var(--info-color); }

    .stats-card h3 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .stats-card p {
      color: var(--text-secondary);
      font-weight: 500;
      margin: 0;
    }

    /* Table Styles */
    .table-container {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 2rem;
      box-shadow: var(--shadow-md);
      border: 1px solid var(--border-color);
      margin-top: 2rem;
    }

    .table-container h4 {
      font-weight: 700;
      margin-bottom: 1.5rem;
      color: var(--text-primary);
    }

    .table {
      margin-bottom: 0;
    }

    .table thead th {
      background: var(--light-bg);
      border: none;
      font-weight: 600;
      color: var(--text-primary);
      padding: 1rem;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .table tbody td {
      padding: 1rem;
      border: none;
      border-bottom: 1px solid var(--border-color);
      vertical-align: middle;
    }

    .table tbody tr:hover {
      background: rgba(99, 102, 241, 0.02);
    }

    .badge {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.75rem;
    }

    .btn {
      border-radius: 8px;
      font-weight: 500;
      padding: 0.5rem 1rem;
      transition: all 0.3s ease;
    }

    .btn:hover {
      transform: translateY(-1px);
    }

    /* Dark Mode */
    .dark-mode {
      --light-bg: #0f172a;
      --card-bg: #1e293b;
      --text-primary: #f1f5f9;
      --text-secondary: #94a3b8;
      --border-color: #334155;
    }

    .dark-mode .sidebar {
      background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
      .sidebar {
        width: 260px;
      }
      .main-content {
        margin-left: 260px;
      }
    }

    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
        width: 280px;
      }
      
      .sidebar.show {
        transform: translateX(0);
    }

    .main-content {
        margin-left: 0;
        padding: 1rem;
      }
      
      .sidebar-toggle {
        display: block;
      }
      
      .stats-card {
        margin-bottom: 1.5rem;
      }
    }

    @media (max-width: 768px) {
      .main-content {
        padding: 1rem 0.5rem;
      }
      
      .top-navbar {
        padding: 1rem;
        margin-bottom: 1.5rem;
      }
      
      .navbar-brand {
        font-size: 1.25rem;
      }
      
      .stats-card {
        padding: 1.5rem;
      }
      
      .stats-card h3 {
        font-size: 2rem;
    }

    .table-container {
        padding: 1rem;
        margin-top: 1.5rem;
      }
      
      .table-responsive {
        border-radius: 8px;
      }
    }

    @media (max-width: 576px) {
      .stats-card .icon {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
      }
      
      .stats-card h3 {
        font-size: 1.75rem;
      }
      
      .btn {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
      }
    }

    /* Loading Animation */
    .loading {
      opacity: 0.7;
      pointer-events: none;
    }

    /* Smooth Transitions */
    * {
      transition: all 0.3s ease;
    }
  </style>
</head>

<body>

  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
    <h4><i class="fa-solid fa-graduation-cap"></i> Admin Panel</h4>
    </div>
    <nav class="sidebar-nav">
      <a href="admin.php" class="active">
        <i class="fa-solid fa-chart-line"></i>
        <span>Dashboard</span>
      </a>
      <a href="admin_students.php">
        <i class="fa-solid fa-user-graduate"></i>
        <span>Students</span>
      </a>
      <a href="admin_certificates.php">
        <i class="fa-solid fa-file-lines"></i>
        <span>Certificates</span>
      </a>
      <a href="admin_pending.php">
        <i class="fa-solid fa-clock"></i>
        <span>Pending Requests</span>
      </a>
      <a href="admin_approved.php">
        <i class="fa-solid fa-check"></i>
        <span>Approved</span>
      </a>
      <a href="admin_settings.php">
        <i class="fa-solid fa-gear"></i>
        <span>Settings</span>
      </a>
      <hr style="border-color: rgba(255,255,255,0.2); margin: 1rem 0;">
      <a href="javascript:void(0);" onclick="logout()">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
      </a>
    </nav>
  </div>


  <div class="main-content" id="mainContent">
    <div class="top-navbar">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <button class="sidebar-toggle me-3" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
          </button>
          <h1 class="navbar-brand mb-0">Bonafide Certificate Admin</h1>
        </div>
        <div class="navbar-actions">
          <button class="btn btn-outline-secondary me-3" onclick="toggleDarkMode()">
            <i class="fa-solid fa-moon"></i>
          </button>
          <div class="dropdown">
            <a class="btn btn-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="fa-solid fa-user-circle me-2"></i> Admin
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#"><i class="fa-solid fa-user me-2"></i>Profile</a></li>
              <li><a class="dropdown-item" href="#"><i class="fa-solid fa-gear me-2"></i>Settings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="#" onclick="logout()"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-xl-3 col-md-6">
        <div class="stats-card">
          <div class="icon primary">
            <i class="fa-solid fa-users"></i>
          </div>
          <h3><?php echo $total_users; ?></h3>
          <p>Total Users</p>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="stats-card">
          <div class="icon success">
            <i class="fa-solid fa-file-lines"></i>
          </div>
          <h3><?php echo $total_forms; ?></h3>
          <p>Total Applications</p>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="stats-card">
          <div class="icon warning">
            <i class="fa-solid fa-clock"></i>
          </div>
          <h3><?php echo $pending_forms; ?></h3>
          <p>Pending Requests</p>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="stats-card">
          <div class="icon info">
            <i class="fa-solid fa-check-circle"></i>
          </div>
          <h3><?php echo $approved_forms; ?></h3>
          <p>Approved Requests</p>
        </div>
      </div>
    </div>

    <div class="table-container">
      <h4>Recent Certificate Requests</h4>
        <div class="table-responsive">
          <table class="table align-middle">
          <thead>
              <tr>
                <th>ID</th>
                <th>Student Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Course</th>
                <th>Purpose</th>
                <th>Date Applied</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              if($recent_forms->num_rows > 0) {
                while($form = $recent_forms->fetch_assoc()) {
                  $status = isset($form['status']) ? $form['status'] : 'pending';
                  $status_class = 'warning';
                  $status_text = 'Pending';
                  
                  if($status == 'approved') {
                    $status_class = 'success';
                    $status_text = 'Approved';
                  } elseif($status == 'rejected') {
                    $status_class = 'danger';
                    $status_text = 'Rejected';
                  }
              ?>
              <tr>
                <td><strong>#<?php echo $form['id']; ?></strong></td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar me-2">
                      <i class="fa-solid fa-user-circle text-primary"></i>
                    </div>
                    <?php echo htmlspecialchars($form['first_name'] . ' ' . $form['last_name']); ?>
                  </div>
                </td>
                <td><?php echo htmlspecialchars($form['email']); ?></td>
                <td><?php echo htmlspecialchars($form['department']); ?></td>
                <td><?php echo htmlspecialchars($form['course']); ?></td>
                <td><?php echo htmlspecialchars($form['purpose']); ?></td>
                <td><?php echo date('Y-m-d', strtotime($form['created_at'])); ?></td>
                <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                <td>
                  <?php if($status == 'pending') { ?>
                  <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-success" onclick="updateStatus(<?php echo $form['id']; ?>, 'approved')">
                      <i class="fa-solid fa-check me-1"></i>Approve
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="updateStatus(<?php echo $form['id']; ?>, 'rejected')">
                      <i class="fa-solid fa-times me-1"></i>Reject
                    </button>
                    <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo $form['id']; ?>)">
                      <i class="fa-solid fa-eye me-1"></i>View
                    </button>
                  </div>
                  <?php } else { ?>
                  <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo $form['id']; ?>)">
                    <i class="fa-solid fa-eye me-1"></i>View Details
                  </button>
                  <?php } ?>
                </td>
              </tr>
              <?php 
                }
              } else {
                echo '<tr><td colspan="9" class="text-center">No applications found</td></tr>';
              }
              ?>
            </tbody>
          </table>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('mainContent');
      
      if (window.innerWidth <= 992) {
        sidebar.classList.toggle('show');
      } else {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
      }
    }

    document.addEventListener('click', function(event) {
      const sidebar = document.getElementById('sidebar');
      const sidebarToggle = document.querySelector('.sidebar-toggle');
      
      if (window.innerWidth <= 992 && 
          !sidebar.contains(event.target) && 
          !sidebarToggle.contains(event.target)) {
        sidebar.classList.remove('show');
      }
    });

    window.addEventListener('resize', function() {
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('mainContent');
      
      if (window.innerWidth > 992) {
        sidebar.classList.remove('show');
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('expanded');
      }
    });

    function toggleDarkMode() {
      document.body.classList.toggle('dark-mode');
      localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
      
      const darkModeBtn = document.querySelector('[onclick="toggleDarkMode()"] i');
      if (document.body.classList.contains('dark-mode')) {
        darkModeBtn.className = 'fa-solid fa-sun';
      } else {
        darkModeBtn.className = 'fa-solid fa-moon';
      }
    }

    if (localStorage.getItem('darkMode') === 'true') {
      document.body.classList.add('dark-mode');
      const darkModeBtn = document.querySelector('[onclick="toggleDarkMode()"] i');
      darkModeBtn.className = 'fa-solid fa-sun';
    }

    function updateStatus(formId, status) {
      const action = status === 'approved' ? 'approve' : 'reject';
      if (confirm(`Are you sure you want to ${action} this application?`)) {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Processing...';
        btn.disabled = true;
        
        // Send AJAX request
        fetch('admin_actions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=update_status&form_id=${formId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
          if(data.success) {
            showNotification(`Application ${status} successfully!`, 'success');
            setTimeout(() => {
              location.reload();
            }, 1500);
          } else {
            showNotification('Error: ' + data.message, 'danger');
            btn.innerHTML = originalText;
            btn.disabled = false;
          }
        })
        .catch(error => {
          showNotification('An error occurred. Please try again.', 'danger');
          btn.innerHTML = originalText;
          btn.disabled = false;
        });
      }
    }

    function viewDetails(formId) {
      window.location.href = `view_application.php?id=${formId}`;
    }

    function showNotification(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      
      document.body.appendChild(notification);
      
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 5000);
    }

    document.querySelectorAll('.sidebar-nav a').forEach(link => {
      link.addEventListener('click', function(e) {
        if(this.getAttribute('onclick') === 'logout()') {
          return;
        }
        
        document.querySelectorAll('.sidebar-nav a').forEach(l => l.classList.remove('active'));
        
        this.classList.add('active');
        
        if (window.innerWidth <= 992) {
          document.getElementById('sidebar').classList.remove('show');
        }
      });
    });

    function logout() {
      if (confirm('Are you sure you want to logout?')) {
        showNotification('Logging out...', 'info');
        setTimeout(() => {
          window.location.href = "logout.php";
        }, 1000);
      }
    }

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    document.documentElement.style.scrollBehavior = 'smooth';
  </script>
</body>

</html>