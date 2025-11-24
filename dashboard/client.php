<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../database/db_connect.php';

// --- MANAGE CLIENTS FUNCTION ---
function manageClients($conn) {

    // ADD CLIENT
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_client'])) {
        $company_name   = $conn->real_escape_string($_POST['company_name'] ?? '');
        $contact_person = $conn->real_escape_string($_POST['contact_person'] ?? '');
        $contact_number = $conn->real_escape_string($_POST['contact_number'] ?? '');
        $email          = $conn->real_escape_string($_POST['email'] ?? '');
        $address        = $conn->real_escape_string($_POST['address'] ?? '');
        $industry       = $conn->real_escape_string($_POST['industry'] ?? '');
        $status         = $conn->real_escape_string($_POST['status'] ?? '');

        $conn->query("
            INSERT INTO clients (company_name, contact_person, contact_number, email, address, industry, status, created_at)
            VALUES ('$company_name', '$contact_person', '$contact_number', '$email', '$address', '$industry', '$status', NOW())
        ");
    }

    // UPDATE CLIENT
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_client'])) {
        $id             = (int)($_POST['client_id'] ?? 0);
        $company_name   = $conn->real_escape_string($_POST['company_name'] ?? '');
        $contact_person = $conn->real_escape_string($_POST['contact_person'] ?? '');
        $contact_number = $conn->real_escape_string($_POST['contact_number'] ?? '');
        $email          = $conn->real_escape_string($_POST['email'] ?? '');
        $address        = $conn->real_escape_string($_POST['address'] ?? '');
        $industry       = $conn->real_escape_string($_POST['industry'] ?? '');
        $status         = $conn->real_escape_string($_POST['status'] ?? '');

        $conn->query("
            UPDATE clients
            SET company_name='$company_name',
                contact_person='$contact_person',
                contact_number='$contact_number',
                email='$email',
                address='$address',
                industry='$industry',
                status='$status'
            WHERE client_id=$id
        ");
    }

    // DELETE CLIENT
    if (isset($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $conn->query("DELETE FROM clients WHERE client_id=$id");
    }

    return $conn->query("SELECT * FROM clients ORDER BY created_at DESC");
}

$clients = manageClients($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clients Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .card { border-radius: 10px; }
        .main-content { padding: 1.5rem; }
    </style>
</head>
<body>
<div class="d-flex">

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-grow-1 main-content">

        <h3 class="mb-4 text-primary">Clients Management</h3>

        <!-- Add Client Form -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header">Add New Client</div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <input type="hidden" name="add_client" value="1">

                    <div class="col-md-4">
                        <input type="text" name="company_name" class="form-control form-control-lg" placeholder="Company Name" required>
                    </div>

                    <div class="col-md-4">
                        <input type="text" name="contact_person" class="form-control form-control-lg" placeholder="Contact Person" required>
                    </div>

                    <div class="col-md-4">
                        <input type="text" name="contact_number" class="form-control form-control-lg" placeholder="Contact Number" required>
                    </div>

                    <div class="col-md-4">
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Email Address" required>
                    </div>

                    <div class="col-md-4">
                        <input type="text" name="industry" class="form-control form-control-lg" placeholder="Industry" required>
                    </div>

                    <div class="col-md-4">
                        <select name="status" class="form-select form-select-lg">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <textarea name="address" class="form-control form-control-lg" placeholder="Company Address" rows="2" required></textarea>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary btn-lg px-4">Add Client</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Clients List Table -->
        <div class="card shadow-sm">
            <div class="card-header">Clients List</div>
            <div class="table-responsive p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Contact Person</th>
                            <th>Number</th>
                            <th>Email</th>
                            <th>Industry</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php while ($c = $clients->fetch_assoc()): ?>
                        <tr>
                            <td><?= $c['client_id'] ?></td>
                            <td><?= htmlspecialchars($c['company_name']) ?></td>
                            <td><?= htmlspecialchars($c['contact_person']) ?></td>
                            <td><?= htmlspecialchars($c['contact_number']) ?></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['industry']) ?></td>
                            <td>
                                <span class="badge bg-<?= $c['status'] == 'Active' ? 'success' : 'secondary' ?>">
                                    <?= $c['status'] ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                            <td>
                                <a href="clients_edit.php?id=<?= $c['client_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="?delete=<?= $c['client_id'] ?>" onclick="return confirm('Delete this client?');" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    <?php if ($clients->num_rows === 0): ?>
                        <tr><td colspan="9" class="text-center py-3">No clients found.</td></tr>
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
