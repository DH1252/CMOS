<?php

namespace Database\Seeders;

use App\Models\EvaluationCriteria;
use Illuminate\Database\Seeder;

class EvaluationCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            [
                'name' => 'Kehadiran',
                'description' => 'Konsistensi hadir pada rapat, agenda, dan tugas organisasi.',
                'max_score' => 5,
                'weight' => 1,
            ],
            [
                'name' => 'Kedisiplinan',
                'description' => 'Kepatuhan terhadap timeline, SOP, dan komitmen kerja.',
                'max_score' => 5,
                'weight' => 1,
            ],
            [
                'name' => 'Tanggung Jawab',
                'description' => 'Kemampuan menuntaskan amanah dengan kualitas yang baik.',
                'max_score' => 5,
                'weight' => 1,
            ],
            [
                'name' => 'Kerjasama',
                'description' => 'Kolaborasi, empati, dan kontribusi dalam tim lintas departemen.',
                'max_score' => 5,
                'weight' => 1,
            ],
            [
                'name' => 'Inisiatif',
                'description' => 'Keaktifan mengambil langkah tanpa menunggu arahan penuh.',
                'max_score' => 5,
                'weight' => 1,
            ],
            [
                'name' => 'Komunikasi',
                'description' => 'Kejelasan koordinasi, update progres, dan respons antar tim.',
                'max_score' => 5,
                'weight' => 1,
            ],
        ] as $criteria) {
            EvaluationCriteria::updateOrCreate(
                ['name' => $criteria['name']],
                [
                    'description' => $criteria['description'],
                    'max_score' => $criteria['max_score'],
                    'weight' => $criteria['weight'],
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('Evaluation criteria seeded.');
    }
}
