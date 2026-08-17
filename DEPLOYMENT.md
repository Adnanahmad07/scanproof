# ScanProof - Live Deployment Guide

## The Email Link Problem (Fixed!)

**The Issue:** Invitation emails generate URLs using `APP_URL`. If `APP_URL=http://localhost`, the email link will be `http://localhost/supervisor/set-password/{token}` — only works on your machine.

**The Fix:** Set `APP_URL=https://your-real-domain.com` in `.env` on the server. The `url()` helper will then generate correct production links.

---

## Quick Deployment Options (Recommended for Client Testing)

### Option 1: Laravel Cloud (Easiest)
```
1. Push code to GitHub
2. Go to cloud.laravel.com
3. Connect GitHub repo
4. Set environment variables (APP_URL, DB, etc.)
5. Deploy — done!
```
**Cost:** Free tier available, then ~$20/month

### Option 2: Railway.app (Fast & Cheap)
```
1. Push code to GitHub
2. Go to railway.app
3. New Project > Deploy from GitHub
4. Add MySQL database
5. Set environment variables
6. Deploy
```
**Cost:** Free $5/month credit

### Option 3: DigitalOcean App Platform
```
1. Push code to GitHub
2. DigitalOcean > App Platform > Create App
3. Select repo
4. Add managed MySQL database
5. Set env vars
6. Deploy
```
**Cost:** ~$12/month

### Option 4: Traditional VPS (Full Control)
```
Server: Ubuntu 22.04 LTS
Requirements: PHP 8.2+, MySQL 8.0, Nginx, Composer, Node.js
```

---

## Deployment Checklist

### 1. Server Requirements
- PHP 8.2+ with extensions: mbstring, xml, curl, zip, bcmath, gd, pdo_mysql
- MySQL 8.0+
- Composer
- Node.js 18+ (for building assets)
- Nginx or Apache

### 2. Environment Setup
```bash
# On server
cd /var/www/scanproof
cp .env.production .env

# Generate app key
php artisan key:generate

# Edit .env with real values
nano .env
```

### 3. Critical .env Values
```env
APP_NAME="ScanProof"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://scanproof.yourdomain.com    # MUST be your real domain!

# Gmail SMTP (for testing)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password              # NOT your regular Gmail password!
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="ScanProof"
```

### 4. Gmail App Password Setup
```
1. Go to myaccount.google.com
2. Security > 2-Step Verification (enable it)
3. Search "App passwords"
4. Generate password for "Mail"
5. Use this 16-character password in .env
```

### 5. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE scanproof CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate --force

# Seed admin user
php artisan db:seed --force
```

### 6. Build Assets
```bash
npm install
npm run build
```

### 7. Permissions
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 8. Queue Worker (Background Jobs)
```bash
# Install Supervisor
sudo apt install supervisor

# Create worker config
sudo nano /etc/supervisor/conf.d/scanproof-worker.conf
```

Add this config:
```ini
[program:scanproof-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/scanproof/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/scanproof/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start scanproof-worker:*
```

### 9. Nginx Config
```nginx
server {
    listen 80;
    server_name scanproof.yourdomain.com;
    root /var/www/scanproof/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 10. SSL Certificate (HTTPS)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d scanproof.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

---

## After Deployment - Testing Checklist

### Test Authentication
- [ ] Register new user → should NOT be admin
- [ ] Login with admin@scanproof.com / password
- [ ] Logout works

### Test Supervisor Invitation (CRITICAL)
1. Login as admin
2. Go to `/admin/supervisors`
3. Invite a supervisor (use your real email)
4. Check email → link should be `https://your-domain.com/supervisor/set-password/{token}`
5. Click link → should show password setup form
6. Set password → should redirect to supervisor dashboard

### Test Dashboards
- [ ] Admin dashboard loads at `/admin/dashboard`
- [ ] Supervisor dashboard loads at `/supervisor/dashboard`
- [ ] Staff dashboard loads at `/staff/dashboard`

### Test Role-Based Access
- [ ] Staff cannot access `/admin/*`
- [ ] Supervisor cannot access `/admin/*`
- [ ] Guest redirected to login

---

## Common Issues & Fixes

### Issue: Email links still show localhost
**Fix:** Clear config cache:
```bash
php artisan config:clear
php artisan cache:clear
```

### Issue: Emails not sending
**Fix:** Check mail logs:
```bash
tail -f storage/logs/laravel.log
```

### Issue: 500 Error
**Fix:** Check storage permissions:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue: CSRF token mismatch
**Fix:** Clear session:
```bash
php artisan session:table
php artisan migrate
```

---

## Recommended: Quick Test with ngrok (No Server Needed)

If you just want to test quickly without deploying:

```bash
# Install ngrok
# Download from ngrok.com

# Start Laravel
php artisan serve

# In another terminal, start ngrok
ngrok http 8000

# Use the https URL ngrok gives you
# Set APP_URL to that ngrok URL in .env
```

**Note:** ngrok URLs change每次重启，适合快速测试，不适合正式给client。

---

## Production Domain Setup

1. Buy domain (e.g., from Namecheap, Cloudflare)
2. Point domain to your server IP:
   - A Record: `@` → `your-server-ip`
   - CNAME: `www` → `your-domain.com`
3. Update `.env` with real domain
4. Run SSL setup
5. Test invitation emails

---

## Summary

**For Client Testing:** Use Railway.app or DigitalOcean App Platform — cheapest and fastest.

**Key Points:**
1. Set `APP_URL=https://your-real-domain.com` in `.env`
2. Use Gmail App Password for SMTP
3. Run `php artisan config:clear` after changing APP_URL
4. Test supervisor invitation flow first

**The invitation email link WILL work on live server** as long as APP_URL is set correctly. The `url()` helper automatically uses whatever APP_URL is configured.
