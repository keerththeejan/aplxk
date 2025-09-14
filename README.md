# Logistip PHP + MySQL

This is a PHP implementation of the Logistip landing page using WAMP (Windows, Apache, MySQL, PHP). Content for Services, Portfolio, Pricing, Brands, Testimonials, and Blog is loaded from MySQL.

## Quick Start

1. Import the database schema and sample data:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a database named `aplx` (or any name you prefer)
   - Import `database/schema.sql` into that database

2. Configure database credentials:
   - Edit `config/config.php` and set `DB_NAME`, `DB_USER`, `DB_PASS` as needed

3. Visit the site:
   - http://localhost/APLX/

## Structure

- `config/config.php` — site constants and DB credentials
- `lib/db.php` — PDO connection helper
- `lib/functions.php` — data access helpers
- `partials/header.php`, `partials/footer.php` — layout includes
- `index.php` — homepage rendering all sections
- `assets/css/*`, `assets/js/main.js` — minimal theme assets (CDNs used for libraries)
- `database/schema.sql` — tables and seed data

## Notes

- If you see a warning about the database not being initialized, import `database/schema.sql` and refresh.
- All external libraries (Bootstrap, Swiper, jQuery, Magnific Popup, Leaflet, etc.) are loaded via CDN.
