<?php
define('LARAVEL_START', microtime(true));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== arsips ===\n";
$cols = DB::select('SHOW COLUMNS FROM arsips');
foreach ($cols as $c) {
    echo $c->Field . ' (' . $c->Type . ')' . PHP_EOL;
}
