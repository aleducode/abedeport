#!/bin/bash

echo "🧪 Testing ABE Deportes site..."
echo ""

echo "📊 Container Status:"
sudo docker-compose -f production.yml ps
echo ""

echo "🌐 Testing internal app response:"
sudo docker-compose -f production.yml exec app curl -I http://localhost/ 2>/dev/null || echo "❌ App not responding internally"
echo ""

echo "🔗 Testing from Traefik to App:"
sudo docker-compose -f production.yml exec traefik wget -qO- --timeout=5 http://app:80/ | head -10 2>/dev/null || echo "❌ Cannot reach app from traefik"
echo ""

echo "🌍 Testing external HTTPS:"
curl -I https://abedeport.abejorralmuchopueblo.com/ 2>/dev/null || echo "❌ HTTPS not responding"
echo ""

echo "📝 Recent app logs:"
sudo docker-compose -f production.yml logs --tail=5 app