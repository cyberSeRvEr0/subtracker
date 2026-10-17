# SubTracker

Track all your subscriptions in one place. Get notified before renewals, see where your money goes, and cancel what you don't need.

## Features

- Add subscriptions from a searchable list of 40+ services (or add custom ones)
- Dashboard with monthly/yearly spending, category breakdown, and charts
- Renewal alerts (7-day warning)
- Notification system (in-app + email)
- "What if you cancel..." savings calculator
- PWA — installable on Android (APK) and iOS (Add to Home Screen)
- Google OAuth sign-in
- 7-day free trial, then $5/month or $40/year via PayPal
- Admin panel for sending notifications and managing users

## Tech Stack

| Layer | Tech |
|-------|------|
| Backend | PHP 8 (no framework) |
| Database | SQLite (WAL mode) |
| Frontend | Vanilla HTML/CSS/JS |
| Charts | Chart.js |
| Payments | PayPal Subscriptions SDK |
| Auth | Session-based + Google OAuth2 |
| Email | PHPMailer (Gmail SMTP) |
| PWA | Service Worker + Manifest |

## Setup

**1. Clone the repo**

```
git clone https://github.com/cyberSeRvEr0/subtracker.git
cd subtracker
```

**2. Create your config**

```
cp settings.example.php settings.php
```

Fill in all the values in settings.php:

- DB_PATH — leave as default (subtracker.sqlite)
- MAIL_USERNAME / MAIL_PASSWORD — Gmail address + App Password
- APP_URL — your live domain (e.g. https://subtracker.example.com)
- PAYPAL_CLIENT_ID / PAYPAL_CLIENT_SECRET — from your PayPal Developer Dashboard
- PAYPAL_PLAN_ID_MONTHLY / PAYPAL_PLAN_ID_ANNUAL — your PayPal plan IDs
- PAYPAL_ENV — sandbox or live
- GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET — from Google Cloud Console
- ADMIN_PASSWORD — your admin panel password

**3. Set up the database**

The repo includes an empty subtracker.sqlite with all tables created. If you need to recreate it:

```
sqlite3 subtracker.sqlite < schema.sql
```

**4. Run it**

```
php -S localhost:8000
```

For production, point your web server (Apache/Nginx) at the project root.

**5. Configure PayPal redirects**

In your PayPal Dashboard, set the return URL to:

```
https://YOUR_DOMAIN/upgrade.php
```

**6. Configure Google OAuth**

In Google Cloud Console, add your authorized redirect URI:

```
https://YOUR_DOMAIN/google_callback.php
```

## Project Structure

```
index.php               Dashboard (main page)
login.php               Sign in
register.php            Sign up
onboarding.php          Trial / upgrade gate
upgrade.php             PayPal subscription checkout
services.php            Searchable service list
add.php                 Add subscription form
edit.php                Edit subscription
delete_subscription.php Delete subscription
notifications.php       User notification list
help.php                Help, pricing, support form
forgot_password.php     Forgot password
password_reset.php      Reset password (via token link)
google_callback.php     Google OAuth2 handler
download.php            APK download + tracks app_downloaded
logout.php              Destroy session
admin_notify.php        Admin: send/view notifications
admin_delete.php        Admin: delete a user
auth.php                Session helpers (require_login, get_user)
config.php              Entry point: session + settings + db
db.php                  PDO SQLite connection
notify.php              create_notification(), get_unread_count()
settings.php            Your credentials (gitignored)
settings.example.php    Template for settings.php
sw.js                   Service worker (network-first)
manifest.json           PWA manifest
subtracker.sqlite       Empty database (schema only)
css/style.css           Main stylesheet
icons/                  SVG service icons + PWA PNG icons
phpmailer/              PHPMailer library
download/subtracker.apk Android APK
video/banner.mp4        Registration page video
```

## Database Schema

### users

| Column | Type | Notes |
|--------|------|-------|
| id | INTEGER PK | Auto-increment |
| name | TEXT | |
| email | TEXT UNIQUE | |
| password | TEXT | Bcrypt hash |
| plan | TEXT | free / trial / paid |
| trial_start | DATETIME | |
| trial_end | DATETIME | |
| trial_used | INTEGER | 0 or 1 |
| sub_start | DATETIME | |
| sub_end | DATETIME | |
| pending_upgrade | INTEGER | 0 or 1 |
| paypal_sub_id | TEXT | PayPal subscription ID |
| reset_token | TEXT | One-time password reset |
| app_downloaded | INTEGER | 0 or 1 |
| created_at | TIMESTAMP | |

### subscriptions

| Column | Type | Notes |
|--------|------|-------|
| id | INTEGER PK | |
| user_id | INTEGER FK | References users.id |
| name | TEXT | |
| price | REAL | Monthly |
| renewal_date | DATE | |
| category | TEXT | streaming / music / fitness / cloud / other |

### notifications

| Column | Type | Notes |
|--------|------|-------|
| id | INTEGER PK | |
| user_id | INTEGER FK | References users.id |
| title | TEXT | |
| message | TEXT | |
| type | TEXT | info / price / renewal / update / alert |
| read | INTEGER | 0 or 1 |
| created_at | TIMESTAMP | |

## .gitignore

```
settings.php
```

## Notes

- All user data is scoped by user_id — no cross-user access
- Passwords are hashed with password_hash() (bcrypt)
- Google sign-in creates accounts with a random password (users always auth via Google)
- The service worker uses a network-first strategy with cache fallback
- Admin pages (admin_*.php) are protected by a shared password (set in settings.php)
- PayPal subscriptions auto-renew; the dashboard checks sub_end and extends if the date has passed
- The 7-day trial is one-time per user (trial_used flag)
- Support requests are emailed to the admin via PHPMailer   