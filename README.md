# 📋 Feedback Kiosk

A lightweight, touch-friendly customer feedback kiosk built with **PHP + MySQL**. Deploy it on iPads (or any tablet/browser) around your location, collect 1–5 star ratings across three questions plus an optional comment, and see results broken down per device in a built-in analytics dashboard — no frameworks, no build step, just PHP.

![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

---

## ✨ Features

- **Touch-first survey UI** — large tap targets, no zoom/scroll issues, built for iPads/tablets
- **3 star-rating questions + optional comment** — customizable in one place
- **Multi-device support** — every station is a row in the database, not hardcoded. Add, rename, or deactivate stations anytime from an admin page
- **Per-device analytics** — compare Station A vs Station B vs Station C at a glance, or drill into any single device's history
- **Auto-resets between customers** — shows a "Thank you" screen, then returns to a blank survey automatically
- **Zero dependencies** — plain PHP + PDO + MySQL, no Composer, no JS framework, no build step
- **SQL-injection & XSS safe** — prepared statements throughout, all output escaped

## 📸 How it works

1. Each tablet is bookmarked to its own URL: `kiosk.php?device=1`, `kiosk.php?device=2`, etc.
2. A customer taps through 3 star ratings and (optionally) types a comment
3. On submit, the response is saved along with which device it came from
4. The kiosk shows "Thank you!" for a few seconds, then resets for the next customer
5. Staff check `analysis.php` for overall stats or `devices.php` to manage stations

## 🗂 Project structure

```
rating-system/
├── kiosk.php              # Survey screen shown on each device (?device=ID)
├── submit_response.php    # Handles form submission, validates & saves to DB
├── devices.php            # Admin: add / rename / deactivate stations
├── device_detail.php      # Analytics for a single device
├── analysis.php           # Overall analytics dashboard
├── config.php             # Database connection settings
├── helpers.php            # Shared helper functions (star rendering, escaping)
├── style.css              # All styling, incl. large touch targets for kiosk mode
├── schema.sql             # Fresh-install database schema + sample data
├── migrate_add_devices.sql   # Migration: adds devices table to older installs
└── migrate_add_comment.sql   # Migration: adds comment column to older installs
```

## 🗄 Database schema

Two tables:

**`devices`** — one row per physical station/tablet
| Column | Type | Notes |
|---|---|---|
| `id` | INT, PK | Referenced by `responses.device_id` |
| `name` | VARCHAR | e.g. "Front Counter iPad" |
| `location` | VARCHAR | Optional |
| `active` | TINYINT(1) | Deactivated devices stop accepting ratings |

**`responses`** — one row per submitted survey
| Column | Type | Notes |
|---|---|---|
| `id` | INT, PK | |
| `device_id` | INT, FK → `devices.id` | Which station this came from |
| `overall_stars` | TINYINT (1–5) | |
| `staff_stars` | TINYINT (1–5) | |
| `speed_stars` | TINYINT (1–5) | |
| `comment` | TEXT, nullable | Optional free-text feedback |
| `created_at` | TIMESTAMP | Auto-set |

## 🚀 Getting started with XAMPP

The easiest way to run this locally — no separate PHP or MySQL install needed, XAMPP bundles everything.

**Requirements:** PHP 7.4+ with `pdo_mysql`, MySQL 5.7+ (or MariaDB) — XAMPP includes both.

### 1. Install XAMPP
Download from [apachefriends.org](https://www.apachefriends.org) and install it (Windows or Mac).

### 2. Copy the project into `htdocs`
This is the folder XAMPP serves websites from.

- **Windows:** `C:\xampp\htdocs\rating-system`
- **Mac:** `/Applications/XAMPP/xamppfiles/htdocs/rating-system`

Either `git clone` directly into that folder, or download/copy the files there:
```bash
git clone https://github.com/yourusername/rating-system.git
```

> **Mac only:** downloaded/dragged-in files sometimes end up without permission for Apache to read them, causing a `Permission denied` / 500 error. If that happens, run:
> ```bash
> sudo chmod -R 755 /Applications/XAMPP/xamppfiles/htdocs/rating-system
> sudo chown -R daemon:admin /Applications/XAMPP/xamppfiles/htdocs/rating-system
> ```

### 3. Start Apache and MySQL
Open the **XAMPP Control Panel** (Windows) or **XAMPP app → Manage Servers** (Mac) and click **Start** next to both Apache and MySQL. Both should show a green/running status.

### 4. Create the database
1. Open `http://localhost/phpmyadmin` in your browser
2. Click **New** in the left sidebar
3. Name it `rating_system` → click **Create**
4. Click into the `rating_system` database → go to the **Import** tab
5. Click **Choose File**, select `schema.sql` from the project folder → click **Go**

This creates the `devices` and `responses` tables and inserts 3 sample stations plus a few sample ratings, so the dashboard isn't empty on first load.

### 5. Set your database credentials
Open `config.php` in the project folder and confirm it matches your XAMPP setup (the defaults below usually work out of the box):
```php
$DB_HOST = 'localhost';
$DB_NAME = 'rating_system';
$DB_USER = 'root';
$DB_PASS = '';
```

### 6. Open it in your browser
- Survey screen: `http://localhost/rating-system/kiosk.php?device=1`
- Manage stations: `http://localhost/rating-system/devices.php`
- Analytics dashboard: `http://localhost/rating-system/analysis.php`

If you see a **404 Not Found**, double check the folder is named exactly `rating-system` directly inside `htdocs` (not nested in an extra subfolder), and that you're using one of the URLs above — `index.php` doesn't exist in this project.

### Upgrading from an older version

If you're pulling updates into an existing install with real data already in it, **don't** re-run `schema.sql` (it drops and recreates tables). Instead, open `http://localhost/phpmyadmin` → your database → **SQL** tab, and paste in the contents of each migration script that applies to you, or run them from a terminal:

```bash
mysql -u root -p rating_system < migrate_add_devices.sql
mysql -u root -p rating_system < migrate_add_comment.sql
```

## 🖥 Setting up physical devices

1. Open `devices.php` and add a device — it gets a unique ID and a ready-to-copy kiosk link
2. On each tablet's browser, open that link and add it to the home screen (Safari: Share → Add to Home Screen)
3. If tablets are on the same network but not the host machine, swap `localhost` for your server's LAN IP in the link (e.g. `http://192.168.1.20/rating-system/kiosk.php?device=1`)

## 🔒 Security notes

- All database queries use prepared statements (PDO) — no raw SQL string concatenation
- All user-supplied output is passed through `htmlspecialchars()` before rendering
- Server-side validation on every submission (star values 1–5, device must exist and be active)
- No authentication is included on the kiosk screen by design (it's a public walk-up survey) — `devices.php` and `analysis.php` are **not** password-protected out of the box; put them behind your web server's auth (e.g. `.htaccess`) or a login layer before deploying somewhere public

## 🛣 Roadmap ideas

- [ ] Basic admin login for `devices.php` / `analysis.php`
- [ ] CSV export of responses
- [ ] Configurable questions via the database instead of editing `kiosk.php`
- [ ] Charts on the analytics dashboard

## 📄 License

MIT — do whatever you'd like with it.
