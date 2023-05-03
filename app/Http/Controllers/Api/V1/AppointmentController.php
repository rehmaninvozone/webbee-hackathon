<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AppointmentRequest;
use App\Http\Requests\V1\AvailableSlotsRequest;
use App\Http\Resources\V1\AppointmentResource;
use App\Http\Resources\V1\SchedulingResource;
use App\Models\Scheduling;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAvailableSlots(AvailableSlotsRequest $request)
    {
        $schedulings = Scheduling::findOrFail($request->scheduling_id)
            ->whereRelation('schedulingDays', fn($query) => $query->whereDate('date', $request->date))
            ->withWhereHas('slots', fn($query) => $query->where(['is_available' => true, 'date' => $request->date]))
            ->with('services')
            ->get();
        return SchedulingResource::collection($schedulings);
    }

    public function bookAppointment(AppointmentRequest $request)
    {
        $scheduling = Scheduling::with('slots', 'holidays', 'schedulingBreaks', 'appointments.appointmentDetails')
            ->findOrFail($request->scheduling_id);

        $startTime = Carbon::parse($request->start_time)->format('H:i:s');
        $endTime = Carbon::parse($request->end_time)->format('H:i:s');

        $featureBookingDate = today()->diffInDays($request->date);

        if ($featureBookingDate > $scheduling->days_in_advance) {
            return response()->json(['message' => 'You can only book ' . $scheduling->days_in_advance . ' days in advance'], 422);
        }

        $isHoliday = $scheduling->holidays()
            ->where('date', $request->date)
            ->exists();

        if ($isHoliday) {
            return response()->json(['message' => 'Not Available Holiday'], 422);
        }

        $isAlreadyBooked = $scheduling->slots()
            ->where([
                'start_time' => $startTime, 'end_time' => $endTime,
                'is_available' => false, 'date' => $request->date
            ])
            ->exists();

        if ($isAlreadyBooked) {
            return response()->json(['message' => 'Selected Appointment Time is Already Booked'], 422);
        }
        
        $isBreakTime = $scheduling->schedulingBreaks()
            ->whereBetweenTime($request->start_time, $request->end_time)
            ->exists();

        if ($isBreakTime) {
            return response()->json(['message' => 'Not Available Break Time'], 422);
        }

        $isBetweenAppointments = $scheduling->appointments()
            ->whereBetweenTime($request->start_time, $request->end_time)
            ->exists();

        if ($isBetweenAppointments) {
            return response()->json(['message' => 'Selected Appointment Time is Between Already Booked Appointment'], 422);
        }

        $isSlotExists = $scheduling->slots()
            ->where([
                'start_time' => $startTime, 'end_time' => $endTime,
                'date' => $request->date
            ])
            ->doesntExist();

        if ($isSlotExists) {
            return response()->json(['message' => "Slot Does’t Exists"], 404);
        }


        $appointment = $scheduling->appointments()->create($request->only('service_id', 'start_time', 'end_time', 'number_of_people', 'booked_by'));

        foreach ($request->booking_details as $booking) {
            $appointment->appointmentDetails()->create([
                'first_name' => $booking['first_name'],
                'last_name' => $booking['last_name'],
                'email' => $booking['email']
            ]);
        }

        $scheduling->slots()->where([
            'start_time' => $startTime, 'end_time' => $endTime,
            'date' => $request->date
        ])->update(['appointment_id' => $appointment->id, 'is_available' => false]);

        return new AppointmentResource($appointment->load('appointmentDetails'));
    }
}
