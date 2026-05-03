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

    protected function prepareForValidation(): void
    {
        $cssKeys = array_merge(
            array_keys(ThemePalette::lightCssDefaults()),
            array_keys(ThemePalette::darkCssDefaults()),
        );

        foreach ($cssKeys as $key) {
            $value = $this->input($key);

            if ($value === '' || $value === null) {
                $this->merge([$key => null]);

                continue;
            }

            $trimmed = strtoupper(trim((string) $value));

            if (! str_starts_with($trimmed, '#')) {
                $trimmed = '#'.$trimmed;
            }

            $this->merge([$key => $trimmed]);
        }
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        $hex = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];

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
            'css_text_strong' => $hex,
            'css_text_soft' => $hex,
            'css_text_muted' => $hex,
            'css_page_bg' => $hex,
            'css_page_bg_soft' => $hex,
            'css_panel_bg' => $hex,
            'css_panel_muted' => $hex,
            'css_line_soft' => $hex,
            'css_signal_success' => $hex,
            'css_signal_warning' => $hex,
            'css_signal_danger' => $hex,
            'css_signal_info' => $hex,
            'css_dark_text_strong' => $hex,
            'css_dark_text_soft' => $hex,
            'css_dark_text_muted' => $hex,
            'css_dark_page_bg' => $hex,
            'css_dark_page_bg_soft' => $hex,
            'css_dark_panel_bg' => $hex,
            'css_dark_panel_muted' => $hex,
            'css_dark_line_soft' => $hex,
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
