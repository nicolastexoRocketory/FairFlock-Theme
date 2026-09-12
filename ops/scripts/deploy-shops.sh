#!/usr/bin/env bash
# Platzhalter: liest ops/shops.registry.json und deployed das Theme auf alle Shops.
# Noch kein Live-Zugriff — hier nur Struktur für die spätere Mittwald-/SFTP-Anbindung.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
REGISTRY="${SHOPS_REGISTRY_FILE:-$ROOT/ops/shops.registry.json}"
ZIP="${1:-}"

if [[ ! -f "$REGISTRY" ]]; then
  echo "Registry fehlt: $REGISTRY" >&2
  echo "Kopiere ops/shops.registry.example.json → ops/shops.registry.json und trage die mStudio-App ein." >&2
  exit 1
fi

if [[ -z "$ZIP" || ! -f "$ZIP" ]]; then
  echo "Usage: $0 dist/fairflock-<version>.zip" >&2
  exit 1
fi

echo "Würde deployen: $ZIP"
echo "Registry: $REGISTRY"
# shellcheck disable=SC2016
echo "TODO: pro Shop Theme nach mittwald.themePath syncen (API/SFTP)."
echo "Kein Live-Deploy in diesem Script-Stand."
exit 1
