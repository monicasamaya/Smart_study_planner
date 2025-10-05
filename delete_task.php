<?php 
require_once 'functions.php'; 
require_login(); 

$user = current_user(); 
$user_id = $user['id']; 

$id = intval($_GET['id'] ?? 0); 

if ($id) {
    // log the deletion BEFORE deleting the task
    $stmt = $pdo->prepare("
        INSERT INTO task_history (task_id, user_id, action) 
        VALUES (?, ?, 'deleted')
    ");
    $stmt->execute([$id, $user_id]);
    $stmt = $pdo->prepare("                         
        DELETE FROM tasks 
        WHERE id = ? AND user_id = ?
    ");            //delete the task
    $stmt->execute([$id, $user_id]);
}

header('Location: dashboard.php');
exit;
?>
