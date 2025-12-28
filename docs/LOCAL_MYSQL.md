# Local MySQL (XAMPP) setup for backend

This project can use a local MySQL server (for example, XAMPP). The default development DB settings in `.env.example` are set for a local MySQL database named `ter` with user `root` and an empty password.

Quick steps:

1. Install and start XAMPP (MySQL) on Windows.
2. Start MySQL from the XAMPP control panel.
3. From the project root (`backend`), run the setup script:

   powershell -ExecutionPolicy Bypass -File .\scripts\setup_local_mysql.ps1

   This will attempt to create the `ter` database and copy `.env.example` to `.env` (if missing).

4. Install PHP dependencies and prepare the app:

   composer install
   php artisan key:generate
   php artisan migrate

Notes:
- The script looks for `mysql.exe` in common XAMPP and MySQL locations. If it's not found, make sure `mysql.exe` is in your PATH or edit the script with the correct path.
- If your XAMPP uses a password for `root`, update `.env` accordingly.
- For CI or tests we still use an in-memory SQLite database by default; this file is intended for local development only.
