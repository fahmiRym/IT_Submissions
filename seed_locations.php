<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$locations = [
    'Physical Locations/INK/Billet Oven 1',
    'Physical Locations/INK/Billet Oven 2',
    'Physical Locations/INK/Billet Oven 3',
    'Physical Locations/INK/Billet Oven 4',
    'Physical Locations/INK/Billet Oven 5',
    'Physical Locations/INK/Billet Oven 6',
    'Physical Locations/INK/Billet Oven 7',
    'Physical Locations/INK/Unit 2 - Billet Oven 8',
    'Physical Locations/INK/Unit 2 - Billet Oven 9',
    'Physical Locations/INK/Unit 2 - Billet Oven 10',
    'Physical Locations/INK/Unit 3 - Billet Oven 11',
    'Physical Locations/INK/Unit 3 - Billet Oven 12',
    'Physical Locations/INK/Unit 3 - Billet Oven 13',
    'Physical Locations/INK/Unit 3 - Billet Oven 14',
    'Physical Locations/INK/Unit 3 - Billet Oven 15',
    'Physical Locations/INK/Unit 3 - Billet Oven 16',
    'Physical Locations/INK/Unit 3 - Billet Oven 17',
    'Physical Locations/INK/Unit 3 - Billet Oven 19',
    'Physical Locations/INK/Unit 3 - Billet Oven 20',
    'Physical Locations/INK/Unit 3 - Billet Oven 21',
    'Physical Locations/INK/Unit 3 - Billet Oven 22',
    'Physical Locations/INK/Unit 3 - Billet Oven 23',
    'Physical Locations/INK/Unit 4B - Billet Oven 24',
    'Physical Locations/INK/Unit 4B - Billet Oven 25',
    'Physical Locations/INK/Unit 4B - Billet Oven 26',
    'INK/Packing',
];

foreach ($locations as $name) {
    \App\Models\Location::firstOrCreate(['name' => $name], ['is_active' => true]);
}

echo "Locations seeded successfully.\n";
