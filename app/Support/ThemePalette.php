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
     * @return array{color: string, palette: array<string, string>, variables: array<string, string>}
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
