<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}

// Handle logout from header
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    session_destroy();
    header("Location: sign-in.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";  // Replace with your DB username
$password = "";      // Replace with your DB password
$dbname = "users";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['logout'])) {
    $fullname = trim($_POST['fullname']);
    $contact = trim($_POST['contact']);
    $requesttype = $_POST['requesttype'];
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];

    // Validation
    if (empty($fullname) || empty($contact) || empty($requesttype) || empty($description)) {
        $error = "All fields are required.";
    } elseif (!preg_match('/^[0-9]{11}$/', $contact)) {
        $error = "Contact number must be 11 digits.";
    } else {
        // Generate unique ticket ID
        $year = date('Y');
        $stmt = $conn->prepare("SELECT COUNT(*) FROM requests WHERE YEAR(submitted_at) = ?");
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        $ticket_id = 'BHR-' . $year . '-' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);

        // Insert request
        $stmt = $conn->prepare("INSERT INTO requests (ticket_id, user_id, fullname, contact, requesttype, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissss", $ticket_id, $user_id, $fullname, $contact, $requesttype, $description);
        if ($stmt->execute()) {
            $success = "Request submitted successfully! Your Ticket ID is: <strong>$ticket_id</strong>. Use it to track your request.";
        } else {
            $error = "Error submitting request. Please try again.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Submit Request — eBCsH</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { 
      margin: 0; 
      padding: 0; 
      box-sizing: border-box; 
      font-family: 'Poppins', sans-serif; 
    }
    
    body { 
      min-height: 100vh;
      background: linear-gradient(135deg, #c8e6d7 0%, #d4ede4 50%, #b8dcc9 100%);
      color: #333; 
    }

    /* Header */
    header { 
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      display: flex; 
      justify-content: space-between; 
      align-items: center; 
      padding: 1.25rem 2rem; 
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .header-left { 
      display: flex; 
      align-items: center;
      gap: 12px;
    }
    
    .logo-wrapper {
      position: relative;
    }
    
    .logo-wrapper::before {
      content: '';
      position: absolute;
      inset: 0;
      background: rgba(44, 95, 63, 0.2);
      border-radius: 50%;
      filter: blur(12px);
    }
    
    .header-left img { 
      height: 48px;
      width: 48px;
      position: relative;
      z-index: 10;
      border-radius: 50%;
    }
    
    .header-title-wrap .title { 
      font-size: 16px; 
      font-weight: 600;
      color: #2c5f3f;
    }
    
    .header-title-wrap .subtitle {
      font-size: 14px;
      color: rgba(44, 95, 63, 0.7);
      font-weight: 400;
    }
    
    .logout-btn { 
      background: linear-gradient(135deg, #ff7875 0%, #ff5c59 100%);
      color: #fff; 
      border: none; 
      padding: 0.5rem 1.5rem; 
      border-radius: 8px; 
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(255, 92, 89, 0.3);
      transition: all 0.3s ease;
    }
    
    .logout-btn:hover {
      background: linear-gradient(135deg, #ff6b68 0%, #ff4f4c 100%);
      box-shadow: 0 6px 16px rgba(255, 92, 89, 0.4);
      transform: translateY(-2px);
    }
    
    .logout-btn i { 
      margin-right: 6px; 
    }

    /* Main Content */
    .page-wrap { 
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      gap: 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    /* How it Works Section */
    .how-it-works {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 24px;
      padding: 2.5rem;
      border: 2px solid #2c5f3f;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      max-width: 360px;
      transition: all 0.3s ease;
    }

    .how-it-works:hover {
      box-shadow: 0 12px 48px rgba(0, 0, 0, 0.15);
    }

    .how-it-works h2 {
      text-align: center;
      margin-bottom: 2.5rem;
      color: #2c5f3f;
      font-size: 24px;
      font-weight: 600;
    }

    .steps {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      position: relative;
      padding-bottom: 1.5rem;
    }

    .step-icon-wrapper {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: linear-gradient(135deg, #ff9966 0%, #ff7744 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
      box-shadow: 0 4px 16px rgba(255, 119, 68, 0.3);
      position: relative;
      z-index: 10;
    }

    .step-icon-wrapper i {
      color: white;
      font-size: 28px;
    }

    .step-number {
      position: absolute;
      top: -4px;
      right: -4px;
      width: 24px;
      height: 24px;
      background: #2c5f3f;
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 600;
    }

    .step h3 {
      margin-bottom: 0.75rem;
      color: #2c5f3f;
      font-size: 18px;
      font-weight: 600;
    }

    .step p {
      font-size: 14px;
      color: #666;
      line-height: 1.6;
    }

    .step-arrow {
      position: absolute;
      top: 80px;
      left: 50%;
      transform: translateX(-50%);
      color: rgba(44, 95, 63, 0.3);
      font-size: 24px;
      z-index: 0;
    }

    /* Form Container */
    .container { 
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      padding: 3rem; 
      border-radius: 24px; 
      border: 2px solid #2c5f3f;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 700px;
      transition: all 0.3s ease;
    }

    .container:hover {
      box-shadow: 0 12px 48px rgba(0, 0, 0, 0.15);
    }
    
    .form-title { 
      font-size: 32px; 
      font-weight: 700; 
      color: #2c5f3f; 
      margin-bottom: 0.5rem; 
    }
    
    .form-description { 
      color: #666; 
      margin-bottom: 2rem;
      line-height: 1.6;
    }
    
    form { 
      display: flex; 
      flex-direction: column; 
      gap: 1.5rem; 
    }
    
    .row { 
      display: flex; 
      gap: 1.5rem; 
    }
    
    .form-group { 
      flex: 1; 
      display: flex; 
      flex-direction: column; 
    }
    
    .form-label { 
      font-weight: 600; 
      color: #2c5f3f; 
      margin-bottom: 0.5rem;
      font-size: 14px;
    }
    
    input, select, textarea { 
      padding: 12px; 
      border-radius: 8px; 
      border: 1px solid #d1d5db; 
      background: #fff; 
      outline: none;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    input:focus, select:focus, textarea:focus {
      border-color: #2c8b5f;
      box-shadow: 0 0 0 3px rgba(44, 139, 95, 0.1);
    }
    
    textarea { 
      min-height: 120px;
      resize: none;
    }

    input::placeholder, textarea::placeholder {
      color: #9ca3af;
    }

    select {
      cursor: pointer;
    }
    
    .submit-button { 
      background: linear-gradient(135deg, #2c8b5f 0%, #238653 100%);
      color: #fff; 
      padding: 1rem; 
      border-radius: 10px; 
      border: none; 
      font-weight: 700;
      font-size: 15px;
      cursor: pointer;
      box-shadow: 0 4px 16px rgba(44, 139, 95, 0.3);
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .submit-button:hover {
      background: linear-gradient(135deg, #247a52 0%, #1e7347 100%);
      box-shadow: 0 6px 20px rgba(44, 139, 95, 0.4);
      transform: translateY(-2px);
    }
    
    .message { 
      padding: 1rem; 
      border-radius: 8px;
      margin-bottom: 1.5rem;
      font-size: 14px;
    }
    
    .success { 
      background: #e6f8ec; 
      color: #1b6b2b;
      border-left: 4px solid #07A840;
    }
    
    .error { 
      background: #fff0f0; 
      color: #942020;
      border-left: 4px solid #ff5c59;
    }

    @media (max-width: 1024px) {
      .page-wrap {
        flex-direction: column;
      }

      .how-it-works {
        max-width: 700px;
        width: 100%;
      }

      .steps {
        flex-direction: row;
        justify-content: space-around;
      }

      .step {
        flex: 1;
        padding-bottom: 0;
      }

      .step-arrow {
        display: none;
      }
    }
    
    @media (max-width: 768px) { 
      .row { 
        flex-direction: column;
      }

      header {
        padding: 1rem;
      }

      .page-wrap { 
        padding: 1rem;
      }

      .container {
        padding: 2rem;
      }

      .form-title {
        font-size: 24px;
      }

      .steps {
        flex-direction: column;
      }

      .step-arrow {
        display: block;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <div class="logo-wrapper">
        <img class="logo-img" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="Logo">
      </div>
      <div class="header-title-wrap">
        <div class="title">Barangay 170</div>
        <div class="subtitle">Community Portal</div>
      </div>
    </div>
    <form method="POST" action="submitreq.php" style="display:inline">
      <button type="submit" name="logout" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
      </button>
    </form>
  </header>

  <div class="page-wrap">
    <!-- How it Works Section -->
    <div class="how-it-works">
      <h2>How it Works</h2>
      <div class="steps">
        <div class="step">
          <div class="step-icon-wrapper">
            <i class="fas fa-upload"></i>
            <div class="step-number">1</div>
          </div>
          <h3>Submit</h3>
          <p>Fill out the online request form with your details and submit it to the barangay office.</p>
          <i class="fas fa-arrow-down step-arrow"></i>
        </div>

        <div class="step">
          <div class="step-icon-wrapper">
            <i class="fas fa-map-marker-alt"></i>
            <div class="step-number">2</div>
          </div>
          <h3>Track</h3>
          <p>Monitor your request status in real-time and receive notification for any updates.</p>
          <i class="fas fa-arrow-down step-arrow"></i>
        </div>

        <div class="step">
          <div class="step-icon-wrapper">
            <i class="fas fa-check-circle"></i>
            <div class="step-number">3</div>
          </div>
          <h3>Receive</h3>
          <p>Get notified whenever your request is approved and ready for pickup or delivery.</p>
        </div>
      </div>
    </div>

    <!-- Form Section -->
    <div class="container">
      <h1 class="form-title">Submit New Request</h1>
      <p class="form-description">Please provide comprehensive detail on your request or concern. The barangay will process it and notify you when ready.</p>

      <?php if ($success): ?>
        <div class="message success"><?php echo $success; ?></div>
      <?php elseif ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="submitreq.php">
        <div class="row">
          <div class="form-group">
            <label class="form-label" for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required />
          </div>
          <div class="form-group">
            <label class="form-label" for="contact">Contact Number</label>
            <input type="tel" id="contact" name="contact" placeholder="09123456789" oninput="this.value=this.value.replace(/[^0-9]/g,'');" maxlength="11" required />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="requesttype">Request Type</label>
          <select id="requesttype" name="requesttype" required>
            <option value="" disabled selected>Select the type of request</option>
            <option value="ID">Barangay ID</option>
            <option value="Clearance">Barangay Business Clearance</option>
            <option value="indigency">Certificate of Indigency</option>
            <option value="Residency">Certificate of Residency</option>
            <option value="No Objection">Clearance of No Objection</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="description">Detailed Description</label>
          <textarea id="description" name="description" placeholder="Provide details, explanation, timeline, or assistance needed." required></textarea>
        </div>

        <button type="submit" class="submit-button">Submit Request</button>
      </form>
    </div>
  </div>
</body>
</html>