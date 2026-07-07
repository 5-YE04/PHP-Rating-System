# Feedback Kiosk — 3-Question iPad Rating Survey

A touch-friendly PHP + MySQL kiosk app for collecting customer feedback on iPads. Devices (stations) are managed in their own database table — add, rename, or deactivate a station anytime without touching code. The Analysis page and per-device pages break results down cleanly.

## If you already have this running (upgrading from the old version)

You have data already, so **don't** re-run `schema.sql` (it wipes everything). Instead:

1. Open `http://localhost/phpmyadmin` → your `rating_system` database → **SQL** tab
2. Paste in the contents of `migrate_add_devices.sql` and run it
   - This creates the `devices` table, auto-fills it with "Station 1/2/3" entries matching your existing data, and links the two tables together — your old responses are kept.
3. Replace your old `kiosk.php`, `submit_response.php`, `analysis.php` with the new versions from this folder, and add the two new files: `devices.php` and `device_detail.php`.
4. Open `http://localhost/rating-system/devices.php` to see your stations — rename them from "Station 1" to whatever's meaningful (e.g. "Front Counter").

## Fresh install

1. Install XAMPP: https://www.apachefriends.org
2. Copy this folder to `htdocs/rating-system` (Windows: `C:\xampp\htdocs\`, Mac: `/Applications/XAMPP/xamppfiles/htdocs/`)
3. Start Apache + MySQL in the XAMPP app
4. In phpMyAdmin: create database `rating_system` → Import tab → choose `schema.sql`
5. Check `config.php` matches your MySQL credentials (XAMPP defaults usually work)

## Managing devices/stations

Go to `http://localhost/rating-system/devices.php`:
- **Add a device** — give it a name (e.g. "Drive-thru iPad") and optional location. It gets a new ID automatically.
- **Kiosk link** — each device row shows its exact URL to bookmark on that iPad.
- **Deactivate** — turns a station off without deleting its history. A deactivated device's kiosk link shows "This station isn't set up yet" instead of accepting ratings, and it disappears from the Analysis nav — but all its past responses stay intact.
- **Response count** — click it to jump to that device's own analysis page.

No more hardcoded "1, 2, or 3" — you can have as many stations as you want, and IDs don't need to be sequential.

## The 3 questions

Every survey still asks: Overall service, Staff friendliness, Speed of service (1–5 stars each). Edit the wording directly in `kiosk.php` (search `kiosk-q-label`).

## Setting up an iPad

1. Go to `devices.php`, find the station, copy its **Kiosk link**
2. On the iPad: open that link in Safari → Share → Add to Home Screen
3. On your PC's local network, replace `localhost` in the link with your PC's LAN IP so the iPad (on Wi-Fi) can reach it:
   - Windows: `ipconfig` → look for IPv4 Address
   - Mac: `ipconfig getifaddr en0` in Terminal

## Viewing results

- `analysis.php` — totals, per-question averages, per-device breakdown table, 15 most recent responses (all device names pulled live from the `devices` table)
- `device_detail.php?id=X` — deep dive into a single station: its own averages and its most recent 30 responses

## Files

| File | Purpose |
|---|---|
| `schema.sql` | Fresh-install schema: `devices` + `responses` tables, sample data |
| `migrate_add_devices.sql` | Adds `devices` table to an existing install, keeps your data |
| `config.php` | Database connection — edit credentials here |
| `helpers.php` | Star-rendering and escaping helpers |
| `kiosk.php` | Survey screen for one device (`?device=ID`), validated against `devices` |
| `submit_response.php` | Saves a submitted survey, checks device is valid & active |
| `devices.php` | Admin: list/add/deactivate stations, copy kiosk links |
| `device_detail.php` | Analysis for a single device |
| `analysis.php` | Overall stats + per-device breakdown (joined with `devices`) |
| `style.css` | Styling, including large touch targets for the kiosk screen |

## Notes

- Deleting a device (not just deactivating) isn't exposed in the UI on purpose — its responses would need to go somewhere. Deactivating hides it from use while keeping history. If you truly want to delete a device and its responses, do it in phpMyAdmin (the foreign key has `ON DELETE CASCADE`, so deleting a device row removes its responses too — be sure that's what you want).
- Tested end-to-end: adding a device, kiosk validating live against the devices table, submitting a rating, deactivating a device, and both analysis pages correctly joining device names.
