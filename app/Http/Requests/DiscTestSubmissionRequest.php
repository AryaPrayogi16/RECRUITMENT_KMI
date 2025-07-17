<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscTestSubmissionRequest extends FormRequest
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
            'session_id' => 'required|integer|exists:disc_3d_test_sessions,id',
            'responses' => 'required|array|size:24',
            'responses.*.section_id' => 'required|integer|between:1,24',
            'responses.*.most_choice_id' => 'required|integer',
            'responses.*.least_choice_id' => 'required|integer',
            'responses.*.time_spent' => 'required|integer|min:1',
            'total_duration' => 'required|integer|min:1'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $responses = $this->input('responses', []);
            
            // Additional validation for each response
            foreach ($responses as $index => $response) {
                // Check if most and least choices are different
                if (isset($response['most_choice_id']) && 
                    isset($response['least_choice_id']) && 
                    $response['most_choice_id'] == $response['least_choice_id']) {
                    
                    $validator->errors()->add(
                        "responses.{$index}.choice_validation", 
                        "Section " . ($index + 1) . ": Pilihan MOST dan LEAST tidak boleh sama."
                    );
                }
                
                // Check section_id uniqueness
                $sectionIds = collect($responses)->pluck('section_id');
                if ($sectionIds->count() !== $sectionIds->unique()->count()) {
                    $validator->errors()->add(
                        "responses.{$index}.section_validation", 
                        "Terdapat section yang duplikasi dalam responses."
                    );
                }
            }
            
            // Check if all sections 1-24 are present
            $expectedSections = range(1, 24);
            $providedSections = collect($responses)->pluck('section_id')->sort()->values()->toArray();
            
            if ($providedSections !== $expectedSections) {
                $missing = array_diff($expectedSections, $providedSections);
                $extra = array_diff($providedSections, $expectedSections);
                
                if (!empty($missing)) {
                    $validator->errors()->add(
                        'responses.completeness', 
                        'Section yang hilang: ' . implode(', ', $missing)
                    );
                }
                
                if (!empty($extra)) {
                    $validator->errors()->add(
                        'responses.validity', 
                        'Section yang tidak valid: ' . implode(', ', $extra)
                    );
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'session_id.required' => 'Session ID diperlukan.',
            'session_id.exists' => 'Session tidak valid atau tidak ditemukan.',
            'responses.required' => 'Responses diperlukan.',
            'responses.array' => 'Responses harus berupa array.',
            'responses.size' => 'Harus ada tepat 24 responses untuk menyelesaikan test.',
            'responses.*.section_id.required' => 'Section ID diperlukan untuk setiap response.',
            'responses.*.section_id.between' => 'Section ID harus antara 1-24.',
            'responses.*.most_choice_id.required' => 'Pilihan MOST diperlukan untuk setiap response.',
            'responses.*.least_choice_id.required' => 'Pilihan LEAST diperlukan untuk setiap response.',
            'responses.*.time_spent.required' => 'Waktu response diperlukan untuk setiap response.',
            'responses.*.time_spent.min' => 'Waktu response minimal 1 detik.',
            'total_duration.required' => 'Total durasi test diperlukan.',
            'total_duration.min' => 'Total durasi minimal 1 detik.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'session_id' => 'Session ID',
            'responses' => 'Responses',
            'responses.*.section_id' => 'Section ID',
            'responses.*.most_choice_id' => 'Pilihan MOST',
            'responses.*.least_choice_id' => 'Pilihan LEAST',
            'responses.*.time_spent' => 'Waktu Response',
            'total_duration' => 'Total Durasi'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Ensure total_duration is calculated if not provided
        if (!$this->has('total_duration') && $this->has('responses')) {
            $totalTime = collect($this->input('responses', []))
                ->sum('time_spent');
            
            $this->merge([
                'total_duration' => $totalTime
            ]);
        }
    }

    /**
     * Get the validated data with additional processing
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);
        
        // Add calculated fields
        if ($key === null) {
            $validated['calculated_total_duration'] = collect($validated['responses'])
                ->sum('time_spent');
                
            $validated['sections_count'] = count($validated['responses']);
            $validated['average_time_per_section'] = $validated['calculated_total_duration'] / 24;
        }
        
        return $validated;
    }
}
