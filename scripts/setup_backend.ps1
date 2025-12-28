<#
Setup helper for the `backend` Laravel project.

This script attempts to:
 - Run `composer create-project --prefer-dist laravel/laravel backend` (if `backend` does not already look like a Laravel app)
 - Initialize git and make an initial commit (if `git` is available)

Note: Run this from the repository root (where `backend` folder is located):
  .\backend\scripts\setup_backend.ps1
#>

param(
  [switch]
  $Force
)

$backendDir = Join-Path $PSScriptRoot '..' | Resolve-Path | Select-Object -ExpandProperty Path
$marker = Join-Path $backendDir 'artisan'

# Check for composer
$composer = (Get-Command composer -ErrorAction SilentlyContinue)?.Source
if (-not $composer) {
  Write-Warning "Composer not found on PATH. Please install Composer and re-run this script, or run the commands manually as documented in README.md."
} else {
  if (-not (Test-Path $marker) -or $Force) {
    Write-Output "Creating Laravel project in: $backendDir"
    Push-Location $backendDir
    try {
      # If directory is empty or not a Laravel project, run create-project into '.', using composer create-project.
      & composer create-project --prefer-dist laravel/laravel . --no-interaction
    } catch {
      Write-Error "Composer create-project failed: $_"
      Pop-Location
      exit 1
    }
    Pop-Location
  } else {
    Write-Output "Detected existing Laravel project in $backendDir (artisan exists). Skipping create-project."
  }
}

# Initialize Git if available
$git = (Get-Command git -ErrorAction SilentlyContinue)?.Source
if ($git) {
  if (-not (Test-Path (Join-Path $backendDir '.git'))) {
    Push-Location $backendDir
    try {
      git init
      git add .
      git commit -m "Initial Laravel project"
      Write-Output "Created git repo and made initial commit."
    } catch {
      Write-Warning "Git init/commit failed: $_"
    }
    Pop-Location
  } else {
    Write-Output "Git repo already exists in $backendDir"
  }
} else {
  Write-Warning "Git not found on PATH. Install Git to initialize repository automatically."
}

Write-Output "Setup helper finished. See README.md for manual steps."