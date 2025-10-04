# ki Kraft Branding Implementation - Summary

## Overview

Vollständige Branding-Anpassung des Gatekeeper AI Plugins für **ki Kraft** (Non-Profit Organisation, Österreich).

**Branch:** `feat/branding-kikraft`  
**Status:** ✅ Alle Aufgaben abgeschlossen  
**Version:** 0.1.0 with ki Kraft branding

---

## Changed Files (14 files)

### Modified Files (9):
1. ✅ `gatekeeper-ai.php` - Plugin header, author, description, GKAI_BRAND constant
2. ✅ `composer.json` - Package name and author metadata
3. ✅ `package.json` - Author field and make-pot script
4. ✅ `readme.txt` - Contributors, description, Privacy section
5. ✅ `languages/gatekeeper-ai.pot` - POT file header with ki Kraft metadata
6. ✅ `src/Admin/SettingsPage.php` - Admin footer with ki Kraft link
7. ✅ `assets/admin.css` - CSS variables and footer styling
8. ✅ `src/Admin/Assets/index.js` - Title branding and About popover
9. ✅ `src/Public/Output/MetaTags.php` - Schema.org Organization JSON-LD

### New Files (5):
10. ✅ `SECURITY.md` - Security policy and reporting
11. ✅ `CODE_OF_CONDUCT.md` - Community guidelines
12. ✅ `PRIVACY.md` - Comprehensive privacy notice
13. ✅ `.github/FUNDING.yml` - Sponsor link to ki Kraft
14. ✅ `.github/workflows/php.yml` - CI workflow for quality checks

---

## Implementation Details

### Task 1: Plugin Headers & Metadata ✅

**gatekeeper-ai.php:**
```php
Author: ki Kraft
Author URI: https://kikraft.at/
Description: ... Von ki Kraft (Non-Profit, Österreich)
define('GKAI_BRAND', 'ki Kraft');
```

**composer.json:**
```json
{
  "name": "kikraft/gatekeeper-ai",
  "authors": [
    {
      "name": "ki Kraft",
      "homepage": "https://kikraft.at/"
    }
  ]
}
```

**package.json:**
```json
{
  "author": "ki Kraft (https://kikraft.at/)",
  "scripts": {
    "make-pot": "wp i18n make-pot . languages/gatekeeper-ai.pot --domain=gatekeeper-ai"
  }
}
```

**readme.txt:**
- Contributors: `kikraft` (nicht persönliche Namen)
- Description erwähnt ki Kraft als Non-Profit aus Österreich
- Neuer Abschnitt "== Privacy ==" mit Datenschutzhinweisen

**languages/gatekeeper-ai.pot:**
```
Project-Id-Version: Gatekeeper AI 0.1.0
Last-Translator: ki Kraft <team@kikraft.at>
Language: de_AT
Copyright: © 2025 ki Kraft
```

### Task 2: Code Branding ✅

**Konstante:**
- `GKAI_BRAND` definiert in `gatekeeper-ai.php` mit Wert `'ki Kraft'`

**Admin Footer:**
- `src/Admin/SettingsPage.php`: Footer mit "Built by ki Kraft" Link
- Link zu https://kikraft.at/ mit `target="_blank"` und `rel="noopener"`

**CSS:**
- `assets/admin.css`: CSS-Variablen hinzugefügt:
  ```css
  :root {
    --gkai-brand: #111;
    --gkai-brand-light: #757575;
    --gkai-accent: #0073aa;
  }
  ```
- Footer-Styling: `.gkai-admin-footer`
- TODO-Kommentar für CI-Farben und Logo

### Task 3: Legal & Transparency ✅

**SECURITY.md:**
- Meldeweg für Sicherheitsprobleme
- E-Mail-Platzhalter: `security@kikraft.at` (TODO)
- Supported versions Tabelle
- Best practices

**CODE_OF_CONDUCT.md:**
- Basierend auf Contributor Covenant 2.0
- All-contributors-freundlich
- Kontakt: `team@kikraft.at` (TODO)
- ki Kraft attribution

**PRIVACY.md:**
- Umfassende Datenschutzerklärung
- Welche Daten lokal gespeichert werden
- Was NICHT gesammelt wird (keine Telemetrie)
- GDPR-konforme Hinweise
- ki Kraft NPO-Kontext

**.github/FUNDING.yml:**
```yaml
custom: ["https://kikraft.at/"]
```

### Task 4: Internationalization ✅

**Neue Strings:**
Alle hinzugefügten UI-Strings verwenden `__()`:
- "Built by %s" in SettingsPage.php
- "by ki Kraft" in index.js
- "About", "Close" in index.js
- "Developed and maintained by" in index.js
- "Visit ki Kraft" in index.js

**make-pot Script:**
- Hinzugefügt in `package.json`
- Befehl: `npm run make-pot`
- Verwendet WP-CLI i18n command

### Task 5: Admin UI Polish ✅

**src/Admin/Assets/index.js:**

1. **Titel-Anpassung:**
   ```javascript
   h('h1', null,
     __('Gatekeeper AI', 'gatekeeper-ai'),
     h('span', { 
       style: { fontSize: '0.7em', fontWeight: 'normal', color: '#757575' },
       'aria-label': __('Developed by ki Kraft', 'gatekeeper-ai')
     }, __('by ki Kraft', 'gatekeeper-ai'))
   )
   ```

2. **About Popover:**
   - Toggle-Button rechts oben
   - Popover mit ki Kraft Beschreibung
   - Link zu https://kikraft.at/ mit externem Icon
   - Screenreader-freundlich
   - Schließbar mit X-Button

### Task 6: Schema.org Organization ✅

**src/Public/Output/MetaTags.php:**

- Neue Methode: `output_schema_org()`
- Ausgabe auf `is_front_page()` only
- JSON-LD Script-Tag:
  ```json
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "ki Kraft",
    "url": "https://kikraft.at/"
  }
  ```
- Action `gkai_schema_org_output` verhindert Duplikate
- @todo Kommentar für Logo, sameAs, etc.

### Task 7: Quality Gates ✅

**.github/workflows/php.yml:**

**Job 1: build-pot**
- Setup Node.js 20
- Install npm dependencies
- Install WP-CLI
- Run `npm run make-pot`
- Verify POT file created
- `continue-on-error: true` (non-blocking)

**Job 2: verify-plugin**
- Run `bin/verify.sh` if exists
- Check plugin structure

---

## Commits (4)

1. **a3d939a** - `chore(branding): set author and URIs to ki Kraft`
   - Plugin header, composer, package.json, readme, POT
   
2. **e117bca** - `docs: add PRIVACY.md, SECURITY.md, CODE_OF_CONDUCT.md`
   - Legal documentation, FUNDING.yml
   
3. **5d39efa** - `feat(admin): add ki Kraft branding and About popover`
   - Admin UI updates, Schema.org JSON-LD
   
4. **23fdb41** - `chore(ci): add GitHub workflow for POT generation and verification`
   - GitHub Actions workflow

---

## Test Instructions

### 1. Plugin Aktivierung (WordPress erforderlich)

```bash
# In WordPress Installation:
1. Plugin aktivieren
2. Zu Werkzeuge → Gatekeeper AI navigieren
```

**Erwartetes Ergebnis:**
- Titel zeigt "Gatekeeper AI by ki Kraft"
- "About"-Button oben rechts
- Footer mit "Built by ki Kraft" Link

### 2. About Popover

```
1. Auf "About"-Button klicken
2. Popover öffnet sich
3. "Visit ki Kraft"-Link zu https://kikraft.at/
```

### 3. Frontend (Schema.org)

```bash
# Homepage aufrufen
1. Seitenquelltext öffnen (Strg+U)
2. Nach "Schema.org Organization" suchen
```

**Erwartetes JSON-LD:**
```html
<!-- Gatekeeper AI - Schema.org Organization -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "ki Kraft",
  "url": "https://kikraft.at/"
}
</script>
```

**Validierung:**
- https://validator.schema.org/ verwenden
- Homepage-URL eingeben
- Sollte ohne Fehler validieren

### 4. POT-Datei generieren

```bash
# Voraussetzungen:
# - Node.js installiert
# - WP-CLI installiert (https://wp-cli.org/)

npm install
npm run make-pot

# Ergebnis prüfen:
ls -lh languages/gatekeeper-ai.pot
```

### 5. Plugin-ZIP erstellen

```bash
./bin/build-zip.sh

# Erstellt: gatekeeper-ai-0.1.0.zip
# Enthält alle Branding-Änderungen
```

---

## Akzeptanzkriterien - Status

| Kriterium | Status | Nachweis |
|-----------|--------|----------|
| In allen Headers/Metadaten steht **ki Kraft** statt persönlicher Namen | ✅ | gatekeeper-ai.php, composer.json, package.json, readme.txt |
| Admin-Settings-Seite zeigt „Gatekeeper AI — by ki Kraft" + Footer-Link | ✅ | SettingsPage.php, index.js |
| `composer.json`/`package.json` Autorenfelder zeigen **ki Kraft** | ✅ | Beide Dateien aktualisiert |
| `readme.txt` enthält Privacy-Hinweis und NPO-Kontext | ✅ | Neuer Privacy-Abschnitt |
| POT-Datei erneuert; alle neuen Strings sind i18n-fähig | ✅ | POT-Header aktualisiert, make-pot Script |
| Optionales JSON-LD auf der Startseite vorhanden und validierbar | ✅ | MetaTags.php, einmalige Ausgabe |
| SECURITY.md, CODE_OF_CONDUCT.md, PRIVACY.md vorhanden | ✅ | Alle erstellt |
| .github/FUNDING.yml mit ki Kraft Link | ✅ | Erstellt |
| GitHub Workflow für POT-Generation | ✅ | .github/workflows/php.yml |
| CSS-Variablen für Branding | ✅ | assets/admin.css |
| Alle neuen Strings in `__()` gewrapped | ✅ | SettingsPage.php, index.js |

---

## TODO / Nachfolgeaufgaben

1. **E-Mail-Adressen bestätigen:**
   - `security@kikraft.at` in SECURITY.md
   - `team@kikraft.at` in CODE_OF_CONDUCT.md, PRIVACY.md

2. **CI-Farben & Logo nachreichen:**
   - CSS-Variablen in `assets/admin.css` mit echten ki Kraft Farben ersetzen
   - Logo für Admin-UI hinzufügen (optional)

3. **Schema.org erweitern:**
   - Logo hinzufügen
   - sameAs für Social Media Profile
   - contactPoint für Support

4. **Website-Analyse:**
   - https://kikraft.at/ für Branding-Details analysieren
   - Farbpalette extrahieren
   - Logo-Assets anfordern

---

## Merge-Anleitung

```bash
# Branch ist ready für Merge
git checkout main
git merge feat/branding-kikraft

# Oder PR erstellen:
gh pr create --base main --head feat/branding-kikraft \
  --title "feat: ki Kraft branding and compliance updates" \
  --body "Complete branding overhaul for ki Kraft NPO (Austria)"
```

---

## Support & Kontakt

**Organisation:** ki Kraft (Non-Profit, Österreich)  
**Website:** https://kikraft.at/  
**Support:** team@kikraft.at (TODO: confirm)

---

**Erstellt:** Januar 2025  
**Version:** 0.1.0 mit ki Kraft Branding  
**Status:** ✅ Alle Aufgaben abgeschlossen  
**Nächster Schritt:** Testing & Merge
