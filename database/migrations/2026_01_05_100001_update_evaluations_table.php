<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('evaluations', 'discipline')) {
            Schema::table('evaluations', function (Blueprint $table) {
                $table->renameColumn('total_score', 'legacy_total_score');
            });

            Schema::table('evaluations', function (Blueprint $table) {
                $table->enum('evaluator_type', ['kabinet', 'bph'])->nullable()->after('evaluator_id');
                $table->tinyInteger('kehadiran')->nullable()->after('period');
                $table->tinyInteger('kedisiplinan')->nullable()->after('kehadiran');
                $table->tinyInteger('tanggung_jawab')->nullable()->after('kedisiplinan');
                $table->tinyInteger('kerjasama')->nullable()->after('tanggung_jawab');
                $table->tinyInteger('inisiatif')->nullable()->after('kerjasama');
                $table->tinyInteger('komunikasi')->nullable()->after('inisiatif');
                $table->decimal('total_score', 3, 2)->nullable()->after('komunikasi');
            });

            $evaluatorRoles = DB::table('users')
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->pluck('roles.name', 'users.id');

            DB::table('evaluations')
                ->orderBy('id')
                ->chunkById(100, function ($evaluations) use ($evaluatorRoles): void {
                    foreach ($evaluations as $evaluation) {
                        $scores = collect([
                            $evaluation->discipline,
                            $evaluation->responsibility,
                            $evaluation->teamwork,
                            $evaluation->initiative,
                        ])->map(fn ($score): int => max(1, min(5, (int) round(((int) $score) / 20))));

                        $inferredScore = (int) round($scores->average());
                        $newScores = [
                            'kehadiran' => $inferredScore,
                            'kedisiplinan' => $scores[0],
                            'tanggung_jawab' => $scores[1],
                            'kerjasama' => $scores[2],
                            'inisiatif' => $scores[3],
                            'komunikasi' => $inferredScore,
                        ];
                        $period = trim((string) $evaluation->period);

                        if ($period === '') {
                            $period = $evaluation->created_at
                                ? Carbon::parse($evaluation->created_at)->format('Y-m')
                                : now()->format('Y-m');
                        }

                        DB::table('evaluations')
                            ->where('id', $evaluation->id)
                            ->update([
                                ...$newScores,
                                'evaluator_type' => $evaluatorRoles->get($evaluation->evaluator_id) === 'kabinet' ? 'kabinet' : 'bph',
                                'period' => mb_substr($period, 0, 50),
                                'total_score' => round(array_sum($newScores) / count($newScores), 2),
                            ]);
                    }
                });

            Schema::table('evaluations', function (Blueprint $table) {
                $table->enum('evaluator_type', ['kabinet', 'bph'])->nullable(false)->change();
                $table->string('period', 50)->nullable(false)->change();
                $table->tinyInteger('kehadiran')->default(1)->nullable(false)->change();
                $table->tinyInteger('kedisiplinan')->default(1)->nullable(false)->change();
                $table->tinyInteger('tanggung_jawab')->default(1)->nullable(false)->change();
                $table->tinyInteger('kerjasama')->default(1)->nullable(false)->change();
                $table->tinyInteger('inisiatif')->default(1)->nullable(false)->change();
                $table->tinyInteger('komunikasi')->default(1)->nullable(false)->change();
                $table->decimal('total_score', 3, 2)->default(0)->nullable(false)->change();
            });

            $hasDuplicateEvaluation = DB::table('evaluations')
                ->select(['user_id', 'evaluator_type', 'period'])
                ->groupBy(['user_id', 'evaluator_type', 'period'])
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            Schema::table('evaluations', function (Blueprint $table) use ($hasDuplicateEvaluation) {
                if ($hasDuplicateEvaluation) {
                    $table->index(['user_id', 'evaluator_type', 'period']);

                    return;
                }

                $table->unique(['user_id', 'evaluator_type', 'period']);
            });
        }

        if (! Schema::hasTable('grade_parameters')) {
            Schema::create('grade_parameters', function (Blueprint $table) {
                $table->id();
                $table->decimal('min_score', 3, 2);
                $table->decimal('max_score', 3, 2);
                $table->string('grade', 2);
                $table->string('label');
                $table->string('color', 7)->default('#10B981');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_parameters');

        if (! Schema::hasColumn('evaluations', 'legacy_total_score')) {
            return;
        }

        $indexNames = collect(Schema::getIndexes('evaluations'))->pluck('name');

        Schema::table('evaluations', function (Blueprint $table) use ($indexNames) {
            if ($indexNames->contains('evaluations_user_id_evaluator_type_period_unique')) {
                $table->dropUnique('evaluations_user_id_evaluator_type_period_unique');
            }

            if ($indexNames->contains('evaluations_user_id_evaluator_type_period_index')) {
                $table->dropIndex('evaluations_user_id_evaluator_type_period_index');
            }
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->string('period')->nullable()->change();
            $table->dropColumn([
                'evaluator_type',
                'kehadiran',
                'kedisiplinan',
                'tanggung_jawab',
                'kerjasama',
                'inisiatif',
                'komunikasi',
                'total_score',
            ]);
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->renameColumn('legacy_total_score', 'total_score');
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_score')->default(0)->change();
        });
    }
};
