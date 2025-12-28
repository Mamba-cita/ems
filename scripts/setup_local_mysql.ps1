# setup_local_mysql.ps1
# Attempts to create a local MySQL database named 'ter' (for XAMPP).
# Usage: Open an Administrator PowerShell, start MySQL from XAMPP control panel, then run:
#   .\scripts\setup_local_mysql.ps1

$mysqlCmd = "mysql"
# Common XAMPP mysql paths
$commonPaths = @(
    "C:\\xampp\\mysql\\bin\\mysql.exe",
    "C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysql.exe",
    "C:\\Program Files (x86)\\MySQL\\MySQL Server 5.7\\bin\\mysql.exe"
)

foreach ($p in $commonPaths) {
    if (Test-Path $p) { $mysqlCmd = "`"$p`""; break }
}

Write-Host "Using mysql command: $mysqlCmd"

# Try to create database 'ter'
$createDbCmd = "$mysqlCmd -u root -e \"CREATE DATABASE IF NOT EXISTS `\`ter\`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\""
Write-Host "Running: $createDbCmd"
try {
    iex $createDbCmd
    Write-Host "Database 'ter' created or already exists." -ForegroundColor Green
} catch {
    Write-Error "Failed to run mysql. Ensure XAMPP MySQL is running and that mysql.exe is available.\nError: $_"
    exit 1
}

# Copy .env.example if .env missing
$envExample = Join-Path $PSScriptRoot "..\.env.example"
$envFile = Join-Path $PSScriptRoot "..\.env"
if (-not (Test-Path $envFile)) {
    Copy-Item $envExample $envFile
    Write-Host "Copied .env.example -> .env"
}

# Ensure .env has the right DB settings
(Get-Content $envFile) -replace 'DB_DATABASE=.*', 'DB_DATABASE=ter' | Set-Content $envFile
(Get-Content $envFile) -replace 'DB_USERNAME=.*', 'DB_USERNAME=root' | Set-Content $envFile
# Leave DB_PASSWORD empty for XAMPP default

Write-Host "Set DB_DATABASE=ter and DB_USERNAME=root in .env"
Write-Host "Next steps: run 'composer install', 'php artisan key:generate', and 'php artisan migrate'."}```