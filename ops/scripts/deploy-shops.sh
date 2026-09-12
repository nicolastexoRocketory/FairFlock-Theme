#!/usr/bin/env bash
# Deployt das FairFlock-Theme auf alle Shops in der Registry via mittwald CLI (mw app upload).
#
# Voraussetzung:
#   - mw (mittwald CLI) installiert und eingeloggt, oder MITTWALD_API_TOKEN gesetzt
#   - SSH-Key für mStudio (mw user ssh-key … / MITTWALD_SSH_IDENTITY_FILE)
#   - rsync lokal verfügbar
#
# Usage:
#   ./ops/scripts/deploy-shops.sh dist/fairflock-0.9.149.zip
#   ./ops/scripts/deploy-shops.sh dist/fairflock-0.9.149.zip --dry-run
#   SHOPS_REGISTRY_FILE=/path/to/registry.json ./ops/scripts/deploy-shops.sh …
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
REGISTRY="${SHOPS_REGISTRY_FILE:-$ROOT/ops/shops.registry.json}"
ZIP=""
DRY_RUN=0

for arg in "$@"; do
  case "$arg" in
    --dry-run) DRY_RUN=1 ;;
    *.zip) ZIP="$arg" ;;
    *)
      echo "Unbekanntes Argument: $arg" >&2
      exit 1
      ;;
  esac
done

if [[ ! -f "$REGISTRY" ]]; then
  echo "Registry fehlt: $REGISTRY" >&2
  echo "Kopiere ops/shops.registry.example.json → ops/shops.registry.json und trage die App-IDs ein." >&2
  exit 1
fi

if [[ -z "$ZIP" || ! -f "$ZIP" ]]; then
  echo "Usage: $0 dist/fairflock-<version>.zip [--dry-run]" >&2
  exit 1
fi

if ! command -v mw >/dev/null 2>&1; then
  echo "mittwald CLI (mw) nicht gefunden. Install: https://developer.mittwald.de/docs/v2/cli/" >&2
  exit 1
fi

if ! command -v python3 >/dev/null 2>&1; then
  echo "python3 wird zum Lesen der Registry benötigt." >&2
  exit 1
fi

STAGE="$(mktemp -d)"
cleanup() { rm -rf "$STAGE"; }
trap cleanup EXIT

unzip -q "$ZIP" -d "$STAGE"
THEME_SRC="$STAGE/fairflock"
if [[ ! -f "$THEME_SRC/style.css" ]]; then
  echo "ZIP enthält keinen Ordner fairflock/ mit style.css" >&2
  exit 1
fi

VERSION="$(grep -E '^Version:' "$THEME_SRC/style.css" | head -1 | awk '{print $2}')"
echo "Theme-Version: ${VERSION}"
echo "Registry: $REGISTRY"
echo "Dry-run: $DRY_RUN"
echo

SHOP_LINES="$(python3 - "$REGISTRY" <<'PY'
import json, sys
with open(sys.argv[1], encoding="utf-8") as f:
    data = json.load(f)
for shop in data.get("shops", []):
    mw = shop.get("mittwald") or {}
    app_id = mw.get("appInstallationId") or mw.get("appId") or ""
    sub = mw.get("themePath") or "wp-content/themes/fairflock"
    if not app_id or "REPLACE" in str(app_id):
        print("skip incomplete: " + str(shop.get("id")), file=sys.stderr)
        continue
    print("|".join([
        shop.get("id", ""),
        shop.get("name", "").replace("|", "/"),
        str(app_id),
        str(sub).lstrip("/"),
    ]))
PY
)"

if [[ -z "${SHOP_LINES}" ]]; then
  echo "Keine gültigen Shops in der Registry (appInstallationId fehlt)." >&2
  exit 1
fi

FAILED=0
while IFS='|' read -r SHOP_ID SHOP_NAME APP_ID REMOTE_SUB; do
  [[ -z "${SHOP_ID}" ]] && continue
  echo "—— Shop: ${SHOP_NAME} (${SHOP_ID}) ——"
  echo "   App: ${APP_ID}"
  echo "   Ziel: ${REMOTE_SUB}/"

  CMD=(
    mw app upload "${APP_ID}"
    # Trailing slash: Inhalt von fairflock/ → remote themePath/ (kein fairflock/fairflock/)
    --source "${THEME_SRC}/"
    --remote-sub-directory "${REMOTE_SUB}"
    --delete
  )

  if [[ "$DRY_RUN" -eq 1 ]]; then
    CMD+=(--dry-run)
    echo "   [dry-run] ${CMD[*]}"
  fi

  if ! "${CMD[@]}"; then
    echo "   FEHLER beim Deploy auf ${SHOP_ID}" >&2
    FAILED=1
    continue
  fi
  echo "   OK"
  echo
done <<< "${SHOP_LINES}"

if [[ "$FAILED" -ne 0 ]]; then
  echo "Mindestens ein Shop-Deploy ist fehlgeschlagen." >&2
  exit 1
fi

echo "Alle Shops aktualisiert auf Theme ${VERSION}."
