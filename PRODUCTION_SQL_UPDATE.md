# How to Apply likes_comments.sql to Production Database

## ⚠️ IMPORTANT: Always backup before making changes!

---

## Option 1: Using Docker Exec (Recommended for Production)

### Step 1: Connect to your production server
```bash
ssh user@your-production-server.com
cd /path/to/abedeport
```

### Step 2: Create a backup FIRST
```bash
# Backup the entire database
docker exec abedeport-db mysqldump -u root -p'root_strong_password' ABEDEPORT > backup_$(date +%Y%m%d_%H%M%S).sql

# Verify the backup file exists and has content
ls -lh backup_*.sql
```

### Step 3: Copy the SQL file to the server (if not already there)
From your local machine:
```bash
scp database/likes_comments.sql user@your-production-server.com:/path/to/abedeport/database/
```

### Step 4: Apply the SQL file
```bash
# Method A: Using docker exec with file
docker exec -i abedeport-db mysql -u root -p'root_strong_password' ABEDEPORT < database/likes_comments.sql

# Method B: Copy file into container and execute
docker cp database/likes_comments.sql abedeport-db:/tmp/likes_comments.sql
docker exec abedeport-db mysql -u root -p'root_strong_password' ABEDEPORT -e "SOURCE /tmp/likes_comments.sql"
```

### Step 5: Verify the changes
```bash
# Check if tables were created
docker exec abedeport-db mysql -u root -p'root_strong_password' -e "USE ABEDEPORT; SHOW TABLES;"

# Verify columns were added
docker exec abedeport-db mysql -u root -p'root_strong_password' -e "USE ABEDEPORT; DESCRIBE blog_posts;" | grep -E "like_count|comment_count"
```

---

## Option 2: Using phpMyAdmin (Easier for non-technical users)

### Step 1: Access phpMyAdmin
- Go to your production phpMyAdmin URL
- Login with your credentials

### Step 2: Create a backup
1. Select the `ABEDEPORT` database
2. Click on "Export" tab
3. Choose "Quick" export method
4. Click "Go" to download the backup
5. Save the file with a date: `abedeport_backup_2025_01_26.sql`

### Step 3: Import the SQL file
1. Select the `ABEDEPORT` database
2. Click on "Import" tab
3. Click "Choose File" and select `likes_comments.sql`
4. Click "Go" at the bottom
5. Wait for success message

### Step 4: Verify
1. Check the "Structure" tab - you should see `post_likes` and `post_comments` tables
2. Click on `blog_posts` table → View structure → verify `like_count` and `comment_count` columns exist

---

## Option 3: Using MySQL Command Line (Direct Connection)

If your production MySQL is accessible remotely:

### Step 1: Create backup
```bash
# From your local machine
mysqldump -h your-production-server.com -u abedeport_user -p ABEDEPORT > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Apply the SQL file
```bash
mysql -h your-production-server.com -u abedeport_user -p ABEDEPORT < database/likes_comments.sql
```

---

## Option 4: One-Line Command (For Quick Production Updates)

If you're already SSH'd into production server:

```bash
# All in one command with backup
docker exec abedeport-db mysqldump -u root -p'root_strong_password' ABEDEPORT > backup_before_likes_$(date +%Y%m%d_%H%M%S).sql && \
docker exec -i abedeport-db mysql -u root -p'root_strong_password' ABEDEPORT < database/likes_comments.sql && \
echo "✅ SQL applied successfully!"
```

---

## Verification Checklist

After applying the SQL, verify everything works:

```bash
# 1. Check tables exist
docker exec abedeport-db mysql -u root -p'root_strong_password' -e "USE ABEDEPORT; SHOW TABLES LIKE 'post%';"

# Expected output:
# post_comments
# post_likes

# 2. Check blog_posts columns
docker exec abedeport-db mysql -u root -p'root_strong_password' -e "USE ABEDEPORT; SHOW COLUMNS FROM blog_posts LIKE '%count';"

# Expected output:
# like_count
# comment_count

# 3. Check sample data (should show 6 likes, 5 comments)
docker exec abedeport-db mysql -u root -p'root_strong_password' -e "USE ABEDEPORT; SELECT COUNT(*) FROM post_likes; SELECT COUNT(*) FROM post_comments;"

# 4. Verify counts are updated
docker exec abedeport-db mysql -u root -p'root_strong_password' -e "USE ABEDEPORT; SELECT id_post, titulo, like_count, comment_count FROM blog_posts;"
```

---

## Rollback Plan (If Something Goes Wrong)

### Restore from backup:
```bash
# Stop the application temporarily
docker-compose -f production.yml stop app

# Restore the backup
docker exec -i abedeport-db mysql -u root -p'root_strong_password' ABEDEPORT < backup_YYYYMMDD_HHMMSS.sql

# Start the application
docker-compose -f production.yml start app
```

---

## Important Notes

1. **Backup First**: ALWAYS create a backup before running SQL updates in production
2. **Test Locally**: You've already tested this in local, so it should work fine
3. **Minimal Downtime**: The SQL execution is fast (< 1 second), no downtime needed
4. **Check Dependencies**: The SQL file uses `IF NOT EXISTS` and `ON DUPLICATE KEY UPDATE`, so it's safe to run multiple times
5. **Foreign Keys**: The script properly handles foreign key constraints
6. **Indexes**: Performance indexes are automatically created

---

## What This SQL Does

1. Creates `post_likes` table - stores which users liked which posts
2. Creates `post_comments` table - stores user comments on posts
3. Adds `like_count` and `comment_count` columns to `blog_posts`
4. Creates indexes for better performance
5. Inserts sample data (6 likes, 5 comments)
6. Updates the count fields based on existing data

---

## After Applying

The like and comment functionality on your production website will start working immediately:
- Like buttons will show correct counts
- Users can add/remove likes
- Comment sections will display
- API endpoints at `/noticias/api/likes.php` will work

---

## Need Help?

If you encounter any errors:
1. Check the error message
2. Verify database credentials are correct
3. Check if MySQL container is running: `docker ps | grep db`
4. Look at MySQL logs: `docker logs abedeport-db`
5. Restore from backup if needed (see Rollback Plan above)

---

**Recommended Approach**: Use **Option 1** (Docker Exec) as it's the safest and most reliable method for production deployments.
