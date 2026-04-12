<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Program;
use App\Models\Task;
use App\Models\Timeline;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $data = [
            'user' => $user,
            'stats' => $this->getStats($user),
            'recentTasks' => $this->getRecentTasks($user),
            'upcomingTimelines' => $this->getUpcomingTimelines($user),
            'tasksByStatus' => $this->getTasksByStatus($user),
        ];

        // Add extra stats for admin/bph
        if ($user->hasRole(['admin', 'bph'])) {
            $data['staffRanking'] = $this->getStaffRanking();
            $data['departmentProgress'] = $this->getDepartmentProgress();
            $data['monthlyTrends'] = $this->getMonthlyTrends();
        }

        return view('dashboard.index', $data);
    }

    private function getStats($user): array
    {
        $baseQuery = Task::query();

        // Staff only sees their own tasks
        if ($user->isStaff()) {
            $baseQuery->where('assigned_to', $user->id);
        } elseif ($user->isKabinet() && $user->department_id) {
            $baseQuery->whereHas('program', fn ($q) => $q->where('department_id', $user->department_id));
        }

        return [
            'totalUsers' => $user->hasRole(['admin', 'bph']) ? User::active()->count() : null,
            'totalPrograms' => $user->isStaff()
                ? $user->programs()->count()
                : ($user->isKabinet()
                    ? Program::where('department_id', $user->department_id)->count()
                    : Program::count()),
            'totalTasks' => (clone $baseQuery)->count(),
            'completedTasks' => (clone $baseQuery)->where('status', 'done')->count(),
            'pendingTasks' => (clone $baseQuery)->where('status', '!=', 'done')->count(),
            'overdueTasks' => (clone $baseQuery)->overdue()->count(),
        ];
    }

    private function getRecentTasks($user)
    {
        $query = Task::with(['program', 'assignee'])
            ->latest()
            ->limit(5);

        if ($user->isStaff()) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->isKabinet() && $user->department_id) {
            $query->whereHas('program', fn ($q) => $q->where('department_id', $user->department_id));
        }

        return $query->get();
    }

    private function getUpcomingTimelines($user)
    {
        $query = Timeline::where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(5);

        if ($user->isStaff() || $user->isKabinet()) {
            $query->where(function ($q) use ($user) {
                $q->where('type', 'global')
                    ->orWhere('department_id', $user->department_id);
            });
        }

        return $query->get();
    }

    private function getTasksByStatus($user): array
    {
        $query = Task::query();

        if ($user->isStaff()) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->isKabinet() && $user->department_id) {
            $query->whereHas('program', fn ($q) => $q->where('department_id', $user->department_id));
        }

        return [
            'todo' => (clone $query)->where('status', 'todo')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'done' => (clone $query)->where('status', 'done')->count(),
        ];
    }

    private function getStaffRanking()
    {
        return User::byRole('staff')
            ->active()
            ->withAvg('evaluations', 'total_score')
            ->orderByDesc('evaluations_avg_total_score')
            ->limit(5)
            ->get();
    }

    /**
     * Get task progress per department for chart
     */
    private function getDepartmentProgress(): array
    {
        return Department::query()
            ->where('departments.status', 'active')
            ->leftJoin('programs', 'programs.department_id', '=', 'departments.id')
            ->leftJoin('tasks', 'tasks.program_id', '=', 'programs.id')
            ->select('departments.name')
            ->selectRaw('COUNT(tasks.id) as total_tasks')
            ->selectRaw("SUM(CASE WHEN tasks.status = 'done' THEN 1 ELSE 0 END) as done_tasks")
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get()
            ->map(function ($department) {
                $totalTasks = (int) ($department->total_tasks ?? 0);
                $doneTasks = (int) ($department->done_tasks ?? 0);

                return [
                    'name' => $department->name,
                    'total' => $totalTasks,
                    'done' => $doneTasks,
                    'percentage' => $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Get monthly task completion trends for chart (last 6 months)
     */
    private function getMonthlyTrends(): array
    {
        $months = [];
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        $createdByMonth = Task::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $completedByMonth = Task::query()
            ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m') as month_key, COUNT(*) as total")
            ->where('status', 'done')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');

            $months[] = [
                'month' => $date->translatedFormat('M Y'),
                'created' => (int) ($createdByMonth[$monthKey] ?? 0),
                'completed' => (int) ($completedByMonth[$monthKey] ?? 0),
            ];
        }

        return $months;
    }
}
