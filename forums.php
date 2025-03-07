<?php
// forums.php
require 'config.php';
require 'header.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = trim($_GET['search'] ?? '');
$perPage = 5;
$offset = ($page - 1) * $perPage;

// Search handling
$where = '';
$params = [];
if (!empty($search)) {
    $where = "WHERE name LIKE ? OR description LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Get forums
$stmt = $pdo->prepare("
    SELECT f.*, 
           (SELECT COUNT(*) FROM forum_posts WHERE forum_id = f.id) AS thread_count,
           (SELECT MAX(created_at) FROM forum_posts WHERE forum_id = f.id) AS last_activity
    FROM forums f
    $where
    ORDER BY created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$forums = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total count for pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM forums $where");
$totalStmt->execute($params);
$totalForums = $totalStmt->fetchColumn();
$totalPages = ceil($totalForums / $perPage);
?>

<!-- Forum Page Container -->
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-3 p-3">
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <!-- Search Bar -->
                    <div class="input-group w-75">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search forums..." value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-primary" onclick="searchForums()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <!-- Create Forum Button -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createForumModal">
                            <i class="fas fa-plus"></i> Create
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Forum List -->
            <?php if (empty($forums)): ?>
                <div class="alert alert-warning text-center mt-3">No forums found.</div>
            <?php else: ?>
                <?php foreach ($forums as $forum): ?>
                    <div class="card shadow-sm border-0 rounded-3 p-3 mt-3">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <img src="assets/default-avatar.png" alt="User" class="rounded-circle" width="50" height="50">
                            </div>
                            <div class="w-100">
                                <div class="d-flex justify-content-between">
                                    <h6 class="fw-bold"> <?= htmlspecialchars($forum['name']) ?> </h6>
                                    <span class="text-muted small"> <i class="far fa-clock"></i> <?= $forum['last_activity'] ? date('M d', strtotime($forum['last_activity'])) : 'No Activity' ?> </span>
                                </div>
                                <p class="text-muted"> <?= nl2br(htmlspecialchars(substr($forum['description'], 0, 100))) ?>...</p>
                                <div class="d-flex justify-content-between small text-muted">
                                    <div><i class="fas fa-comments"></i> <?= $forum['thread_count'] ?> Threads</div>
                                    <div>
                                        <a href="#" class="text-decoration-none me-3"><i class="far fa-thumbs-up"></i> Like</a>
                                        <a href="#" class="text-decoration-none"><i class="far fa-comment"></i> Comment</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="forum.php?id=<?= $forum['id'] ?>" class="btn btn-outline-primary w-100">
                                View Forum <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Pagination Controls -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= htmlspecialchars($search) ?>">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"> <?= $i ?> </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= htmlspecialchars($search) ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Create Forum Modal -->
<div class="modal fade" id="createForumModal">
    <div class="modal-dialog">
        <form method="POST" action="create_forum.php" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Create New Forum</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Forum Name" required>
                </div>
                <div class="mb-3">
                    <textarea name="description" class="form-control" rows="3" placeholder="Description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<?php require 'footer.php'; ?>
