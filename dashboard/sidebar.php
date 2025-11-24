<?php
// sidebar.php


$fullname = $_SESSION['fullname'] ?? 'Unknown User';
$role = $_SESSION['role'] ?? 'guest';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="d-flex">
  <div class="bg-dark text-white vh-100 d-flex flex-column justify-content-between p-3" style="width: 250px;">
    <h4 class="text-center mb-4 text-warning">Manpower System</h4>

    <ul class="nav nav-pills flex-column mb-4">
      <?php if($role === 'admin'): ?>
        <li class="nav-item mb-1"><a class="nav-link text-white" href="admin_dashboard.php">🏠 Dashboard</a></li>
        <li class="nav-item mb-1"><a class="nav-link text-white" href="client.php">👥 Clients</a></li>
        <li class="nav-item mb-1"><a class="nav-link text-white" href="workers.php">🧰 Workers</a></li>
        <li class="nav-item mb-1"><a class="nav-link text-white" href="deployment.php">📦 Deployments</a></li>
        <li class="nav-item mb-1"><a class="nav-link text-white" href="payroll.php">💰 Payroll</a></li>
        <li class="nav-item mb-1"><a class="nav-link text-white" href="reports.php">📊 Reports/Analytics</a></a></li>
      
      <?php elseif($role === 'employee'): ?>
        <li class="nav-item mb-1"><a class="nav-link text-white" href="employee_dashboard.php">🏠 Dashboard</a></li>
        <li class="nav-item mb-1"><a class="nav-link text-white" href="#">📋 My Deployment</a></li>
        <li class="nav-item mb-1"><a class="nav-link text-white" href="#">⏰ Attendance</a></li>
        <li class="nav-item mb-1"><a class="nav-link text-white" href="#">💵 Payslip</a></li>
      <?php endif; ?>
    </ul>

    <div class="user-info text-center bg-secondary rounded p-3">
      <p class="mb-1 fs-6">👤 <?= htmlspecialchars($fullname) ?></p>
      <p class="small text-light">(<?= ucfirst($role) ?>)</p>
      <a href="../login.php" class="btn btn-warning btn-sm mt-2 w-100">Logout</a>
    </div>
  </div>
