@extends('layouts.app')

@section('title', 'Task Saya')
@section('page-title', 'Task Saya')
@section('page-meta', 'Daftar task dan progres terbaru.')

@section('content')
@php
    $todoCount = $tasks->where('status', 'todo')->count();
    $progressCount = $tasks->where('status', 'in_progress')->count();
    $doneCount = $tasks->where('status', 'done')->count();
@endphp

<div class="card animate-fadeIn mb-4">
    <div class="card-body py-4">
        <div class="d-flex flex-column gap-4">
            <div>
                <div class="text-muted fs-sm">Ringkasan kerja</div>
                <h3 class="mb-2">Task yang sedang menjadi tanggung jawab Anda</h3>
            </div>

            <div class="d-flex flex-wrap gap-3">
                <div class="card" style="min-width: 10rem;">
                    <div class="card-body py-3">
                        <div class="text-muted fs-sm">Todo</div>
                        <div class="fs-2 fw-bold">{{ $todoCount }}</div>
                    </div>
                </div>
                <div class="card" style="min-width: 10rem;">
                    <div class="card-body py-3">
                        <div class="text-muted fs-sm">In Progress</div>
                        <div class="fs-2 fw-bold">{{ $progressCount }}</div>
                    </div>
                </div>
                <div class="card" style="min-width: 10rem;">
                    <div class="card-body py-3">
                        <div class="text-muted fs-sm">Selesai</div>
                        <div class="fs-2 fw-bold">{{ $doneCount }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card animate-fadeIn">
    <div class="card-header">
        <div>
            <h3 class="card-title mb-1">Daftar task</h3>
        </div>
        @if(auth()->user()->hasRole(['admin', 'bph', 'kabinet']))
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Tambah task
            </a>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>Judul task</th>
                        <th>Program</th>
                        @if(!auth()->user()->isStaff())
                            <th>Penanggung jawab</th>
                        @endif
                        <th>Status</th>
                        <th>Prioritas</th>
                        <th>Progress</th>
                        <th>Deadline</th>
                        <th class="no-sort" style="width: 132px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $task->title }}</div>
                                @if($task->description)
                                    <div class="fs-sm text-muted">{{ \\Illuminate\\Support\\Str::limit($task->description, 80) }}</div>
                                @endif
                            </td>
                            <td class="fs-sm">{{ $task->program->name ?? '-' }}</td>
                            @if(!auth()->user()->isStaff())
                                <td>
                                    @if($task->assignee)
                                        <div class="d-flex align-center gap-2">
                                            <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" class="avatar-sm" width="32" height="32" loading="lazy" decoding="async">
                                            <span class="fs-sm">{{ $task->assignee->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">Belum ditetapkan</span>
                                    @endif
                                </td>
                            @endif
                            <td>
                                <span class="badge badge-{{ $task->status_badge }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $task->priority_badge }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </td>
                            <td style="min-width: 120px;">
                                <div class="d-flex align-center gap-2">
                                    <div class="progress" style="flex: 1; height: 6px;">
                                        <div class="progress-bar {{ $task->progress >= 100 ? 'success' : '' }}" style="width: {{ $task->progress }}%;"></div>
                                    </div>
                                    <span class="fs-xs text-muted" style="width: 35px;">{{ $task->progress }}%</span>
                                </div>
                            </td>
                            <td class="fs-sm {{ $task->is_overdue ? 'text-danger fw-semibold' : '' }}">
                                {{ $task->deadline?->format('d M Y') ?? '-' }}
                                @if($task->is_overdue)
                                    <i class="fas fa-exclamation-circle"></i>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-secondary" title="Buka detail">
                                        <i class="fas fa-eye"></i>
                                        Buka
                                    </a>
                                    @if(auth()->user()->hasRole(['admin', 'bph', 'kabinet']))
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger btn-icon" data-confirm-delete="{{ $task->title }}" title="Hapus task">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
