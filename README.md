# Customer Statements

Laravel 12 / Blade application for browsing sold-to parties, previewing customer statements, downloading PDFs, and sharing statements by email.

## Features

- Material-inspired responsive statement dashboard
- Customer search, date filtering, statement preview, PDF download, and email sharing
- Long customer names and addresses wrap cleanly in generated statements
- Background statement delivery for every customer with a valid email address
- Console-only user creation with public registration disabled
- Root-domain and subdirectory deployment support for Vite assets and frontend URLs

## Requirements

- PHP 8.2.12 or newer (tested dependency floor is locked to 8.2.12)
- MariaDB/MySQL with the existing `snapshot_customers`, `customer_statement`, and `customer_balance` tables
- Composer
- Node.js and npm only when rebuilding frontend assets

## Local development

```sh
composer run setup
composer run dev
```

Update the generated `.env` with the application, database, mail, and queue settings for the local environment. The development command starts Laravel, the queue listener, the log viewer, and Vite together.

Run the automated checks with:

```sh
composer test
npm run test:js
npm run build
```

## XAMPP deployment

1. Point the Apache virtual host document root to this project's `public` directory. Do not expose the Laravel project root.
2. Copy `.env.example` to `.env` and fill in database and SMTP credentials.
3. Run `/opt/lampp/bin/php /path/to/composer install --no-dev --optimize-autoloader` (replace the Composer path as needed).
4. Run `/opt/lampp/bin/php artisan key:generate` and `/opt/lampp/bin/php artisan migrate --force`.
5. Ensure `storage` and `bootstrap/cache` are writable by Apache.
6. Run `/opt/lampp/bin/php artisan optimize`.

The compiled Breeze assets are committed under `public/build`, so Node.js is not required on the XAMPP server unless the CSS or JavaScript is changed.

Public registration is disabled. Create users from the server console; passwords are entered through hidden prompts and are not placed in shell history:

```sh
php artisan users:create "Finance Admin" admin@example.com
```

Users can then sign in at `/login`. Statement routes require authentication.

## Deploy under a URL base path

For a root-domain deployment, leave the Vite base path empty:

```dotenv
APP_URL=http://localhost:8000
VITE_APP_BASE_PATH=
```

For a subdirectory deployment, set both values and keep a trailing slash on the base path:

```dotenv
APP_URL=https://app.euro-mega.com/ng/fixporter/
VITE_APP_BASE_PATH=ng/fixporter/
```

Then rebuild the frontend with `npm run build`. Vite assets will use `/ng/fixporter/build/`, and frontend code can resolve application-relative URLs with `window.withBasePath('statements')`. The helper accepts base paths with or without surrounding slashes and avoids applying the prefix twice.

## Queue monthly customer statements

Queue one statement email for every customer in a sales organization that has a valid email address:

```sh
php artisan statements:send pfnl --from=2026-07-01 --to=2026-07-31
php artisan queue:work --queue=statements --tries=3 --timeout=120
```

When `--from` and `--to` are omitted, the command uses the complete previous calendar month. Use `--dry-run` to list eligible recipients without dispatching jobs, or `--sync` to send in the foreground for troubleshooting.

Shell example for the previous calendar month:

```sh
php artisan statements:send pfnl \
  --from="$(date -d 'last month' +%Y-%m-01)" \
  --to="$(date -d 'this month - 1 day' +%Y-%m-%d)"
```

In a crontab, escape each `%` as `\%`. Keep a queue worker running under Supervisor or systemd so queued `statements` jobs are processed in the background. Failed jobs can be inspected with `php artisan queue:failed` and retried with `php artisan queue:retry <id>`.

The original standalone PHP implementation is preserved locally in `legacy/`. It is excluded from version control because it contains private CSV exports and historical configuration credentials.
