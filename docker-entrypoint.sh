#!/bin/bash
set -e

echo "Starting container initialization..."

# Ensure storage directories exist with correct permissions
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/storage/framework/{cache,sessions,views}
mkdir -p /var/www/html/storage/logs

# Fix permissions
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Remove existing storage symlink/directory if exists
if [ -e /var/www/html/public/storage ] || [ -L /var/www/html/public/storage ]; then
    rm -rf /var/www/html/public/storage
fi

# Create storage link
php artisan storage:link

# Clear and cache config
php artisan config:clear
php artisan config:cache

echo "Storage link created successfully!"
echo "Container initialization complete."

# Execute the main command
exec "$@"
