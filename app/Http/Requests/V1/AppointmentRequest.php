<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'scheduling_id' => ['required', 'exists:schedulings,id'],
            'service_id' => ['required', 'exists:services,id'],
            'start_time' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_time' => ['required', 'date_format:Y-m-d H:i:s'],
            'date' => ['required', 'date_format:Y-m-d'],
            'number_of_people' => ['required', 'integer', 'min:1'],
            'booked_by' => ['required', 'email'],
            'booking_details' => ['required', 'array', 'size:' . $this->number_of_people]
        ];
    }
}
