<?php

require __DIR__ . '/../bootstrap/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

/** @var \App\Console\Kernel $kernel */
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

$kernel->call('pillar:setup-test-database');