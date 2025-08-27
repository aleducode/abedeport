# 🏭 Production Tournament Import

Production-ready import system with enhanced security validations for deploying tournament data to production environment.

## 🔒 Security Features

- **Input sanitization** - Team names are sanitized to prevent SQL injection
- **Data validation** - Numeric ranges validated, duplicate positions removed
- **Transaction safety** - All operations wrapped in database transactions
- **Environment variables** - Supports secure password management
- **Enhanced logging** - Detailed validation reports and error handling

## 🚀 Quick Production Import

### Option 1: Using Environment Variables (Recommended)
```bash
export DB_PASSWORD='your_secure_production_password'
export MYSQL_ROOT_PASSWORD='your_secure_root_password'
./import-prod.sh
```

### Option 2: Using Default Passwords
```bash
./import-prod.sh
```

## 📋 Manual Production Import

### Step 1: Generate Production SQL
```bash
python3 generate-sql-prod.py
```

### Step 2: Start Production Database
```bash
# With environment variables
export DB_PASSWORD='your_production_password'
docker-compose -f production.yml up -d db

# Or with defaults
docker-compose -f production.yml up -d db
```

### Step 3: Import Data
```bash
# With environment variable
docker-compose -f production.yml exec -T db mysql -u abedeport_user -p$DB_PASSWORD ABEDEPORT < tournament_import_prod.sql

# Or with default password
docker-compose -f production.yml exec -T db mysql -u abedeport_user -pabedeport_strong_password ABEDEPORT < tournament_import_prod.sql
```

## 🎯 What Gets Imported (Production Data)

- **Liga de Futsal 2024:** 16 teams with validated statistics
- **Liga de Baloncesto 2024:** 4 teams with cleaned data
- **Liga de Voleibol 2024:** 4 teams with secure team names
- **Enhanced validation:** All data sanitized and range-checked

## ✅ Production Verification

After import, verify the data:
```bash
docker-compose -f production.yml exec -T db mysql -u abedeport_user -p$DB_PASSWORD ABEDEPORT -e "
    SELECT 'Tournaments:' as Type, COUNT(*) as Count FROM tournaments
    UNION ALL
    SELECT 'Teams:', COUNT(*) FROM equipos_tournament;
"
```

## 🌐 Production Access

After successful import:
- **Main Site:** https://abedeport.abejorralmuchopueblo.com
- **Admin Panel:** https://abedeport.abejorralmuchopueblo.com/admin  
- **Traefik Dashboard:** Available on port 8080 (if enabled)

## 🔐 Security Recommendations

### Before Production Deploy:
1. **Change default passwords** in production.yml or use environment variables
2. **Enable SSL certificates** via Traefik configuration
3. **Set up automated backups** for the MySQL database
4. **Configure firewall rules** to restrict database access
5. **Review application logs** regularly

### Environment Variables Setup:
```bash
# Create production environment file
echo "DB_PASSWORD=your_secure_password_here" > .env.production
echo "MYSQL_ROOT_PASSWORD=your_secure_root_password_here" >> .env.production

# Load before running
source .env.production
./import-prod.sh
```

## 🚨 Production Safety Features

- **Confirmation Required:** Script requires typing 'PRODUCTION' to proceed
- **Transaction Rollback:** Failed imports are automatically rolled back
- **Data Validation:** Invalid data is rejected with detailed error messages
- **Backup Friendly:** Import process is designed to work with backup systems

## 🔄 Re-importing Data

To update tournament data in production:
1. Update Excel files in `tablas/` directory
2. Run the import script again - it will clean and reload all data
3. Verify data integrity after import

---

**⚠️ Important:** Always test imports in a staging environment before running in production!