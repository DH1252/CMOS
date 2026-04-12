@extends('layouts.app')

@section('title', 'Evaluasi Staff')
@section('page-title', 'Evaluasi Staff')

@section('content')
@php
    $monthLabel = $availableMonths[$month] ?? \App\Models\Evaluation::getMonthLabel($month);

    $props = [
        'title' => 'Evaluasi Staff',
        'description' => 'Pilih departemen untuk melakukan review staff dan pantau peringkat performa terbaik pada periode yang sama.',
        'month' => [
            'value' => $month,
            'label' => $monthLabel,
        ],
        'months' => collect($availableMonths)->map(fn ($label, $value) => [
            'value' => $value,
            'label' => $label,
        ])->values(),
        'monthAction' => route('evaluations.index'),
        'ranking' => collect($ranking)->map(function ($staff) {
            $grade = data_get($staff, 'evaluation_data.grade');

            return [
                'name' => $staff['name'],
                'avatar' => $staff['avatar_url'] ?? ('https://ui-avatars.com/api/?name=' . urlencode($staff['name'])),
                'department' => data_get($staff, 'department.name', '-'),
                'score' => number_format($staff['evaluation_score'], 1),
                'grade' => $grade ? [
                    'label' => $grade['grade'],
                    'color' => $grade['color'],
                ] : null,
            ];
        })->values(),
        'departments' => $departments->map(fn ($department) => [
            'href' => route('evaluations.department', ['department' => $department, 'month' => $month]),
            'name' => $department->name,
            'description' => ($department->evaluated_count ?? 0) . ' dari ' . ($department->users_count ?? 0) . ' staff selesai dinilai',
            'totalStaff' => $department->users_count ?? 0,
            'evaluatedStaff' => $department->evaluated_count ?? 0,
        ])->values(),
        'emptyRanking' => [
            'title' => 'Belum ada ranking',
            'text' => 'Data peringkat akan muncul setelah evaluasi staff tersedia pada periode ini.',
        ],
        'emptyDepartments' => [
            'title' => 'Belum ada departemen',
            'text' => 'Departemen akan muncul di sini setelah struktur organisasi tersedia.',
        ],
    ];
@endphp

<script id="svelte-evaluation-hub-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-evaluation-hub-root"></div>
@endsection
