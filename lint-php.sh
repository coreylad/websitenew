#!/bin/bash

################################################################################
# PHP Linting and Validation Script
# Ensures code quality and PHP 8.3+ compatibility
################################################################################

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counters
TOTAL_FILES=0
PASSED_FILES=0
FAILED_FILES=0
WARNINGS=0

echo -e "${BLUE}╔══════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║           PHP Linting and Validation Tool                        ║${NC}"
echo -e "${BLUE}║           PHP 8.3+ Compatibility Checker                         ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Function to check PHP syntax
check_syntax() {
    local file="$1"
    TOTAL_FILES=$((TOTAL_FILES + 1))
    
    if php -l "$file" > /dev/null 2>&1; then
        PASSED_FILES=$((PASSED_FILES + 1))
        return 0
    else
        FAILED_FILES=$((FAILED_FILES + 1))
        echo -e "${RED}✗ Syntax error in: $file${NC}"
        php -l "$file" 2>&1 | grep -v "^No syntax errors"
        return 1
    fi
}

# Function to check for strict types
check_strict_types() {
    local file="$1"
    if ! grep -q "declare(strict_types=1)" "$file"; then
        WARNINGS=$((WARNINGS + 1))
        return 1
    fi
    return 0
}

# Function to check for error suppression
check_error_suppression() {
    local file="$1"
    local count=$(grep -c "@" "$file" 2>/dev/null || echo 0)
    if [ "$count" -gt 0 ]; then
        return 1
    fi
    return 0
}

echo -e "${YELLOW}Phase 1: Checking PHP Syntax...${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Find all PHP files
PHP_FILES=$(find . -name "*.php" -type f \
    ! -path "./cache/*" \
    ! -path "./torrents/*" \
    ! -path "./.git/*" \
    ! -path "./misc/*" \
    2>/dev/null)

# Check syntax for all PHP files
SYNTAX_ERRORS=()
for file in $PHP_FILES; do
    if ! check_syntax "$file"; then
        SYNTAX_ERRORS+=("$file")
    fi
done

echo ""
echo -e "${YELLOW}Phase 2: Checking Code Quality...${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Count files with strict types
STRICT_TYPE_COUNT=0
for file in $PHP_FILES; do
    if check_strict_types "$file"; then
        STRICT_TYPE_COUNT=$((STRICT_TYPE_COUNT + 1))
    fi
done

# Count files with error suppression
ERROR_SUPPRESSION_COUNT=0
for file in $PHP_FILES; do
    if ! check_error_suppression "$file"; then
        ERROR_SUPPRESSION_COUNT=$((ERROR_SUPPRESSION_COUNT + 1))
    fi
done

echo ""
echo -e "${BLUE}╔══════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                         RESULTS                                  ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "Total Files Checked:          ${BLUE}$TOTAL_FILES${NC}"
echo -e "Files Passed:                 ${GREEN}$PASSED_FILES${NC}"
echo -e "Files Failed:                 ${RED}$FAILED_FILES${NC}"
echo ""
echo -e "${YELLOW}Code Quality Metrics:${NC}"
echo -e "Files with strict types:      ${GREEN}$STRICT_TYPE_COUNT${NC} / $TOTAL_FILES ($(awk "BEGIN {printf \"%.1f\", ($STRICT_TYPE_COUNT/$TOTAL_FILES)*100}")%)"
echo -e "Files with @ suppression:     ${YELLOW}$ERROR_SUPPRESSION_COUNT${NC}"
echo ""

if [ $FAILED_FILES -eq 0 ]; then
    echo -e "${GREEN}✓ All PHP files have valid syntax!${NC}"
    echo ""
    exit 0
else
    echo -e "${RED}✗ Found $FAILED_FILES files with syntax errors${NC}"
    echo ""
    echo -e "${RED}Failed files:${NC}"
    for file in "${SYNTAX_ERRORS[@]}"; do
        echo "  - $file"
    done
    echo ""
    exit 1
fi
