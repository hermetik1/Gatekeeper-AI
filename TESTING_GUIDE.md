# Gatekeeper AI - Testing Guide

## Installation & Activation Test

### Prerequisites
- WordPress 6.4+ with PHP 8.1+
- Fresh or existing WordPress installation

### Steps
1. Upload plugin to `/wp-content/plugins/gatekeeper-ai/`
2. Activate via Plugins menu
3. Verify no activation errors
4. Check that "Gatekeeper AI" appears under Tools menu

**Expected Result**: Plugin activates without errors.

---

## Test 1: Policies Tab - Block All Preset

### Steps
1. Navigate to **Tools → Gatekeeper AI**
2. Ensure you're on the **Policies** tab
3. Click **"Block All Bots"** preset button
4. Verify all 8 bots appear in the "Block Bots" column:
   - GPTBot
   - ClaudeBot
   - Google-Extended
   - PerplexityBot
   - Bytespider
   - Applebot-Extended
   - CCBot
   - Amazonbot
5. Click **"Save Settings"**
6. Look for success message

**Expected Result**: All bots moved to block list, settings saved successfully.

---

## Test 2: Preview robots.txt

### Steps
1. After saving "Block All" preset
2. Click **"Preview robots.txt"** button
3. Modal should open showing generated robots.txt

**Expected Output**:
```
User-agent: Amazonbot
Disallow: /

User-agent: Applebot-Extended
Disallow: /

User-agent: Bytespider
Disallow: /

User-agent: CCBot
Disallow: /

User-agent: ClaudeBot
Disallow: /

User-agent: GPTBot
Disallow: /

User-agent: Google-Extended
Disallow: /

User-agent: PerplexityBot
Disallow: /
```

3. Click **"Copy"** button
4. Close modal

**Expected Result**: Modal displays robots.txt, copy works, modal closes.

---

## Test 3: Verify Live robots.txt

### Steps
1. Open browser to: `https://your-site.com/robots.txt`
2. Compare content with preview

**Expected Result**: robots.txt matches preview (all bots blocked).

---

## Test 4: Routes Tab - Allow GPTBot on /blog/*

### Steps
1. Switch to **Routes** tab
2. Click **"Add Route"**
3. Enter pattern: `/blog/*`
4. In the route's "Allow" column, check **GPTBot**
5. Click **"Save Settings"**
6. Switch back to **Policies** tab
7. Click **"Preview robots.txt"**

**Expected Output**: GPTBot should show:
```
User-agent: GPTBot
Allow: /blog/
Disallow: /
```

**Expected Result**: Route added, robots.txt shows exception for /blog/ path.

---

## Test 5: Tools Tab - Test Merge

### Steps
1. Switch to **Tools** tab
2. In "Test Bot Access" section:
   - Path: `/blog/hello-world`
3. Click **"Test"** button
4. Review results in the gray box

**Expected Result**: JSON output showing:
- `path: "/blog/hello-world"`
- `merged_policy` showing GPTBot in allow list (due to route), others in block list

---

## Test 6: Per-Post Override

### Steps
1. Go to **Posts → Add New**
2. Enter title: "Test Post for Bot Policy"
3. Look for **"Bot Access Policy"** metabox in sidebar (may need to scroll)
4. Set general policy: **Allow**
5. In bot-specific overrides, select **GPTBot: Allow**
6. Click **Publish** or **Update**
7. Note the post ID

**Expected Result**: Post published with metabox settings saved.

---

## Test 7: Tools Tab - Test Per-Post Merge

### Steps
1. Return to **Tools → Gatekeeper AI → Tools** tab
2. In "Test Bot Access":
   - Path: `/test-post-for-bot-policy/` (or actual post URL path)
   - Note: post_id is automatically detected if path matches
3. Click **"Test"**

**Expected Result**: GPTBot should be in allow list (per-post override has highest priority).

---

## Test 8: C2PA Settings & Upload

### Steps
1. Switch to **C2PA** tab
2. Check **"Enable C2PA-Light"**
3. Check **"Mark uploads as AI-assisted by default"**
4. Click **"Save Settings"**
5. Go to **Media → Add New**
6. Upload a test image (any JPG/PNG)
7. Note the attachment ID after upload

**Expected Result**: Image uploaded successfully.

---

## Test 9: C2PA Media Scan

### Steps
1. Return to **C2PA** tab
2. Click **"Scan Media Library"**
3. Wait for results table to appear

**Expected Result**: 
- Table shows up to 20 random images
- Recently uploaded image should show "✓" in Manifest column
- Other images may show "—" if they were uploaded before C2PA was enabled

---

## Test 10: C2PA Manifest File Check

### Steps
1. Connect via FTP or File Manager
2. Navigate to: `/wp-content/uploads/gatekeeper-ai/`
3. Look for file: `{attachment-id}.json` (use ID from upload)
4. Download and open the JSON file

**Expected Content**:
```json
{
  "version": "1.0",
  "type": "c2pa-light",
  "generated_at": "2024-...",
  "attachment_id": 123,
  "creator": "Your Site Name",
  "created_at": "2024-...",
  "ai_assisted": true,
  "source": "http://..."
}
```

**Expected Result**: Manifest file exists and contains correct data.

---

## Test 11: Logs Tab - Enable Logging

### Steps
1. Switch to **Logs** tab
2. Check **"Enable Logging"**
3. Wait for save confirmation
4. Open your site in a private/incognito window
5. Visit a few pages
6. Return to **Logs** tab
7. Click **"Refresh"**

**Expected Result**: 
- Logging enabled message
- No logs yet (real bot requests needed, or see Test 12)

---

## Test 12: Simulate Bot Request (Advanced)

### Steps
1. Using curl or Postman:
```bash
curl -A "GPTBot/1.0" https://your-site.com/
curl -A "ClaudeBot/1.0" https://your-site.com/blog/
```

2. Return to **Logs** tab
3. Click **"Refresh"**

**Expected Result**: 
- Logs table shows entries
- GPTBot blocked on `/` (global policy)
- ClaudeBot may vary based on routes

**Note**: Logs only capture known bot user agents.

---

## Test 13: Logs Statistics

### Steps
1. With some log entries present
2. View the **Statistics** box above the logs
3. Check:
   - Total requests count
   - Top Bots list
   - Results (Allowed vs Blocked)

**Expected Result**: Statistics accurately reflect logged access attempts.

---

## Test 14: Clear Logs

### Steps
1. Click **"Clear Logs"** button
2. Confirm the alert dialog
3. Click **"Refresh"**

**Expected Result**: 
- Success message
- Logs table empty
- Statistics reset to 0

---

## Test 15: Export Policies

### Steps
1. Switch to **Tools** tab
2. Click **"Export Policies"**
3. Browser should download a JSON file

**Expected Content**:
```json
{
  "version": "1.0",
  "exported_at": "2024-...",
  "policies": {
    "global": {
      "allow": [],
      "block": ["GPTBot", "ClaudeBot", ...]
    },
    "routes": [
      {
        "pattern": "/blog/*",
        "allow": ["GPTBot"],
        "block": []
      }
    ],
    "per_post": {}
  }
}
```

**Expected Result**: File downloads with current policy configuration.

---

## Test 16: Import Policies

### Steps
1. Make a change to policies (e.g., remove all blocks)
2. Save settings
3. In **Tools** tab, click **"Import Policies"** button
4. Select the previously exported JSON file
5. Wait for confirmation

**Expected Result**: 
- Success message
- Policies restored to exported state
- Preview robots.txt to verify

---

## Test 17: Invalid Import

### Steps
1. Create a test file `invalid.json` with malformed content:
```json
{
  "policies": {
    "global": {
      "allow": ["FakeBot"],
      "block": []
    }
  }
}
```

2. Try to import this file

**Expected Result**: Error message about unknown bot "FakeBot" (validation catches it).

---

## Test 18: About Tab

### Steps
1. Switch to **About** tab
2. Verify content displays:
   - "Gatekeeper AI"
   - "Developed by ki Kraft"
   - Link to https://kikraft.at/
   - NPO description
   - Privacy statement
   - Features list

**Expected Result**: About page displays correctly with ki Kraft branding.

---

## Test 19: Admin Footer

### Steps
1. While on any Gatekeeper AI admin page
2. Scroll to bottom of page
3. Look at the footer text

**Expected Result**: Footer shows "Built by ki Kraft" with link to kikraft.at.

---

## Test 20: Per-Post Metabox Functionality

### Steps
1. Edit an existing post
2. Find "Bot Access Policy" metabox
3. Test each option:
   - General Policy: Default → Save → verify saved
   - General Policy: Allow → Save → verify saved  
   - General Policy: Block → Save → verify saved
4. Set per-bot overrides:
   - GPTBot: Allow
   - ClaudeBot: Block
5. Save post
6. Reload post editor

**Expected Result**: All selections persist after save and reload.

---

## Test 21: Route Pattern Validation

### Steps
1. Go to **Routes** tab
2. Add a route with invalid characters:
   - Pattern: `/blog/<script>alert(1)</script>`
3. Try to save

**Expected Result**: Validation error about invalid characters.

---

## Test 22: Bot in Both Lists Validation

### Steps
1. Go to **Policies** tab
2. Check GPTBot in Allow list
3. Check GPTBot in Block list (by clicking checkbox again in Block)
4. Click **"Save Settings"**

**Note**: The UI should prevent this by auto-unchecking the opposite list.

**Expected Result**: UI handles gracefully, server validates and rejects if both.

---

## Test 23: Preset Workflows

### Test "Blog Only" Preset:
1. Click **"Blog Only"** preset
2. All bots moved to Block list
3. Info message suggests adding routes
4. Add route `/blog/*` with all bots in Allow
5. Save
6. Preview robots.txt

**Expected**: Bots blocked globally, allowed on /blog/*.

### Test "Media Only" Preset:
1. Click **"Block Media Only"** preset
2. All bots moved to Allow list
3. Info message suggests adding routes
4. Add route `/wp-content/uploads/*` with all bots in Block
5. Save
6. Preview robots.txt

**Expected**: Bots allowed globally, blocked on /uploads/*.

### Test "Custom" Preset:
1. Click **"Custom (Clear)"**
2. All lists cleared

**Expected**: Empty allow/block lists.

---

## Test 24: C2PA Badge Shortcode

### Steps
1. Get attachment ID from uploaded image (from C2PA scan or Media Library)
2. Create a new post
3. Add shortcode: `[gkai_badge id="123"]` (replace 123 with actual ID)
4. Publish post
5. View post on frontend

**Expected Result**: Badge displays showing:
- "Content Provenance" header
- Creator name
- Creation date
- AI-Assisted indicator (if enabled)

---

## Test 25: Route Wildcards

### Test various patterns:
1. `/blog/*` - matches all under /blog/
2. `/uploads/*.pdf` - matches PDF files
3. `/products/*/reviews` - nested wildcards
4. `*` alone - matches everything (not recommended)

### Verification:
- Use **Tools → Test Bot Access** with different paths
- Check if patterns match as expected

---

## Test 26: Priority Testing

### Setup:
1. **Global**: Block GPTBot
2. **Route** `/test/*`: Allow GPTBot
3. **Per-Post** on post at `/test/special`: Block GPTBot

### Test:
- Path `/test/regular`: Should allow GPTBot (route overrides global)
- Path `/test/special` (with per-post): Should block GPTBot (per-post overrides route)
- Path `/other`: Should block GPTBot (global applies)

Use **Tools → Test** to verify.

**Expected**: Per-post > Route > Global priority respected.

---

## Test 27: Empty State Robots.txt

### Steps:
1. Go to **Policies** tab
2. Uncheck all bots from both lists
3. Remove all routes
4. Save
5. Preview robots.txt

**Expected Output**:
```
User-agent: *
Allow: /
```

**Expected Result**: Default open policy when no bots configured.

---

## Regression Testing Checklist

After any code changes, verify:
- [ ] Plugin activates without errors
- [ ] Settings page loads
- [ ] All 7 tabs render
- [ ] Save settings works
- [ ] robots.txt generates
- [ ] Preview modal works
- [ ] Routes can be added/removed
- [ ] Per-post metabox saves
- [ ] C2PA uploads create manifests
- [ ] Logs capture (if enabled)
- [ ] Export/Import works
- [ ] No JavaScript console errors
- [ ] No PHP errors in debug.log

---

## Performance Testing

### Large Route List:
1. Add 50+ routes
2. Test save performance
3. Test merge performance in Tools tab

**Expected**: Should handle dozens of routes without slowdown.

### Large Log Volume:
1. Enable logging
2. Simulate many bot requests (or wait for real traffic)
3. Check Logs tab with 1000+ entries

**Expected**: UI remains responsive, only shows first 200.

---

## Security Testing

### Nonce Verification:
1. Open browser DevTools
2. Try to call REST endpoints without proper nonce
3. Should get authentication error

### Capability Check:
1. Log in as Subscriber or Editor
2. Try to access Tools → Gatekeeper AI
3. Should see "Insufficient permissions"

### XSS Protection:
1. Try to inject script in route pattern: `/blog/<script>alert(1)</script>`
2. Validation should reject or escape properly

---

## Browser Compatibility

Test in:
- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Mobile browsers (iOS Safari, Chrome Android)

All features should work across browsers.

---

## Accessibility Testing

- [ ] Keyboard navigation works through all tabs
- [ ] Screen reader announces tab changes
- [ ] Form labels properly associated
- [ ] Error messages are announced
- [ ] Focus indicators visible

---

## Summary

Complete testing ensures:
1. ✅ All 7 tabs functional
2. ✅ Policies saved and applied correctly
3. ✅ robots.txt generated accurately
4. ✅ Routes work with wildcards
5. ✅ Per-post overrides function
6. ✅ C2PA manifests created
7. ✅ Logging captures bot access
8. ✅ Export/Import works
9. ✅ Validation prevents errors
10. ✅ UI responsive and intuitive
11. ✅ No security vulnerabilities
12. ✅ ki Kraft branding throughout

---

## Bug Reporting

If you find issues:
1. Check the browser console for JavaScript errors
2. Enable WP_DEBUG and check debug.log
3. Export a debug report from Tools → GKAI Debug (if available)
4. Report with:
   - WordPress version
   - PHP version
   - Steps to reproduce
   - Expected vs actual behavior
   - Error messages

Contact: https://kikraft.at/
