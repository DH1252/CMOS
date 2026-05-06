<?php

namespace App\Support;

class ThemePalette
{
    public const DEFAULT = 'purple';

    /**
     * @var array<string, string>
     */
    private const CUSTOM_SETTING_MAP = [
        'theme_primary' => 'primary',
        'theme_hover' => 'hover',
        'theme_soft' => 'soft',
        'theme_light' => 'light',
        'theme_secondary' => 'secondary',
        'theme_secondary_soft' => 'secondarySoft',
        'theme_primary_foreground' => 'primaryForeground',
    ];

    /**
     * Map of setting keys to CSS variable names for light mode.
     *
     * @var array<string, string>
     */
    private const LIGHT_CSS_MAP = [
        'css_text_strong' => 'text-strong',
        'css_text_soft' => 'text-soft',
        'css_text_muted' => 'text-muted',
        'css_page_bg' => 'page-bg',
        'css_page_bg_soft' => 'page-bg-soft',
        'css_panel_bg' => 'panel-bg',
        'css_panel_muted' => 'panel-muted',
        'css_line_soft' => 'line-soft',
        'css_signal_success' => 'signal-success',
        'css_signal_warning' => 'signal-warning',
        'css_signal_danger' => 'signal-danger',
        'css_signal_info' => 'signal-info',
        'css_pill_text_secondary' => 'pill-text-secondary',
        'css_pill_text_warning' => 'pill-text-warning',
        'css_pill_text_success' => 'pill-text-success',
        'css_pill_text_danger' => 'pill-text-danger',
    ];

    /**
     * Map of setting keys to CSS variable names for dark mode.
     *
     * @var array<string, string>
     */
    private const DARK_CSS_MAP = [
        'css_dark_text_strong' => 'text-strong',
        'css_dark_text_soft' => 'text-soft',
        'css_dark_text_muted' => 'text-muted',
        'css_dark_page_bg' => 'page-bg',
        'css_dark_page_bg_soft' => 'page-bg-soft',
        'css_dark_panel_bg' => 'panel-bg',
        'css_dark_panel_muted' => 'panel-muted',
        'css_dark_line_soft' => 'line-soft',
        'css_dark_pill_text_secondary' => 'pill-text-secondary',
        'css_dark_pill_text_warning' => 'pill-text-warning',
        'css_dark_pill_text_success' => 'pill-text-success',
        'css_dark_pill_text_danger' => 'pill-text-danger',
    ];

    /**
     * Map of setting keys to CSS variable names for landing page.
     *
     * @var array<string, string>
     */
    private const LANDING_CSS_MAP = [
        'css_landing_text_strong' => 'text-strong',
        'css_landing_text_soft' => 'text-soft',
        'css_landing_text_muted' => 'text-muted',
        'css_landing_page_bg' => 'page-bg',
        'css_landing_page_bg_soft' => 'page-bg-soft',
        'css_landing_panel_bg' => 'panel-bg',
        'css_landing_panel_muted' => 'panel-muted',
        'css_landing_line_soft' => 'line-soft',
        'css_landing_brand_primary' => 'brand-primary',
        'css_landing_brand_hover' => 'brand-hover',
        'css_landing_brand_soft' => 'brand-soft',
        'css_landing_brand_light' => 'brand-light',
        'css_landing_brand_secondary' => 'brand-secondary',
        'css_landing_brand_secondary_soft' => 'brand-secondary-soft',
        'css_landing_terminal_bg' => 'landing-terminal-bg',
        'css_landing_terminal_hero_bg' => 'landing-terminal-hero-bg',
        'css_landing_terminal_panel' => 'landing-terminal-panel',
        'css_landing_terminal_panel_soft' => 'landing-terminal-panel-soft',
        'css_landing_terminal_line' => 'landing-terminal-line',
        'css_landing_terminal_text' => 'landing-terminal-text',
        'css_landing_terminal_heading' => 'landing-terminal-heading',
        'css_landing_terminal_soft' => 'landing-terminal-soft',
        'css_landing_terminal_muted' => 'landing-terminal-muted',
        'css_landing_terminal_accent' => 'landing-terminal-accent',
        'css_landing_terminal_interactive' => 'landing-terminal-interactive',
        'css_landing_terminal_command' => 'landing-terminal-command',
        'css_landing_terminal_frame_accent' => 'landing-terminal-frame-accent',
        'css_landing_terminal_icon' => 'landing-terminal-icon',
        'css_landing_terminal_button_text' => 'landing-terminal-button-text',
        'css_landing_terminal_button_hover' => 'landing-terminal-button-hover',
        'css_landing_terminal_button_secondary_text' => 'landing-terminal-button-secondary-text',
        'css_landing_terminal_button_secondary_hover' => 'landing-terminal-button-secondary-hover',
    ];

    /**
     * @var array<string, array{label: string, primary: string, hover: string, soft: string, light: string, secondary: string, secondarySoft: string, primaryForeground: string}>
     */
    private const COLORS = [
        'purple' => ['label' => 'Ungu', 'primary' => '#7C3AED', 'hover' => '#6D28D9', 'soft' => '#A78BFA', 'light' => '#EDE9FE', 'secondary' => '#5B2BA9', 'secondarySoft' => '#E9E0F8', 'primaryForeground' => '#FFFFFF'],
        'blue' => ['label' => 'Biru', 'primary' => '#3B82F6', 'hover' => '#2563EB', 'soft' => '#60A5FA', 'light' => '#DBEAFE', 'secondary' => '#1D4ED8', 'secondarySoft' => '#DFE8FD', 'primaryForeground' => '#FFFFFF'],
        'green' => ['label' => 'Hijau', 'primary' => '#10B981', 'hover' => '#059669', 'soft' => '#34D399', 'light' => '#D1FAE5', 'secondary' => '#0F766E', 'secondarySoft' => '#D6F3EE', 'primaryForeground' => '#0F172A'],
        'red' => ['label' => 'Merah', 'primary' => '#EF4444', 'hover' => '#DC2626', 'soft' => '#F87171', 'light' => '#FEE2E2', 'secondary' => '#B91C1C', 'secondarySoft' => '#F9DDDD', 'primaryForeground' => '#FFFFFF'],
        'orange' => ['label' => 'Oranye', 'primary' => '#F59E0B', 'hover' => '#D97706', 'soft' => '#FBBF24', 'light' => '#FEF3C7', 'secondary' => '#B45309', 'secondarySoft' => '#F8E6CC', 'primaryForeground' => '#0F172A'],
        'pink' => ['label' => 'Pink', 'primary' => '#EC4899', 'hover' => '#DB2777', 'soft' => '#F472B6', 'light' => '#FCE7F3', 'secondary' => '#BE185D', 'secondarySoft' => '#F8DDEB', 'primaryForeground' => '#FFFFFF'],
        'indigo' => ['label' => 'Indigo', 'primary' => '#6366F1', 'hover' => '#4F46E5', 'soft' => '#818CF8', 'light' => '#E0E7FF', 'secondary' => '#4338CA', 'secondarySoft' => '#DEE1FC', 'primaryForeground' => '#FFFFFF'],
        'teal' => ['label' => 'Teal', 'primary' => '#14B8A6', 'hover' => '#0D9488', 'soft' => '#2DD4BF', 'light' => '#CCFBF1', 'secondary' => '#0F766E', 'secondarySoft' => '#D4F3EF', 'primaryForeground' => '#0F172A'],
        'cyan' => ['label' => 'Cyan', 'primary' => '#06B6D4', 'hover' => '#0891B2', 'soft' => '#22D3EE', 'light' => '#CFFAFE', 'secondary' => '#0E7490', 'secondarySoft' => '#D5F1F7', 'primaryForeground' => '#0F172A'],
        'rose' => ['label' => 'Rose', 'primary' => '#F43F5E', 'hover' => '#E11D48', 'soft' => '#FB7185', 'light' => '#FFE4E6', 'secondary' => '#BE123C', 'secondarySoft' => '#FBDCE2', 'primaryForeground' => '#FFFFFF'],
        'amber' => ['label' => 'Amber', 'primary' => '#F59E0B', 'hover' => '#D97706', 'soft' => '#FBBF24', 'light' => '#FEF3C7', 'secondary' => '#B45309', 'secondarySoft' => '#F8E6CC', 'primaryForeground' => '#0F172A'],
        'slate' => ['label' => 'Slate', 'primary' => '#64748B', 'hover' => '#475569', 'soft' => '#94A3B8', 'light' => '#F1F5F9', 'secondary' => '#334155', 'secondarySoft' => '#E2E8F0', 'primaryForeground' => '#FFFFFF'],
    ];

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_keys(self::COLORS);
    }

    public static function defaultName(): string
    {
        return self::DEFAULT;
    }

    /**
     * @return array<int, string>
     */
    public static function settingKeys(): array
    {
        return array_keys(self::CUSTOM_SETTING_MAP);
    }

    /**
     * @return array<int, string>
     */
    public static function cssVariableKeys(): array
    {
        return array_merge(
            array_keys(self::LIGHT_CSS_MAP),
            array_keys(self::DARK_CSS_MAP),
            array_keys(self::LANDING_CSS_MAP),
        );
    }

    /**
     * @return array<string, string>
     */
    public static function color(string $name): array
    {
        return self::COLORS[$name] ?? self::COLORS[self::DEFAULT];
    }

    /**
     * @return array<int, array{name: string, label: string, primary: string, hover: string, soft: string, light: string, secondary: string, secondarySoft: string, primaryForeground: string}>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::COLORS as $name => $palette) {
            $options[] = [
                'name' => $name,
                'label' => $palette['label'],
                'primary' => $palette['primary'],
                'hover' => $palette['hover'],
                'soft' => $palette['soft'],
                'light' => $palette['light'],
                'secondary' => $palette['secondary'],
                'secondarySoft' => $palette['secondarySoft'],
                'primaryForeground' => $palette['primaryForeground'],
            ];
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function cssVariables(string $name): array
    {
        $color = self::color($name);

        return [
            'brand-primary-base' => $color['primary'],
            'brand-hover-base' => $color['hover'],
            'brand-soft-base' => $color['soft'],
            'brand-light-base' => $color['light'],
            'brand-secondary-base' => $color['secondary'],
            'brand-secondary-soft-base' => $color['secondarySoft'],
            'primary-foreground-base' => $color['primaryForeground'],
        ];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array{color: string, palette: array<string, string>, variables: array<string, string>, customCss: array{light: array<string, string>, dark: array<string, string>, shared: array<string, string>}}
     */
    public static function payloadFromSettings(array $settings): array
    {
        $themeName = (string) ($settings['theme_color'] ?? self::DEFAULT);
        $palette = self::color($themeName);

        foreach (self::CUSTOM_SETTING_MAP as $settingKey => $paletteKey) {
            $value = $settings[$settingKey] ?? null;

            if (! is_string($value)) {
                continue;
            }

            $normalized = self::normalizeHex($value);

            if (! $normalized) {
                continue;
            }

            $palette[$paletteKey] = $normalized;
        }

        $lightCss = [];
        foreach (self::LIGHT_CSS_MAP as $settingKey => $cssVar) {
            $value = $settings[$settingKey] ?? null;
            if (is_string($value)) {
                $normalized = self::normalizeHex($value);
                if ($normalized) {
                    $lightCss[$cssVar] = $normalized;
                }
            }
        }

        $darkCss = [];
        foreach (self::DARK_CSS_MAP as $settingKey => $cssVar) {
            $value = $settings[$settingKey] ?? null;
            if (is_string($value)) {
                $normalized = self::normalizeHex($value);
                if ($normalized) {
                    $darkCss[$cssVar] = $normalized;
                }
            }
        }

        $sharedCss = [];
        $sharedKeys = ['css_signal_success', 'css_signal_warning', 'css_signal_danger', 'css_signal_info'];
        foreach ($sharedKeys as $settingKey) {
            $value = $settings[$settingKey] ?? null;
            if (is_string($value)) {
                $normalized = self::normalizeHex($value);
                if ($normalized) {
                    $cssVar = str_replace('css_', '', str_replace('css_signal_', 'signal-', $settingKey));
                    $sharedCss[$cssVar] = $normalized;
                }
            }
        }

        $landingCss = [];
        foreach (self::LANDING_CSS_MAP as $settingKey => $cssVar) {
            $value = $settings[$settingKey] ?? null;
            if (is_string($value)) {
                $normalized = self::normalizeHex($value);
                if ($normalized) {
                    $landingCss[$cssVar] = $normalized;
                }
            }
        }

        return [
            'color' => array_key_exists($themeName, self::COLORS) ? $themeName : self::DEFAULT,
            'palette' => $palette,
            'variables' => [
                'brand-primary-base' => $palette['primary'],
                'brand-hover-base' => $palette['hover'],
                'brand-soft-base' => $palette['soft'],
                'brand-light-base' => $palette['light'],
                'brand-secondary-base' => $palette['secondary'],
                'brand-secondary-soft-base' => $palette['secondarySoft'],
                'primary-foreground-base' => $palette['primaryForeground'],
            ],
            'customCss' => [
                'light' => $lightCss,
                'dark' => $darkCss,
                'shared' => $sharedCss,
                'landing' => $landingCss,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function lightCssDefaults(): array
    {
        return [
            'css_text_strong' => '#211D18',
            'css_text_soft' => '#4A433A',
            'css_text_muted' => '#6D655B',
            'css_page_bg' => '#F5F3EF',
            'css_page_bg_soft' => '#F1EEE8',
            'css_panel_bg' => '#FFFFFF',
            'css_panel_muted' => '#ECE8E0',
            'css_line_soft' => '#D6D0C6',
            'css_signal_success' => '#3F7A50',
            'css_signal_warning' => '#A96B12',
            'css_signal_danger' => '#B44C40',
            'css_signal_info' => '#7751DE',
            'css_pill_text_secondary' => '#4A433A',
            'css_pill_text_warning' => '#7A4F0A',
            'css_pill_text_success' => '#2D5A3A',
            'css_pill_text_danger' => '#8A3530',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function darkCssDefaults(): array
    {
        return [
            'css_dark_text_strong' => '#F3EEE8',
            'css_dark_text_soft' => '#DDD4C8',
            'css_dark_text_muted' => '#C8BAB0',
            'css_dark_page_bg' => '#18161A',
            'css_dark_page_bg_soft' => '#1D1A20',
            'css_dark_panel_bg' => '#211F24',
            'css_dark_panel_muted' => '#312D35',
            'css_dark_line_soft' => '#3B3640',
            'css_dark_pill_text_secondary' => '#DDD4C8',
            'css_dark_pill_text_warning' => '#F0C34A',
            'css_dark_pill_text_success' => '#7ECF9A',
            'css_dark_pill_text_danger' => '#F08A82',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function landingCssDefaults(): array
    {
        return [
            'css_landing_text_strong' => '#211D18',
            'css_landing_text_soft' => '#4A433A',
            'css_landing_text_muted' => '#6D655B',
            'css_landing_page_bg' => '#FAF9F7',
            'css_landing_page_bg_soft' => '#F6F4F1',
            'css_landing_panel_bg' => '#FFFFFF',
            'css_landing_panel_muted' => '#F2EFEA',
            'css_landing_line_soft' => '#D6D1C9',
            'css_landing_brand_primary' => '#7C3AED',
            'css_landing_brand_hover' => '#6D28D9',
            'css_landing_brand_soft' => '#A78BFA',
            'css_landing_brand_light' => '#EDE9FE',
            'css_landing_brand_secondary' => '#5B2BA9',
            'css_landing_brand_secondary_soft' => '#E9E0F8',
            'css_landing_terminal_bg' => '#18141E',
            'css_landing_terminal_hero_bg' => '#18141E',
            'css_landing_terminal_panel' => '#221F2E',
            'css_landing_terminal_panel_soft' => '#2C283A',
            'css_landing_terminal_line' => '#8A7A3C',
            'css_landing_terminal_text' => '#F0E6C8',
            'css_landing_terminal_heading' => '#F0E6C8',
            'css_landing_terminal_soft' => '#CABE9E',
            'css_landing_terminal_muted' => '#9E9278',
            'css_landing_terminal_accent' => '#D9AE43',
            'css_landing_terminal_interactive' => '#D9AE43',
            'css_landing_terminal_command' => '#D9AE43',
            'css_landing_terminal_frame_accent' => '#D9AE43',
            'css_landing_terminal_icon' => '#D9AE43',
            'css_landing_terminal_button_text' => '#251C0A',
            'css_landing_terminal_button_hover' => '#E4BE5D',
            'css_landing_terminal_button_secondary_text' => '#F0E6C8',
            'css_landing_terminal_button_secondary_hover' => '#2C283A',
        ];
    }

    private static function normalizeHex(string $value): ?string
    {
        $candidate = strtoupper(trim($value));

        if ($candidate === '') {
            return null;
        }

        if (! str_starts_with($candidate, '#')) {
            $candidate = '#'.$candidate;
        }

        if (! preg_match('/^#[0-9A-F]{6}$/', $candidate)) {
            return null;
        }

        return $candidate;
    }
}
