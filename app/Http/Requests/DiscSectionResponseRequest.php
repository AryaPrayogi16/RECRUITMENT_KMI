<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscSectionResponseRequest extends FormRequest
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
            'section_id' => 'required|integer|between:1,24',
            'most_choice_id' => 'required|integer',
            'least_choice_id' => 'required|integer|different:most_choice_id',
            'time_spent' => 'required|integer|min:1|max:600',
            'revision_count' => 'integer|min:0'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'session_id.required' => 'Session ID diperlukan.',
            'session_id.exists' => 'Session tidak valid.',
            'section_id.required' => 'Section ID diperlukan.',
            'section_id.between' => 'Section ID harus antara 1-24.',
            'most_choice_id.required' => 'Pilihan MOST diperlukan.',
            'least_choice_id.required' => 'Pilihan LEAST diperlukan.',
            'least_choice_id.different' => 'Pilihan MOST dan LEAST tidak boleh sama.',
            'time_spent.required' => 'Waktu response diperlukan.',
            'time_spent.min' => 'Waktu response minimal 1 detik.',
            'time_spent.max' => 'Waktu response maksimal 10 menit.',
            'revision_count.integer' => 'Revision count harus berupa angka.',
            'revision_count.min' => 'Revision count tidak boleh negatif.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'session_id' => 'Session ID',
            'section_id' => 'Section ID',
            'most_choice_id' => 'Pilihan MOST',
            'least_choice_id' => 'Pilihan LEAST',
            'time_spent' => 'Waktu Response',
            'revision_count' => 'Jumlah Revisi'
        ];
    }
}