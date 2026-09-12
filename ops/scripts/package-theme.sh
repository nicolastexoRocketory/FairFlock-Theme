#!/usr/bin/env bash
# Baut dist/fairflock-<version>.zip mit Top-Level-Ordner "fairflock/" (WP-Upload & Deploy).
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

rsync -a \
  --exclude '.git' \
  --exclude '.gitignore' \
  --exclude 'ops' \
  --exclude 'dist' \
  --exclude 'README.md' \
  --exclude '.DS_Store' \
  --exclude '*.zip' \
  "$ROOT/" "$STAGE/fairflock/"

(
  cd "$STAGE"
  zip -rq "$OUT_DIR/$ZIP_NAME" fairflock
)

rm -rf "$STAGE"
echo "OK: $OUT_DIR/$ZIP_NAME"
