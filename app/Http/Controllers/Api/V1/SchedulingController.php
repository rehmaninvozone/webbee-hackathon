<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreSchedulingRequest;
use App\Http\Requests\V1\UpdateSchedulingRequest;
use App\Http\Resources\V1\SchedulingResource;
use App\Models\Scheduling;
use Carbon\Carbon;

class SchedulingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SchedulingResource::collection(Scheduling::with(['services', 'schedulingDays', 'slots'])->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSchedulingRequest $request)
    {
        $index = 0;
        $scheduling = Scheduling::create($request->only('name', 'description', 'max_clients_per_slot', 'days_in_advance'));

        // Set the schedule for the next requested days
        $startDate = now()->startOfDay();
        $endDate = now()->addDays($request->scheduling_days)->endOfDay();

        // add holidays
        if ($request->holidays && count($request->holidays) > 0) {
            foreach ($request->holidays as $holiday) {
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

            if ($request->breaks && count($request->breaks) > 0) {
                foreach ($request->breaks as $break) {

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
                $break = $scheduling->schedulingBreaks()->whereBetweenTime($slotStartTime, $slotStartTime)->exists();

                if (!$break) {
                    //adding duration to every slot
                    $slotEndTime = $slotEndTime->addMinutes($request->duration);
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

                    if ($request->break_between_slots) {
                        $endBreakTime = $endBreakTime->addMinutes($request->break_between_slots);
                    }
                    $scheduling->schedulingBreaks()
                        ->create([
                            'name' => 'cleaning beak',
                            'start_time' => $startBreakTime,
                            'end_time' => $endBreakTime,
                        ]);
                }
                // update the start time for the next iteration
                if ($request->break_between_slots) {
                    $startTime = $startTime->addMinutes($request->duration + $request->break_between_slots);
                } else {
                    $startTime = $startTime->addMinutes($request->duration);
                }
            }
            $startDate->addDay();
        }
        return new SchedulingResource($scheduling);
    }

    /**
     * Display the specified resource.
     */
    public function show(Scheduling $scheduling)
    {
        return new SchedulingResource($scheduling->with(['services', 'schedulingDays', 'slots'])->get());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchedulingRequest $request, Scheduling $scheduling)
    {
        $scheduling->update($request->validated());
        return new SchedulingResource($scheduling);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scheduling $scheduling)
    {
        $scheduling->services()->delete();
        $scheduling->schedulingDays()->delete();
        $scheduling->schedulingBreaks()->delete();
        $scheduling->holidays()->delete();
        $scheduling->delete();
        return response()->noContent();
    }
}
