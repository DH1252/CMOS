<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Models\Setting;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $settings = Setting::all()->keyBy('key');

        return \Inertia\Inertia::render(
            'pages/SettingsPage',
            [
                'title' => 'Pengaturan Aplikasi',
                'description' => 'Atur identitas sistem dan ritme evaluasi organisasi.',
                'form' => [
                    'action' => route('settings.update', 'general'),
                    'csrfToken' => csrf_token(),
                    'spoofMethod' => 'PUT',
                ],
                'values' => [
                    'appName' => old('app_name', $settings['app_name']?->value ?? 'CMOS'),
                    'organizationName' => old('organization_name', $settings['organization_name']?->value ?? 'HIMATEKKOM ITS'),
                    'evaluationPeriod' => old('evaluation_period', $settings['evaluation_period']?->value ?? 'quarterly'),
                    'appTimezone' => old('app_timezone', $settings['app_timezone']?->value ?? config('app.client_timezone', 'Asia/Jakarta')),
                    'timezoneOptions' => $this->timezoneOptions(),
                    'periodOptions' => [
                        ['value' => 'monthly', 'label' => 'Bulanan'],
                        ['value' => 'quarterly', 'label' => 'Per Kuartal'],
                        ['value' => 'semester', 'label' => 'Per Semester'],
                        ['value' => 'yearly', 'label' => 'Tahunan'],
                    ],
                ],
                'errors' => collect(session('errors')?->messages() ?? [])->map(fn ($messages): string => $messages[0])->toArray(),
            ],
        );
    }

    public function update(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        try {
            foreach ($request->validated() as $key => $value) {
                Setting::set($key, $value);
            }
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('settings.index')
                ->with('error', 'Gagal menyimpan pengaturan: '.$e->getMessage());
        }

        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function timezoneOptions(): array
    {
        $now = new DateTimeImmutable;

        return collect(timezone_identifiers_list())
            ->map(function (string $identifier) use ($now): array {
                $timezone = new DateTimeZone($identifier);
                $offset = $timezone->getOffset($now);
                $sign = $offset >= 0 ? '+' : '-';
                $absoluteOffset = abs($offset);
                $hours = intdiv($absoluteOffset, 3600);
                $minutes = intdiv($absoluteOffset % 3600, 60);

                return [
                    'value' => $identifier,
                    'label' => sprintf('%s (UTC%s%02d:%02d)', $identifier, $sign, $hours, $minutes),
                ];
            })
            ->all();
    }
}
