<?php
session_start();

$DB_HOST = 'localhost';          
$DB_NAME = 'productivity_db';    
$DB_USER = 'root';               
$DB_PASS = '12345';              
$db_port = 3307;                 

try {
    
    $pdo = new PDO(
        "mysql:host=$DB_HOST;port=$db_port;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

function require_login() {
    if (empty($_SESSION['user_id'])) {   // if empty require login 
        header('Location: login.php');
        exit;
    }
}
?>
