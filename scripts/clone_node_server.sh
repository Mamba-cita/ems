#!/usr/bin/env bash
# Clone server/ into backend/node-server (POSIX)
# Usage: ./backend/scripts/clone_node_server.sh [-f] [-i]
FORCE=0
INSTALL=0
while getopts "fi" opt; do
  case $opt in
    f) FORCE=1 ;;
    i) INSTALL=1 ;;
  esac
done

SRC="$(pwd)/server"
DEST="$(pwd)/backend/node-server"
if [ ! -d "$SRC" ]; then
  echo "Source server/ not found in repo root." >&2
  exit 1
fi
if [ -d "$DEST" ]; then
  if [ "$FORCE" -eq 1 ]; then
    rm -rf "$DEST"
  else
    echo "Destination $DEST already exists; use -f to overwrite." >&2
    exit 0
  fi
fi

mkdir -p "$DEST"
# Use rsync, exclude node_modules and git metadata
rsync -a --exclude 'node_modules' --exclude '.git' --exclude '.github' "$SRC/" "$DEST/"

if [ "$INSTALL" -eq 1 ]; then
  if command -v npm >/dev/null 2>&1; then
    echo "Installing npm dependencies..."
    (cd "$DEST" && npm install --no-audit --no-fund)
  else
    echo "npm not found on PATH; skipping dependency install." >&2
  fi
fi

echo "Clone complete. Destination: $DEST"