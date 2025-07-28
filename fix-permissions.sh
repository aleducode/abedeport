#!/bin/bash
chown -R www-data:www-data /var/www/html/admin /var/www/html/assets /var/www/html/noticias
chmod -R 755 /var/www/html/admin /var/www/html/assets /var/www/html/noticias
exec "$@"