<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // A real application should authorize the current user and record here.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'in:general,work,personal'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'in:urgent,follow-up'],
            'priority' => ['required', 'in:normal,high'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'appointment_at' => ['required', 'date_format:Y-m-d\\TH:i'],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:8'],
            'attachment' => ['nullable', 'file', 'max:4096'],
            'details.reference' => ['required', 'string', 'max:80'],
        ];
    }
}
