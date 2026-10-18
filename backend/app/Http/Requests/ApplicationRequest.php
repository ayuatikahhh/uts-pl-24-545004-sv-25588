<?php

namespace App\Http\Requests;

use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;
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
}