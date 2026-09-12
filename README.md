# FairFlock Theme (Deploy-Quelle)

Eigenständiges Theme-Repo für Multi-Tenant-Shops (Rocketory / FairFlock).  
**Dieses Repo ist die Quelle der Wahrheit** für alle Online-Installationen.

Der Ordner `FairFlock Theme/` mit WordPress-Core ist nur lokaler Playground — nicht deployen.

## Regeln

1. Version immer in `style.css` **und** `FAIRFLOCK_VERSION` in `functions.php` bumpen.
2. Release = Git-Tag `vX.Y.Z` (gleich wie Theme-Version).
3. Deploy nur den Theme-Inhalt nach `wp-content/themes/fairflock/` — nie Uploads, nie Datenbank.
4. Keine Content-Seeds / Destructive Migrations in Updates (Kundenänderungen bleiben in der DB / Site Editor).

## Lokal ↔ Playground

```bash
# Nach Arbeit im Playground: hierher übernehmen
./ops/scripts/sync-from-playground.sh

# Nach Pull: lokal im Playground testen
./ops/scripts/sync-to-playground.sh
```

## ZIP bauen (optional)

```bash
./ops/scripts/package-theme.sh
# → dist/fairflock-<version>.zip  (Top-Level-Ordner: fairflock/)
```

Dauerbetrieb: Push-Deploy per Pipeline auf alle Shops in der Registry — kein manuelles Theme-Austauschen als Prozess.

## Shop-Registry & Deploy

Anleitung: [`ops/DEPLOY.md`](ops/DEPLOY.md)

- Vorlage: `ops/shops.registry.example.json`
- Lokal: `ops/shops.registry.json` (gitignore)
- Script: `./ops/scripts/deploy-shops.sh dist/fairflock-*.zip [--dry-run]`

## GitHub Release / Update-Weg

1. Änderungen committen, Version bumpen (`style.css` + `functions.php`).
2. Tag setzen: `git tag v0.9.149 && git push origin v0.9.149`
3. GitHub Action **Release FairFlock Theme** baut das ZIP (Artifact).
4. Deploy: Workflow manuell starten; Secrets laut `ops/DEPLOY.md`. Default ist Dry-Run — Live erst mit `dry_run=false`.