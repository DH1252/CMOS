<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('status');
        });

        DB::table('tasks')
            ->select(['id'])
            ->orderBy('id')
            ->get()
            ->each(function (object $task): void {
                DB::table('tasks')
                    ->where('id', $task->id)
                    ->update(['sort_order' => $task->id]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
