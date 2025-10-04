=== Gatekeeper AI ===
Contributors: hermetik1
Tags: ai, crawler, robots, c2pa, content-provenance
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Granular control for AI crawlers with content provenance (C2PA-Light) in WordPress.

== Description ==

Gatekeeper AI gives you complete control over which AI bots can access your content, while also providing content provenance tracking for uploaded media.

= Key Features =

**AI Bot Management:**
* Control access for major AI crawlers (GPTBot, ClaudeBot, Google-Extended, PerplexityBot, Bytespider)
* Global allow/block lists with per-post overrides
* Route-based policies with wildcard pattern support
* Automatic robots.txt generation
* Meta robots tags and X-Robots-Tag headers

**Content Provenance (C2PA-Light):**
* Automatic manifest creation for uploaded images
* Track creator, creation date, and AI-assistance status
* JSON manifests stored in `/wp-content/uploads/gatekeeper-ai/`
* Optional badge display via shortcode `[gkai_badge id="123"]`

**Modern Admin Interface:**
* React-based settings page with tabs (Policies | C2PA | Logs)
* Per-post metabox for fine-grained control
* REST API integration with proper security
* **Debug Dashboard** with comprehensive logging and health checks

**Debug & Error Handling:**
* Multi-level logging system (ERROR, WARNING, INFO, DEBUG)
* Debug dashboard with log viewer and filtering
* System information and health checks
* Automatic error detection during activation
* Debug toolbar for developers
* Performance profiling tools

= Policy Priority =

Policies are merged with the following priority:
1. Per-post bot-specific overrides (highest priority)
2. Route-based patterns
3. Global settings (lowest priority)

= Requirements =

* WordPress 6.4 or higher
* PHP 8.1 or higher

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/gatekeeper-ai/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Tools → Gatekeeper AI to configure settings

== Frequently Asked Questions ==

= Which AI bots are supported? =

Currently supported bots:
* GPTBot (OpenAI)
* ClaudeBot (Anthropic)
* Google-Extended
* PerplexityBot
* Bytespider (ByteDance)

= How do per-post overrides work? =

When editing a post or page, you'll find a "Gatekeeper AI Policy" metabox in the sidebar. You can set a general policy (Default/Allow/Block) and override specific bots.

= What is C2PA-Light? =

C2PA-Light is a simplified content provenance system. When enabled, the plugin creates JSON manifest files for uploaded images containing metadata about creator, creation date, and whether the content is AI-assisted.

= Where are manifests stored? =

Manifests are stored as JSON files in `/wp-content/uploads/gatekeeper-ai/{attachment-id}.json`

= How do I display the C2PA badge? =

Use the shortcode `[gkai_badge id="123"]` where 123 is the attachment ID. You can also use the helper function `\AIPM\Public_\Badges\CredentialBadge::get_badge($attachment_id)` in your theme.

= How do I access the debug dashboard? =

Navigate to Tools → GKAI Debug in your WordPress admin. The debug dashboard provides:
* Log viewer with filtering and search
* System information
* Health checks for plugin dependencies
* Debug report export

= What should I do if plugin activation fails? =

The plugin now includes comprehensive error handling. If activation fails:
1. Check the error message displayed
2. Review debug logs at `wp-content/uploads/gatekeeper-ai-logs/debug.log`
3. Ensure minimum requirements are met (WordPress 6.4+, PHP 8.1+)
4. Export a debug report from Tools → GKAI Debug and report the issue

= How do I enable debug mode? =

Debug mode is automatically enabled when WP_DEBUG is enabled in wp-config.php. Additionally, you can access advanced debug features through the Debug Dashboard (Tools → GKAI Debug).

== Screenshots ==

1. Admin settings page with tabs
2. Policies tab with bot selection
3. C2PA settings
4. Per-post metabox

== Changelog ==

= 0.1.1 =
* Added comprehensive debug system
* Multi-level logging (ERROR, WARNING, INFO, DEBUG)
* Debug dashboard with log viewer, filtering, and search
* System information collector
* Health check utility for dependencies
* Debug toolbar in admin bar for developers
* Performance profiling tools
* Enhanced error handling during plugin activation
* Automatic version checks (WordPress 6.4+, PHP 8.1+)
* Safe mode activation with detailed error messages

= 0.1.0 =
* Initial MVP release
* Global and per-post AI bot policies
* Route-based policy patterns with wildcards
* Automatic robots.txt generation
* Meta robots and X-Robots-Tag headers
* C2PA-Light manifest creation for uploads
* React-based admin interface
* REST API endpoints for settings management
* Credential badge shortcode

== Upgrade Notice ==

= 0.1.0 =
Initial release of Gatekeeper AI MVP.

== Development ==

This plugin uses modern WordPress development practices:
* PSR-4 autoloading
* React for admin UI (WordPress components)
* REST API for data management
* Proper nonce verification and capability checks
* Sanitization and escaping throughout

For developers: The plugin namespace is `AIPM\` and all classes follow WordPress coding standards.

