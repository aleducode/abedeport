# AEDEPORT Blog Management System

## Overview

This is a comprehensive blog management system integrated into the AEDEPORT sports organization website. It provides a complete admin panel for creating, editing, and managing blog posts, along with a public-facing blog for visitors.

## Features

### Admin Panel Features
- **Dashboard**: Overview with statistics and recent posts
- **Create Posts**: Rich form for creating new blog posts
- **Edit Posts**: Full editing capabilities for existing posts
- **Manage Posts**: List view with filtering, search, and bulk actions
- **View Posts**: Detailed view with status management
- **Preview System**: Live preview before publishing
- **Image Upload**: Support for featured images
- **SEO Features**: Meta descriptions, tags, and URL slugs

### Public Blog Features
- **Responsive Design**: Mobile-friendly layout
- **Post Listing**: Grid view of published posts
- **Individual Post View**: Full post display with metadata
- **Social Sharing**: Share posts on social media
- **Print Functionality**: Print-friendly post views
- **SEO Optimized**: Proper meta tags and structured data

## File Structure

```
abedeport/
├── app/
│   ├── blog_functions.php      # Blog helper functions
│   ├── admin_dashboard.php     # Admin dashboard
│   ├── create_post.php         # Create new posts
│   ├── edit_post.php          # Edit existing posts
│   ├── view_post.php          # View post details
│   ├── manage_posts.php       # Manage all posts
│   ├── preview_post.php       # Preview system
│   └── home.php               # Main admin interface
├── blog/
│   ├── index.php              # Public blog viewer
│   └── .htaccess              # URL rewriting
├── assets/
│   └── img/
│       └── blog/              # Blog images storage
└── database/
    └── blog_posts.sql         # Database schema
```

## Database Schema

The blog system uses a `blog_posts` table with the following structure:

```sql
CREATE TABLE blog_posts (
  id_post INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL,
  contenido TEXT NOT NULL,
  imagen VARCHAR(255) NULL,
  autor_id INT NOT NULL,
  estado ENUM('borrador', 'publicado', 'archivado') DEFAULT 'borrador',
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_publicacion TIMESTAMP NULL,
  fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  slug VARCHAR(255) UNIQUE,
  meta_descripcion VARCHAR(160) NULL,
  etiquetas VARCHAR(255) NULL,
  FOREIGN KEY (autor_id) REFERENCES usuario(id_usuario)
);
```

## Installation

1. **Database Setup**:
   ```sql
   -- Run the blog_posts.sql file in your database
   source database/blog_posts.sql;
   ```

2. **Directory Permissions**:
   ```bash
   # Ensure the blog images directory is writable
   chmod 755 assets/img/blog/
   ```

3. **URL Rewriting**:
   - Ensure Apache mod_rewrite is enabled
   - The .htaccess file in the blog directory handles URL rewriting

## Usage

### Admin Access
1. Login to the admin panel at `/app/`
2. Navigate to the Dashboard to see an overview
3. Use "Nuevo Post" to create blog posts
4. Use "Gestionar Posts" to manage existing posts

### Creating Posts
1. Go to "Nuevo Post"
2. Fill in the title and content
3. Upload a featured image (optional)
4. Set the status (draft/published/archived)
5. Add tags and meta description
6. Save or preview the post

### Managing Posts
- **Filter**: Search by title or filter by status
- **Bulk Actions**: Select multiple posts for bulk operations
- **Quick Actions**: Edit, view, or delete individual posts
- **Status Management**: Change post status directly

### Public Blog
- Access the public blog at `/blog/`
- Published posts are automatically displayed
- Individual posts are accessible via their slug URLs

## Features in Detail

### Post States
- **Borrador (Draft)**: Only visible to admins
- **Publicado (Published)**: Visible to public
- **Archivado (Archived)**: Hidden but preserved

### Image Management
- Supports JPG, PNG, and GIF formats
- Maximum file size: 5MB
- Automatic resizing and optimization
- Unique filename generation

### SEO Features
- Automatic slug generation from titles
- Meta description support
- Tag system for categorization
- Clean URLs for better SEO

### Security Features
- Input validation and sanitization
- File upload security checks
- SQL injection prevention
- XSS protection

## Customization

### Styling
The system uses Bootstrap 5 with custom CSS variables:
```css
:root {
    --primary-color: rgb(173, 75, 19);
    --bs-primary: rgb(173, 75, 19);
    --bs-primary-rgb: 173, 75, 19;
}
```

### Configuration
Key configuration options in `blog_functions.php`:
- Image upload directory
- Maximum file size
- Allowed file types
- Posts per page

## API Functions

The `blog_functions.php` file provides these main functions:

- `createBlogPost($data)` - Create new post
- `updateBlogPost($id, $data)` - Update existing post
- `deleteBlogPost($id)` - Delete post
- `getBlogPostById($id)` - Get post by ID
- `getBlogPostBySlug($slug)` - Get post by slug
- `getPublishedBlogPosts($limit, $offset)` - Get published posts
- `getAllBlogPosts($limit, $offset)` - Get all posts
- `uploadImage($file)` - Handle image uploads

## Troubleshooting

### Common Issues

1. **Images not uploading**:
   - Check directory permissions
   - Verify file size limits
   - Check allowed file types

2. **URL rewriting not working**:
   - Ensure mod_rewrite is enabled
   - Check .htaccess file exists
   - Verify Apache configuration

3. **Posts not displaying**:
   - Check post status (must be 'publicado')
   - Verify database connection
   - Check for PHP errors

### Error Logs
Check your server's error logs for detailed information about any issues.

## Support

For technical support or feature requests, please contact the development team.

## License

This blog management system is part of the AEDEPORT project and follows the same licensing terms. 