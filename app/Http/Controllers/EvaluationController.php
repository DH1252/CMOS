<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\GradeParameter;
use App\Models\User;
use App\Services\PostHogService;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    /**
     * Landing page - Department cards + Best Staff ranking
     * BPH: See all departments
     * Kabinet: Redirect to their department
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', Evaluation::getCurrentMonth());

        // Kabinet: redirect to their department
        if ($user->isKabinet() && $user->department_id) {
            return redirect()->route('evaluations.department', [
                'department' => $user->department_id,
                'month' => $month,
            ]);
        }

        // BPH/Admin: show all departments + ranking
        $departments = Department::withCount(['users' => fn ($q) => $q->active()->byRole('staff')])
            ->orderBy('name')
            ->get()
            ->map(function ($dept) use ($month, $user) {
                // Count evaluated staff this month
                $staffIds = User::where('department_id', $dept->id)
                    ->byRole('staff')
                    ->active()
                    ->pluck('id');

                $evaluatedCount = Evaluation::whereIn('user_id', $staffIds)
                    ->byMonth($month)
                    ->byEvaluatorType($user->isBph() ? 'bph' : 'kabinet')
                    ->distinct('user_id')
                    ->count('user_id');

                $dept->evaluated_count = $evaluatedCount;

                return $dept;
            });

        // Get Best Staff of the Month ranking
        $ranking = Evaluation::getMonthlyRanking($month);
        $availableMonths = Evaluation::getAvailableMonths();

        return \Inertia\Inertia::render(
            'pages/EvaluationHubPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $monthLabel = $availableMonths[$month] ?? \App\Models\Evaluation::getMonthLabel($month);

                $props = [
                    'title' => 'Evaluasi Staff',
                    'description' => 'Pilih departemen untuk melakukan review staff dan pantau peringkat performa terbaik pada periode yang sama.',
                    'month' => [
                        'value' => $month,
                        'label' => $monthLabel,
                    ],
                    'months' => collect($availableMonths)->map(fn ($label, $value) => [
                        'value' => $value,
                        'label' => $label,
                    ])->values(),
                    'monthAction' => route('evaluations.index'),
                    'ranking' => collect($ranking)->map(function ($staff) {
                        $grade = data_get($staff, 'evaluation_data.grade');

                        return [
                            'name' => $staff['name'],
                            'avatar' => $staff['avatar_url'] ?? ('https://ui-avatars.com/api/?name='.urlencode($staff['name'])),
                            'department' => data_get($staff, 'department.name', '-'),
                            'score' => number_format($staff['evaluation_score'], 1),
                            'grade' => $grade ? [
                                'label' => $grade['grade'],
                                'color' => $grade['color'],
                            ] : null,
                        ];
                    })->values(),
                    'departments' => $departments->map(fn ($department) => [
                        'href' => route('evaluations.department', ['department' => $department, 'month' => $month]),
                        'name' => $department->name,
                        'description' => ($department->evaluated_count ?? 0).' dari '.($department->users_count ?? 0).' staff selesai dinilai',
                        'totalStaff' => $department->users_count ?? 0,
                        'evaluatedStaff' => $department->evaluated_count ?? 0,
                    ])->values(),
                    'emptyRanking' => [
                        'title' => 'Belum ada ranking',
                        'text' => 'Data peringkat akan muncul setelah evaluasi staff tersedia pada periode ini.',
                    ],
                    'emptyDepartments' => [
                        'title' => 'Belum ada departemen',
                        'text' => 'Departemen akan muncul di sini setelah struktur organisasi tersedia.',
                    ],
                ];

                return $props;
            })(compact('departments', 'ranking', 'month', 'availableMonths')),
        );
    }

    /**
     * Department staff list
     */
    public function department(Department $department, Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', Evaluation::getCurrentMonth());

        // Kabinet can only access their department
        if ($user->isKabinet() && $user->department_id !== $department->id) {
            abort(403, 'Anda tidak memiliki akses ke departemen ini');
        }

        // Get staff members with their evaluation status
        $staffMembers = User::where('department_id', $department->id)
            ->byRole('staff')
            ->active()
            ->with('department')
            ->orderBy('name')
            ->get()
            ->map(function ($staff) use ($month, $user) {
                $combined = Evaluation::getCombinedScore($staff->id, $month);
                $staff->evaluation_data = $combined;

                // Check if current user has evaluated this staff
                $evaluatorType = $user->isBph() ? 'bph' : 'kabinet';
                $staff->has_evaluated = Evaluation::where('user_id', $staff->id)
                    ->byMonth($month)
                    ->where('evaluator_type', $evaluatorType)
                    ->exists();

                return $staff;
            });

        $availableMonths = Evaluation::getAvailableMonths();

        return \Inertia\Inertia::render(
            'pages/EvaluationStaffPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $monthLabel = $availableMonths[$month] ?? \App\Models\Evaluation::getMonthLabel($month);

                $props = [
                    'title' => 'Staff '.$department->name,
                    'description' => 'Lacak status penilaian per staff, lihat detail histori, atau langsung isi evaluasi untuk periode '.$monthLabel.'.',
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

                return $props;
            })(compact('department', 'staffMembers', 'month', 'availableMonths')),
        );
    }

    /**
     * Create evaluation form
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $staffId = $request->get('user_id');
        $month = $request->get('month', Evaluation::getCurrentMonth());

        if (! $staffId) {
            return redirect()->route('evaluations.index')->with('error', 'Pilih staff terlebih dahulu');
        }

        $staff = User::with('department')->findOrFail($staffId);

        // Check if Kabinet can evaluate this staff
        if ($user->isKabinet() && $user->department_id !== $staff->department_id) {
            abort(403, 'Anda tidak dapat menilai staff dari departemen lain');
        }

        // Check if already evaluated
        $evaluatorType = $user->isBph() ? 'bph' : 'kabinet';
        $existingEval = Evaluation::where('user_id', $staffId)
            ->byMonth($month)
            ->where('evaluator_type', $evaluatorType)
            ->first();

        if ($existingEval) {
            return redirect()->route('evaluations.edit', $existingEval)
                ->with('info', 'Anda sudah menilai staff ini. Silakan edit penilaian.');
        }

        $gradeParams = GradeParameter::getAllGrades();
        $availableMonths = Evaluation::getAvailableMonths();

        return \Inertia\Inertia::render(
            'pages/EvaluationFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

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
                        'range' => $grade->min_score.' - '.$grade->max_score,
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
                    'errors' => collect(session('errors')?->messages() ?? [])->map(fn ($messages) => $messages[0])->all(),
                    'cancelAction' => [
                        'href' => route('evaluations.department', ['department' => $staff->department_id, 'month' => $month]),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                ];

                return $props;
            })(compact('staff', 'month', 'evaluatorType', 'gradeParams', 'availableMonths')),
        );
    }

    /**
     * Store evaluation
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $evaluatorType = $user->isBph() ? 'bph' : 'kabinet';

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'kehadiran' => 'required|integer|min:1|max:5',
            'kedisiplinan' => 'required|integer|min:1|max:5',
            'tanggung_jawab' => 'required|integer|min:1|max:5',
            'kerjasama' => 'required|integer|min:1|max:5',
            'inisiatif' => 'required|integer|min:1|max:5',
            'komunikasi' => 'required|integer|min:1|max:5',
            'notes' => 'nullable|string',
        ]);

        // Check if already evaluated
        $exists = Evaluation::where('user_id', $validated['user_id'])
            ->where('period', $validated['period'])
            ->where('evaluator_type', $evaluatorType)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Staff sudah dinilai oleh '.strtoupper($evaluatorType).' untuk bulan ini.');
        }

        $validated['evaluator_id'] = $user->id;
        $validated['evaluator_type'] = $evaluatorType;

        $evaluation = Evaluation::create($validated);

        $staff = User::find($validated['user_id']);
        ActivityLog::log('created', "Created {$evaluatorType} evaluation for: {$staff->name} ({$validated['period']})", $evaluation);

        app(PostHogService::class)->capture((string) $user->id, 'evaluation_submitted', [
            'evaluation_id' => $evaluation->id,
            'evaluator_type' => $evaluatorType,
            'staff_id' => $staff->id,
            'period' => $validated['period'],
            'total_score' => $evaluation->total_score,
        ]);

        return redirect()->route('evaluations.department', [
            'department' => $staff->department_id,
            'month' => $validated['period'],
        ])->with('success', 'Evaluasi berhasil ditambahkan!');
    }

    /**
     * Show staff evaluation history
     */
    public function show(User $user, Request $request)
    {
        $user->loadMissing('department');

        $evaluations = Evaluation::where('user_id', $user->id)
            ->with('evaluator')
            ->orderByDesc('period')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('period');

        $periodScores = [];
        foreach ($evaluations as $period => $evals) {
            $periodScores[$period] = Evaluation::getCombinedScore($user->id, $period);
        }

        $gradeParams = GradeParameter::getAllGrades();

        return \Inertia\Inertia::render(
            'pages/EvaluationHistoryPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

                $criteria = [
                    'kehadiran' => 'Kehadiran',
                    'kedisiplinan' => 'Kedisiplinan',
                    'tanggung_jawab' => 'Tanggung Jawab',
                    'kerjasama' => 'Kerjasama',
                    'inisiatif' => 'Inisiatif',
                    'komunikasi' => 'Komunikasi',
                ];

                $props = [
                    'title' => 'Riwayat Evaluasi',
                    'description' => 'Ringkasan nilai lintas periode, lengkap dengan detail penilaian dari Kabinet dan BPH.',
                    'profile' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar_url,
                        'badge' => [
                            'label' => $user->department?->name ?? 'No Department',
                            'tone' => 'info',
                        ],
                    ],
                    'createAction' => [
                        'href' => route('evaluations.create', ['user_id' => $user->id]),
                        'label' => 'Beri Evaluasi Baru',
                        'icon' => 'fas fa-star',
                    ],
                    'backAction' => [
                        'href' => route('evaluations.index'),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                    'gradeLegend' => $gradeParams->map(fn ($grade) => [
                        'letter' => $grade->grade,
                        'label' => $grade->label,
                        'range' => $grade->min_score.' - '.$grade->max_score,
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
                                'label' => $combined['grade']->grade.' · '.$combined['grade']->label,
                                'color' => $combined['grade']->color,
                            ] : null,
                            'summary' => $combined ? [
                                [
                                    'label' => 'Kabinet',
                                    'value' => $combined['has_kabinet'] ? number_format($combined['kabinet_score'], 1) : '-',
                                    'muted' => ! $combined['has_kabinet'],
                                ],
                                [
                                    'label' => 'BPH',
                                    'value' => $combined['has_bph'] ? number_format($combined['bph_score'], 1) : '-',
                                    'muted' => ! $combined['has_bph'],
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
                                    'byline' => 'oleh '.($eval->evaluator?->name ?? 'Unknown').' · '.$eval->created_at->format('d M Y'),
                                    'score' => number_format($eval->total_score, 1),
                                    'scoreColor' => $eval->grade_color,
                                    'editAction' => [
                                        'href' => route('evaluations.edit', $eval),
                                        'label' => 'Edit evaluasi',
                                        'icon' => 'fas fa-pen',
                                    ],
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
                        'text' => 'Staff ini belum memiliki evaluasi untuk ditinjau.',
                    ],
                ];

                return $props;
            })(compact('user', 'evaluations', 'periodScores', 'gradeParams')),
        );
    }

    /**
     * Edit evaluation
     */
    public function edit(Evaluation $evaluation)
    {
        $evaluation->loadMissing(['user.department']);

        $gradeParams = GradeParameter::getAllGrades();
        $availableMonths = Evaluation::getAvailableMonths();

        return \Inertia\Inertia::render(
            'pages/EvaluationFormPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

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
                        'name' => $evaluation->user?->name ?? '-',
                        'email' => $evaluation->user?->email,
                        'department' => $evaluation->user?->department?->name ?? '-',
                        'avatar' => $evaluation->user?->avatar_url,
                    ],
                    'evaluatorType' => strtoupper($evaluation->evaluator_type),
                    'periodLabel' => \App\Models\Evaluation::getMonthLabel($evaluation->period),
                    'gradeLegend' => $gradeParams->map(fn ($grade) => [
                        'letter' => $grade->grade,
                        'label' => $grade->label,
                        'range' => $grade->min_score.' - '.$grade->max_score,
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
                    'errors' => collect(session('errors')?->messages() ?? [])->map(fn ($messages) => $messages[0])->all(),
                    'cancelAction' => [
                        'href' => route('evaluations.show', ['user' => $evaluation->user_id]),
                        'label' => 'Kembali',
                        'icon' => 'fas fa-arrow-left',
                    ],
                ];

                return $props;
            })(compact('evaluation', 'gradeParams', 'availableMonths')),
        );
    }

    /**
     * Update evaluation
     */
    public function update(Request $request, Evaluation $evaluation)
    {
        $validated = $request->validate([
            'kehadiran' => 'required|integer|min:1|max:5',
            'kedisiplinan' => 'required|integer|min:1|max:5',
            'tanggung_jawab' => 'required|integer|min:1|max:5',
            'kerjasama' => 'required|integer|min:1|max:5',
            'inisiatif' => 'required|integer|min:1|max:5',
            'komunikasi' => 'required|integer|min:1|max:5',
            'notes' => 'nullable|string',
        ]);

        $evaluation->update($validated);

        ActivityLog::log('updated', 'Updated evaluation for: '.($evaluation->user?->name ?? 'Unknown'), $evaluation);

        app(PostHogService::class)->capture((string) auth()->id(), 'evaluation_updated', [
            'evaluation_id' => $evaluation->id,
            'evaluator_type' => $evaluation->evaluator_type,
            'staff_id' => $evaluation->user_id,
            'period' => $evaluation->period,
            'total_score' => $evaluation->total_score,
        ]);

        return redirect()->route('evaluations.department', [
            'department' => $evaluation->user->department_id,
            'month' => $evaluation->period,
        ])->with('success', 'Evaluasi berhasil diupdate!');
    }

    /**
     * Delete evaluation
     */
    public function destroy(Evaluation $evaluation)
    {
        $userName = $evaluation->user?->name ?? 'Unknown';
        $deptId = $evaluation->user?->department_id;
        $month = $evaluation->period;

        ActivityLog::log('deleted', "Deleted evaluation for: {$userName}", $evaluation);

        $evaluation->delete();

        return redirect()->route('evaluations.department', [
            'department' => $deptId,
            'month' => $month,
        ])->with('success', "Evaluasi untuk {$userName} berhasil dihapus!");
    }

    /**
     * Staff view their own evaluations
     */
    public function myEvaluations()
    {
        $user = auth()->user();

        $evaluations = Evaluation::where('user_id', $user->id)
            ->with('evaluator')
            ->orderByDesc('period')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('period');

        $periodScores = [];
        foreach ($evaluations as $period => $evals) {
            $periodScores[$period] = Evaluation::getCombinedScore($user->id, $period);
        }

        $gradeParams = GradeParameter::getAllGrades();

        return \Inertia\Inertia::render(
            'pages/EvaluationHistoryPage',
            (static function (array $__viewData): array {
                extract($__viewData, EXTR_SKIP);

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
                        'range' => $grade->min_score.' - '.$grade->max_score,
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
                                'label' => $combined['grade']->grade.' · '.$combined['grade']->label,
                                'color' => $combined['grade']->color,
                            ] : null,
                            'summary' => $combined ? [
                                [
                                    'label' => 'Kabinet',
                                    'value' => $combined['has_kabinet'] ? number_format($combined['kabinet_score'], 1) : 'Pending',
                                    'muted' => ! $combined['has_kabinet'],
                                ],
                                [
                                    'label' => 'BPH',
                                    'value' => $combined['has_bph'] ? number_format($combined['bph_score'], 1) : 'Pending',
                                    'muted' => ! $combined['has_bph'],
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

                return $props;
            })(compact('evaluations', 'periodScores', 'gradeParams')),
        );
    }

    /**
     * Get ranking API (for AJAX)
     */
    public function ranking(Request $request)
    {
        $month = $request->get('month', Evaluation::getCurrentMonth());
        $departmentId = $request->get('department_id');

        $ranking = Evaluation::getMonthlyRanking($month, $departmentId);

        return response()->json([
            'success' => true,
            'ranking' => $ranking,
            'month' => $month,
            'month_label' => Evaluation::getMonthLabel($month),
        ]);
    }
}
