<#
Clone the existing Node server (from repo root `server/`) into `backend/node-server`.
Usage (from repo root):
  .\backend\scripts\clone_node_server.ps1
  .\backend\scripts\clone_node_server.ps1 -Force -InstallDeps

Options:
  -Force: overwrite existing destination
  -InstallDeps: run `npm install` in the copied folder if npm is available
#>
param(
  [switch]
  $Force,
  [switch]
  $InstallDeps
)

$src = (Resolve-Path (Join-Path $PSScriptRoot '..\..\server'))
if (-not $src) { Write-Error "Source 'server' not found in repo root."; exit 1 }
$srcPath = $src.Path
$dest = Resolve-Path (Join-Path $PSScriptRoot '..\node-server' ) -ErrorAction SilentlyContinue
$destPath = if ($dest) { $dest.Path } else { (Join-Path $PSScriptRoot '..\node-server') }

if (Test-Path $destPath) {
  if ($Force) {
    Write-Output "Removing existing $destPath"
    Remove-Item -Recurse -Force $destPath
  } else {
    Write-Output "Destination $destPath already exists; use -Force to overwrite."; exit 0
  }
}

Write-Output "Copying files from $srcPath to $destPath..."
# Use robocopy to preserve metadata and skip node_modules
$robo = "robocopy `"$srcPath`" `"$destPath`" /MIR /XF node_modules /XD node_modules .git .github"
Write-Output $robo
Invoke-Expression $robo

if ($InstallDeps) {
  $npm = (Get-Command npm -ErrorAction SilentlyContinue)?.Source
  if ($npm) {
    Write-Output "Installing npm dependencies in $destPath"
    Push-Location $destPath
    npm install --no-audit --no-fund
    Pop-Location
  } else {
    Write-Warning "npm not found on PATH; skipping dependency install."
  }
}

Write-Output "Clone complete. Destination: $destPath"