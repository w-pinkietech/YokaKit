#!/bin/bash
# T001: Dockerfile Validation Test (PinkieIt commit a5d3b77)
# Purpose: Validate Dockerfile structure matches a5d3b77 pattern
# Must FAIL before T003 implementation, PASS after

set -e

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DOCKERFILE="${REPO_ROOT}/Dockerfile"

echo "🧪 T001: Dockerfile Validation (a5d3b77 pattern)"
echo "================================================"

# Test 1: Dockerfile exists
if [ ! -f "$DOCKERFILE" ]; then
    echo "❌ FAIL: Dockerfile not found at $DOCKERFILE"
    exit 1
fi
echo "✅ PASS: Dockerfile exists"

# Test 2: Base image is PHP 8.2-apache
if ! grep -q "FROM php:8.2.*-apache" "$DOCKERFILE"; then
    echo "❌ FAIL: Base image must be PHP 8.2-apache"
    exit 1
fi
echo "✅ PASS: Base image is PHP 8.2-apache"

# Test 3: Required PHP extensions installed
REQUIRED_EXTENSIONS=("pdo_mysql" "mysqli" "mbstring" "exif" "pcntl" "bcmath" "gd")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! grep -q "$ext" "$DOCKERFILE"; then
        echo "❌ FAIL: Required extension missing: $ext"
        exit 1
    fi
done
echo "✅ PASS: All required PHP extensions present"

# Test 4: Apache mod_rewrite enabled
if ! grep -q "a2enmod rewrite" "$DOCKERFILE"; then
    echo "❌ FAIL: Apache mod_rewrite not enabled"
    exit 1
fi
echo "✅ PASS: Apache mod_rewrite enabled"

# Test 5: Apache configuration copied
if ! grep -q "COPY docker/apache/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf" "$DOCKERFILE"; then
    echo "❌ FAIL: Apache configuration not copied"
    exit 1
fi
echo "✅ PASS: Apache configuration copied"

# Test 6: Port 80 exposed
if ! grep -q "EXPOSE 80" "$DOCKERFILE"; then
    echo "❌ FAIL: Port 80 not exposed"
    exit 1
fi
echo "✅ PASS: Port 80 exposed"

# Test 7: Apache foreground command
if ! grep -q 'CMD.*apache2-foreground' "$DOCKERFILE"; then
    echo "❌ FAIL: Apache foreground command missing"
    exit 1
fi
echo "✅ PASS: Apache foreground command present"

# Test 8: MariaDB dev packages installed (for pdo_mysql)
if ! grep -q "libmariadb-dev" "$DOCKERFILE"; then
    echo "❌ FAIL: MariaDB development packages missing"
    exit 1
fi
echo "✅ PASS: MariaDB development packages present"

echo ""
echo "🎉 ALL TESTS PASSED: Dockerfile matches a5d3b77 pattern"
echo "Reference: PinkieIt commit a5d3b77ad98f34afae9ac7c6cd6be55770a4309c"
