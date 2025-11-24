<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../database/db_connect.php';
$fullname = $_SESSION['fullname'] ?? 'Admin';

// --- SUMMARY DATA ---
$totalClients = $conn->query("SELECT COUNT(*) AS total FROM clients")->fetch_assoc()['total'] ?? 0;
$totalWorkers = $conn->query("SELECT COUNT(*) AS total FROM workers")->fetch_assoc()['total'] ?? 0;
$totalDeployments = $conn->query("SELECT COUNT(*) AS total FROM deployments")->fetch_assoc()['total'] ?? 0;
$totalPayroll = $conn->query("SELECT SUM(amount) AS total FROM payroll")->fetch_assoc()['total'] ?? 0;

// --- ADDITIONAL DATA ---
$pendingRequests = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE status='pending'")->fetch_assoc()['total'] ?? 0;
$approvedRequests = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE status='approved'")->fetch_assoc()['total'] ?? 0;
$rejectedRequests = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE status='rejected'")->fetch_assoc()['total'] ?? 0;
$activeWorkers = $conn->query("SELECT COUNT(*) AS total FROM workers WHERE status='active'")->fetch_assoc()['total'] ?? 0;
$inactiveWorkers = $conn->query("SELECT COUNT(*) AS total FROM workers WHERE status='inactive'")->fetch_assoc()['total'] ?? 0;
$newClients = $conn->query("SELECT COUNT(*) AS total FROM clients WHERE DATE(created_at) >= CURDATE() - INTERVAL 30 DAY")->fetch_assoc()['total'] ?? 0;
$recentRequests = $conn->query("SELECT * FROM requests ORDER BY date_requested DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body {
      background: #f4f6f9;
      overflow-x: hidden;
    }
    .nav-link:hover {
      background-color: #ffc10733;
      border-radius: 5px;
      color: #000 !important;
    }
    .card {
      border-radius: 10px;
      padding: 1.5rem;  /* increased padding for bigger card content spacing */
      min-height: 150px; /* ensure bigger card height */
    }
    .shadow-sm {
      box-shadow: 0 4px 10px rgba(0,0,0,0.08) !important;
    }
    /* Make card bodies flex containers to center content vertically */
    .card-body {
      display: flex;
      flex-direction: column;
      justify-content: center;
      height: 100%;
      text-align: center;
    }
    /* Optional: Remove default padding from row to stretch cards fully */
    .row {
      margin-left: 0;
      margin-right: 0;
    }
  </style>
</head>
<body>

<div class="d-flex">

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <div class="flex-grow-1 w-100">

    <!-- HEADER -->
    <div class="px-4 pt-4">
      <h3 class="mb-1 text-primary">Admin Dashboard</h3>
      <p class="mb-2">Welcome, <b><?= htmlspecialchars($fullname); ?></b> 👋</p>
      <hr>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="row g-4 px-4 mb-4"> <!-- increased gap to g-4 for more spacing -->
      <div class="col-12 col-md-4"> <!-- changed to col-md-4 for bigger cards -->
        <div class="card text-white bg-primary border-0 shadow-sm h-100">
          <div class="card-body"><h6>Total Clients</h6><h2><?= $totalClients ?></h2></div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card text-white bg-success border-0 shadow-sm h-100">
          <div class="card-body"><h6>Total Workers</h6><h2><?= $totalWorkers ?></h2></div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card text-white bg-warning border-0 shadow-sm h-100">
          <div class="card-body"><h6>Ongoing Deployments</h6><h2><?= $totalDeployments ?></h2></div>
        </div>
      </div>

      <!-- Payroll card same column size, appears next row if needed -->
      <div class="col-12 col-md-4">
        <div class="card text-white bg-danger border-0 shadow-sm h-100">
          <div class="card-body"><h6>Payroll Total</h6><h2>₱<?= number_format($totalPayroll, 2) ?></h2></div>
        </div>
      </div>
    </div>

    <!-- METRICS -->
    <div class="row g-3 px-4 mb-4">
      <div class="col-12 col-md-3">
        <div class="card bg-light shadow-sm h-100">
          <div class="card-body"><h6 class="text-muted">Pending Requests</h6><h4><?= $pendingRequests ?></h4></div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="card bg-light shadow-sm h-100">
          <div class="card-body"><h6 class="text-muted">Approved Requests</h6><h4><?= $approvedRequests ?></h4></div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="card bg-light shadow-sm h-100">
          <div class="card-body"><h6 class="text-muted">Rejected Requests</h6><h4><?= $rejectedRequests ?></h4></div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="card bg-light shadow-sm h-100">
          <div class="card-body"><h6 class="text-muted">New Clients (30 Days)</h6><h4><?= $newClients ?></h4></div>
        </div>
      </div>
    </div>

    <!-- WORKER STATS -->
    <div class="row g-3 px-4 mb-4">
      <div class="col-12 col-md-6">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5>Active Workers</h5>
            <h2 class="text-success"><?= $activeWorkers ?></h2>
            <p class="text-muted">Currently deployed or working.</p>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <h5>Inactive Workers</h5>
            <h2 class="text-danger"><?= $inactiveWorkers ?></h2>
            <p class="text-muted">Waiting for assignment or left the company.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- RECENT REQUESTS TABLE -->
    <div class="px-4">
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
          <h5 class="mb-0">Recent Manpower Requests</h5>
        </div>
        <div class="card-body p-0">
          <table class="table table-striped mb-0">
            <thead class="table-primary">
              <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Position</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($recentRequests->num_rows > 0): ?>
                <?php while ($row = $recentRequests->fetch_assoc()): ?>
                  <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                    <td><?= htmlspecialchars($row['position']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><span class="badge bg-secondary"><?= ucfirst($row['status']) ?></span></td>
                    <td><?= date('M d, Y', strtotime($row['date_requested'])) ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="6" class="text-center py-3 text-muted">No requests found</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- CHART -->
    <div class="px-4">
      <div class="card shadow-sm mb-5">
        <div class="card-header bg-light">
          <h5>Worker Distribution Chart</h5>
        </div>
        <div class="card-body">
          <canvas id="workerChart" height="120"></canvas>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
new Chart(document.getElementById('workerChart'), {
  type: 'pie',
  data: {
    labels: ['Active Workers', 'Inactive Workers'],
    datasets: [{
      data: [<?= $activeWorkers ?>, <?= $inactiveWorkers ?>],
      backgroundColor: ['#28a745', '#dc3545']
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'bottom' } }
  }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
