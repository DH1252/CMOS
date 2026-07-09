<?php

namespace App\Support;

use App\Models\Setting;

class AboutPageData
{
    /**
     * @return array<string, mixed>
     */
    public function props(): array
    {
        $settings = Setting::query()
            ->whereIn('key', array_merge(['app_name', 'organization_name', 'theme_color'], ThemePalette::settingKeys()))
            ->pluck('value', 'key');

        $organizationName = (string) $settings->get('organization_name', 'HIMATEKKOM ITS');
        $theme = ThemePalette::payloadFromSettings($settings->all());

        $homeUrl = route('home');
        $loginUrl = route('login');
        $infoUrl = route('informasi.index');
        $acaraUrl = route('acara.index');
        $departemenUrl = route('departemen');
        $kompetisiUrl = route('kompetisi');
        $tentangUrl = route('tentang');

        $pageTitle = 'Tentang Kami - '.$organizationName;
        $pageDescription = 'Profil, visi misi, dan sejarah Kabinet Sentra Sinergi '.$organizationName.'.';

        return [
            'organizationName' => $organizationName,
            'themeColor' => $theme['color'],
            'themeVariables' => $theme['variables'],
            'homeUrl' => $homeUrl,
            'loginUrl' => $loginUrl,
            'infoUrl' => $infoUrl,
            'acaraUrl' => $acaraUrl,
            'departemenUrl' => $departemenUrl,
            'kompetisiUrl' => $kompetisiUrl,
            'tentangUrl' => $tentangUrl,
            'seo' => [
                'title' => $pageTitle,
                'description' => $pageDescription,
                'canonical' => $tentangUrl,
                'type' => 'website',
            ],

            'visionMission' => [
                'eyebrow' => 'Profil Kabinet',
                'title' => 'Visi & Misi',
                'visionLabel' => 'Visi',
                'vision' => 'Mewujudkan HIMATEKKOM sebagai poros pergerakan yang unggul melalui optimalissasi sistem dan kolaborasi strategis, demi tercapainya ekspansi kebermanfaatan yang berkelanjutan.',
                'missionLabel' => 'Misi',
                'missionItems' => [
                    'Mengoptimalkan tata kelola dan sistem monitoring organisasi yang terintegrasi, guna menjamin solidaritas internal serta konsistensi kinerja yang berkelanjutan.',
                    'Membangun kolaborasi strategis dengan stakeholder external untuk memperkuat relasi, menjawab kebutuhan mahasiswa, serta mewujudkan kemandirian organisasi.',
                    'Melakukan ekspansi ekosistem pengembangan mahasiswa yang adaptif serta meningkatkan eksistensi HIMATEKKKOM di lingkup eksternal.',
                ],
            ],

            // TODO: Ganti semua teks [PLACEHOLDER] dengan sejarah asli HIMATEKKOM ITS.
            'history' => [
                'eyebrow' => 'Perjalanan Kami',
                'title' => 'Sejarah Kabinet',
                'intro' => '[PLACEHOLDER] Tulis ringkasan singkat perjalanan HIMATEKKOM ITS, sejak awal berdiri hingga terbentuknya Kabinet Sentra Sinergi 2026.',
                'timeline' => [
                    [
                        'year' => '[PLACEHOLDER 20XX]',
                        'title' => '[PLACEHOLDER] Berdirinya HIMATEKKOM ITS',
                        'description' => '[PLACEHOLDER] Ceritakan latar belakang dan momen awal berdirinya himpunan.',
                    ],
                    [
                        'year' => '[PLACEHOLDER 20XX]',
                        'title' => '[PLACEHOLDER] Nama Kabinet Sebelumnya',
                        'description' => '[PLACEHOLDER] Highlight pencapaian atau fokus kerja kabinet-kabinet sebelumnya.',
                    ],
                    [
                        'year' => '2026',
                        'title' => 'Kabinet Sentra Sinergi',
                        'description' => 'Kabinet 2026 mengusung semangat #OKE — Optimalisasi, Kolaborasi, Ekspansi — untuk memperkuat sistem kerja dan memperluas dampak organisasi.',
                    ],
                ],
            ],

            'footer' => [
                'description' => 'Kabinet Sentra Sinergi, Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember.',
            ],
        ];
    }
}
