<?php
//to communicate between frontend and backend ,to provide data
require_once 'functions.php';
require_login();
$user = current_user();
$user_id = $user['id'];
header('Content-Type: application/json'); //uses json

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT t.*, c.name as category_name, c.color as category_color FROM tasks t LEFT JOIN categories c ON t.category_id = c.id WHERE t.user_id = ? ORDER BY t.is_done ASC, t.due_date IS NULL, t.due_date ASC");
    $stmt->execute([$user_id]);
    echo json_encode($stmt->fetchAll()); //fetch all and creates json array
    exit;
}

echo json_encode(['error' => 'invalid action']);
