<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $uniqueIndex = 'evaluations_user_id_evaluator_type_period_unique';
        $indexNames = collect(Schema::getIndexes('evaluations'))->pluck('name');
        $uniqueWasPresent = $indexNames->contains($uniqueIndex);

        if (! Schema::hasTable('evaluation_legacy_archives')) {
            Schema::create('evaluation_legacy_archives', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_id')->nullable()->index();
                $table->longText('payload')->nullable();
                $table->boolean('is_metadata')->default(false);
                $table->boolean('unique_was_present')->default(false);
                $table->timestamps();
            });
        }

        DB::table('evaluation_legacy_archives')->insert([
            'is_metadata' => true,
            'unique_was_present' => $uniqueWasPresent,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $duplicateGroups = DB::table('evaluations')
            ->select(['user_id', 'evaluator_type', 'period'])
            ->groupBy(['user_id', 'evaluator_type', 'period'])
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $duplicates = DB::table('evaluations')
                ->where('user_id', $group->user_id)
                ->where('evaluator_type', $group->evaluator_type)
                ->where('period', $group->period)
                ->orderByDesc('id')
                ->get()
                ->skip(1);

            foreach ($duplicates as $duplicate) {
                DB::table('evaluation_legacy_archives')->insert([
                    'original_id' => $duplicate->id,
                    'payload' => json_encode((array) $duplicate, JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('evaluations')->where('id', $duplicate->id)->delete();
            }
        }

        $indexNames = collect(Schema::getIndexes('evaluations'))->pluck('name');

        Schema::table('evaluations', function (Blueprint $table) use ($indexNames, $uniqueIndex) {
            if ($indexNames->contains('evaluations_user_id_evaluator_type_period_index')) {
                $table->dropIndex('evaluations_user_id_evaluator_type_period_index');
            }

            if (! $indexNames->contains($uniqueIndex)) {
                $table->unique(['user_id', 'evaluator_type', 'period']);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('evaluation_legacy_archives')) {
            return;
        }

        $uniqueWasPresent = (bool) DB::table('evaluation_legacy_archives')
            ->where('is_metadata', true)
            ->value('unique_was_present');

        if (! $uniqueWasPresent) {
            $indexNames = collect(Schema::getIndexes('evaluations'))->pluck('name');

            if ($indexNames->contains('evaluations_user_id_evaluator_type_period_unique')) {
                Schema::table('evaluations', function (Blueprint $table) {
                    $table->dropUnique('evaluations_user_id_evaluator_type_period_unique');
                });
            }
        }

        DB::table('evaluation_legacy_archives')
            ->where('is_metadata', false)
            ->orderBy('id')
            ->each(function ($archive): void {
                DB::table('evaluations')->insert(
                    json_decode($archive->payload, true, 512, JSON_THROW_ON_ERROR),
                );
            });

        Schema::drop('evaluation_legacy_archives');
    }
};
