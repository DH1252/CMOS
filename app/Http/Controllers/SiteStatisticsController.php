<?php

namespace App\Http\Controllers;

use App\Support\SiteStatistics;
use Inertia\Response;

class SiteStatisticsController extends Controller
{
    public function __construct(private readonly SiteStatistics $statistics) {}

    public function index(): Response
    {
        $summary = $this->statistics->summary();

        return \Inertia\Inertia::render('pages/SiteStatisticsPage', [
            'title' => 'Statistik Situs',
            'description' => 'Trafik pengunjung dan ringkasan aktivitas situs.',
            'stats' => $summary['stats'],
            'visitorTrend' => $summary['visitorTrend'],
            'topUrls' => $summary['topUrls'],
            'recentVisitors' => $summary['recentVisitors'],
        ]);
    }
}
