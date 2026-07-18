<?php

namespace App\Http\Controllers;

use App\Support\SiteStatistics;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Inertia\Response;

class SiteStatisticsController extends Controller
{
    public function __construct(private readonly SiteStatistics $statistics) {}

    public function index(): Response
    {
        $summary = $this->statistics->summary();
        $timezone = (string) config('app.client_timezone', 'Asia/Jakarta');

        $competitionsPath = storage_path('app/competitions.json');
        $lastFetched = file_exists($competitionsPath)
            ? Carbon::createFromTimestamp(filemtime($competitionsPath), $timezone)->toIso8601String()
            : null;

        return \Inertia\Inertia::render('pages/SiteStatisticsPage', [
            'title' => 'Statistik Situs',
            'description' => 'Trafik pengunjung dan ringkasan aktivitas situs.',
            'stats' => $summary['stats'],
            'visitorTrend' => $summary['visitorTrend'],
            'topUrls' => $summary['topUrls'],
            'recentVisitors' => $summary['recentVisitors'],
            'competitionSettings' => [
                'spreadsheetUrl' => \App\Models\Setting::get('competition_spreadsheet_url', 'https://docs.google.com/spreadsheets/d/1rHMZoGB3RgzDVwRqW0QalCahShWGMdLTOQVO62x5EK4/edit'),
                'scheduleTime' => \App\Models\Setting::get('competition_schedule_time', '01:00'),
                'scheduleInfo' => 'Setiap hari pukul '.\App\Models\Setting::get('competition_schedule_time', '01:00').' ('.$timezone.')',
                'timezone' => $timezone,
                'lastFetched' => $lastFetched,
                'hasApiKey' => ! empty(config('services.google.api_key')),
            ],
        ]);
    }

    public function fetchCompetitions(): JsonResponse
    {
        $exitCode = Artisan::call('competitions:fetch');
        $output = Artisan::output();

        return response()->json([
            'success' => $exitCode === 0,
            'output' => $output,
        ]);
    }

    public function updateSettings(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'spreadsheetUrl' => ['required', 'url'],
            'scheduleTime' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
        ]);

        \App\Models\Setting::set('competition_spreadsheet_url', $request->input('spreadsheetUrl'));
        \App\Models\Setting::set('competition_schedule_time', $request->input('scheduleTime'));

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan kompetisi berhasil disimpan.',
        ]);
    }
}
