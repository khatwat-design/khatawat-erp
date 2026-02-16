#!/bin/bash
# ============================================================
# Rollback Script - Revert to previous Git state and reset Laravel
# ============================================================

set -e

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
LARAVEL_DIR="$ROOT_DIR"
if [ -d "$ROOT_DIR/laravel_app" ] && [ -f "$ROOT_DIR/laravel_app/artisan" ]; then
    LARAVEL_DIR="$ROOT_DIR/laravel_app"
fi

cd "$LARAVEL_DIR"

echo ">>> Resetting Git to previous state..."
git reset --hard ORIG_HEAD

echo ">>> Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear 2>/dev/null || true

echo ">>> Restarting queue..."
php artisan queue:restart

echo ">>> Fixing storage permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache 2>/dev/null || true

echo ">>> Rollback complete."
