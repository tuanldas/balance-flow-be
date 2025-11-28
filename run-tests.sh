#!/bin/bash

# Script to run tests with proper testing environment
# Uses compose-testing.yml for isolated testing database

set -e

echo "================================"
echo "🧪 Starting Test Environment"
echo "================================"

# Step 1: Down all services
echo "📦 Stopping all running services..."
docker compose down

# Step 2: Start testing environment
echo "🚀 Starting testing environment..."
docker compose -f compose.yml -f compose-testing.yml up -d

# Wait for database to be ready
echo "⏳ Waiting for test database to be ready..."
sleep 5

# Step 3: Run migrations for test database
echo "📊 Running migrations for test database..."
docker compose exec app php artisan migrate:fresh --seed --force

# Step 4: Run tests
echo "🧪 Running tests..."
docker compose exec app php artisan test "$@"

# Step 5: Down testing environment
echo "🛑 Stopping testing environment..."
docker compose down

# Step 6: Restart development environment
echo "🔄 Restarting development environment..."
docker compose up -d

echo "================================"
echo "✅ Test execution completed"
echo "================================"
