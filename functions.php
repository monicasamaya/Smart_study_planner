<?php
// functions.php (include after config)
require_once 'config.php';

function current_user() {
    global $pdo;
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// calculate weekly consistency score - FIXED VERSION
function weekly_consistency_score($user_id) {
    global $pdo;
    
    // Get the last 7 days including today
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT DATE(action_at)) as days_count
        FROM task_history
        WHERE user_id = ? 
          AND action = 'completed' 
          AND action_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    $days_with_completion = $result['days_count'] ?? 0;
    
    // Debug: Let's see what's happening
    error_log("Consistency Debug - User $user_id: $days_with_completion days with completion out of 7 days");
    
    // Calculate percentage
    $score = round(($days_with_completion / 7) * 100);
    
    return $score;
}
?>