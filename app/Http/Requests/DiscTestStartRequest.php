<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscTestStartRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'candidate_code' => 'required|string|exists:candidates,candidate_code',
            'screen_resolution' => 'nullable|string',
            'timezone' => 'nullable|string',
            'test_mode' => 'nullable|string|in:fresh_start,resume',
            'browser_info' => 'nullable|array',
            'device_capabilities' => 'nullable|array'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'candidate_code.required' => 'Kode kandidat diperlukan.',
            'candidate_code.exists' => 'Kode kandidat tidak valid.',
            'test_mode.in' => 'Mode test tidak valid.',
            'screen_resolution.string' => 'Screen resolution harus berupa string.',
            'timezone.string' => 'Timezone harus berupa string.',
            'browser_info.array' => 'Browser info harus berupa array.',
            'device_capabilities.array' => 'Device capabilities harus berupa array.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'candidate_code' => 'Kode Kandidat',
            'screen_resolution' => 'Resolusi Layar',
            'timezone' => 'Zona Waktu',
            'test_mode' => 'Mode Test',
            'browser_info' => 'Info Browser',
            'device_capabilities' => 'Kemampuan Device'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Set default values
        $this->merge([
            'test_mode' => $this->input('test_mode', 'fresh_start'),
            'timezone' => $this->input('timezone', 'Asia/Jakarta'),
        ]);
    }
}