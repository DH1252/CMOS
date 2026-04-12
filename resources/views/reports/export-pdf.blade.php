<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan CMOS</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.45;
            margin: 24px;
        }

        h1,
        h2 {
            margin: 0 0 8px;
            color: #111827;
        }

        p {
            margin: 0 0 12px;
        }

        .meta {
            margin-bottom: 24px;
            color: #4b5563;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .grid th,
        .grid td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }

        .grid th {
            background: #f3f4f6;
            font-weight: 700;
        }

        .section {
            margin-bottom: 24px;
        }

        .stats td:first-child,
        .stats td:nth-child(3) {
            width: 32%;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="section">
        <h1>Laporan CMOS</h1>
        <p class="meta">Diekspor pada {{ $generatedAt->format('d M Y H:i') }}</p>
    </div>

    <div class="section">
        <h2>Ringkasan</h2>
        <table class="grid stats">
            <tbody>
                <tr>
                    <td>Total anggota</td>
                    <td>{{ $payload['stats']['totalUsers'] }}</td>
                    <td>Total program</td>
                    <td>{{ $payload['stats']['totalPrograms'] }}</td>
                </tr>
                <tr>
                    <td>Total task</td>
                    <td>{{ $payload['stats']['totalTasks'] }}</td>
                    <td>Task selesai</td>
                    <td>{{ $payload['stats']['completedTasks'] }}</td>
                </tr>
                <tr>
                    <td>Rata-rata evaluasi</td>
                    <td>{{ $payload['averageEvaluationScore'] }}</td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Distribusi Task</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payload['tasksByStatus'] as $status => $value)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td>
                        <td>{{ $value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Distribusi Program</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payload['programsByStatus'] as $status => $value)
                    <tr>
                        <td>{{ ucfirst($status) }}</td>
                        <td>{{ $value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Progress Departemen</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Departemen</th>
                    <th>Anggota</th>
                    <th>Program</th>
                    <th>Task Selesai</th>
                    <th>Total Task</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payload['departments'] as $department)
                    <tr>
                        <td>{{ $department->name }}</td>
                        <td>{{ (int) ($department->users_count ?? 0) }}</td>
                        <td>{{ (int) ($department->programs_count ?? 0) }}</td>
                        <td>{{ (int) ($department->completed_tasks_count ?? 0) }}</td>
                        <td>{{ (int) ($department->tasks_count ?? 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Top Staff</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Departemen</th>
                    <th>Skor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payload['topStaff'] as $staff)
                    <tr>
                        <td>{{ $staff->name }}</td>
                        <td>{{ $staff->department?->name ?? '-' }}</td>
                        <td>{{ number_format(((float) ($staff->evaluations_avg_total_score ?? 0)) / 4, 1) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Belum ada data evaluasi staff.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
