#!/bin/bash
# Constitutional Compliance Validation Script for YokaKit
# Validates adherence to YokaKit_Replay constitution requirements

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

VIOLATIONS=0

echo "======================================"
echo "Constitutional Compliance Validation"
echo "======================================"
echo ""

# Function to report violation
report_violation() {
    echo -e "${RED}✗ VIOLATION:${NC} $1"
    ((VIOLATIONS++))
}

# Function to report success
report_success() {
    echo -e "${GREEN}✓ PASS:${NC} $1"
}

# Function to report warning
report_warning() {
    echo -e "${YELLOW}⚠ WARNING:${NC} $1"
}

cd "$PROJECT_ROOT"

echo "[1/6] Checking for PinkieIt references in code..."
PINKIEIT_REFS=$(grep -r "pinkieit\|PinkieIt\|PINKIEIT" \
    --include="*.php" \
    --include="*.js" \
    --include="*.vue" \
    --include="*.blade.php" \
    --include="*.yml" \
    --include="*.yaml" \
    --include="*.json" \
    --include="*.conf" \
    --exclude-dir=vendor \
    --exclude-dir=node_modules \
    --exclude-dir=storage \
    app/ docker/ mqtt/ config/ 2>/dev/null || true)

if [ -n "$PINKIEIT_REFS" ]; then
    report_violation "PinkieIt references found in code (violates Identity Preservation)"
    echo "$PINKIEIT_REFS" | head -10
else
    report_success "No PinkieIt references in code"
fi

echo ""
echo "[2/6] Validating YokaKit naming in Docker configurations..."

# Check docker-compose.yml
if ! grep -q "yokakit-web-app" docker-compose.yml 2>/dev/null; then
    report_violation "docker-compose.yml missing 'yokakit-web-app' service name"
else
    report_success "Docker service name uses YokaKit naming"
fi

if ! grep -q "yokakit-db" docker-compose.yml 2>/dev/null; then
    report_violation "docker-compose.yml missing 'yokakit-db' container name"
else
    report_success "Database container uses YokaKit naming"
fi

if ! grep -q "networks:" docker-compose.yml && grep -q "yokakit:" docker-compose.yml 2>/dev/null; then
    report_success "Docker network uses YokaKit naming"
elif grep -q "networks:" docker-compose.yml && ! grep -q "yokakit:" docker-compose.yml 2>/dev/null; then
    report_violation "Docker network not using YokaKit naming"
fi

echo ""
echo "[3/6] Validating .env configuration..."

if [ -f ".env.example" ]; then
    if grep -q "APP_NAME='YokaKit'" .env.example 2>/dev/null; then
        report_success ".env.example uses YokaKit app name"
    else
        report_violation ".env.example does not use APP_NAME='YokaKit'"
    fi

    if grep -q "YOKAKIT_COPYRIGHT" .env.example 2>/dev/null; then
        report_success ".env.example uses YOKAKIT_COPYRIGHT variable"
    else
        report_warning ".env.example missing YOKAKIT_COPYRIGHT variable"
    fi
else
    report_warning ".env.example not found"
fi

echo ""
echo "[4/6] Checking directory structure (app/laravel/ pattern)..."

if [ -d "app/laravel" ]; then
    report_success "app/laravel/ directory structure exists"
else
    report_violation "app/laravel/ directory structure missing (CR3 requirement)"
fi

if [ -d "docker/base" ] && [ -d "docker/app" ]; then
    report_success "docker/base/ and docker/app/ structure exists"
else
    report_violation "docker/base/ and docker/app/ structure missing (CR3 requirement)"
fi

echo ""
echo "[5/6] Validating essential Laravel files..."

REQUIRED_FILES=(
    "app/laravel/artisan"
    "app/laravel/composer.json"
    "app/laravel/package.json"
    "app/laravel/app/Providers/AppServiceProvider.php"
    "app/laravel/storage/app/.gitignore"
    "app/laravel/bootstrap/cache/.gitignore"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        report_success "Required file exists: $file"
    else
        report_violation "Required file missing: $file"
    fi
done

echo ""
echo "[6/6] Checking Git commit message references..."

# Check recent commits for PinkieIt commit hash references
RECENT_COMMITS=$(git log --oneline -10 --grep="PinkieIt commit:" 2>/dev/null || true)

if [ -n "$RECENT_COMMITS" ]; then
    report_success "Recent commits reference PinkieIt commit hashes (audit trail)"
    echo "$RECENT_COMMITS" | head -5
else
    report_warning "No recent commits with PinkieIt commit references (check if audit trail maintained)"
fi

echo ""
echo "======================================"
if [ $VIOLATIONS -eq 0 ]; then
    echo -e "${GREEN}✓ CONSTITUTIONAL COMPLIANCE: PASS${NC}"
    echo "All constitutional requirements validated successfully."
    exit 0
else
    echo -e "${RED}✗ CONSTITUTIONAL COMPLIANCE: FAIL${NC}"
    echo "Found $VIOLATIONS violation(s) of constitutional requirements."
    exit 1
fi
