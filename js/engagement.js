/**
 * Engagement Widget — Likes & Comments
 * Usage: EngagementWidget.init(container, type, id)
 */
const EngagementWidget = (() => {

    function timeAgo(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
        if (diff < 60)   return 'just now';
        if (diff < 3600) return Math.floor(diff/60) + 'm ago';
        if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
        return Math.floor(diff/86400) + 'd ago';
    }

    function renderComment(c) {
        return `<div class="eng-comment">
            <div class="eng-comment-avatar">${c.name.charAt(0).toUpperCase()}</div>
            <div class="eng-comment-body">
                <div class="eng-comment-meta">
                    <span class="eng-comment-name">${c.name}</span>
                    <span class="eng-comment-time">${timeAgo(c.created_at)}</span>
                </div>
                <p class="eng-comment-text">${c.comment}</p>
            </div>
        </div>`;
    }

    function init(container, type, id) {
        const apiBase = '/api/engagement.php';

        container.innerHTML = `
            <div class="eng-widget">
                <div class="eng-actions">
                    <button class="eng-like-btn" data-liked="false">
                        <i class="bi bi-heart"></i>
                        <span class="eng-like-count">0</span>
                    </button>
                    <button class="eng-comment-toggle">
                        <i class="bi bi-chat"></i>
                        <span class="eng-comment-count">0</span>
                    </button>
                </div>
                <div class="eng-comments-panel" style="display:none;">
                    <div class="eng-comments-list"></div>
                    <form class="eng-comment-form">
                        <input type="text" class="eng-input" name="name" placeholder="Your name" maxlength="80" required>
                        <textarea class="eng-input" name="comment" placeholder="Write a comment..." maxlength="1000" required></textarea>
                        <button type="submit" class="eng-submit-btn"><i class="bi bi-send"></i> Post</button>
                    </form>
                </div>
            </div>`;

        const likeBtn      = container.querySelector('.eng-like-btn');
        const likeCount    = container.querySelector('.eng-like-count');
        const commentToggle = container.querySelector('.eng-comment-toggle');
        const commentCount  = container.querySelector('.eng-comment-count');
        const panel        = container.querySelector('.eng-comments-panel');
        const list         = container.querySelector('.eng-comments-list');
        const form         = container.querySelector('.eng-comment-form');

        // Load initial data
        fetch(`${apiBase}?type=${type}&id=${id}`)
            .then(r => r.json())
            .then(data => {
                likeCount.textContent = data.likes;
                commentCount.textContent = data.comments.length;
                if (data.liked) {
                    likeBtn.classList.add('liked');
                    likeBtn.dataset.liked = 'true';
                    likeBtn.querySelector('i').className = 'bi bi-heart-fill';
                }
                list.innerHTML = data.comments.length
                    ? data.comments.map(renderComment).join('')
                    : '<p class="eng-no-comments">No comments yet. Be the first!</p>';
            });

        // Like toggle
        likeBtn.addEventListener('click', () => {
            fetch(apiBase, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=like&type=${type}&id=${id}`
            })
            .then(r => r.json())
            .then(data => {
                likeCount.textContent = data.likes;
                likeBtn.dataset.liked = data.liked;
                likeBtn.classList.toggle('liked', data.liked);
                likeBtn.querySelector('i').className = data.liked ? 'bi bi-heart-fill' : 'bi bi-heart';
            });
        });

        // Toggle comments panel
        commentToggle.addEventListener('click', () => {
            const open = panel.style.display === 'none';
            panel.style.display = open ? 'block' : 'none';
            commentToggle.classList.toggle('active', open);
        });

        // Submit comment
        form.addEventListener('submit', e => {
            e.preventDefault();
            const name    = form.querySelector('[name=name]').value.trim();
            const comment = form.querySelector('[name=comment]').value.trim();
            if (!name || !comment) return;

            const btn = form.querySelector('.eng-submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Posting...';

            fetch(apiBase, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=comment&type=${type}&id=${id}&name=${encodeURIComponent(name)}&comment=${encodeURIComponent(comment)}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const noMsg = list.querySelector('.eng-no-comments');
                    if (noMsg) noMsg.remove();
                    list.insertAdjacentHTML('afterbegin', renderComment(data.comment));
                    const count = parseInt(commentCount.textContent) + 1;
                    commentCount.textContent = count;
                    form.reset();
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send"></i> Post';
            });
        });
    }

    return { init };
})();

// Auto-init all [data-engagement] elements
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-engagement]').forEach(el => {
        const type = el.dataset.engType;
        const id   = el.dataset.engId;
        if (type && id) EngagementWidget.init(el, type, id);
    });
});
