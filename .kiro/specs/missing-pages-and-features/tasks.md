# Implementation Tasks

## Task List

- [x] 1. Create `news.php` — Public Article Listing Page
  - [x] 1.1 Create `news.php` at the project root; set `$currentPage = 'news'`, query `SELECT * FROM articles WHERE deleted_at IS NULL ORDER BY created_at DESC`, and include `includes/nav.php` and `includes/footer.php`
  - [x] 1.2 Render article cards using existing CSS classes `.articles-section`, `.articles-grid`, `.article-card`, `.article-card-body`, `.article-card-footer` from `css/pages.css`
  - [x] 1.3 For each card display: title (3-line clamp), author with icon, category badge, excerpt (3-line clamp), and date — use `published_date` when non-NULL, otherwise `created_at`
  - [x] 1.4 Wrap each card in `<a href="article.php?id={id}">` so clicking navigates to the article detail
  - [x] 1.5 Add empty-state block (`.empty-state`) when no articles exist
  - [x] 1.6 Include `css/engagement.css`, `js/engagement.js`, and `js/realtime.js`; add `data-realtime="articles"` to the grid container

- [x] 2. Create `media.php` — Public Media Library Page
  - [x] 2.1 Create `media.php` at the project root; set `$currentPage = 'media'`, query `SELECT * FROM media WHERE deleted_at IS NULL ORDER BY created_at DESC`, and include nav and footer
  - [x] 2.2 Render media cards using existing CSS classes `.media-section`, `.media-grid`, `.media-card` from `css/pages.css`
  - [x] 2.3 For each card display: title, description, type badge (`.type-audio`, `.type-video`), and category
  - [x] 2.4 Render `<audio controls>` for `type = 'audio'`, `<video controls>` for `type = 'video'`, and `<img>` for `type = 'image'`, each using `file_path` as the source
  - [x] 2.5 Add empty-state block when no media records exist
  - [x] 2.6 Add a filter bar with buttons for All / Audio / Video / Image that filter cards client-side using JS

- [x] 3. Add Vocabulary Section to `lesson.php`
  - [x] 3.1 In `lesson.php`, after the `<div class="lesson-text">` block and before the prev/next navigation, add a conditional block: `<?php if (!empty($lesson['vocabulary'])): ?>` that renders a vocabulary section
  - [x] 3.2 Style the vocabulary section with a new `.lesson-vocabulary` card in `css/lesson.css` — use the same card background and border-radius as `.lesson-content-card`, with a purple left border accent and a heading "Vocabulary"

- [x] 4. Surface `published_date` and `excerpt` in `article.php` and `admin/articles-manage.php`
  - [x] 4.1 In `article.php`, replace the hardcoded `$article['created_at']` date in the `.article-meta` row with: `published_date` when non-NULL, otherwise `created_at`
  - [x] 4.2 In `admin/articles-manage.php`, add `published_date` (date input) and `excerpt` (textarea) fields to the Add/Edit modal form, and include them in the INSERT and UPDATE SQL statements

- [x] 5. Create `admin/comments-manage.php` — Comments Moderation Page
  - [x] 5.1 Create `admin/comments-manage.php` with session check redirecting to `login.php` when unauthenticated
  - [x] 5.2 Handle three POST actions: `approve` (sets `status = 'approved'`), `reject` (sets `status = 'rejected'`), `delete` (hard deletes the row) — all by comment `id`
  - [x] 5.3 Query all comments ordered by `created_at DESC` and render them in a table with columns: #, Content Type, Content ID, Name, Comment (truncated to 80 chars), Status badge, Date, Actions
  - [x] 5.4 Add Approve / Reject / Delete action buttons per row; use existing `.btn-icon` styles and confirmation modal for delete
  - [x] 5.5 Add filter tabs for All / Pending / Approved / Rejected that filter the table client-side
  - [x] 5.6 Add the Comments link to `admin/includes/sidebar.php` in the Communication section, with a badge showing the count of non-approved comments

- [x] 6. Add Engagement Metrics to `admin/analytics.php`
  - [x] 6.1 In the PHP section of `admin/analytics.php`, add queries: total likes from `likes` table, total comments from `comments` table, comments grouped by `status`, and likes/comments per `content_type`
  - [x] 6.2 Add two new metric cards to the existing `.analytics-grid`: "Total Likes" (heart icon, `#f43f5e`) and "Total Comments" (chat icon, `#667eea`)
  - [x] 6.3 Add a new chart section below the existing charts: a doughnut chart for "Comments by Status" (approved/pending/rejected) and a bar chart for "Engagement by Content Type" (likes + comments per type)
  - [x] 6.4 Handle zero-row cases: display `0` for counts and show an empty-state message inside the chart canvas wrapper when both likes and comments are zero
