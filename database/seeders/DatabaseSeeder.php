<?php

namespace Database\Seeders;

use App\Models\Cabinet;
use App\Models\Department;
use App\Models\InformationCategory;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $cabinet = $this->seedCabinet();

        $this->seedRoles();
        $this->seedDepartments($cabinet);
        $this->seedInformationCategories();
        $this->seedSettings();

        $this->call([
            GradeParameterSeeder::class,
            EvaluationCriteriaSeeder::class,
        ]);

        if ($this->shouldSeedDevelopmentData()) {
            $this->call(DevelopmentSeeder::class);

            $this->command?->info('Development dataset seeded.');
            $this->command?->info('Admin: admin@savana.test / password');
            $this->command?->info('BPH: bph@savana.test / password');
            $this->command?->info('Kabinet: kabinet.psdm@savana.test / password');
            $this->command?->info('Staff: staff1@savana.test / password');
        }
    }

    private function shouldSeedDevelopmentData(): bool
    {
        $configured = config('app.seed_development_data');

        if (is_bool($configured)) {
            return $configured;
        }

        if (is_string($configured)) {
            $normalized = strtolower(trim($configured));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        return ! app()->isProduction();
    }

    private function seedCabinet(): Cabinet
    {
        return Cabinet::updateOrCreate(
            ['year' => '2026/2027'],
            [
                'name' => 'Kabinet Sentra Sinergi',
                'status' => 'active',
            ]
        );
    }

    private function seedRoles(): void
    {
        foreach ([
            ['name' => 'admin', 'description' => 'Administrator dengan akses penuh'],
            ['name' => 'bph', 'description' => 'Badan Pengurus Harian'],
            ['name' => 'kabinet', 'description' => 'Kepala Departemen'],
            ['name' => 'staff', 'description' => 'Anggota Staff'],
        ] as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }

    private function seedDepartments(Cabinet $cabinet): void
    {
        // Struktur departemen sesuai TKO HIMATEKKOM ITS 2026/2027 Kabinet Sentra Sinergi (BAB IV).
        foreach ([
            [
                'name' => 'Badan Pengurus Harian (BPH)',
                'slug' => 'bph',
                'description' => 'Badan Pengurus Harian (BPH) merupakan inti dari kepengurusan HIMATEKKOM ITS. BPH bertanggung jawab atas pengambilan keputusan strategis serta mengawasi jalannya organisasi secara keseluruhan.',
            ],
            [
                'name' => 'Biro Personalia',
                'slug' => 'personalia',
                'description' => 'Biro Personalia merupakan biro yang bertanggung jawab atas manajemen sumber daya Fungsionaris HIMATEKKOM ITS. Biro ini berfokus pada penyusunan standar pengembangan, pemeliharaan motivasi, serta evaluasi kinerja guna memastikan setiap individu dapat berkontribusi secara optimal dalam iklim organisasi yang profesional dan sistematis.',
            ],
            [
                'name' => 'Dalam Negeri (DAGRI)',
                'slug' => 'dagri',
                'description' => 'Departemen Dalam Negeri (DAGRI) merupakan departemen yang bertanggung jawab untuk meningkatkan hubungan internal antar anggota HIMATEKKOM ITS, serta memfasilitasi kegiatan yang berhubungan dengan internal dan minat bakat.',
            ],
            [
                'name' => 'Hubungan Luar (HUBLU)',
                'slug' => 'hublu',
                'description' => 'Departemen Hubungan Luar (HUBLU) HIMATEKKOM ITS berfokus pada pengembangan dan pemeliharaan hubungan strategis dengan berbagai pihak eksternal, termasuk perusahaan, organisasi mahasiswa lain, dan alumni, guna memperluas jaringan serta menjembatani dunia akademik dan industri.',
            ],
            [
                'name' => 'Pengembangan Sumber Daya Mahasiswa (PSDM)',
                'slug' => 'psdm',
                'description' => 'Departemen Pengembangan Sumber Daya Mahasiswa (PSDM) HIMATEKKOM ITS bertanggung jawab untuk meningkatkan kualitas dan kompetensi anggotanya melalui berbagai program pelatihan, workshop, serta kegiatan pengembangan diri. Departemen ini berfokus pada pembentukan karakter, keterampilan teknis, dan kepemimpinan yang dibutuhkan mahasiswa untuk menghadapi tantangan di dunia akademik maupun profesional.',
            ],
            [
                'name' => 'Kesejahteraan Mahasiswa (KESMA)',
                'slug' => 'kesma',
                'description' => 'Departemen Kesejahteraan Mahasiswa (KESMA) HIMATEKKOM ITS merupakan departemen yang bertugas memberikan dukungan dalam bentuk fasilitas, layanan akademik, serta advokasi dan penyaluran aspirasi demi terciptanya lingkungan kemahasiswaan Teknik Komputer ITS yang sejahtera.',
            ],
            [
                'name' => 'Riset dan Keprofesian (RISPROF)',
                'slug' => 'risprof',
                'description' => 'Departemen Riset dan Keprofesian (RISPROF) HIMATEKKOM ITS bertugas untuk mendorong anggota dalam mengembangkan kemampuan riset dan memperdalam keprofesian di bidang Teknik Komputer melalui program penelitian, inovasi, serta pengembangan karier.',
            ],
            [
                'name' => 'Media dan Informasi (MEDFO)',
                'slug' => 'medfo',
                'description' => 'Departemen Media dan Informasi (MEDFO) HIMATEKKOM ITS merupakan garda terdepan dalam pengelolaan seluruh kanal komunikasi dan publikasi organisasi, baik internal maupun eksternal, termasuk produksi konten visual, pengelolaan media sosial, dokumentasi kegiatan, serta penyampaian informasi strategis.',
            ],
            [
                'name' => 'Kewirausahaan (KWU)',
                'slug' => 'kwu',
                'description' => 'Departemen Kewirausahaan (KWU) HIMATEKKOM ITS merupakan departemen yang bertanggung jawab mengelola pendanaan mandiri HIMATEKKOM ITS dan meningkatkan wawasan seputar kewirausahaan bagi mahasiswa departemen Teknik Komputer ITS.',
            ],
            [
                'name' => 'Kaderisasi (TUK)',
                'slug' => 'kaderisasi',
                'description' => 'Departemen Kaderisasi merupakan departemen yang memiliki peran strategis dalam mencetak dan membentuk kader-kader HIMATEKKOM ITS yang memiliki jiwa kepemimpinan, loyalitas, serta pemahaman organisasi yang kuat, dengan koordinasi langsung bersama Ketua HIMATEKKOM ITS.',
            ],
        ] as $department) {
            Department::updateOrCreate(
                ['slug' => $department['slug']],
                [
                    'cabinet_id' => $cabinet->id,
                    'name' => $department['name'],
                    'description' => $department['description'],
                    'status' => 'active',
                ]
            );
        }
    }

    private function seedInformationCategories(): void
    {
        foreach ([
            ['name' => 'Pengumuman', 'slug' => 'pengumuman'],
            ['name' => 'Kegiatan', 'slug' => 'kegiatan'],
            ['name' => 'Kolaborasi', 'slug' => 'kolaborasi'],
            ['name' => 'Dokumentasi', 'slug' => 'dokumentasi'],
        ] as $category) {
            InformationCategory::updateOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']]
            );
        }
    }

    private function seedSettings(): void
    {
        Setting::set('app_name', 'CMOS');
        Setting::set('organization_name', 'HIMATEKKOM ITS');
        Setting::set('theme_color', 'purple');
        Setting::set('evaluation_period', 'quarterly');
    }
}
