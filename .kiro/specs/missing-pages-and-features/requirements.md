# Requirements Document

## Introduction

The Runyakitara Hub is a PHP/MySQL language learning and cultural platform. A system audit identified six missing or incomplete features: two public-facing pages that are linked from navigation and footer but do not exist (`news.php`, `media.php`), two data fields stored in the database but never rendered (`lessons.vocabulary`, `articles.excerpt` / `articles.published_date`), and two admin tools that are absent or non-functional (comments moderation page, analytics engagement data). This document specifies the requirements to close all six gaps.

---

## Glossary

- **Hub**: The Runyakitara Hub PHP/MySQL web application.
- **News_Page**: The public article listing page (`news.php`).
- **Media_Page**: The public media library page (`media.php`).
- **Lesson_Detail**: The public single-lesson view (`lesson.php`).
- **Article_Detail**: The public single-article view (`article.php`).
- **Admin_Comments**: The admin comments moderation page (`admin/comments-manage.php`).
- **Admin_Analytics**: The existing admin analytics page (`admin/analytics.php`).
- **Visitor**: An unauthenticated user browsing the public site.
- **Admin**: An authenticated user with access to the admin panel.
- **Soft-deleted**: A record whose `deleted_at` column is non-NULL.
- **Published_date**: The `published_date` DATE column on the `articles` table.
- **Excerpt**: The `excerpt` TEXT column on the `articles` table.
- **Vocabulary**: The `vocabulary` TEXT column on the `lessons` table.
- **Engagement**: Likes and comments stored in the `likes` and `comments` tables.

---

## Requirements

### Requirement 1: Public News / Articles Listing Page

**User Story:** As a Visitor, I want to browse all published articles on a dedicated news page, so that I can discover cultural and language content without navigating away from the site.

#### Acceptance Criteria

1. THE News_Page SHALL fetch all articles from the `articles` table where `deleted_at IS NULL`, ordered by `created_at` descending.
2. WHEN the `articles` table contains one or more non-soft-deleted rows, THE News_Page SHALL render one article card per row, displaying the article title, author, category badge, excerpt (truncated to 3 lines), and date.
3. WHEN an article has a non-NULL `published_date`, THE News_Page SHALL display `published_date` as the article date; WHEN `published_date` IS NULL, THE News_Page SHALL display `created_at` as the article date.
4. WHEN a Visitor clicks an article card, THE News_Page SHALL navigate the Visitor to `article.php?id={article_id}`.
5. WHEN the `articles` table contains no non-soft-deleted rows, THE News_Page SHALL display an empty-state message without a PHP error or blank page.
6. THE News_Page SHALL include the shared navigation (`includes/nav.php`) and footer (`includes/footer.php`).
7. THE News_Page SHALL set `$currentPage = 'news'` so the navigation highlights the correct menu item.
8. THE News_Page SHALL apply the existing CSS classes from `css/pages.css` (`.articles-section`, `.articles-grid`, `.article-card`) for visual consistency.

---

### Requirement 2: Public Media Library Page

**User Story:** As a Visitor, I want to browse audio, video, and image resources on a dedicated media page, so that I can access cultural media content directly.

#### Acceptance Criteria

1. THE Media_Page SHALL fetch all media records from the `media` table where `deleted_at IS NULL`, ordered by `created_at` descending.
2. WHEN the `media` table contains one or more non-soft-deleted rows, THE Media_Page SHALL render one media card per row, displaying the media title, description, type badge (`audio`, `video`, or `image`), and category.
3. WHEN a media record has `type = 'audio'`, THE Media_Page SHALL render an HTML `<audio>` element with the `file_path` as the source.
4. WHEN a media record has `type = 'video'`, THE Media_Page SHALL render an HTML `<video>` element with the `file_path` as the source.
5. WHEN a media record has `type = 'image'`, THE Media_Page SHALL render an `<img>` element with the `file_path` as the source.
6. WHEN the `media` table contains no non-soft-deleted rows, THE Media_Page SHALL display an empty-state message without a PHP error or blank page.
7. THE Media_Page SHALL include the shared navigation and footer, set `$currentPage = 'media'`, and apply the existing CSS classes from `css/pages.css` (`.media-section`, `.media-grid`, `.media-card`).

---

### Requirement 3: Vocabulary Section on Lesson Detail Page

**User Story:** As a Visitor studying a lesson, I want to see the vocabulary words associated with that lesson, so that I can learn key terms in context.

#### Acceptance Criteria

1. WHEN a lesson record has a non-empty `vocabulary` column, THE Lesson_Detail SHALL render a vocabulary section below the main lesson content, displaying the vocabulary text.
2. WHEN a lesson record has a NULL or empty `vocabulary` column, THE Lesson_Detail SHALL not render a vocabulary section, and the page SHALL display without error.
3. THE Lesson_Detail SHALL retrieve the `vocabulary` column in the existing `SELECT * FROM lessons WHERE id = ?` query without adding a separate database query.
4. THE Lesson_Detail SHALL style the vocabulary section consistently with the existing lesson content card styles defined in `css/lesson.css`.

---

### Requirement 4: Article Excerpt and Published Date on Article Detail Page

**User Story:** As a Visitor reading an article, I want to see the article's excerpt as a lead paragraph and its published date, so that I get a clear summary and accurate publication context.

#### Acceptance Criteria

1. WHEN an article record has a non-empty `excerpt` column, THE Article_Detail SHALL render the excerpt as a lead paragraph above the main article body.
2. WHEN an article record has a NULL or empty `excerpt` column, THE Article_Detail SHALL not render a lead paragraph, and the page SHALL display without error.
3. WHEN an article record has a non-NULL `published_date`, THE Article_Detail SHALL display `published_date` (formatted as `F d, Y`) in the article metadata row.
4. WHEN an article record has a NULL `published_date`, THE Article_Detail SHALL display `created_at` (formatted as `F d, Y`) in the article metadata row.
5. THE Article_Detail SHALL retrieve `excerpt` and `published_date` from the existing `SELECT * FROM articles WHERE id = ?` query without adding a separate database query.

> **Note:** `article.php` already renders `$article['excerpt']` conditionally. Requirement 4 primarily ensures `published_date` is surfaced and that the admin article form captures `excerpt` and `published_date` so the data is actually populated.

---

### Requirement 5: Admin Comments Moderation Page

**User Story:** As an Admin, I want to view and moderate user comments submitted through the engagement system, so that I can approve, reject, or delete inappropriate content.

#### Acceptance Criteria

1. THE Admin_Comments SHALL require an active admin session; WHEN no session exists, THE Admin_Comments SHALL redirect the Visitor to `admin/login.php`.
2. THE Admin_Comments SHALL fetch all rows from the `comments` table, ordered by `created_at` descending, and display them in a table showing: comment ID, content type, content ID, commenter name, comment text (truncated), status badge, and date.
3. WHEN an Admin submits a `status=approve` action for a comment, THE Admin_Comments SHALL update that comment's `status` column to `'approved'` in the database.
4. WHEN an Admin submits a `status=reject` action for a comment, THE Admin_Comments SHALL update that comment's `status` column to `'rejected'` in the database.
5. WHEN an Admin submits a `delete` action for a comment, THE Admin_Comments SHALL permanently delete that comment row from the database.
6. THE Admin_Comments SHALL display a count of comments with `status = 'pending'` or `status != 'approved'` as a badge in the sidebar navigation.
7. IF the `comments` table contains no rows, THEN THE Admin_Comments SHALL display an empty-state message without error.
8. THE Admin_Comments SHALL apply the existing admin CSS (`css/dashboard.css`, `css/table-utils.css`) and include the shared admin sidebar and header.
9. FOR ALL comments with `status = 'approved'`, approving the comment again SHALL leave the status as `'approved'` (idempotent approve action).

---

### Requirement 6: Admin Analytics — Real Engagement Data

**User Story:** As an Admin, I want the analytics page to show real engagement metrics (total likes, total comments, comments by status), so that I can understand how Visitors interact with content.

#### Acceptance Criteria

1. THE Admin_Analytics SHALL query the `likes` table and display the total like count across all content types.
2. THE Admin_Analytics SHALL query the `comments` table and display the total comment count across all content types.
3. THE Admin_Analytics SHALL query the `comments` table grouped by `status` and display a breakdown of comment counts per status (approved, pending, rejected).
4. THE Admin_Analytics SHALL display a bar or doughnut chart showing engagement (likes and comments) per content type (`lesson`, `article`, `proverb`, `grammar`, `translation`).
5. WHEN the `likes` table contains zero rows, THE Admin_Analytics SHALL display `0` for total likes without a PHP error.
6. WHEN the `comments` table contains zero rows, THE Admin_Analytics SHALL display `0` for total comments and render the chart with an empty-state message.
7. THE Admin_Analytics SHALL add the engagement metric cards and chart to the existing `admin/analytics.php` page without removing the existing content count metrics or growth charts.
