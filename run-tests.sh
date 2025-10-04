#!/bin/bash
#
# Simple test runner for Gatekeeper AI
# Usage: ./run-tests.sh
#

echo "================================"
echo "Gatekeeper AI Test Suite"
echo "================================"
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Track results
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Run admin menu tests
echo "Running admin menu tests..."
if php tests/test-admin-menu.php; then
    echo -e "${GREEN}✓ Admin menu tests passed${NC}"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ Admin menu tests failed${NC}"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo ""

# Run options sanitization tests
echo "Running options sanitization tests..."
if php tests/test-options-sanitizing.php; then
    echo -e "${GREEN}✓ Options sanitization tests passed${NC}"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ Options sanitization tests failed${NC}"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo ""

# Run REST nonce tests
echo "Running REST nonce tests..."
if php tests/test-rest-nonces.php; then
    echo -e "${GREEN}✓ REST nonce tests passed${NC}"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ REST nonce tests failed${NC}"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo ""

# Run frontend provider loading tests
echo "Running frontend provider loading tests..."
if php tests/test-frontend-provider.php; then
    echo -e "${GREEN}✓ Frontend provider loading tests passed${NC}"
    PASSED_TESTS=$((PASSED_TESTS + 1))
else
    echo -e "${RED}✗ Frontend provider loading tests failed${NC}"
    FAILED_TESTS=$((FAILED_TESTS + 1))
fi
TOTAL_TESTS=$((TOTAL_TESTS + 1))
echo ""

# Summary
echo "================================"
echo "Test Summary"
echo "================================"
echo "Total: $TOTAL_TESTS"
echo -e "Passed: ${GREEN}$PASSED_TESTS${NC}"
if [ $FAILED_TESTS -gt 0 ]; then
    echo -e "Failed: ${RED}$FAILED_TESTS${NC}"
    exit 1
else
    echo -e "Failed: $FAILED_TESTS"
    echo ""
    echo -e "${GREEN}All tests passed!${NC}"
    exit 0
fi
