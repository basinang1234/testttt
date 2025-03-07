<!-- create_forum.php -->
<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    try {
        $stmt = $pdo->prepare('INSERT INTO forums (name, description, user_id) VALUES (?, ?, ?)');
        $stmt->execute([$name, $description, $_SESSION['user_id']]);
        header('Location: forums.php');
        exit;
    } catch (PDOException $e) {
        error_log('Forum creation error: ' . $e->getMessage());
        echo 'System error occurred';
    }
}
?>