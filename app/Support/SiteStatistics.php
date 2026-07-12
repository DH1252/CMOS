<?php

namespace App\Support;

use App\Models\InformationBoard;
use App\Models\Program;
use App\Models\Task;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class SiteStatistics
{
    private string $clientTimezone;

    public function __construct()
    {
        $this->clientTimezone = (string) config('app.client_timezone', 'Asia/Jakarta');
    }

    /**
     * Lightweight visitor counts surfaced to the auth shell footer.
     *
     * @return array{today: int, thisMonth: int, total: int}
     */
    public function visitorCounts(): array
    {
        if (! $this->visitorsTableExists()) {
            return ['today' => 0, 'thisMonth' => 0, 'total' => 0];
        }

        [$dayStart, $dayEnd] = $this->dayBoundsUtc();
        [$monthStart, $monthEnd] = $this->monthBoundsUtc();

        return [
            'today' => Visitor::query()
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count(),
            'thisMonth' => Visitor::query()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count(),
            'total' => Visitor::count(),
        ];
    }

    /**
     * Full statistics payload rendered on the admin statistics page.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $visitorCounts = $this->visitorCounts();

        return [
            'stats' => [
                ['label' => 'Pengunjung Unik Hari Ini', 'value' => $visitorCounts['today'], 'icon' => 'fas fa-eye', 'tone' => 'primary'],
                ['label' => 'Pengunjung Unik Bulan Ini', 'value' => $visitorCounts['thisMonth'], 'icon' => 'fas fa-calendar-check', 'tone' => 'info'],
                ['label' => 'Total Pengunjung Unik', 'value' => $visitorCounts['total'], 'icon' => 'fas fa-users-line', 'tone' => 'success'],
                ['label' => 'IP Unik Bulan Ini', 'value' => $this->uniqueIpsThisMonth(), 'icon' => 'fas fa-fingerprint', 'tone' => 'warning'],
                ['label' => 'Total Anggota', 'value' => $this->safeCount(User::query()), 'icon' => 'fas fa-users', 'tone' => 'secondary'],
                ['label' => 'Total Program', 'value' => $this->safeCount(Program::query()), 'icon' => 'fas fa-diagram-project', 'tone' => 'primary'],
                ['label' => 'Total Task', 'value' => $this->safeCount(Task::query()), 'icon' => 'fas fa-list-check', 'tone' => 'info'],
                ['label' => 'Total Publikasi', 'value' => $this->safeCount(InformationBoard::query()), 'icon' => 'fas fa-newspaper', 'tone' => 'success'],
            ],
            'visitorTrend' => $this->dailyVisitorTrend(14),
            'topUrls' => $this->topVisitedUrls(10),
            'recentVisitors' => $this->recentVisitors(10),
        ];
    }

    /**
     * Daily visitor counts for the last $days days (oldest first).
     *
     * @return array<int, array{date: string, label: string, count: int}>
     */
    public function dailyVisitorTrend(int $days = 14): array
    {
        if (! $this->visitorsTableExists()) {
            return [];
        }

        $trend = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $dayStart = now($this->clientTimezone)->subDays($i)->startOfDay();
            $dayEnd = $dayStart->copy()->addDay();

            $trend[] = [
                'date' => $dayStart->toDateString(),
                'label' => $dayStart->locale('id')->translatedFormat('d M'),
                'count' => Visitor::query()
                    ->whereBetween('created_at', [
                        $dayStart->setTimezone('UTC'),
                        $dayEnd->setTimezone('UTC'),
                    ])
                    ->count(),
            ];
        }

        return $trend;
    }

    /**
     * @return array<int, array{url: string, visits: int}>
     */
    public function topVisitedUrls(int $limit = 10): array
    {
        if (! $this->visitorsTableExists()) {
            return [];
        }

        return Visitor::query()
            ->selectRaw('url, COUNT(*) as visits')
            ->whereNotNull('url')
            ->groupBy('url')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => ['url' => $row->url, 'visits' => (int) $row->visits])
            ->all();
    }

    /**
     * @return array<int, array{ip: string, url: string, userAgent: string, visitedAt: string}>
     */
    public function recentVisitors(int $limit = 10): array
    {
        if (! $this->visitorsTableExists()) {
            return [];
        }

        return Visitor::query()
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Visitor $visitor) => [
                'ip' => $visitor->ip_address ?: '-',
                'url' => $visitor->url ?: '-',
                'userAgent' => $visitor->user_agent ?: '-',
                'visitedAt' => optional($visitor->created_at?->setTimezone($this->clientTimezone))->toIso8601String(),
            ])
            ->all();
    }

    private function uniqueIpsThisMonth(): int
    {
        if (! $this->visitorsTableExists()) {
            return 0;
        }

        [$monthStart, $monthEnd] = $this->monthBoundsUtc();

        return (int) Visitor::query()
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereNotNull('ip_address')
            ->distinct('ip_address')
            ->count('ip_address');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function dayBoundsUtc(): array
    {
        $start = now($this->clientTimezone)->startOfDay();

        return [
            $start->copy()->setTimezone('UTC'),
            $start->copy()->addDay()->setTimezone('UTC'),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function monthBoundsUtc(): array
    {
        $start = now($this->clientTimezone)->startOfMonth();

        return [
            $start->copy()->setTimezone('UTC'),
            $start->copy()->addMonth()->setTimezone('UTC'),
        ];
    }

    private function visitorsTableExists(): bool
    {
        return Schema::hasTable('visitors');
    }

    private function safeCount($query): int
    {
        $model = $query->getModel();

        if (! Schema::hasTable($model->getTable())) {
            return 0;
        }

        return $query->count();
    }
}
