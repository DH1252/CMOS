@extends('layouts.app')

@section('title', 'Edit Evaluasi')
@section('page-title', 'Edit Evaluasi')

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
        'title' => 'Edit Evaluasi',
        'description' => 'Sesuaikan penilaian untuk menjaga akurasi histori evaluasi staff pada periode ini.',
        'staff' => [
            'name' => $evaluation->user->name,
            'email' => $evaluation->user->email,
            'department' => $evaluation->user->department?->name ?? '-',
            'avatar' => $evaluation->user->avatar_url,
        ],
        'evaluatorType' => strtoupper($evaluation->evaluator_type),
        'periodLabel' => \App\Models\Evaluation::getMonthLabel($evaluation->period),
        'gradeLegend' => $gradeParams->map(fn ($grade) => [
            'letter' => $grade->grade,
            'label' => $grade->label,
            'range' => $grade->min_score . ' - ' . $grade->max_score,
            'color' => $grade->color,
        ])->values(),
        'criteria' => $criteria,
        'form' => [
            'action' => route('evaluations.update', $evaluation),
            'method' => 'POST',
            'spoofMethod' => 'PUT',
            'csrfToken' => csrf_token(),
        ],
        'values' => [
            'notes' => old('notes', $evaluation->notes),
            'kehadiran' => old('kehadiran', $evaluation->kehadiran),
            'kedisiplinan' => old('kedisiplinan', $evaluation->kedisiplinan),
            'tanggung_jawab' => old('tanggung_jawab', $evaluation->tanggung_jawab),
            'kerjasama' => old('kerjasama', $evaluation->kerjasama),
            'inisiatif' => old('inisiatif', $evaluation->inisiatif),
            'komunikasi' => old('komunikasi', $evaluation->komunikasi),
        ],
        'errors' => collect($errors->messages())->map(fn ($messages) => $messages[0])->all(),
        'cancelAction' => [
            'href' => route('evaluations.show', ['user' => $evaluation->user_id]),
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
    ];
@endphp

<script id="svelte-evaluation-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-evaluation-form-root"></div>
@endsection
