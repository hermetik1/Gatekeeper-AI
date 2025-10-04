# Gatekeeper AI - Debug System Quick Start

## 🚀 Was ist neu?

Das Plugin hat jetzt ein **umfassendes Debug-System**, das alle Anforderungen aus der Aufgabenstellung erfüllt:

### ✅ Aktivierungs-Schutz
- Keine fatalen Fehler mehr bei Plugin-Aktivierung
- Automatische Version-Checks (WordPress 6.4+, PHP 8.1+)
- Klare Fehlermeldungen wenn etwas nicht stimmt
- Safe-Mode: Plugin wird bei Fehlern automatisch deaktiviert

### ✅ Debug Dashboard
Zugriff: **WP-Admin → Werkzeuge → GKAI Debug**

**3 Tabs:**
1. **Logs** - Alle Plugin-Logs anzeigen, filtern, exportieren
2. **System Info** - Vollständige System-Informationen
3. **Health Check** - Automatische Dependency-Checks

### ✅ Logging System
- 4 Log-Levels: ERROR, WARNING, INFO, DEBUG
- Logs in: `wp-content/uploads/gatekeeper-ai-logs/debug.log`
- Geschützt durch .htaccess

### ✅ Debug Toolbar
- In der Admin-Bar (nur für Admins)
- Zeigt Performance-Metriken in Echtzeit
- Schnellzugriff zum Debug Dashboard

### ✅ Developer Tools
- Performance Profiling
- Log Analytics
- Export-Funktionen
- WP_DEBUG Integration

## 🎯 Sofort loslegen

### 1. Plugin aktivieren
```
WP-Admin → Plugins → Gatekeeper AI aktivieren
```
Bei Problemen wird jetzt eine klare Fehlermeldung angezeigt!

### 2. Debug Dashboard öffnen
```
WP-Admin → Werkzeuge → GKAI Debug
```

### 3. Health Check durchführen
```
Debug Dashboard → Health Check Tab → Status prüfen
```

### 4. Logs anschauen
```
Debug Dashboard → Logs Tab → Filter nutzen
```

## 📊 Debug-Funktionen im Detail

### Log-Level verstehen
- **ERROR** (rot): Kritische Fehler, sofort handeln
- **WARNING** (orange): Potenzielle Probleme, beobachten
- **INFO** (blau): Normale Operationen
- **DEBUG** (lila): Entwickler-Informationen

### Log-Filter nutzen
Im Logs Tab können Sie:
- Nach Level filtern (nur ERROR, WARNING, etc.)
- Nach Text suchen
- Anzahl der Einträge wählen (50-1000)
- Logs löschen
- Debug-Report exportieren

### Health Check verstehen
Grün (✓) = OK | Gelb (!) = Warnung | Rot (✗) = Fehler

Checks:
- PHP Version 8.1+
- WordPress Version 6.4+
- Alle Klassen vorhanden
- Verzeichnisse beschreibbar
- Einstellungen korrekt

### Debug Report exportieren
1. Logs Tab → "Export Debug Report"
2. TXT-Datei wird heruntergeladen
3. Enthält: System Info + Health Check + Logs
4. Perfekt für Support-Anfragen

## 💡 Für Entwickler

### Logger im Code nutzen
```php
use AIPM\Logging\Logger;

// Verschiedene Log-Levels
Logger::error('Fehler!', ['details' => $data]);
Logger::warning('Achtung!');
Logger::info('Info');
Logger::debug('Debug', ['var' => $value]);
```

### Performance profilen
```php
use AIPM\Debug\DebugToolbar;

DebugToolbar::start_profile('meine_funktion');
// ... Code ...
DebugToolbar::end_profile('meine_funktion');
// Automatisch geloggt mit Zeit und Speicher
```

### Debug-Toolbar aktivieren
```php
$settings = get_option('gatekeeper_ai_settings');
$settings['debug']['enabled'] = true;
update_option('gatekeeper_ai_settings', $settings);
```

### Log-Analyse
```php
use AIPM\Logging\Reports;

// Statistiken der letzten 7 Tage
$stats = Reports::get_statistics(7);

// Trends
$trends = Reports::get_trends(30);

// Häufigste Fehler
$errors = Reports::get_common_errors(10);
```

## 🔧 Fehlerbehebung

### "Plugin kann nicht aktiviert werden"
1. **Fehlermeldung lesen** - zeigt genau was fehlt
2. **Anforderungen prüfen**: WordPress 6.4+, PHP 8.1+
3. **Logs prüfen**: `wp-content/uploads/gatekeeper-ai-logs/debug.log`
4. **Berechtigungen**: `wp-content/uploads/` muss beschreibbar sein

### "Keine Logs sichtbar"
1. **Health Check** durchführen
2. **Logging aktivieren** in Plugin-Einstellungen
3. **Berechtigungen** für Log-Verzeichnis prüfen

### "Performance-Probleme"
1. **Log-Level** auf WARNING oder ERROR reduzieren
2. **Debug-Modus** deaktivieren
3. **Alte Logs** löschen (Logs Tab → Clear Logs)

## 📚 Vollständige Dokumentation

- **DEBUG_SYSTEM.md** - Umfassende Dokumentation (deutsch)
- **IMPLEMENTATION.md** - Technische Details
- **ZUSAMMENFASSUNG.md** - Komplette Feature-Übersicht

## ✨ Das Problem ist gelöst!

**Vorher:**
- ❌ Fataler Fehler bei Aktivierung
- ❌ Keine Fehlerinformationen
- ❌ Schwierig zu debuggen

**Jetzt:**
- ✅ Sichere Aktivierung mit klaren Meldungen
- ✅ Umfassendes Logging-System
- ✅ Debug Dashboard mit allen Tools
- ✅ Health Checks und Reports
- ✅ Developer Tools mit Profiling

## 🎉 Bereit!

Das Plugin ist jetzt produktionsreif und kann sicher aktiviert werden. Bei Problemen hilft das Debug-System sofort weiter!

**Viel Erfolg!** 🚀
