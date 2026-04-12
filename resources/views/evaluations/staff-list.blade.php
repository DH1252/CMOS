@extends('layouts.app')

@section('title', $department->name . ' - Evaluasi Staff')
@section('page-title', 'Evaluasi Staff')

@section('content')
@php
    $monthLabel = $availableMonths[$month] ?? \App\Models\Evaluation::getMonthLabel($month);

    $props = [
        'title' => 'Staff ' . $department->name,
        'description' => 'Lacak status penilaian per staff, lihat detail histori, atau langsung isi evaluasi untuk periode ' . $monthLabel . '.',
        'breadcrumbs' => [
            ['label' => 'Evaluasi', 'href' => route('evaluations.index')],
            ['label' => $department->name],
        ],
        'month' => [
            'value' => $month,
            'label' => $monthLabel,
        ],
        'months' => collect($availableMonths)->map(fn ($label, $value) => [
            'value' => $value,
            'label' => $label,
        ])->values(),
        'monthAction' => route('evaluations.department', $department),
        'staff' => $staffMembers->map(function ($staff) use ($month) {
            return [
                'name' => $staff->name,
                'email' => $staff->email,
                'avatar' => $staff->avatar_url,
                'hasEvaluated' => $staff->has_evaluated,
                'statusLabel' => $staff->has_evaluated ? 'Dinilai' : 'Belum dinilai',
                'statusTone' => $staff->has_evaluated ? 'success' : 'warning',
                'evaluation' => $staff->evaluation_data ? [
                    'hasKabinet' => $staff->evaluation_data['has_kabinet'],
                    'kabinetScore' => number_format($staff->evaluation_data['kabinet_score'], 1),
                    'hasBph' => $staff->evaluation_data['has_bph'],
                    'bphScore' => number_format($staff->evaluation_data['bph_score'], 1),
                    'finalScore' => number_format($staff->evaluation_data['final_score'], 1),
                    'grade' => $staff->evaluation_data['grade'] ? [
                        'label' => $staff->evaluation_data['grade']->grade,
                        'color' => $staff->evaluation_data['grade']->color,
                    ] : null,
                ] : null,
                'primaryAction' => $staff->has_evaluated
                    ? [
                        'href' => route('evaluations.show', ['user' => $staff]),
                        'label' => 'Lihat Detail',
                        'icon' => 'fas fa-eye',
                        'tone' => 'secondary',
                    ]
                    : [
                        'href' => route('evaluations.create', ['user_id' => $staff->id, 'month' => $month]),
                        'label' => 'Nilai Sekarang',
                        'icon' => 'fas fa-star',
                        'tone' => 'primary',
                    ],
            ];
        })->values(),
        'emptyState' => [
            'title' => 'Tidak ada staff aktif',
            'text' => 'Departemen ini belum memiliki staff aktif untuk dinilai.',
        ],
    ];
@endphp

<script id="svelte-evaluation-staff-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-evaluation-staff-root"></div>
@endsection
