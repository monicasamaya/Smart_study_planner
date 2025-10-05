<?php
require_once 'functions.php';
require_login();
$user = current_user();
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['action']) && $_POST['action'] === 'add_category' && !empty($_POST['new_category'])) {
        $name = trim($_POST['new_category']);
        $color = $_POST['new_category_color'] ?? '#74b9ff';
        $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, color) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $name, $color]);
        header('Location: dashboard.php');
        exit;
    }
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : NULL;
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : NULL;
    $priority = $_POST['priority'] ?? 'medium';
    if ($title) {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, category_id, title, description, due_date, priority) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $category_id, $title, $description, $due_date, $priority]);
        $task_id = $pdo->lastInsertId();
  
        $stmt = $pdo->prepare("INSERT INTO task_history (task_id, user_id, action) VALUES (?, ?, 'created')");
        $stmt->execute([$task_id, $user_id]);
    }
}
header('Location: dashboard.php');
exit;
