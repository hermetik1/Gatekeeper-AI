# Privacy Notice

## Gatekeeper AI - Data Handling & Privacy

**Developed by ki Kraft (Non-Profit, Austria)**

Gatekeeper AI is designed with privacy and data sovereignty in mind. This document explains what data the plugin stores and processes.

---

## Data We Store Locally

All data is stored **locally in your WordPress installation**. Nothing is transmitted to external services.

### 1. Plugin Settings

- **What**: Policy configurations, bot allow/block lists, route patterns
- **Where**: WordPress options table (`wp_options`)
- **Contains**: Non-personal technical configuration data

### 2. C2PA Manifests

- **What**: Content provenance metadata for uploaded media
- **Where**: `/wp-content/uploads/gatekeeper-ai/{attachment-id}.json`
- **Contains**: 
  - Creator name (from WordPress user profile)
  - Creation timestamp
  - AI-assistance flag
  - No personal data from site visitors

### 3. Post-Level Overrides

- **What**: Per-post/page policy settings
- **Where**: WordPress post meta table (`wp_postmeta`)
- **Contains**: Non-personal policy preferences

### 4. Activity Logs (Optional)

- **What**: Technical logs of bot access attempts
- **Where**: WordPress database (custom table, if logging is enabled)
- **Contains**: 
  - Bot user-agent strings
  - Requested URL paths
  - Timestamps
  - **NO personal visitor data** (IP addresses, cookies, etc.)
- **Note**: Logging is **disabled by default** and must be explicitly enabled by the site administrator

---

## Data We Do NOT Collect

❌ **No Telemetry**: We do not collect usage statistics or analytics  
❌ **No External Calls**: No data is sent to ki Kraft or any third party (except optional future bot directory updates, which will be opt-in)  
❌ **No Tracking**: We do not track site visitors  
❌ **No Personal Data**: We do not collect names, emails, IP addresses, or any personally identifiable information from site visitors  

---

## Third-Party Services

Gatekeeper AI does **not** connect to any external services in the current version (0.1.x).

**Planned (Optional, Opt-In):**
- Bot directory updates: Fetch latest bot list from a public directory (no data sent, only received)

---

## Your Rights (GDPR/Data Protection)

Since Gatekeeper AI stores data **only locally** in your WordPress installation:

- **You control all data**: You can view, export, or delete all plugin data at any time
- **No data processor agreements needed**: ki Kraft does not process your data
- **Standard WordPress practices apply**: Follow your existing WordPress backup and data retention policies

---

## Questions?

If you have questions about data handling in Gatekeeper AI, please contact:

📧 **team@kikraft.at** (TODO: confirm email)  
🌐 **https://kikraft.at/**

---

**Last Updated**: January 2025  
**Version**: 0.1.0  
**Maintained by**: [ki Kraft](https://kikraft.at/) - Non-Profit Organization, Austria
