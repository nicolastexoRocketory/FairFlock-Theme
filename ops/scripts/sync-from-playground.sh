#!/usr/bin/env bash
# Kopiert das Theme aus dem lokalen WordPress-Playground in dieses Repo.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
PLAYGROUND="${FAIRFLOCK_PLAYGROUND:-$ROOT/../FairFlock Theme/wp-content/themes/fairflock}"

if [[ ! -f "$PLAYGROUND/style.css" ]]; then
  echo "Playground-Theme nicht gefunden: $PLAYGROUND" >&2
  echo "Setze FAIRFLOCK_PLAYGROUND auf den Pfad zu wp-content/themes/fairflock" >&2
  exit 1
fi

rsync -a --delete \
  --exclude '.git' \
  --exclude 'ops' \
  --exclude 'dist' \
  --exclude 'README.md' \
  --exclude '.gitignore' \
  --exclude '.DS_Store' \
  "$PLAYGROUND/" "$ROOT/"

echo "OK: Playground → $ROOT (Version $(grep -E '^Version:' "$ROOT/style.css" | awk '{print $2}'))"
