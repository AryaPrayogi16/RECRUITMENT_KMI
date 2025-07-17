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
        return true; // Allow public access for candidates
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'test_mode' => 'nullable|string|in:fresh_start,resume',
            'agreement' => 'nullable|boolean',
            // Remove candidate_code validation - it's from URL parameter
            'screen_resolution' => 'nullable|string',
            'timezone' => 'nullable|string',
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
            '_token.required' => 'Token keamanan diperlukan.',
            'test_mode.in' => 'Mode test tidak valid.',
        ];
    }

    /**
     * Get validated data with defaults
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Set default values
        $validated['test_mode'] = $validated['test_mode'] ?? 'fresh_start';
        $validated['agreement'] = $validated['agreement'] ?? false;
        
        return $validated;
    }
}