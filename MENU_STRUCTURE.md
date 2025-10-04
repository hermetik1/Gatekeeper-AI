# Gatekeeper AI - Menu Structure Change

## Before (v0.1.0)

```
WordPress Admin
├── Dashboard
├── Posts
├── Media
├── Pages
├── Comments
├── ...
├── Tools
│   ├── Import
│   ├── Export
│   ├── Site Health
│   └── Gatekeeper AI  ← Old location
├── Settings
└── ...
```

## After (v0.1.1)

```
WordPress Admin
├── Dashboard
├── Posts
├── Media
├── Pages
├── Comments
├── ...
├── Tools
│   ├── Import
│   ├── Export
│   └── Site Health
├── Settings
├── ...
└── 🛡️ Gatekeeper AI  ← NEW: Top-level menu ⭐
    ├── Dashboard       ← Main settings (7 tabs)
    │   ├── Policies
    │   ├── Routes
    │   ├── Per-Post
    │   ├── C2PA
    │   ├── Logs
    │   ├── Tools
    │   └── About
    └── GKAI Debug      ← Debug dashboard
        ├── Logs
        ├── System Info
        └── Health Check
```

## Menu Details

### Top-Level Menu
- **Title**: Gatekeeper AI
- **Icon**: dashicons-shield-alt (shield)
- **Position**: 30 (after Comments, before Plugins)
- **Capability**: manage_options
- **Slug**: gatekeeper-ai

### Dashboard Submenu
- **Title**: Dashboard
- **Parent**: gatekeeper-ai
- **Callback**: Same as top-level (SettingsPage::render)
- **Contains**: React-based UI with 7 tabs

### GKAI Debug Submenu
- **Title**: GKAI Debug
- **Parent**: gatekeeper-ai (moved from 'tools.php')
- **Callback**: DebugPage::render
- **Contains**: Log viewer, system info, health checks

## Asset Loading

Assets now load for both hook patterns:
- `toplevel_page_gatekeeper-ai` (clicking main menu)
- `gatekeeper-ai_page_gatekeeper-ai` (clicking Dashboard submenu)

## Benefits

1. **Professional Appearance**: Dedicated top-level menu shows plugin importance
2. **Better UX**: No need to navigate through Tools menu
3. **Clearer Structure**: Main settings + Debug dashboard separation
4. **Accessibility**: Improved keyboard navigation with aria-current
5. **i18n Ready**: Translation support fully configured

## User Impact

When users upgrade from 0.1.0 to 0.1.1:
- Menu location changes automatically
- All settings preserved
- No manual configuration needed
- New menu appears immediately after activation

## Admin Bar Integration

The Debug Toolbar also updated:
- Links now point to `admin.php?page=gatekeeper-ai-debug`
- Previously pointed to `tools.php?page=gatekeeper-ai-debug`

## Navigation Flow

```
User Journey:
1. Click "Gatekeeper AI" in sidebar → Dashboard page loads
2. Configure bot policies, routes, C2PA, etc.
3. Click "GKAI Debug" → View logs and system info
4. Use tabs for different sections (keyboard accessible)
```

## Technical Implementation

```php
// Top-level menu
add_menu_page(
    __('Gatekeeper AI', 'gatekeeper-ai'),    // Page title
    __('Gatekeeper AI', 'gatekeeper-ai'),    // Menu title
    'manage_options',                         // Capability
    self::SLUG,                              // Menu slug
    [self::class, 'render'],                 // Callback
    'dashicons-shield-alt',                  // Icon
    30                                       // Position
);

// Dashboard submenu
add_submenu_page(
    self::SLUG,                              // Parent slug
    __('Dashboard', 'gatekeeper-ai'),        // Page title
    __('Dashboard', 'gatekeeper-ai'),        // Menu title
    'manage_options',                         // Capability
    self::SLUG,                              // Menu slug (same as parent)
    [self::class, 'render']                  // Callback (same as parent)
);

// Debug submenu
add_submenu_page(
    \AIPM\Admin\SettingsPage::SLUG,         // Parent slug (changed!)
    __('Gatekeeper AI Debug', 'gatekeeper-ai'),
    __('GKAI Debug', 'gatekeeper-ai'),
    'manage_options',
    'gatekeeper-ai-debug',
    [self::class, 'render']
);
```

## Testing

Run `php tests/test-admin-menu.php` to verify:
- ✓ Top-level menu registered
- ✓ dashicons-shield-alt icon set
- ✓ Dashboard submenu exists
- ✓ Debug submenu under correct parent
