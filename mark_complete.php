<?php
require_once 'functions.php';
require_login();
$user = current_user();
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = intval($_POST['task_id']);
    // toggle
    $stmt = $pdo->prepare("SELECT is_done FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);
    $t = $stmt->fetch();
    if ($t) {
        $new = $t['is_done'] ? 0 : 1;
        $completed_at = $new ? date('Y-m-d H:i:s') : null;
        $stmt = $pdo->prepare("UPDATE tasks SET is_done = ?, completed_at = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new, $completed_at, $task_id, $user_id]);

        $action = $new ? 'completed' : 'updated';
        $stmt = $pdo->prepare("INSERT INTO task_history (task_id, user_id, action) VALUES (?, ?, ?)");
        $stmt->execute([$task_id, $user_id, $action]);
    }
}
header('Location: dashboard.php');
exit;
?>
