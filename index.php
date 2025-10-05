<?php
// index.php
require_once 'config.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Smart Productivity Tracker - Welcome</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 text-center card p-4 shadow">
        <h1 class="display-6">Smart Productivity & Task Tracker</h1>
        <p class="lead">Colourful, fun, and built for consistent productivity. Sign up and start tracking!</p>
        <a href="register.php" class="btn btn-primary me-2">Create Account</a>
        <a href="login.php" class="btn btn-outline-light">Sign In</a>
        <hr>
        <p class="small text-muted">Demo account: demo@example.com / pass123</p>
      </div>
    </div>
  </div>
</body>
</html>
