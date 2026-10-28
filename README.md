# Points Tracker

A PHP and MySQL application for QR booth check-ins, student points, redemption history and administrator dashboards.

## Setup

Requires PHP 8.2+ with mysqli and MySQL/MariaDB.

1. Create a local database named `portfolio_demo` and import `schema.sql`.
2. Copy `config.php.example` to `config.php` and set your local database credentials.
3. Create local accounts and sample records matching the schema.
4. Run `php -S 127.0.0.1:8000` and open the local address.

QR scanning requires browser camera permission. Localhost supports camera access; remote use requires HTTPS. Frontend CDN libraries require internet access.

## Development notes

`schema.sql` was reconstructed from the application queries and may need reconciliation with other database versions. This version stores and compares passwords as plaintext and uses shared reset behavior. Use disposable local credentials until authentication is updated; do not deploy this version with real accounts.

Images are placeholders. Add your own assets and records through local configuration, and keep credentials and user data out of version control.
