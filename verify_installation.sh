#!/bin/bash
# Installation Verification Script for Online Bonafide Certificate System
# This script helps verify that the system was cloned and set up correctly

echo "========================================"
echo "Online Bonafide Certificate System"
echo "Installation Verification Script"
echo "========================================"
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counter for passed/failed checks
PASSED=0
FAILED=0
WARNINGS=0

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check 1: Git installation
echo -n "Checking Git installation... "
if command_exists git; then
    GIT_VERSION=$(git --version)
    echo -e "${GREEN}✓ $GIT_VERSION${NC}"
    ((PASSED++))
else
    echo -e "${RED}✗ Git not found${NC}"
    echo "  Install git to clone the repository"
    ((FAILED++))
fi

# Check 2: PHP installation
echo -n "Checking PHP installation... "
if command_exists php; then
    PHP_VERSION=$(php -v | head -n 1)
    echo -e "${GREEN}✓ $PHP_VERSION${NC}"
    ((PASSED++))
    
    # Check PHP version
    PHP_VER=$(php -r 'echo PHP_VERSION;')
    if php -r 'exit(version_compare(PHP_VERSION, "7.4.0", ">=") ? 0 : 1);'; then
        echo -e "  ${GREEN}✓ PHP version is 7.4 or higher${NC}"
        ((PASSED++))
    else
        echo -e "  ${YELLOW}⚠ PHP version $PHP_VER is below 7.4${NC}"
        ((WARNINGS++))
    fi
else
    echo -e "${RED}✗ PHP not found${NC}"
    echo "  Install PHP 7.4+ to run the system"
    ((FAILED++))
fi

# Check 3: Required PHP extensions
if command_exists php; then
    echo "Checking PHP extensions:"
    
    for ext in mysqli mbstring json session; do
        echo -n "  - $ext: "
        if php -m | grep -q "^$ext$" 2>/dev/null; then
            echo -e "${GREEN}✓ Loaded${NC}"
            ((PASSED++))
        else
            echo -e "${RED}✗ Missing${NC}"
            ((FAILED++))
        fi
    done
fi

# Check 4: MySQL/MariaDB
echo -n "Checking MySQL/MariaDB... "
if command_exists mysql; then
    MYSQL_VERSION=$(mysql --version)
    echo -e "${GREEN}✓ $MYSQL_VERSION${NC}"
    ((PASSED++))
else
    echo -e "${YELLOW}⚠ MySQL client not found in PATH${NC}"
    echo "  Ensure MySQL/MariaDB is installed (may be in XAMPP)"
    ((WARNINGS++))
fi

# Check 5: Required files
echo "Checking required files:"
REQUIRED_FILES=(
    "connection.php"
    "login.php"
    "register.php"
    "admin.php"
    "user.php"
    "form.php"
    "setup_database.php"
    "verify_system.php"
    "README.md"
)

for file in "${REQUIRED_FILES[@]}"; do
    echo -n "  - $file: "
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓ Found${NC}"
        ((PASSED++))
    else
        echo -e "${RED}✗ Missing${NC}"
        ((FAILED++))
    fi
done

# Check 6: Git repository
echo -n "Checking Git repository... "
if [ -d ".git" ]; then
    echo -e "${GREEN}✓ Git repository initialized${NC}"
    ((PASSED++))
    
    # Check remote URL
    REMOTE_URL=$(git config --get remote.origin.url 2>/dev/null)
    if [ -n "$REMOTE_URL" ]; then
        echo "  Remote URL: $REMOTE_URL"
        if [[ "$REMOTE_URL" == *"sksuraj2/Online-Bonafide-certificate-System"* ]]; then
            echo -e "  ${GREEN}✓ Correct repository${NC}"
            ((PASSED++))
        else
            echo -e "  ${YELLOW}⚠ Different repository URL${NC}"
            ((WARNINGS++))
        fi
    fi
else
    echo -e "${YELLOW}⚠ Not a git repository${NC}"
    echo "  Clone from: git clone https://github.com/sksuraj2/Online-Bonafide-certificate-System.git"
    ((WARNINGS++))
fi

# Summary
echo ""
echo "========================================"
echo "Summary"
echo "========================================"
echo -e "${GREEN}Passed: $PASSED${NC}"
echo -e "${YELLOW}Warnings: $WARNINGS${NC}"
echo -e "${RED}Failed: $FAILED${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✅ System verification completed successfully!${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Ensure XAMPP (or similar) is running"
    echo "2. Place this folder in htdocs directory"
    echo "3. Visit: http://localhost/Online-Bonafide-certificate-System/verify_system.php"
    echo "4. Run setup_database.php to create database tables"
    echo "5. Access the application via login.php"
    exit 0
else
    echo -e "${RED}❌ System verification found errors!${NC}"
    echo ""
    echo "Please fix the issues above before proceeding."
    echo "See README.md for detailed installation instructions."
    exit 1
fi
