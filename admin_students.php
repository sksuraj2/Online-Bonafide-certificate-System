<?php
session_start();
include 'connection.php';

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

// Get all users
$users_query = $conn->query("SELECT u.*, 
    COUNT(f.id) as total_applications,
    SUM(CASE WHEN f.status='approved' THEN 1 ELSE 0 END) as approved_applications
    FROM users u 
    LEFT JOIN form f ON u.email = f.email 
    GROUP BY u.id 
    ORDER BY u.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Students - Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #6366f1;
      --primary-dark: #4f46e5;
      --secondary-color: #8b5cf6;
      --success-color: #10b981;
      --light-bg: #f8fafc;
      --card-bg: #ffffff;
      --text-primary: #1e293b;
      --text-secondary: #64748b;
      --border-color: #e2e8f0;
      --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
      --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
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
    }

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
      box-shadow: var(--shadow-xl);
    }

    .sidebar-header {
      text-align: center;
      margin-bottom: 2rem;
      padding: 0 1.5rem;
    }

    .sidebar-header h4 {
      font-weight: 700;
      font-size: 1.5rem;
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
    }

    .main-content {
      margin-left: 280px;
      padding: 2rem;
      min-height: 100vh;
    }

    .top-navbar {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 1rem 1.5rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow-md);
      border: 1px solid var(--border-color);
    }

    .table-container {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 2rem;
      box-shadow: var(--shadow-md);
      border: 1px solid var(--border-color);
    }

    .table thead th {
      background: var(--light-bg);
      border: none;
      font-weight: 600;
      color: var(--text-primary);
      padding: 1rem;
      font-size: 0.875rem;
      text-transform: uppercase;
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
    }

    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .main-content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <div class="sidebar-header">
      <h4><i class="fa-solid fa-graduation-cap"></i> Admin Panel</h4>
    </div>
    <nav class="sidebar-nav">
      <a href="admin.php">
        <i class="fa-solid fa-chart-line"></i>
        <span>Dashboard</span>
      </a>
      <a href="admin_students.php" class="active">
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

  <div class="main-content">
    <div class="top-navbar">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0 h4"><i class="fa-solid fa-user-graduate me-2"></i> Students Management</h1>
        <a href="admin.php" class="btn btn-outline-primary">
          <i class="fa-solid fa-arrow-left me-2"></i> Back to Dashboard
        </a>
      </div>
    </div>

    <div class="table-container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">All Students</h4>
        <div class="input-group" style="width: 300px;">
          <input type="text" class="form-control" id="searchInput" placeholder="Search students..." onkeyup="searchTable()">
          <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
        </div>
      </div>
      
      <div class="table-responsive">
        <table class="table align-middle" id="studentsTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Joined Date</th>
              <th>Applications</th>
              <th>Approved</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 1;
            while($user = $users_query->fetch_assoc()) {
            ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar me-2">
                    <i class="fa-solid fa-user-circle text-primary" style="font-size: 1.5rem;"></i>
                  </div>
                  <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                </div>
              </td>
              <td><?php echo htmlspecialchars($user['email']); ?></td>
              <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
              <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
              <td><span class="badge bg-primary"><?php echo $user['total_applications']; ?></span></td>
              <td><span class="badge bg-success"><?php echo $user['approved_applications']; ?></span></td>
              <td>
                <a href="view_student.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                  <i class="fa-solid fa-eye"></i> View
                </a>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function logout() {
      if (confirm('Are you sure you want to logout?')) {
        window.location.href = "logout.php";
      }
    }

    function searchTable() {
      const input = document.getElementById('searchInput');
      const filter = input.value.toUpperCase();
      const table = document.getElementById('studentsTable');
      const tr = table.getElementsByTagName('tr');

      for (let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < td.length; j++) {
          if (td[j]) {
            const txtValue = td[j].textContent || td[j].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
              found = true;
              break;
            }
          }
        }
        
        tr[i].style.display = found ? '' : 'none';
      }
    }
  </script>
</body>
</html>
