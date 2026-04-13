@extends('layouts.app')

@section('title', 'Evaluasi Staff')
@section('page-title', 'Evaluasi Staff')
@section('page-meta', 'Daftar staff, evaluasi terakhir, dan form penilaian.')

@section('content')
<div class="card animate-fadeIn mb-4">
    <div class="card-body py-4">
        <div class="d-flex flex-column gap-4">
            <div>
                <div class="text-muted fs-sm">Ringkasan evaluasi</div>
                <h3 class="mb-2">Tinjau staf yang perlu dinilai terlebih dahulu</h3>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @foreach($gradeParams as $grade)
                    <div class="d-flex align-center gap-2 card px-3 py-2">
                        <span class="badge" style="background: {{ $grade->color }};">{{ $grade->grade }}</span>
                        <span class="fs-sm">{{ $grade->label }} ({{ $grade->min_score }}-{{ $grade->max_score }})</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="card animate-fadeIn">
    <div class="card-header">
        <div>
            <h3 class="card-title mb-1">Daftar staff dan evaluasi terakhir</h3>
        </div>
        <a href="{{ route('evaluations.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Beri evaluasi
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-container">
            <table class="table datatable">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Departemen</th>
                        <th>Periode terakhir</th>
                        <th>Nilai kabinet</th>
                        <th>Nilai BPH</th>
                        <th>Final score</th>
                        <th>Grade</th>
                        <th class="no-sort" style="width: 132px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffMembers as $staff)
                        <tr>
                            <td>
                                <div class="d-flex align-center gap-2">
                                    <img src="{{ $staff->avatar_url }}" alt="{{ $staff->name }}" class="avatar-sm" width="32" height="32" loading="lazy" decoding="async">
                                    <div>
                                        <div class="fw-semibold">{{ $staff->name }}</div>
                                        <div class="fs-sm text-muted">{{ $staff->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="fs-sm">{{ $staff->department?->name ?? '-' }}</td>
                            <td>{{ $staff->latest_period ?? '-' }}</td>
                            <td>
                                @if($staff->latest_evaluation)
                                    @if($staff->latest_evaluation['kabinet_score'] > 0)
                                        <span class="badge badge-info">{{ $staff->latest_evaluation['kabinet_score'] }}</span>
                                    @else
                                        <span class="badge badge-secondary">Pending</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($staff->latest_evaluation)
                                    @if($staff->latest_evaluation['bph_score'] > 0)
                                        <span class="badge badge-primary">{{ $staff->latest_evaluation['bph_score'] }}</span>
                                    @else
                                        <span class="badge badge-secondary">Pending</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($staff->latest_evaluation)
                                    <span class="fw-bold">{{ $staff->latest_evaluation['final_score'] }}</span>
                                    @if(!$staff->latest_evaluation['is_complete'])
                                        <span class="fs-sm text-warning">• Menunggu evaluasi lengkap</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($staff->latest_evaluation && $staff->latest_evaluation['grade'])
                                    <span class="badge" style="background: {{ $staff->latest_evaluation['grade']->color }};">
                                        {{ $staff->latest_evaluation['grade']->grade }} - {{ $staff->latest_evaluation['grade']->label }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('evaluations.show', $staff) }}" class="btn btn-sm btn-secondary" title="Buka detail">
                                        <i class="fas fa-eye"></i>
                                        Buka
                                    </a>
                                    <a href="{{ route('evaluations.create', ['user_id' => $staff->id]) }}" class="btn btn-sm btn-primary btn-icon" title="Beri evaluasi">
                                        <i class="fas fa-star"></i>
                                    </a>
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
