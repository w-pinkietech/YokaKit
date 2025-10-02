#!/bin/bash
# Docker Environment Health Check Script for YokaKit
# Validates that the Docker environment is running correctly

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
echo "Docker Environment Health Check"
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

# Function to report warning
report_warning() {
    echo -e "${YELLOW}⚠ WARNING:${NC} $1"
}

cd "$PROJECT_ROOT"

echo "[1/7] Checking Docker daemon..."
if docker info > /dev/null 2>&1; then
    report_success "Docker daemon is running"
else
    report_failure "Docker daemon is not running"
    exit 1
fi

echo ""
echo "[2/7] Checking Docker Compose installation..."
if docker compose version > /dev/null 2>&1; then
    COMPOSE_VERSION=$(docker compose version --short)
    report_success "Docker Compose installed (version: $COMPOSE_VERSION)"
else
    report_failure "Docker Compose is not installed or not accessible"
    exit 1
fi

echo ""
echo "[3/7] Checking running containers..."

# Check if containers are running
RUNNING_CONTAINERS=$(docker compose ps --services --filter "status=running" 2>/dev/null || true)

if echo "$RUNNING_CONTAINERS" | grep -q "web-app"; then
    report_success "yokakit-web-app container is running"
else
    report_failure "yokakit-web-app container is not running"
fi

if echo "$RUNNING_CONTAINERS" | grep -q "db"; then
    report_success "yokakit-db container is running"
else
    report_failure "yokakit-db container is not running"
fi

if echo "$RUNNING_CONTAINERS" | grep -q "mqtt"; then
    report_success "yokakit_mqtt_broker container is running"
else
    report_failure "yokakit_mqtt_broker container is not running"
fi

echo ""
echo "[4/7] Checking container health..."

# Check database health
DB_HEALTH=$(docker inspect --format='{{.State.Health.Status}}' yokakit-db 2>/dev/null || echo "unknown")
if [ "$DB_HEALTH" = "healthy" ]; then
    report_success "Database container is healthy"
elif [ "$DB_HEALTH" = "starting" ]; then
    report_warning "Database container is starting (healthcheck in progress)"
elif [ "$DB_HEALTH" = "unknown" ]; then
    report_warning "Database container health unknown (container may not be running)"
else
    report_failure "Database container is unhealthy (status: $DB_HEALTH)"
fi

echo ""
echo "[5/7] Checking network connectivity..."

# Check if yokakit network exists (may be prefixed with directory name)
YOKAKIT_NETWORK=$(docker network ls --filter "name=yokakit" --format "{{.Name}}" | head -1)

if [ -n "$YOKAKIT_NETWORK" ]; then
    report_success "YokaKit Docker network exists ($YOKAKIT_NETWORK)"

    # Check if containers are connected to the network
    NETWORK_CONTAINERS=$(docker network inspect "$YOKAKIT_NETWORK" --format '{{range .Containers}}{{.Name}} {{end}}' 2>/dev/null || true)

    if echo "$NETWORK_CONTAINERS" | grep -q "yokakit-web-app"; then
        report_success "web-app connected to yokakit network"
    else
        report_warning "web-app not connected to yokakit network"
    fi

    if echo "$NETWORK_CONTAINERS" | grep -q "yokakit-db"; then
        report_success "db connected to yokakit network"
    else
        report_warning "db not connected to yokakit network"
    fi

    if echo "$NETWORK_CONTAINERS" | grep -q "yokakit_mqtt_broker"; then
        report_success "mqtt connected to yokakit network"
    else
        report_warning "mqtt not connected to yokakit network"
    fi
else
    report_failure "YokaKit Docker network does not exist"
fi

echo ""
echo "[6/7] Checking HTTP connectivity..."

# Wait for web server to be ready (max 10 seconds)
MAX_RETRIES=10
RETRY_COUNT=0
HTTP_SUCCESS=false

while [ $RETRY_COUNT -lt $MAX_RETRIES ]; do
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:18080 | grep -q "200\|302"; then
        HTTP_SUCCESS=true
        break
    fi
    RETRY_COUNT=$((RETRY_COUNT + 1))
    sleep 1
done

if [ "$HTTP_SUCCESS" = true ]; then
    report_success "Web application accessible at http://localhost:18080"
else
    report_failure "Web application not accessible at http://localhost:18080"
fi

echo ""
echo "[7/7] Checking volumes..."

# Check if database volume exists
if docker volume ls | grep -q "yokakit.*dbdata\|dbdata"; then
    report_success "Database volume exists"
else
    report_warning "Database volume not found (may need to be created on first run)"
fi

# Check MQTT volumes
if [ -d "mqtt/mosquitto/config" ] && [ -d "mqtt/mosquitto/data" ] && [ -d "mqtt/mosquitto/log" ]; then
    report_success "MQTT volume directories exist"
else
    report_warning "MQTT volume directories incomplete"
fi

echo ""
echo "======================================"
if [ $FAILURES -eq 0 ]; then
    echo -e "${GREEN}✓ DOCKER ENVIRONMENT: HEALTHY${NC}"
    echo "All Docker environment checks passed successfully."
    exit 0
else
    echo -e "${RED}✗ DOCKER ENVIRONMENT: UNHEALTHY${NC}"
    echo "Found $FAILURES failure(s) in Docker environment."
    echo ""
    echo "To start the environment, run:"
    echo "  docker compose up -d"
    exit 1
fi
