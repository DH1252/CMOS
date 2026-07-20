<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DriveController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InformationBoardController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePasswordController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\PublicInformationController;
use App\Http\Controllers\RealtimeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SiteStatisticsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\UserController;
use App\Models\Setting;
use App\Support\LandingPageData;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Landing Page
Route::get('/', function () {
    return Inertia::render('LandingPage', app(LandingPageData::class)->props());
})->name('home');
Route::get('/informasi', [PublicInformationController::class, 'index'])->name('informasi.index');
Route::get('/informasi/{informationBoard:slug}', [PublicInformationController::class, 'show'])->name('informasi.show');
Route::get('/acara', [PublicEventController::class, 'index'])->name('acara.index');
Route::get('/acara/{event:slug}', [PublicEventController::class, 'show'])->name('acara.show');

// Coming-soon public pages (navbar targets not yet built)
$comingSoon = static function (string $pageTitle, string $description) {
    $organizationName = (string) Setting::get('organization_name', 'HIMATEKKOM ITS');

    return Inertia::render('PublicComingSoonPage', [
        'pageTitle' => $pageTitle,
        'organizationName' => $organizationName,
        'description' => $description,
        'homeUrl' => route('home'),
        'seo' => [
            'title' => $pageTitle.' - '.$organizationName,
            'description' => $description,
            'canonical' => url()->current(),
        ],
    ]);
};

Route::get('/tentang', function () {
    return Inertia::render('public/PublicAboutPage', app(\App\Support\AboutPageData::class)->props());
})->name('tentang');

Route::get('/departemen/overview', function () {
    $organizationName = (string) \App\Models\Setting::get('organization_name', 'HIMATEKKOM ITS');

    return Inertia::render('public/PublicDepartmentOverviewPage', [
        'organizationName' => $organizationName,
        'homeUrl' => route('home'),
        'loginUrl' => route('login'),
        'infoUrl' => route('informasi.index'),
        'acaraUrl' => route('acara.index'),
    ]);
})->name('departemen.overview');

Route::get('/departemen/{slug?}', function (?string $slug = null) {
    $organizationName = (string) \App\Models\Setting::get('organization_name', 'HIMATEKKOM ITS');

    if ($slug) {
        $validSlugs = array_keys(\App\Models\Department::PUBLIC_PAGE_OPTIONS);
        if (in_array(strtolower($slug), $validSlugs)) {
            $department = \App\Models\Department::where('slug', strtolower($slug))->first();

            if (! $department) {
                $search = match (strtolower($slug)) {
                    'risprof' => 'ristek',
                    'kwu' => 'kewirausahaan',
                    'hublu' => 'humas',
                    'medfo' => 'medinfo',
                    default => $slug,
                };
                $department = \App\Models\Department::where('name', 'LIKE', '%'.$search.'%')->first();
            }

            $programs = $department
                ? $department->programs()
                    ->with('tasks')
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('start_date')
                    ->get()
                    ->map(fn ($program) => [
                        'name' => $program->name,
                        'description' => $program->description,
                        'startDate' => $program->start_date?->toDateString(),
                        'endDate' => $program->end_date?->toDateString(),
                        'status' => $program->status,
                        'progress' => $program->progress,
                    ])
                    ->values()
                : [];

            return Inertia::render('public/PublicDepartmentDetailPage', [
                'organizationName' => $organizationName,
                'homeUrl' => route('home'),
                'loginUrl' => route('login'),
                'infoUrl' => route('informasi.index'),
                'acaraUrl' => route('acara.index'),
                'selectedSlug' => strtolower($slug),
                'staffGraphics' => $department ? $department->staff_graphics : null,
                'programs' => $programs,
            ]);
        }
    }

    return Inertia::render('public/PublicDepartmentPage', [
        'organizationName' => $organizationName,
        'homeUrl' => route('home'),
        'loginUrl' => route('login'),
        'infoUrl' => route('informasi.index'),
        'acaraUrl' => route('acara.index'),
        'selectedSlug' => $slug,
    ]);
})->name('departemen');
Route::get('/kompetisi', function () {
    $organizationName = (string) \App\Models\Setting::get('organization_name', 'HIMATEKKOM ITS');
    $competitionsPath = storage_path('app/competitions.json');
    $competitions = [];

    if (file_exists($competitionsPath)) {
        $competitions = json_decode(file_get_contents($competitionsPath), true) ?: [];
    }

    return Inertia::render('public/PublicCompetitionPage', [
        'organizationName' => $organizationName,
        'homeUrl' => route('home'),
        'loginUrl' => route('login'),
        'infoUrl' => route('informasi.index'),
        'acaraUrl' => route('acara.index'),
        'departemenUrl' => route('departemen'),
        'kompetisiUrl' => route('kompetisi'),
        'tentangUrl' => route('tentang'),
        'competitions' => $competitions,
    ]);
})->name('kompetisi');

// Optimized image serving
Route::get('/images/optimize/{path}', [ImageController::class, 'show'])
    ->where('path', '.*')
    ->withoutMiddleware([
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
        \App\Http\Middleware\LogVisitor::class,
    ])
    ->name('images.optimize');

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/realtime/snapshot', [RealtimeController::class, 'snapshot'])->name('realtime.snapshot');

    // Profile Routes - All authenticated users
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
    Route::put('/profile/password', [ProfilePasswordController::class, 'update'])->name('profile.password.update');

    // Notification Routes - All authenticated users
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Admin Only Routes
    Route::middleware('role:admin')->group(function () {
        // User Import (must be before resource to avoid conflict with {user})
        Route::get('/users/import', [UserController::class, 'importForm'])->name('users.import');
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import.process');
        Route::get('/users/import/template', [UserController::class, 'downloadTemplate'])->name('users.import.template');

        Route::resource('users', UserController::class);
        Route::resource('settings', SettingController::class)->only(['index', 'update']);
        Route::get('/statistics', [SiteStatisticsController::class, 'index'])->name('statistics.index');
        Route::post('/statistics/fetch-competitions', [SiteStatisticsController::class, 'fetchCompetitions'])->name('statistics.fetch-competitions');
        Route::post('/statistics/settings', [SiteStatisticsController::class, 'updateSettings'])->name('statistics.update-settings');
    });

    // Admin, BPH & Kabinet Routes
    Route::middleware('role:admin,bph,kabinet')->group(function () {
        Route::resource('drives', DriveController::class)->except(['index', 'show']);
        Route::resource('cabinets', CabinetController::class);
        Route::post('departments/upload-graphic', [\App\Http\Controllers\DepartmentController::class, 'uploadGraphic'])->name('departments.upload-graphic');
        Route::resource('departments', DepartmentController::class);
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');

        // Links CRUD (Admin & BPH)
        Route::resource('links', LinkController::class)->except(['index', 'show']);
    });

    // All authenticated users can view programs
    Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
    Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('programs.show');

    // Admin, BPH & Kabinet Routes
    Route::middleware('role:admin,bph,kabinet')->group(function () {
        Route::get('/programs/create', [ProgramController::class, 'create'])->name('programs.create');
        Route::post('/programs', [ProgramController::class, 'store'])->name('programs.store');
        Route::put('/programs/{program}', [ProgramController::class, 'update'])->name('programs.update');
        Route::delete('/programs/{program}', [ProgramController::class, 'destroy'])->name('programs.destroy');
        Route::post('/programs/{program}/members', [ProgramController::class, 'addMember'])->name('programs.members.add');
        Route::delete('/programs/{program}/members/{user}', [ProgramController::class, 'removeMember'])->name('programs.members.remove');
        Route::post('/programs/{program}/pics', [ProgramController::class, 'addPic'])->name('programs.pics.add');
        Route::delete('/programs/{program}/pics/{user}', [ProgramController::class, 'removePic'])->name('programs.pics.remove');

        // Evaluations - Hierarchical navigation
        Route::get('/evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
        Route::get('/evaluations/department/{department}', [EvaluationController::class, 'department'])->name('evaluations.department');
        Route::get('/evaluations/create', [EvaluationController::class, 'create'])->name('evaluations.create');
        Route::post('/evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');
        Route::get('/evaluations/user/{user}', [EvaluationController::class, 'show'])->name('evaluations.show');
        Route::get('/evaluations/{evaluation}/edit', [EvaluationController::class, 'edit'])->name('evaluations.edit');
        Route::put('/evaluations/{evaluation}', [EvaluationController::class, 'update'])->name('evaluations.update');
        Route::delete('/evaluations/{evaluation}', [EvaluationController::class, 'destroy'])->name('evaluations.destroy');
        Route::get('/evaluations/ranking', [EvaluationController::class, 'ranking'])->name('evaluations.ranking');
    });

    // Staff can view programs they are member/PIC of
    Route::get('/my-programs', [ProgramController::class, 'myPrograms'])->name('programs.my');

    // All Authenticated Users - Timelines
    Route::get('/timelines', [TimelineController::class, 'index'])->name('timelines.index');
    Route::get('/timelines/calendar', [TimelineController::class, 'calendar'])->name('timelines.calendar');
    Route::get('/timelines/calendar-data', [TimelineController::class, 'calendarData'])->name('timelines.calendar.data');
    Route::get('/timelines/global', [TimelineController::class, 'global'])->name('timelines.global');
    Route::get('/timelines/department/{department?}', [TimelineController::class, 'department'])->name('timelines.department');
    Route::get('/timelines/program/{program}', [TimelineController::class, 'program'])->name('timelines.program');

    Route::middleware('role:admin,bph,kabinet')->group(function () {
        Route::resource('timelines', TimelineController::class)->except(['index', 'show']);
    });

    // Task Routes - Kanban Style Navigation
    // Kanban Navigation - All authenticated users
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/global', [TaskController::class, 'global'])->name('tasks.global');
    Route::get('/tasks/department/{department}', [TaskController::class, 'department'])->name('tasks.department');
    Route::get('/tasks/department/{department}/tasks', [TaskController::class, 'departmentTasks'])->name('tasks.department.tasks');
    Route::get('/tasks/program/{program}', [TaskController::class, 'program'])->name('tasks.program');

    // Inline Kanban CRUD (JSON API - all authenticated)
    Route::post('/tasks/inline', [TaskController::class, 'storeInline'])->name('tasks.inline.store');
    Route::patch('/tasks/{task}/inline', [TaskController::class, 'updateInline'])->name('tasks.inline.update');
    Route::delete('/tasks/{task}/inline', [TaskController::class, 'destroyInline'])->name('tasks.inline.destroy');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');

    // Legacy Task CRUD (keep for compatibility)
    Route::middleware('role:admin,bph,kabinet')->group(function () {
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    });
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/progress', [TaskController::class, 'updateProgress'])->name('tasks.progress');

    // All users can view Drive & Links
    Route::get('/drives', [DriveController::class, 'index'])->name('drives.index');
    Route::get('/links', [LinkController::class, 'index'])->name('links.index');

    // Announcements - All users can create, only creator can delete
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::post('/announcements/{announcement}/comment', [AnnouncementController::class, 'comment'])->name('announcements.comment');
    Route::post('/announcements/{announcement}/react', [AnnouncementController::class, 'react'])->name('announcements.react');
    Route::post('/announcements/{announcement}/vote', [AnnouncementController::class, 'vote'])->name('announcements.vote');

    // Information Boards - all authenticated roles can write
    Route::post('/information-boards/attachments/upload', [InformationBoardController::class, 'uploadAttachment'])
        ->name('information-boards.attachments.upload');
    Route::resource('information-boards', InformationBoardController::class);

    // Events - all authenticated roles can write
    Route::resource('events', EventController::class);

    // Messages - All users
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount'])->name('messages.unread');
    Route::get('/messages/sidebar-data', [MessageController::class, 'sidebarData'])->name('messages.sidebar-data');
    Route::get('/messages/conversation/{user}', [MessageController::class, 'conversation'])->name('messages.conversation');
    Route::post('/messages/send/{user}', [MessageController::class, 'send'])->name('messages.send');
    Route::post('/messages/read/{user}', [MessageController::class, 'markRead'])->name('messages.read');

    // Staff: view own evaluations
    Route::get('/my-evaluations', [EvaluationController::class, 'myEvaluations'])->name('evaluations.my');
});
