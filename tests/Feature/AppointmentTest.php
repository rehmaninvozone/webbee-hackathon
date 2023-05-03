<?php

namespace Tests\Feature;

use App\Models\Scheduling;
use Tests\TestCase;

class AppointmentTest extends TestCase
{

    /**
     * A basic test example.
     */
    public array $data = [
        'scheduling_id' => 1,
        'service_id' => 1,
        'date' => '2023-05-03',
        'start_time' => '2023-05-03 08:00:00',
        'end_time' => '2023-05-03 08:10:00',
        'number_of_people' => 3,
        'booked_by' => 'ali@gmail.com',
        'booking_details' => [
            ['first_name' => 'Ali', 'last_name' => 'Raza', 'email' => 'ali@gmail.com'],
            ['first_name' => 'Bilal', 'last_name' => 'khalid', 'email' => 'bilal@gmail.com'],
            ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@gmail.com']
        ]
    ];


    public function test_a_user_can_book_appointment_on_holiday(): void
    {
        $this->data['date'] = now()->addDays(2)->format('Y-m-d');
        $this->postJson(route('bookAppointment'), $this->data)
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Not Available Holiday',
            ]);
    }

    public function test_a_user_can_exceed_future_book_appointment_limit(): void
    {
        $this->data['date'] = now()->addDays(8)->format('Y-m-d');
        $scheduling = Scheduling::find($this->data['scheduling_id']);
        $this->postJson(route('bookAppointment'), $this->data)
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'You can only book ' . $scheduling->days_in_advance . ' days in advance',
            ]);
    }

    public function test_a_user_can_book_appointment_between_appointments(): void
    {
        $this->data['date'] = now()->format('Y-m-d');
        $this->data['start_time'] = now()->format('Y-m-d') . ' 08:00:00';
        $this->data['end_time'] = now()->format('Y-m-d') . ' 08:09:00';
        $this->postJson(route('bookAppointment'), $this->data)
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Selected Appointment Time is Between Already Booked Appointment',
            ]);
    }

    public function test_a_user_can_book_non_existing_slot(): void
    {
        $this->data['start_time'] = now()->format('Y-m-d') . ' 07:00:00';
        $this->data['end_time'] = now()->format('Y-m-d') . ' 07:10:00';
        $this->data['date'] = now()->format('Y-m-d');
        $this->postJson(route('bookAppointment'), $this->data)
            ->assertNotFound()
            ->assertJson([
                'message' => 'Slot Does’t Exists',
            ]);
    }

    public function test_a_user_can_book_booked_appointment(): void
    {
        $this->data['date'] = now()->format('Y-m-d');
        $this->data['start_time'] = now()->format('Y-m-d') . ' 08:00:00';
        $this->data['end_time'] = now()->format('Y-m-d') . ' 08:10:00';
        $this->postJson(route('bookAppointment'), $this->data)
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Selected Appointment Time is Already Booked',
            ]);

    }

    public function test_a_user_can_book_appointment_between_break(): void
    {
        $this->data['date'] = now()->format('Y-m-d');
        $this->data['start_time'] = now()->format('Y-m-d') . ' 08:00:00';
        $this->data['end_time'] = now()->format('Y-m-d') . ' 08:15:00';
        $this->postJson(route('bookAppointment'), $this->data)
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Not Available Break Time',
            ]);
    }
}
