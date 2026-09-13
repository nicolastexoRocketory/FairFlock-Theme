#!/usr/bin/env bash
# Kopiert dieses Repo zurück in den lokalen Playground.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
PLAYGROUND="${FAIRFLOCK_PLAYGROUND:-$ROOT/../FairFlock Theme/wp-content/themes/fairflock}"

mkdir -p "$PLAYGROUND"
rsync -a --delete \
  --exclude '.git' \
  --exclude 'ops' \
  --exclude 'dist' \
  --exclude 'README.md' \
  --exclude '.gitignore' \
  --exclude '.DS_Store' \
  --exclude '.cursor' \
  --exclude '.github' \
  "$ROOT/" "$PLAYGROUND/"

echo "OK: $ROOT → $PLAYGROUND"
grep -E '^Version:' "$PLAYGROUND/style.css" || true
