<?php
session_start();
include 'connection.php';

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if($action === 'update_status' && isset($_POST['form_id']) && isset($_POST['status'])) {
        $form_id = intval($_POST['form_id']);
        $status = $_POST['status'];
        
        // Validate status
        if(!in_array($status, ['pending', 'approved', 'rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit();
        }
        
        // Update status in database
        $stmt = $conn->prepare("UPDATE form SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $form_id);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action or missing parameters']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
}

$conn->close();
?>
