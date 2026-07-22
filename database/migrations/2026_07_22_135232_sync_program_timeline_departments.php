<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('timelines')
            ->whereNotNull('program_id')
            ->orderBy('id')
            ->chunkById(100, function ($timelines): void {
                $programDepartments = DB::table('programs')
                    ->whereIn('id', $timelines->pluck('program_id')->unique())
                    ->pluck('department_id', 'id');

                foreach ($timelines as $timeline) {
                    DB::table('timelines')
                        ->where('id', $timeline->id)
                        ->update(['department_id' => $programDepartments->get($timeline->program_id)]);
                }
            });
    }

    public function down(): void
    {
        // Canonical department values cannot be distinguished from pre-existing values.
    }
};
