<?php

declare(strict_types=1);

/**
 * Simple deployment helper for Hostinger.
 * Run from project root: php deploy.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

$commands = [
    'config:clear',
    'cache:clear',
    'migrate --force',
    'db:seed --force',
    'config:cache',
    'route:cache',
];

foreach ($commands as $command) {
    $status = $kernel->call($command);
    echo $kernel->output();
    if ($status !== 0) {
        echo "Command failed: {$command}\n";
        exit($status);
    }
}

echo "Deployment completed.\n";
