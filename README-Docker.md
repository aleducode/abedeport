# ABEDEPORT - Docker Stack

This document provides instructions for running the ABEDEPORT application using Docker.

## Prerequisites

- Docker
- Docker Compose

## Quick Start

1. **Clone or download the project files**

2. **Start the Docker stack:**
   ```bash
   docker-compose up -d
   ```

3. **Access the application:**
   - Main application: http://localhost:8080
   - Home application: http://localhost:8082
   - phpMyAdmin: http://localhost:8081

## Services

The Docker stack includes the following services:

### 1. PHP Application (app)
- **Port:** 8080
- **URL:** http://localhost:8080
- **Description:** Main PHP application with Apache web server

### 2. Home Application (home-app)
- **Port:** 8082
- **URL:** http://localhost:8082
- **Description:** Custom home page with different HTML content
- **Path:** `/home` directory mounted to `/var/www/html`

### 3. MySQL Database (db)
- **Port:** 3306
- **Description:** MySQL 8.0 database server
- **Database:** ABEDEPORT
- **Credentials:**
  - Username: `abedeport_user`
  - Password: `abedeport_password`
  - Root Password: `root_password`

### 4. phpMyAdmin (phpmyadmin)
- **Port:** 8081
- **URL:** http://localhost:8081
- **Description:** Web-based MySQL administration tool

## Default Login Credentials

### Application Users
- **Admin User:**
  - Email: `admin@abedeport.com`
  - Password: `admin123`

- **Test User:**
  - Email: `user@abedeport.com`
  - Password: `user123`

### phpMyAdmin
- **Username:** `abedeport_user`
- **Password:** `abedeport_password`

## Docker Commands

### Start the stack
```bash
docker-compose up -d
```

### Stop the stack
```bash
docker-compose down
```

### View logs
```bash
# All services
docker-compose logs

# Specific service
docker-compose logs app
docker-compose logs home-app
docker-compose logs db
docker-compose logs phpmyadmin
```

### Rebuild containers
```bash
docker-compose up -d --build
```

### Remove everything (including volumes)
```bash
docker-compose down -v
```

## File Structure

```
abedeport/
├── app/                    # PHP application files
├── assets/                 # CSS, JS, images
├── home/                   # Custom home directory
│   └── index.html         # Home page HTML
├── database/
│   └── init.sql           # Database initialization script
├── Dockerfile             # PHP application container
├── docker-compose.yml     # Docker stack configuration
├── .dockerignore          # Files to exclude from Docker build
└── README-Docker.md       # This file
```

## Environment Variables

The application uses the following environment variables:

- `DB_HOST`: Database host (default: localhost)
- `DB_NAME`: Database name (default: ABEDEPORT)
- `DB_USER`: Database username (default: root)
- `DB_PASSWORD`: Database password (default: empty)

These are automatically set in the docker-compose.yml file.

## Custom Home Path

The new home application (`home-app`) provides:
- **Custom HTML content** in the `/home` directory
- **Separate port** (8082) for isolation
- **Shared assets** with the main application
- **Database connectivity** for dynamic content
- **Navigation links** to other services

### Home Directory Features:
- Responsive Bootstrap design
- Services showcase section
- Contact information
- Links to main application and phpMyAdmin
- Custom styling and branding

## Troubleshooting

### Port conflicts
If ports 8080, 8081, 8082, or 3306 are already in use, modify the ports in `docker-compose.yml`:

```yaml
ports:
  - "8083:80"  # Change 8080 to 8083
```

### Database connection issues
1. Ensure the database container is running:
   ```bash
   docker-compose ps
   ```

2. Check database logs:
   ```bash
   docker-compose logs db
   ```

3. Wait a few minutes for the database to fully initialize on first run.

### Permission issues
If you encounter permission issues, run:
```bash
sudo chown -R $USER:$USER .
```

## Development

### Making changes to the application
The application files are mounted as volumes, so changes to the PHP files will be reflected immediately without rebuilding the container.

### Making changes to the home page
Edit files in the `home/` directory and they will be immediately available at http://localhost:8082.

### Database changes
To make changes to the database structure:
1. Modify `database/init.sql`
2. Rebuild the database container:
   ```bash
   docker-compose down
   docker-compose up -d --build
   ```

**Note:** This will reset the database. For production, use proper database migrations.

## Production Considerations

For production deployment:

1. **Change default passwords** in `docker-compose.yml`
2. **Use environment files** for sensitive data
3. **Configure SSL/TLS** certificates
4. **Set up proper backups** for the database
5. **Use a reverse proxy** (nginx) in front of the application
6. **Configure proper logging**

## Support

For issues or questions about the Docker setup, please refer to the main project documentation or create an issue in the project repository. 