=== Gatekeeper AI ===
Contributors: kikraft
Tags: ai, crawler, robots, c2pa, content-provenance
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 0.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Granular control for AI crawlers with content provenance (C2PA-Light) in WordPress. Developed by ki Kraft.

== Description ==

Gatekeeper AI gives you complete control over which AI bots can access your content, while also providing content provenance tracking for uploaded media.

**Developed by [ki Kraft](https://kikraft.at/)** - a non-profit organization based in Austria dedicated to promoting ethical and transparent use of AI technology.

= Key Features =

**AI Bot Management:**
* Control access for major AI crawlers (GPTBot, ClaudeBot, Google-Extended, PerplexityBot, Bytespider, Applebot-Extended, CCBot, Amazonbot)
* Global allow/block lists with route-based and per-post overrides
* Route-based policies with wildcard pattern support (e.g., `/blog/*`, `/uploads/*`)
* Quick presets: Block All, Blog Only, Media Only, Custom
* Automatic robots.txt generation with preview
* Meta robots tags and X-Robots-Tag headers
* Policy export/import for backup and migration

**Content Provenance (C2PA-Light):**
* Automatic manifest creation for uploaded images
* Track creator, creation date, and AI-assistance status
* JSON manifests stored in `/wp-content/uploads/gatekeeper-ai/`
* Media library scanner to check manifest status
* Optional badge display via shortcode `[gkai_badge id="123"]`

**Access Logging & Analytics:**
* GDPR-friendly logging (no personal data/IP addresses stored)
* Track bot access attempts with timestamp, path, and result
* Statistics: top bots, top routes, allow/block counts
* Configurable (enable/disable logging)
* Ring buffer storage (max 5000 entries)

**Modern Admin Interface:**
* React-based settings page with 7 tabs (accessible via **Gatekeeper AI** in admin menu):
  - **Policies**: Global bot allow/block with presets and preview
  - **Routes**: Path-based rules with wildcard support
  - **Per-Post**: Instructions for post-level overrides
  - **C2PA**: Content provenance settings and media scanner
  - **Logs**: Access logs, statistics, and filtering
  - **Tools**: Policy test, export/import, bot directory
  - **About**: ki Kraft info, privacy statement
* Debug dashboard at **Gatekeeper AI → GKAI Debug**
* Per-post metabox for fine-grained control
* REST API integration with proper security
* Policy validation and error messages

**Privacy & Security:**
* No telemetry or external data collection
* All data stays on your WordPress installation
* Optional logging stores only: timestamp, path, bot name, and decision
* Logs and manifests stored securely in `/wp-content/uploads/gatekeeper-ai-logs/` and `/wp-content/uploads/gatekeeper-ai/`
* Protected directories with .htaccess and index.php files
* Nonce verification and capability checks throughout
* Input sanitization and output escaping
* Deep sanitization for all settings data

= Policy Priority =

Policies are merged with the following priority (deterministic):
1. **Per-post** bot-specific overrides (highest priority)
2. **Route-based** patterns (most specific routes first)
3. **Global** settings (lowest priority)

= Supported AI Bots =

* **GPTBot** (OpenAI)
* **ClaudeBot** (Anthropic)
* **Google-Extended** (Google AI)
* **PerplexityBot** (Perplexity AI)
* **Bytespider** (ByteDance/TikTok)
* **Applebot-Extended** (Apple Intelligence)
* **CCBot** (Common Crawl AI)
* **Amazonbot** (Amazon)

= Requirements =

* WordPress 6.4 or higher
* PHP 8.1 or higher

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/gatekeeper-ai/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Gatekeeper AI** in the admin menu to configure settings


== Frequently Asked Questions ==

= Which AI bots are supported? =

Currently supported bots:
* **GPTBot** (OpenAI) - Default: Block
* **ClaudeBot** (Anthropic) - Default: Block
* **Google-Extended** (Google AI) - Default: Allow
* **PerplexityBot** (Perplexity AI) - Default: Block
* **Bytespider** (ByteDance/TikTok) - Default: Block
* **Applebot-Extended** (Apple Intelligence) - Default: Block
* **CCBot** (Common Crawl AI) - Default: Block
* **Amazonbot** (Amazon) - Default: Block

= How do I get started? =

1. Navigate to **Gatekeeper AI** in the admin menu
2. In the **Policies** tab, choose a preset or select individual bots
3. Click "Preview robots.txt" to see the generated rules
4. Save your settings
5. Your robots.txt will be automatically updated

= How do per-post overrides work? =

When editing a post or page, you'll find a "Bot Access Policy" metabox in the sidebar. You can:
* Set a general policy (Default/Allow/Block)
* Override specific bots
* Per-post rules have the highest priority

= How do route-based rules work? =

Go to the **Routes** tab to create path-specific rules:
* Pattern examples: `/blog/*`, `/wp-content/uploads/*`, `/products/*.pdf`
* Use `*` as a wildcard
* Routes override global settings but are overridden by per-post settings

= What is C2PA-Light? =

C2PA-Light is a simplified content provenance system. When enabled, the plugin creates JSON manifest files for uploaded images containing:
* Creator name (site name)
* Creation timestamp
* AI-assisted flag
* Source URL

= Does the plugin work with Multisite? =

Yes! Gatekeeper AI is Multisite-compatible:
* Settings are stored per-site (not network-wide)
* Each site in the network has its own bot policies and logs
* Network administrators can activate the plugin network-wide
* Site administrators manage their own Gatekeeper AI settings

= Where are logs and manifests stored? =

* **Logs**: `/wp-content/uploads/gatekeeper-ai-logs/debug.log`
* **C2PA Manifests**: `/wp-content/uploads/gatekeeper-ai/{attachment-id}.json`
* Both directories are protected with .htaccess and index.php files to prevent direct access

= How do I display the C2PA badge? =

Use the shortcode `[gkai_badge id="123"]` where 123 is the attachment ID. You can also use the helper function `\AIPM\Public_\Badges\CredentialBadge::get_badge($attachment_id)` in your theme.

= Is logging GDPR-compliant? =

Yes! Logging is completely optional and GDPR-friendly:
* No IP addresses stored
* No personal information collected
* Only logs: timestamp, path, bot name, access decision, and rule source
* Data stays on your server
* Can be disabled or cleared at any time

= How do I test my policies? =

Use the **Tools** tab:
1. Enter a URL path (e.g., `/blog/my-post`)
2. Click "Test"
3. See which bots would be allowed/blocked and why

= Can I backup my policies? =

Yes! In the **Tools** tab:
* Click "Export Policies" to download a JSON file
* Click "Import Policies" to restore from a backup
* Validation ensures imported policies are correct

= What happens if I click "Block All Bots"? =

The preset will:
* Add all known bots to the global block list
* Clear the allow list
* Generate a restrictive robots.txt
* You can still add route-based or per-post overrides

= Who develops Gatekeeper AI? =

Gatekeeper AI is developed by **[ki Kraft](https://kikraft.at/)**, a non-profit organization in Austria dedicated to ethical and transparent AI technology. We believe in empowering website owners with tools to control how AI bots access and use their content.

= Does the plugin collect any data? =

**No.** Gatekeeper AI:
* Has no telemetry
* Doesn't phone home
* Doesn't collect analytics
* Stores all data locally on your WordPress installation
* Respects your privacy completely

== Screenshots ==

1. Admin settings page with tabs
2. Policies tab with bot selection
3. C2PA settings
4. Per-post metabox

== Changelog ==

= 0.1.1 =
* **Admin Navigation Improvements**
  - Changed to top-level admin menu "Gatekeeper AI" with dashicons-shield-alt icon
  - Added Dashboard and GKAI Debug as submenus under Gatekeeper AI
  - Improved admin menu structure and accessibility
* **i18n Enhancements**
  - Added wp_set_script_translations for JavaScript translations
  - All strings use 'gatekeeper-ai' textdomain
  - Ready for full translation support
* **Security Hardening**
  - Enhanced Options::update() with deep sanitization for arrays
  - Added input whitelisting in REST API endpoints
  - Improved filesystem operations with validation and error handling
  - Better directory creation security (permissions, error checking)
  - Atomic file writes with LOCK_EX flag
  - Directory traversal prevention
* **Accessibility Improvements**
  - Added aria-current="page" to active tabs
  - Better keyboard navigation support
* **Testing Infrastructure**
  - Added test-admin-menu.php to verify menu registration
  - Added test-options-sanitizing.php to verify data sanitization
  - All tests passing

= 0.2.0 =
* **Complete Feature Implementation**
* Added 3 new AI bots: Applebot-Extended, CCBot (Common Crawl), Amazonbot
* Complete 7-tab admin interface:
  - Policies tab with presets and robots.txt preview
  - Routes tab for path-based rules
  - Per-Post tab with usage instructions
  - C2PA tab with media library scanner
  - Logs tab with statistics and filtering
  - Tools tab with test, export/import
  - About tab with ki Kraft information
* Enhanced robots.txt generator: deterministic, route-aware, alphabetically sorted
* Bot detection middleware for automatic access logging
* GDPR-friendly access logging (ring buffer, no PII)
* Policy validation: prevents bots in both lists, validates patterns
* Route pattern wildcards properly converted to regex
* Deterministic merge logic with proper priority
* REST API endpoints for all features
* Admin footer branding with ki Kraft
* Updated branding throughout to ki Kraft (https://kikraft.at/)
* Privacy-focused: no telemetry, no external connections

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

= 0.2.0 =
Major update with complete feature set, enhanced UI, access logging, and ki Kraft branding. All features from the specification implemented.

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

