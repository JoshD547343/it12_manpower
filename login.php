<?php
ob_start();
session_start();
include 'database/db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {

        // Set session values
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        // Redirect by role
        if ($user['role'] === 'admin') {
            header("Location: dashboard/admin_dashboard.php");
            exit();
        } 
        else if ($user['role'] === 'employee') {
            header("Location: dashboard/employee_dashboard.php");
            exit();
        } 
        else {
            $message = "Invalid role.";
        }

    } else {
        $message = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login | Manpower System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow p-4">
        <h3 class="text-center mb-3">User Login</h3>

        <?php if($message): ?>
          <div class="alert alert-danger"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3">No account? <a href="register.php">Register</a></p>
      </div>
    </div>
  </div>
</div>
</body>
</html>
