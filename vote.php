<!-- vote.php -->
<?php
require 'config.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$content_type = $_POST['content_type'] ?? '';
$content_id = (int)($_POST['content_id'] ?? 0);
$value = (int)($_POST['value'] ?? 0);

if(!in_array($content_type, ['post', 'comment']) || !$content_id || !in_array($value, [1, -1])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    // Upsert vote
    $stmt = $pdo->prepare('
        INSERT INTO votes (user_id, content_type, content_id, value) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE value = ?
    ');
    $stmt->execute([$_SESSION['user_id'], $content_type, $content_id, $value, $value]);

    // Get updated score
    $stmt = $pdo->prepare('
        SELECT SUM(value) AS score 
        FROM votes 
        WHERE content_type = ? AND content_id = ?
    ');
    $stmt->execute([$content_type, $content_id]);
    $score = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'score' => $score]);

} catch(PDOException $e) {
    error_log('Vote error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error']);
}
?><!-- vote.php -->
<?php
require 'config.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$content_type = $_POST['content_type'] ?? '';
$content_id = (int)($_POST['content_id'] ?? 0);
$value = (int)($_POST['value'] ?? 0);

if(!in_array($content_type, ['post', 'comment']) || !$content_id || !in_array($value, [1, -1])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    // Upsert vote
    $stmt = $pdo->prepare('
        INSERT INTO votes (user_id, content_type, content_id, value) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE value = ?
    ');
    $stmt->execute([$_SESSION['user_id'], $content_type, $content_id, $value, $value]);

    // Get updated score
    $stmt = $pdo->prepare('
        SELECT SUM(value) AS score 
        FROM votes 
        WHERE content_type = ? AND content_id = ?
    ');
    $stmt->execute([$content_type, $content_id]);
    $score = $stmt->fetchColumn();

    echo json_encode(['success' => true, 'score' => $score]);

} catch(PDOException $e) {
    error_log('Vote error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error']);
}
?>