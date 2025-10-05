<?php
require_once 'functions.php';
require_login();
$user = current_user();
$user_id = $user['id'];

echo "<h2>Debug Task Status</h2>";
echo "<p>User ID: $user_id</p>";

// Check tasks
$stmt = $pdo->prepare("SELECT id, title, is_done FROM tasks WHERE user_id = ?");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll();

echo "<h3>Current Tasks:</h3>";
foreach ($tasks as $task) {
    echo "<p>Task {$task['id']}: {$task['title']} - is_done: {$task['is_done']}</p>";
}

// Check task history
$stmt = $pdo->prepare("SELECT task_id, action, action_at FROM task_history WHERE user_id = ? ORDER BY action_at DESC");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll();

echo "<h3>Task History:</h3>";
foreach ($history as $h) {
    echo "<p>Task {$h['task_id']}: {$h['action']} at {$h['action_at']}</p>";
}
?>
<a href="dashboard.php">Back to Dashboard</a>