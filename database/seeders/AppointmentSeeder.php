<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Slot;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointment = Appointment::create(['service_id' => 1, 'scheduling_id' => 1, 'booked_by' => 'ali@gmail.com', 'number_of_people' => 3, 'start_time' => now()->format('Y-m-d') . ' 08:00:00', 'end_time' => now()->format('Y-m-d') . ' 08:10:00']);
        $appointment->appointmentDetails()->createMany([
            ['first_name' => 'Ali', 'last_name' => 'Raza', 'email' => 'ali@gmail.com'],
            ['first_name' => 'Bilal', 'last_name' => 'khalid', 'email' => 'bilal@gmail.com'],
            ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@gmail.com']
        ]);
        Slot::query()->where([
            'scheduling_id' => 1,
            'start_time' => '08:00:00', 'end_time' => '08:10:00',
            'date' => now()->format('Y-m-d')
        ])->update(['appointment_id' => $appointment->id, 'is_available' => false]);
    }
}
