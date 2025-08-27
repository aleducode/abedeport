# 🏆 Tournament Data Import

Reads real tournament data from Excel files in `tablas/` directory and imports into the database.

## 🚀 Quick Import (One Command)

```bash
docker-compose -f local.yml up -d db && python3 generate-sql.py && docker-compose -f local.yml exec -T db mysql -u abedeport_user -pabedeport_local_password ABEDEPORT < tournament_import.sql
```

## 📋 Step by Step

1. **Start the database:**
   ```bash
   docker-compose -f local.yml up -d db
   ```

2. **Generate SQL from Excel files:**
   ```bash
   python3 generate-sql.py
   ```

3. **Import to database:**
   ```bash
   docker-compose -f local.yml exec -T db mysql -u abedeport_user -pabedeport_local_password ABEDEPORT < tournament_import.sql
   ```

## 🎯 What Gets Imported (Real Data)

- **Liga de Futsal 2024:** 16 teams (PANTANILLO, PURIMA, CABUYA F.C, DIESEL, etc.)
- **Liga de Baloncesto 2024:** 4 teams (Guerreros, Cabuya, Dinasty Club, Los Profes)
- **Liga de Voleibol 2024:** 4 teams (Equipo Nro 4, The Team, Cabuya, etc.)
- **Real statistics:** Actual games played, wins, losses, points from Excel files

## ✅ Verification

Check the data was imported:
```bash
docker-compose -f local.yml exec -T db mysql -u abedeport_user -pabedeport_local_password ABEDEPORT -e "SELECT COUNT(*) as tournaments FROM tournaments; SELECT COUNT(*) as teams FROM equipos_tournament;"
```

## 🔄 Re-import

To clean and re-import data, just run the same command again. The SQL script automatically cleans existing data first.

## 🌐 View in Browser

After import, view tournaments at:
- Main site: http://localhost:8080
- phpMyAdmin: http://localhost:8081

---

**Note:** This method is fast, reliable, and works consistently with the docker-compose setup.