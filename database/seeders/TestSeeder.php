<?php

namespace Database\Seeders;

use App\Models\Scheduling;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $request = array(
            "name" => "Men's Haircut",
            "description" => "Men's Haircut Scheduling",
            "max_clients_per_slot" => 3,
            "days_in_advance" => 7,
            "scheduling_days" => 6,
            "duration" => 10,
            "break_between_slots" => 5,
            "holidays" => array(
                array("name" => "Sunday", "date" => "2023-05-07"),
                array("name" => "Public Holiday", "date" => "2023-05-04")
            ),
            "breaks" => array(
                array("name" => "Lunch Break", "start_time" => "12", "end_time" => "13"),
                array("name" => "Cleaning Break", "start_time" => "15", "end_time" => "16")
            ),
            "opening_closing_time" => array(
                array("date" => "2023-05-02", "opening_time" => "8", "closing_time" => "20"),
                array("date" => "2023-05-03", "opening_time" => "8", "closing_time" => "20"),
                array("date" => "2023-05-05", "opening_time" => "8", "closing_time" => "20"),
                array("date" => "2023-05-06", "opening_time" => "10", "closing_time" => "22"),
                array("date" => "2023-05-08", "opening_time" => "8", "closing_time" => "10")
            )
        );


        $index = 0;
        $scheduling = Scheduling::create(['name' => "Men's Haircut", 'description' => "Men's Haircut Scheduling", 'max_clients_per_slot' => 3, 'days_in_advance' => 6]);

        // Set the schedule for the next requested days
        $startDate = now()->startOfDay();
        $endDate = now()->addDays(6)->endOfDay();

        // add holidays
        if ($request['holidays'] && count($request['holidays']) > 0) {
            foreach ($request['holidays'] as $holiday) {
                $scheduling->holidays()
                    ->create([
                        'name' => $holiday['name'],
                        'date' => $holiday['date'],
                    ]);
            }
        }

        // Loop through each day in the schedule
        while ($startDate <= $endDate) {
            $holidays = $scheduling->holidays()->whereDate('date', $startDate->format('Y-m-d'))->exists();

            if ($holidays) {
                $startDate->addDay();
                continue;
            }

            if ($request['breaks'] && count($request['breaks']) > 0) {
                foreach ($request['breaks'] as $break) {

                    $beakStart = Carbon::createFromTime($break['start_time'], 0)->setDateFrom($startDate);
                    $breakEnd = Carbon::createFromTime($break['end_time'], 0)->setDateFrom($startDate);

                    $scheduling->schedulingBreaks()
                        ->create([
                            'name' => $break['name'],
                            'start_time' => $beakStart,
                            'end_time' => $breakEnd,
                        ]);
                }
            }

            $dayOfWeek = $startDate->dayOfWeek;

            // Set the start and end times for each day
            $startTime = Carbon::createFromTime($request['opening_closing_time'][$index]['opening_time'], 0, 0)->setDateFrom($startDate);
            $endTime = Carbon::createFromTime($request['opening_closing_time'][$index]['closing_time'], 0, 0)->setDateFrom($startDate);

            //creating a weekly schedule
            $scheduling->schedulingDays()
                ->create([
                    'day_of_week' => $dayOfWeek,
                    'date' => $startDate,
                    'opening_time' => $startTime,
                    'closing_time' => $endTime
                ]);

            while ($startTime < $endTime) {
                $slotStartTime = clone $startTime;
                $slotEndTime = clone $startTime;

                //if $slotEndTime is between breaks ignore because we already add break at that time
                $break = $scheduling->schedulingBreaks()->where(function ($query) use ($slotStartTime) {
                    $query->where('start_time', '<=', $slotStartTime)
                        ->where('end_time', '>=', $slotStartTime);
                })->exists();

                if (!$break) {
                    //adding duration to every slot
                    $slotEndTime = $slotEndTime->addMinutes($request['duration']);
                    //creating weekly available slots
                    $scheduling->slots()
                        ->create([
                            'day_of_week' => $dayOfWeek,
                            'date' => $startDate,
                            'start_time' => $slotStartTime,
                            'end_time' => $slotEndTime
                        ]);
                    $startBreakTime = clone $slotEndTime;
                    $endBreakTime = clone $slotEndTime;

                    if ($request['break_between_slots']) {
                        $endBreakTime = $endBreakTime->addMinutes($request['break_between_slots']);
                    }
                    $scheduling->schedulingBreaks()
                        ->create([
                            'name' => 'cleaning beak',
                            'start_time' => $startBreakTime,
                            'end_time' => $endBreakTime,
                        ]);
                }
                // update the start time for the next iteration
                if ($request['break_between_slots']) {
                    $startTime = $startTime->addMinutes($request['duration'] + $request['break_between_slots']);
                } else {
                    $startTime = $startTime->addMinutes($request['duration']);
                }
            }
            $startDate->addDay();
        }
    }
}
