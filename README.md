# Customer Statements

Laravel 12 / Blade application for browsing sold-to parties, previewing customer statements, downloading PDFs, and sharing statements by email.

## Requirements

- PHP 8.2.12 or newer (tested dependency floor is locked to 8.2.12)
- MariaDB/MySQL with the existing `snapshot_customers`, `customer_statement`, and `customer_balance` tables
- Composer
- Node.js and npm only when rebuilding frontend assets

## XAMPP deployment

1. Point the Apache virtual host document root to this project's `public` directory. Do not expose the Laravel project root.
2. Copy `.env.example` to `.env` and fill in database and SMTP credentials.
3. Run `/opt/lampp/bin/php /path/to/composer install --no-dev --optimize-autoloader` (replace the Composer path as needed).
4. Run `/opt/lampp/bin/php artisan key:generate` and `/opt/lampp/bin/php artisan migrate --force`.
5. Ensure `storage` and `bootstrap/cache` are writable by Apache.
6. Run `/opt/lampp/bin/php artisan optimize`.

The compiled Breeze assets are committed under `public/build`, so Node.js is not required on the XAMPP server unless the CSS or JavaScript is changed.

Register the first user at `/register`, then sign in at `/login`. Statement routes require authentication.

The original standalone PHP implementation is preserved locally in `legacy/`. It is excluded from version control because it contains private CSV exports and historical configuration credentials.
