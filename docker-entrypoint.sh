#!/bin/bash

# Set ServerName to suppress Apache warning
echo "ServerName abedeport.abejorralmuchopueblo.com" >> /etc/apache2/apache2.conf

# Enable Apache modules
a2enmod rewrite
a2enmod php8.2

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Start Apache in foreground
exec apache2-foreground