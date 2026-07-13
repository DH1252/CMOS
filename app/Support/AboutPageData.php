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
                'vision' => 'Mewujudkan HIMATEKKOM sebagai poros pergerakan yang unggul melalui optimalisasi sistem dan kolaborasi strategis, demi tercapainya ekspansi kebermanfaatan yang berkelanjutan.',
                'missionLabel' => 'Misi',
                'missionItems' => [
                    'Mengoptimalkan tata kelola dan sistem monitoring organisasi yang terintegrasi, guna menjamin solidaritas internal serta konsistensi kinerja yang berkelanjutan.',
                    'Membangun kolaborasi strategis dengan stakeholder eksternal untuk memperkuat relasi, menjawab kebutuhan mahasiswa, serta mewujudkan kemandirian organisasi.',
                    'Melakukan ekspansi ekosistem pengembangan mahasiswa yang adaptif serta meningkatkan eksistensi HIMATEKKOM di lingkup eksternal.',
                ],
            ],

            'history' => [
                'eyebrow' => 'Perjalanan Kami',
                'title' => 'Sejarah Himpunan',
                'intro' => 'Himpunan Mahasiswa Teknik Komputer (HIMATEKKOM) ITS dibentuk sebagai wadah kemahasiswaan mandiri untuk menaungi fungsionaris dan mahasiswa Teknik Komputer ITS. Berawal dari cikal bakal Jurusan Teknik Multimedia dan Jaringan (TMJ) pada tahun 2012, departemen bertransformasi menjadi Teknik Komputer pada tahun 2014. Setelah inisiasi panjang dan pembentukan Tim Formatur, HIMATEKKOM ITS resmi dideklarasikan secara berdaulat pada 26 Agustus 2020 melalui MMTK I.',
                'timeline' => [
                    [
                        'year' => '2012',
                        'title' => 'Cikal Bakal TMJ ITS',
                        'description' => 'Jurusan Teknik Multimedia dan Jaringan (PSS TMJ - ITS) resmi berdiri berdasarkan SK Menteri Pendidikan Nasional No. 382/E/O/2012 tertanggal 9 Nopember 2012.',
                    ],
                    [
                        'year' => '2014',
                        'title' => 'Transformasi Teknik Komputer',
                        'description' => 'PSS TMJ - ITS bertransformasi menjadi Departemen Teknik Komputer ITS akibat penyesuaian penamaan program studi (Permendikbud No. 154 Tahun 2014) di bawah FTEIC.',
                    ],
                    [
                        'year' => '2020',
                        'title' => 'Deklarasi HIMATEKKOM ITS',
                        'description' => 'Inisiasi pembentukan himpunan mandiri disahkan SK Dekan FTEIC pada 30 Juli 2020. Himpunan resmi berdiri pasca Musyawarah Mahasiswa Teknik Komputer (MMTK) I pada 24-26 Agustus 2020.',
                    ],
                    [
                        'year' => '2021',
                        'title' => 'Kabinet Prakarsa (Periode I)',
                        'description' => 'Kepengurusan pertama HIMATEKKOM ITS dipimpin oleh Vidityar Adith Nugroho (angkatan 2018), merumuskan instrumen dasar AD/ART dan organisasi.',
                    ],
                    [
                        'year' => '2022',
                        'title' => 'Kabinet Convergence (Periode II)',
                        'description' => 'Dipimpin oleh Fauzan Wahyudi (angkatan 2019), memfokuskan program kerja pada konvergensi kebutuhan anggota himpunan.',
                    ],
                    [
                        'year' => '2023',
                        'title' => 'Kabinet Sinkronisasi (Periode III)',
                        'description' => 'Dipimpin oleh Nabil Virio (angkatan 2020), memperkuat integrasi program kerja lintas divisi dan sinkronisasi administrasi.',
                    ],
                    [
                        'year' => '2024',
                        'title' => 'Kabinet Integrasi (Periode IV)',
                        'description' => 'Dipimpin oleh Muhammad Hikmal Akbar (angkatan 2021), memperkuat sinergi internal serta integrasi keprofesian mahasiswa.',
                    ],
                    [
                        'year' => '2025',
                        'title' => 'Kabinet Titik Transformasi (Periode V)',
                        'description' => 'Dipimpin oleh Miftah Ghifari (angkatan 2022), memfokuskan himpunan sebagai wadah transformasi digital dan keprofesian yang adaptif.',
                    ],
                    [
                        'year' => '2026',
                        'title' => 'Kabinet Sentra Sinergi (Periode VI)',
                        'description' => 'Kabinet saat ini mengusung nilai #OKE (Optimalisasi, Kolaborasi, Ekspansi) untuk memperkuat tata kelola internal, kolaborasi strategis, dan memperluas kebermanfaatan himpunan.',
                    ],
                ],
            ],

            'footer' => [
                'description' => 'Kabinet Sentra Sinergi, Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember.',
            ],
        ];
    }
}
