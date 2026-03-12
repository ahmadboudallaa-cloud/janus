# Deployment AWS (EC2 + RDS)

This guide describes a minimal, production-ready deployment for the Habit Tracker API on AWS using EC2 and RDS. It assumes a Linux EC2 instance (Ubuntu 22.04 LTS or Amazon Linux 2023).

## Target Architecture
- EC2: Laravel app (Nginx + PHP-FPM)
- RDS: MySQL 8 (or PostgreSQL 15)
- Security Groups: restrict DB to EC2 only
- S3 (optional): storage for user uploads
- CloudWatch (optional): logs/metrics

## 1) RDS Setup
1. Create RDS instance
   - Engine: MySQL 8 (or PostgreSQL 15)
   - Instance class: db.t3.micro (dev), db.t3.small+ (prod)
   - Storage: gp3
2. Network
   - VPC: same as EC2
   - Public access: **No**
3. Security group for RDS
   - Inbound: allow port 3306 (MySQL) or 5432 (Postgres) only from EC2 security group

## 2) EC2 Setup
1. Launch EC2 instance
   - Ubuntu 22.04 or Amazon Linux 2023
   - Attach security group allowing:
     - 22 (SSH) from your IP only
     - 80/443 from 0.0.0.0/0
2. Install packages (Ubuntu example)
   - Nginx
   - PHP 8.2 + extensions: `mbstring`, `bcmath`, `curl`, `xml`, `zip`, `mysql` or `pgsql`
   - Composer
   - Git

## 3) App Deployment
1. Clone repository
2. Create `.env` from `.env.example`
3. Configure DB in `.env`
   - `DB_CONNECTION=mysql`
   - `DB_HOST=<rds-endpoint>`
   - `DB_PORT=3306`
   - `DB_DATABASE=habit_tracker`
   - `DB_USERNAME=<db_user>`
   - `DB_PASSWORD=<db_pass>`
4. Install dependencies
   - `composer install --no-dev --optimize-autoloader`
5. Generate key
   - `php artisan key:generate`
6. Run migrations
   - `php artisan migrate --force`
7. Set permissions
   - `storage/` and `bootstrap/cache/` must be writable

## 4) Nginx Configuration
1. Create Nginx server block pointing to `/public`
2. Example (Ubuntu):
```
server {
    listen 80;
    server_name your-domain.com;

    root /var/www/habit-tracker/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }
}
```

## 5) HTTPS (Recommended)
- Use Let's Encrypt via Certbot
- Redirect HTTP -> HTTPS

## 6) Queue / Scheduler (if needed later)
- Use `cron` for `php artisan schedule:run`
- Use Supervisor to run queues

## 7) Environment & Secrets
- Never commit `.env`
- Use AWS SSM Parameter Store or Secrets Manager in production

## 8) Postman/Bruno
- Update `baseUrl` to your domain
- Run the collection to validate

## 9) Health Checks
- Use `/up` endpoint for basic health

## 10) Rollback Strategy
- Keep previous release directory
- Use symlink switch for zero-downtime

---

### Notes
- If using PostgreSQL, change `DB_CONNECTION=pgsql` and the port to `5432`.
- Ensure security groups do not expose RDS publicly.
- Consider using an ALB + autoscaling for production scale.
