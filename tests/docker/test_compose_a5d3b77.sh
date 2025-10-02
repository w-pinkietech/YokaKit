#!/bin/bash
# T002: docker-compose.yml Validation Test (PinkieIt commit a5d3b77)
# Purpose: Validate docker-compose.yml structure matches a5d3b77 pattern
# Must FAIL before T004 implementation, PASS after

set -e

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
COMPOSE_FILE="${REPO_ROOT}/docker-compose.yml"

echo "🧪 T002: docker-compose.yml Validation (a5d3b77 pattern)"
echo "======================================================="

# Test 1: docker-compose.yml exists
if [ ! -f "$COMPOSE_FILE" ]; then
    echo "❌ FAIL: docker-compose.yml not found at $COMPOSE_FILE"
    exit 1
fi
echo "✅ PASS: docker-compose.yml exists"

# Test 2: App service exists
if ! grep -q "^  app:" "$COMPOSE_FILE"; then
    echo "❌ FAIL: 'app' service not defined"
    exit 1
fi
echo "✅ PASS: App service defined"

# Test 3: DB service exists
if ! grep -q "^  db:" "$COMPOSE_FILE"; then
    echo "❌ FAIL: 'db' service not defined"
    exit 1
fi
echo "✅ PASS: DB service defined"

# Test 4: App service builds from Dockerfile
if ! grep -A5 "^  app:" "$COMPOSE_FILE" | grep -q "dockerfile: Dockerfile"; then
    echo "❌ FAIL: App service must build from Dockerfile"
    exit 1
fi
echo "✅ PASS: App service builds from Dockerfile"

# Test 5: App service exposes port 18080:80
if ! grep -A15 "^  app:" "$COMPOSE_FILE" | grep -q "18080:80"; then
    echo "❌ FAIL: App service must expose port 18080:80"
    exit 1
fi
echo "✅ PASS: App service exposes port 18080:80"

# Test 6: DB service uses MariaDB 10.11.4
if ! grep -A5 "^  db:" "$COMPOSE_FILE" | grep -q "mariadb:10.11.4"; then
    echo "❌ FAIL: DB service must use mariadb:10.11.4"
    exit 1
fi
echo "✅ PASS: DB service uses MariaDB 10.11.4"

# Test 7: Database named "yokakit" (Constitutional: YokaKit identity preservation)
if ! grep -A10 "^  db:" "$COMPOSE_FILE" | grep -q "MYSQL_DATABASE.*yokakit"; then
    echo "❌ FAIL: Database must be named 'yokakit' (Constitutional requirement)"
    exit 1
fi
echo "✅ PASS: Database named 'yokakit' (Constitutional compliance)"

# Test 8: YokaKit user credentials
if ! grep -A10 "^  db:" "$COMPOSE_FILE" | grep -q "MYSQL_USER.*yokakit"; then
    echo "❌ FAIL: MySQL user must be 'yokakit'"
    exit 1
fi
echo "✅ PASS: MySQL user is 'yokakit'"

# Test 9: Network defined
if ! grep -q "^networks:" "$COMPOSE_FILE"; then
    echo "❌ FAIL: Networks section missing"
    exit 1
fi
echo "✅ PASS: Network defined"

# Test 10: Volume for database persistence
if ! grep -q "^volumes:" "$COMPOSE_FILE" && ! grep -q "dbdata:" "$COMPOSE_FILE"; then
    echo "❌ FAIL: Database volume 'dbdata' missing"
    exit 1
fi
echo "✅ PASS: Database volume defined"

# Test 11: Container name for app service
if ! grep -A15 "^  app:" "$COMPOSE_FILE" | grep -q "container_name.*laravel-app"; then
    echo "❌ FAIL: App container must be named 'laravel-app'"
    exit 1
fi
echo "✅ PASS: App container named 'laravel-app'"

# Test 12: Docker Compose config validation
if command -v docker-compose &> /dev/null; then
    cd "$REPO_ROOT"
    if ! docker-compose config > /dev/null 2>&1; then
        echo "❌ FAIL: docker-compose config validation failed"
        exit 1
    fi
    echo "✅ PASS: docker-compose config is valid"
else
    echo "⚠️  SKIP: docker-compose not available, skipping syntax validation"
fi

echo ""
echo "🎉 ALL TESTS PASSED: docker-compose.yml matches a5d3b77 pattern"
echo "Reference: PinkieIt commit a5d3b77ad98f34afae9ac7c6cd6be55770a4309c"
