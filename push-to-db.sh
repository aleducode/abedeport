#!/bin/bash

# Simple Database Push Script
# Usage: ./push-to-db.sh [environment]
# Environment: local (default) or prod

ENVIRONMENT=${1:-local}

echo "🚀 Database Push Script"
echo "======================"
echo "Environment: $ENVIRONMENT"
echo ""

# Check if tournaments_data.sql exists
if [ ! -f "tournaments_data.sql" ]; then
    echo "❌ tournaments_data.sql file not found"
    exit 1
fi

case $ENVIRONMENT in
    "local")
        echo "📍 Pushing to LOCAL database..."
        echo "Config: local.yml"
        echo "User: abedeport_user"
        echo "Password: abedeport_local_password"
        echo ""
        
        # Check if local database is running
        if ! sudo docker-compose -f local.yml ps db | grep -q "Up"; then
            echo "🚀 Starting local database..."
            sudo docker-compose -f local.yml up -d db
            
            echo "⏳ Waiting for database to be ready..."
            sleep 10
        fi
        
        echo "💾 Importing tournament data..."
        sudo docker-compose -f local.yml exec -T db mysql -u abedeport_user -pabedeport_local_password ABEDEPORT < tournaments_data.sql
        
        if [ $? -eq 0 ]; then
            echo ""
            echo "✅ LOCAL import completed successfully!"
            echo ""
            echo "🌐 Access points:"
            echo "- Main site: http://localhost:8080"
            echo "- phpMyAdmin: http://localhost:8081"
        else
            echo "❌ LOCAL import failed"
            exit 1
        fi
        ;;
        
    "prod")
        echo "📍 Pushing to PRODUCTION database..."
        echo "Config: production.yml"
        echo "User: abedeport_user"
        echo ""
        
        read -p "⚠️  Are you sure you want to push to PRODUCTION? (type 'YES'): " confirm
        
        if [[ $confirm != "YES" ]]; then
            echo "❌ Production push cancelled"
            exit 0
        fi
        
        # Load environment variables from .env file
        if [ -f ".env" ]; then
            echo "📄 Loading environment from .env file..."
            export $(grep -v '^#' .env | xargs)
        fi
        
        # Set up environment variables
        DB_PASSWORD=${DB_PASSWORD:-abedeport_strong_password}
        MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD:-root_strong_password}
        
        echo "🔐 Using DB_PASSWORD: ${DB_PASSWORD:0:3}***"
        echo ""
        
        # Check if production database is running
        if ! sudo docker-compose -f production.yml ps db | grep -q "Up"; then
            echo "🚀 Starting production database..."
            sudo docker-compose -f production.yml up -d db
            
            echo "⏳ Waiting for production database to be ready..."
            for i in {1..30}; do
                if sudo docker-compose -f production.yml exec -T db mysqladmin ping -h localhost -u root -p$MYSQL_ROOT_PASSWORD > /dev/null 2>&1; then
                    echo "✅ Production database is ready!"
                    break
                fi
                if [ $i -eq 30 ]; then
                    echo "❌ Timeout waiting for database"
                    exit 1
                fi
                echo -n "."
                sleep 2
            done
            echo ""
        fi
        
        echo "💾 Importing tournament data to production..."
        sudo docker-compose -f production.yml exec -T db mysql -u abedeport_user -p$DB_PASSWORD ABEDEPORT < tournaments_data.sql
        
        if [ $? -eq 0 ]; then
            echo ""
            echo "✅ PRODUCTION import completed successfully!"
            echo ""
            echo "🌐 Production site: https://abedeport.abejorralmuchopueblo.com"
            echo "🔧 Admin panel: https://abedeport.abejorralmuchopueblo.com/admin"
        else
            echo "❌ PRODUCTION import failed"
            exit 1
        fi
        ;;
        
    *)
        echo "❌ Invalid environment: $ENVIRONMENT"
        echo "Usage: $0 [local|prod]"
        exit 1
        ;;
esac

echo ""
echo "🎯 Import Complete!"