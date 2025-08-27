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

## 🔐 **Production Setup**

1. **Create .env file with your passwords:**
```bash
cp .env.example .env
nano .env
```

2. **Edit .env file:**
```bash
DB_PASSWORD=your_secure_production_password
MYSQL_ROOT_PASSWORD=your_secure_root_password
```

3. **Deploy:**
```bash
sudo ./push-to-db.sh prod
```

## ✅ **Success Output**

The script will show:
- ✅ Import completed successfully!
- 📊 3 tournaments imported
- 🎯 24 teams imported
- 🌐 Production site URL

---

**One command, zero dependencies, ready to deploy!** 🎯