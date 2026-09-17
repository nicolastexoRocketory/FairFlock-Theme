#!/usr/bin/env bash
# Baut dist/fairflock-<version>.zip mit Top-Level-Ordner "fairflock/" (WP-Upload & Deploy).
# Allowlist: nur Theme-Runtime, kein Agency-Tooling (.cursor, ops, docs, …).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"

VERSION="$(grep -E '^Version:' style.css | head -1 | awk '{print $2}')"
if [[ -z "${VERSION}" ]]; then
  echo "Version nicht in style.css gefunden." >&2
  exit 1
fi

OUT_DIR="$ROOT/dist"
STAGE="$OUT_DIR/stage"
ZIP_NAME="fairflock-${VERSION}.zip"

rm -rf "$STAGE"
mkdir -p "$STAGE/fairflock" "$OUT_DIR"

# Nur Auslieferungspfade — alles andere bleibt draußen.
rsync -a \
  --include='style.css' \
  --include='functions.php' \
  --include='theme.json' \
  --include='screenshot.png' \
  --include='readme.txt' \
  --include='assets/' \
  --include='assets/***' \
  --include='inc/' \
  --include='inc/***' \
  --include='parts/' \
  --include='parts/***' \
  --include='patterns/' \
  --include='patterns/***' \
  --include='styles/' \
  --include='styles/***' \
  --include='templates/' \
  --include='templates/***' \
  --include='languages/' \
  --include='languages/***' \
  --exclude='*' \
  "$ROOT/" "$STAGE/fairflock/"

if [[ ! -f "$STAGE/fairflock/style.css" || ! -f "$STAGE/fairflock/functions.php" ]]; then
  echo "Staging unvollständig — style.css/functions.php fehlen." >&2
  exit 1
fi

(
  cd "$STAGE"
  zip -rq "$OUT_DIR/$ZIP_NAME" fairflock
)

rm -rf "$STAGE"
echo "OK: $OUT_DIR/$ZIP_NAME"
