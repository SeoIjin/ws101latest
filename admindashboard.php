<?php
session_start();
// require admin session
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: sign-in.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
  <title>Modern Admin Dashboard</title>
  <link rel="stylesheet" href="design.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #DAF1DE;
      min-height: 100vh;
    }

    /* Header */
    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      background: white;
      padding: 1rem 2rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .page-header img {
      height: 50px;
      border-radius: 50%;
    }

    .page-header h1 {
      font-size: 1.25rem;
      font-weight: 400;
      flex: 1;
      margin-left: 1rem;
    }

    .header-actions {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }

    .header-actions .tab {
      padding: 0.375rem 1rem;
      font-size: 0.875rem;
      cursor: pointer;
      border: none;
      border-radius: 0.375rem;
      background: #228650;
      color: white;
      transition: opacity 0.2s;
    }

    .header-actions .tab:hover {
      opacity: 0.9;
    }

    .header-actions .logout-tab {
      background: transparent;
      color: #333;
      border: 1px solid rgba(0,0,0,0.08);
    }

    .header-actions .logout-tab:hover {
      background: #f5f5f5;
    }

    /* Main Content */
    .main-content {
      padding: 2rem;
      max-width: 1600px;
      margin: 0 auto;
    }

    /* Analytics Cards */
    .analytics {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }

    .card {
      background: white;
      border-radius: 0.5rem;
      padding: 1.5rem;
      flex: 1;
      min-width: 180px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .card h2 {
      font-size: 2rem;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .card small {
      color: #666;
      font-size: 0.875rem;
    }

    /* Analytics Graph Section */
    .analytics-graph-section {
      background: white;
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .graph-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.25rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .graph-header h2 {
      font-size: 1.25rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.25rem;
    }
    
    .graph-header p {
      color: #7f8c8d;
      font-size: 0.875rem;
      font-weight: 300;
      margin: 0;
    }
    
    .timeframe-selector {
      display: flex;
      gap: 0.5rem;
      background: #f8f9fa;
      padding: 0.25rem;
      border-radius: 0.5rem;
    }
    
    .timeframe-btn {
      padding: 0.375rem 1rem;
      border: none;
      background: transparent;
      color: #7f8c8d;
      border-radius: 0.375rem;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .timeframe-btn:hover {
      background: #e9ecef;
    }
    
    .timeframe-btn.active {
      background: #2E5DFC;
      color: white;
    }
    
    .stats-summary {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .stat-card-small {
      padding: 1.25rem;
      border-radius: 0.625rem;
      color: white;
    }
    
    .stat-card-small.blue {
      background: linear-gradient(135deg, #2E5DFC 0%, #4a76fc 100%);
    }
    
    .stat-card-small.orange {
      background: linear-gradient(135deg, #F66D31 0%, #ff8a5c 100%);
    }
    
    .stat-card-small.green {
      background: linear-gradient(135deg, #07A840 0%, #2bc965 100%);
    }
    
    .stat-card-small h3 {
      font-size: 1.875rem;
      font-weight: 700;
      margin-bottom: 0.25rem;
    }
    
    .stat-card-small p {
      font-size: 0.75rem;
      opacity: 0.95;
      font-weight: 400;
      margin: 0;
    }
    
    .chart-container {
      position: relative;
      height: 320px;
      margin-top: 1rem;
    }
    
    .loading-chart {
      display: none;
      justify-content: center;
      align-items: center;
      height: 320px;
      color: #7f8c8d;
    }
    
    .spinner {
      border: 3px solid #f3f3f3;
      border-top: 3px solid #2E5DFC;
      border-radius: 50%;
      width: 35px;
      height: 35px;
      animation: spin 1s linear infinite;
      margin-right: 12px;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Request Management Section */
    .boxed {
      background: white;
      border-radius: 0.75rem;
      padding: 1.5rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      margin-bottom: 1.5rem;
    }

    .boxed h1 {
      font-size: 1.5rem;
      font-weight: 300;
      margin-bottom: 0.25rem;
    }

    .boxed > p {
      color: #666;
      font-weight: 100;
      margin-bottom: 1.5rem;
      font-size: 0.875rem;
    }

    /* Search Bar */
    .search-bar {
      position: relative;
      margin-bottom: 1rem;
    }

    .search-bar i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
    }

    .search-bar input {
      width: 100%;
      padding: 0.75rem 1rem 0.75rem 3rem;
      border: 1px solid #e0e0e0;
      border-radius: 0.5rem;
      outline: none;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
    }

    .search-bar input:focus {
      border-color: #228650;
    }

    /* Tabs */
    .tabs {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1rem;
      flex-wrap: wrap;
    }

    .tabs .tab {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 0.5rem;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      font-size: 0.875rem;
      text-transform: capitalize;
      transition: all 0.2s;
      background: #f5f5f5;
      color: #333;
    }

    .tabs .tab.active {
      background: #228650;
      color: white;
    }

    /* Table */
    .table-container {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    thead tr {
      background: #f8f9fa;
    }

    th {
      text-align: left;
      padding: 0.75rem;
      border-bottom: 2px solid #dee2e6;
      font-weight: 500;
      font-size: 0.875rem;
    }

    tbody tr {
      border-bottom: 1px solid #dee2e6;
      transition: background 0.2s;
    }

    tbody tr:hover {
      background: #f8f9fa;
    }

    td {
      padding: 0.75rem;
      font-size: 0.875rem;
    }

    td.center {
      text-align: center;
      color: #666;
      padding: 1rem;
    }

    /* Priority Badges */
    .priority-low {
      color: #006b3c;
      background: #d4f8e8;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    .priority-medium {
      color: #9a7800;
      background: #fff3b0;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    .priority-high {
      color: #a30000;
      background: #ffd1d1;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    /* Status Badges */
    .status-under-review {
      color: #f39c12;
      background: #fff3e0;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    .status-in-progress {
      color: #ff6b4a;
      background: #ffe8e4;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    .status-ready {
      color: #505B6D;
      background: #e8ebed;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    .status-completed {
      color: #07A840;
      background: #e6f8ec;
      padding: 0.25rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.875rem;
      display: inline-block;
    }

    /* Actions */
    .actions a {
      margin-right: 0.75rem;
      color: #228650;
      text-decoration: none;
      transition: opacity 0.2s;
    }

    .actions a:last-child {
      color: #007bff;
    }

    .actions a:hover {
      opacity: 0.7;
    }

    @media (max-width: 768px) {
      .main-content {
        padding: 1rem;
      }

      .analytics {
        flex-direction: column;
      }

      .graph-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .stats-summary {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <div class="page-header">
    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRTDCuh4kIpAtR-QmjA1kTjE_8-HSd8LSt3Gw&s" alt="seal">
    <h1>Admin Dashboard</h1>
    <div class="header-actions">
      <button class="tab" id="requestTab">Request</button>
      <button class="tab logout-tab" id="logoutTab">Logout</button>
    </div>
  </div>

  <div class="main-content">
    <!-- Analytics Cards -->
    <div class="analytics">
      <div class="card">
        <h2 id="totalCount" style="color: #2E5DFC;">0</h2>
        <small>Total</small>
      </div>
      <div class="card">
        <h2 id="reviewCount" style="color: #F66D31;">0</h2>
        <small>Under Review</small>
      </div>
      <div class="card">
        <h2 id="progressCount" style="color: #E27508;">0</h2>
        <small>In Progress</small>
      </div>
      <div class="card">
        <h2 id="readyCount" style="color: #505B6D;">0</h2>
        <small>Ready</small>
      </div>
      <div class="card">
        <h2 id="completedCount" style="color: #07A840;">0</h2>
        <small>Completed</small>
      </div>
    </div>

    <!-- Analytics Graph Section -->
    <div class="analytics-graph-section">
      <div class="graph-header">
        <div>
          <h2>📊 Report Graph</h2>
          <p>Track reports over time</p>
        </div>
        <div class="timeframe-selector">
          <button class="timeframe-btn active" data-timeframe="day">Today</button>
          <button class="timeframe-btn" data-timeframe="week">This Week</button>
          <button class="timeframe-btn" data-timeframe="month">This Month</button>
        </div>
      </div>
      
      <div class="stats-summary">
        <div class="stat-card-small blue">
          <h3 id="graphTotalRequests">0</h3>
          <p>Total Requests</p>
        </div>
        <div class="stat-card-small orange">
          <h3 id="graphAvgPerPeriod">0</h3>
          <p id="graphAvgLabel">Avg per Hour</p>
        </div>
        <div class="stat-card-small green">
          <h3 id="graphPeakValue">0</h3>
          <p id="graphPeakLabel">Peak Hour</p>
        </div>
      </div>
      
      <div class="chart-container">
        <canvas id="analyticsChart"></canvas>
      </div>
      
      <div id="loadingChartIndicator" class="loading-chart">
        <div class="spinner"></div>
        <span>Loading data...</span>
      </div>
    </div>

    <!-- Request Management Section -->
    <div class="boxed">
      <h1>Request Management</h1>
      <p>Manage and track all health request from citizens</p>

      <div class="table-container">
        <div class="search-bar">
          <i class="fa fa-search"></i>
          <input type="text" id="searchInput" placeholder="Search by ID, name, or request type...">
        </div>

        <!-- Tabs -->
        <div class="tabs">
          <button class="tab active" data-status="all">All</button>
          <button class="tab" data-status="review">Pending</button>
          <button class="tab" data-status="progress">In Progress</button>
          <button class="tab" data-status="ready">Ready</button>
          <button class="tab" data-status="done">Completed Report</button>
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Type</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="requestTableBody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
  // Keep track of selected tab
  let currentFilter = "all";

  // Fetch requests from server API
  async function fetchRequests() {
    try {
      const res = await fetch('api_get_requests.php', {cache: 'no-store'});
      if (!res.ok) {
        console.error('Failed to fetch requests', res.status);
        return [];
      }
      const data = await res.json();
      return Array.isArray(data) ? data : [];
    } catch (err) {
      console.error('Error fetching requests', err);
      return [];
    }
  }

  // Load, filter and render requests
  async function loadRequests() {
    const tableBody = document.getElementById("requestTableBody");
    const requests = await fetchRequests();
    const searchInput = document.getElementById("searchInput")?.value.toLowerCase() || "";

    // Apply search filter
    let filteredRequests = requests.filter(r =>
      ('' + (r.id || '')).toLowerCase().includes(searchInput) ||
      (r.name || '').toLowerCase().includes(searchInput) ||
      (r.type || '').toLowerCase().includes(searchInput)
    );

    // Apply tab (status) filter
    if (currentFilter !== "all") {
      filteredRequests = filteredRequests.filter(r => {
        const status = (r.status || '').toLowerCase();
        if (currentFilter === "review") return status === "under review" || status === "review" || status === "pending";
        if (currentFilter === "progress") return status === "in progress" || status === "progress";
        if (currentFilter === "ready") return status === "ready";
        if (currentFilter === "done") return status === "completed" || status === "done";
        return true;
      });
    }

    // Populate table
    tableBody.innerHTML = "";
    if (filteredRequests.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="7" class="center">No matching requests</td></tr>`;
    } else {
      filteredRequests.forEach(r => {
        const priority = (r.priority || 'Medium').toLowerCase();
        const priorityClass =
          priority === "low" ? "priority-low" : priority === "medium" ? "priority-medium" : "priority-high";

        const st = (r.status || '').toLowerCase();
        const statusClass = st === "under review" ? "status-under-review" : st === "in progress" ? "status-in-progress" : st === "ready" ? "status-ready" : "status-completed";

        tableBody.innerHTML += `
          <tr>
            <td>${r.ticket_id || r.id}</td>
            <td>${r.name}</td>
            <td>${r.type}</td>
            <td><span class="${priorityClass}">${r.priority || 'Medium'}</span></td>
            <td><span class="${statusClass}">${r.status || 'New'}</span></td>
            <td>${r.submitted}</td>
            <td class="actions">
              <a href="ReqDet&Upd.php?ticket_id=${encodeURIComponent(r.ticket_id || r.id)}"><i class="fa fa-eye"></i></a>
              <a href="UpdReqAdmin.php?ticket_id=${encodeURIComponent(r.ticket_id || r.id)}"><i class="fa fa-edit"></i></a>
            </td>
          </tr>`;
      });
    }
    // Update analytics dashboard based on full requests set
    updateDashboard(requests);
  }

  // Dashboard analytics update
  function updateDashboard(reqs) {
    const counts = {
      total: reqs.length,
      review: reqs.filter(r => r.status.toLowerCase() === "under review" || r.status.toLowerCase() === "pending").length,
      progress: reqs.filter(r => r.status.toLowerCase() === "in progress").length,
      ready: reqs.filter(r => r.status.toLowerCase() === "ready").length,
      completed: reqs.filter(r => r.status.toLowerCase() === "completed").length,
    };
    document.getElementById("totalCount").textContent = counts.total;
    document.getElementById("reviewCount").textContent = counts.review;
    document.getElementById("progressCount").textContent = counts.progress;
    document.getElementById("readyCount").textContent = counts.ready;
    document.getElementById("completedCount").textContent = counts.completed;
  }

  // Tabs – filter table when clicked
  document.querySelectorAll(".tabs .tab").forEach(tab => {
    tab.addEventListener("click", () => {
      document.querySelectorAll(".tabs .tab").forEach(t => t.classList.remove("active"));
      tab.classList.add("active");
      currentFilter = tab.dataset.status;
      loadRequests();
    });
  });

  // Search listener
  document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("searchInput");
    if (searchInput) searchInput.addEventListener("input", loadRequests);
    loadRequests();
  });

  // Optional: Auto-refresh table
  setInterval(loadRequests, 30000);

  // Request and Logout handlers
  (function() {
    const requestTabEl = document.getElementById('requestTab');
    if (requestTabEl) {
      requestTabEl.addEventListener('click', (e) => {
        window.location.href = 'ReqDet&Upd.php';
      });
    }
    const logoutTabEl = document.getElementById('logoutTab');
    if (logoutTabEl) {
      logoutTabEl.addEventListener('click', (e) => {
        window.location.href = 'sign-in.php';
      });
    }
  })();

  // ===== ANALYTICS CHART FUNCTIONALITY =====
  let analyticsChart = null;
  let currentTimeframe = 'day';
  let autoRefreshInterval = null;

  // Initialize chart
  function initAnalyticsChart() {
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    
    analyticsChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [],
        datasets: [{
          label: 'Requests',
          data: [],
          borderColor: '#2E5DFC',
          backgroundColor: 'rgba(46, 93, 252, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointBackgroundColor: '#2E5DFC',
          pointBorderColor: '#fff',
          pointBorderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: {
              size: 14,
              family: 'Poppins'
            },
            bodyFont: {
              size: 13,
              family: 'Poppins'
            },
            displayColors: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              font: {
                family: 'Poppins',
                size: 11
              }
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            ticks: {
              font: {
                family: 'Poppins',
                size: 10
              },
              maxRotation: 45,
              minRotation: 45
            },
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  // Fetch analytics data
  async function fetchAnalytics(timeframe) {
    try {
      const response = await fetch(`api_get_analytics.php?timeframe=${timeframe}`, {
        cache: 'no-store'
      });
      
      if (!response.ok) {
        throw new Error('Failed to fetch analytics');
      }
      
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error fetching analytics:', error);
      return [];
    }
  }

  // Update chart with new data
  async function updateAnalyticsChart(timeframe) {
    document.getElementById('loadingChartIndicator').style.display = 'flex';
    document.querySelector('.chart-container canvas').style.opacity = '0.3';
    
    const data = await fetchAnalytics(timeframe);
    
    if (analyticsChart && data.length > 0) {
      analyticsChart.data.labels = data.map(d => d.label);
      analyticsChart.data.datasets[0].data = data.map(d => d.value);
      analyticsChart.update('none');
      
      // Update stats
      updateAnalyticsStats(data, timeframe);
    }
    
    document.getElementById('loadingChartIndicator').style.display = 'none';
    document.querySelector('.chart-container canvas').style.opacity = '1';
  }

  // Update summary statistics
  function updateAnalyticsStats(data, timeframe) {
    const total = data.reduce((sum, item) => sum + item.value, 0);
    const avg = data.length > 0 ? (total / data.length).toFixed(1) : 0;
    const peak = Math.max(...data.map(d => d.value));
    const peakIndex = data.findIndex(d => d.value === peak);
    const peakLabel = peakIndex >= 0 ? data[peakIndex].label : '-';
    
    document.getElementById('graphTotalRequests').textContent = total;
    document.getElementById('graphAvgPerPeriod').textContent = avg;
    document.getElementById('graphPeakValue').textContent = peak;
    
    // Update labels based on timeframe
    if (timeframe === 'day') {
      document.getElementById('graphAvgLabel').textContent = 'Avg per Hour';
      document.getElementById('graphPeakLabel').textContent = `Peak: ${peakLabel}`;
    } else if (timeframe === 'week') {
      document.getElementById('graphAvgLabel').textContent = 'Avg per Day';
      document.getElementById('graphPeakLabel').textContent = `Peak: ${peakLabel}`;
    } else {
      document.getElementById('graphAvgLabel').textContent = 'Avg per Day';
      document.getElementById('graphPeakLabel').textContent = `Peak Day`;
    }
  }

  // Setup timeframe buttons
  document.querySelectorAll('.timeframe-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.timeframe-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentTimeframe = btn.dataset.timeframe;
      updateAnalyticsChart(currentTimeframe);
    });
  });

  // Initialize analytics chart on page load
  document.addEventListener('DOMContentLoaded', () => {
    initAnalyticsChart();
    updateAnalyticsChart(currentTimeframe);
    
    // Auto-refresh chart every 30 seconds
    autoRefreshInterval = setInterval(() => {
      updateAnalyticsChart(currentTimeframe);
    }, 30000);
  });

  // Cleanup on page unload
  window.addEventListener('beforeunload', () => {
    if (autoRefreshInterval) {
      clearInterval(autoRefreshInterval);
    }
  });
  </script>
</body>
</html>