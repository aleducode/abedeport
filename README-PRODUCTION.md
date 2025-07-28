# ABE Deportes - Production Deployment Guide

This guide covers deploying ABE Deportes to production using Docker Compose with Traefik as a reverse proxy.

## 🚀 Quick Start

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd abedeport
   ```

2. **Configure environment**
   ```bash
   cp .env.production .env
   # Edit .env with your production values
   nano .env
   ```

3. **Deploy**
   ```bash
   ./deploy.sh
   ```

## 📋 Prerequisites

- Docker and Docker Compose installed
- Domain name pointing to your server
- Server with at least 2GB RAM
- Open ports: 80, 443

## 🔧 Configuration

### Environment Variables (.env)

```bash
# Domain Configuration
DOMAIN=your-domain.com
ACME_EMAIL=admin@your-domain.com

# Database Configuration  
DB_PASSWORD=your_secure_database_password
MYSQL_ROOT_PASSWORD=your_secure_root_password

# Security
PMA_AUTH=admin:$2y$10$encrypted_password_hash
```

### Generate Secure Passwords

```bash
# For database passwords
openssl rand -base64 32

# For phpMyAdmin basic auth
htpasswd -nb admin your_password
```

## 🏗️ Architecture

```
Internet
    ↓
Traefik (Port 80/443)
    ↓
┌─────────────────────────────────────┐
│  ABE Deportes Application           │
│  ├── PHP/Apache (abedeport.com)     │
│  ├── MySQL Database                 │
│  ├── Redis Cache                    │
│  ├── phpMyAdmin (pma.abedeport.com) │
│  └── Automated Backups              │
└─────────────────────────────────────┘
```

## 🌐 Services

| Service | URL | Description |
|---------|-----|-------------|
| **Main App** | `https://your-domain.com` | ABE Deportes website |
| **Traefik Dashboard** | `https://traefik.your-domain.com` | Reverse proxy dashboard |
| **phpMyAdmin** | `https://pma.your-domain.com` | Database management |

## 🔒 Security Features

- **SSL/TLS**: Automatic Let's Encrypt certificates
- **Security Headers**: HSTS, XSS protection, etc.
- **Basic Auth**: phpMyAdmin protected
- **Network Isolation**: Internal Docker networks
- **Regular Updates**: Watchtower for automatic updates

## 💾 Backup System

- **Automatic Backups**: Daily at 2 AM
- **Retention**: Keeps last 7 backups
- **Location**: `./backups/` directory
- **Compression**: gzipped SQL dumps

### Manual Backup

```bash
docker-compose -f production.yml exec backup /scripts/backup.sh
```

### Restore from Backup

```bash
# Stop the application
docker-compose -f production.yml stop app

# Restore database
gunzip -c backups/abedeport_backup_YYYYMMDD_HHMMSS.sql.gz | \
docker-compose -f production.yml exec -T db mysql -u root -p$MYSQL_ROOT_PASSWORD

# Start the application
docker-compose -f production.yml start app
```

## 📊 Monitoring & Logs

### View Logs
```bash
# All services
docker-compose -f production.yml logs -f

# Specific service
docker-compose -f production.yml logs -f app
docker-compose -f production.yml logs -f traefik
docker-compose -f production.yml logs -f db
```

### Service Status
```bash
docker-compose -f production.yml ps
```

### Resource Usage
```bash
docker stats
```

## 🔧 Management Commands

### Start Services
```bash
docker-compose -f production.yml up -d
```

### Stop Services
```bash
docker-compose -f production.yml down
```

### Restart Services
```bash
docker-compose -f production.yml restart
```

### Update Application
```bash
git pull
docker-compose -f production.yml up -d --build
```

### Scale Application (if needed)
```bash
docker-compose -f production.yml up -d --scale app=2
```

## 🚨 Troubleshooting

### SSL Certificate Issues
```bash
# Check Traefik logs
docker-compose -f production.yml logs traefik

# Remove and regenerate certificates
rm -rf traefik/acme/acme.json
touch traefik/acme/acme.json
chmod 600 traefik/acme/acme.json
docker-compose -f production.yml restart traefik
```

### Database Connection Issues
```bash
# Check database logs
docker-compose -f production.yml logs db

# Connect to database directly
docker-compose -f production.yml exec db mysql -u root -p
```

### Application Issues
```bash
# Check application logs
docker-compose -f production.yml logs app

# Access application container
docker-compose -f production.yml exec app bash
```

## 📈 Performance Optimization

### Database Tuning
- Edit `database/my.cnf` for MySQL optimization
- Adjust `innodb_buffer_pool_size` based on available RAM

### Application Caching
- Redis is configured for session storage
- Consider implementing application-level caching

### Resource Limits
Add resource limits to docker-compose services:
```yaml
deploy:
  resources:
    limits:
      memory: 512M
      cpus: '0.5'
```

## 🔄 Updates & Maintenance

### Automatic Updates
Watchtower is configured to automatically update containers daily at 4 AM.

### Manual Updates
```bash
# Pull latest images
docker-compose -f production.yml pull

# Recreate containers with new images
docker-compose -f production.yml up -d --force-recreate
```

### Database Maintenance
```bash
# Optimize database tables
docker-compose -f production.yml exec db mysqlcheck -u root -p --optimize ABEDEPORT
```

## 📞 Support

For issues or questions:
1. Check the logs first
2. Review this documentation
3. Check Docker and Traefik documentation
4. Create an issue in the repository

---

## 📝 Additional Notes

- Ensure your domain DNS is properly configured
- Monitor disk space for backups and logs
- Regularly update passwords and security configurations
- Consider implementing monitoring tools like Prometheus + Grafana for production environments