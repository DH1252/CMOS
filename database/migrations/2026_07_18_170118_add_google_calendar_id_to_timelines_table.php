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
        Schema::table('timelines', function (Blueprint $table) {
            $table->string('google_calendar_id')->nullable()->after('google_event_id')->index();
            $table->unsignedInteger('google_sync_generation')->default(0)->after('google_calendar_id');
        });

        $calendarId = trim((string) config('services.google_calendar.calendar_id'));
        if ($calendarId !== '') {
            DB::table('timelines')
                ->whereNotNull('google_event_id')
                ->update(['google_calendar_id' => $calendarId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->dropColumn(['google_calendar_id', 'google_sync_generation']);
        });
    }
};
