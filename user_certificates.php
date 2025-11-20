<?php
session_start();
include 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Get user details
$userQuery = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

// Get approved applications
$appQuery = "SELECT * FROM form WHERE email = ? AND status = 'approved' ORDER BY created_at DESC";
$stmt = $conn->prepare($appQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$appResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100%;
            background: linear-gradient(145deg, #0d6efd, #0099ff);
            padding-top: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .sidebar h3 {
            color: white;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            margin: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1) !important;
        }
        .navbar {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9) !important;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            .toggle-sidebar {
                display: block !important;
            }
        }
        .toggle-sidebar {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
        }
        .certificate-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .certificate-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>

    <button class="btn btn-primary toggle-sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar">
        <h3 class="animate__animated animate__fadeInDown">Certificate</h3>
        <ul class="nav flex-column mt-3">
            <li class="nav-item"><a href="user.php" class="nav-link animate__animated animate__fadeInLeft" style="animation-delay: 0.1s">🏠 Dashboard</a></li>
            <li class="nav-item"><a href="form.php" class="nav-link animate__animated animate__fadeInLeft" style="animation-delay: 0.2s">📝 Apply Bonafide</a></li>
            <li class="nav-item"><a href="user_certificates.php" class="nav-link active animate__animated animate__fadeInLeft" style="animation-delay: 0.3s">🎓 Certificates</a></li>
            <li class="nav-item"><a href="application_status.php" class="nav-link animate__animated animate__fadeInLeft" style="animation-delay: 0.4s">📋 Application Status</a></li>
            <li class="nav-item"><a href="profile.php" class="nav-link animate__animated animate__fadeInLeft" style="animation-delay: 0.5s">👤 Profile</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link text-danger animate__animated animate__fadeInLeft" style="animation-delay: 0.6s">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <nav class="navbar navbar-light bg-white shadow-sm mb-4">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h4">
                    Welcome, <b><?php echo htmlspecialchars($user['full_name']); ?></b> 👋
                </span>
                <div>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-certificate me-2"></i> My Certificates</h5>
                </div>
                <div class="card-body">
                    <?php if ($appResult->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Application ID</th>
                                        <th>Name</th>
                                        <th>Course</th>
                                        <th>Department</th>
                                        <th>Purpose</th>
                                        <th>Date Issued</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    while ($row = $appResult->fetch_assoc()) { 
                                        $app_id = 'BON' . date('Y', strtotime($row['created_at'])) . str_pad($row['id'], 5, '0', STR_PAD_LEFT);
                                    ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><strong><?php echo $app_id; ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                                        <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <a href="generate_certificate.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-success" 
                                               target="_blank" 
                                               title="View Certificate">
                                                <i class="fas fa-certificate"></i> View Certificate
                                            </a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-certificate" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
                            <h4>No Certificates Yet</h4>
                            <p class="text-muted">You don't have any approved certificates yet.</p>
                            <a href="form.php" class="btn btn-primary mt-3">
                                <i class="fas fa-plus-circle me-2"></i> Apply for Certificate
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.toggle-sidebar');
            if(toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    document.querySelector('.sidebar').classList.toggle('show');
                });
            }
        });
    </script>
</body>
</html>
