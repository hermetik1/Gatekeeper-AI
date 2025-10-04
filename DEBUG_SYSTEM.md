# Gatekeeper AI - Debug System Documentation

## Überblick (Overview)

Das Gatekeeper AI Plugin verfügt nun über ein umfassendes Debug- und Fehlerbehandlungssystem, das Entwicklern und Administratoren hilft, Probleme schnell zu identifizieren und zu beheben.

## Features

### 1. Error Logging System

Das Plugin verwendet ein mehrstufiges Logging-System:

- **ERROR**: Kritische Fehler, die sofortige Aufmerksamkeit erfordern
- **WARNING**: Warnungen, die auf potenzielle Probleme hinweisen
- **INFO**: Informative Meldungen über normale Operationen
- **DEBUG**: Detaillierte Debug-Informationen für Entwickler

#### Verwendung im Code

```php
use AIPM\Logging\Logger;

// Verschiedene Log-Level
Logger::error('Critical error message', ['context' => 'data']);
Logger::warning('Warning message');
Logger::info('Informational message');
Logger::debug('Debug message');
```

### 2. Plugin-Aktivierung mit Fehlerbehandlung

Das Plugin führt bei der Aktivierung mehrere Prüfungen durch:

- ✅ WordPress-Version (mindestens 6.4)
- ✅ PHP-Version (mindestens 8.1)
- ✅ Erforderliche Verzeichnisse werden erstellt
- ✅ Abhängigkeiten werden überprüft
- ✅ Standardeinstellungen werden initialisiert

Bei Fehlern während der Aktivierung:
- Der Fehler wird protokolliert
- Das Plugin wird automatisch deaktiviert
- Eine benutzerfreundliche Fehlermeldung wird angezeigt

### 3. Debug Dashboard

Zugriff über: **WP-Admin → Werkzeuge → GKAI Debug**

#### Logs Tab

- Anzeige aller Log-Einträge
- Filter nach Level (ERROR, WARNING, INFO, DEBUG)
- Suchfunktion
- Logs löschen
- Debug-Report exportieren

#### System Info Tab

Zeigt umfassende Systeminformationen:
- WordPress-Version und -Konfiguration
- PHP-Version und -Konfiguration
- Server-Information
- Plugin-Konfiguration
- Aktive Plugins
- Theme-Information

#### Health Check Tab

Automatische Überprüfung von:
- PHP-Version-Kompatibilität
- WordPress-Version-Kompatibilität
- Erforderliche Klassen
- Datei-Berechtigungen
- Autoloader-Status
- Plugin-Einstellungen
- Erforderliche Verzeichnisse
- WordPress-Abhängigkeiten

### 4. Debug Toolbar

Für Benutzer mit `manage_options`-Berechtigung wird in der Admin-Bar eine Debug-Toolbar angezeigt:

- **Performance-Metriken**: Ausführungszeit und Speicherverbrauch
- **Datenbank**: Anzahl der Queries
- **Hooks**: Anzahl der registrierten Actions und Filter
- Schnellzugriff zum Debug Dashboard

Die Toolbar kann in den Plugin-Einstellungen unter `debug.enabled` aktiviert werden.

### 5. Performance Profiling

Entwickler können Code-Abschnitte profilen:

```php
use AIPM\Debug\DebugToolbar;

DebugToolbar::start_profile('my_operation');
// ... Code to profile
DebugToolbar::end_profile('my_operation');
```

Die Profiling-Daten werden automatisch geloggt.

## Log-Datei

Logs werden gespeichert in:
```
wp-content/uploads/gatekeeper-ai-logs/debug.log
```

Das Verzeichnis ist durch `.htaccess` geschützt und über HTTP nicht zugänglich.

## Konfiguration

Die Debug-Einstellungen können in den Plugin-Optionen konfiguriert werden:

```php
$settings = get_option('gatekeeper_ai_settings');

// Logging-Konfiguration
$settings['logging']['enabled'] = true;        // Logging aktivieren/deaktivieren
$settings['logging']['level'] = Logger::INFO;  // Mindest-Log-Level

// Debug-Konfiguration
$settings['debug']['enabled'] = true;          // Debug-Modus aktivieren
$settings['debug']['profiling'] = false;       // Profiling aktivieren

update_option('gatekeeper_ai_settings', $settings);
```

## WP_DEBUG Integration

Das Plugin integriert sich mit WordPress' WP_DEBUG:

- Wenn `WP_DEBUG` aktiviert ist, registriert das Plugin automatisch einen PHP-Error-Handler
- PHP-Fehler aus dem Plugin werden in das Plugin-Log geschrieben
- Fatal Errors werden abgefangen und protokolliert

In `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## Debug-Report Export

Der Debug-Report enthält:
- Vollständige Systeminformationen
- Health Check Ergebnisse
- Die letzten 50 Log-Einträge

Export über:
1. Debug Dashboard → Logs Tab → "Export Debug Report"
2. Debug Dashboard → System Info Tab → "Export System Info"

Die Datei wird als `.txt` heruntergeladen und kann an Support gesendet werden.

## Log Analytics

Die Reports-Klasse bietet erweiterte Log-Analysen:

```php
use AIPM\Logging\Reports;

// Statistiken der letzten 7 Tage
$stats = Reports::get_statistics(7);

// Fehler-Trends der letzten 30 Tage
$trends = Reports::get_trends(30);

// Häufigste Fehler
$common_errors = Reports::get_common_errors(10);

// Logs als CSV exportieren
$csv = Reports::export_csv(1000, Logger::ERROR);
```

## Fehlerbehebung

### Problem: Plugin kann nicht aktiviert werden

1. Überprüfen Sie die WordPress-Debug-Logs:
   - `wp-content/debug.log` (wenn WP_DEBUG_LOG aktiviert)
   - `wp-content/uploads/gatekeeper-ai-logs/debug.log`

2. Stellen Sie sicher, dass die Mindestanforderungen erfüllt sind:
   - WordPress 6.4+
   - PHP 8.1+

3. Überprüfen Sie Datei-Berechtigungen:
   - `wp-content/uploads/` muss beschreibbar sein

### Problem: Debug Dashboard zeigt keine Logs

1. Überprüfen Sie, ob Logging aktiviert ist:
   - Debug Dashboard → Health Check Tab
   - Prüfen Sie Plugin Settings

2. Überprüfen Sie Datei-Berechtigungen:
   - `wp-content/uploads/gatekeeper-ai-logs/` muss beschreibbar sein

3. Leeren Sie den Cache (falls verwendet)

### Problem: Performance-Probleme

1. Reduzieren Sie den Log-Level auf WARNING oder ERROR
2. Deaktivieren Sie Profiling in den Debug-Einstellungen
3. Löschen Sie alte Logs regelmäßig

## Best Practices

### Für Entwickler

1. **Verwenden Sie passende Log-Levels**:
   - ERROR für kritische Fehler
   - WARNING für potenzielle Probleme
   - INFO für wichtige Ereignisse
   - DEBUG nur während der Entwicklung

2. **Fügen Sie Context hinzu**:
   ```php
   Logger::error('Failed to save settings', [
       'user_id' => get_current_user_id(),
       'settings' => $settings,
       'error' => $exception->getMessage()
   ]);
   ```

3. **Profilen Sie Performance-kritische Bereiche**:
   ```php
   DebugToolbar::start_profile('database_query');
   // Expensive operation
   DebugToolbar::end_profile('database_query');
   ```

### Für Administratoren

1. **Regelmäßige Log-Überprüfung**:
   - Überprüfen Sie das Debug Dashboard wöchentlich
   - Achten Sie auf wiederkehrende Fehler

2. **Log-Management**:
   - Löschen Sie alte Logs regelmäßig
   - Überwachen Sie die Log-Datei-Größe

3. **Health Checks**:
   - Führen Sie nach Updates einen Health Check durch
   - Beheben Sie Warnungen zeitnah

## API-Referenz

### Logger-Klasse

```php
namespace AIPM\Logging;

class Logger {
    // Log-Levels
    const ERROR = 'ERROR';
    const WARNING = 'WARNING';
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';
    
    // Methoden
    public static function init(): void;
    public static function log(string $level, string $message, array $context = []): bool;
    public static function error(string $message, array $context = []): bool;
    public static function warning(string $message, array $context = []): bool;
    public static function info(string $message, array $context = []): bool;
    public static function debug(string $message, array $context = []): bool;
    public static function get_entries(int $limit = 100, ?string $level = null, ?string $search = null): array;
    public static function clear(): bool;
    public static function get_size(): int;
}
```

### Reports-Klasse

```php
namespace AIPM\Logging;

class Reports {
    public static function get_statistics(int $days = 7): array;
    public static function get_trends(int $days = 30): array;
    public static function get_common_errors(int $limit = 10): array;
    public static function export_csv(int $limit = 1000, ?string $level = null, ?string $search = null): string;
    public static function get_summary(): array;
}
```

### SystemInfo-Klasse

```php
namespace AIPM\Debug;

class SystemInfo {
    public static function collect(): array;
}
```

### HealthCheck-Klasse

```php
namespace AIPM\Debug;

class HealthCheck {
    public static function run(): array;
}
```

### DebugToolbar-Klasse

```php
namespace AIPM\Debug;

class DebugToolbar {
    public static function register(): void;
    public static function start_profile(string $label): void;
    public static function end_profile(string $label): void;
    public static function get_profiles(): array;
}
```

## Support

Bei Problemen oder Fragen:
1. Überprüfen Sie das Debug Dashboard
2. Exportieren Sie einen Debug-Report
3. Erstellen Sie ein Issue auf GitHub mit dem Debug-Report

## Changelog

### Version 0.1.1
- ✅ Umfassendes Logging-System implementiert
- ✅ Debug Dashboard hinzugefügt
- ✅ System Info und Health Check implementiert
- ✅ Debug Toolbar für Entwickler
- ✅ Performance Profiling
- ✅ Erweiterte Fehlerbehandlung bei Aktivierung
- ✅ WP_DEBUG Integration
