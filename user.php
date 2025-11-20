<?php
session_start();
include 'connection.php';

// Initialize variables with default values
$totalApplied = 0;
$approved = 0;
$pending = 0;
$rejected = 0;
$user = null;
$appResult = null;
$isLoggedIn = isset($_SESSION['email']);

// Only fetch user data if logged in
if ($isLoggedIn) {
    try {
        $email = $_SESSION['email'];
        
        // Get user details with error checking
        $userQuery = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($userQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $userResult = $stmt->get_result();
        $user = $userResult->fetch_assoc();
        
        if ($user) {
            $user_email = $user['email'];
            
            // Get applications with error checking
            $appQuery = "SELECT * FROM form WHERE email = ? ORDER BY created_at DESC";
            $stmt = $conn->prepare($appQuery);
            $stmt->bind_param("s", $user_email);
            $stmt->execute();
            $appResult = $stmt->get_result();
            
            // Get counts using prepared statements
            $countQuery = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status='pending' OR status IS NULL THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) as rejected
                FROM form 
                WHERE email = ?";
                
            $stmt = $conn->prepare($countQuery);
            $stmt->bind_param("s", $user_email);
            $stmt->execute();
            $counts = $stmt->get_result()->fetch_assoc();
            
            if ($counts) {
                $totalApplied = $counts['total'];
                $approved = $counts['approved'];
                $pending = $counts['pending'];
                $rejected = $counts['rejected'];
            }
        }
    } catch (Exception $e) {
        // Log error for administrator
        error_log("Database error: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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
            pointer-events: auto;
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
            pointer-events: auto;
            position: relative;
            z-index: 10;
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
        .stats-card {
            background: white;
            padding: 20px;
            height: 100%;
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: scale(1.02);
        }
        .table {
            animation: fadeIn 1s ease;
        }
        .badge {
            transition: all 0.3s ease;
        }
        .badge:hover {
            transform: scale(1.1);
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
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }
        .animate-on-scroll.show {
            opacity: 1;
            transform: translateY(0);
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
            <li class="nav-item"><a href="user.php" class="nav-link active animate__animated animate__fadeInLeft" style="animation-delay: 0.1s">🏠 Dashboard</a></li>
            <li class="nav-item"><a href="form.php" class="nav-link animate__animated animate__fadeInLeft" style="animation-delay: 0.2s">📝 Apply Bonafide</a></li>
            <li class="nav-item"><a href="user_certificates.php" class="nav-link animate__animated animate__fadeInLeft" style="animation-delay: 0.3s">🎓 Certificates</a></li>
            <li class="nav-item"><a href="application_status.php" class="nav-link animate__animated animate__fadeInLeft" style="animation-delay: 0.4s">📋 Application Status</a></li>
            <li class="nav-item"><a href="profile.php" class="nav-link animate__animated animate__fadeInLeft" style="animation-delay: 0.5s">👤 Profile</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link text-danger animate__animated animate__fadeInLeft" style="animation-delay: 0.6s">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <nav class="navbar navbar-light bg-white shadow-sm mb-4">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h4">
                    <?php if ($isLoggedIn): ?>
                        Welcome, <b><?php echo htmlspecialchars($user['full_name']); ?></b> 👋
                    <?php else: ?>
                        <b>Bonafide Certificate System</b>
                    <?php endif; ?>
                </span>
                <div>
                    <?php if ($isLoggedIn): ?>
                        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-sm">Login</a>
                        <a href="register.php" class="btn btn-outline-primary btn-sm">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <div class="container">
            <?php if ($isLoggedIn): ?>
                <!-- Dashboard for Logged In Users -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center p-3 shadow-sm">
                            <h5 class="text-primary">Total Applied</h5>
                            <h2><?php echo $totalApplied; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3 shadow-sm">
                            <h5 class="text-success">Approved</h5>
                            <h2><?php echo $approved; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3 shadow-sm">
                            <h5 class="text-warning">Pending</h5>
                            <h2><?php echo $pending; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3 shadow-sm">
                            <h5 class="text-danger">Rejected</h5>
                            <h2><?php echo $rejected; ?></h2>
                        </div>
                    </div>
                </div>

                <!-- Application Table -->
                <div class="card shadow-sm" id="applications">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Recent Applications</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Application ID</th>
                                    <th>Date Applied</th>
                                    <th>Status</th>
                                    <th>Purpose</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                if ($appResult && $appResult->num_rows > 0) {
                                    while ($row = $appResult->fetch_assoc()) {
                                        $status = isset($row['status']) ? $row['status'] : 'pending';
                                        $status_display = ucfirst($status);
                                        $app_id = 'BON' . date('Y', strtotime($row['created_at'])) . str_pad($row['id'], 5, '0', STR_PAD_LEFT);
                                        echo "<tr>
                                                <td>{$i}</td>
                                                <td><strong>{$app_id}</strong></td>
                                                <td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>
                                                <td>";
                                        if ($status == 'approved') {
                                            echo "<span class='badge bg-success'>Approved</span>";
                                        } elseif ($status == 'pending' || $status == NULL) {
                                            echo "<span class='badge bg-warning text-dark'>Pending</span>";
                                        } else {
                                            echo "<span class='badge bg-danger'>Rejected</span>";
                                        }
                                        echo "</td>
                                              <td>" . htmlspecialchars($row['purpose']) . "</td>
                                              <td>";
                                        if ($status == 'approved') {
                                            echo "<a href='generate_certificate.php?id=" . $row['id'] . "' class='btn btn-sm btn-success' target='_blank'><i class='fas fa-certificate'></i> Certificate</a>";
                                        } else {
                                            echo "<span class='text-muted'>-</span>";
                                        }
                                        echo "</td>
                                              </tr>";
                                        $i++;
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center text-muted'>No applications found. <a href='form.php'>Apply Now</a></td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <!-- Welcome Section for Guest Users -->
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card shadow-sm p-5 text-center mb-4">
                            <h2 class="mb-3" style="color: #0d6efd;">
                                <i class="fas fa-certificate" style="font-size: 3rem; margin-bottom: 20px;"></i>
                            </h2>
                            <h3 class="mb-3">Welcome to Bonafide Certificate System</h3>
                            <p class="text-muted mb-4" style="font-size: 1.1rem;">
                                Get your bonafide certificate quickly and easily. Our system allows you to apply for and track your certificate applications in real-time.
                            </p>
                            
                            <div class="row g-4 mb-5">
                                <div class="col-md-4">
                                    <div class="feature-box p-3">
                                        <i class="fas fa-file-alt" style="font-size: 2.5rem; color: #0d6efd; margin-bottom: 15px;"></i>
                                        <h5>Easy Application</h5>
                                        <p class="text-muted">Fill out a simple form to apply</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-box p-3">
                                        <i class="fas fa-clock" style="font-size: 2.5rem; color: #0d6efd; margin-bottom: 15px;"></i>
                                        <h5>Quick Processing</h5>
                                        <p class="text-muted">Get your certificate faster</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="feature-box p-3">
                                        <i class="fas fa-check-circle" style="font-size: 2.5rem; color: #0d6efd; margin-bottom: 15px;"></i>
                                        <h5>Track Status</h5>
                                        <p class="text-muted">Monitor your application status</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                                <a href="login.php" class="btn btn-primary btn-lg px-4">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login
                                </a>
                                <a href="register.php" class="btn btn-outline-primary btn-lg px-4">
                                    <i class="fas fa-user-plus me-2"></i> Register Now
                                </a>
                            </div>

                            <hr class="my-5">

                            <h5 class="mb-4">Want to apply without login?</h5>
                            <a href="form.php" class="btn btn-success btn-lg px-5">
                                <i class="fas fa-pencil-alt me-2"></i> Fill Application Form
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.scrollToApplications = function(event) {
            event.preventDefault();
            event.stopPropagation();
            const applicationsSection = document.getElementById('applications');
            if(applicationsSection) {
                applicationsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                event.currentTarget.classList.add('active');
            }
            return false;
        };

        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.toggle-sidebar');
            if(toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    document.querySelector('.sidebar').classList.toggle('show');
                });
            }
        });

        function animateOnScroll() {
            const elements = document.querySelectorAll('.animate-on-scroll');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                if (elementTop < windowHeight - 50) {
                    element.classList.add('show');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', animateOnScroll);
        window.addEventListener('scroll', animateOnScroll);

        document.querySelectorAll('.card').forEach((card, index) => {
            card.classList.add('animate-on-scroll');
            card.style.transitionDelay = `${index * 0.1}s`;
        });
    </script>
</body>
</html>
