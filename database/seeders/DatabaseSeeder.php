<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            JabatanSeeder::class,
            UnitOrganisasiSeeder::class,
            UserSeeder::class,
            AlurApprovalSeeder::class,
        ]);
    }
}