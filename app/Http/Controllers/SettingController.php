<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Http\Requests\UpdateLandingAppearanceRequest;
use App\Models\Setting;
use App\Support\ThemePalette;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $settings = Setting::all()->keyBy('key');
        $settingsMap = $settings->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting->value])->all();
        $themePayload = ThemePalette::payloadFromSettings($settingsMap);

        return \Inertia\Inertia::render(
            'pages/SettingsPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Pengaturan Aplikasi',
                    'description' => 'Atur identitas visual, nama sistem, dan ritme evaluasi organisasi.',
                    'form' => [
                        'action' => route('settings.update', 'general'),
                        'csrfToken' => csrf_token(),
                        'spoofMethod' => 'PUT',
                    ],
                    'values' => [
                        'appName' => old('app_name', $settings['app_name']?->value ?? 'CMOS'),
                        'organizationName' => old('organization_name', $settings['organization_name']?->value ?? 'HIMATEKKOM ITS'),
                        'themeColor' => old('theme_color', $themePayload['color']),
                        'themePrimary' => old('theme_primary', $themePayload['palette']['primary']),
                        'themeHover' => old('theme_hover', $themePayload['palette']['hover']),
                        'themeSoft' => old('theme_soft', $themePayload['palette']['soft']),
                        'themeLight' => old('theme_light', $themePayload['palette']['light']),
                        'themeSecondary' => old('theme_secondary', $themePayload['palette']['secondary']),
                        'themeSecondarySoft' => old('theme_secondary_soft', $themePayload['palette']['secondarySoft']),
                        'themePrimaryForeground' => old('theme_primary_foreground', $themePayload['palette']['primaryForeground']),
                        'customCss' => [
                            'light' => array_merge(
                                ThemePalette::lightCssDefaults(),
                                collect(ThemePalette::lightCssDefaults())
                                    ->mapWithKeys(fn ($default, $key) => [$key => old($key, $settings[$key]?->value ?? $default)])
                                    ->all()
                            ),
                            'dark' => array_merge(
                                ThemePalette::darkCssDefaults(),
                                collect(ThemePalette::darkCssDefaults())
                                    ->mapWithKeys(fn ($default, $key) => [$key => old($key, $settings[$key]?->value ?? $default)])
                                    ->all()
                            ),
                            'landing' => array_merge(
                                ThemePalette::landingCssDefaults(),
                                collect(ThemePalette::landingCssDefaults())
                                    ->mapWithKeys(fn ($default, $key) => [$key => old($key, $settings[$key]?->value ?? $default)])
                                    ->all()
                            ),
                        ],
                        'evaluationPeriod' => old('evaluation_period', $settings['evaluation_period']?->value ?? 'quarterly'),
                        'periodOptions' => [
                            ['value' => 'monthly', 'label' => 'Bulanan'],
                            ['value' => 'quarterly', 'label' => 'Per Kuartal'],
                            ['value' => 'semester', 'label' => 'Per Semester'],
                            ['value' => 'yearly', 'label' => 'Tahunan'],
                        ],
                    ],
                    'colors' => ThemePalette::options(),
                    'errors' => collect(session('errors')?->messages() ?? [])->map(fn ($messages): string => $messages[0])->toArray(),
                ];

                return $props;
            })(compact('settings', 'themePayload')),
        );
    }

    public function update(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        $cssKeys = array_merge(
            array_keys(ThemePalette::lightCssDefaults()),
            array_keys(ThemePalette::darkCssDefaults()),
            array_keys(ThemePalette::landingCssDefaults()),
        );

        try {
            foreach ($request->validated() as $key => $value) {
                if (in_array($key, $cssKeys, true) && ($value === null || $value === '')) {
                    Setting::query()->where('key', $key)->delete();

                    continue;
                }

                Setting::set($key, $value);
            }
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('settings.index')
                ->with('error', 'Gagal menyimpan pengaturan: '.$e->getMessage());
        }

        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil disimpan dan diterapkan ke seluruh situs.');
    }

    public function landing(): Response
    {
        $settings = Setting::all()->keyBy('key');
        $settingsMap = $settings->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting->value])->all();
        $themePayload = ThemePalette::payloadFromSettings($settingsMap);

        return \Inertia\Inertia::render(
            'pages/LandingAppearancePage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $props = [
                    'title' => 'Tampilan Landing Page',
                    'description' => 'Atur warna dan identitas visual halaman publik.',
                    'form' => [
                        'action' => route('settings.landing.update'),
                        'csrfToken' => csrf_token(),
                        'spoofMethod' => 'PUT',
                    ],
                    'values' => [
                        'themeColor' => old('theme_color', $themePayload['color']),
                        'themePrimary' => old('theme_primary', $themePayload['palette']['primary']),
                        'themeHover' => old('theme_hover', $themePayload['palette']['hover']),
                        'themeSoft' => old('theme_soft', $themePayload['palette']['soft']),
                        'themeLight' => old('theme_light', $themePayload['palette']['light']),
                        'themeSecondary' => old('theme_secondary', $themePayload['palette']['secondary']),
                        'themeSecondarySoft' => old('theme_secondary_soft', $themePayload['palette']['secondarySoft']),
                        'themePrimaryForeground' => old('theme_primary_foreground', $themePayload['palette']['primaryForeground']),
                        'customCss' => array_merge(
                            ThemePalette::landingCssDefaults(),
                            collect(ThemePalette::landingCssDefaults())
                                ->mapWithKeys(fn ($default, $key) => [$key => old($key, $settings[$key]?->value ?? $default)])
                                ->all()
                        ),
                    ],
                    'colors' => ThemePalette::options(),
                    'previewUrl' => route('home'),
                    'errors' => collect(session('errors')?->messages() ?? [])->map(fn ($messages): string => $messages[0])->toArray(),
                ];

                return $props;
            })(compact('settings', 'themePayload')),
        );
    }

    public function updateLanding(UpdateLandingAppearanceRequest $request): RedirectResponse
    {
        $cssKeys = array_keys(ThemePalette::landingCssDefaults());

        try {
            foreach ($request->validated() as $key => $value) {
                if (in_array($key, $cssKeys, true) && ($value === null || $value === '')) {
                    Setting::query()->where('key', $key)->delete();

                    continue;
                }

                Setting::set($key, $value);
            }
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('settings.landing')
                ->with('error', 'Gagal menyimpan pengaturan: '.$e->getMessage());
        }

        return redirect()->route('settings.landing')
            ->with('success', 'Pengaturan landing page berhasil disimpan.');
    }
}
