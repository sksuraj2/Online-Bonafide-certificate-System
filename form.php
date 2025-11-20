<?php
session_start();
include 'connection.php';

$success_msg = '';
$error_msg = '';
$isLoggedIn = isset($_SESSION['email']);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Validate all required fields
    $required_fields = ['first_name', 'last_name', 'gender', 'dob', 'mobile', 'email', 'address', 'roll_no', 'course', 'department', 'year_sem', 'purpose'];
    
    foreach($required_fields as $field) {
        if(empty($_POST[$field])) {
            $error_msg = "All fields are required!";
            break;
        }
    }
    
    if(empty($error_msg)) {
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $gender = $conn->real_escape_string($_POST['gender']);
        $dob = $conn->real_escape_string($_POST['dob']);
        $mobile = $conn->real_escape_string($_POST['mobile']);
        $email = $conn->real_escape_string($_POST['email']);
        $address = $conn->real_escape_string($_POST['address']);
        $roll_no = $conn->real_escape_string($_POST['roll_no']);
        $course = $conn->real_escape_string($_POST['course']);
        $department = $conn->real_escape_string($_POST['department']);
        $year_sem = $conn->real_escape_string($_POST['year_sem']);
        $purpose = $conn->real_escape_string($_POST['purpose']);

        // Validate phone number
        if(!preg_match('/^[0-9]{10}$/', $mobile)) {
            $error_msg = "Invalid mobile number! Please enter 10 digits.";
        } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_msg = "Invalid email address!";
        } else {
            $uploads_dir = 'uploads';
            if(!is_dir($uploads_dir)) mkdir($uploads_dir, 0777, true);

            function uploadFile($file, $dir){
                $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
                if(isset($file) && $file['error'] == 0){
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if(in_array($ext, $allowed) && $file['size'] <= 5242880) {
                        $filename = time().'_'.bin2hex(random_bytes(5)).'.'.$ext;
                        $target = $dir.'/'.$filename;
                        if(move_uploaded_file($file['tmp_name'],$target)) return $filename;
                    }
                }
                return '';
            }

            $id_card = uploadFile($_FILES['id_card'],$uploads_dir);
            $fee_receipt = isset($_FILES['fee_receipt']) && $_FILES['fee_receipt']['size'] > 0 ? uploadFile($_FILES['fee_receipt'],$uploads_dir) : '';
            $signature = uploadFile($_FILES['signature'],$uploads_dir);

            if(!$id_card || !$signature) {
                $error_msg = "Failed to upload required files. Please try again.";
            } else {
                $stmt = $conn->prepare("INSERT INTO form 
                    (first_name,last_name,gender,dob,mobile,email,address,roll_no,course,department,year_sem,purpose,id_card,fee_receipt,signature,created_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");

                $stmt->bind_param("sssssssssssssss",$first_name,$last_name,$gender,$dob,$mobile,$email,$address,$roll_no,$course,$department,$year_sem,$purpose,$id_card,$fee_receipt,$signature);

                if($stmt->execute()){
                    $success_msg = "✓ Application submitted successfully! Redirecting...";
                    header("refresh:2;url=user.php");
                } else {
                    $error_msg = "Error: ".$stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bonafide Certificate Application</title>
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
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.form-container {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 900px;
    padding: 50px;
    transition: all 0.4s ease;
    animation: slideUp 0.6s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-header {
    text-align: center;
    margin-bottom: 40px;
}

.form-header h2 {
    font-weight: 700;
    color: #2d3436;
    margin-bottom: 10px;
    font-size: 2.5rem;
}

.form-header p {
    color: #636e72;
    font-size: 1rem;
}

.progress-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 40px;
    position: relative;
}

.progress-container::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #dfe6e9;
    z-index: 0;
}

.progress-step {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}

.step-number {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #dfe6e9;
    color: #636e72;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 8px;
    transition: all 0.3s ease;
    border: 2px solid #dfe6e9;
}

.progress-step.active .step-number,
.progress-step.completed .step-number {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.step-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #636e72;
    text-align: center;
}

.progress-step.active .step-label,
.progress-step.completed .step-label {
    color: #667eea;
    font-weight: 600;
}

.form-section {
    display: none;
    animation: fadeIn 0.5s ease;
}

.form-section.active {
    display: block;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.section-title {
    color: #667eea;
    font-weight: 700;
    margin-bottom: 25px;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    font-size: 1.8rem;
}

label {
    font-weight: 500;
    color: #2d3436;
    margin-bottom: 8px;
    display: block;
}

.form-control,
.form-select {
    border-radius: 10px;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
}

.form-control::placeholder {
    color: #b2bec3;
}

.form-control.is-invalid {
    border-color: #e74c3c;
}

.file-input-wrapper {
    position: relative;
    margin-bottom: 20px;
}

.file-input-wrapper input[type="file"] {
    display: none;
}

.file-input-label {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
    border: 2px dashed #667eea;
    border-radius: 10px;
    background: #f8f9ff;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.file-input-label:hover {
    border-color: #764ba2;
    background: #f0f2ff;
}

.file-input-label i {
    margin-right: 10px;
    color: #667eea;
    font-size: 1.5rem;
}

.file-input-label .file-text {
    color: #636e72;
    font-weight: 500;
}

.file-name {
    margin-top: 8px;
    font-size: 0.85rem;
    color: #667eea;
    font-weight: 600;
}

.btn-container {
    display: flex;
    gap: 15px;
    justify-content: space-between;
    margin-top: 30px;
}

.btn {
    border-radius: 10px;
    padding: 12px 30px;
    font-size: 1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: #dfe6e9;
    color: #636e72;
}

.btn-secondary:hover {
    background: #b2bec3;
    color: white;
    transform: translateY(-2px);
}

.btn-success {
    background: #00b894;
    color: white;
    flex: 1;
}

.btn-success:hover {
    background: #00a383;
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 184, 148, 0.3);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.success-message {
    background: #d4edda;
    border: 2px solid #00b894;
    color: #155724;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideDown 0.5s ease;
}

.success-message i {
    font-size: 1.3rem;
}

.error-message {
    background: #f8d7da;
    border: 2px solid #e74c3c;
    color: #721c24;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideDown 0.5s ease;
}

.error-message i {
    font-size: 1.3rem;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-note {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 12px 15px;
    border-radius: 5px;
    margin-top: 20px;
    color: #1565c0;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .form-container {
        padding: 30px 20px;
    }

    .form-header h2 {
        font-size: 1.8rem;
    }

    .progress-container {
        margin-bottom: 30px;
    }

    .progress-container::before {
        display: none;
    }

    .progress-step {
        flex-direction: row;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .step-number {
        min-width: 40px;
        height: 40px;
    }

    .step-label {
        font-size: 0.8rem;
        text-align: left;
    }

    .btn-container {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
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
}

.back-button:hover {
    background: #667eea;
    color: white;
    transform: translateX(-5px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
}

.back-button i {
    margin-right: 8px;
}
</style>
</head>
<body>
<a href="user.php" class="back-button">
    <i class="fas fa-arrow-left"></i> Back
</a>

<div class="form-container">
    <div class="form-header">
        <h2><i class="fas fa-file-pdf" style="color: #667eea;"></i> Bonafide Certificate</h2>
        <p>Complete the form below to apply for your bonafide certificate</p>
    </div>

    <!-- Progress Indicator -->
    <div class="progress-container">
        <div class="progress-step active" id="step1-indicator">
            <div class="step-number">1</div>
            <div class="step-label">Personal Info</div>
        </div>
        <div class="progress-step" id="step2-indicator">
            <div class="step-number">2</div>
            <div class="step-label">Academic Details</div>
        </div>
        <div class="progress-step" id="step3-indicator">
            <div class="step-number">3</div>
            <div class="step-label">Documents</div>
        </div>
    </div>

    <?php if($success_msg): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <?php echo $success_msg; ?>
        </div>
    <?php elseif($error_msg): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <form id="bonafideForm" action="" method="POST" enctype="multipart/form-data" novalidate>

        <!-- STEP 1: Personal Information -->
        <div class="form-section active" id="step1">
            <h5 class="section-title"><i class="fas fa-user"></i> Personal Information</h5>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="first_name">First Name *</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter your first name" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name">Last Name *</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter your last name" required>
                </div>
                <div class="col-md-6">
                    <label for="gender">Gender *</label>
                    <select class="form-select" id="gender" name="gender" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="dob">Date of Birth *</label>
                    <input type="date" class="form-control" id="dob" name="dob" required>
                </div>
                <div class="col-md-6">
                    <label for="mobile">Mobile Number *</label>
                    <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="10-digit mobile number" pattern="[0-9]{10}" required>
                </div>
                <div class="col-md-6">
                    <label for="email">Email Address *</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com" required>
                </div>
                <div class="col-12">
                    <label for="address">Full Address *</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Enter your complete address" required>
                </div>
            </div>

            <div class="btn-container">
                <button type="button" class="btn btn-secondary" onclick="prevStep()" style="visibility: hidden;">Previous</button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">Next <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></button>
            </div>
        </div>

        <!-- STEP 2: Academic Details -->
        <div class="form-section" id="step2">
            <h5 class="section-title"><i class="fas fa-graduation-cap"></i> Academic Details</h5>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <label for="roll_no">Roll Number *</label>
                    <input type="text" class="form-control" id="roll_no" name="roll_no" placeholder="Enter your roll number" required>
                </div>
                <div class="col-md-6">
                    <label for="course">Course *</label>
                    <select class="form-select" id="course" name="course" required>
                        <option value="">-- Select Course --</option>
                        <option value="BCA">BCA</option>
                        <option value="MCA">MCA</option>
                        <option value="B.Tech">B.Tech</option>
                        <option value="M.Tech">M.Tech</option>
                        <option value="B.Sc">B.Sc</option>
                        <option value="M.Sc">M.Sc</option>
                        <option value="BA">BA</option>
                        <option value="MA">MA</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="department">Department *</label>
                    <input type="text" class="form-control" id="department" name="department" placeholder="e.g., Computer Science, Engineering" required>
                </div>
                <div class="col-md-6">
                    <label for="year_sem">Year/Semester *</label>
                    <input type="text" class="form-control" id="year_sem" name="year_sem" placeholder="e.g., 1st Year, 3rd Semester" required>
                </div>
                <div class="col-12">
                    <label for="purpose">Purpose of Certificate *</label>
                    <textarea class="form-control" id="purpose" name="purpose" rows="4" placeholder="Explain why you need this bonafide certificate..." required></textarea>
                </div>
            </div>

            <div class="btn-container">
                <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Previous</button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">Next <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></button>
            </div>
        </div>

        <!-- STEP 3: Document Upload -->
        <div class="form-section" id="step3">
            <h5 class="section-title"><i class="fas fa-file-upload"></i> Upload Documents</h5>
            
            <div class="row g-4">
                <div class="col-12">
                    <label>ID Card (Aadhar/PAN/Student ID) *</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="id_card" name="id_card" accept="image/*,.pdf" required>
                        <label for="id_card" class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span class="file-text">Click to upload or drag and drop<br><small>PNG, JPG or PDF (Max 5MB)</small></span>
                        </label>
                        <div class="file-name" id="id_card_name"></div>
                    </div>
                </div>

                <div class="col-12">
                    <label>Fee Receipt (Optional)</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="fee_receipt" name="fee_receipt" accept="image/*,.pdf">
                        <label for="fee_receipt" class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span class="file-text">Click to upload or drag and drop<br><small>PNG, JPG or PDF (Max 5MB)</small></span>
                        </label>
                        <div class="file-name" id="fee_receipt_name"></div>
                    </div>
                </div>

                <div class="col-12">
                    <label>Signature *</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="signature" name="signature" accept="image/*,.pdf" required>
                        <label for="signature" class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span class="file-text">Click to upload or drag and drop<br><small>PNG, JPG or PDF (Max 5MB)</small></span>
                        </label>
                        <div class="file-name" id="signature_name"></div>
                    </div>
                </div>
            </div>

            <div class="form-note">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> Please ensure all documents are clear and legible. Maximum file size is 5MB.
            </div>

            <div class="btn-container">
                <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Previous</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check" style="margin-right: 8px;"></i> Submit Application</button>
            </div>
        </div>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentStep = 1;
const totalSteps = 3;

// Update progress indicator
function updateProgress() {
    for(let i = 1; i <= totalSteps; i++) {
        const indicator = document.getElementById(`step${i}-indicator`);
        if(i < currentStep) {
            indicator.classList.add('completed');
            indicator.classList.remove('active');
        } else if(i === currentStep) {
            indicator.classList.add('active');
            indicator.classList.remove('completed');
        } else {
            indicator.classList.remove('active', 'completed');
        }
    }
}

// Show specific step
function showStep(step) {
    document.querySelectorAll('.form-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById(`step${step}`).classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Next step with validation
function nextStep() {
    const currentSection = document.querySelector('.form-section.active');
    const inputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
    
    let isValid = true;
    inputs.forEach(input => {
        if(!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    if(isValid && currentStep < totalSteps) {
        currentStep++;
        showStep(currentStep);
        updateProgress();
    }
}

// Previous step
function prevStep() {
    if(currentStep > 1) {
        currentStep--;
        showStep(currentStep);
        updateProgress();
    }
}

// File input handling
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        const fileNameDiv = document.getElementById(this.id + '_name');
        if(this.files.length > 0) {
            const fileName = this.files[0].name;
            const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);
            fileNameDiv.textContent = `✓ ${fileName} (${fileSize}MB)`;
        }
    });

    // Drag and drop
    const label = input.nextElementSibling;
    if(label && label.classList.contains('file-input-label')) {
        label.addEventListener('dragover', (e) => {
            e.preventDefault();
            label.style.borderColor = '#667eea';
            label.style.background = '#f0f2ff';
        });

        label.addEventListener('dragleave', () => {
            label.style.borderColor = '#667eea';
            label.style.background = '#f8f9ff';
        });

        label.addEventListener('drop', (e) => {
            e.preventDefault();
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            label.style.borderColor = '#667eea';
            label.style.background = '#f8f9ff';
        });
    }
});

// Form validation on input
document.querySelectorAll('input, select, textarea').forEach(input => {
    input.addEventListener('blur', function() {
        if(this.hasAttribute('required') && !this.value.trim()) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
});

// Initialize
updateProgress();
</script>
</body>
</html>
