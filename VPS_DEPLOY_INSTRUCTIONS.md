# Instruksi Deploy & Fix untuk VPS

## LANGKAH 1: Update Kode
```bash
cd /path/to/rtrw-be
git pull
composer install
php artisan migrate
php artisan optimize:clear
```

## LANGKAH 2: Buat Folder Temp
```bash
mkdir -p storage/app/public/temp
chmod -R 775 storage
chown -R www-data:www-data storage
```

## LANGKAH 3: Jalankan Queue Worker
```bash
# Test dulu (foreground)
php artisan queue:work

# Kalau OK, jalankan background:
nohup php artisan queue:work --sleep=3 --tries=3 > storage/logs/queue.log 2>&1 &
```

---

## LANGKAH 4: Fix PHP Config

Edit file `/etc/php/[versi]/fpm/php.ini` atau `/www/server/php/[versi]/etc/php.ini`:

```ini
upload_max_filesize = 128M
post_max_size = 128M
max_execution_time = 600
max_input_time = 600
memory_limit = 512M
```

## LANGKAH 5: Fix Nginx Config

Edit nginx config untuk domain `malikbe.matlhy.my.id`:

```nginx
server {
    # ... existing config ...
    
    client_max_body_size 128M;
    client_body_timeout 600s;
    proxy_read_timeout 600s;
    proxy_connect_timeout 600s;
    fastcgi_read_timeout 600s;
    
    # ... rest of config ...
}
```

## LANGKAH 6: Restart Services
```bash
sudo systemctl restart php-fpm
# atau: sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

---

## LANGKAH 7: Verifikasi

```bash
# Cek PHP settings
php -i | grep -E "(upload_max|post_max|memory_limit)"

# Cek nginx config
nginx -t

# Cek Laravel logs
tail -50 storage/logs/laravel.log

# Test endpoint
curl -X GET https://malikbe.matlhy.my.id/api/role/guest
```

---

## Jika Masih Error

Kirimkan output dari:
```bash
# PHP version dan settings
php -v
php -i | grep -E "(upload|post_max|memory|execution)"

# Nginx error log
sudo tail -100 /var/log/nginx/error.log

# Laravel error log
tail -100 storage/logs/laravel.log

# Check permissions
ls -la storage/app/public/
```
