#!/bin/bash

# ABE Deportes Production Deployment Script

set -e

echo "🚀 Starting ABE Deportes production deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env file exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  .env file not found. Creating from template...${NC}"
    if [ -f .env.production ]; then
        cp .env.production .env
    fi
    echo -e "${RED}❌ Please edit .env file with your production values before continuing!${NC}"
    exit 1
fi

# Create necessary directories
echo -e "${YELLOW}📁 Creating required directories...${NC}"
mkdir -p compose/production/traefik

# Pull latest images
echo -e "${YELLOW}📥 Pulling latest Docker images...${NC}"
docker-compose -f production.yml pull

# Stop existing containers
echo -e "${YELLOW}🛑 Stopping existing containers...${NC}"
docker-compose -f production.yml down

# Build and start services
echo -e "${YELLOW}🔨 Building and starting services...${NC}"
docker-compose -f production.yml up -d --build

# Wait for services to be ready
echo -e "${YELLOW}⏳ Waiting for services to be ready...${NC}"
sleep 30

# Check service health
echo -e "${YELLOW}🏥 Checking service health...${NC}"
docker-compose -f production.yml ps

# Show service URLs
echo -e "${GREEN}✅ Deployment completed!${NC}"
echo ""
echo "📊 Service URLs:"
echo "🌐 Main site: https://abedeport.abejorralmuchopueblo.com"
echo "🗄️  phpMyAdmin: https://pma.abedeport.abejorralmuchopueblo.com"
echo ""
echo "📝 Logs:"
echo "   docker-compose -f production.yml logs -f"
echo ""
echo "🔧 Management:"
echo "   docker-compose -f production.yml ps       # Check status"
echo "   docker-compose -f production.yml down     # Stop all services"
echo "   docker-compose -f production.yml restart  # Restart all services" 