<?php

namespace App\Http\Controllers;

use App\Support\SiteStatistics;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Inertia\Response;

class SiteStatisticsController extends Controller
{
    public function __construct(private readonly SiteStatistics $statistics) {}

    public function index(): Response
    {
        $summary = $this->statistics->summary();

        $competitionsPath = storage_path('app/competitions.json');
        $lastFetched = file_exists($competitionsPath)
            ? date('c', filemtime($competitionsPath))
            : null;

        return \Inertia\Inertia::render('pages/SiteStatisticsPage', [
            'title' => 'Statistik Situs',
            'description' => 'Trafik pengunjung dan ringkasan aktivitas situs.',
            'stats' => $summary['stats'],
            'visitorTrend' => $summary['visitorTrend'],
            'topUrls' => $summary['topUrls'],
            'recentVisitors' => $summary['recentVisitors'],
            'competitionSettings' => [
                'spreadsheetUrl' => 'https://docs.google.com/spreadsheets/d/1rHMZoGB3RgzDVwRqW0QalCahShWGMdLTOQVO62x5EK4/edit',
                'scheduleInfo' => 'Setiap hari pukul 01:00 WIB (Daily at 01:00 AM)',
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
}
