<?php

namespace Database\Seeders;

use App\Models\Scheduling;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;

class WomanHaircutScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cleanupBreak = 10;
        //creating a Schedule for haircut
        $scheduling = Scheduling::create([
            'name' => 'Woman haircut',
            'description' => 'Scheduling date for Woman haircut',
            'max_clients_per_slot' => 3,
        ]);

        //creating Men Haircut Service
        $womanHaircutService = $scheduling->services()->create([
            'name' => 'Woman Haircut',
            'duration' => 60,
        ]);

        // Set the schedule for the next 7 days
        $startDate = now()->startOfDay();
        $endDate = now()->addDays(6)->endOfDay();

        $publicHolidayDay = now()->addDays(2);

        // Loop through each day in the schedule
        while ($startDate <= $endDate) {

            // Skip public holiday
            if ($startDate->isSameDay($publicHolidayDay)) {
                $scheduling->holidays()
                    ->create([
                        'name' => 'Public Holiday',
                        'date' => $startDate,
                    ]);
                $startDate->addDay();
                continue;
            }
            $dayOfWeek = $startDate->dayOfWeek;


            // Check if the day is Sunday (the salon is closed on Sundays)
            if ($dayOfWeek !== CarbonInterface::SUNDAY) {

                // Adding lunch and cleaning break
                $cleaningBreakStart = Carbon::createFromTime(15, 0)->setDateFrom($startDate);
                $cleaningBreakEnd = Carbon::createFromTime(16, 0)->setDateFrom($startDate);

                $lunchBreakStart = Carbon::createFromTime(12, 0)->setDateFrom($startDate);
                $lunchBreakEnd = Carbon::createFromTime(13, 0)->setDateFrom($startDate);

                $scheduling->schedulingBreaks()
                    ->createMany(
                        [[
                            'name' => 'lunch beak',
                            'start_time' => $lunchBreakStart,
                            'end_time' => $lunchBreakEnd,
                        ],
                            [
                                'name' => 'cleaning beak',
                                'start_time' => $cleaningBreakStart,
                                'end_time' => $cleaningBreakEnd,
                            ]]);

                // Set the start and end times for each day
                $startTime = Carbon::createFromTime(8, 0, 0)->setDateFrom($startDate);
                $endTime = Carbon::createFromTime(20, 0, 0)->setDateFrom($startDate);

                // If it's Saturday, adjust the time
                if ($startDate->dayOfWeek === CarbonInterface::SATURDAY) {
                    $startTime = Carbon::createFromTime(10, 0, 0)->setDateFrom($startDate);
                    $endTime = Carbon::createFromTime(22, 0, 0)->setDateFrom($startDate);
                }
                //creating a weekly schedule
                $scheduling->schedulingDays()
                    ->create([
                        'date' => $startDate,
                        'opening_time' => $startTime,
                        'closing_time' => $endTime
                    ]);

                while ($startTime < $endTime) {
                    $slotStartTime = clone $startTime;
                    $slotEndTime = clone $startTime;

                    //if $slotEndTime is between cleaning or lunch break ignore because we already add break at that time
                    if (!($slotStartTime->between($cleaningBreakStart, $cleaningBreakEnd) || $slotStartTime->between($lunchBreakStart, $lunchBreakEnd))) {

                        //adding 10 minutes to every slot
                        $slotEndTime = $slotEndTime->addMinutes($womanHaircutService->duration);

                        //creating weekly available slots
                        $scheduling->slots()
                            ->create([
                                'date' => $startDate,
                                'start_time' => $slotStartTime,
                                'end_time' => $slotEndTime
                            ]);
                        $startBreakTime = clone $slotEndTime;
                        $endBreakTime = clone $slotEndTime;
                        $endBreakTime = $endBreakTime->addMinutes($cleanupBreak);

                        $scheduling->schedulingBreaks()
                            ->create([
                                'name' => 'cleaning beak',
                                'start_time' => $startBreakTime,
                                'end_time' => $endBreakTime,
                            ]);
                    }
                    // update the start time for the next iteration
                    $startTime = $startTime->addMinutes($womanHaircutService->duration + $cleanupBreak);
                }
            } else {
                //Sunday is closed
                $scheduling->holidays()
                    ->create([
                        'name' => 'Sunday',
                        'date' => $startDate,
                    ]);
            }
            $startDate->addDay();
        }
    }
}
