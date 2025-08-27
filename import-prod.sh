#!/bin/bash

# Production Tournament Import Script
# Usage: ./import-prod.sh

echo "🏭 Production Tournament Import"
echo "=============================="
echo "⚠️  WARNING: This will import data to PRODUCTION environment!"
echo ""

# Check if we're really ready for production
read -p "Are you sure you want to import to PRODUCTION? (type 'PRODUCTION' to confirm): " confirm

if [[ $confirm != "PRODUCTION" ]]; then
    echo "❌ Import cancelled. Production import requires typing 'PRODUCTION' to confirm."
    exit 0
fi

# Check if Docker is available
if ! command -v docker-compose &> /dev/null; then
    echo "❌ docker-compose is not installed"
    exit 1
fi

# Check if production.yml exists
if [ ! -f "production.yml" ]; then
    echo "❌ production.yml file not found"
    exit 1
fi

echo ""
echo "🔐 Production Environment Setup"
echo "=============================="

# Check for environment variables
if [ -z "$DB_PASSWORD" ]; then
    echo "⚠️  DB_PASSWORD environment variable not set"
    echo "Using default production password"
    DB_PASSWORD="abedeport_strong_password"
else
    echo "✅ Using DB_PASSWORD from environment"
fi

if [ -z "$MYSQL_ROOT_PASSWORD" ]; then
    echo "⚠️  MYSQL_ROOT_PASSWORD environment variable not set"
    echo "Using default root password"
    MYSQL_ROOT_PASSWORD="root_strong_password"
else
    echo "✅ Using MYSQL_ROOT_PASSWORD from environment"
fi

echo ""
echo "🚀 Starting Production Import Process"
echo "====================================="

# Step 1: Generate production SQL
echo "1️⃣  Generating production SQL with security validations..."
python3 generate-sql-prod.py

if [ $? -ne 0 ]; then
    echo "❌ Failed to generate production SQL"
    exit 1
fi

if [ ! -f "tournament_import_prod.sql" ]; then
    echo "❌ Production SQL file not created"
    exit 1
fi

echo "✅ Production SQL generated successfully"
echo ""

# Step 2: Start production database
echo "2️⃣  Starting production database..."
export DB_PASSWORD=$DB_PASSWORD
export MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD

docker-compose -f production.yml up -d db

if [ $? -ne 0 ]; then
    echo "❌ Failed to start production database"
    exit 1
fi

echo "✅ Production database started"
echo ""

# Step 3: Wait for database to be ready
echo "3️⃣  Waiting for production database to be ready..."
echo "⏳ This may take up to 60 seconds..."

for i in {1..30}; do
    if docker-compose -f production.yml exec -T db mysqladmin ping -h localhost -u root -p$MYSQL_ROOT_PASSWORD > /dev/null 2>&1; then
        echo "✅ Production database is ready!"
        break
    fi
    if [ $i -eq 30 ]; then
        echo "❌ Timeout waiting for database to be ready"
        exit 1
    fi
    echo -n "."
    sleep 2
done
echo ""

# Step 4: Import data
echo "4️⃣  Importing tournament data to production..."
docker-compose -f production.yml exec -T db mysql -u abedeport_user -p$DB_PASSWORD ABEDEPORT < tournament_import_prod.sql

if [ $? -ne 0 ]; then
    echo "❌ Failed to import tournament data"
    exit 1
fi

echo "✅ Tournament data imported successfully"
echo ""

# Step 5: Verify import
echo "5️⃣  Verifying production data..."
echo "📊 Production Database Summary:"
echo "================================"

docker-compose -f production.yml exec -T db mysql -u abedeport_user -p$DB_PASSWORD ABEDEPORT -e "
    SELECT 'Tournaments:' as Type, COUNT(*) as Count FROM tournaments
    UNION ALL
    SELECT 'Teams:', COUNT(*) FROM equipos_tournament;
"

echo ""
echo "🎯 Tournament Breakdown:"
docker-compose -f production.yml exec -T db mysql -u abedeport_user -p$DB_PASSWORD ABEDEPORT -e "
    SELECT t.nombre as Tournament, t.deporte as Sport, COUNT(et.id_equipo_tournament) as Teams
    FROM tournaments t 
    LEFT JOIN equipos_tournament et ON t.id_tournament = et.id_tournament 
    GROUP BY t.id_tournament;
"

echo ""
echo "✅ Production import completed successfully!"
echo ""
echo "🌐 Access your production site:"
echo "- Main site: https://abedeport.abejorralmuchopueblo.com"
echo "- Admin panel: https://abedeport.abejorralmuchopueblo.com/admin"
echo ""
echo "🔒 Security Recommendations:"
echo "- Change default passwords immediately"
echo "- Enable SSL certificates"
echo "- Review database access logs"
echo "- Set up automated backups"