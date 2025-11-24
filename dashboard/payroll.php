<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../database/db_connect.php';

function managePayroll($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payroll'])) {
        $worker = $conn->real_escape_string($_POST['worker']);
        $amount = $conn->real_escape_string($_POST['amount']);
        $pay_date = $conn->real_escape_string($_POST['pay_date']);
        $status = $conn->real_escape_string($_POST['status']);
        $conn->query("INSERT INTO payroll (worker, amount, pay_date, status, created_at) VALUES ('$worker', '$amount', '$pay_date', '$status', NOW())");
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payroll'])) {
        $id = (int)$_POST['id'];
        $worker = $conn->real_escape_string($_POST['worker']);
        $amount = $conn->real_escape_string($_POST['amount']);
        $pay_date = $conn->real_escape_string($_POST['pay_date']);
        $status = $conn->real_escape_string($_POST['status']);
        $conn->query("UPDATE payroll SET worker='$worker', amount='$amount', pay_date='$pay_date', status='$status' WHERE id=$id");
    }
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $conn->query("DELETE FROM payroll WHERE id=$id");
    }
    return $conn->query("SELECT * FROM payroll ORDER BY created_at DESC");
}

$payrolls = managePayroll($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Payroll Management</title>
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
        <h3 class="mb-4 text-primary">Payroll Management</h3>
        <div class="card mb-4 shadow-sm w-100">
            <div class="card-header">Add Payroll</div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <input type="hidden" name="add_payroll" value="1" />
                    <div class="col-md-3"><input type="text" name="worker" class="form-control" placeholder="Worker" required /></div>
                    <div class="col-md-3"><input type="number" name="amount" class="form-control" placeholder="Amount" required /></div>
                    <div class="col-md-3"><input type="date" name="pay_date" class="form-control" required /></div>
                    <div class="col-md-3">
                        <select name="status" class="form-select" required>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-danger">Add Payroll</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card shadow-sm w-100">
            <div class="card-header">Payroll List</div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-danger">
                        <tr>
                            <th>ID</th>
                            <th>Worker</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $payrolls->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['worker']) ?></td>
                                <td>₱<?= number_format($row['amount'], 2) ?></td>
                                <td><?= htmlspecialchars($row['pay_date']) ?></td>
                                <td>
                                    <span class="badge <?= $row['status'] === 'paid' ? 'bg-danger' : 'bg-secondary' ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="payroll_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this payroll?');" class="btn btn-sm btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($payrolls->num_rows === 0): ?>
                            <tr><td colspan="6" class="text-center py-3 text-muted">No payroll records found.</td></tr>
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
