<!-- view_post.php -->
<?php
require 'config.php';
require 'header.php';

$post_id = (int)$_GET['id'];

// Fetch post details
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.profile_picture,
           (SELECT SUM(value) FROM votes WHERE content_type = 'post' AND content_id = p.id) AS score
    FROM forum_posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$post_id]);
$post = $stmt->fetch();
if(!$post) {
    http_response_code(404);
    echo '<div class="alert alert-danger">Post not found</div>';
    exit;
}

// Fetch comments
$stmt = $pdo->prepare("
    SELECT c.*, u.username, u.profile_picture,
           (SELECT SUM(value) FROM votes WHERE content_type = 'comment' AND content_id = c.id) AS score
    FROM forum_comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
");
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <!-- Original Post -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex align-items-center">
                <img src="<?= $post['profile_picture'] ?: 'avatar.png' ?>" 
                     class="rounded-circle me-3" style="width: 60px; height: 60px;">
                <div>
                    <h4><?= htmlspecialchars($post['username']) ?></h4>
                    <small><?= date('M d, Y', strtotime($post['created_at'])) ?></small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <h3><?= htmlspecialchars($post['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
            <div class="d-flex gap-3 mb-3">
                <button class="btn btn-outline-primary vote-btn <?= ($post['user_vote'] ?? 0) == 1 ? 'active' : '' ?>"
                        onclick="handleVote('post', <?= $post['id'] ?>, 1)">
                    👍 <span><?= $post['score'] ?></span>
                </button>
                <button class="btn btn-outline-danger vote-btn <?= ($post['user_vote'] ?? 0) == -1 ? 'active' : '' ?>"
                        onclick="handleVote('post', <?= $post['id'] ?>, -1)">
                    👎 <span><?= $post['score'] ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Comment Section -->
    <div class="card mb-4">
        <div class="card-body">
            <h4>Comments</h4>
            
            <!-- Comment Form -->
            <form id="commentForm" class="mb-4">
                <input type="hidden" name="post_id" value="<?= $post_id ?>">
                <div class="mb-3">
                    <textarea class="form-control" name="content" rows="3" placeholder="Add comment..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Comment</button>
            </form>

            <!-- Existing Comments -->
            <?php foreach($comments as $comment): ?>
                <div class="card mb-3 comment-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <img src="<?= $comment['profile_picture'] ?: 'avatar.png' ?>" 
                                 class="rounded-circle me-3" style="width: 40px; height: 40px;">
                            <div>
                                <h6><?= htmlspecialchars($comment['username']) ?></h6>
                                <small><?= date('M d, Y', strtotime($comment['created_at'])) ?></small>
                            </div>
                        </div>
                        <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                        <div class="d-flex gap-3">
                            <button class="btn btn-sm btn-outline-primary vote-btn <?= ($comment['user_vote'] ?? 0) == 1 ? 'active' : '' ?>"
                                    onclick="handleVote('comment', <?= $comment['id'] ?>, 1)">
                                👍 <span><?= $comment['score'] ?></span>
                            </button>
                            <button class="btn btn-sm btn-outline-danger vote-btn <?= ($comment['user_vote'] ?? 0) == -1 ? 'active' : '' ?>"
                                    onclick="handleVote('comment', <?= $comment['id'] ?>, -1)">
                                👎 <span><?= $comment['score'] ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>