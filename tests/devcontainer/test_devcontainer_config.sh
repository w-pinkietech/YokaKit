#!/bin/bash
# DevContainer Configuration Validation Test
# This test MUST FAIL before T038 implementation

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

FAILURES=0

echo "======================================"
echo "DevContainer Configuration Test"
echo "======================================"
echo ""

# Function to report failure
report_failure() {
    echo -e "${RED}✗ FAIL:${NC} $1"
    ((FAILURES++))
}

# Function to report success
report_success() {
    echo -e "${GREEN}✓ PASS:${NC} $1"
}

cd "$PROJECT_ROOT"

echo "[1/5] Checking .devcontainer directory..."
if [ -d ".devcontainer" ]; then
    report_success ".devcontainer directory exists"
else
    report_failure ".devcontainer directory missing"
fi

echo ""
echo "[2/5] Checking devcontainer.json..."
if [ -f ".devcontainer/devcontainer.json" ]; then
    report_success "devcontainer.json exists"

    # Check required fields
    if grep -q '"name"' .devcontainer/devcontainer.json; then
        report_success "devcontainer.json has 'name' field"
    else
        report_failure "devcontainer.json missing 'name' field"
    fi

    if grep -q '"dockerComposeFile"' .devcontainer/devcontainer.json; then
        report_success "devcontainer.json has 'dockerComposeFile' field"
    else
        report_failure "devcontainer.json missing 'dockerComposeFile' field"
    fi

    if grep -q '"service"' .devcontainer/devcontainer.json; then
        report_success "devcontainer.json has 'service' field"
    else
        report_failure "devcontainer.json missing 'service' field"
    fi

    if grep -q '"workspaceFolder"' .devcontainer/devcontainer.json; then
        report_success "devcontainer.json has 'workspaceFolder' field"
    else
        report_failure "devcontainer.json missing 'workspaceFolder' field"
    fi
else
    report_failure "devcontainer.json missing"
fi

echo ""
echo "[3/5] Checking required extensions..."
if [ -f ".devcontainer/devcontainer.json" ]; then
    # Check for essential Laravel extensions
    REQUIRED_EXTENSIONS=(
        "bmewburn.vscode-intelephense-client"
        "amiralizadeh9480.laravel-extra-intellisense"
        "onecentlin.laravel-blade"
        "ms-azuretools.vscode-docker"
    )

    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if grep -q "$ext" .devcontainer/devcontainer.json; then
            report_success "Extension configured: $ext"
        else
            report_failure "Extension missing: $ext"
        fi
    done
else
    report_failure "Cannot check extensions (devcontainer.json missing)"
fi

echo ""
echo "[4/5] Checking compose.override.yml..."
if [ -f ".devcontainer/compose.override.yml" ]; then
    report_success "compose.override.yml exists"

    if grep -q "yokakit" .devcontainer/compose.override.yml; then
        report_success "compose.override.yml uses YokaKit naming"
    else
        report_failure "compose.override.yml does not use YokaKit naming"
    fi
else
    report_failure "compose.override.yml missing"
fi

echo ""
echo "[5/5] Checking post-create script..."
if [ -f ".devcontainer/post-create.sh" ]; then
    report_success "post-create.sh exists"

    if [ -x ".devcontainer/post-create.sh" ]; then
        report_success "post-create.sh is executable"
    else
        report_failure "post-create.sh is not executable"
    fi
else
    report_failure "post-create.sh missing"
fi

echo ""
echo "======================================"
if [ $FAILURES -eq 0 ]; then
    echo -e "${GREEN}✓ DEVCONTAINER CONFIGURATION: VALID${NC}"
    echo "All required DevContainer files and configurations present."
    exit 0
else
    echo -e "${RED}✗ DEVCONTAINER CONFIGURATION: INVALID${NC}"
    echo "Found $FAILURES missing configuration(s)."
    echo ""
    echo "This is EXPECTED before T038 implementation."
    echo "After implementing T038, this test should PASS."
    exit 1
fi
