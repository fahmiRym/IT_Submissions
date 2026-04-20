<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'admin')->first();
if (!$user) {
    echo "No admin user found\n";
    exit;
}
$controller = app(App\Http\Controllers\Api\ArsipApiController::class);
$request = Illuminate\Http\Request::create('/api/arsip/dashboard', 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = $controller->getDashboard($request);
    echo $response->getContent();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
