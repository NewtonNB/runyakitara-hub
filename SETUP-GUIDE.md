# Runyakitara Hub - Complete Setup Guide

A modern language learning platform for Runyakitara with enterprise-grade RBAC, media management, and premium UI.

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- SQLite3 extension enabled
- Web server (Apache/Nginx) or XAMPP

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/NewtonNB/runyakitara-hub.git
   cd runyakitara-hub
   ```

2. **Start the server**
   - Windows: Double-click `start.bat`
   - Linux/Mac: Run `./start.sh`
   - Or use XAMPP and access via `http://localhost/runyakitara-hub`

3. **Initialize the database**
   - Navigate to `http://localhost/runyakitara-hub/init-database.php`
   - This creates the SQLite database with sample data

4. **Set up RBAC (Role-Based Access Control)**
   - Windows: Double-click `setup-rbac.bat`
   - Linux/Mac: Run `./setup-rbac.sh`
   - Or navigate to `http://localhost/runyakitara-hub/migrate-to-rbac.php`

5. **Access the admin panel**
   - URL: `http://localhost/runyakitara-hub/admin/`
   - Default credentials:
     - Username: `admin`
     - Password: `admin123`

## 📁 Project Structure

```
runyakitara-hub/
├── admin/              # Admin panel
│   ├── css/           # Admin styles
│   ├── js/            # Admin JavaScript
│   ├── includes/      # Shared admin components
│   └── *-manage.php   # Management pages
├── api/               # API endpoints
│   └── v1/           # API version 1
├── config/            # Configuration files
│   ├── database.php   # Database connection
│   ├── RBAC.php      # RBAC system
│   └── *.sql         # Database schemas
├── css/              # Public styles
├── js/               # Public JavaScript
├── includes/         # Shared components
├── uploads/          # User uploaded files
│   └── media/       # Media files
└── *.php            # Public pages
```

## 🎯 Features

### 1. Role-Based Access Control (RBAC)
- 5 default roles: Super Admin, Content Editor, Moderator, Registered User, Guest
- 40+ granular permissions across 10 resources
- Audit logging for all permission changes
- Role management UI at `admin/roles-manage.php`

**Using RBAC in your code:**
```php
require_once '../config/RBAC.php';

// Check permission
if (RBAC::hasPermission('articles', 'create')) {
    // User can create articles
}

// Check role
if (RBAC::hasRole('admin')) {
    // User is admin
}

// Require permission (redirects if denied)
RBAC::requirePermission('users', 'delete');
```

### 2. Content Management
- **Lessons**: Structured language lessons with levels (beginner/intermediate/advanced)
- **Dictionary**: Runyakitara-English word translations with pronunciation
- **Grammar**: Grammar topics with examples
- **Proverbs**: Traditional proverbs with translations and meanings
- **Articles**: News and blog posts
- **Translations**: Cultural content translations
- **Media**: Audio, video, and image uploads

### 3. Media Management
- Upload audio files (MP3, WAV, OGG)
- Upload video files (MP4, WEBM, AVI, MOV)
- Upload images (JPG, PNG, GIF, WEBP)
- Auto-detection of media types
- File storage in `uploads/media/`
- Preview and playback functionality

### 4. Form Validation
- Real-time client-side validation
- Visual feedback with animations
- Green checkmarks for valid fields
- Red warnings for invalid fields
- Auto-focus on first error
- Consistent across all forms

### 5. Contact System
- Public contact form at `/contact.php`
- Messages stored in database
- Admin panel to view/manage messages at `admin/messages-manage.php`
- Email validation and spam protection

### 6. RESTful API
- Base URL: `/api/v1/`
- Endpoints for all content types
- JSON responses
- CORS enabled
- Documentation at `/api/v1/docs.php`

**API Examples:**
```bash
# Get all lessons
GET /api/v1/lessons.php

# Get specific lesson
GET /api/v1/lessons.php?id=1

# Search dictionary
GET /api/v1/dictionary.php?search=omuntu

# Submit contact form
POST /api/v1/contact.php
```

## 🔧 Configuration

### Database
Edit `config/database.php` to change database settings:
```php
$dbPath = __DIR__ . '/../data/runyakitara.db';
```

### RBAC Permissions
Edit `config/rbac-setup.sql` to modify roles and permissions, then run:
```bash
php migrate-to-rbac.php
```

### File Uploads
Maximum file size is controlled by PHP settings in `php.ini`:
```ini
upload_max_filesize = 50M
post_max_size = 50M
```

## 🎨 Customization

### Styling
- Admin styles: `admin/css/dashboard.css`
- Public styles: `css/pages.css`, `css/home.css`
- Form validation: `admin/css/form-validation.css`

### Adding New Content Types
1. Create database table in `config/setup.sql`
2. Create admin management page: `admin/[type]-manage.php`
3. Create public display page: `[type].php`
4. Add API endpoint: `api/v1/[type].php`
5. Update navigation in `includes/nav.php` and `admin/includes/sidebar.php`

## 🔐 Security

### Best Practices
- Change default admin password immediately
- Keep `config/database.php` secure (excluded from Git)
- Database file is in `data/` (excluded from Git)
- Uploaded files are in `uploads/` (excluded from Git)
- Use RBAC permissions for all sensitive operations
- Validate and sanitize all user inputs

### Password Hashing
```php
// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Verify password
if (password_verify($inputPassword, $hashedPassword)) {
    // Password correct
}
```

## 📊 Database Schema

### Main Tables
- `users` - Admin users with authentication
- `roles` - RBAC roles
- `permissions` - RBAC permissions
- `role_permissions` - Role-permission mappings
- `user_roles` - User-role assignments
- `rbac_audit_log` - Permission change audit trail
- `lessons` - Language lessons
- `dictionary` - Word translations
- `grammar_topics` - Grammar explanations
- `proverbs` - Traditional proverbs
- `articles` - News and blog posts
- `translations` - Cultural translations
- `media` - Uploaded media files
- `contact_messages` - Contact form submissions

## 🚀 Deployment

### Production Checklist
1. Change all default passwords
2. Set proper file permissions (755 for directories, 644 for files)
3. Enable HTTPS
4. Configure proper error logging
5. Disable display_errors in PHP
6. Set up regular database backups
7. Configure proper CORS settings for API
8. Review and update RBAC permissions
9. Test all forms and file uploads
10. Set up monitoring and analytics

### Apache Configuration
```apache
<Directory /path/to/runyakitara-hub>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

## 🐛 Troubleshooting

### Database Issues
- Ensure SQLite extension is enabled in PHP
- Check file permissions on `data/` directory
- Run `init-database.php` to recreate database

### Upload Issues
- Check `uploads/media/` directory exists and is writable
- Verify PHP upload settings in `php.ini`
- Check file size limits

### RBAC Issues
- Run `migrate-to-rbac.php` to reset RBAC tables
- Check session is started in all admin pages
- Verify user has proper role assignments

### Permission Errors
```bash
# Linux/Mac - Set proper permissions
chmod -R 755 .
chmod -R 777 data/
chmod -R 777 uploads/
```

## 📝 Development

### Adding New Features
1. Create feature branch: `git checkout -b feature-name`
2. Make changes and test thoroughly
3. Commit: `git commit -m "Add feature description"`
4. Push: `git push origin feature-name`
5. Create pull request on GitHub

### Code Style
- Use 4 spaces for indentation
- Follow PSR-12 coding standards
- Comment complex logic
- Use meaningful variable names
- Keep functions small and focused

## 📞 Support

- GitHub: https://github.com/NewtonNB/runyakitara-hub
- Issues: https://github.com/NewtonNB/runyakitara-hub/issues
- Email: tukamuhebwanewton@gmail.com

## 📄 License

This project is open source and available for educational purposes.

## 🙏 Credits

Developed by Newton Tukamuhebwa (NewtonNB)
- Email: tukamuhebwanewton@gmail.com
- GitHub: https://github.com/NewtonNB

---

**Last Updated:** March 2026
**Version:** 1.0.0
