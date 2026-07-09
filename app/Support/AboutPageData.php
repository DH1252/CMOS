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
                'vision' => 'Menjadikan HIMATEKKOM ITS sebagai himpunan yang progresif, inklusif, dan berdampak, dengan tata kelola organisasi yang profesional serta budaya kolaborasi yang kuat dalam semangat Kabinet Sentra Sinergi.',
                'missionLabel' => 'Misi',
                'missionItems' => [
                    'Menguatkan pelayanan internal melalui sistem kerja terstruktur, evaluasi berkala, dan pengembangan kapasitas pengurus yang berkelanjutan.',
                    'Mendorong kolaborasi lintas pihak, mulai dari mahasiswa, alumni, departemen, hingga mitra kegiatan, untuk memperluas dampak program kerja.',
                    'Mewujudkan transparansi informasi melalui publikasi kegiatan, dokumentasi terpusat, dan akses informasi yang mudah bagi warga Teknik Komputer ITS.',
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
