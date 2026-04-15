<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\AnnouncementReaction;
use App\Models\Department;
use App\Models\DriveAccount;
use App\Models\Evaluation;
use App\Models\InformationBoard;
use App\Models\InformationCategory;
use App\Models\Message;
use App\Models\Notification;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Program;
use App\Models\Role;
use App\Models\Task;
use App\Models\Timeline;
use App\Models\UsefulLink;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()->get()->keyBy('name');
        $departments = Department::query()->get()->keyBy('name');

        $users = $this->seedUsers($roles->all(), $departments->all());
        $programs = $this->seedPrograms($users, $departments->all());

        $this->seedTasks($users, $departments->all(), $programs);
        $this->seedTimelines($departments->all(), $programs);
        $this->seedDriveAccounts($departments->all());
        $this->seedUsefulLinks($users['admin']);
        $this->seedAnnouncements($users);
        $this->seedMessages($users);
        $this->seedEvaluations($users);
        $this->seedInformationBoards($users);
        $this->seedOperationalNotifications($users, $programs);
        $this->seedActivityLogs($users, $programs);
    }

    /**
     * @param  array<string, Role>  $roles
     * @param  array<string, Department>  $departments
     * @return array<string, User>
     */
    private function seedUsers(array $roles, array $departments): array
    {
        return [
            'admin' => $this->upsertUser('admin@savana.test', [
                'name' => 'Administrator',
                'password' => 'password',
                'role_id' => $roles['admin']->id,
                'department_id' => null,
                'status' => 'active',
            ]),
            'bph' => $this->upsertUser('bph@savana.test', [
                'name' => 'Ketua Umum',
                'password' => 'password',
                'role_id' => $roles['bph']->id,
                'department_id' => null,
                'status' => 'active',
            ]),
            'psdm_head' => $this->upsertUser('kabinet.psdm@savana.test', [
                'name' => 'Kepala PSDM',
                'password' => 'password',
                'role_id' => $roles['kabinet']->id,
                'department_id' => $departments['PSDM']->id,
                'status' => 'active',
            ]),
            'medinfo_head' => $this->upsertUser('kabinet.medinfo@savana.test', [
                'name' => 'Kepala Medinfo',
                'password' => 'password',
                'role_id' => $roles['kabinet']->id,
                'department_id' => $departments['Medinfo']->id,
                'status' => 'active',
            ]),
            'humas_head' => $this->upsertUser('kabinet.humas@savana.test', [
                'name' => 'Kepala Humas',
                'password' => 'password',
                'role_id' => $roles['kabinet']->id,
                'department_id' => $departments['Humas']->id,
                'status' => 'active',
            ]),
            'ristek_head' => $this->upsertUser('kabinet.ristek@savana.test', [
                'name' => 'Kepala Ristek',
                'password' => 'password',
                'role_id' => $roles['kabinet']->id,
                'department_id' => $departments['Ristek']->id,
                'status' => 'active',
            ]),
            'akademik_head' => $this->upsertUser('kabinet.akademik@savana.test', [
                'name' => 'Kepala Akademik',
                'password' => 'password',
                'role_id' => $roles['kabinet']->id,
                'department_id' => $departments['Akademik']->id,
                'status' => 'active',
            ]),
            'psdm_staff_1' => $this->upsertUser('staff1@savana.test', [
                'name' => 'Staff PSDM 1',
                'password' => 'password',
                'role_id' => $roles['staff']->id,
                'department_id' => $departments['PSDM']->id,
                'status' => 'active',
            ]),
            'psdm_staff_2' => $this->upsertUser('staff.psdm2@savana.test', [
                'name' => 'Staff PSDM 2',
                'password' => 'password',
                'role_id' => $roles['staff']->id,
                'department_id' => $departments['PSDM']->id,
                'status' => 'active',
            ]),
            'medinfo_staff_1' => $this->upsertUser('staff2@savana.test', [
                'name' => 'Staff Medinfo 1',
                'password' => 'password',
                'role_id' => $roles['staff']->id,
                'department_id' => $departments['Medinfo']->id,
                'status' => 'active',
            ]),
            'medinfo_staff_2' => $this->upsertUser('staff.medinfo2@savana.test', [
                'name' => 'Staff Medinfo 2',
                'password' => 'password',
                'role_id' => $roles['staff']->id,
                'department_id' => $departments['Medinfo']->id,
                'status' => 'active',
            ]),
            'humas_staff_1' => $this->upsertUser('staff.humas1@savana.test', [
                'name' => 'Staff Humas 1',
                'password' => 'password',
                'role_id' => $roles['staff']->id,
                'department_id' => $departments['Humas']->id,
                'status' => 'active',
            ]),
            'humas_staff_2' => $this->upsertUser('staff.humas2@savana.test', [
                'name' => 'Staff Humas 2',
                'password' => 'password',
                'role_id' => $roles['staff']->id,
                'department_id' => $departments['Humas']->id,
                'status' => 'active',
            ]),
            'ristek_staff_1' => $this->upsertUser('staff.ristek1@savana.test', [
                'name' => 'Staff Ristek 1',
                'password' => 'password',
                'role_id' => $roles['staff']->id,
                'department_id' => $departments['Ristek']->id,
                'status' => 'active',
            ]),
            'ristek_staff_2' => $this->upsertUser('staff.ristek2@savana.test', [
                'name' => 'Staff Ristek 2',
                'password' => 'password',
                'role_id' => $roles['staff']->id,
                'department_id' => $departments['Ristek']->id,
                'status' => 'active',
            ]),
            'akademik_staff_1' => $this->upsertUser('staff.akademik1@savana.test', [
                'name' => 'Staff Akademik 1',
                'password' => 'password',
                'role_id' => $roles['staff']->id,
                'department_id' => $departments['Akademik']->id,
                'status' => 'active',
            ]),
            'akademik_staff_2' => $this->upsertUser('staff.akademik2@savana.test', [
                'name' => 'Staff Akademik 2',
                'password' => 'password',
                'role_id' => $roles['staff']->id,
                'department_id' => $departments['Akademik']->id,
                'status' => 'active',
            ]),
            'inactive_staff' => $this->upsertUser('staff.inactive@savana.test', [
                'name' => 'Staff Alumni',
                'password' => 'password',
                'role_id' => $roles['staff']->id,
                'department_id' => $departments['Medinfo']->id,
                'status' => 'inactive',
            ]),
        ];
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Department>  $departments
     * @return array<string, Program>
     */
    private function seedPrograms(array $users, array $departments): array
    {
        $programs = [];

        $programs['psdm_bootcamp'] = $this->upsertProgram(
            'Bootcamp Staff Baru',
            $departments['PSDM'],
            [
                'description' => 'Program onboarding staff baru dengan mentoring, evaluasi mingguan, dan simulasi kerja departemen.',
                'created_by' => $users['psdm_head']->id,
                'start_date' => now()->subWeeks(2)->toDateString(),
                'end_date' => now()->addWeeks(4)->toDateString(),
                'status' => 'active',
            ],
            [
                $users['psdm_head']->id => ['role' => 'leader'],
                $users['psdm_staff_1']->id => ['role' => 'member'],
                $users['psdm_staff_2']->id => ['role' => 'member'],
            ],
            [$users['psdm_head']->id, $users['psdm_staff_1']->id]
        );

        $programs['medinfo_campaign'] = $this->upsertProgram(
            'Campaign Media Ramadhan',
            $departments['Medinfo'],
            [
                'description' => 'Konten campaign lintas kanal untuk publikasi agenda organisasi dan rekap pencapaian semester.',
                'created_by' => $users['medinfo_head']->id,
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->addWeeks(3)->toDateString(),
                'status' => 'active',
            ],
            [
                $users['medinfo_head']->id => ['role' => 'leader'],
                $users['medinfo_staff_1']->id => ['role' => 'member'],
                $users['medinfo_staff_2']->id => ['role' => 'member'],
                $users['ristek_staff_1']->id => ['role' => 'member'],
            ],
            [$users['medinfo_head']->id]
        );

        $programs['humas_visit'] = $this->upsertProgram(
            'Campus Relation Visit',
            $departments['Humas'],
            [
                'description' => 'Roadshow ke himpunan mitra untuk penjajakan kolaborasi acara dan sponsorship.',
                'created_by' => $users['humas_head']->id,
                'start_date' => now()->addWeek()->toDateString(),
                'end_date' => now()->addWeeks(6)->toDateString(),
                'status' => 'planning',
            ],
            [
                $users['humas_head']->id => ['role' => 'leader'],
                $users['humas_staff_1']->id => ['role' => 'member'],
                $users['humas_staff_2']->id => ['role' => 'member'],
            ],
            [$users['humas_head']->id]
        );

        $programs['ristek_dashboard'] = $this->upsertProgram(
            'Internal Dashboard CMOS',
            $departments['Ristek'],
            [
                'description' => 'Pengembangan dashboard operasional untuk memantau task, evaluasi, dan agenda lintas departemen.',
                'created_by' => $users['ristek_head']->id,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'status' => 'active',
            ],
            [
                $users['ristek_head']->id => ['role' => 'leader'],
                $users['ristek_staff_1']->id => ['role' => 'member'],
                $users['ristek_staff_2']->id => ['role' => 'member'],
                $users['medinfo_staff_2']->id => ['role' => 'member'],
            ],
            [$users['ristek_head']->id, $users['ristek_staff_1']->id]
        );

        $programs['akademik_clinic'] = $this->upsertProgram(
            'Klinik Akademik UTS',
            $departments['Akademik'],
            [
                'description' => 'Pendampingan akademik menjelang UTS melalui sesi konsultasi, bank soal, dan review materi.',
                'created_by' => $users['akademik_head']->id,
                'start_date' => now()->subWeeks(6)->toDateString(),
                'end_date' => now()->subWeek()->toDateString(),
                'status' => 'completed',
            ],
            [
                $users['akademik_head']->id => ['role' => 'leader'],
                $users['akademik_staff_1']->id => ['role' => 'member'],
                $users['akademik_staff_2']->id => ['role' => 'member'],
                $users['psdm_staff_2']->id => ['role' => 'member'],
            ],
            [$users['akademik_head']->id]
        );

        return $programs;
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Department>  $departments
     * @param  array<string, Program>  $programs
     */
    private function seedTasks(array $users, array $departments, array $programs): void
    {
        $tasks = [
            [
                'title' => 'Susun modul orientasi staff',
                'description' => 'Finalisasi modul onboarding dan materi mentoring untuk bootcamp staff baru.',
                'program_id' => $programs['psdm_bootcamp']->id,
                'department_id' => $departments['PSDM']->id,
                'assigned_to' => $users['psdm_staff_1']->id,
                'created_by' => $users['psdm_head']->id,
                'status' => 'done',
                'sort_order' => 1,
                'progress' => 100,
                'priority' => 'high',
                'deadline' => now()->subDays(10)->toDateString(),
                'is_global' => false,
            ],
            [
                'title' => 'Buat jadwal mentoring mingguan',
                'description' => 'Koordinasikan mentor, room, dan PIC tiap sesi selama satu bulan.',
                'program_id' => $programs['psdm_bootcamp']->id,
                'department_id' => $departments['PSDM']->id,
                'assigned_to' => $users['psdm_staff_2']->id,
                'created_by' => $users['psdm_head']->id,
                'status' => 'in_progress',
                'sort_order' => 2,
                'progress' => 65,
                'priority' => 'high',
                'deadline' => now()->addDays(5)->toDateString(),
                'is_global' => false,
            ],
            [
                'title' => 'Review kalender konten mingguan',
                'description' => 'Pastikan distribusi topik feed, reels, dan story seimbang untuk campaign utama.',
                'program_id' => $programs['medinfo_campaign']->id,
                'department_id' => $departments['Medinfo']->id,
                'assigned_to' => $users['medinfo_staff_1']->id,
                'created_by' => $users['medinfo_head']->id,
                'status' => 'pending',
                'sort_order' => 3,
                'progress' => 80,
                'priority' => 'medium',
                'deadline' => now()->addDays(2)->toDateString(),
                'is_global' => false,
            ],
            [
                'title' => 'Optimasi landing page publikasi',
                'description' => 'Refactor hero section dan performa asset untuk landing page campaign.',
                'program_id' => $programs['ristek_dashboard']->id,
                'department_id' => $departments['Ristek']->id,
                'assigned_to' => $users['ristek_staff_1']->id,
                'created_by' => $users['ristek_head']->id,
                'status' => 'in_progress',
                'sort_order' => 4,
                'progress' => 55,
                'priority' => 'high',
                'deadline' => now()->addWeek()->toDateString(),
                'is_global' => false,
            ],
            [
                'title' => 'Siapkan proposal mitra kampus',
                'description' => 'Lengkapi deck kolaborasi dan kebutuhan sponsorship untuk roadshow.',
                'program_id' => $programs['humas_visit']->id,
                'department_id' => $departments['Humas']->id,
                'assigned_to' => $users['humas_staff_1']->id,
                'created_by' => $users['humas_head']->id,
                'status' => 'todo',
                'sort_order' => 5,
                'progress' => 0,
                'priority' => 'medium',
                'deadline' => now()->addWeeks(2)->toDateString(),
                'is_global' => false,
            ],
            [
                'title' => 'Rekap bank soal UTS',
                'description' => 'Dokumentasikan hasil klinik akademik dan finalisasi arsip materi.',
                'program_id' => $programs['akademik_clinic']->id,
                'department_id' => $departments['Akademik']->id,
                'assigned_to' => $users['akademik_staff_2']->id,
                'created_by' => $users['akademik_head']->id,
                'status' => 'done',
                'sort_order' => 6,
                'progress' => 100,
                'priority' => 'low',
                'deadline' => now()->subWeeks(2)->toDateString(),
                'is_global' => false,
            ],
            [
                'title' => 'Sinkronisasi agenda kabinet bulanan',
                'description' => 'Konsolidasikan milestone tiap departemen untuk rapat BPH dan kabinet.',
                'program_id' => null,
                'department_id' => null,
                'assigned_to' => $users['bph']->id,
                'created_by' => $users['admin']->id,
                'status' => 'in_progress',
                'sort_order' => 7,
                'progress' => 40,
                'priority' => 'high',
                'deadline' => now()->addDays(3)->toDateString(),
                'is_global' => true,
            ],
            [
                'title' => 'Audit inventaris desain publikasi',
                'description' => 'Rapikan aset desain, template poster, dan folder arsip semester.',
                'program_id' => null,
                'department_id' => $departments['Medinfo']->id,
                'assigned_to' => $users['medinfo_staff_2']->id,
                'created_by' => $users['medinfo_head']->id,
                'status' => 'todo',
                'sort_order' => 8,
                'progress' => 0,
                'priority' => 'medium',
                'deadline' => now()->addDays(8)->toDateString(),
                'is_global' => false,
            ],
        ];

        foreach ($tasks as $task) {
            $this->upsertTask($task);
        }
    }

    /**
     * @param  array<string, Department>  $departments
     * @param  array<string, Program>  $programs
     */
    private function seedTimelines(array $departments, array $programs): void
    {
        foreach ([
            [
                'title' => 'Rapat Besar Bulanan',
                'description' => 'Forum evaluasi dan sinkronisasi target seluruh departemen.',
                'type' => 'global',
                'department_id' => null,
                'program_id' => null,
                'start_date' => now()->addDays(4)->toDateString(),
                'end_date' => now()->addDays(4)->toDateString(),
                'color' => '#2563EB',
            ],
            [
                'title' => 'Mentoring Staff PSDM Batch 2',
                'description' => 'Sesi pendampingan progres onboarding dan pembagian evaluasi pekanan.',
                'type' => 'department',
                'department_id' => $departments['PSDM']->id,
                'program_id' => null,
                'start_date' => now()->addDays(6)->toDateString(),
                'end_date' => now()->addDays(6)->toDateString(),
                'color' => '#10B981',
            ],
            [
                'title' => 'Review Sprint Dashboard',
                'description' => 'Demo progres pengembangan dashboard internal dan penentuan next sprint.',
                'type' => 'program',
                'department_id' => $departments['Ristek']->id,
                'program_id' => $programs['ristek_dashboard']->id,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'color' => '#F59E0B',
            ],
            [
                'title' => 'Roadshow Mitra Gelombang 1',
                'description' => 'Kunjungan ke mitra eksternal untuk membuka peluang kolaborasi acara besar.',
                'type' => 'program',
                'department_id' => $departments['Humas']->id,
                'program_id' => $programs['humas_visit']->id,
                'start_date' => now()->addWeeks(2)->toDateString(),
                'end_date' => now()->addWeeks(2)->addDay()->toDateString(),
                'color' => '#EC4899',
            ],
        ] as $timeline) {
            Timeline::updateOrCreate(
                ['title' => $timeline['title'], 'type' => $timeline['type']],
                $timeline
            );
        }
    }

    /**
     * @param  array<string, Department>  $departments
     */
    private function seedDriveAccounts(array $departments): void
    {
        foreach ([
            [
                'department' => 'PSDM',
                'name' => 'Drive PSDM',
                'email' => 'drive.psdm@savana.test',
                'password' => 'psdm-drive-2026',
                'drive_url' => 'https://drive.google.com/drive/folders/psdm-shared-drive',
            ],
            [
                'department' => 'Medinfo',
                'name' => 'Drive Medinfo',
                'email' => 'drive.medinfo@savana.test',
                'password' => 'medinfo-drive-2026',
                'drive_url' => 'https://drive.google.com/drive/folders/medinfo-shared-drive',
            ],
            [
                'department' => 'Humas',
                'name' => 'Drive Humas',
                'email' => 'drive.humas@savana.test',
                'password' => 'humas-drive-2026',
                'drive_url' => 'https://drive.google.com/drive/folders/humas-shared-drive',
            ],
            [
                'department' => 'Ristek',
                'name' => 'Drive Ristek',
                'email' => 'drive.ristek@savana.test',
                'password' => 'ristek-drive-2026',
                'drive_url' => 'https://drive.google.com/drive/folders/ristek-shared-drive',
            ],
            [
                'department' => 'Akademik',
                'name' => 'Drive Akademik',
                'email' => 'drive.akademik@savana.test',
                'password' => 'akademik-drive-2026',
                'drive_url' => 'https://drive.google.com/drive/folders/akademik-shared-drive',
            ],
        ] as $account) {
            DriveAccount::updateOrCreate(
                ['email' => $account['email']],
                [
                    'department_id' => $departments[$account['department']]->id,
                    'name' => $account['name'],
                    'password' => $account['password'],
                    'drive_url' => $account['drive_url'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedUsefulLinks(User $admin): void
    {
        foreach ([
            [
                'title' => 'Notion Master Tracker',
                'description' => 'Tracker terpusat untuk milestone kabinet dan status follow up mingguan.',
                'url' => 'https://notion.so/himatekkom/master-tracker',
                'icon' => 'fas fa-chart-line',
                'category' => 'tracker',
                'sort_order' => 1,
            ],
            [
                'title' => 'Template LPJ Kegiatan',
                'description' => 'Template laporan pertanggungjawaban untuk seluruh program kerja.',
                'url' => 'https://docs.google.com/document/d/template-lpj',
                'icon' => 'fas fa-file-alt',
                'category' => 'template',
                'sort_order' => 2,
            ],
            [
                'title' => 'Form Peminjaman Inventaris',
                'description' => 'Form standar untuk peminjaman alat, ruangan, dan perlengkapan acara.',
                'url' => 'https://forms.gle/peminjaman-inventaris',
                'icon' => 'fas fa-clipboard-list',
                'category' => 'form',
                'sort_order' => 3,
            ],
            [
                'title' => 'Peraturan Internal Organisasi',
                'description' => 'Ringkasan aturan kerja, etika koordinasi, dan SOP publikasi.',
                'url' => 'https://drive.google.com/file/d/peraturan-internal',
                'icon' => 'fas fa-gavel',
                'category' => 'rules',
                'sort_order' => 4,
            ],
            [
                'title' => 'Bank Desain Publikasi',
                'description' => 'Referensi aset desain, icon, dan guideline visual organisasi.',
                'url' => 'https://www.figma.com/file/himatekkom/design-bank',
                'icon' => 'fas fa-folder-open',
                'category' => 'resource',
                'sort_order' => 5,
            ],
            [
                'title' => 'Portal Dokumen Umum',
                'description' => 'Kumpulan dokumen umum yang dibutuhkan semua departemen.',
                'url' => 'https://drive.google.com/drive/folders/general-portal',
                'icon' => 'fas fa-link',
                'category' => 'general',
                'sort_order' => 6,
            ],
        ] as $link) {
            UsefulLink::updateOrCreate(
                ['title' => $link['title']],
                [
                    'description' => $link['description'],
                    'url' => $link['url'],
                    'icon' => $link['icon'],
                    'category' => $link['category'],
                    'created_by' => $admin->id,
                    'is_active' => true,
                    'sort_order' => $link['sort_order'],
                ]
            );
        }
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedAnnouncements(array $users): void
    {
        $announcement = Announcement::updateOrCreate(
            [
                'user_id' => $users['bph']->id,
                'content' => 'Reminder: rapat besar kabinet dipindah ke hari Jumat pukul 19.30. Pastikan setiap departemen membawa update target mingguan dan blocker utama.',
            ],
            ['has_poll' => false, 'poll_question' => null, 'poll_ends_at' => null]
        );

        AnnouncementComment::firstOrCreate([
            'announcement_id' => $announcement->id,
            'user_id' => $users['medinfo_head']->id,
            'content' => 'Siap, Medinfo akan bawa rekap campaign dan kebutuhan approval konten.',
        ]);

        AnnouncementReaction::updateOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => $users['psdm_head']->id],
            ['type' => 'like']
        );

        AnnouncementReaction::updateOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => $users['ristek_staff_1']->id],
            ['type' => 'wow']
        );

        $pollAnnouncement = Announcement::updateOrCreate(
            [
                'user_id' => $users['admin']->id,
                'content' => 'Tentukan format gathering internal bulan ini agar tim konsumsi dan acara bisa segera finalisasi kebutuhan.',
            ],
            [
                'has_poll' => true,
                'poll_question' => 'Format gathering internal yang paling cocok?',
                'poll_ends_at' => now()->addDays(5),
            ]
        );

        $pollOptions = [
            'Offline di kampus',
            'Hybrid + game night',
            'Online santai malam hari',
        ];

        $votes = [
            'Offline di kampus' => [$users['psdm_staff_1']->id, $users['humas_staff_1']->id, $users['akademik_staff_2']->id],
            'Hybrid + game night' => [$users['medinfo_staff_1']->id, $users['ristek_staff_1']->id, $users['psdm_head']->id],
            'Online santai malam hari' => [$users['humas_head']->id],
        ];

        foreach ($pollOptions as $optionText) {
            $option = PollOption::updateOrCreate(
                ['announcement_id' => $pollAnnouncement->id, 'option_text' => $optionText],
                ['votes_count' => 0]
            );

            foreach ($votes[$optionText] as $userId) {
                PollVote::firstOrCreate([
                    'poll_option_id' => $option->id,
                    'user_id' => $userId,
                ]);
            }

            $option->update([
                'votes_count' => $option->votes()->count(),
            ]);
        }

        AnnouncementComment::firstOrCreate([
            'announcement_id' => $pollAnnouncement->id,
            'user_id' => $users['akademik_head']->id,
            'content' => 'Kalau hybrid dipilih, Akademik bisa bantu susun sesi ice breaking dan rundown belajar santai.',
        ]);

        foreach ([
            $users['admin']->id => 'love',
            $users['medinfo_staff_1']->id => 'haha',
            $users['ristek_head']->id => 'like',
        ] as $userId => $type) {
            AnnouncementReaction::updateOrCreate(
                ['announcement_id' => $pollAnnouncement->id, 'user_id' => $userId],
                ['type' => $type]
            );
        }
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedMessages(array $users): void
    {
        foreach ([
            [$users['psdm_staff_1']->id, $users['psdm_head']->id, 'Kak, draft modul onboarding sudah aku update di drive.'],
            [$users['psdm_head']->id, $users['psdm_staff_1']->id, 'Siap, nanti malam aku review dan kasih catatan.'],
            [$users['medinfo_staff_2']->id, $users['ristek_staff_1']->id, 'Boleh bantu cek integrasi asset baru ke landing page?'],
            [$users['ristek_staff_1']->id, $users['medinfo_staff_2']->id, 'Boleh, kirim link figma dan daftar issue yang perlu dibereskan.'],
            [$users['humas_staff_1']->id, $users['bph']->id, 'Untuk kunjungan mitra, apakah perlu surat pengantar resmi minggu ini?'],
            [$users['bph']->id, $users['humas_staff_1']->id, 'Iya, siapkan draftnya dulu. Aku approve sebelum Jumat siang.'],
            [$users['humas_head']->id, $users['humas_staff_2']->id, 'Cek notifikasi deadline task untuk follow-up proposal mitra hari ini.'],
            [$users['ristek_head']->id, $users['ristek_staff_2']->id, 'Pastikan notifikasi evaluasi terbaru sudah dibaca sebelum standup.'],
        ] as [$senderId, $receiverId, $content]) {
            Message::firstOrCreate([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'content' => $content,
            ], [
                'is_read' => true,
            ]);
        }
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Program>  $programs
     */
    private function seedOperationalNotifications(array $users, array $programs): void
    {
        foreach ([
            [
                'user_id' => $users['medinfo_staff_2']->id,
                'type' => Notification::TYPE_TASK_ASSIGNED,
                'title' => 'Task Baru',
                'message' => 'Kamu ditugaskan untuk: Audit inventaris desain publikasi',
                'data' => ['program_id' => $programs['medinfo_campaign']->id, 'source' => 'seed'],
                'read_at' => null,
            ],
            [
                'user_id' => $users['ristek_staff_1']->id,
                'type' => Notification::TYPE_DEADLINE_REMINDER,
                'title' => 'Deadline Mendekat',
                'message' => 'Task \'Optimasi landing page publikasi\' jatuh tempo dalam 7 hari',
                'data' => ['program_id' => $programs['ristek_dashboard']->id, 'source' => 'seed'],
                'read_at' => null,
            ],
            [
                'user_id' => $users['psdm_staff_1']->id,
                'type' => Notification::TYPE_EVALUATION_NEW,
                'title' => 'Evaluasi Baru',
                'message' => 'Kamu mendapat evaluasi dari Kabinet.',
                'data' => ['period' => now()->format('Y-m'), 'source' => 'seed'],
                'read_at' => now()->subDay(),
            ],
            [
                'user_id' => $users['humas_staff_1']->id,
                'type' => Notification::TYPE_ANNOUNCEMENT,
                'title' => 'Pengumuman Baru',
                'message' => 'Ada pengumuman rapat besar kabinet.',
                'data' => ['source' => 'seed'],
                'read_at' => null,
            ],
        ] as $notification) {
            Notification::updateOrCreate(
                [
                    'user_id' => $notification['user_id'],
                    'type' => $notification['type'],
                    'title' => $notification['title'],
                ],
                [
                    'message' => $notification['message'],
                    'data' => $notification['data'],
                    'read_at' => $notification['read_at'],
                ]
            );
        }
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedEvaluations(array $users): void
    {
        $periods = [now()->format('Y-m'), now()->subMonth()->format('Y-m')];

        $staffEvaluators = [
            'psdm_staff_1' => $users['psdm_head'],
            'psdm_staff_2' => $users['psdm_head'],
            'medinfo_staff_1' => $users['medinfo_head'],
            'medinfo_staff_2' => $users['medinfo_head'],
            'humas_staff_1' => $users['humas_head'],
            'humas_staff_2' => $users['humas_head'],
            'ristek_staff_1' => $users['ristek_head'],
            'ristek_staff_2' => $users['ristek_head'],
            'akademik_staff_1' => $users['akademik_head'],
            'akademik_staff_2' => $users['akademik_head'],
        ];

        $scoreMap = [
            'psdm_staff_1' => [5, 4, 5, 4, 4, 5],
            'psdm_staff_2' => [4, 4, 4, 5, 4, 4],
            'medinfo_staff_1' => [4, 5, 4, 4, 5, 5],
            'medinfo_staff_2' => [4, 3, 4, 4, 4, 4],
            'humas_staff_1' => [5, 4, 4, 5, 4, 4],
            'humas_staff_2' => [3, 4, 4, 4, 3, 4],
            'ristek_staff_1' => [5, 5, 5, 4, 5, 4],
            'ristek_staff_2' => [4, 4, 5, 4, 4, 5],
            'akademik_staff_1' => [4, 5, 4, 5, 4, 4],
            'akademik_staff_2' => [4, 4, 4, 4, 4, 5],
        ];

        foreach ($staffEvaluators as $staffKey => $kabinetEvaluator) {
            foreach ($periods as $index => $period) {
                $scores = $this->adjustScores($scoreMap[$staffKey], $index);

                $this->upsertEvaluation(
                    $users[$staffKey],
                    $kabinetEvaluator,
                    'kabinet',
                    $period,
                    $scores,
                    'Evaluasi kabinet untuk progres bulanan dan kolaborasi tim.'
                );

                $this->upsertEvaluation(
                    $users[$staffKey],
                    $users['bph'],
                    'bph',
                    $period,
                    $this->adjustScores($scores, -1),
                    'Evaluasi BPH untuk kualitas output dan konsistensi eksekusi.'
                );
            }
        }
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedInformationBoards(array $users): void
    {
        $categories = InformationCategory::query()->get()->keyBy('slug');

        $articles = [
            [
                'slug' => 'pembaruan-roadmap-semester',
                'title' => 'Pembaruan Roadmap Semester dan Prioritas Divisi',
                'excerpt' => 'Ringkasan fokus kerja tiap departemen untuk paruh semester berikutnya.',
                'content' => '<p>Kabinet menetapkan tiga prioritas utama: penguatan koordinasi, peningkatan kualitas dokumentasi, dan akselerasi delivery program lintas departemen.</p><p>Setiap kepala departemen diminta menurunkan target tersebut menjadi milestone mingguan yang realistis.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(7),
                'meta_title' => 'Roadmap Semester Kabinet Harmoni',
                'meta_description' => 'Update prioritas semester dan langkah eksekusi lintas departemen CMOS.',
                'user_id' => $users['bph']->id,
                'categories' => ['pengumuman', 'kegiatan'],
            ],
            [
                'slug' => 'kolaborasi-dengan-mitra-media',
                'title' => 'Kolaborasi Baru dengan Mitra Media Kampus',
                'excerpt' => 'Medinfo dan Humas membuka kanal distribusi publikasi yang lebih luas.',
                'content' => '<p>Kolaborasi ini membuka peluang distribusi konten lintas kanal dan mempercepat publikasi kegiatan yang bersifat eksternal.</p><p>Tim juga sedang menyiapkan guideline approval agar kualitas visual dan copywriting tetap konsisten.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'meta_title' => 'Kolaborasi Media Kampus',
                'meta_description' => 'Inisiatif baru untuk memperluas jangkauan publikasi dan komunikasi eksternal.',
                'user_id' => $users['medinfo_head']->id,
                'categories' => ['kolaborasi', 'dokumentasi'],
            ],
            [
                'slug' => 'arsitektur-dashboard-internal',
                'title' => 'Catatan Pengembangan Dashboard Internal',
                'excerpt' => 'Ristek membagikan progres sprint dan rencana iterasi berikutnya.',
                'content' => '<p>Sprint terbaru berfokus pada penyederhanaan alur input task, optimasi render halaman publik, dan penyusunan dataset evaluasi yang lebih representatif.</p>',
                'status' => 'draft',
                'published_at' => null,
                'meta_title' => 'Pengembangan Dashboard Internal CMOS',
                'meta_description' => 'Catatan teknis sprint dashboard internal CMOS.',
                'user_id' => $users['ristek_head']->id,
                'categories' => ['dokumentasi'],
            ],
        ];

        foreach ($articles as $article) {
            $board = InformationBoard::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'user_id' => $article['user_id'],
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content' => $article['content'],
                    'cover_image' => null,
                    'status' => $article['status'],
                    'published_at' => $article['published_at'],
                    'meta_title' => $article['meta_title'],
                    'meta_description' => $article['meta_description'],
                ]
            );

            $board->categories()->sync(
                collect($article['categories'])
                    ->map(fn (string $slug) => $categories[$slug]->id)
                    ->all()
            );
        }
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Program>  $programs
     */
    private function seedActivityLogs(array $users, array $programs): void
    {
        foreach ([
            [
                'user_id' => $users['admin']->id,
                'action' => 'created',
                'model_type' => Program::class,
                'model_id' => $programs['ristek_dashboard']->id,
                'description' => 'Membuat struktur awal untuk dashboard internal.',
                'properties' => ['source' => 'seed', 'scope' => 'program'],
            ],
            [
                'user_id' => $users['bph']->id,
                'action' => 'updated',
                'model_type' => Task::class,
                'model_id' => Task::query()->where('title', 'Sinkronisasi agenda kabinet bulanan')->value('id'),
                'description' => 'Memperbarui progres koordinasi agenda kabinet.',
                'properties' => ['progress' => 40],
            ],
            [
                'user_id' => $users['medinfo_head']->id,
                'action' => 'created',
                'model_type' => Announcement::class,
                'model_id' => Announcement::query()->where('user_id', $users['admin']->id)->value('id'),
                'description' => 'Membuat pengumuman polling gathering internal.',
                'properties' => ['channel' => 'announcement'],
            ],
            [
                'user_id' => $users['psdm_staff_1']->id,
                'action' => 'login',
                'model_type' => null,
                'model_id' => null,
                'description' => 'Login untuk update progres onboarding staff baru.',
                'properties' => ['source' => 'seed'],
            ],
        ] as $activity) {
            ActivityLog::firstOrCreate(
                [
                    'user_id' => $activity['user_id'],
                    'action' => $activity['action'],
                    'description' => $activity['description'],
                ],
                [
                    'model_type' => $activity['model_type'],
                    'model_id' => $activity['model_id'],
                    'properties' => $activity['properties'],
                    'ip_address' => '127.0.0.1',
                ]
            );
        }
    }

    private function upsertUser(string $email, array $attributes): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            array_merge($attributes, ['email_verified_at' => now()])
        );
    }

    /**
     * @param  array<int, array{role: string}>  $members
     * @param  array<int, int>  $pics
     */
    private function upsertProgram(
        string $name,
        Department $department,
        array $attributes,
        array $members,
        array $pics,
    ): Program {
        $program = Program::updateOrCreate(
            ['department_id' => $department->id, 'name' => $name],
            $attributes
        );

        $program->members()->sync($members);
        $program->pics()->sync($pics);

        return $program;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsertTask(array $attributes): Task
    {
        $task = Task::updateOrCreate(
            [
                'title' => $attributes['title'],
                'program_id' => $attributes['program_id'],
                'department_id' => $attributes['department_id'],
            ],
            $attributes
        );

        $deadline = $task->deadline instanceof Carbon ? $task->deadline : ($task->deadline ? Carbon::parse($task->deadline) : null);

        if ($task->assigned_to && $deadline && $deadline->isFuture() && $deadline->diffInDays(now()) <= 3) {
            Notification::firstOrCreate(
                [
                    'user_id' => $task->assigned_to,
                    'type' => Notification::TYPE_DEADLINE_REMINDER,
                    'title' => 'Deadline Mendekat',
                    'message' => "Task '{$task->title}' jatuh tempo dalam {$deadline->diffInDays(now())} hari",
                ],
                [
                    'data' => ['task_id' => $task->id, 'deadline' => $deadline->toDateString()],
                ]
            );
        }

        return $task;
    }

    /**
     * @param  array<int, int>  $scores
     */
    private function upsertEvaluation(
        User $staff,
        User $evaluator,
        string $evaluatorType,
        string $period,
        array $scores,
        string $notes,
    ): void {
        Evaluation::updateOrCreate(
            [
                'user_id' => $staff->id,
                'evaluator_type' => $evaluatorType,
                'period' => $period,
            ],
            [
                'evaluator_id' => $evaluator->id,
                'kehadiran' => $scores[0],
                'kedisiplinan' => $scores[1],
                'tanggung_jawab' => $scores[2],
                'kerjasama' => $scores[3],
                'inisiatif' => $scores[4],
                'komunikasi' => $scores[5],
                'notes' => $notes,
            ]
        );
    }

    /**
     * @param  array<int, int>  $scores
     * @return array<int, int>
     */
    private function adjustScores(array $scores, int $delta): array
    {
        return collect($scores)
            ->map(fn (int $score) => max(1, min(5, $score + $delta)))
            ->values()
            ->all();
    }
}
