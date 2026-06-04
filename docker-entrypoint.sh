#!/bin/bash
set -e

# Generate .env if missing
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Ensure APP_KEY exists
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --force
fi

# Ensure SQLite file exists (fallback if using sqlite)
if grep -q "^DB_CONNECTION=sqlite" .env 2>/dev/null; then
    touch database/database.sqlite
fi

# Run migrations
php artisan migrate --force 2>/dev/null || true

# Storage link
php artisan storage:link 2>/dev/null || true

# Install node dependencies
npm install --legacy-peer-deps

# Build frontend assets
npm run build

echo "========================================"
echo "  Starting phuduc dev server"
echo "  App:  http://localhost:8741"
echo "  Vite: http://localhost:5179"
echo "========================================"

# Run all services concurrently
npx concurrently -c "#93c5fd,#c4b5fd,#fb7185,#fdba74" \
    "php artisan serve --host=0.0.0.0 --port=8741 --no-reload" \
    "php artisan queue:listen --tries=1 --timeout=0" \
    "npm run dev -- --host 0.0.0.0 --port 5179" \
    --names=server,queue,vite \
    --kill-others
