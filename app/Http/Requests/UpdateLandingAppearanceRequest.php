<?php

namespace App\Http\Requests;

use App\Support\ThemePalette;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLandingAppearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $cssKeys = array_keys(ThemePalette::landingCssDefaults());

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
            'css_landing_text_strong' => $hex,
            'css_landing_text_soft' => $hex,
            'css_landing_text_muted' => $hex,
            'css_landing_page_bg' => $hex,
            'css_landing_page_bg_soft' => $hex,
            'css_landing_panel_bg' => $hex,
            'css_landing_panel_muted' => $hex,
            'css_landing_line_soft' => $hex,
            'css_landing_brand_primary' => $hex,
            'css_landing_brand_hover' => $hex,
            'css_landing_brand_soft' => $hex,
            'css_landing_brand_light' => $hex,
            'css_landing_brand_secondary' => $hex,
            'css_landing_brand_secondary_soft' => $hex,
            'css_landing_terminal_bg' => $hex,
            'css_landing_terminal_panel' => $hex,
            'css_landing_terminal_panel_soft' => $hex,
            'css_landing_terminal_line' => $hex,
            'css_landing_terminal_text' => $hex,
            'css_landing_terminal_soft' => $hex,
            'css_landing_terminal_muted' => $hex,
            'css_landing_terminal_accent' => $hex,
            'css_landing_terminal_accent_soft' => $hex,
            'css_landing_terminal_button_text' => $hex,
        ];
    }
}
