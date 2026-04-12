@extends('layouts.app')

@section('title', 'Import User')
@section('page-title', 'Import User dari CSV')

@section('content')
@php
    $results = session('import_results', ['success' => [], 'errors' => []]);

    $props = [
        'title' => 'Import User dari CSV',
        'description' => 'Unggah data anggota secara massal menggunakan template resmi dan tinjau hasil impor langsung dari halaman ini.',
        'form' => [
            'action' => route('users.import.process'),
            'csrfToken' => csrf_token(),
            'templateUrl' => route('users.import.template'),
        ],
        'roles' => $roles->pluck('name')->values(),
        'departments' => $departments->pluck('name')->values(),
        'errors' => collect($errors->messages())->map(fn ($messages) => $messages[0])->all(),
        'results' => [
            'success' => array_values($results['success'] ?? []),
            'errors' => array_values($results['errors'] ?? []),
        ],
    ];
@endphp

<script id="svelte-user-import-props" type="application/json">{!! str_replace('</', '<\/', json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
<div id="svelte-user-import-root"></div>
@endsection
