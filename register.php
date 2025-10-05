<?php
require_once 'config.php';
if (!empty($_SESSION['user_id'])) { 
    header('Location: dashboard.php'); 
    exit; 
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$name || !$email || !$password) {
        $errors[] = "Fill all required fields.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password too short.";
    }

    if (empty($errors)) {
        // check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already registered.";
        } else {
            $pw = password_hash($password, PASSWORD_DEFAULT); //bcrypt algo
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $pw]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Register - Smart Productivity</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 card p-4 shadow">
      <h3>Create account</h3>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
        </div>
      <?php endif; ?>
      <form method="post" novalidate>
        <div class="mb-3">
          <input class="form-control" name="name" placeholder="Full name" required>
        </div>
        <div class="mb-3">
          <input class="form-control" name="email" type="email" placeholder="Email" required>
        </div>
        <div class="mb-3">
          <input class="form-control" name="password" type="password" placeholder="Password" required>
        </div>
        <div class="mb-3">
          <input class="form-control" name="confirm" type="password" placeholder="Confirm password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Create account</button>
      </form>
      <hr>
      <div class="text-center"><a href="login.php">Already have an account? Login</a></div>
    </div>
  </div>
</div>
</body>
</html>
