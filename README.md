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

## 🚀 Getting started

### Requirements
- PHP 7.4+ with the `pdo_mysql` extension
- MySQL 5.7+ (or MariaDB equivalent)
- Any web server (Apache/Nginx), or just XAMPP for local development

### Install

```bash
git clone https://github.com/yourusername/rating-system.git
```

1. Copy the folder into your web server's document root
   - XAMPP (Windows): `C:\xampp\htdocs\rating-system`
   - XAMPP (Mac): `/Applications/XAMPP/xamppfiles/htdocs/rating-system`
2. Create a database and import the schema:
   ```bash
   mysql -u root -p -e "CREATE DATABASE rating_system"
   mysql -u root -p rating_system < schema.sql
   ```
   (This also inserts 3 sample devices and a few sample responses so the dashboard isn't empty on first load.)
3. Edit `config.php` with your database credentials:
   ```php
   $DB_HOST = 'localhost';
   $DB_NAME = 'rating_system';
   $DB_USER = 'root';
   $DB_PASS = '';
   ```
4. Visit `http://localhost/rating-system/devices.php` to manage stations, or `http://localhost/rating-system/kiosk.php?device=1` to try the survey.

### Upgrading from an older version

If you're pulling updates into an existing install with real data already in it, **don't** re-run `schema.sql` (it drops and recreates tables). Instead run the relevant migration script(s) against your existing database:

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
