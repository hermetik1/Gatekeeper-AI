#!/usr/bin/env bash
# Gatekeeper AI - Verification Script
# This script validates the plugin implementation

echo "=========================================="
echo "Gatekeeper AI - MVP Verification"
echo "=========================================="
echo ""

# Track results
PASS=0
FAIL=0

check() {
    local msg="$1"
    shift
    
    if "$@" > /dev/null 2>&1; then
        echo "✓ $msg"
        ((PASS++))
    else
        echo "✗ $msg"
        ((FAIL++))
    fi
}

# 1. PHP Syntax Check
echo "1. Checking PHP syntax..."
if find . -name "*.php" -not -path "./vendor/*" -not -path "./node_modules/*" -exec php -l {} \; 2>&1 | grep -v "No syntax errors" | grep -q "Parse error"; then
    echo "✗ PHP syntax errors found"
    ((FAIL++))
else
    echo "✓ All PHP files have valid syntax"
    ((PASS++))
fi

# 2. Check required files exist
echo ""
echo "2. Checking required files..."

check "Main plugin file exists" test -f gatekeeper-ai.php
check "Plugin class exists" test -f src/Plugin.php
check "PolicyManager exists" test -f src/Policies/PolicyManager.php
check "RobotsTxtGenerator exists" test -f src/Policies/RobotsTxtGenerator.php
check "HeadersGenerator exists" test -f src/Policies/HeadersGenerator.php
check "MetaTags output exists" test -f src/Public/Output/MetaTags.php
check "React admin UI exists" test -f src/Admin/Assets/index.js
check "C2PA ManifestBuilder exists" test -f src/C2PA/ManifestBuilder.php
check "REST API routes exist" test -f src/REST/Routes.php

# 3. Check for key functions/methods
echo ""
echo "3. Checking implementation completeness..."

check "PolicyManager has merged_for_path method" grep -q "merged_for_path" src/Policies/PolicyManager.php
check "PolicyManager has bot validation" grep -q "known_bots" src/Policies/PolicyManager.php
check "ManifestBuilder has create_manifest method" grep -q "create_manifest" src/C2PA/ManifestBuilder.php
check "CredentialBadge has shortcode registration" grep -q "register_shortcode" src/Public/Badges/CredentialBadge.php
check "Admin UI has Policies tab" grep -q "PoliciesTab" src/Admin/Assets/index.js
check "Admin UI has C2PA tab" grep -q "C2PATab" src/Admin/Assets/index.js

# 4. Check documentation
echo ""
echo "4. Checking documentation..."

check "Readme has minimum WP version" grep -q "Requires at least: 6.4" readme.txt
check "Readme has minimum PHP version" grep -q "Requires PHP: 8.1" readme.txt
check "Readme has installation instructions" grep -q "Installation" readme.txt

# 5. Security checks
echo ""
echo "5. Checking security measures..."

check "Post metabox uses nonce verification" grep -q "wp_verify_nonce" src/Admin/MetaBoxes/PostPolicyMetaBox.php
check "REST routes have permission callbacks" grep -q "permission_callback" src/REST/Routes.php
check "Input sanitization is present" grep -q "sanitize_text_field" src/Admin/MetaBoxes/PostPolicyMetaBox.php
check "Output escaping is present" grep -q "esc_html" src/Admin/MetaBoxes/PostPolicyMetaBox.php

# Summary
echo ""
echo "=========================================="
echo "Verification Summary"
echo "=========================================="
echo "Passed: $PASS"
if [ $FAIL -gt 0 ]; then
    echo "Failed: $FAIL"
    exit 1
else
    echo "All checks passed!"
    echo ""
    echo "The Gatekeeper AI MVP implementation is complete and ready for testing."
    exit 0
fi


