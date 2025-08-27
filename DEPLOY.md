# 🚀 Tournament Database Deployment

Simple deployment script that pushes pre-generated tournament data to the database.

## ✅ **Single Production Command**

```bash
./push-to-db.sh prod
```

## 📋 **All Available Commands**

```bash
# Local development
./push-to-db.sh local

# Production deployment  
./push-to-db.sh prod
```

## 🎯 **What Gets Deployed**

- **Liga de Futsal 2024:** 16 teams
- **Liga de Baloncesto 2024:** 4 teams  
- **Liga de Voleibol 2024:** 4 teams
- **Total:** 3 tournaments, 24 teams

## 📁 **Files**

- `tournaments_data.sql` - Pre-generated SQL with all tournament data
- `push-to-db.sh` - Simple deployment script
- No Python dependencies required!

## 🔐 **Production Setup (Optional)**

Set environment variables for custom passwords:
```bash
export DB_PASSWORD='your_secure_password'
export MYSQL_ROOT_PASSWORD='your_root_password'
./push-to-db.sh prod
```

## ✅ **Success Output**

The script will show:
- ✅ Import completed successfully!
- 📊 3 tournaments imported
- 🎯 24 teams imported
- 🌐 Production site URL

---

**One command, zero dependencies, ready to deploy!** 🎯