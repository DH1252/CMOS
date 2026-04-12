@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Aplikasi')

@section('content')
@php
    $themeColors = [
        'purple' => '#7C3AED',
        'blue' => '#3B82F6',
        'green' => '#10B981',
        'red' => '#EF4444',
        'orange' => '#F59E0B',
        'pink' => '#EC4899',
        'indigo' => '#6366F1',
        'teal' => '#14B8A6',
        'cyan' => '#06B6D4',
        'rose' => '#F43F5E',
        'amber' => '#F59E0B',
        'slate' => '#64748B',
    ];

    $props = [
        'title' => 'Pengaturan Aplikasi',
        'description' => 'Atur identitas visual, nama sistem, dan ritme evaluasi organisasi.',
        'form' => [
            'action' => route('settings.update', 'general'),
            'csrfToken' => csrf_token(),
            'spoofMethod' => 'PUT',
        ],
        'values' => [
            'appName' => old('app_name', $settings['app_name']?->value ?? 'CMOS'),
            'organizationName' => old('organization_name', $settings['organization_name']?->value ?? 'HIMATEKKOM ITS'),
            'themeColor' => old('theme_color', $settings['theme_color']?->value ?? 'purple'),
            'evaluationPeriod' => old('evaluation_period', $settings['evaluation_period']?->value ?? 'quarterly'),
            'periodOptions' => [
                ['value' => 'monthly', 'label' => 'Bulanan'],
                ['value' => 'quarterly', 'label' => 'Per Kuartal'],
                ['value' => 'semester', 'label' => 'Per Semester'],
                ['value' => 'yearly', 'label' => 'Tahunan'],
            ],
        ],
        'colors' => collect($themeColors)->map(fn ($hex, $name) => [
            'name' => $name,
            'label' => ucfirst($name),
            'hex' => $hex,
        ])->values(),
        'errors' => collect($errors->messages())->map(fn ($messages) => $messages[0])->all(),
    ];
@endphp

<script id="svelte-settings-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-settings-root"></div>
@endsection
