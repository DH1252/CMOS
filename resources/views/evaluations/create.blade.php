@extends('layouts.app')

@section('title', 'Beri Evaluasi')
@section('page-title', 'Beri Evaluasi Staff')

@section('content')
@php
    $criteria = [
        ['key' => 'kehadiran', 'label' => 'Kehadiran', 'description' => 'Konsistensi hadir dalam rapat, agenda, dan kegiatan organisasi.'],
        ['key' => 'kedisiplinan', 'label' => 'Kedisiplinan', 'description' => 'Ketepatan waktu, kesiapan, dan kepatuhan terhadap aturan kerja.'],
        ['key' => 'tanggung_jawab', 'label' => 'Tanggung Jawab', 'description' => 'Kualitas penyelesaian tugas serta komitmen menutup pekerjaan.'],
        ['key' => 'kerjasama', 'label' => 'Kerjasama', 'description' => 'Kemampuan berkolaborasi, membantu tim, dan menjaga ritme kerja bersama.'],
        ['key' => 'inisiatif', 'label' => 'Inisiatif', 'description' => 'Keaktifan menawarkan solusi, ide, atau langkah tanpa menunggu arahan.'],
        ['key' => 'komunikasi', 'label' => 'Komunikasi', 'description' => 'Kejelasan menyampaikan progres, hambatan, dan koordinasi antar pihak.'],
    ];

    $props = [
        'title' => 'Form Evaluasi',
        'description' => 'Isi penilaian yang spesifik, konsisten, dan relevan dengan performa staff pada periode berjalan.',
        'staff' => [
            'name' => $staff->name,
            'email' => $staff->email,
            'department' => $staff->department?->name ?? '-',
            'avatar' => $staff->avatar_url,
        ],
        'evaluatorType' => strtoupper($evaluatorType),
        'periodLabel' => \App\Models\Evaluation::getMonthLabel($month),
        'gradeLegend' => $gradeParams->map(fn ($grade) => [
            'letter' => $grade->grade,
            'label' => $grade->label,
            'range' => $grade->min_score . ' - ' . $grade->max_score,
            'color' => $grade->color,
        ])->values(),
        'criteria' => $criteria,
        'form' => [
            'action' => route('evaluations.store'),
            'method' => 'POST',
            'csrfToken' => csrf_token(),
            'hidden' => [
                ['name' => 'user_id', 'value' => $staff->id],
                ['name' => 'period', 'value' => $month],
            ],
        ],
        'values' => [
            'notes' => old('notes'),
            'kehadiran' => old('kehadiran', 3),
            'kedisiplinan' => old('kedisiplinan', 3),
            'tanggung_jawab' => old('tanggung_jawab', 3),
            'kerjasama' => old('kerjasama', 3),
            'inisiatif' => old('inisiatif', 3),
            'komunikasi' => old('komunikasi', 3),
        ],
        'errors' => collect($errors->messages())->map(fn ($messages) => $messages[0])->all(),
        'cancelAction' => [
            'href' => route('evaluations.department', ['department' => $staff->department_id, 'month' => $month]),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
    ];
@endphp

<script id="svelte-evaluation-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-evaluation-form-root"></div>
@endsection
