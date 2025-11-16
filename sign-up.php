<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $barangay = trim($_POST['barangay']);
    $id_type = $_POST['id_type'];
    $resident_type = isset($_POST['resident_type']) ? $_POST['resident_type'] : '';
    $file_path = "";

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($barangay) || empty($id_type)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Handle file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            // Validate file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
            if (!in_array(strtolower($file_extension), $allowed_types)) {
                $error = "Invalid file type. Only JPG, PNG, and PDF files are allowed.";
            } elseif ($_FILES['file']['size'] > 5000000) { // 5MB max
                $error = "File size too large. Maximum 5MB allowed.";
            } else {
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                    $error = "File upload failed.";
                }
            }
        } else {
            $error = "Please upload a valid ID.";
        }

        if (!$error) {
            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT id FROM account WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $error = "Email already exists. Please use a different email.";
                $check_stmt->close();
            } else {
                $check_stmt->close();
                
                // Store password as plain text (for development/testing only)
                // NOTE: In production, use password_hash() for security
                
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO account (first_name, middle_name, last_name, email, password, barangay, id_type, resident_type, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssss", $first_name, $middle_name, $last_name, $email, $password, $barangay, $id_type, $resident_type, $file_path);
                
                if ($stmt->execute()) {
                    $success = "Account created successfully! Redirecting to sign in...";
                    header("refresh:2;url=sign-in.php");
                } else {
                    $error = "Error creating account. Please try again.";
                }
                $stmt->close();
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>eBCsH System - Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Quicksand:wght@500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', 'Quicksand', Arial, sans-serif;
        }
        
        .container {
            display: flex;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }
        
        /* Left Panel */
        .left-panel {
            flex: 1;
            min-width: 320px;
            background: linear-gradient(135deg, #a3c3ad 0%, #22594b 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        
        .logo-img {
            width: 270px;
            max-width: 48%;
            border-radius: 130px;
            margin-bottom: 30px;
        }
        
        .welcome-text {
            color: #fff;
            font-size: 1.25rem;
            text-align: center;
            margin-top: 12px;
            line-height: 1.4;
        }
        
        /* Right Panel */
        .right-panel {
            flex: 1;
            min-width: 420px;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 56px;
            overflow-y: auto;
        }
        
        /* Tab Group */
        .tab-group {
            display: flex;
            width: 100%;
            max-width: 760px;
            background: transparent;
            border-radius: 0;
            margin-bottom: 28px;
            overflow: visible;
            height: auto;
            align-items: center;
            gap: 16px;
            justify-content: center;
        }
        
        .tab-btn {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            font-size: 1.05rem;
            color: #999;
            cursor: pointer;
            font-weight: 500;
            border-radius: 12px;
            padding: 12px 40px;
            transition: all 0.3s ease;
            min-width: 140px;
        }
        
        .tab-btn.active {
            background: #fff;
            color: #22594b;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* Form Container */
        .form-container {
            width: 100%;
            max-width: 760px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .form-header {
            width: 100%;
            text-align: left;
            margin-bottom: 8px;
            margin-top: 8px;
        }
        
        .form-title {
            font-family: 'Montserrat', 'Poppins', Arial, sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 4px;
        }
        
        .form-subtitle {
            font-size: 1.05rem;
            color: #888;
            margin-bottom: 24px;
        }
        
        .form-content {
            width: 100%;
        }
        
        /* Error/Success Messages */
        .error {
            color: #dc2626;
            font-size: 0.875rem;
            margin-bottom: 12px;
            padding: 8px 12px;
            background: #fee2e2;
            border-radius: 6px;
        }
        
        .success {
            color: #16a34a;
            font-size: 0.875rem;
            margin-bottom: 12px;
            padding: 8px 12px;
            background: #d1fae5;
            border-radius: 6px;
        }
        
        /* Form Grid Layout */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 24px;
            align-items: start;
        }
        
        .form-grid .full {
            grid-column: 1 / -1;
        }
        
        /* Input Groups */
        .input-group {
            width: 100%;
        }
        
        .input-label {
            font-size: 0.875rem;
            color: #222;
            margin-bottom: 4px;
            font-weight: 500;
            display: block;
        }
        
        .input-eye-wrapper {
            position: relative;
            width: 100%;
            display: block;
        }
        
        .input-box {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #fff;
            font-size: 1rem;
            color: #222;
            outline: none;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            display: block;
        }
        
        .input-box.with-icon {
            padding-left: 44px;
        }
        
        .input-box.with-right-icon {
            padding-right: 44px;
        }
        
        .input-box:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            opacity: 0.7;
            pointer-events: none;
        }
        
        .eye-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            cursor: pointer;
            opacity: 0.85;
            background: transparent;
            border: 0;
            padding: 0;
        }
        
        .eye-toggle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        /* Select Dropdown */
        select.input-box {
            appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }
        
        /* Upload Box */
        .upload-box {
            height: 110px;
            border-radius: 8px;
            background: #f5f6fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed rgba(0,0,0,0.1);
        }
        
        .upload-btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #888;
            background: #fff;
            cursor: pointer;
            font-size: 0.875rem;
            font-family: 'Poppins', sans-serif;
        }
        
        #fileInput {
            display: none;
        }
        
        /* Checkbox Group */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
        }
        
        .checkbox-group input[type="checkbox"] {
            accent-color: #22594b;
            cursor: pointer;
        }
        
        .checkbox-group label {
            font-size: 0.875rem;
            color: #222;
            cursor: pointer;
        }
        
        /* Terms Checkbox */
        .terms-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
        }
        
        .terms-group input[type="checkbox"] {
            accent-color: #22594b;
            cursor: pointer;
        }
        
        .terms-group label {
            font-size: 0.875rem;
            color: #222;
            cursor: pointer;
        }
        
        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 14px 0;
            border-radius: 10px;
            border: none;
            background: #22594b;
            color: #fff;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(34, 89, 75, 0.2);
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            margin-top: 8px;
        }
        
        .submit-btn:hover {
            background: #1a453a;
            box-shadow: 0 4px 12px rgba(34, 89, 75, 0.3);
        }
        
        /* Bottom Text */
        .bottom-note {
            width: 100%;
            text-align: center;
            margin-top: 12px;
            color: #666;
            font-size: 1rem;
        }
        
        .bottom-note a {
            color: #22594b;
            font-weight: 600;
            text-decoration: none;
        }
        
        .bottom-note a:hover {
            text-decoration: underline;
        }
        
        /* Responsive Design */
        @media (max-width: 980px) {
            .container {
                flex-direction: column;
                height: auto;
            }
            
            .left-panel, .right-panel {
                width: 100%;
                padding: 32px 20px;
                min-width: auto;
            }
            
            .logo-img {
                max-width: 180px;
                margin-bottom: 18px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 640px) {
            .right-panel {
                padding: 24px 16px;
            }
            
            .form-title {
                font-size: 1.125rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <img class="logo-img" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="Logo">
            <div class="welcome-text">
                Welcome to eBCsH<br>Your friendly assistant is here to help!
            </div>
        </div>

        <div class="right-panel">
            <div class="tab-group">
                <button class="tab-btn" type="button" onclick="window.location.href='sign-in.php'">Sign In</button>
                <button class="tab-btn active" type="button">Sign Up</button>
            </div>

            <div class="form-container">
                <div class="form-header">
                    <h2 class="form-title">Create Account</h2>
                </div>

                <div class="form-content">
                    <?php if ($error): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="sign-up.php" enctype="multipart/form-data">
                        <div class="form-grid">
                            <!-- First Name -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">First Name *</label>
                                    <input class="input-box" type="text" name="first_name" placeholder="First Name" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Middle Name -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Middle Name</label>
                                    <input class="input-box" type="text" name="middle_name" placeholder="Middle Name" value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Last Name -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Last Name *</label>
                                    <input class="input-box" type="text" name="last_name" placeholder="Last Name" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Email - Full Width -->
                            <div class="full">
                                <div class="input-group">
                                    <label class="input-label">Email Address *</label>
                                    <div class="input-eye-wrapper">
                                        <img class="input-icon" src="https://img.icons8.com/ios-filled/50/000000/new-post.png" alt="">
                                        <input class="input-box with-icon" type="email" name="email" placeholder="example@gmail.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Password -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Password *</label>
                                    <div class="input-eye-wrapper">
                                        <img class="input-icon" src="https://cdn-icons-png.flaticon.com/128/345/345535.png" alt="">
                                        <input id="signUpPassword" class="input-box with-icon with-right-icon" type="password" name="password" placeholder="Create a password" required>
                                        <button type="button" class="eye-toggle" onclick="togglePassword('signUpPassword', 'eyeSignUpPassword')">
                                            <img id="eyeSignUpPassword" src="https://cdn-icons-png.flaticon.com/128/2767/2767146.png" alt="">
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Barangay -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Barangay *</label>
                                    <input class="input-box" type="text" name="barangay" placeholder="Address" required value="<?php echo isset($_POST['barangay']) ? htmlspecialchars($_POST['barangay']) : ''; ?>">
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Confirm Password *</label>
                                    <div class="input-eye-wrapper">
                                        <img class="input-icon" src="https://cdn-icons-png.flaticon.com/128/345/345535.png" alt="">
                                        <input id="confirmPassword" class="input-box with-icon with-right-icon" type="password" name="confirm_password" placeholder="Confirm your password" required>
                                        <button type="button" class="eye-toggle" onclick="togglePassword('confirmPassword', 'eyeConfirmPassword')">
                                            <img id="eyeConfirmPassword" src="https://cdn-icons-png.flaticon.com/128/2767/2767146.png" alt="">
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty cell for alignment -->
                            <div></div>

                            <!-- Upload Valid ID -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Upload Valid ID *</label>
                                    <div id="uploadBox" class="upload-box">
                                        <input type="file" id="fileInput" name="file" accept=".jpg,.jpeg,.png,.pdf" required>
                                        <label for="fileInput" class="upload-btn">Browse File</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Type of ID -->
                            <div>
                                <div class="input-group">
                                    <label class="input-label">Type of ID *</label>
                                    <div class="input-eye-wrapper">
                                        <img class="input-icon" src="https://cdn-icons-png.flaticon.com/128/2659/2659360.png" alt="">
                                        <select class="input-box with-icon" name="id_type" required>
                                            <option value="">Select ID Type</option>
                                            <option value="government-id">Government ID</option>
                                            <option value="drivers-license">Driver's License</option>
                                            <option value="passport">Passport</option>
                                            <option value="postal-id">Postal ID</option>
                                            <option value="voters-id">Voter's ID</option>
                                            <option value="senior-citizen-id">Senior Citizen ID</option>
                                            <option value="pwd-id">PWD ID</option>
                                            <option value="philhealth-id">PhilHealth ID</option>
                                            <option value="sss-id">SSS ID</option>
                                            <option value="umid">UMID</option>
                                            <option value="student-id">Student ID</option>
                                            <option value="company-id">Company ID</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Resident/Non-Resident Checkboxes -->
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="resident" name="resident_type" value="resident" onchange="handleResidentChange(this)">
                                        <label for="resident">Resident</label>
                                        <input type="checkbox" id="non-resident" name="resident_type" value="non-resident" onchange="handleNonResidentChange(this)">
                                        <label for="non-resident">Non Resident</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms and Conditions - Full Width -->
                            <div class="full">
                                <div class="terms-group">
                                    <input type="checkbox" id="terms" name="terms" required>
                                    <label for="terms">I agree to the Terms and Conditions *</label>
                                </div>
                            </div>

                            <!-- Submit Button - Full Width -->
                            <div class="full">
                                <button class="submit-btn" type="submit">Create Account</button>
                            </div>
                        </div>
                    </form>

                    <div class="bottom-note">
                        Already have an account? <a href="sign-in.php">Sign in instead</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password toggle function
        function togglePassword(inputId, eyeId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            const openIcon = 'https://cdn-icons-png.flaticon.com/128/709/709612.png';
            const closedIcon = 'https://cdn-icons-png.flaticon.com/128/2767/2767146.png';
            
            if (input.type === 'password') {
                input.type = 'text';
                eye.src = openIcon;
            } else {
                input.type = 'password';
                eye.src = closedIcon;
            }
        }

        // File input label update
        (function() {
            const fileInput = document.getElementById('fileInput');
            const uploadBox = document.getElementById('uploadBox');
            const btn = uploadBox.querySelector('.upload-btn');
            
            fileInput.addEventListener('change', function() {
                if (!fileInput.files || fileInput.files.length === 0) {
                    btn.textContent = 'Browse File';
                    return;
                }
                const names = Array.from(fileInput.files).map(f => f.name).join(', ');
                btn.textContent = names.length > 36 ? names.slice(0, 33) + '...' : names;
            });
        })();

        // Resident/Non-Resident checkbox handling
        function handleResidentChange(checkbox) {
            if (checkbox.checked) {
                document.getElementById('non-resident').checked = false;
            }
        }

        function handleNonResidentChange(checkbox) {
            if (checkbox.checked) {
                document.getElementById('resident').checked = false;
            }
        }
    </script>
</body>
</html>