<?php

namespace Tests\Feature;

use Tests\TestCase;

class SchedulingTest extends TestCase
{

    /**
     * A basic test example.
     */
    public array $data = [
        "name" => "Men's Haircut",
        "description" => "Men's Haircut Scheduling",
        "max_clients_per_slot" => 3,
        "days_in_advance" => 7,
        "scheduling_days" => 6,
        "duration" => 10,
        "break_between_slots" => 5,
        "holidays" => [
            ["name" => "Sunday", "date" => "2023-05-07"],
            ["name" => "Public Holiday", "date" => "2023-05-05"]
        ],
        "breaks" => [
            ["name" => "Lunch Break", "start_time" => "12", "end_time" => "13"],
            ["name" => "Cleaning Break", "start_time" => "15", "end_time" => "16"]
        ],
        "opening_closing_time" => [
            ["date" => "2023-05-03", "opening_time" => "8", "closing_time" => "20"],
            ["date" => "2023-05-04", "opening_time" => "8", "closing_time" => "20"],
            ["date" => "2023-05-06", "opening_time" => "10", "closing_time" => "22"],
            ["date" => "2023-05-08", "opening_time" => "8", "closing_time" => "20"],
            ["date" => "2023-05-09", "opening_time" => "8", "closing_time" => "20"]
        ]
    ];


    public function test_a_user_can_create_schedule(): void
    {
        $response = $this->postJson(route('schedulings.store'), $this->data);
        $response->assertCreated();
        $response->assertJson([
            'data' => [
                "name" => "Men's Haircut",
            ],
        ]);
    }
}

