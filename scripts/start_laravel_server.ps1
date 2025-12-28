<#
Start Laravel dev server (php artisan serve) in background.
Usage: .\start_laravel_server.ps1 -Host 127.0.0.1 -Port 8000
#>
param(
  [string]
  $Host = '127.0.0.1',
  [int]
  $Port = 8000,
  [string]
  $ServerPath = (Join-Path $PSScriptRoot '..')
)

$php = (Get-Command php -ErrorAction SilentlyContinue)?.Source
if (-not $php) { Write-Error "php not found on PATH"; exit 1 }

$serverRoot = Resolve-Path $ServerPath
$serverRoot = $serverRoot.Path

# Check port
$inUse = Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue
if ($inUse) { Write-Output "Port $Port already in use (PID $($inUse.OwningProcess))."; exit 0 }

# Start php artisan serve
$startInfo = New-Object System.Diagnostics.ProcessStartInfo
$startInfo.FileName = $php
$startInfo.Arguments = "artisan serve --host=$Host --port=$Port"
$startInfo.WorkingDirectory = $serverRoot
$startInfo.RedirectStandardOutput = $true
$startInfo.RedirectStandardError = $true
$startInfo.UseShellExecute = $false
$proc = [System.Diagnostics.Process]::Start($startInfo)
Start-Sleep -Seconds 1
# Save PID
$proc.Id | Out-File -FilePath (Join-Path $serverRoot '.server.pid') -Encoding ascii
Write-Output "Started Laravel server on http://$Host:$Port (PID $($proc.Id))"