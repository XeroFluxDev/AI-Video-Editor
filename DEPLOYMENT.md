# Deployment Guide - AI Video Editor

Complete guide for deploying the AI Video Editor to production environments.

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [Server Requirements](#server-requirements)
3. [Deployment Options](#deployment-options)
4. [Production Configuration](#production-configuration)
5. [Security Hardening](#security-hardening)
6. [Performance Optimization](#performance-optimization)
7. [Monitoring & Maintenance](#monitoring--maintenance)

## Pre-Deployment Checklist

- [ ] PHP 8.1+ installed
- [ ] FFmpeg 4.x+ installed
- [ ] Composer dependencies installed
- [ ] API keys configured
- [ ] Storage directories created with correct permissions
- [ ] Web server configured
- [ ] SSL certificate installed (HTTPS)
- [ ] Firewall rules configured
- [ ] Backup strategy in place

## Server Requirements

### Minimum Specifications

- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 50GB SSD
- **Bandwidth**: 100Mbps

### Recommended Specifications

- **CPU**: 4+ cores
- **RAM**: 8GB+
- **Storage**: 100GB+ SSD
- **Bandwidth**: 1Gbps

### Software Requirements

- **OS**: Ubuntu 20.04 LTS / 22.04 LTS (recommended)
- **PHP**: 8.1+ with extensions: `php-cli`, `php-fpm`, `php-mbstring`, `php-xml`, `php-curl`
- **Web Server**: Nginx (recommended) or Apache 2.4+
- **FFmpeg**: 4.x or higher
- **Composer**: 2.x

## Deployment Options

### Option 1: VPS/Cloud Server (Recommended)

#### DigitalOcean

```bash
# Create Droplet (Ubuntu 22.04, 4GB RAM)
# SSH into server
ssh root@your-server-ip

# Update system
apt update && apt upgrade -y

# Install requirements
apt install -y php8.1-fpm php8.1-cli php8.1-mbstring php8.1-xml php8.1-curl \
    nginx ffmpeg composer git unzip

# Clone repository
cd /var/www
git clone https://github.com/XeroFluxDev/AI-Video-Editor.git
cd AI-Video-Editor

# Install dependencies
cd app
composer install --no-dev --optimize-autoloader

# Setup environment
cp ../.env.example .env
nano .env  # Add API keys

# Create directories
mkdir -p storage/{uploads,temp,exports,ai-cache}
chmod -R 775 storage
chown -R www-data:www-data storage

# Configure Nginx (see below)
```

#### AWS EC2

```bash
# Launch EC2 instance (t3.medium, Ubuntu 22.04)
# SSH into instance
ssh -i your-key.pem ubuntu@your-server-ip

# Follow same steps as DigitalOcean above
```

#### Google Cloud Platform

```bash
# Create Compute Engine instance
# SSH via browser or gcloud CLI
# Follow same steps as above
```

### Option 2: Shared Hosting

**Note**: Shared hosting has limitations. Verify these requirements:
- PHP 8.1+ available
- Command-line access (SSH)
- Ability to install Composer packages
- FFmpeg available or can be installed
- Sufficient storage space

```bash
# Upload files via FTP/SFTP
# SSH into shared hosting
cd public_html
git clone https://github.com/XeroFluxDev/AI-Video-Editor.git
# Or upload ZIP and extract

# Install dependencies
cd AI-Video-Editor/app
composer install

# Configure .env
cp ../.env.example .env
nano .env

# Adjust paths in config files if needed
```

### Option 3: Docker (Advanced)

```dockerfile
# Dockerfile
FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    ffmpeg \
    nginx \
    && docker-php-ext-install pdo_mysql

WORKDIR /var/www/html
COPY app/ /var/www/html/
RUN composer install --no-dev --optimize-autoloader

EXPOSE 80
CMD ["php-fpm"]
```

```yaml
# docker-compose.yml
version: '3'
services:
  web:
    build: .
    ports:
      - "80:80"
    volumes:
      - ./storage:/var/www/html/storage
    environment:
      - OPENROUTER_API_KEY=${OPENROUTER_API_KEY}
      - OPENAI_API_KEY=${OPENAI_API_KEY}
```

## Production Configuration

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/AI-Video-Editor/app/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # File upload size
    client_max_body_size 500M;
    client_body_timeout 600s;

    # Timeouts
    proxy_connect_timeout 600;
    proxy_send_timeout 600;
    proxy_read_timeout 600;
    send_timeout 600;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_read_timeout 600;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ /storage/ {
        deny all;
    }

    # Allow storage/exports for downloads
    location ~ /storage/exports/ {
        allow all;
    }
}
```

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/AI-Video-Editor/app/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem

    <Directory /var/www/AI-Video-Editor/app/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"

    # PHP Settings
    php_value upload_max_filesize 500M
    php_value post_max_size 500M
    php_value max_execution_time 600
    php_value memory_limit 512M
</VirtualHost>
```

### PHP Configuration (php.ini)

```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 600
max_input_time = 600
memory_limit = 512M
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
```

### Environment Variables (.env)

```env
APP_ENV=production
APP_DEBUG=false

FFMPEG_PATH=/usr/bin/ffmpeg
FFPROBE_PATH=/usr/bin/ffprobe

OPENROUTER_API_KEY=your_actual_key_here
OPENAI_API_KEY=your_actual_key_here
```

## Security Hardening

### 1. File Permissions

```bash
# Set correct ownership
chown -R www-data:www-data /var/www/AI-Video-Editor

# Set directory permissions
find /var/www/AI-Video-Editor -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/AI-Video-Editor -type f -exec chmod 644 {} \;

# Storage directories
chmod -R 775 /var/www/AI-Video-Editor/app/storage
```

### 2. Firewall Configuration

```bash
# UFW (Ubuntu)
ufw allow 22/tcp   # SSH
ufw allow 80/tcp   # HTTP
ufw allow 443/tcp  # HTTPS
ufw enable
```

### 3. SSL Certificate (Let's Encrypt)

```bash
apt install certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com -d www.yourdomain.com
certbot renew --dry-run  # Test auto-renewal
```

### 4. Disable Directory Listing

Add to `.htaccess` or Nginx config:
```
Options -Indexes
```

### 5. Rate Limiting

Nginx:
```nginx
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;

location /api/ {
    limit_req zone=api burst=20;
}
```

### 6. Hide Server Information

Nginx:
```nginx
server_tokens off;
```

Apache:
```apache
ServerTokens Prod
ServerSignature Off
```

## Performance Optimization

### 1. Enable OPcache

`/etc/php/8.1/fpm/conf.d/10-opcache.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 2. PHP-FPM Tuning

`/etc/php/8.1/fpm/pool.d/www.conf`:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

### 3. Caching Strategy

- Enable browser caching for static assets
- Use Redis/Memcached for session storage (optional)
- Implement CDN for large files (optional)

### 4. Database Optimization (if added)

```sql
-- Add indexes
-- Optimize queries
-- Regular maintenance
```

## Monitoring & Maintenance

### 1. Log Files

```bash
# Application logs
/var/www/AI-Video-Editor/app/storage/logs/

# Nginx logs
/var/log/nginx/access.log
/var/log/nginx/error.log

# PHP logs
/var/log/php8.1-fpm.log
```

### 2. Monitoring Tools

- **Uptime**: UptimeRobot, Pingdom
- **Server**: Netdata, Grafana
- **Errors**: Sentry, Rollbar

### 3. Backup Strategy

```bash
# Backup script (backup.sh)
#!/bin/bash
DATE=$(date +%Y%m%d)
BACKUP_DIR="/backups"

# Backup files
tar -czf $BACKUP_DIR/files-$DATE.tar.gz /var/www/AI-Video-Editor

# Backup storage
tar -czf $BACKUP_DIR/storage-$DATE.tar.gz /var/www/AI-Video-Editor/app/storage

# Clean old backups (keep 7 days)
find $BACKUP_DIR -type f -mtime +7 -delete
```

Add to crontab:
```bash
0 2 * * * /path/to/backup.sh
```

### 4. Auto-Cleanup Old Files

```bash
# cleanup.sh
#!/bin/bash
find /var/www/AI-Video-Editor/app/storage/temp -type f -mtime +1 -delete
find /var/www/AI-Video-Editor/app/storage/uploads -type f -mtime +7 -delete
find /var/www/AI-Video-Editor/app/storage/exports -type f -mtime +3 -delete
```

Add to crontab:
```bash
0 3 * * * /path/to/cleanup.sh
```

### 5. Updates

```bash
# Update application
cd /var/www/AI-Video-Editor
git pull origin main
cd app
composer install --no-dev --optimize-autoloader

# Update system packages
apt update && apt upgrade -y

# Restart services
systemctl restart nginx
systemctl restart php8.1-fpm
```

## Troubleshooting

### Issue: 502 Bad Gateway

```bash
# Check PHP-FPM status
systemctl status php8.1-fpm

# Check logs
tail -f /var/log/nginx/error.log
tail -f /var/log/php8.1-fpm.log

# Restart PHP-FPM
systemctl restart php8.1-fpm
```

### Issue: Upload Fails

```bash
# Check PHP settings
php -i | grep upload_max_filesize
php -i | grep post_max_size

# Check Nginx settings
grep client_max_body_size /etc/nginx/nginx.conf
```

### Issue: Permission Denied

```bash
# Fix permissions
chown -R www-data:www-data /var/www/AI-Video-Editor/app/storage
chmod -R 775 /var/www/AI-Video-Editor/app/storage
```

## Scaling

### Horizontal Scaling

- Use load balancer (Nginx, HAProxy)
- Shared storage (NFS, S3)
- Session storage (Redis, Memcached)
- Queue system for background processing

### Vertical Scaling

- Increase server resources (CPU, RAM)
- Optimize PHP-FPM pool settings
- Enable OPcache and APCu

## Cost Estimates

### Monthly Hosting Costs

| Provider | Plan | CPU | RAM | Storage | Bandwidth | Cost |
|----------|------|-----|-----|---------|-----------|------|
| DigitalOcean | Droplet | 2 | 4GB | 80GB | 4TB | $24/mo |
| AWS EC2 | t3.medium | 2 | 4GB | 30GB | 1TB | ~$35/mo |
| Vultr | Cloud Compute | 2 | 4GB | 80GB | 3TB | $18/mo |
| Linode | Shared CPU | 2 | 4GB | 80GB | 4TB | $24/mo |

### API Costs

- **OpenRouter** (Claude 3.5 Sonnet): ~$3-15 per million tokens
- **OpenAI** (Whisper): $0.006 per minute of audio

## Support

For deployment issues:
- Check logs first
- Review this guide
- GitHub Issues: https://github.com/XeroFluxDev/AI-Video-Editor/issues

---

**Last Updated:** 2025-11-12
