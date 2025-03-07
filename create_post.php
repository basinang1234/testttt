<!-- create_post.php -->
<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$forum_id = (int)$_POST['forum_id'];
$title = trim($_POST['title']);
$content = trim($_POST['content']);

if (empty($title) || empty($content)) {
    header('Location: forum.php?id=' . $forum_id . '&error=Title and content are required');
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO forum_posts (user_id, forum_id, title, content, created_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([$_SESSION['user_id'], $forum_id, $title, $content]);
    header('Location: forum.php?id=' . $forum_id);
} catch (PDOException $e) {
    error_log('Post creation error: ' . $e->getMessage());
    echo 'System error occurred';
}
?>