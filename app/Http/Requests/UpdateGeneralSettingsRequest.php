<?php

namespace App\Http\Requests;

use App\Support\ThemePalette;
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
        $hexRule = 'nullable|regex:/^#[0-9A-Fa-f]{6}$/';

        return [
            'app_name' => ['required', 'string', 'max:60'],
            'organization_name' => ['required', 'string', 'max:80'],
            'theme_color' => ['required', Rule::in(ThemePalette::names())],
            'theme_primary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_hover' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_soft' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_light' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_secondary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_secondary_soft' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_primary_foreground' => ['required', Rule::in(['#FFFFFF', '#0F172A'])],
            'evaluation_period' => ['required', Rule::in(['monthly', 'quarterly', 'semester', 'yearly'])],
            'css_text_strong' => [$hexRule],
            'css_text_soft' => [$hexRule],
            'css_text_muted' => [$hexRule],
            'css_page_bg' => [$hexRule],
            'css_page_bg_soft' => [$hexRule],
            'css_panel_bg' => [$hexRule],
            'css_panel_muted' => [$hexRule],
            'css_line_soft' => [$hexRule],
            'css_signal_success' => [$hexRule],
            'css_signal_warning' => [$hexRule],
            'css_signal_danger' => [$hexRule],
            'css_signal_info' => [$hexRule],
            'css_dark_text_strong' => [$hexRule],
            'css_dark_text_soft' => [$hexRule],
            'css_dark_text_muted' => [$hexRule],
            'css_dark_page_bg' => [$hexRule],
            'css_dark_page_bg_soft' => [$hexRule],
            'css_dark_panel_bg' => [$hexRule],
            'css_dark_panel_muted' => [$hexRule],
            'css_dark_line_soft' => [$hexRule],
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
            'theme_color.in' => 'Warna tema yang dipilih tidak tersedia.',
            'theme_primary.regex' => 'Warna primer harus berupa kode hex valid.',
            'theme_hover.regex' => 'Warna hover harus berupa kode hex valid.',
            'theme_soft.regex' => 'Warna soft harus berupa kode hex valid.',
            'theme_light.regex' => 'Warna light harus berupa kode hex valid.',
            'theme_secondary.regex' => 'Warna sekunder harus berupa kode hex valid.',
            'theme_secondary_soft.regex' => 'Warna sekunder soft harus berupa kode hex valid.',
            'theme_primary_foreground.in' => 'Warna teks tombol utama harus kontras.',
            'evaluation_period.in' => 'Periode evaluasi tidak valid.',
        ];
    }
}
