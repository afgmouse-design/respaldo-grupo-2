<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tipos = Illuminate\Support\Facades\DB::table('tipo_periodo')->get();
echo "Total tipo_periodo: " . $tipos->count() . "\n";
foreach ($tipos as $t) {
    print_r($t);
}
