<!-- forum.php -->
<?php
require 'config.php';
require 'header.php';

$forum_id = (int)$_GET['id'];
$page = (int)($_GET['page'] ?? 1);
$perPage = 5;
$offset = ($page - 1) * $perPage;

// Check forum exists
$stmt = $pdo->prepare('SELECT * FROM forums WHERE id = ?');
$stmt->execute([$forum_id]);
$forum = $stmt->fetch();
if (!$forum) {
    http_response_code(404);
    echo 'Forum not found';
    exit;
}

// Get posts
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.profile_picture,
           (SELECT SUM(value) FROM votes WHERE content_type = 'post' AND content_id = p.id) AS score
    FROM forum_posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.forum_id = ?
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$forum_id, $perPage, $offset]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total posts for pagination
$totalStmt = $pdo->prepare('SELECT COUNT(*) FROM forum_posts WHERE forum_id = ?');
$totalStmt->execute([$forum_id]);
$totalPosts = $totalStmt->fetchColumn();
$totalPages = ceil($totalPosts / $perPage);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= htmlspecialchars($forum['name']) ?></h2>
        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                New Thread <i class="fas fa-plus"></i>
            </button>
        <?php endif; ?>
    </div>

    <!-- Post List -->
    <?php foreach ($posts as $post): ?>
        <div class="card mb-3 post-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?= $post['profile_picture'] ?: 'avatar.png' ?>" 
                         class="rounded-circle me-3" style="width: 50px; height: 50px;">
                    <div>
                        <h5><?= htmlspecialchars($post['username']) ?></h5>
                        <small><?= date('M d, Y', strtotime($post['created_at'])) ?></small>
                    </div>
                </div>
                <h3><?= htmlspecialchars($post['title']) ?></h3>
                <p><?= nl2br(htmlspecialchars(substr($post['content'], 0, 200))) ?>...</p>
                <div class="d-flex gap-3">
                    <button class="vote-btn btn btn-outline-primary <?= ($post['user_vote'] ?? 0) == 1 ? 'active' : '' ?>"
                            onclick="handleVote('post', <?= $post['id'] ?>, 1)">
                        👍 <span><?= $post['score'] ?></span>
                    </button>
                    <a href="view_post.php?id=<?= $post['id'] ?>" class="btn btn-outline-dark">
                        💬 <?= $post['comment_count'] ?> Comments
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="forum.php?id=<?= $forum_id ?>&page=<?= $page - 1 ?>">Previous</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="forum.php?id=<?= $forum_id ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="forum.php?id=<?= $forum_id ?>&page=<?= $page + 1 ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Create Post Modal -->
    <div class="modal fade" id="createPostModal">
        <div class="modal-dialog">
            <form method="POST" action="create_post.php" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Thread</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" name="title" class="form-control" placeholder="Title" required>
                    </div>
                    <div class="mb-3">
                        <textarea name="content" class="form-control" rows="5" placeholder="Content"></textarea>
                    </div>
                    <input type="hidden" name="forum_id" value="<?= $forum_id ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>