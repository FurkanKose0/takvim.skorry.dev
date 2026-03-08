# takvim.skorry.dev

A simple, PHP-based calendar application with an administrative panel for managing entries.

## Project Structure

- `index.php`: The main calendar interface for users.
- `admin.php` & `admin-login.php`: Admin panel and authentication system.
- `db.php`: SQLite database connection handling.
- `init_db.php` & `seed_data.php`: Scripts for initializing the database schema and populating it with sample data.
- `ekqzdnwk_takvim.sql` & `veritabani.sql`: SQL schemas and database dumps.

## Getting Started

### Prerequisites
- PHP 7.4 or higher
- SQLite3 PHP extension enabled

### Installation

1. Clone this repository or download the source code.
2. In the project root, ensure you have read/write permissions for the directory so that SQLite can create the database file.
3. Run the application using the built-in PHP server:
   ```bash
   php -S localhost:8000
   ```
4. Visit `http://localhost:8000` in your web browser.

### Initializing the Database
Navigate to `init_db.php` (e.g., `http://localhost:8000/init_db.php`) in your browser to create the necessary tables. You can also run `seed_data.php` if you would like to quickly add some initial test records.

## Admin Access
- **Login Page**: `admin-login.php`
- **Dashboard**: `admin.php`
