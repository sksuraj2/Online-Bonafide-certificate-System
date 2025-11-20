<?php
session_start();
include 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch user details
$userQuery = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
$stmt->close();

// Get filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build query based on filter
$appQuery = "SELECT * FROM form WHERE email = ?";
if ($filter == 'pending') {
    $appQuery .= " AND (status = 'pending' OR status IS NULL)";
} elseif ($filter == 'approved') {
    $appQuery .= " AND status = 'approved'";
} elseif ($filter == 'rejected') {
    $appQuery .= " AND status = 'rejected'";
}
$appQuery .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($appQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$appResult = $stmt->get_result();
$stmt->close();

// Get statistics
$countQuery = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status='pending' OR status IS NULL THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) as rejected
    FROM form 
    WHERE email = ?";
    
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$counts = $stmt->get_result()->fetch_assoc();
$stmt->close();

$totalApplied = $counts['total'];
$approved = $counts['approved'];
$pending = $counts['pending'];
$rejected = $counts['rejected'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status - Bonafide Certificate System</title>
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

        .container {
            max-width: 1200px;
            margin: 80px auto 40px;
        }

        .header-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .header-card h2 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stats-row {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            flex: 1;
            min-width: 200px;
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.2);
        }

        .stat-card.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card p {
            font-size: 1rem;
            font-weight: 500;
            margin: 0;
        }

        .filter-buttons {
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 25px;
            border-radius: 10px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .filter-btn:hover, .filter-btn.active {
            background: #667eea;
            color: white;
        }

        .applications-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .application-item {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .application-item:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
            transform: translateX(5px);
        }

        .app-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .app-id {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .app-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }

        .detail-value {
            font-size: 1rem;
            color: #2d3436;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 5rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        @media (max-width: 768px) {
            .container {
                margin-top: 60px;
            }

            .stat-card {
                min-width: 150px;
            }

            .stat-card h3 {
                font-size: 2rem;
            }

            .app-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <a href="user.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="container">
        <div class="header-card">
            <h2><i class="fas fa-clipboard-list me-2"></i> Application Status</h2>
            <p class="text-muted mb-0">Track all your bonafide certificate applications</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-row">
            <a href="application_status.php?filter=all" class="stat-card <?php echo $filter == 'all' ? 'active' : ''; ?>">
                <h3><?php echo $totalApplied; ?></h3>
                <p>Total Applied</p>
            </a>
            <a href="application_status.php?filter=pending" class="stat-card <?php echo $filter == 'pending' ? 'active' : ''; ?>">
                <h3><?php echo $pending; ?></h3>
                <p>Pending</p>
            </a>
            <a href="application_status.php?filter=approved" class="stat-card <?php echo $filter == 'approved' ? 'active' : ''; ?>">
                <h3><?php echo $approved; ?></h3>
                <p>Approved</p>
            </a>
            <a href="application_status.php?filter=rejected" class="stat-card <?php echo $filter == 'rejected' ? 'active' : ''; ?>">
                <h3><?php echo $rejected; ?></h3>
                <p>Rejected</p>
            </a>
        </div>

        <!-- Applications List -->
        <div class="applications-card">
            <h4 class="mb-4">
                <?php 
                if($filter == 'pending') echo 'Pending Applications';
                elseif($filter == 'approved') echo 'Approved Applications';
                elseif($filter == 'rejected') echo 'Rejected Applications';
                else echo 'All Applications';
                ?>
            </h4>

            <?php
            if ($appResult->num_rows > 0) {
                while ($app = $appResult->fetch_assoc()) {
                    $status = isset($app['status']) ? $app['status'] : 'pending';
                    $status_class = 'warning';
                    $status_text = 'Pending';
                    
                    if($status == 'approved') {
                        $status_class = 'success';
                        $status_text = 'Approved';
                    } elseif($status == 'rejected') {
                        $status_class = 'danger';
                        $status_text = 'Rejected';
                    }
                    
                    $app_id = 'BON' . date('Y', strtotime($app['created_at'])) . str_pad($app['id'], 5, '0', STR_PAD_LEFT);
            ?>
                <div class="application-item">
                    <div class="app-header">
                        <div class="app-id">
                            <i class="fas fa-file-alt me-2"></i><?php echo $app_id; ?>
                        </div>
                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    </div>
                    <div class="app-details">
                        <div class="detail-item">
                            <span class="detail-label">Full Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Department</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['department']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Course</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['course']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Purpose</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['purpose']); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Applied On</span>
                            <span class="detail-value"><?php echo date('M d, Y', strtotime($app['created_at'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Roll Number</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['roll_no']); ?></span>
                        </div>
                    </div>
                    <div class="app-actions" style="margin-top: 15px; text-align: right;">
                        <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-info" style="padding: 8px 20px; border-radius: 8px; text-decoration: none;">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                        <?php if($status == 'approved'): ?>
                        <a href="generate_certificate.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-success" target="_blank" style="padding: 8px 20px; border-radius: 8px; text-decoration: none; margin-left: 5px;">
                            <i class="fas fa-certificate me-1"></i> View Certificate
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php
                }
            } else {
            ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>No Applications Found</h4>
                    <p>You haven't submitted any applications yet.</p>
                    <a href="form.php" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i> Apply Now
                    </a>
                </div>
            <?php
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
