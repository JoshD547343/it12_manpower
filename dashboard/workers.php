<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../database/db_connect.php';

// --- Worker management functions ---
function manageWorkers($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_worker'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $position = $conn->real_escape_string($_POST['position']);
        $status = $conn->real_escape_string($_POST['status']);
        $conn->query("INSERT INTO workers (name, email, phone, position, status, created_at) VALUES ('$name', '$email', '$phone', '$position', '$status', NOW())");
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_worker'])) {
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $position = $conn->real_escape_string($_POST['position']);
        $status = $conn->real_escape_string($_POST['status']);
        $conn->query("UPDATE workers SET name='$name', email='$email', phone='$phone', position='$position', status='$status' WHERE id=$id");
    }
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $conn->query("DELETE FROM workers WHERE id=$id");
    }
    return $conn->query("SELECT * FROM workers ORDER BY created_at DESC");
}

$workers = manageWorkers($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Workers Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background: #f4f6f9; }
        .card { border-radius: 10px; min-height: 220px; }
        .card-body { display: flex; flex-direction: column; justify-content: center; height: 100%; }
        .nav-link:hover { background-color: #ffc10733; border-radius: 5px; color: #000 !important; }
        .main-content { padding: 1.5rem; }
        .btn-sm { padding: .25rem .5rem; font-size: .8rem; }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include 'sidebar.php'; ?>
    <div class="flex-grow-1 w-100 main-content">
        <h3 class="mb-4 text-primary">Workers Management</h3>

        <!-- Add Worker Card -->
        <div class="card mb-4 shadow-sm w-100">
            <div class="card-header">Add New Worker</div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <input type="hidden" name="add_worker" value="1" />
                    <div class="col-md-3">
                        <input type="text" name="name" class="form-control form-control-lg" placeholder="Name" required />
                    </div>
                    <div class="col-md-3">
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required />
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="phone" class="form-control form-control-lg" placeholder="Phone" required />
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="position" class="form-control form-control-lg" placeholder="Position" required />
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-lg" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-success btn-lg px-4">Add Worker</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Workers List Card -->
        <div class="card shadow-sm w-100">
            <div class="card-header">Workers List</div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($worker = $workers->fetch_assoc()): ?>
                            <tr>
                                <td><?= $worker['id'] ?></td>
                                <td><?= htmlspecialchars($worker['name']) ?></td>
                                <td><?= htmlspecialchars($worker['email']) ?></td>
                                <td><?= htmlspecialchars($worker['phone']) ?></td>
                                <td><?= htmlspecialchars($worker['position']) ?></td>
                                <td>
                                    <span class="badge <?= $worker['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst($worker['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($worker['created_at'])) ?></td>
                                <td>
                                    <a href="workers_edit.php?id=<?= $worker['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="?delete=<?= $worker['id'] ?>" onclick="return confirm('Delete this worker?');" class="btn btn-sm btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($workers->num_rows === 0): ?>
                            <tr><td colspan="8" class="text-center py-3 text-muted">No workers found.</td></tr>
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
