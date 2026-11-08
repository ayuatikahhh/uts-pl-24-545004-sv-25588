<?php

namespace App\Http\Requests;

use App\Models\Application;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            // status boleh tidak dikirim (bawaan: applied), tapi kalau dikirim harus salah satu nilai sah
            'status' => ['sometimes', 'required', Rule::in(Application::STATUSES)],
            'deadline' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /**
     * Mencatat validasi yang gagal sebelum Laravel membalas 422.
     * Yang dicatat hanya NAMA kolom yang gagal, bukan isinya.
     */
    protected function failedValidation(Validator $validator): void
    {
        Log::warning('Application validation failed', [
            'user_id' => $this->user()?->id,
            'action' => $this->isMethod('POST') ? 'application.create' : 'application.update',
            'reason' => 'validation_failed',
            'failed_fields' => array_keys($validator->errors()->messages()),
        ]);

        parent::failedValidation($validator);
    }

}