<#
Stop Laravel dev server started by start_laravel_server.ps1
Usage: .\stop_laravel_server.ps1 -Port 8000
#>
param(
  [int]
  $Port = 8000,
  [string]
  $ServerPath = (Join-Path $PSScriptRoot '..')
)
$serverRoot = Resolve-Path $ServerPath
$serverRoot = $serverRoot.Path

$pidFile = Join-Path $serverRoot '.server.pid'
if (Test-Path $pidFile) {
  try {
    $pid = Get-Content $pidFile | Out-String | Trim
    Stop-Process -Id $pid -Force -ErrorAction SilentlyContinue
    Remove-Item $pidFile -ErrorAction SilentlyContinue
    Write-Output "Stopped process $pid"
  } catch {
    Write-Warning "Failed to stop PID $pid: $_"
  }
} else {
  # Fallback: find any 'artisan serve' processes
  $procs = Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -and ($_.CommandLine -match 'artisan serve') }
  if (-not $procs) { Write-Output "No artisan serve processes found."; exit 0 }
  foreach ($p in $procs) {
    try { Stop-Process -Id $p.ProcessId -Force } catch { }
    Write-Output "Stopped PID $($p.ProcessId)"
  }
}
