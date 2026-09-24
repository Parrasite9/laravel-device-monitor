<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Tank PLC', 'Pump RTU', 'Boiler gateway'] as $name) {
            Device::firstOrCreate(['name' => $name], ['timeout_seconds' => 30]);
        }
    }
}
