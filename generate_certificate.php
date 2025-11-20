<?php
session_start();
include 'connection.php';

if (!isset($_GET['id'])) {
    header("Location: user.php");
    exit();
}

$form_id = $_GET['id'];

// Get application details
$stmt = $conn->prepare("SELECT * FROM form WHERE id = ? AND status = 'approved'");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();

if (!$application) {
    die("Certificate not found or not approved yet.");
}

$app_id = 'BON' . date('Y', strtotime($application['created_at'])) . str_pad($application['id'], 5, '0', STR_PAD_LEFT);
$issue_date = date('F d, Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bonafide Certificate - <?php echo $app_id; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap');
    
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body { 
      font-family: 'Crimson Text', serif; 
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      padding: 2rem;
    }
    
    .certificate-wrapper {
      max-width: 900px;
      margin: 0 auto;
      background: white;
      padding: 3rem;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      border-radius: 8px;
      position: relative;
    }
    
    .certificate-border {
      border: 8px solid #6366f1;
      padding: 2rem;
      position: relative;
      background: linear-gradient(to bottom, #ffffff 0%, #f8f9ff 100%);
    }
    
    .certificate-border::before {
      content: '';
      position: absolute;
      top: 10px;
      left: 10px;
      right: 10px;
      bottom: 10px;
      border: 2px solid #8b5cf6;
    }
    
    .header {
      text-align: center;
      margin-bottom: 2rem;
      position: relative;
      z-index: 1;
    }
    
    .header .logo {
      width: 100px;
      height: 100px;
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      border-radius: 50%;
      margin: 0 auto 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 3rem;
      border: 4px solid #e5e7eb;
    }
    
    .institution-name {
      font-family: 'Cinzel', serif;
      font-size: 2rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.5rem;
      letter-spacing: 2px;
    }
    
    .institution-address {
      font-size: 1rem;
      color: #64748b;
      margin-bottom: 0.25rem;
    }
    
    .certificate-title {
      font-family: 'Cinzel', serif;
      font-size: 2.5rem;
      font-weight: 700;
      color: #6366f1;
      text-align: center;
      margin: 2rem 0 1.5rem;
      letter-spacing: 3px;
      text-transform: uppercase;
    }
    
    .certificate-id {
      text-align: center;
      font-size: 0.9rem;
      color: #64748b;
      margin-bottom: 2rem;
      font-weight: 600;
    }
    
    .content {
      font-size: 1.2rem;
      line-height: 2;
      color: #334155;
      text-align: justify;
      margin: 2rem 0;
      padding: 0 2rem;
    }
    
    .student-name {
      font-weight: 700;
      font-size: 1.4rem;
      color: #6366f1;
      text-transform: uppercase;
      border-bottom: 2px solid #6366f1;
      display: inline-block;
      padding: 0 1rem 0.25rem;
      margin: 0 0.5rem;
    }
    
    .highlight {
      font-weight: 600;
      color: #1e293b;
    }
    
    .signature-section {
      display: flex;
      justify-content: space-between;
      margin-top: 4rem;
      padding: 0 2rem;
    }
    
    .signature-box {
      text-align: center;
      width: 200px;
    }
    
    .signature-line {
      border-top: 2px solid #1e293b;
      margin-bottom: 0.5rem;
      padding-top: 0.5rem;
    }
    
    .signature-title {
      font-weight: 600;
      font-size: 0.95rem;
      color: #1e293b;
    }
    
    .signature-designation {
      font-size: 0.85rem;
      color: #64748b;
      font-style: italic;
    }
    
    .stamp-placeholder {
      position: absolute;
      bottom: 3rem;
      left: 3rem;
      width: 120px;
      height: 120px;
      border: 3px dashed #cbd5e1;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #cbd5e1;
      font-size: 0.75rem;
      text-align: center;
      opacity: 0.5;
    }
    
    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-45deg);
      font-size: 8rem;
      color: rgba(99, 102, 241, 0.03);
      font-weight: 900;
      pointer-events: none;
      z-index: 0;
      font-family: 'Cinzel', serif;
    }
    
    .action-buttons {
      text-align: center;
      margin-top: 2rem;
    }
    
    .btn {
      border-radius: 8px;
      font-weight: 600;
      padding: 0.75rem 2rem;
      margin: 0 0.5rem;
      font-size: 1rem;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      border: none;
    }
    
    .btn-secondary {
      background: #64748b;
      border: none;
    }
    
    @media print {
      body {
        background: white;
        padding: 0;
      }
      
      .certificate-wrapper {
        box-shadow: none;
        max-width: 100%;
      }
      
      .action-buttons {
        display: none;
      }
      
      .stamp-placeholder {
        display: none;
      }
    }
  </style>
</head>
<body>
  <div class="certificate-wrapper">
    <div class="certificate-border">
      <div class="watermark">BONAFIDE</div>
      
      <div class="stamp-placeholder">
        <span>Official<br>Stamp</span>
      </div>
      
      <div class="header">
        <div class="logo">
          <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="institution-name">ABC College of Arts & Science</div>
        <div class="institution-address">123 College Road, Education District</div>
        <div class="institution-address">City - 123456 | Phone: +91-1234567890</div>
        <div class="institution-address">Email: info@abccollege.edu.in | Website: www.abccollege.edu.in</div>
      </div>
      
      <div class="certificate-title">Bonafide Certificate</div>
      <div class="certificate-id">Certificate No: <?php echo $app_id; ?></div>
      
      <div class="content">
        <p style="text-align: center; margin-bottom: 2rem;">
          <strong>TO WHOMSOEVER IT MAY CONCERN</strong>
        </p>
        
        <p>
          This is to certify that 
          <span class="student-name"><?php echo strtoupper(htmlspecialchars($application['first_name'] . ' ' . $application['last_name'])); ?></span>
          bearing Roll Number <span class="highlight"><?php echo htmlspecialchars($application['roll_no']); ?></span>
          is a bonafide student of this institution.
        </p>
        
        <p>
          He/She is currently pursuing 
          <span class="highlight"><?php echo htmlspecialchars($application['course']); ?></span>
          in the Department of <span class="highlight"><?php echo htmlspecialchars($application['department']); ?></span>,
          <?php echo htmlspecialchars($application['year_sem']); ?>.
        </p>
        
        <p>
          This certificate is issued for the purpose of 
          <span class="highlight"><?php echo htmlspecialchars($application['purpose']); ?></span>
          as per the student's request.
        </p>
        
        <p>
          Date of Birth: <span class="highlight"><?php echo date('F d, Y', strtotime($application['dob'])); ?></span><br>
          Gender: <span class="highlight"><?php echo ucfirst(htmlspecialchars($application['gender'])); ?></span><br>
          Mobile: <span class="highlight"><?php echo htmlspecialchars($application['mobile']); ?></span><br>
          Email: <span class="highlight"><?php echo htmlspecialchars($application['email']); ?></span>
        </p>
        
        <p style="margin-top: 2rem;">
          We wish him/her all success in his/her future endeavors.
        </p>
      </div>
      
      <div class="signature-section">
        <div class="signature-box">
          <div class="signature-line">
            <div class="signature-title">Class Teacher</div>
          </div>
          <div class="signature-designation">Department of <?php echo htmlspecialchars($application['department']); ?></div>
        </div>
        
        <div class="signature-box">
          <div class="signature-line">
            <div class="signature-title">Principal</div>
          </div>
          <div class="signature-designation">ABC College</div>
        </div>
      </div>
      
      <div style="text-align: center; margin-top: 3rem; font-size: 0.9rem; color: #64748b;">
        <strong>Date of Issue:</strong> <?php echo $issue_date; ?>
      </div>
    </div>
  </div>
  
  <div class="action-buttons">
    <button onclick="window.print()" class="btn btn-primary">
      <i class="fa-solid fa-print me-2"></i> Print Certificate
    </button>
    <button onclick="downloadPDF()" class="btn btn-primary">
      <i class="fa-solid fa-download me-2"></i> Download PDF
    </button>
    <button onclick="window.history.back()" class="btn btn-secondary">
      <i class="fa-solid fa-arrow-left me-2"></i> Go Back
    </button>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script>
    function downloadPDF() {
      const element = document.querySelector('.certificate-wrapper');
      const opt = {
        margin: 0,
        filename: 'Bonafide_Certificate_<?php echo $app_id; ?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };
      
      // Hide buttons before generating PDF
      document.querySelector('.action-buttons').style.display = 'none';
      document.querySelector('.stamp-placeholder').style.display = 'none';
      
      html2pdf().set(opt).from(element).save().then(() => {
        document.querySelector('.action-buttons').style.display = 'block';
        document.querySelector('.stamp-placeholder').style.display = 'flex';
      });
    }
  </script>
</body>
</html>
