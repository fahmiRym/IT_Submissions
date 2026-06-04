<?php
define('LARAVEL_START', microtime(true));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$controller = new App\Http\Controllers\Superadmin\ArsipController();
$response = $controller->edit(697);
echo $response->getContent() . "\n";
