<!-- footer.php -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Voting system
function handleVote(contentType, contentId, value) {
    $.ajax({
        url: 'vote.php',
        method: 'POST',
        data: {
            content_type: contentType,
            content_id: contentId,
            value: value
        },
        success: function(data) {
            if(data.success) {
                $(`.vote-btn[onclick*="${contentType}, ${contentId}, ${value}"]`)
                    .toggleClass('active', data.user_vote === value)
                    .siblings('span').text(data.score);
            }
        }
    });
}

// Comment submission
$('#commentForm').submit(function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    $.ajax({
        url: 'comment.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(data) {
            if(data.success) {
                $('#commentForm textarea').val('');
                loadComments(); // Implement this function to refresh comments
            }
        }
    });
});

// Search functionality
function searchForums() {
    const query = $('#searchInput').val().trim();
    window.location.href = `forums.php?search=${encodeURIComponent(query)}`;
}
</script>