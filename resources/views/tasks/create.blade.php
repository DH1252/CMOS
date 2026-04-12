@extends('layouts.app')

@section('title', 'Tambah Task')
@section('page-title', 'Tambah Task')

@section('content')
@php
    $typeLocked = filled($typeId);
    $contextName = null;

    if ($type === 'program' && $typeId) {
        $contextName = $programs->firstWhere('id', (int) $typeId)?->name;
    } elseif ($type === 'department' && $typeId) {
        $contextName = $departments->firstWhere('id', (int) $typeId)?->name;
    }

    $description = match ($type) {
        'global' => 'Buat tugas lintas departemen untuk koordinasi organisasi.',
        'department' => $contextName ? "Susun penugasan baru untuk {$contextName}." : 'Buat tugas pada level departemen.',
        default => $contextName ? "Susun penugasan baru untuk program {$contextName}." : 'Buat tugas baru untuk program kerja.',
    };

    $cancelHref = match ($type) {
        'global' => route('tasks.global'),
        'department' => $typeId ? route('tasks.department.tasks', $typeId) : route('tasks.index'),
        default => $typeId ? route('tasks.program', $typeId) : route('tasks.index'),
    };

    $props = [
        'title' => 'Form Tambah Task',
        'description' => $description,
        'form' => [
            'action' => route('tasks.store'),
            'method' => 'POST',
            'csrfToken' => csrf_token(),
        ],
        'taskType' => $type,
        'typeLocked' => $typeLocked,
        'typeId' => $typeId,
        'values' => [
            'title' => old('title'),
            'description' => old('description'),
            'program_id' => old('program_id', $type === 'program' ? $typeId : null),
            'department_id' => old('department_id', $type === 'department' ? $typeId : null),
            'assigned_to' => old('assigned_to'),
            'priority' => old('priority', 'medium'),
            'deadline' => old('deadline'),
        ],
        'users' => $users->map(fn ($user) => [
            'value' => $user->id,
            'label' => $user->name . ' (' . ucfirst($user->role?->name ?? 'user') . ')',
        ])->values(),
        'programs' => $programs->map(fn ($program) => [
            'value' => $program->id,
            'label' => $program->name . ' (' . ($program->department?->name ?? 'Tanpa departemen') . ')',
        ])->values(),
        'departments' => $departments->map(fn ($department) => [
            'value' => $department->id,
            'label' => $department->name,
        ])->values(),
        'errors' => collect($errors->messages())->map(fn ($messages) => $messages[0])->all(),
        'cancelAction' => [
            'href' => $cancelHref,
            'label' => 'Kembali',
            'icon' => 'fas fa-arrow-left',
        ],
    ];
@endphp

<script id="svelte-task-form-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-task-form-root"></div>
@endsection
