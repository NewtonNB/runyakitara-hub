# Runyakitara Hub Website

A comprehensive, modern website for learning and preserving Runyakitara languages (Runyankore, Rukiga, Runyoro, and Rutooro).

## ✨ Features

### Frontend (9 Pages)
- **Home**: Hero section with mission statement and feature showcase
- **Lessons**: Structured learning from beginner to advanced levels
- **Dictionary**: Searchable vocabulary with thematic categories
- **Translations**: Songs, stories, poems, and documents
- **Grammar**: Comprehensive grammar rules and explanations
- **Proverbs**: Cultural wisdom and traditional sayings
- **News**: Latest updates on language and culture
- **Media**: Audio/video multimedia resources
- **Contact**: Working contact form with database storage

### Backend & Admin
- **SQLite Database**: Zero-configuration database (no MySQL setup needed!)
- **Admin Dashboard**: Professional content management system
- **REST API**: JSON endpoints for all content
- **Authentication**: Secure login system with password hashing
- **RBAC System**: Enterprise-grade Role-Based Access Control with granular permissions

### Design
- Modern gradient backgrounds with animations
- Bootstrap Icons throughout
- Google Fonts (Poppins + Playfair Display)
- Fully responsive (mobile, tablet, desktop)
- AOS scroll animations
- Professional navbar with login button

## 🚀 Quick Start (2 Minutes!)

### Option 1: View Static Site (No Setup)
Just open `index.html` in your browser - works immediately!

### Option 2: Full Backend Setup
1. **Start your web server** (XAMPP, WAMP, or any PHP server)
2. **Place files** in your web root:
   - XAMPP: `C:\xampp\htdocs\runyakitara-hub\`
   - WAMP: `C:\wamp64\www\runyakitara-hub\`
3. **Access the site**: `http://localhost/runyakitara-hub/`
4. **Login to admin**: `http://localhost/runyakitara-hub/admin/login.php`
   - Username: `admin`
   - Password: `admin123`

That's it! The SQLite database is created automatically on first run.

## 📁 Project Structure

```
runyakitara-hub/
├── index.html              # Home page
├── lessons.html            # Lessons
├── dictionary.html         # Dictionary
├── translations.html       # Translations
├── grammar.html            # Grammar guide
├── proverbs.html          # Proverbs
├── news.html              # News & articles
├── media.html             # Media resources
├── contact.html           # Contact form
│
├── admin/                 # Admin panel
│   ├── login.php          # Login page
│   ├── dashboard.php      # Dashboard
│   ├── dictionary.php     # Manage dictionary
│   ├── messages.php       # View messages
│   └── css/               # Admin styles
│
├── api/                   # Backend API
│   ├── dictionary.php     # Dictionary API
│   └── contact.php        # Contact form API
│
├── config/
│   ├── database.php       # Database config
│   └── setup.sql          # SQL schema (reference)
│
├── data/
│   └── runyakitara.db     # SQLite database (auto-created)
│
├── css/
│   ├── style.css          # Main styles
│   └── pages.css          # Page styles
│
└── js/
    └── main.js            # JavaScript

```

## 🎨 Design Highlights

- **Color Scheme**: Warm browns, golds, and gradients
- **Typography**: Poppins (body) + Playfair Display (headings)
- **Icons**: Bootstrap Icons v1.11
- **Animations**: AOS (Animate On Scroll)
- **Layout**: Flexbox & Grid, fully responsive

## 🔧 Customization

### Change Colors
Edit `css/style.css`:
```css
:root {
    --primary-color: #8B4513;
    --secondary-color: #D4A574;
    --accent-color: #CD853F;
}
```

### Update Content
- Edit HTML files directly
- Or use the admin panel to manage dynamic content

### Add Media
- Place audio/video files in appropriate folders
- Update paths in HTML or add via admin panel

## 🔐 Admin Panel Features

Access at `/admin/login.php` (default: admin/admin123)

- **Dashboard**: Statistics and overview
- **Dictionary Management**: Add/edit/delete words
- **Message Inbox**: View contact form submissions
- **Content Management**: Manage all site content
- **Role Management**: Assign roles and permissions to users (Super Admin only)
- **Modern UI**: Professional design with charts

### Role-Based Access Control (RBAC)

The system includes enterprise-grade RBAC with 5 default roles:

- **Super Admin** (Level 100): Full system access
- **Content Editor** (Level 50): Create/edit all content
- **Moderator** (Level 30): Review and moderate submissions
- **Registered User** (Level 10): Basic authenticated access
- **Guest** (Level 0): Public read-only access

**Setup RBAC:**
```bash
# Windows
setup-rbac.bat

# Linux/Mac
./setup-rbac.sh
```

See `RBAC-GUIDE.md` for complete documentation and `RBAC-QUICK-REFERENCE.md` for quick reference.

## 🌐 API Endpoints

- `api/dictionary.php?search=word` - Search dictionary
- `api/contact.php` - Submit contact form (POST)

## 📱 Browser Support

- Chrome, Firefox, Safari, Edge (latest versions)
- Mobile browsers (iOS Safari, Chrome Mobile)
- Responsive design works on all screen sizes

## 🔒 Security Features

- Password hashing (bcrypt)
- SQL injection protection (prepared statements)
- Input validation and sanitization
- Protected config files (.htaccess)
- Session management

## 🛠️ Requirements

- **PHP**: 7.4 or higher
- **SQLite**: Built into PHP (no separate install needed)
- **Web Server**: Apache, Nginx, or built-in PHP server
- **Browser**: Any modern browser

## 📝 Notes

- **Database**: Uses SQLite for easy setup (no MySQL configuration!)
- **First Run**: Database is created automatically
- **Sample Data**: Includes demo content to get started
- **Production**: Change admin password immediately!

## 🎯 What's Next?

1. **Change admin password** (important!)
2. Add your own content via admin panel
3. Customize colors and branding
4. Add audio/video resources
5. Test contact form
6. Deploy to production server

## 📄 License

© 2024 Runyakitara Hub. All rights reserved.

---

**Need help?** Check `PROJECT_STRUCTURE.md` for detailed file organization.
