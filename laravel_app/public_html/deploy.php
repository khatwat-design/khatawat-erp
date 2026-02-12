<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);

$token = $_GET['token'] ?? null;
$expected = env('DEPLOY_TOKEN');

if ($expected && $token !== $expected) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$commands = [
    'migrate --force',
    'config:cache',
];

foreach ($commands as $command) {
    $status = $kernel->call($command);
    echo nl2br($kernel->output());
    if ($status !== 0) {
        echo '<br>Command failed: ' . htmlspecialchars($command, ENT_QUOTES, 'UTF-8');
        exit($status);
    }
}

echo '<br>Deployment completed.';
