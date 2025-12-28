# backend (Laravel)

This folder is intended to contain the Laravel backend for the project.

What I scaffolded for you:
- Minimal project structure and files to start a Laravel app
- `.gitignore` with Laravel defaults
- `scripts/setup_backend.ps1` that will run Composer to create the Laravel project and initialize Git (Windows)

How to finish setup locally
1. Ensure PHP 8.1+, Composer and Git are installed and on PATH.
2. From repository root run (Windows PowerShell):
   .\backend\scripts\setup_backend.ps1

Or run the commands manually:

- Create a fresh laravel app in `backend` (this downloads vendor code):
  composer create-project --prefer-dist laravel/laravel backend --no-interaction

- Initialize git and make the initial commit:
  cd backend
  git init
  git add .
  git commit -m "Initial Laravel project"

- Copy `.env.example` to `.env` and generate an app key:
  cp .env.example .env
  php artisan key:generate

If you want, I can attempt to install Composer and Git and complete these steps for you here—say "Please install and finish" and I'll proceed.

CI (GitHub Actions)

I added a basic CI workflow for the Laravel backend at `.github/workflows/ci.yml` which:
- runs on push / pull_request
- sets up PHP 8.1, MySQL service
- installs composer dependencies, runs migrations, and runs `php artisan test`

I also added a Flutter CI at the repository root `.github/workflows/flutter-ci.yml` which:
- installs Flutter
- runs `flutter pub get` and `flutter test --coverage`

Repository initialization (run locally)

Run these commands locally to initialize the repo and push it to GitHub (the example you provided):

  echo "# ems" >> README.md
  git init
  git add README.md
  git commit -m "first commit"
  git branch -M main
  git remote add origin https://github.com/Mamba-cita/ems.git
  git push -u origin main

After pushing, GitHub will pick up the workflows and run the CI jobs.

Running integration tests locally

To run the integration job locally (start Laravel server and run the live Flutter integration tests):

1. Ensure the `backend` app is set up and dependencies installed:

   cd backend
   composer install --no-interaction --prefer-dist --no-progress
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --force

2. Start the Laravel dev server (background):

   # Linux / macOS
   php artisan serve --host=127.0.0.1 --port=8000 &

   # Windows (PowerShell)
   .\scripts\start_laravel_server.ps1 -Host 127.0.0.1 -Port 8000

3. In the root of the repository run the Flutter integration test (sets RUN_LIVE_INTEGRATION to enable live tests):

   RUN_LIVE_INTEGRATION=true PUBLIC_API_URL=http://127.0.0.1:8000 flutter test test/integration/streem_live_test.dart -r expanded

4. Stop the Laravel server when done:

   # Linux / macOS: kill $(cat backend/.server.pid) || true
   # Windows (PowerShell): .\backend\scripts\stop_laravel_server.ps1 -Port 8000

If you'd like I can add a small wrapper script to automate the whole sequence (start, test, stop).

Environment variables

Ensure you set `JWT_SECRET` in your `.env` before running the app. For example (in PowerShell):

  $env:JWT_SECRET = 'your-strong-secret'

Or add it to the `.env` file:

  JWT_SECRET=your-strong-secret

This secret is used by the backend to sign JWT tokens returned by the auth endpoints.

Tests

- Unit/feature tests live under `backend/tests` and can be run with `vendor/bin/phpunit` after you run `composer install` and set up the app.
- The included `tests/Feature/AuthFlowTest.php` covers register/login/profile/refresh/logout behavior (uses in-memory sqlite DB by default configured in `phpunit.xml`).



Cloning the Node server into backend

If you want a copy of the existing Node server inside the `backend` folder (useful if you want to keep a Node microservice alongside Laravel), run the included helper scripts from the repo root:

# Windows (PowerShell)
PS> .\backend\scripts\clone_node_server.ps1 -Force -InstallDeps

# Linux / macOS
$ ./backend/scripts/clone_node_server.sh -f -i

The helpers copy `server/` to `backend/node-server` excluding `node_modules` and `.git` and can run `npm install` if `-InstallDeps`/`-i` is supplied.
"}***{