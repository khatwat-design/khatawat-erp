#!/bin/bash
# ============================================================
# Laravel Forge - Deploy Commands Reference
# Paste these into Forge Site → Deployment Script (or use as-is)
# Forge injects: $FORGE_SITE_BRANCH, $FORGE_COMPOSER, $FORGE_PHP
# ============================================================

cd /home/forge/khatawat-api
git pull origin $FORGE_SITE_BRANCH
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader
( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload
) 9>/tmp/fpmlock
if [ -f artisan ]; then
    $FORGE_PHP artisan migrate --force
    $FORGE_PHP artisan config:cache
    $FORGE_PHP artisan route:cache
    $FORGE_PHP artisan view:cache
    $FORGE_PHP artisan queue:restart
fi
