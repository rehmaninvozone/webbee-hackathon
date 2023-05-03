<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(MenHaircutScheduleSeeder::class);
        $this->call(AppointmentSeeder::class);

//        $this->call(TestSeeder::class);

//        $this->call(WomanHaircutScheduleSeeder::class);
    }
}
