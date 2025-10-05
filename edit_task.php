<?php
require_once 'functions.php';
require_login();
$user = current_user();
$user_id = $user['id'];

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: dashboard.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$task = $stmt->fetch();
if (!$task) { header('Location: dashboard.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = ?");
$stmt->execute([$user_id]);
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : NULL;
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : NULL;
    $priority = $_POST['priority'] ?? 'medium';

    if ($title) {
        $stmt = $pdo->prepare("UPDATE tasks SET title=?, description=?, category_id=?, due_date=?, priority=? WHERE id=? AND user_id=?");
        $stmt->execute([$title, $description, $category_id, $due_date, $priority, $id, $user_id]);
        $stmt = $pdo->prepare("INSERT INTO task_history (task_id, user_id, action) VALUES (?, ?, 'updated')");
        $stmt->execute([$id, $user_id]);
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Task</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gradient">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-7 card p-4 shadow">
      <h4>Edit Task</h4>
      <form method="post">
        <div class="mb-2"><input name="title" class="form-control" value="<?=htmlspecialchars($task['title'])?>" required></div>
        <div class="mb-2"><textarea name="description" class="form-control"><?=htmlspecialchars($task['description'])?></textarea></div>
        <div class="mb-2">
          <select name="category_id" class="form-select">
            <option value="">No category</option>
            <?php foreach($categories as $c): ?>
              <option value="<?=$c['id']?>" <?= $task['category_id']==$c['id'] ? 'selected' : ''?>><?=htmlspecialchars($c['name'])?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="row g-2">
          <div class="col-6"><input name="due_date" type="date" class="form-control" value="<?=$task['due_date']?>"></div>
          <div class="col-6">
            <select name="priority" class="form-select">
              <option value="low" <?= $task['priority']=='low' ? 'selected' : ''?>>Low</option>
              <option value="medium" <?= $task['priority']=='medium' ? 'selected' : ''?>>Medium</option>
              <option value="high" <?= $task['priority']=='high' ? 'selected' : ''?>>High</option>
            </select>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary">Save</button>
          <a class="btn btn-outline-secondary" href="dashboard.php">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
