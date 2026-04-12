@extends('layouts.app')

@section('title', 'Nilai Saya')
@section('page-title', 'Nilai Saya')

@section('content')
@php
    $user = auth()->user();

    $criteria = [
        'kehadiran' => 'Kehadiran',
        'kedisiplinan' => 'Kedisiplinan',
        'tanggung_jawab' => 'Tanggung Jawab',
        'kerjasama' => 'Kerjasama',
        'inisiatif' => 'Inisiatif',
        'komunikasi' => 'Komunikasi',
    ];

    $props = [
        'title' => 'Nilai Saya',
        'description' => 'Lihat ringkasan nilai dari Kabinet dan BPH pada setiap periode, termasuk feedback yang sudah masuk.',
        'profile' => [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar_url,
            'badge' => [
                'label' => $user->department?->name ?? 'No Department',
                'tone' => 'info',
            ],
        ],
        'gradeLegend' => $gradeParams->map(fn ($grade) => [
            'letter' => $grade->grade,
            'label' => $grade->label,
            'range' => $grade->min_score . ' - ' . $grade->max_score,
            'color' => $grade->color,
        ])->values(),
        'periods' => $evaluations->map(function ($evals, $period) use ($periodScores, $criteria) {
            $combined = $periodScores[$period] ?? null;

            return [
                'label' => \App\Models\Evaluation::getMonthLabel($period),
                'status' => $combined ? [
                    'label' => $combined['is_complete'] ? 'Lengkap' : 'Pending',
                    'tone' => $combined['is_complete'] ? 'success' : 'warning',
                ] : null,
                'grade' => $combined && $combined['grade'] ? [
                    'label' => $combined['grade']->grade . ' · ' . $combined['grade']->label,
                    'color' => $combined['grade']->color,
                ] : null,
                'summary' => $combined ? [
                    [
                        'label' => 'Kabinet',
                        'value' => $combined['has_kabinet'] ? number_format($combined['kabinet_score'], 1) : 'Pending',
                        'muted' => !$combined['has_kabinet'],
                    ],
                    [
                        'label' => 'BPH',
                        'value' => $combined['has_bph'] ? number_format($combined['bph_score'], 1) : 'Pending',
                        'muted' => !$combined['has_bph'],
                    ],
                    [
                        'label' => 'Final',
                        'value' => number_format($combined['final_score'], 1),
                        'badge' => $combined['grade'] ? [
                            'label' => $combined['grade']->grade,
                            'color' => $combined['grade']->color,
                        ] : null,
                    ],
                ] : [],
                'entries' => $evals->map(function ($eval) use ($criteria) {
                    return [
                        'label' => strtoupper($eval->evaluator_type),
                        'tone' => $eval->evaluator_type === 'bph' ? 'primary' : 'info',
                        'byline' => $eval->created_at->format('d M Y'),
                        'score' => number_format($eval->total_score, 1),
                        'scoreColor' => $eval->grade_color,
                        'criteria' => collect($criteria)->map(fn ($label, $key) => [
                            'label' => $label,
                            'value' => (int) $eval->$key,
                        ])->values(),
                        'notes' => $eval->notes,
                    ];
                })->values(),
            ];
        })->values(),
        'emptyState' => [
            'title' => 'Belum ada evaluasi',
            'text' => 'Anda belum memiliki evaluasi dari Kabinet atau BPH.',
        ],
    ];
@endphp

<script id="svelte-evaluation-history-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-evaluation-history-root"></div>
@endsection
