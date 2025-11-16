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
$user_id = $_SESSION['user_id'];
$search_result = null;
$search_error = "";
// Handle search
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ticket_id']) && !isset($_POST['logout'])) {
    $ticket_id = trim($_POST['ticket_id']);
    if (!empty($ticket_id)) {
        $stmt = $conn->prepare("SELECT ticket_id, requesttype, status, submitted_at FROM requests WHERE ticket_id = ? AND user_id = ?");
        $stmt->bind_param("si", $ticket_id, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($ticket_id, $requesttype, $status, $submitted_at);
            $stmt->fetch();
            $search_result = ['ticket_id' => $ticket_id, 'requesttype' => $requesttype, 'status' => $status, 'submitted_at' => $submitted_at];
        } else {
            $search_error = "No request found for that ticket ID.";
        }
        $stmt->close();
    } else {
        $search_error = "Please enter a ticket ID.";
    }
}
// Fetch recent requests for the user
$recent_requests = [];
$stmt = $conn->prepare("SELECT ticket_id, requesttype, status, submitted_at FROM requests WHERE user_id = ? ORDER BY submitted_at DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_requests[] = $row;
}
$stmt->close();
$conn->close();
// Helper function for status color
function statusColor($status) {
    switch (strtoupper($status)) {
        case 'READY': return '#064b38';
        case 'UNDER REVIEW': return '#f39c12';
        case 'COMPLETED': return '#1ea2a8';
        case 'IN PROGRESS':
        case 'PENDING': return '#ff6b4a';
        default: return '#6b6f72';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Track Request — eBCsH</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { 
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body {
      background: #DAF1DE;
      color: #223;
      min-height: 100vh;
    }
    
    /* Header */
    header {
      background: #fff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .header-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .header-left img {
      height: 48px;
      width: 48px;
      object-fit: cover;
    }
    .header-left .title {
      font-size: 16px;
      font-weight: 600;
      line-height: 1.3;
    }
    .logout-btn {
      background: #FD7E7E;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: opacity 0.2s;
    }
    .logout-btn:hover {
      opacity: 0.9;
    }
    
    /* Main Container */
    .frame { 
      max-width: 1200px; 
      margin: 28px auto; 
      background: #fff; 
      border-radius: 12px; 
      box-shadow: 0 6px 30px rgba(0,0,0,0.08); 
      overflow: hidden; 
    }
    .container { 
      display: grid; 
      grid-template-columns: 1fr 300px; 
      gap: 20px; 
      padding: 20px;
    }
    
    /* Track Section */
    .track { 
      background: linear-gradient(180deg, #fafafa, #f6f8f7); 
      border-radius: 10px; 
      padding: 20px;
    }
    .track h2 { 
      margin: 0 0 8px 0; 
      font-size: 18px;
      font-weight: 600;
      color: #064b38; 
    }
    .label { 
      color: #6b6f72; 
      font-size: 14px;
      margin-bottom: 8px;
    }
    .input-row { 
      display: flex; 
      gap: 10px; 
      margin-top: 10px; 
    }
    .input-row input[type=text] { 
      flex: 1; 
      padding: 10px 12px; 
      border-radius: 8px; 
      border: 1px solid #e1e6e4; 
      background: #fff;
      font-size: 14px;
    }
    .btn { 
      background: #064b38; 
      color: #fff; 
      border: none; 
      padding: 10px 16px; 
      border-radius: 8px;
      cursor: pointer; 
      font-weight: 600;
      font-size: 14px;
      white-space: nowrap;
      transition: opacity 0.2s;
    }
    .btn:hover {
      opacity: 0.9;
    }
    
    /* Search Results */
    .search-result { 
      margin-top: 14px; 
      padding: 12px; 
      border-radius: 8px; 
      background: #f0f8f0; 
      border: 1px solid #d0e0d0;
      line-height: 1.6;
    }
    .search-error { 
      margin-top: 14px; 
      padding: 12px; 
      border-radius: 8px; 
      background: #ffeaea; 
      border: 1px solid #ffdddd; 
      color: #d9534f; 
    }
    .info-row {
      margin-top: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .badge {
      background: #e8f5e9;
      color: #064b38;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 13px;
      font-weight: 600;
    }
    
    /* Help Section */
    .help { 
      padding: 20px; 
      border-radius: 10px; 
      background: linear-gradient(180deg, #fff, #f7fbf9); 
      border: 1px solid #e9f0ed; 
      text-align: center;
    }
    .icon { 
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: #f0f4f2;
      display: inline-grid;
      place-items: center;
      font-weight: 700;
      font-size: 24px;
      color: #064b38;
      margin: 0 auto 6px;
    }
    .help-title {
      font-weight: 700;
      margin: 8px 0;
    }
    .help-text {
      color: #6b6f72;
      font-size: 14px;
      margin: 8px 0;
      line-height: 1.6;
    }
    
    /* Recent Requests Section */
    .recent { 
      padding: 20px 22px 28px 22px; 
      border-top: 1px solid #eef3ef;
    }
    .recent-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }
    .recent-title {
      font-weight: 800;
      font-size: 16px;
    }
    .tabs { 
      display: flex; 
      gap: 8px; 
    }
    .tab { 
      background: #f2f6f4; 
      padding: 6px 10px; 
      border-radius: 20px; 
      font-weight: 600; 
      color: #064b38; 
      cursor: pointer;
      border: none;
      font-size: 14px;
      transition: all 0.2s;
    }
    .tab.active { 
      background: #064b38; 
      color: #fff;
    }
    .tab:hover {
      opacity: 0.8;
    }
    
    /* Cards */
    .cards { 
      display: grid; 
      grid-template-columns: repeat(2, 1fr); 
      gap: 16px; 
      margin-top: 16px; 
    }
    .card { 
      background: linear-gradient(180deg, #fff, #fbfffd); 
      border-radius: 10px; 
      padding: 14px; 
      border: 1px solid #eef4f1;
    }
    .card-header {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
    }
    .dot { 
      width: 10px; 
      height: 10px; 
      border-radius: 50%;
      flex-shrink: 0;
    }
    .ticket-id { 
      font-weight: 700;
      flex: 1;
      font-size: 14px;
    }
    .status { 
      font-size: 13px; 
      font-weight: 700; 
    }
    .card-type {
      font-weight: 700;
      margin-top: 6px;
      font-size: 14px;
    }
    .card-meta { 
      font-size: 13px; 
      color: #6b6f72; 
      margin-top: 6px; 
    }
    
    @media(max-width: 880px) { 
      .container { 
        grid-template-columns: 1fr;
      } 
      .cards { 
        grid-template-columns: 1fr;
      }
      header {
        padding: 1rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="Logo">
      <div class="title">Barangay 170-E<br><span style="font-weight:400">Community Portal</span></div>
    </div>
    <form method="POST" action="trackreq.php" style="display:inline">
      <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
  </header>
  
  <div class="frame">
    <div class="container">
      <div>
        <div class="track">
          <h2>Track New Request</h2>
          <div class="label">Ticket ID</div>
          <form method="POST" action="trackreq.php">
            <div class="input-row">
              <input type="text" name="ticket_id" placeholder="Enter your ticket ID (e.g., BHR-2024-001234)" required />
              <button type="submit" class="btn">Track Request</button>
            </div>
          </form>
          
          <?php if ($search_result): ?>
            <div class="search-result">
              <div><strong>Ticket ID:</strong> <?php echo htmlspecialchars($search_result['ticket_id']); ?></div>
              <div><strong>Type:</strong> <?php echo htmlspecialchars($search_result['requesttype']); ?></div>
              <div><strong>Status:</strong> <?php echo htmlspecialchars($search_result['status']); ?></div>
              <div><strong>Submitted:</strong> <?php echo htmlspecialchars($search_result['submitted_at']); ?></div>
            </div>
          <?php elseif ($search_error): ?>
            <div class="search-error"><?php echo htmlspecialchars($search_error); ?></div>
          <?php endif; ?>
          
          <div class="info-row">
            <span class="badge">In Ready</span>
            <div class="label" style="margin:0">Enter a ticket ID and click "Track Request" to find its status.</div>
          </div>
        </div>
      </div>
      
      <aside class="help">
        <div class="icon">?</div>
        <div class="help-title">Need Help?</div>
        <div class="help-text">• Lost your ID?<br>• Contact Support</div>
      </aside>
    </div>
    
    <div class="recent">
      <div class="recent-top">
        <div class="recent-title">My Recent Requests</div>
        <div class="tabs">
          <button class="tab active" data-filter="all">All</button>
          <button class="tab" data-filter="in progress">Pending</button>
          <button class="tab" data-filter="under review">Under Review</button>
          <button class="tab" data-filter="ready">Ready</button>
          <button class="tab" data-filter="completed">Completed</button>
        </div>
      </div>
      
      <div class="cards">
        <?php foreach ($recent_requests as $request): ?>
          <div class="card" data-status="<?php echo strtolower($request['status']); ?>">
            <div class="card-header">
              <span class="dot" style="background:<?php echo statusColor($request['status']); ?>"></span>
              <div class="ticket-id"><?php echo htmlspecialchars($request['ticket_id']); ?></div>
              <div class="status" style="color:<?php echo statusColor($request['status']); ?>"><?php echo htmlspecialchars($request['status']); ?></div>
            </div>
            <div class="card-type"><?php echo htmlspecialchars($request['requesttype']); ?></div>
            <div class="card-meta">Submitted: <?php echo htmlspecialchars(date('Y-m-d', strtotime($request['submitted_at']))); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  
  <script>
    // Tabs functionality
    document.querySelectorAll('.tab').forEach(t => {
      t.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(x => x.classList.remove('active'));
        t.classList.add('active');
        const filter = t.dataset.filter;
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
          const status = card.dataset.status;
          if (filter === 'all' || status.includes(filter)) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });
  </script>
</body>
</html>