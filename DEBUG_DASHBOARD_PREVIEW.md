# Gatekeeper AI - Debug Dashboard Preview

## Admin Interface Overview

### Debug Dashboard Location
```
WordPress Admin
└── Werkzeuge (Tools)
    └── GKAI Debug ← New menu item
```

## Tab 1: Logs

```
┌─────────────────────────────────────────────────────────────────┐
│ Gatekeeper AI - Debug Dashboard                                 │
├─────────────────────────────────────────────────────────────────┤
│ [Logs] [System Info] [Health Check]                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Filters:                                                         │
│ [All Levels ▼] [Search...      ] [100 entries ▼] [Filter]      │
│ [Clear Logs] [Export Debug Report]                              │
│                                                                  │
│ Log File Size: 45.2 KB                                          │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ [2025-01-20 10:15:23] [INFO] Plugin Initialization      │    │
│ │ === Plugin Initialization Started ===                    │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ [2025-01-20 10:15:24] [DEBUG] Admin service registered  │    │
│ │ Admin service provider registered                        │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ [2025-01-20 10:15:25] [WARNING] Cache not available     │    │
│ │ Object cache plugin not detected                         │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ [2025-01-20 10:16:01] [ERROR] Failed to save settings   │    │
│ │ Permission denied for user 123                           │    │
│ │ Context: {"user_id": 123, "action": "save_settings"}    │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

**Color Coding:**
- 🔴 ERROR: Red border, light red background
- 🟡 WARNING: Orange border, light orange background
- 🔵 INFO: Blue border, light blue background
- 🟣 DEBUG: Purple border, light purple background

## Tab 2: System Info

```
┌─────────────────────────────────────────────────────────────────┐
│ Gatekeeper AI - Debug Dashboard                                 │
├─────────────────────────────────────────────────────────────────┤
│ [Logs] [System Info] [Health Check]                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ System Information                    [Export System Info]      │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ WordPress                                                │    │
│ ├─────────────────────────────────────────────────────────┤    │
│ │ Version           │ 6.7                                  │    │
│ │ Site URL          │ https://example.com                  │    │
│ │ Multisite         │ No                                   │    │
│ │ Debug Mode        │ Enabled                              │    │
│ │ Memory Limit      │ 40M                                  │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ PHP                                                      │    │
│ ├─────────────────────────────────────────────────────────┤    │
│ │ Version           │ 8.1.27                               │    │
│ │ Memory Limit      │ 128M                                 │    │
│ │ Max Exec Time     │ 30s                                  │    │
│ │ Upload Max Size   │ 8M                                   │    │
│ │ Extensions        │ json, mbstring, curl, gd, imagick   │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ Server                                                   │    │
│ ├─────────────────────────────────────────────────────────┤    │
│ │ Software          │ nginx/1.24.0                         │    │
│ │ Protocol          │ HTTP/1.1                             │    │
│ │ HTTPS             │ Yes                                  │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ Plugin                                                   │    │
│ ├─────────────────────────────────────────────────────────┤    │
│ │ Version           │ 0.1.1                                │    │
│ │ Logging Enabled   │ Yes                                  │    │
│ │ Log Level         │ INFO                                 │    │
│ │ C2PA Enabled      │ No                                   │    │
│ │ Debug Mode        │ Yes                                  │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Tab 3: Health Check

```
┌─────────────────────────────────────────────────────────────────┐
│ Gatekeeper AI - Debug Dashboard                                 │
├─────────────────────────────────────────────────────────────────┤
│ [Logs] [System Info] [Health Check]                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Plugin Health Check                                              │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ ✓ PHP Version                                            │    │
│ │   Required: 8.1, Current: 8.1.27                         │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ ✓ WordPress Version                                      │    │
│ │   Required: 6.4, Current: 6.7                            │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ ✓ Required Classes                                       │    │
│ │   All required classes are loaded                        │    │
│ │   ▼ Details                                              │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ ! File Permissions                                       │    │
│ │   Not writable: /wp-content/cache                        │    │
│ │   ▼ Details                                              │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ ✓ Autoloader                                             │    │
│ │   Using fallback SPL autoloader                          │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ ✓ Plugin Settings                                        │    │
│ │   All settings configured                                │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ ✓ Required Directories                                   │    │
│ │   All required directories exist                         │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│ ┌─────────────────────────────────────────────────────────┐    │
│ │ ✓ WordPress Dependencies                                 │    │
│ │   All WordPress functions available                      │    │
│ └─────────────────────────────────────────────────────────┘    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

**Status Colors:**
- ✓ Green border: Check passed
- ! Yellow border: Warning (needs attention)
- ✗ Red border: Check failed (critical)

## Debug Toolbar (Admin Bar)

```
┌─────────────────────────────────────────────────────────────────┐
│ WordPress Logo  MySite  ...  [🔧 GKAI Debug 12.45ms ▼]  User  │
│                                  │                               │
│                                  ├─ Performance                  │
│                                  │  Time: 12.45ms                │
│                                  │  Memory: 2.1MB / 4.5MB peak   │
│                                  │                               │
│                                  ├─ Database                     │
│                                  │  15 queries                   │
│                                  │                               │
│                                  ├─ Hooks                        │
│                                  │  234 actions | 156 filters    │
│                                  │                               │
│                                  └─ View Debug Dashboard →       │
└─────────────────────────────────────────────────────────────────┘
```

## Activation Error Screen

When activation fails, users see:

```
┌─────────────────────────────────────────────────────────────────┐
│                                                                  │
│                    Plugin Activation Failed                      │
│                                                                  │
│  Gatekeeper AI requires PHP 8.1 or higher.                      │
│  You are running PHP 7.4.                                       │
│                                                                  │
│  Please upgrade your PHP version to continue.                   │
│                                                                  │
│                    [← Back to Plugins]                          │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Admin Notices

Success notice:
```
┌─────────────────────────────────────────────────────────────────┐
│ ✓ Logs cleared successfully.                              [×]   │
└─────────────────────────────────────────────────────────────────┘
```

Error notice:
```
┌─────────────────────────────────────────────────────────────────┐
│ ✗ Gatekeeper AI Error: Unable to create log directory.   [×]   │
└─────────────────────────────────────────────────────────────────┘
```

## Export Files

### Debug Report (TXT)
```
GATEKEEPER AI DEBUG REPORT
================================================================================

Generated: 2025-01-20 10:30:45

SYSTEM INFORMATION
--------------------------------------------------------------------------------

WordPress:
  Version: 6.7
  Site URL: https://example.com
  ...

PHP:
  Version: 8.1.27
  Memory Limit: 128M
  ...

HEALTH CHECK
--------------------------------------------------------------------------------

[PASS] PHP Version: Required: 8.1, Current: 8.1.27
[PASS] WordPress Version: Required: 6.4, Current: 6.7
...

RECENT LOG ENTRIES (Last 50)
--------------------------------------------------------------------------------

[2025-01-20 10:30:01] [INFO] === Plugin Initialization Started ===
[2025-01-20 10:30:02] [DEBUG] Admin service provider registered
...
```

## File Structure

```
wp-content/
├── plugins/
│   └── gatekeeper-ai/
│       ├── gatekeeper-ai.php (main file with error handling)
│       └── src/
│           ├── Logging/
│           │   ├── Logger.php (logging system)
│           │   └── Reports.php (analytics)
│           ├── Admin/
│           │   └── DebugPage.php (dashboard UI)
│           └── Debug/
│               ├── SystemInfo.php
│               ├── HealthCheck.php
│               └── DebugToolbar.php
└── uploads/
    └── gatekeeper-ai-logs/
        ├── debug.log (protected)
        ├── .htaccess (deny from all)
        └── index.php (silence is golden)
```

## Key Features Highlighted

1. **Multi-Tab Interface**: Clean navigation between Logs, System Info, and Health Check
2. **Color-Coded Logs**: Visual distinction between ERROR, WARNING, INFO, DEBUG
3. **Filtering & Search**: Easy to find specific log entries
4. **Export Functionality**: One-click debug report generation
5. **Health Checks**: Automated validation with clear status indicators
6. **Debug Toolbar**: Real-time performance metrics in admin bar
7. **Safe Activation**: Clear error messages instead of fatal errors
8. **Protected Logs**: Secure log storage with .htaccess protection

All interfaces follow WordPress admin styling for a consistent user experience.
