<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AssociationSeeder::class,
            EventSeeder::class,
            EventRoundSeeder::class,
            EventTablesSeeder::class,
            // EventStandingsSeeder::class, (se c'è)
        ]);
    }
}
