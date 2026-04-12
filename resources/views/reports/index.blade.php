@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan & Statistik')
@section('page-meta', 'Pantau distribusi task, progres departemen, dan performa evaluasi dalam satu dashboard.')

@section('content')
@php
    $props = [
        'title' => 'Laporan & Statistik',
        'description' => 'Pantau kapasitas anggota, distribusi task, dan kualitas eksekusi program lintas departemen.',
        'stats' => [
            ['label' => 'Total Anggota', 'value' => $stats['totalUsers'], 'icon' => 'fas fa-users', 'tone' => 'primary'],
            ['label' => 'Total Proker', 'value' => $stats['totalPrograms'], 'icon' => 'fas fa-diagram-project', 'tone' => 'info'],
            ['label' => 'Total Task', 'value' => $stats['totalTasks'], 'icon' => 'fas fa-list-check', 'tone' => 'warning'],
            ['label' => 'Task Selesai', 'value' => $stats['completedTasks'], 'icon' => 'fas fa-circle-check', 'tone' => 'success'],
        ],
        'taskDistribution' => [
            ['label' => 'Todo', 'value' => $tasksByStatus['todo'], 'tone' => 'secondary'],
            ['label' => 'In Progress', 'value' => $tasksByStatus['in_progress'], 'tone' => 'warning'],
            ['label' => 'Selesai', 'value' => $tasksByStatus['done'], 'tone' => 'success'],
        ],
        'programDistribution' => [
            ['label' => 'Planning', 'value' => $programsByStatus['planning'], 'tone' => 'secondary'],
            ['label' => 'Active', 'value' => $programsByStatus['active'], 'tone' => 'info'],
            ['label' => 'Completed', 'value' => $programsByStatus['completed'], 'tone' => 'success'],
            ['label' => 'Cancelled', 'value' => $programsByStatus['cancelled'], 'tone' => 'warning'],
        ],
        'departments' => $departments->map(function ($department) {
            $totalTasks = (int) ($department->tasks_count ?? 0);
            $completedTasks = (int) ($department->completed_tasks_count ?? 0);

            return [
                'name' => $department->name,
                'members' => (int) $department->users_count,
                'programs' => (int) $department->programs_count,
                'totalTasks' => $totalTasks,
                'completedTasks' => $completedTasks,
                'completionRate' => $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0,
            ];
        })->values(),
        'topStaff' => $topStaff->values()->map(fn ($staff, $index) => [
            'rank' => $index + 1,
            'name' => $staff->name,
            'avatar' => $staff->avatar_url,
            'department' => $staff->department?->name ?? '-',
            'score' => number_format(($staff->evaluations_avg_total_score ?? 0) / 4, 1),
        ]),
        'exports' => [
            ['label' => 'Export PDF', 'href' => route('reports.export', 'pdf'), 'icon' => 'fas fa-file-pdf', 'tone' => 'danger'],
            ['label' => 'Export Excel', 'href' => route('reports.export', 'excel'), 'icon' => 'fas fa-file-excel', 'tone' => 'success'],
        ],
    ];
@endphp

<script id="svelte-report-dashboard-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-report-dashboard-root"></div>
@endsection
