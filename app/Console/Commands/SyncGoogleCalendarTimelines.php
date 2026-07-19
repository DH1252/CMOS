<?php

namespace App\Console\Commands;

use App\Models\Timeline;
use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;

class SyncGoogleCalendarTimelines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-calendar:sync
                            {--missing : Only synchronize timelines without a complete Google Calendar mapping}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize timelines and retry queued Google Calendar deletions';

    /**
     * Execute the console command.
     */
    public function handle(GoogleCalendarService $googleCalendarService): int
    {
        if (! $googleCalendarService->enabled()) {
            $this->error('Google Calendar synchronization is disabled.');

            return self::FAILURE;
        }

        $deletionResult = $googleCalendarService->retryQueuedDeletions();
        if ($deletionResult['processed'] > 0 || $deletionResult['failed'] > 0) {
            $this->info("Penghapusan tertunda: {$deletionResult['processed']} berhasil, {$deletionResult['failed']} gagal.");
        }

        $query = Timeline::query()->with(['department', 'program'])->orderBy('id');

        if ($this->option('missing')) {
            $query->where(function ($query) {
                $query->whereNull('google_event_id')
                    ->orWhereRaw("TRIM(COALESCE(google_event_id, '')) = ''")
                    ->orWhereNull('google_calendar_id')
                    ->orWhereRaw("TRIM(COALESCE(google_calendar_id, '')) = ''");
            });
        }

        $synchronized = 0;
        $failed = 0;

        $query->chunkById(100, function ($timelines) use ($googleCalendarService, &$synchronized, &$failed) {
            foreach ($timelines as $timeline) {
                $result = $googleCalendarService->upsertTimelineEvent($timeline);

                if (! $result) {
                    $failed++;
                    $this->warn("Timeline {$timeline->id} gagal disinkronkan.");

                    continue;
                }

                $timeline->fill([
                    'google_event_id' => $result['event_id'],
                    'google_calendar_id' => $result['calendar_id'],
                ]);

                if ($timeline->isDirty()) {
                    $timeline->saveQuietly();
                }

                $synchronized++;
            }
        });

        $this->info("Sinkronisasi selesai: {$synchronized} berhasil, {$failed} gagal.");

        return $failed === 0 && $deletionResult['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
