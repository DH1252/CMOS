<?php

namespace App\Support;

use App\Models\InformationBoard;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LandingPageData
{
    /**
     * @return array<string, mixed>
     */
    public function props(): array
    {
        $settings = Setting::query()
            ->whereIn('key', array_merge(['app_name', 'organization_name', 'theme_color'], ThemePalette::settingKeys()))
            ->pluck('value', 'key');

        $appName = (string) $settings->get('app_name', 'CMOS');
        $organizationName = (string) $settings->get('organization_name', 'HIMATEKKOM ITS');
        $theme = ThemePalette::payloadFromSettings($settings->all());
        $homeUrl = route('home');
        $loginUrl = route('login');
        $infoUrl = route('informasi.index');
        $logoUrl = asset('images/logokabinet.png');
        $instagramUrl = 'https://www.instagram.com/sentrasinergi/';
        $organizationId = $homeUrl.'#organization';
        $websiteId = $homeUrl.'#website';
        $latestInfo = $this->latestInfo();

        $navigation = [
            ['href' => '#profil', 'label' => 'Profil Organisasi'],
            ['href' => '#program-kerja', 'label' => 'Program Kerja'],
            ['href' => '#informasi', 'label' => 'Informasi'],
        ];

        $supportLinks = [
            [
                'title' => 'Dokumen Organisasi',
                'description' => 'Referensi kegiatan dan dokumen resmi kabinet.',
                'href' => 'https://its.id/m/RPOSentraSinergi',
            ],
            [
                'title' => 'Materi Presentasi',
                'description' => 'Materi publik yang dibagikan kabinet untuk kebutuhan sosialisasi dan dokumentasi.',
                'href' => 'https://its.id/m/PPTRPOSentraSinergi',
            ],
            [
                'title' => 'Instagram Sentra Sinergi',
                'description' => 'Kanal publikasi kegiatan dan pembaruan kabinet terbaru.',
                'href' => $instagramUrl,
            ],
        ];

        $latestInfoSchemaItems = collect($latestInfo)->map(fn (array $item): array => [
            'name' => $item['title'],
            'url' => $item['url'],
        ])->all();
        $jsonLdNodes = [
            StructuredData::organization($organizationName, $homeUrl, $logoUrl, $instagramUrl),
            StructuredData::website($organizationName, $homeUrl, $infoUrl, $organizationId),
            StructuredData::page([
                '@id' => $homeUrl.'#webpage',
                'url' => $homeUrl,
                'name' => 'Website Resmi HIMATEKKOM ITS 2026 | Kabinet Sentra Sinergi',
                'description' => 'Platform resmi HIMATEKKOM ITS untuk informasi publik dan kerja operasional kabinet.',
                'isPartOf' => ['@id' => $websiteId],
                'about' => ['@id' => $organizationId],
                'mainEntity' => $latestInfoSchemaItems === [] ? ['@id' => $organizationId] : ['@id' => $homeUrl.'#latest-information'],
                'inLanguage' => 'id-ID',
            ]),
        ];

        if ($latestInfoSchemaItems !== []) {
            $jsonLdNodes[] = StructuredData::itemList($latestInfoSchemaItems, $homeUrl.'#latest-information');
        }

        return [
            'page' => 'landing',
            'appName' => $appName,
            'organizationName' => $organizationName,
            'themeColor' => $theme['color'],
            'themeVariables' => $theme['variables'],
            'seo' => [
                'title' => 'Website Resmi HIMATEKKOM ITS 2026 | Kabinet Sentra Sinergi',
                'description' => 'Platform resmi HIMATEKKOM ITS untuk informasi publik dan kerja operasional kabinet.',
                'canonical' => $homeUrl,
                'image' => $logoUrl,
                'type' => 'website',
                'jsonLd' => StructuredData::encode(StructuredData::graph($jsonLdNodes)),
            ],
            'loginUrl' => $loginUrl,
            'infoUrl' => $infoUrl,
            'logoUrl' => $logoUrl,
            'navigation' => $navigation,
            'hero' => [
                'titleVariants' => ['Kabinet Sentra Sinergi', 'HIMATEKKOM ITS'],
                'description' => 'Kabinet Sentra Sinergi menjaga publikasi, dokumentasi, dan akses internal melalui satu sistem yang rapi dan mudah dipantau.',
                'actions' => [
                    ['href' => '#informasi', 'label' => 'Buka arsip publik', 'variant' => 'primary'],
                    ['href' => '#profil', 'label' => 'Profil organisasi', 'variant' => 'secondary'],
                ],
            ],
            'quickFacts' => [
                'Website resmi HIMATEKKOM ITS 2026.',
                'Kabinet Sentra Sinergi menjaga transparansi, dokumentasi, dan kolaborasi organisasi.',
                sprintf('%s dipakai pengurus untuk kerja operasional sehari-hari.', $appName),
            ],
            'profileSection' => [
                'title' => 'Profil organisasi',
                'description' => 'HIMATEKKOM ITS 2026 menjalankan publikasi dan operasional kabinet dengan alur yang terstruktur, terdokumentasi, dan mudah dibaca oleh pengurus maupun publik.',
                'visionLabel' => 'Visi',
                'vision' => 'Menjadikan HIMATEKKOM ITS sebagai himpunan yang progresif, inklusif, dan berdampak, dengan tata kelola organisasi yang profesional serta budaya kolaborasi yang kuat dalam semangat Kabinet Sentra Sinergi.',
                'missionTitle' => 'Misi kerja',
                'missionItems' => [
                    'Menguatkan pelayanan internal melalui sistem kerja terstruktur, evaluasi berkala, dan pengembangan kapasitas pengurus yang berkelanjutan.',
                    'Mendorong kolaborasi lintas pihak, mulai dari mahasiswa, alumni, departemen, hingga mitra kegiatan, untuk memperluas dampak program kerja.',
                    'Mewujudkan transparansi informasi melalui publikasi kegiatan, dokumentasi terpusat, dan akses informasi yang mudah bagi warga Teknik Komputer ITS.',
                ],
            ],
            'programSection' => [
                'title' => 'Program kerja kabinet',
                'description' => 'Tiga rumpun kerja utama kabinet, disusun sebagai garis kerja yang saling melengkapi.',
                'groups' => [
                    [
                        'title' => 'Optimalisasi',
                        'description' => 'Penguatan sistem internal organisasi dan pengelolaan kerja yang lebih terukur.',
                        'items' => [
                            [
                                'name' => 'CMOS',
                                'unit' => 'Monitoring & Pelaporan',
                                'description' => 'Sistem monitoring dan pelaporan program kerja untuk mendukung transparansi, akuntabilitas, dan manajemen organisasi berbasis data.',
                            ],
                            [
                                'name' => 'Personalia',
                                'unit' => 'Sumber Daya Manusia',
                                'description' => 'Pengelolaan rekrutmen, upgrading, rapor staf, dan sistem apresiasi untuk membangun budaya kerja yang sehat dan bertumbuh.',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Kolaborasi',
                        'description' => 'Penguatan relasi organisasi dengan mahasiswa, alumni, dan isu kesejahteraan yang dekat dengan kebutuhan anggota.',
                        'items' => [
                            [
                                'name' => 'Hi Alumni',
                                'unit' => 'Hubungan Alumni',
                                'description' => 'Penguatan relasi mahasiswa aktif dan alumni melalui basis data terstruktur serta publikasi pengalaman dari dunia kuliah hingga dunia kerja.',
                            ],
                            [
                                'name' => 'Sosmas',
                                'unit' => 'Hubungan Luar',
                                'description' => 'Program yang berfokus pada isu sosial kemasyarakatan seperti charity, bantuan sosial, dan kolaborasi eksternal.',
                            ],
                            [
                                'name' => 'Advocation Corner',
                                'unit' => 'Kesejahteraan Mahasiswa',
                                'description' => 'Layanan advokasi aktif pada isu UKT, FRS, beasiswa, dan kebutuhan kesejahteraan mahasiswa lainnya.',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Ekspansi',
                        'description' => 'Perluasan dampak kabinet lewat pengembangan karier, media, kaderisasi, dan kanal digital organisasi.',
                        'items' => [
                            [
                                'name' => 'COD',
                                'unit' => 'Pengembangan Karier',
                                'description' => 'Program pengembangan karier melalui pelatihan CV, simulasi interview, dan penguatan personal branding mahasiswa.',
                            ],
                            [
                                'name' => 'BIOS',
                                'unit' => 'Kajian & Riset',
                                'description' => 'Forum kajian isu keprofesian Teknik Komputer untuk mendorong diskusi kritis dan solusi yang relevan.',
                            ],
                            [
                                'name' => 'TEKKOM Insight',
                                'unit' => 'Media & Informasi',
                                'description' => 'Media informasi dengan konten edukatif dan kreatif untuk meningkatkan literasi teknologi mahasiswa.',
                            ],
                            [
                                'name' => 'Buku Panduan Kaderisasi',
                                'unit' => 'Kader',
                                'description' => 'Pedoman nilai, alur, dan indikator kaderisasi untuk menjaga kesinambungan regenerasi kepemimpinan.',
                            ],
                            [
                                'name' => 'Website HIMATEKKOM',
                                'unit' => 'Digital',
                                'description' => 'Pusat informasi, dokumentasi, dan layanan digital himpunan yang terhubung dengan CMOS.',
                            ],
                        ],
                    ],
                ],
            ],
            'informationSection' => [
                'title' => 'Informasi terbaru',
                'description' => 'Publikasi dan dokumentasi terbaru yang sudah terbit di kanal resmi HIMATEKKOM ITS.',
                'archiveLabel' => 'Arsip lengkap',
                'emptyText' => 'Belum ada publikasi yang terbit di papan informasi.',
            ],
            'ctaSection' => [
                'title' => 'Kabinet Sentra Sinergi',
                'description' => 'Satu sistem, satu kabinet. Transparansi dan dokumentasi kerja berjalan di satu tempat.',
                'buttonLabel' => 'Jelajahi arsip publik',
                'instagramUrl' => $instagramUrl,
                'instagramLabel' => '@sentrasinergi',
                'instagramPrefix' => 'Ikuti',
                'instagramSuffix' => 'di Instagram',
            ],
            'footer' => [
                'description' => 'Kabinet Sentra Sinergi, Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember.',
                'address' => 'Gedung Teknik Komputer, Kampus ITS Sukolilo, Surabaya.',
                'sections' => [
                    [
                        'title' => 'Navigasi',
                        'links' => $navigation,
                    ],
                    [
                        'title' => 'Akses',
                        'links' => [
                            ['href' => $loginUrl, 'label' => 'Masuk ke CMOS'],
                            ['href' => $infoUrl, 'label' => 'Arsip informasi'],
                            ['href' => $instagramUrl, 'label' => 'Instagram resmi'],
                        ],
                    ],
                    [
                        'title' => 'Kanal pendukung',
                        'links' => array_map(static fn (array $item): array => [
                            'href' => $item['href'],
                            'label' => $item['title'],
                        ], $supportLinks),
                    ],
                ],
            ],
            'latestInfo' => $latestInfo,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function latestInfo(): array
    {
        if (! Schema::hasTable('information_boards')) {
            return [];
        }

        return InformationBoard::published()
            ->select(['id', 'title', 'slug', 'excerpt', 'content', 'cover_image', 'published_at'])
            ->with('categories:id,name')
            ->latest('published_at')
            ->take(3)
            ->get()
            ->map(fn (InformationBoard $item) => [
                'title' => $item->title,
                'excerpt' => $item->excerpt ?: Str::limit(strip_tags($item->content), 140),
                'publishedAtLabel' => optional($item->publishedAtLocal)?->locale('id')->translatedFormat('d M Y'),
                'coverImage' => $item->cover_image_optimized,
                'category' => $item->categories->pluck('name')->implode(', ') ?: 'Papan Informasi',
                'url' => route('informasi.show', $item->slug),
            ])
            ->values()
            ->all();
    }
}
