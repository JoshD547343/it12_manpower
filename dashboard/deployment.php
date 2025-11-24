<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../database/db_connect.php';

function manageDeployments($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_deployment'])) {
        $client = $conn->real_escape_string($_POST['client']);
        $worker = $conn->real_escape_string($_POST['worker']);
        $position = $conn->real_escape_string($_POST['position']);
        $start = $conn->real_escape_string($_POST['start_date']);
        $end = $conn->real_escape_string($_POST['end_date']);
        $status = $conn->real_escape_string($_POST['status']);
        $conn->query("INSERT INTO deployments (client, worker, position, start_date, end_date, status, created_at) VALUES ('$client', '$worker', '$position', '$start', '$end', '$status', NOW())");
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_deployment'])) {
        $id = (int)$_POST['id'];
        $client = $conn->real_escape_string($_POST['client']);
        $worker = $conn->real_escape_string($_POST['worker']);
        $position = $conn->real_escape_string($_POST['position']);
        $start = $conn->real_escape_string($_POST['start_date']);
        $end = $conn->real_escape_string($_POST['end_date']);
        $status = $conn->real_escape_string($_POST['status']);
        $conn->query("UPDATE deployments SET client='$client', worker='$worker', position='$position', start_date='$start', end_date='$end', status='$status' WHERE id=$id");
    }
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $conn->query("DELETE FROM deployments WHERE id=$id");
    }
    return $conn->query("SELECT * FROM deployments ORDER BY created_at DESC");
}

$deployments = manageDeployments($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Deployment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background: #f4f6f9; }
        .card { border-radius: 10px; min-height: 220px; }
        .main-content { padding: 1.5rem; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>
    <div class="flex-grow-1 w-100 main-content">
        <h3 class="mb-4 text-primary">Deployment Management</h3>
        <div class="card mb-4 shadow-sm w-100">
            <div class="card-header">Add Deployment</div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <input type="hidden" name="add_deployment" value="1" />
                    <div class="col-md-2"><input type="text" name="client" class="form-control" placeholder="Client" required /></div>
                    <div class="col-md-2"><input type="text" name="worker" class="form-control" placeholder="Worker" required /></div>
                    <div class="col-md-2"><input type="text" name="position" class="form-control" placeholder="Position" required /></div>
                    <div class="col-md-2"><input type="date" name="start_date" class="form-control" required /></div>
                    <div class="col-md-2"><input type="date" name="end_date" class="form-control" required /></div>
                    <div class="col-md-2">
                        <select name="status" class="form-select" required>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Add Deployment</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card shadow-sm w-100">
            <div class="card-header">Deployment List</div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-warning">
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Worker</th>
                            <th>Position</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $deployments->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['client']) ?></td>
                                <td><?= htmlspecialchars($row['worker']) ?></td>
                                <td><?= htmlspecialchars($row['position']) ?></td>
                                <td><?= htmlspecialchars($row['start_date']) ?></td>
                                <td><?= htmlspecialchars($row['end_date']) ?></td>
                                <td>
                                    <span class="badge <?= $row['status'] === 'ongoing' ? 'bg-warning' : 'bg-success' ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="deployments_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this deployment?');" class="btn btn-sm btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($deployments->num_rows === 0): ?>
                            <tr><td colspan="8" class="text-center py-3 text-muted">No deployments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
