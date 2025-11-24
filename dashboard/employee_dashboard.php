<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit();
}
?>

<?php include 'sidebar.php'; ?>

<div class="flex-grow-1 p-3">
  <h2>Employee Dashboard</h2>
  <p>Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?>!</p>

  <!-- Add employee-specific modules here -->
  <div class="row">
    <div class="col-md-6">
      <div class="card text-center mb-3">
        <div class="card-body">
          <h5 class="card-title">My Deployment</h5>
          <p class="card-text">View your assigned deployments.</p>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card text-center mb-3">
        <div class="card-body">
          <h5 class="card-title">Attendance</h5>
          <p class="card-text">Check your attendance record.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
