#!/bin/bash

# Complete Tournament Deployment Script
# Supports both local and production environments

echo "🏆 Tournament Data Deployment"
echo "============================="
echo ""
echo "Choose environment:"
echo "1. Local Development (local.yml)"
echo "2. Production (production.yml)"
echo "3. Exit"
echo ""

read -p "Select environment (1-3): " choice

case $choice in
    1)
        echo ""
        echo "🛠️  Local Development Import"
        echo "============================="
        echo ""
        
        # Local import
        echo "Starting local database..."
        docker-compose -f local.yml up -d db
        
        echo "Generating SQL from Excel files..."
        python3 generate-sql.py
        
        if [ $? -ne 0 ]; then
            echo "❌ Failed to generate SQL"
            exit 1
        fi
        
        echo "Importing to local database..."
        docker-compose -f local.yml exec -T db mysql -u abedeport_user -pabedeport_local_password ABEDEPORT < tournament_import.sql
        
        if [ $? -eq 0 ]; then
            echo ""
            echo "✅ Local import completed successfully!"
            echo ""
            echo "🌐 Access your local site:"
            echo "- Main site: http://localhost:8080"
            echo "- phpMyAdmin: http://localhost:8081"
            echo ""
            
            # Show summary
            echo "📊 Import Summary:"
            docker-compose -f local.yml exec -T db mysql -u abedeport_user -pabedeport_local_password ABEDEPORT -e "
                SELECT t.nombre as Tournament, COUNT(et.id_equipo_tournament) as Teams
                FROM tournaments t 
                LEFT JOIN equipos_tournament et ON t.id_tournament = et.id_tournament 
                GROUP BY t.id_tournament;
            " 2>/dev/null
        else
            echo "❌ Local import failed"
            exit 1
        fi
        ;;
        
    2)
        echo ""
        echo "🏭 Production Import"
        echo "===================="
        echo ""
        echo "⚠️  WARNING: This will deploy to PRODUCTION environment!"
        echo ""
        
        read -p "Are you absolutely sure? Type 'DEPLOY' to continue: " confirm
        
        if [[ $confirm != "DEPLOY" ]]; then
            echo "❌ Production deployment cancelled"
            exit 0
        fi
        
        # Check if production files exist
        if [ ! -f "production.yml" ]; then
            echo "❌ production.yml not found"
            exit 1
        fi
        
        # Run production import
        ./import-prod.sh
        ;;
        
    3)
        echo "Goodbye!"
        exit 0
        ;;
        
    *)
        echo "❌ Invalid option"
        exit 1
        ;;
esac