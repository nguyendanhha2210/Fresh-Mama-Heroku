<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DummyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DummyUserSeeder::class,
            DummyRoleSeeder::class,
            DummyTypeSeeder::class,
            DummyWeightSeeder::class,
            DummyDescriptionSeeder::class,
        ]);
    }
}
