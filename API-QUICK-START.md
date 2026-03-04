# 🚀 API v1 Quick Start Guide

## ✅ Status: WORKING

All API tests passed successfully! Your API is ready to use.

## 📊 Test Results

```
✓ BaseAPI loaded successfully
✓ Database connected successfully
✓ Dictionary table has 4 words
✓ Lessons table has 3 lessons
✓ Grammar topics table has 2 topics
✓ Proverbs table has 2 proverbs
✓ Articles table has 2 articles
✓ All API endpoint files exist
```

## 🎯 Quick Access

### Option 1: Interactive Tester (Recommended)
```bash
# Run this batch file:
test-api.bat

# Or manually open:
http://localhost/RUNYAKITARA%20HUB/test-api.html
```

### Option 2: API Documentation
```
http://localhost/RUNYAKITARA%20HUB/api/v1/docs.php
```

### Option 3: Direct API Calls

**Get all dictionary words:**
```
GET http://localhost/RUNYAKITARA%20HUB/api/v1/dictionary
```

**Get paginated results:**
```
GET http://localhost/RUNYAKITARA%20HUB/api/v1/dictionary?page=1&limit=10
```

**Get specific word:**
```
GET http://localhost/RUNYAKITARA%20HUB/api/v1/dictionary/1
```

**Filter by category:**
```
GET http://localhost/RUNYAKITARA%20HUB/api/v1/dictionary?category=greetings
```

## 📚 Available Endpoints

### Dictionary API
- `GET /api/v1/dictionary` - Get all words
- `GET /api/v1/dictionary/{id}` - Get specific word
- Query params: `page`, `limit`, `search`, `category`

### Lessons API
- `GET /api/v1/lessons` - Get all lessons
- `GET /api/v1/lessons/{id}` - Get specific lesson
- Query params: `page`, `limit`, `level`

### Grammar API
- `GET /api/v1/grammar` - Get all grammar topics
- `GET /api/v1/grammar/{id}` - Get specific topic
- Query params: `page`, `limit`, `difficulty`

### Proverbs API
- `GET /api/v1/proverbs` - Get all proverbs
- `GET /api/v1/proverbs/{id}` - Get specific proverb
- Query params: `page`, `limit`, `search`

### Articles API
- `GET /api/v1/articles` - Get all articles
- `GET /api/v1/articles/{id}` - Get specific article
- Query params: `page`, `limit`, `category`, `author`

### Translations API
- `GET /api/v1/translations` - Get all translations
- `GET /api/v1/translations/{id}` - Get specific translation
- Query params: `page`, `limit`, `type`

### Media API
- `GET /api/v1/media` - Get all media
- `GET /api/v1/media/{id}` - Get specific media
- Query params: `page`, `limit`, `type`

### Contact API
- `POST /api/v1/contact` - Submit contact form
- Body: `{ "name", "email", "subject", "message" }`

## 📝 Response Format

### Success Response
```json
{
  "success": true,
  "version": "v1",
  "data": {
    "items": [...],
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 150,
      "pages": 8
    }
  },
  "timestamp": "2025-02-17T10:30:00+00:00"
}
```

### Error Response
```json
{
  "success": false,
  "version": "v1",
  "error": "Error message",
  "timestamp": "2025-02-17T10:30:00+00:00"
}
```

## 🔧 Testing Tools

1. **Command Line Test:**
   ```bash
   php test-api-simple.php
   ```

2. **Browser Test:**
   ```bash
   test-api.bat
   ```

3. **cURL Examples:**
   ```bash
   # Get dictionary words
   curl http://localhost/RUNYAKITARA%20HUB/api/v1/dictionary
   
   # Get lessons with pagination
   curl "http://localhost/RUNYAKITARA%20HUB/api/v1/lessons?page=1&limit=5"
   
   # Submit contact form
   curl -X POST http://localhost/RUNYAKITARA%20HUB/api/v1/contact \
     -H "Content-Type: application/json" \
     -d '{"name":"Test","email":"test@example.com","subject":"Test","message":"Hello"}'
   ```

## 🎨 Features

✅ **Versioned URLs** - `/api/v1/` prefix for future compatibility
✅ **Pagination** - Built-in pagination support
✅ **Filtering** - Filter by category, level, difficulty, type
✅ **Search** - Full-text search capabilities
✅ **CORS Enabled** - Cross-origin requests supported
✅ **Input Validation** - Automatic validation and sanitization
✅ **Error Handling** - Consistent error responses
✅ **Documentation** - Beautiful HTML documentation
✅ **Testing Tools** - Interactive tester included

## 🚀 Next Steps

1. **Test the API:**
   - Run `test-api.bat`
   - Try different endpoints
   - Test pagination and filtering

2. **Integrate with Frontend:**
   - Use fetch() or axios to call API
   - Handle pagination
   - Display results

3. **Future Versions:**
   - Create `/api/v2/` when needed
   - Old clients continue using v1
   - No breaking changes!

## 📞 Support

If you encounter any issues:
1. Check Apache is running (`start.bat`)
2. Run `php test-api-simple.php` to diagnose
3. Check `.htaccess` file exists
4. Verify database is accessible

---

**Your API is ready to use! 🎉**
