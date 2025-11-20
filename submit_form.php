<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bonafide";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success'=>false, 'message'=>'DB Connection Failed']);
    exit();
}

$fname = $_POST['fname'];
$lname = $_POST['lname'];
$gender = $_POST['gender'];
$dob = $_POST['dob'];
$mobile = $_POST['mobile'];
$email = $_POST['email'];

$roll = $_POST['roll'];
$course = $_POST['course'];
$year = $_POST['year'];
$dept = $_POST['dept'];
$reason = $_POST['reason'];

$uploads_dir = 'uploads';
if(!is_dir($uploads_dir)) mkdir($uploads_dir);

function uploadFile($file, $dir) {
    $filename = time().'_'.basename($file['name']);
    $target = $dir.'/'.$filename;
    if(move_uploaded_file($file['tmp_name'], $target)) return $filename;
    return null;
}

$idCard = uploadFile($_FILES['idCard'], $uploads_dir);
$feeReceipt = isset($_FILES['feeReceipt']) ? uploadFile($_FILES['feeReceipt'], $uploads_dir) : '';
$signature = uploadFile($_FILES['signature'], $uploads_dir);

$stmt = $conn->prepare("INSERT INTO form 
(first_name, last_name, gender, dob, mobile, email, address, roll_no, department, course, year_sem, purpose, id_card, fee_receipt, signature, status) 
VALUES (?, ?, ?, ?, ?, ?, '', ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("ssssssssssssss", $fname, $lname, $gender, $dob, $mobile, $email, $roll, $dept, $course, $year, $reason, $idCard, $feeReceipt, $signature);

if($stmt->execute()) {
    echo json_encode(['success'=>true, 'application_id'=>$stmt->insert_id]);
} else {
    echo json_encode(['success'=>false, 'message'=>'Failed to insert data']);
}

$stmt->close();
$conn->close();
?>
