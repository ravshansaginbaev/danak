<?php
session_start();
$message = '';
if (isset($_SESSION['admin_toast'])) {
    $message = $_SESSION['admin_toast'];
    unset($_SESSION['admin_toast']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <h3 class="text-center">🔐 Admin Login</h3>
      <?php if ($message): ?>
        <div class="alert alert-danger"><?= $message ?></div>
      <?php endif; ?>
      <form action="admin_auth.php" method="POST">
        <label class="form-label">Username:</label>
        <input type="text" name="username" class="form-control mb-3" required>

        <label class="form-label">Password:</label>
        <input type="password" name="password" class="form-control mb-3" required>

        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
