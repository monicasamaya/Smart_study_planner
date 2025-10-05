<?php
require_once 'config.php';
if (!empty($_SESSION['user_id'])) {  //if the user is signed in goes to dashboard prvent from reaching again to login page
  header('Location: dashboard.php'); 
  exit; 
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); //if input not given subs null
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) $err = "Fill both fields.";
    else {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) { //check password is created
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit;
        } else $err = "Invalid credentials.";
    }
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login - Smart Productivity</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5 card p-4 shadow">
      <h3>Sign in</h3>
      <?php if ($err): ?><div class="alert alert-danger"><?=htmlspecialchars($err)?></div><?php endif; ?>
      <form method="post" novalidate>
        <div class="mb-3"><input class="form-control" name="email" type="email" placeholder="Email" required></div>
        <div class="mb-3"><input class="form-control" name="password" type="password" placeholder="Password" required></div>
        <button class="btn btn-primary w-100" type="submit">Sign in</button>
      </form>
      <hr>
      <div class="text-center"><a href="register.php">Create an account</a></div>
    </div>
  </div>
</div>
</body>
</html>
