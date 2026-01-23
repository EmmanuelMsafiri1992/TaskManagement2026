<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check inventory table structure
echo "=== inventory table ===" . PHP_EOL;
$columns = DB::select('DESCRIBE inventory');
foreach ($columns as $col) {
    echo "  {$col->Field} ({$col->Type})" . PHP_EOL;
}

echo PHP_EOL . "=== legume_inventory table ===" . PHP_EOL;
$columns = DB::select('DESCRIBE legume_inventory');
foreach ($columns as $col) {
    echo "  {$col->Field} ({$col->Type})" . PHP_EOL;
}

echo PHP_EOL . "=== legume_products table ===" . PHP_EOL;
$columns = DB::select('DESCRIBE legume_products');
foreach ($columns as $col) {
    echo "  {$col->Field} ({$col->Type})" . PHP_EOL;
}

echo PHP_EOL . "=== Sample data ===" . PHP_EOL;
echo "inventory count: " . DB::table('inventory')->count() . PHP_EOL;
echo "legume_inventory count: " . DB::table('legume_inventory')->count() . PHP_EOL;
echo "legume_products count: " . DB::table('legume_products')->count() . PHP_EOL;
