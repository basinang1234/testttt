<!-- comment.php -->
<?php
require 'config.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$post_id = (int)$_POST['post_id'] ?? 0;
$content = trim($_POST['content'] ?? '');

if(empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO forum_comments (user_id, post_id, content, created_at)
        VALUES (?, ?, ?, NOW())
    ');
    $stmt->execute([$_SESSION['user_id'], $post_id, $content]);
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    error_log('Comment error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error']);
}
?>