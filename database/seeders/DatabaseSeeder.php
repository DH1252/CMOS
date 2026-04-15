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
            ['year' => '2025/2026'],
            [
                'name' => 'Kabinet Harmoni',
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
        foreach ([
            ['name' => 'PSDM', 'description' => 'Pengembangan Sumber Daya Manusia'],
            ['name' => 'Medinfo', 'description' => 'Media dan Informasi'],
            ['name' => 'Humas', 'description' => 'Hubungan Masyarakat'],
            ['name' => 'Ristek', 'description' => 'Riset dan Teknologi'],
            ['name' => 'Akademik', 'description' => 'Bidang Akademik'],
        ] as $department) {
            Department::updateOrCreate(
                ['cabinet_id' => $cabinet->id, 'name' => $department['name']],
                [
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
