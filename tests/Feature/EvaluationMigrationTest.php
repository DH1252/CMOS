<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EvaluationMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_evaluations_are_transformed_without_losing_rows_or_original_totals(): void
    {
        $role = Role::create(['name' => 'bph', 'description' => 'BPH']);
        $staff = User::factory()->createOne(['status' => 'active']);
        $evaluator = User::factory()->createOne([
            'role_id' => $role->id,
            'status' => 'active',
        ]);
        $migration = require database_path('migrations/2026_01_05_100001_update_evaluations_table.php');
        $archiveMigration = require database_path('migrations/2026_07_22_135233_archive_duplicate_evaluations_and_enforce_uniqueness.php');

        $archiveMigration->down();
        $migration->down();

        DB::table('evaluations')->insert([
            [
                'user_id' => $staff->id,
                'evaluator_id' => $evaluator->id,
                'period' => null,
                'discipline' => 80,
                'responsibility' => 80,
                'teamwork' => 80,
                'initiative' => 80,
                'total_score' => 320,
                'notes' => 'Legacy one',
                'created_at' => '2026-04-15 10:00:00',
                'updated_at' => '2026-04-15 10:00:00',
            ],
            [
                'user_id' => $staff->id,
                'evaluator_id' => $evaluator->id,
                'period' => null,
                'discipline' => 100,
                'responsibility' => 100,
                'teamwork' => 100,
                'initiative' => 100,
                'total_score' => 400,
                'notes' => 'Legacy duplicate',
                'created_at' => '2026-04-20 10:00:00',
                'updated_at' => '2026-04-20 10:00:00',
            ],
        ]);

        $migration->up();
        $archiveMigration->up();

        $this->assertDatabaseCount('evaluations', 1);
        $this->assertDatabaseHas('evaluations', [
            'notes' => 'Legacy duplicate',
            'evaluator_type' => 'bph',
            'period' => '2026-04',
            'kehadiran' => 5,
            'kedisiplinan' => 5,
            'tanggung_jawab' => 5,
            'kerjasama' => 5,
            'inisiatif' => 5,
            'komunikasi' => 5,
            'total_score' => 5,
            'legacy_total_score' => 400,
        ]);
        $archivePayload = DB::table('evaluation_legacy_archives')
            ->where('is_metadata', false)
            ->value('payload');

        $this->assertIsString($archivePayload);
        $this->assertSame('Legacy one', json_decode($archivePayload, true, 512, JSON_THROW_ON_ERROR)['notes']);
    }
}
