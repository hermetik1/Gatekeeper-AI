# ki Kraft Branding Implementation - Test Guide

This document describes how to test the ki Kraft branding changes in Gatekeeper AI.

## Changes Summary

### Files Modified (7 files):
1. `gatekeeper-ai.php` - Updated plugin header with ki Kraft author and description
2. `composer.json` - Updated package name and author
3. `package.json` - Added author field and make-pot script
4. `readme.txt` - Updated contributors, description, and added Privacy section
5. `languages/gatekeeper-ai.pot` - Updated POT header with ki Kraft metadata
6. `src/Admin/SettingsPage.php` - Added admin footer with ki Kraft branding
7. `assets/admin.css` - Added CSS variables and footer styles
8. `src/Admin/Assets/index.js` - Added "by ki Kraft" to title and About popover
9. `src/Public/Output/MetaTags.php` - Added Schema.org Organization JSON-LD output

### Files Created (8 files):
1. `SECURITY.md` - Security policy and reporting guidelines
2. `CODE_OF_CONDUCT.md` - Community code of conduct
3. `PRIVACY.md` - Comprehensive privacy notice
4. `.github/FUNDING.yml` - Funding link to ki Kraft
5. `.github/workflows/php.yml` - CI workflow for POT generation
6. `BRANDING_TEST.md` - This file

## Test Checklist

### 1. Plugin Metadata Tests

#### Test 1.1: Plugin Header
```bash
# View the plugin header
head -15 gatekeeper-ai.php
```
**Expected:**
- Author: `ki Kraft`
- Author URI: `https://kikraft.at/`
- Description mentions: "Von ki Kraft (Non-Profit, Österreich)"

#### Test 1.2: Composer Metadata
```bash
cat composer.json
```
**Expected:**
- Package name: `kikraft/gatekeeper-ai`
- Authors array contains ki Kraft with homepage

#### Test 1.3: Package.json
```bash
cat package.json
```
**Expected:**
- Author field: `ki Kraft (https://kikraft.at/)`
- Script `make-pot` is defined

#### Test 1.4: Readme.txt
```bash
grep -A 5 "Contributors" readme.txt
```
**Expected:**
- Contributors: `kikraft` (not personal names)
- Description mentions ki Kraft as NPO from Austria

### 2. WordPress Admin UI Tests

These tests require a WordPress installation with the plugin activated.

#### Test 2.1: Settings Page Branding
1. Navigate to **Tools → Gatekeeper AI**
2. Check the page title: Should show "Gatekeeper AI by ki Kraft" (with "by ki Kraft" in lighter color)
3. Look at the top-right: Should see an "About" button
4. Scroll to bottom: Should see footer with "Built by ki Kraft" link

**Expected:**
- Title includes "by ki Kraft" in subdued styling
- About button is visible and clickable
- Footer link points to https://kikraft.at/

#### Test 2.2: About Popover
1. On the Gatekeeper AI settings page, click the **About** button
2. A notice box should appear with:
   - Description of Gatekeeper AI
   - "Developed and maintained by ki Kraft, a non-profit organization based in Austria"
   - "Visit ki Kraft" button with external link icon

**Expected:**
- Popover opens/closes correctly
- Link to https://kikraft.at/ opens in new tab
- Close button (X) works

#### Test 2.3: Footer Link
1. Scroll to bottom of settings page
2. Click the "ki Kraft" link in the footer

**Expected:**
- Link opens https://kikraft.at/ in new tab
- Link has proper `rel="noopener"` attribute

### 3. Frontend Tests

#### Test 3.1: Schema.org JSON-LD on Homepage
1. Visit your site's **homepage** (front page)
2. View page source (Ctrl+U or Cmd+Option+U)
3. Search for "Schema.org Organization"

**Expected:**
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

#### Test 3.2: Schema.org Validation
1. Go to https://validator.schema.org/
2. Enter your homepage URL or paste the JSON-LD snippet
3. Click **Run Test**

**Expected:**
- No errors
- Valid Organization schema detected

#### Test 3.3: No Duplicate JSON-LD
1. View homepage source
2. Count occurrences of "Schema.org Organization"

**Expected:**
- Should appear exactly **once** (not duplicated)

### 4. Documentation Tests

#### Test 4.1: Legal Files Exist
```bash
ls -1 *.md .github/FUNDING.yml
```
**Expected files:**
- `SECURITY.md`
- `CODE_OF_CONDUCT.md`
- `PRIVACY.md`
- `.github/FUNDING.yml`

#### Test 4.2: Privacy Notice Content
```bash
grep -i "ki kraft" PRIVACY.md
```
**Expected:**
- Multiple references to ki Kraft
- "Non-Profit, Austria" mentioned
- Contact email placeholder present

#### Test 4.3: Readme Privacy Section
```bash
grep -A 10 "== Privacy ==" readme.txt
```
**Expected:**
- Privacy section exists after FAQ
- Mentions no telemetry, no external calls
- Attributes plugin to ki Kraft

### 5. Internationalization (i18n) Tests

#### Test 5.1: POT File Header
```bash
head -15 languages/gatekeeper-ai.pot
```
**Expected:**
- Copyright: `© 2025 ki Kraft`
- Project-Id-Version includes Gatekeeper AI
- Language: `de_AT`
- Last-Translator: ki Kraft

#### Test 5.2: Generate POT File
```bash
# Requires WP-CLI and npm dependencies installed
npm install
npm run make-pot
```
**Expected:**
- Command completes without errors
- `languages/gatekeeper-ai.pot` is updated
- All translatable strings are included

### 6. Build & Package Tests

#### Test 6.1: ZIP Build
```bash
./bin/build-zip.sh
```
**Expected:**
- Creates `gatekeeper-ai-0.1.0.zip`
- ZIP includes all branding changes
- Excludes node_modules, .git, etc.

#### Test 6.2: Verify Script
```bash
./bin/verify.sh
```
**Expected:**
- All checks pass
- No errors reported

### 7. GitHub Integration Tests

#### Test 7.1: FUNDING.yml
1. Navigate to GitHub repository
2. Look for "Sponsor" button in the UI

**Expected:**
- Button links to https://kikraft.at/

#### Test 7.2: CI Workflow
1. Push changes to GitHub
2. Navigate to **Actions** tab
3. Check "PHP Quality Checks" workflow

**Expected:**
- Workflow runs successfully
- "Build POT File" job completes (may be non-blocking)
- "Verify Plugin Structure" job passes

## Manual Testing Summary

### Quick 5-Minute Test
1. ✅ Activate plugin in WordPress
2. ✅ Go to Tools → Gatekeeper AI
3. ✅ Verify "by ki Kraft" in title
4. ✅ Click "About" button → Check popover content
5. ✅ Verify footer link to ki Kraft
6. ✅ Visit homepage → View source → Find JSON-LD
7. ✅ Check readme.txt for privacy section

### Full Test (20 minutes)
- Run all tests in this document
- Validate Schema.org with online tool
- Build ZIP and inspect contents
- Review all new documentation files
- Test i18n string extraction

## Expected Outcomes

All tests should pass with:
- ✅ All metadata updated to ki Kraft
- ✅ Admin UI shows branding consistently
- ✅ Footer and About popover functional
- ✅ Schema.org Organization on homepage (no duplicates)
- ✅ Privacy documentation comprehensive
- ✅ CI workflow configured and working
- ✅ No console errors in browser
- ✅ No PHP errors in WordPress debug log

## Troubleshooting

### Issue: "by ki Kraft" not showing in admin
- **Check:** Browser cache (hard refresh: Ctrl+Shift+R)
- **Check:** Admin CSS loaded: `assets/admin.css`
- **Check:** JavaScript console for errors

### Issue: JSON-LD not appearing
- **Check:** You're on the front page (not a post/page)
- **Check:** Theme calls `wp_head()` action
- **Check:** No feed/AJAX/admin context

### Issue: About popover not working
- **Check:** JavaScript console for errors
- **Check:** React is loading: Look for `#gkai-app` in DOM
- **Check:** `src/Admin/Assets/index.js` is enqueued

### Issue: make-pot fails
- **Solution:** Install WP-CLI: https://wp-cli.org/#installing
- **Solution:** Run `npm install` first

## Notes for Developers

1. **CSS Variables**: Added in `assets/admin.css` with TODO for actual ki Kraft CI colors
2. **Email Placeholders**: TODO markers in SECURITY.md, CODE_OF_CONDUCT.md, PRIVACY.md for confirmed email addresses
3. **Schema.org**: Basic implementation with TODO for logo, sameAs, etc.
4. **i18n Ready**: All new strings wrapped in `__()` functions

## Support

If you encounter issues:
1. Check this test guide for troubleshooting steps
2. Review commit history for recent changes
3. Contact: team@kikraft.at (TODO: confirm)

---

**Last Updated:** January 2025  
**Version:** 0.1.0 with ki Kraft branding  
**Branch:** feat/branding-kikraft
