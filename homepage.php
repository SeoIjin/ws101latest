<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: sign-in.php");
    exit();
}
// Handle logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    session_destroy();
    header("Location: sign-in.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>eBCsH - Community Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-green: #166534;
      --light-green: #dcfce7;
      --green-50: #f0fdf4;
      --green-100: #dcfce7;
      --green-600: #16a34a;
      --green-700: #15803d;
      --green-800: #166534;
      --green-900: #14532d;
      --blue-500: #3b82f6;
      --blue-600: #2563eb;
      --red-500: #ef4444;
      --red-600: #dc2626;
      --orange-100: #ffedd5;
      --orange-600: #ea580c;
      --font: 'Poppins', sans-serif;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: var(--font);
    }
    
    body {
      background: linear-gradient(135deg, var(--green-50) 0%, var(--light-green) 100%);
      min-height: 100vh;
      color: #333;
    }
    
    /* Header */
    header {
      background-color: #fff;
      border-bottom: 1px solid var(--green-100);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 50;
    }
    
    .header-container {
      max-width: 1600px;
      margin: 0 auto;
      padding: 1rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .header-left {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    
    .header-left img {
      width: 40px;
      height: 40px;
    }
    
    .header-left .title h1 {
      font-size: 1.125rem;
      color: var(--green-800);
      font-weight: 600;
    }
    
    .header-left .title p {
      font-size: 0.875rem;
      color: var(--green-600);
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .notification-btn,
    .profile-btn {
      background-color: var(--green-600);
      color: #fff;
      border: none;
      padding: 0.625rem;
      border-radius: 0.375rem;
      cursor: pointer;
      transition: background-color 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .notification-btn:hover,
    .profile-btn:hover {
      background-color: var(--green-700);
    }

    .notification-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      background: var(--red-500);
      color: #fff;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.625rem;
      font-weight: 600;
    }

    .dropdown {
      position: absolute;
      top: calc(100% + 0.5rem);
      right: 0;
      background: white;
      border: 1px solid #e5e7eb;
      border-radius: 0.5rem;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      width: 400px;
      max-height: 500px;
      overflow-y: auto;
      z-index: 1000;
      display: none;
    }

    .dropdown.active {
      display: block;
    }

    .dropdown-header {
      padding: 1rem;
      border-bottom: 1px solid #e5e7eb;
      position: sticky;
      top: 0;
      background: white;
      z-index: 1;
    }

    .dropdown-header h3 {
      margin: 0;
      fontSize: 1rem;
      fontWeight: 600;
      color: var(--green-900);
    }

    .dropdown-item {
      padding: 1rem;
      border-bottom: 1px solid #f3f4f6;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .dropdown-item:hover {
      background: #f9fafb;
    }

    .dropdown-empty {
      padding: 2rem;
      text-align: center;
      color: #6b7280;
    }

    .status-badge {
      display: inline-block;
      padding: 0.125rem 0.375rem;
      border-radius: 0.25rem;
      fontSize: 0.6875rem;
      fontWeight: 600;
      color: white;
    }

    .dropdown-footer {
      padding: 0.75rem 1rem;
      border-top: 1px solid #e5e7eb;
      background: #f9fafb;
    }

    .dropdown-footer button {
      width: 100%;
      background: var(--green-600);
      color: white;
      border: none;
      padding: 0.5rem;
      border-radius: 0.375rem;
      fontSize: 0.875rem;
      fontWeight: 600;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .dropdown-footer button:hover {
      background: var(--green-700);
    }
    
    .logout-btn {
      background-color: var(--red-500);
      color: #fff;
      border: none;
      padding: 0.625rem 1.25rem;
      border-radius: 0.375rem;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    
    .logout-btn:hover {
      background-color: var(--red-600);
    }
    
    /* Main Layout */
    .main-wrapper {
      max-width: 1600px;
      margin: 0 auto;
      padding: 3rem 1.5rem;
      display: flex;
      gap: 2rem;
      align-items: start;
      justify-content: center;
    }
    
    /* Sidebars */
    .sidebar {
      width: 320px;
      flex-shrink: 0;
    }
    
    .sidebar-card {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(8px);
      border: 1px solid #d1fae5;
      border-radius: 0.5rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      position: sticky;
      top: 6rem;
    }
    
    .card-header {
      padding: 1.25rem 1.5rem 0.5rem;
      border-bottom: 1px solid var(--green-100);
    }
    
    .card-title {
      font-size: 1.125rem;
      font-weight: 700;
      color: var(--green-900);
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin: 0;
    }
    
    .card-content {
      padding: 1.5rem;
    }
    
    /* Contact Sidebar */
    .contact-section {
      margin-bottom: 1.5rem;
    }
    
    .contact-section h3 {
      color: var(--green-900);
      font-size: 1rem;
      margin-bottom: 0.75rem;
    }
    
    .contact-item {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 0.75rem;
      font-size: 0.875rem;
    }
    
    .contact-item i {
      color: var(--green-600);
      width: 16px;
      margin-top: 2px;
      flex-shrink: 0;
    }
    
    .contact-label {
      color: var(--green-700);
      font-weight: 500;
    }
    
    .contact-value {
      color: var(--green-900);
    }
    
    .divider {
      border-top: 1px solid var(--green-200);
      margin: 1rem 0;
    }
    
    .quote-box {
      font-style: italic;
      color: var(--green-800);
      line-height: 1.6;
      font-size: 0.875rem;
    }
    
    .services-list {
      list-style: none;
      color: var(--green-700);
      font-size: 0.875rem;
    }
    
    .services-list li {
      margin-bottom: 0.5rem;
    }
    
    .icon-row {
      display: flex;
      justify-content: space-around;
      padding-top: 1rem;
    }
    
    .icon-row i {
      font-size: 1.75rem;
      color: var(--green-600);
    }
    
    .tip-box {
      background-color: var(--green-50);
      border: 1px solid var(--green-100);
      border-radius: 0.5rem;
      padding: 0.75rem;
      font-size: 0.875rem;
    }
    
    .tip-box .tip-title {
      color: var(--green-800);
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    
    .tip-box p {
      color: var(--green-700);
      line-height: 1.5;
      margin: 0;
    }
    
    /* Updates Sidebar */
    .update-item {
      margin-bottom: 1.5rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid var(--green-100);
    }
    
    .update-item:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }
    
    .update-header {
      display: flex;
      gap: 0.5rem;
      align-items: start;
      margin-bottom: 0.5rem;
    }
    
    .badge {
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.75rem;
      font-weight: 600;
      flex-shrink: 0;
    }
    
    .badge-new {
      background-color: var(--green-100);
      color: var(--green-700);
    }
    
    .badge-info {
      background-color: #dbeafe;
      color: #1e40af;
    }
    
    .badge-event {
      background-color: #fee2e2;
      color: #991b1b;
    }

    .badge-news {
      background-color: var(--green-100);
      color: var(--green-700);
    }
    
    .update-title {
      font-size: 0.9375rem;
      color: var(--green-900);
      font-weight: 600;
      margin-bottom: 0.25rem;
    }
    
    .update-date {
      font-size: 0.8125rem;
      color: var(--green-700);
      margin-bottom: 0.5rem;
    }
    
    .update-description {
      font-size: 0.875rem;
      color: var(--green-600);
      line-height: 1.5;
      margin: 0;
    }

    .show-more-btn {
      background: none;
      color: var(--green-600);
      border: none;
      padding: 0.5rem 0;
      text-align: center;
      cursor: pointer;
      transition: color 0.2s;
      font-family: var(--font);
      font-size: 0.875rem;
      width: 100%;
      font-weight: 500;
    }

    .show-more-btn:hover {
      color: var(--green-700);
    }
    
    /* Center Content */
    .center-content {
      flex: 1;
      max-width: 1000px;
    }
    
    /* Welcome Section */
    .welcome-section {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .logo-circle {
      display: inline-block;
      padding: 1rem;
      background-color: #fff;
      border-radius: 50%;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 1rem;
    }
    
    .logo-circle img {
      width: 48px;
      height: 48px;
    }
    
    .welcome-section h1 {
      font-size: 1.875rem;
      color: var(--green-900);
      font-weight: 700;
      margin-bottom: 0.75rem;
    }
    
    .welcome-section p {
      font-size: 1rem;
      color: var(--green-700);
      max-width: 42rem;
      margin: 0 auto;
      line-height: 1.6;
    }
    
    /* Action Cards */
    .action-cards {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .action-card {
      background-color: #fff;
      border: 1px solid var(--green-100);
      border-radius: 0.5rem;
      padding: 2rem 1.5rem;
      text-align: center;
      transition: box-shadow 0.3s;
      cursor: pointer;
    }
    
    .action-card:hover {
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    
    .action-icon {
      width: 64px;
      height: 64px;
      background-color: var(--green-100);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
    }
    
    .action-icon i {
      font-size: 2rem;
      color: var(--green-700);
    }
    
    .action-card h3 {
      font-size: 1.25rem;
      color: var(--green-900);
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    .action-card p {
      font-size: 0.9375rem;
      color: var(--green-600);
      margin-bottom: 1.25rem;
      line-height: 1.5;
    }
    
    .action-card button {
      border: none;
      padding: 0.625rem 1.25rem;
      border-radius: 0.375rem;
      font-weight: 600;
      font-size: 0.9375rem;
      cursor: pointer;
      color: #fff;
      transition: background-color 0.2s;
    }
    
    .btn-submit {
      background-color: var(--blue-500);
    }
    
    .btn-submit:hover {
      background-color: var(--blue-600);
    }
    
    .btn-track {
      background-color: var(--green-600);
    }
    
    .btn-track:hover {
      background-color: var(--green-700);
    }
    
    /* How It Works */
    .how-it-works {
      background-color: #fff;
      border: 1px solid var(--green-100);
      border-radius: 0.5rem;
      padding: 2rem 1.5rem;
    }
    
    .how-it-works h2 {
      text-align: center;
      font-size: 1.5rem;
      color: var(--green-900);
      font-weight: 700;
      margin-bottom: 2rem;
    }
    
    .steps {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
    }
    
    .step {
      text-align: center;
    }
    
    .step-icon {
      width: 64px;
      height: 64px;
      background-color: var(--orange-100);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 0.75rem;
    }
    
    .step-icon i {
      font-size: 2rem;
      color: var(--orange-600);
    }
    
    .step h3 {
      font-size: 1.125rem;
      color: var(--green-900);
      font-weight: 600;
      margin-bottom: 0.75rem;
    }
    
    .step p {
      font-size: 0.9375rem;
      color: var(--green-700);
      line-height: 1.6;
      margin: 0;
    }
    
    /* Footer */
    footer {
      background-color: #fff;
      border-top: 1px solid var(--green-100);
      margin-top: 3rem;
    }
    
    .footer-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 2rem 1.5rem;
    }
    
    .footer-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
      margin-bottom: 1.5rem;
    }
    
    .footer-section {
      text-align: center;
    }
    
    .footer-section h3 {
      font-size: 1.125rem;
      color: var(--green-900);
      font-weight: 600;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    
    .footer-section h3 i {
      color: var(--green-600);
    }
    
    .footer-list {
      display: inline-block;
      text-align: left;
    }
    
    .footer-item {
      display: flex;
      align-items: flex-start;
      gap: 1rem;
      margin-bottom: 0.75rem;
      font-size: 0.9375rem;
    }
    
    .footer-item-label {
      color: var(--green-700);
      min-width: 80px;
    }
    
    .footer-item-value {
      color: var(--green-900);
    }
    
    .hospital-item {
      margin-bottom: 0.75rem;
    }
    
    .hospital-name {
      color: var(--green-700);
      font-weight: 500;
    }
    
    .hospital-contact {
      color: var(--green-900);
      font-size: 0.875rem;
    }
    
    .footer-divider {
      border-top: 1px solid var(--green-200);
      margin: 1.5rem 0;
    }
    
    .copyright {
      text-align: center;
      color: var(--green-700);
      font-size: 0.9375rem;
    }
    
    .copyright p {
      margin-bottom: 0.5rem;
    }
    
    /* Responsive Design */
    @media (max-width: 1280px) {
      .sidebar {
        display: none;
      }
      
      .main-wrapper {
        justify-content: center;
      }
    }
    
    @media (max-width: 768px) {
      .action-cards {
        grid-template-columns: 1fr;
      }
      
      .steps {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }
      
      .footer-grid {
        grid-template-columns: 1fr;
      }
      
      .main-wrapper {
        padding: 2rem 1rem;
      }

      .dropdown {
        width: 320px;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header>
    <div class="header-container">
      <div class="header-left">
        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="Barangay Logo">
        <div class="title">
          <h1>Barangay 170</h1>
          <p>Deparo, Caloocan</p>
        </div>
      </div>
      <div class="header-right">
        <!-- Notification Bell -->
        <div style="position: relative;">
          <button class="notification-btn" id="notificationBtn">
            <i class="fas fa-bell"></i>
            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
          </button>
          
          <!-- Notification Dropdown -->
          <div class="dropdown" id="notificationDropdown">
            <div class="dropdown-header">
              <h3>Your Request Progress</h3>
            </div>
            <div id="notificationContent">
              <div class="dropdown-empty">
                <p>No requests yet. Submit your first request!</p>
              </div>
            </div>
            <div class="dropdown-footer">
              <button onclick="window.location.href='trackreq.php'">View All Requests</button>
            </div>
          </div>
        </div>

        <!-- Profile Button -->
        <button class="profile-btn" onclick="window.location.href='profilepage.php'">
          <i class="fas fa-user-circle"></i>
        </button>

        <form method="POST" action="homepage.php" style="display: inline;">
          <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="main-wrapper">
    <!-- Left Sidebar - Contact Information -->
    <aside class="sidebar">
      <div class="sidebar-card">
        <div class="card-header">
          <h2 class="card-title">
            <i class="fas fa-phone"></i>
            Contact Us
          </h2>
        </div>
        <div class="card-content">
          <div class="contact-section">
            <h3>Barangay Health Office</h3>
            <div class="contact-item">
              <i class="fas fa-map-marker-alt"></i>
              <div>
                <div class="contact-label">Address</div>
                <div class="contact-value">Deparo, Caloocan City, Metro Manila</div>
              </div>
            </div>
            <div class="contact-item">
              <i class="fas fa-phone"></i>
              <div>
                <div class="contact-label">Hotline</div>
                <div class="contact-value">(02) 8123-4567</div>
              </div>
            </div>
            <div class="contact-item">
              <i class="fas fa-envelope"></i>
              <div>
                <div class="contact-label">Email</div>
                <div class="contact-value">K1contrerascris@gmail.com</div>
              </div>
            </div>
            <div class="contact-item">
              <i class="fas fa-clock"></i>
              <div>
                <div class="contact-label">Office Hours</div>
                <div class="contact-value">Mon-Fri, 8:00 AM - 5:00 PM</div>
              </div>
            </div>
          </div>
          <div class="divider"></div>
          <div class="quote-box">
            "Serving our community with care and transparency."
          </div>
          <div class="divider"></div>
          <div class="contact-section">
            <h3>Available Services</h3>
            <ul class="services-list">
              <li>• Medical Certificates</li>
              <li>• Health Clearance</li>
              <li>• Barangay Clearance</li>
              <li>• Barangay Certificates</li>
            </ul>
          </div>
          <div class="divider"></div>
          <div class="icon-row">
            <i class="fas fa-heart"></i>
            <i class="fas fa-stethoscope"></i>
            <i class="fas fa-heartbeat"></i>
          </div>
          <div class="divider"></div>
          <div class="tip-box">
            <div class="tip-title">💡 Did You Know?</div>
            <p>You can now submit and track your requests online 24/7.</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Center Content -->
    <main class="center-content">
      <!-- Welcome Section -->
      <section class="welcome-section">
        <div class="logo-circle">
          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="eBCsH Logo">
        </div>
        <h1>Welcome to eBCsH</h1>
        <p>Submit health-related requests to your barangay and track their progress in real time. Our system ensures transparency and efficient processing of your requests.</p>
      </section>

      <!-- Action Cards -->
      <div class="action-cards">
        <div class="action-card" onclick="window.location.href='submitreq.php'">
          <div class="action-icon">
            <i class="fas fa-file-alt"></i>
          </div>
          <h3>Submit Request</h3>
          <p>File new requests directly to your local barangay health office</p>
          <button class="btn-submit">Start a New Request</button>
        </div>
        <div class="action-card" onclick="window.location.href='trackreq.php'">
          <div class="action-icon">
            <i class="fas fa-search"></i>
          </div>
          <h3>Track Request</h3>
          <p>Check the status and updates on your submitted health requests</p>
          <button class="btn-track">View Existing Request</button>
        </div>
      </div>

      <!-- How it Works -->
      <section class="how-it-works">
        <h2>How it Works</h2>
        <div class="steps">
          <div class="step">
            <div class="step-icon">
              <i class="fas fa-upload"></i>
            </div>
            <h3>Submit</h3>
            <p>Fill out the request form with your details and submit it to the barangay health office</p>
          </div>
          <div class="step">
            <div class="step-icon">
              <i class="fas fa-bell"></i>
            </div>
            <h3>Track</h3>
            <p>Monitor your request's status in real-time and receive notifications for any updates</p>
          </div>
          <div class="step">
            <div class="step-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <h3>Receive</h3>
            <p>Get notified whenever your request is approved and ready for pickup or delivery</p>
          </div>
        </div>
      </section>
    </main>

    <!-- Right Sidebar - Latest Updates -->
    <aside class="sidebar">
      <div class="sidebar-card">
        <div class="card-header">
          <h2 class="card-title">
            <i class="fas fa-bell"></i>
            Latest Updates
          </h2>
        </div>
        <div class="card-content" id="updatesContent">
          <!-- Updates will be loaded here -->
        </div>
      </div>
    </aside>
  </div>

  <!-- Footer -->
  <footer>
    <div class="footer-container">
      <div class="footer-grid">
        <!-- Barangay Health Office -->
        <div class="footer-section">
          <h3>
            <i class="fas fa-building"></i>
            Barangay Health Office
          </h3>
          <div class="footer-list">
            <div class="footer-item">
              <span class="footer-item-label">Address</span>
              <span class="footer-item-value">Deparo, Caloocan City, Metro Manila</span>
            </div>
            <div class="footer-item">
              <span class="footer-item-label">Hotline</span>
              <span class="footer-item-value">(02) 8123-4567</span>
            </div>
            <div class="footer-item">
              <span class="footer-item-label">Email</span>
              <span class="footer-item-value">K1contrerascris@gmail.com</span>
            </div>
            <div class="footer-item">
              <span class="footer-item-label">Hours</span>
              <span class="footer-item-value">Mon-Fri, 8:00 AM - 5:00 PM</span>
            </div>
          </div>
        </div>
        <!-- Emergency Hotlines -->
        <div class="footer-section">
          <h3>
            <i class="fas fa-phone"></i>
            Emergency Hotlines
          </h3>
          <div class="footer-list">
            <div class="footer-item">
              <span class="footer-item-label">Police</span>
              <span class="footer-item-value">(02) 8426-4663 | 09193645337</span>
            </div>
            <div class="footer-item">
              <span class="footer-item-label">BFP</span>
              <span class="footer-item-value">(02) 8245 0849</span>
            </div>
            <div class="footer-item">
              <span class="footer-item-label">Meralco</span>
              <span class="footer-item-value">(02) 16211 or 16211</span>
            </div>
            <div class="footer-item">
              <span class="footer-item-label">Maynilad</span>
              <span class="footer-item-value">1626 | +63 998 864 1446</span>
            </div>
          </div>
        </div>
        <!-- Hospitals Near Barangay -->
        <div class="footer-section">
          <h3>
            <i class="fas fa-stethoscope"></i>
            Hospitals Near Barangay
          </h3>
          <div class="footer-list">
            <div class="hospital-item">
              <div class="hospital-name">Camarin Doctors Hospital</div>
              <div class="hospital-contact">2-7004-2881 | 0945-795-43-93</div>
            </div>
            <div class="hospital-item">
              <div class="hospital-name">Caloocan City North Medical center</div>
              <div class="hospital-contact">(02) 8288 7077</div>
            </div>
            <div class="hospital-item">
              <div class="hospital-name">Nodado General Hospital</div>
              <div class="hospital-contact">(02) 899 51920 | 0919 058 5858</div>
            </div>
          </div>
        </div>
      </div>
      <div class="footer-divider"></div>
      <div class="copyright">
        <p>© 2025 Barangay 170, Deparo, Caloocan. All rights reserved.</p>
        <p>Electronic Barangay Certificate System for Health (eBCsH)</p>
      </div>
    </div>
  </footer>

  <script>
    // Notification dropdown toggle
    let notificationsExpanded = false;
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');

    notificationBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      notificationDropdown.classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!notificationDropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
        notificationDropdown.classList.remove('active');
      }
    });

    // Fetch user requests and display in notification dropdown
    async function fetchUserRequests() {
      try {
        const response = await fetch('api_get_user_requests.php', {
          cache: 'no-store'
        });
        
        if (!response.ok) {
          console.error('Failed to fetch user requests');
          return;
        }
        
        const data = await response.json();
        
        if (data.requests && data.requests.length > 0) {
          // Show badge with count
          const badge = document.getElementById('notificationBadge');
          badge.textContent = data.requests.length;
          badge.style.display = 'flex';
          
          // Get all updates from requests
          const allUpdates = [];
          data.requests.forEach(request => {
            if (request.updates && request.updates.length > 0) {
              request.updates.forEach(update => {
                allUpdates.push({
                  ...update,
                  requestType: request.type,
                  ticketId: request.ticket_id,
                  requestId: request.id
                });
              });
            }
          });
          
          // Sort by timestamp
          allUpdates.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
          
          // Display updates
          const content = document.getElementById('notificationContent');
          
          if (allUpdates.length === 0) {
            content.innerHTML = '<div class="dropdown-empty"><p>No updates yet. Check back later!</p></div>';
          } else {
            content.innerHTML = allUpdates.map(update => `
              <div class="dropdown-item" onclick="window.location.href='trackreq.php'">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                  <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                      <span class="status-badge" style="background: ${getStatusColor(update.status)};">
                        ${update.status}
                      </span>
                      <span style="font-size: 0.6875rem; color: #9ca3af;">
                        ${formatTimestamp(update.timestamp)}
                      </span>
                    </div>
                    <div style="font-size: 0.8125rem; font-weight: 600; color: #14532d; margin-bottom: 0.25rem;">
                      ${update.requestType}
                    </div>
                    <div style="font-size: 0.6875rem; color: #6b7280; margin-bottom: 0.375rem;">
                      ${update.ticketId}
                    </div>
                  </div>
                </div>
                <p style="font-size: 0.75rem; color: #4b5563; margin: 0; line-height: 1.4;">
                  ${update.message}
                </p>
                <div style="font-size: 0.6875rem; color: #9ca3af; margin-top: 0.5rem; font-style: italic;">
                  Updated by: ${update.updated_by}
                </div>
              </div>
            `).join('');
          }
        }
      } catch (error) {
        console.error('Error fetching user requests:', error);
      }
    }

    function getStatusColor(status) {
      const colors = {
        'New': '#3b82f6',
        'Under Review': '#f59e0b',
        'In Progress': '#8b5cf6',
        'Ready': '#10b981',
        'Completed': '#059669',
        'Rejected': '#ef4444'
      };
      return colors[status] || '#6b7280';
    }

    function formatTimestamp(timestamp) {
      const date = new Date(timestamp);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 60) {
        return diffMins === 0 ? 'Just now' : `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
      } else if (diffHours < 24) {
        return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
      } else if (diffDays < 7) {
        return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
      } else {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      }
    }

    // Fetch updates/notifications for sidebar
    async function fetchUpdates() {
      try {
        const response = await fetch('api_get_notifications.php', {
          cache: 'no-store'
        });
        
        if (!response.ok) {
          console.error('Failed to fetch notifications');
          return;
        }
        
        const notifications = await response.json();
        
        if (notifications && notifications.length > 0) {
          displayUpdates(notifications);
        }
      } catch (error) {
        console.error('Error fetching notifications:', error);
      }
    }

    function displayUpdates(notifications) {
      const content = document.getElementById('updatesContent');
      const displayCount = notificationsExpanded ? notifications.length : Math.min(3, notifications.length);
      const displayedNotifications = notifications.slice(0, displayCount);
      
      let html = displayedNotifications.map(notification => {
        const badgeClass = notification.type === 'NEWS' ? 'badge-news' : 'badge-event';
        return `
          <div class="update-item">
            <div class="update-header">
              <span class="badge ${badgeClass}">${notification.type}</span>
              <div>
                <div class="update-title">${notification.title}</div>
                <div class="update-date">${notification.date}</div>
                <p class="update-description">${notification.description}</p>
              </div>
            </div>
          </div>
        `;
      }).join('');
      
      if (notifications.length > 3) {
        html += `
          <button class="show-more-btn" onclick="toggleNotifications()">
            ${notificationsExpanded ? 'Show Less' : 'Show More'}
          </button>
        `;
      }
      
      content.innerHTML = html;
    }

    function toggleNotifications() {
      notificationsExpanded = !notificationsExpanded;
      fetchUpdates();
    }

    // Initial load
    document.addEventListener('DOMContentLoaded', () => {
      fetchUserRequests();
      fetchUpdates();
      
      // Refresh every 30 seconds
      setInterval(() => {
        fetchUserRequests();
        fetchUpdates();
      }, 30000);
    });
  </script>
</body>
</html>