<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $appName = \App\Models\Setting::get('app_name', 'CMOS');
        $organizationName = \App\Models\Setting::get('organization_name', 'HIMATEKKOM ITS');
        $themeColor = \App\Models\Setting::get('theme_color', 'purple');
        $themeColors = [
            'purple' => ['primary' => '#7C3AED', 'hover' => '#6D28D9', 'soft' => '#A78BFA', 'light' => '#EDE9FE'],
            'blue' => ['primary' => '#3B82F6', 'hover' => '#2563EB', 'soft' => '#60A5FA', 'light' => '#DBEAFE'],
            'green' => ['primary' => '#10B981', 'hover' => '#059669', 'soft' => '#34D399', 'light' => '#D1FAE5'],
            'red' => ['primary' => '#EF4444', 'hover' => '#DC2626', 'soft' => '#F87171', 'light' => '#FEE2E2'],
            'orange' => ['primary' => '#F59E0B', 'hover' => '#D97706', 'soft' => '#FBBF24', 'light' => '#FEF3C7'],
            'pink' => ['primary' => '#EC4899', 'hover' => '#DB2777', 'soft' => '#F472B6', 'light' => '#FCE7F3'],
            'indigo' => ['primary' => '#6366F1', 'hover' => '#4F46E5', 'soft' => '#818CF8', 'light' => '#E0E7FF'],
            'teal' => ['primary' => '#14B8A6', 'hover' => '#0D9488', 'soft' => '#2DD4BF', 'light' => '#CCFBF1'],
            'cyan' => ['primary' => '#06B6D4', 'hover' => '#0891B2', 'soft' => '#22D3EE', 'light' => '#CFFAFE'],
            'rose' => ['primary' => '#F43F5E', 'hover' => '#E11D48', 'soft' => '#FB7185', 'light' => '#FFE4E6'],
            'amber' => ['primary' => '#F59E0B', 'hover' => '#D97706', 'soft' => '#FBBF24', 'light' => '#FEF3C7'],
            'slate' => ['primary' => '#64748B', 'hover' => '#475569', 'soft' => '#94A3B8', 'light' => '#F1F5F9'],
        ];
        $colors = $themeColors[$themeColor] ?? $themeColors['purple'];
        $pageTitle = trim($__env->yieldContent('page-title')) ?: trim($__env->yieldContent('title')) ?: 'Dashboard';
        $pageMeta = trim($__env->yieldContent('page-meta'));
        $currentUser = auth()->user();
        $navSections = [
            [
                'title' => 'Operasional',
                'description' => 'Area kerja utama untuk aktivitas harian.',
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'fas fa-house', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard'), 'meta' => 'Ringkasan utama workspace'],
                    ['label' => 'Task', 'icon' => 'fas fa-list-check', 'href' => route('tasks.index'), 'active' => request()->routeIs('tasks.*'), 'meta' => 'Tindak lanjut lintas tim'],
                    ['label' => 'Timeline', 'icon' => 'fas fa-calendar-days', 'href' => route('timelines.calendar'), 'active' => request()->routeIs('timelines.*'), 'meta' => 'Agenda dan kalender kerja'],
                    ['label' => 'Pesan', 'icon' => 'fas fa-comments', 'href' => route('messages.index'), 'active' => request()->routeIs('messages.*'), 'meta' => 'Percakapan dan unread'],
                ],
            ],
            [
                'title' => 'Koordinasi',
                'description' => 'Publikasi, program, dan evaluasi kabinet.',
                'items' => [
                    ['label' => 'Pengumuman', 'icon' => 'fas fa-bullhorn', 'href' => route('announcements.index'), 'active' => request()->routeIs('announcements.*'), 'meta' => 'Feed internal kabinet'],
                    ['label' => 'Informasi', 'icon' => 'fas fa-newspaper', 'href' => route('information-boards.index'), 'active' => request()->routeIs('information-boards.*'), 'meta' => 'Publikasi dan arsip resmi'],
                    ['label' => 'Program kerja', 'icon' => 'fas fa-diagram-project', 'href' => $currentUser->hasRole(['admin', 'bph', 'kabinet']) ? route('programs.index') : route('programs.my'), 'active' => request()->routeIs('programs.*'), 'meta' => $currentUser->hasRole(['admin', 'bph', 'kabinet']) ? 'Daftar dan detail program' : 'Program yang sedang diikuti'],
                ],
            ],
        ];

        if ($currentUser->hasRole(['admin', 'bph'])) {
            $managementItems = [];

            if ($currentUser->isAdmin()) {
                $managementItems[] = ['label' => 'Data User', 'icon' => 'fas fa-users', 'href' => route('users.index'), 'active' => request()->routeIs('users.index') || request()->routeIs('users.show') || request()->routeIs('users.create') || request()->routeIs('users.edit')];
                $managementItems[] = ['label' => 'Import User CSV', 'icon' => 'fas fa-file-csv', 'href' => route('users.import'), 'active' => request()->routeIs('users.import*')];
            }

            $managementItems[] = ['label' => 'Departemen', 'icon' => 'fas fa-building', 'href' => route('departments.index'), 'active' => request()->routeIs('departments.*')];
            $managementItems[] = ['label' => 'Kabinet', 'icon' => 'fas fa-landmark', 'href' => route('cabinets.index'), 'active' => request()->routeIs('cabinets.*')];

            $navSections[] = [
                'title' => 'Struktur',
                'description' => 'Data kabinet, departemen, dan personel yang membentuk organisasi.',
                'items' => array_map(function ($item) {
                    $item['meta'] = match ($item['label']) {
                        'Data User' => 'Akun dan detail personel',
                        'Import User CSV' => 'Masukkan banyak user sekaligus',
                        'Departemen' => 'Unit kerja dan penanggung jawab',
                        'Kabinet' => 'Kabinet aktif dan riwayat struktur',
                        default => null,
                    };

                    return $item;
                }, $managementItems),
            ];
        }

        if ($currentUser->hasRole(['admin', 'bph', 'kabinet'])) {
            $evaluationItems = [
                ['label' => 'Evaluasi Staff', 'icon' => 'fas fa-star', 'href' => route('evaluations.index'), 'active' => request()->routeIs('evaluations.*'), 'meta' => 'Hub departemen dan ranking'],
            ];

            if ($currentUser->hasRole(['admin', 'bph'])) {
                $evaluationItems[] = ['label' => 'Laporan', 'icon' => 'fas fa-chart-column', 'href' => route('reports.index'), 'active' => request()->routeIs('reports.*'), 'meta' => 'Ringkasan performa organisasi'];
            }

            $navSections[] = [
                'title' => 'Penilaian',
                'description' => 'Area review untuk penilaian dan pelaporan lintas tim.',
                'items' => $evaluationItems,
            ];
        }

        if ($currentUser->isStaff()) {
            $navSections[] = [
                'title' => 'Penilaian',
                'description' => 'Area review yang paling relevan untuk staf.',
                'items' => [
                    ['label' => 'Nilai Saya', 'icon' => 'fas fa-star', 'href' => route('evaluations.my'), 'active' => request()->routeIs('evaluations.my'), 'meta' => 'Riwayat evaluasi pribadi'],
                ],
            ];
        }

        $navSections[] = [
            'title' => 'Referensi',
            'description' => 'Akses cepat ke dokumen, link, dan referensi kerja bersama.',
            'items' => array_values(array_filter([
                ['label' => 'Google Drive', 'icon' => 'fab fa-google-drive', 'href' => route('drives.index'), 'active' => request()->routeIs('drives.*'), 'meta' => 'Folder dan dokumen bersama'],
                ['label' => 'Kumpulan Link', 'icon' => 'fas fa-link', 'href' => route('links.index'), 'active' => request()->routeIs('links.*'), 'meta' => 'Tautan kerja yang sering dipakai'],
                $currentUser->hasRole(['admin', 'bph', 'kabinet']) ? ['label' => 'Proker Saya', 'icon' => 'fas fa-folder-open', 'href' => route('programs.my'), 'active' => request()->routeIs('programs.my'), 'meta' => 'Program yang sedang saya tangani'] : null,
            ])),
        ];

        if ($currentUser->isAdmin()) {
            $navSections[] = [
                'title' => 'Pengaturan',
                'description' => 'Kontrol identitas aplikasi dan pengaturan dasar workspace.',
                'items' => [
                    ['label' => 'Pengaturan', 'icon' => 'fas fa-gear', 'href' => route('settings.index'), 'active' => request()->routeIs('settings.*'), 'meta' => 'Identitas, warna, dan cadence evaluasi'],
                ],
            ];
        }

        $quickChatUsers = \App\Models\User::query()
            ->where('id', '!=', $currentUser->id)
            ->where('status', 'active')
            ->with(['role', 'department'])
            ->orderBy('name')
            ->get();

        $quickChatConversations = \App\Models\Message::getConversations($currentUser->id);

        $authProps = [
            'appName' => $appName,
            'organizationName' => $organizationName,
            'pageTitle' => $pageTitle,
            'pageMeta' => $pageMeta,
            'csrfToken' => csrf_token(),
            'user' => [
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'roleName' => $currentUser->role_name,
                'avatarUrl' => $currentUser->avatar_url,
            ],
            'navSections' => $navSections,
            'links' => [
                'dashboard' => route('dashboard'),
                'announcementsIndex' => route('announcements.index'),
                'profile' => route('profile.edit'),
                'settings' => $currentUser->isAdmin() ? route('settings.index') : null,
                'notifications' => route('notifications.index'),
                'messages' => route('messages.index'),
                'logout' => route('logout'),
            ],
            'endpoints' => [
                'realtimeSnapshot' => route('realtime.snapshot'),
                'notificationsRecent' => route('notifications.recent'),
                'notificationsUnread' => route('notifications.unread-count'),
                'notificationsMarkAll' => route('notifications.mark-all-read'),
            ],
            'quickChat' => [
                'users' => $quickChatUsers->map(fn ($chatUser) => [
                    'id' => $chatUser->id,
                    'name' => $chatUser->name,
                    'avatar' => $chatUser->avatar_url,
                    'role' => $chatUser->role_name,
                    'department' => $chatUser->department?->name ?? null,
                ])->values(),
                'conversations' => $quickChatConversations->values()->map(fn ($conversation) => [
                    'id' => $conversation->id,
                    'name' => $conversation->name,
                    'avatar' => $conversation->avatar_url,
                    'role' => $conversation->role_name,
                    'department' => $conversation->department?->name ?? null,
                    'lastMessage' => $conversation->last_message?->content ?? '',
                    'lastMessageAt' => $conversation->last_message?->created_at?->toIso8601String(),
                    'unreadCount' => (int) ($conversation->unread_count ?? 0),
                ]),
                'endpoints' => [
                    'realtimeSnapshot' => route('realtime.snapshot'),
                    'unread' => route('messages.unread'),
                    'sidebarData' => route('messages.sidebar-data'),
                    'conversationBase' => url('/messages/conversation'),
                    'sendBase' => url('/messages/send'),
                    'readBase' => url('/messages/read'),
                ],
                'link' => route('messages.index'),
                'csrfToken' => csrf_token(),
            ],
        ];
    @endphp
    <title>@yield('title', 'Dashboard') - {{ $appName }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logokabinet.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        (() => {
            try {
                const theme = localStorage.getItem('cmos-theme');

                if (theme === 'light' || theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', theme);
                }
            } catch (error) {
                // ignore storage access failures
            }
        })();
    </script>
    <style>
        :root {
            --brand-primary: {{ $colors['primary'] }};
            --brand-hover: {{ $colors['hover'] }};
            --brand-soft: {{ $colors['soft'] }};
            --brand-light: {{ $colors['light'] }};
            --brand-secondary: #8b5cf6;
            --brand-secondary-soft: #251d39;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <script id="svelte-auth-props" type="application/json">{!! str_replace('</', '<\/', json_encode($authProps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
    @if(session('swal'))
        <script id="session-swal-data" type="application/json">{!! str_replace('</', '<\/', json_encode([
            'type' => session('swal.type', 'success'),
            'title' => session('swal.title'),
            'text' => session('swal.text', ''),
            'confirmButtonColor' => '#f5c518',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !!}</script>
    @endif
    <div id="svelte-auth-root"></div>

    <div id="server-page-content">
        <main class="page-content">
            @if(session('success'))
                <div class="alert alert-success animate-fadeIn">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger animate-fadeIn">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning animate-fadeIn">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger animate-fadeIn">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')

    @auth
    @endauth
</body>
</html>
