<?php
require_once 'functions.php';
require_login();
$user = current_user();
$user_id = $user['id'];

header('Content-Type: application/json');

// Debug: Log the request
error_log("=== TASK UPDATE REQUEST ===");
error_log("User ID: $user_id");
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = intval($_POST['task_id'] ?? 0);
    
    error_log("Task ID: $task_id");
    
    if (!$task_id) {
        echo json_encode(['error' => 'Invalid task ID', 'debug_user' => $user_id]);
        exit;
    }
    
    // Check current task status
    $stmt = $pdo->prepare("SELECT id, title, is_done FROM tasks WHERE id=? AND user_id=?");
    $stmt->execute([$task_id, $user_id]);
    $task = $stmt->fetch();
    
    error_log("Current task: " . print_r($task, true));
    
    if (!$task) {
        echo json_encode(['error' => 'Task not found or not owned by user']);
        exit;
    }
    
    $new_status = $task['is_done'] ? 0 : 1;
    error_log("Changing is_done from {$task['is_done']} to $new_status");
    
    $completed_at = $new_status ? date('Y-m-d H:i:s') : null;

    // Update task
    $stmt = $pdo->prepare("UPDATE tasks SET is_done=?, completed_at=? WHERE id=? AND user_id=?");
    $update_result = $stmt->execute([$new_status, $completed_at, $task_id, $user_id]);
    
    error_log("Update result: " . ($update_result ? 'success' : 'failed'));
    
    // Verify the update
    $stmt = $pdo->prepare("SELECT is_done FROM tasks WHERE id=?");
    $stmt->execute([$task_id]);
    $verified_task = $stmt->fetch();
    error_log("Verified task status: " . $verified_task['is_done']);

    // Update task history
    if ($new_status) {
        $stmt = $pdo->prepare("INSERT INTO task_history (task_id, user_id, action, action_at) VALUES (?, ?, 'completed', ?)");
        $history_result = $stmt->execute([$task_id, $user_id, $completed_at]);
        error_log("History insert result: " . ($history_result ? 'success' : 'failed'));
    } else {
        $stmt = $pdo->prepare("DELETE FROM task_history WHERE task_id=? AND user_id=? AND action='completed' ORDER BY action_at DESC LIMIT 1");
        $history_result = $stmt->execute([$task_id, $user_id]);
        error_log("History delete result: " . ($history_result ? 'success' : 'failed'));
    }

    // Recalculate weekly consistency score
    $score = weekly_consistency_score($user_id);
    error_log("Consistency score: $score");

    // Get completion stats
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=? AND is_done=1");
    $stmt->execute([$user_id]);
    $completed = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id=?");
    $stmt->execute([$user_id]);
    $total = $stmt->fetchColumn();

    $progress = $total ? round(($completed/$total)*100) : 0;
    error_log("Completed: $completed, Total: $total, Progress: $progress");

    // Get last 7 days completion data for chart
    $stmt = $pdo->prepare("
        SELECT DATE(action_at) AS d, COUNT(*) AS c
        FROM task_history
        WHERE user_id=? AND action='completed' AND action_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(action_at)
    ");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll();
    
    error_log("Raw history data: " . print_r($rows, true));
    
    $completed_map = [];
    foreach ($rows as $r) {
        $completed_map[$r['d']] = (int)$r['c'];
    }

    $values = [];
    $labels = [];
    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-$i days"));
        $values[] = $completed_map[$day] ?? 0;
        $labels[] = date('D', strtotime($day));
    }

    error_log("Chart values: " . print_r($values, true));
    error_log("Chart labels: " . print_r($labels, true));

    $response = [
        'completed' => $completed,
        'total' => $total,
        'progress' => $progress,
        'consistency_score' => $score,
        'history' => $values,
        'labels' => $labels,
        'success' => true,
        'debug' => [
            'task_id' => $task_id,
            'old_status' => $task['is_done'],
            'new_status' => $new_status,
            'user_id' => $user_id
        ]
    ];
    
    error_log("Final response: " . print_r($response, true));
    
    echo json_encode($response);
    exit;
} else {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}