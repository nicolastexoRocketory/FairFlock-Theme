# Deploy auf mStudio-Shops

Ziel: Ein Git-Tag / manueller Workflow aktualisiert `wp-content/themes/fairflock` auf **allen** Einträgen in der Registry — ohne Uploads/DB anzufassen.

## 1. Registry

```bash
cp ops/shops.registry.example.json ops/shops.registry.json
```

Pro Shop brauchst du:

| Feld | Beispiel | Wo finden |
|------|----------|-----------|
| `mittwald.appInstallationId` | `a-gpdufg` | mStudio → App → Kurz-ID / CLI `mw app list -p PROJECT` |
| `mittwald.themePath` | `wp-content/themes/fairflock` | relativ zur WP-Installation |
| `url` | Shop-URL | Kontrolle |

`ops/shops.registry.json` ist in `.gitignore` (lokale Liste). Für CI: Inhalt als GitHub-Secret `SHOPS_REGISTRY_JSON` hinterlegen **oder** die example-Datei committen, wenn keine Secrets drin sind (App-IDs allein sind oft ok).

Aktuelle Example zeigt die WP-App **Amateurhandball.de** (`a-gpdufg`) — bitte bestätigen, ob das eure FairFlock-Testinstallation ist.

## 2. Lokal (einmalig)

1. [mittwald CLI](https://developer.mittwald.de/docs/v2/cli/) installieren  
2. API-Token: mStudio → API-Tokens → `export MITTWALD_API_TOKEN=…`  
3. SSH-Key in mStudio hinterlegen: `mw user ssh-key create` (oder vorhandenen importieren)  
4. Theme zippen und dry-run:

```bash
./ops/scripts/package-theme.sh
./ops/scripts/deploy-shops.sh dist/fairflock-*.zip --dry-run
```

5. Echter Deploy (schreibt auf den Server — Freigabe nötig):

```bash
./ops/scripts/deploy-shops.sh dist/fairflock-*.zip
```

## 3. GitHub Actions

Secrets im Repo `FairFlock-Theme`:

| Secret | Inhalt |
|--------|--------|
| `MITTWALD_API_TOKEN` | mStudio API-Token |
| `MITTWALD_SSH_PRIVATE_KEY` | Private SSH-Key (PEM), der in mStudio hinterlegt ist |
| `MITTWALD_SSH_HOST_KEY` | Optional: Ausgabe von `ssh-keyscan ssh.<host>.project.host` |
| `SHOPS_REGISTRY_JSON` | Inhalt von `shops.registry.json` (JSON) |

Workflow: **Release FairFlock Theme**

- Tag `v*` → baut ZIP  
- Manuell mit `dry_run=false` → deployed auf alle Registry-Shops  

Automatischer Deploy bei jedem Tag kann später freigeschaltet werden.

## 4. Sicherheit

- Deploy betrifft **nur** den Theme-Ordner (`--remote-sub-directory` + `--delete` nur dort).  
- Keine Kundenmedien, keine DB.  
- Site-Editor-Overrides in der DB bleiben erhalten.

## 5. Multi-Tenant: individuelle Änderungen bleiben erhalten

### Was ein Theme-Push tut

| Ebene | Beim Deploy | Nutzer-Anpassungen |
|-------|-------------|-------------------|
| Dateien unter `wp-content/themes/fairflock` | Werden ersetzt | — |
| Medien / Uploads | Unberührt | bleiben |
| Datenbank (Seiten, Produkte, Options) | Unberührt vom rsync | bleiben |
| Site-Editor-Templates (`wp_template` / `wp_template_part`) | Liegen in der **DB**, nicht im Theme-ZIP | bleiben und **überschreiben** Theme-Dateien |

Shops, die Header, Mega-Menü, Footer oder Templates im Site Editor angepasst haben, behalten diese DB-Kopien. Neue Theme-Defaults gelten automatisch nur, wenn **kein** angepasstes Template-Part in der DB liegt.

### Regel im Code (`inc/tenant-safety.php`)

- **Verboten** bei automatischem `init`: komplette Template-Parts/Seiten mit Theme-Datei überschreiben.
- **Erlaubt**: additive Fixes (fehlenden Slot einfügen, wenn noch nicht vorhanden).
- **Explizit**: Demo-Inhalt mit Checkbox „Überschreiben“, oder Filter `fairflock_may_migrate_db_content`.

Neues Mega-Layout für einen Shop, der das Mega-Menü schon angepasst hat:

1. Site Editor → Template-Teile → Mega-Menü → auf Theme-Default zurücksetzen, **oder**
2. Inhalt manuell nachziehen.

Niemals eine neue „Refresh“-Migration bauen, die `wp_update_post` mit der kompletten `parts/*.html` auf alle Shops schreibt.
