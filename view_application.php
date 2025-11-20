<?php
session_start();
include 'connection.php';

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin'])) {
    header("Location: login.php");
    exit();
}

// Get application ID
if(!isset($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$form_id = intval($_GET['id']);

// Fetch application details
$stmt = $conn->prepare("SELECT * FROM form WHERE id = ?");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    header("Location: admin.php");
    exit();
}

$application = $result->fetch_assoc();
$stmt->close();

$status = isset($application['status']) ? $application['status'] : 'pending';
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application - Admin Dashboard</title>
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
            max-width: 1000px;
            margin: 80px auto 40px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            border: none;
            margin-bottom: 30px;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px 30px;
            border-radius: 15px 15px 0 0;
            margin: -40px -40px 30px;
        }

        .card-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .info-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #667eea;
            width: 200px;
            flex-shrink: 0;
        }

        .info-value {
            color: #2d3436;
            flex: 1;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .document-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .document-link:hover {
            color: #764ba2;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
        }
    </style>
</head>
<body>
    <a href="admin.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-file-alt me-2"></i> Application Details #<?php echo $application['id']; ?></h2>
            </div>

            <div class="mb-4">
                <h5 class="mb-3">Current Status</h5>
                <span class="badge bg-<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
            </div>

            <h5 class="mb-3">Personal Information</h5>
            <div class="info-row">
                <div class="info-label">Full Name:</div>
                <div class="info-value"><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Gender:</div>
                <div class="info-value"><?php echo htmlspecialchars($application['gender']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date of Birth:</div>
                <div class="info-value"><?php echo htmlspecialchars($application['dob']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Mobile:</div>
                <div class="info-value"><?php echo htmlspecialchars($application['mobile']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value"><?php echo htmlspecialchars($application['email']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value"><?php echo htmlspecialchars($application['address']); ?></div>
            </div>

            <h5 class="mb-3 mt-4">Academic Information</h5>
            <div class="info-row">
                <div class="info-label">Roll Number:</div>
                <div class="info-value"><?php echo htmlspecialchars($application['roll_no']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Department:</div>
                <div class="info-value"><?php echo htmlspecialchars($application['department']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Course:</div>
                <div class="info-value"><?php echo htmlspecialchars($application['course']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Year/Semester:</div>
                <div class="info-value"><?php echo htmlspecialchars($application['year_sem']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Purpose:</div>
                <div class="info-value"><?php echo htmlspecialchars($application['purpose']); ?></div>
            </div>

            <h5 class="mb-3 mt-4">Documents</h5>
            <div class="info-row">
                <div class="info-label">ID Card:</div>
                <div class="info-value">
                    <?php if(!empty($application['id_card'])) { ?>
                        <a href="uploads/<?php echo htmlspecialchars($application['id_card']); ?>" target="_blank" class="document-link">
                            <i class="fas fa-download"></i> Download ID Card
                        </a>
                    <?php } else { ?>
                        <span class="text-muted">Not uploaded</span>
                    <?php } ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Fee Receipt:</div>
                <div class="info-value">
                    <?php if(!empty($application['fee_receipt'])) { ?>
                        <a href="uploads/<?php echo htmlspecialchars($application['fee_receipt']); ?>" target="_blank" class="document-link">
                            <i class="fas fa-download"></i> Download Fee Receipt
                        </a>
                    <?php } else { ?>
                        <span class="text-muted">Not uploaded</span>
                    <?php } ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Signature:</div>
                <div class="info-value">
                    <?php if(!empty($application['signature'])) { ?>
                        <a href="uploads/<?php echo htmlspecialchars($application['signature']); ?>" target="_blank" class="document-link">
                            <i class="fas fa-download"></i> Download Signature
                        </a>
                    <?php } else { ?>
                        <span class="text-muted">Not uploaded</span>
                    <?php } ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label">Applied On:</div>
                <div class="info-value"><?php echo date('F d, Y h:i A', strtotime($application['created_at'])); ?></div>
            </div>

            <?php if($status == 'pending') { ?>
            <div class="action-buttons">
                <button class="btn btn-success" onclick="updateStatus(<?php echo $application['id']; ?>, 'approved')">
                    <i class="fas fa-check me-2"></i> Approve Application
                </button>
                <button class="btn btn-danger" onclick="updateStatus(<?php echo $application['id']; ?>, 'rejected')">
                    <i class="fas fa-times me-2"></i> Reject Application
                </button>
            </div>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(formId, status) {
            const action = status === 'approved' ? 'approve' : 'reject';
            if (confirm(`Are you sure you want to ${action} this application?`)) {
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
                btn.disabled = true;
                
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
                        alert(`Application ${status} successfully!`);
                        window.location.href = 'admin.php';
                    } else {
                        alert('Error: ' + data.message);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    alert('An error occurred. Please try again.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            }
        }
    </script>
</body>
</html>
