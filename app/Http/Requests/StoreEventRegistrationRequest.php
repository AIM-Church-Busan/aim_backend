<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'event_id' => [
                'required',
                'integer',
                'exists:events,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'event_id.required' => 'Event is required.',
            'event_id.exists'   => 'The selected event does not exist.',
        ];
    }
}
