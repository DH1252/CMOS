<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:60'],
            'organization_name' => ['required', 'string', 'max:80'],
            'evaluation_period' => ['required', Rule::in(['monthly', 'quarterly', 'semester', 'yearly'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'app_name.required' => 'Nama aplikasi wajib diisi.',
            'organization_name.required' => 'Nama organisasi wajib diisi.',
            'evaluation_period.in' => 'Periode evaluasi tidak valid.',
        ];
    }
}
