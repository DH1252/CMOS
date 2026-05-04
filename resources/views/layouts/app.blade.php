<!DOCTYPE html>
<html lang="id" data-theme="dark" data-brand="{{ \App\Models\Setting::get('theme_color', \App\Support\ThemePalette::defaultName()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $appName = \App\Models\Setting::get('app_name', 'CMOS');
        $organizationName = \App\Models\Setting::get('organization_name', 'HIMATEKKOM ITS');
        $pageTitle = trim($__env->yieldContent('page-title')) ?: trim($__env->yieldContent('title')) ?: 'Dashboard';
        $pageMeta = trim($__env->yieldContent('page-meta'));
        $currentUser = auth()->user();
        $isSveltePropExtraction = (bool) ($__svelteExtractingProps ?? false);
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

        if ($isSveltePropExtraction) {
            $quickChatUsers = collect();
            $quickChatConversations = collect();
        } else {
            $quickChatUsers = \App\Models\User::query()
                ->where('id', '!=', $currentUser->id)
                ->where('status', 'active')
                ->with(['role', 'department'])
                ->orderBy('name')
                ->get();

            $quickChatConversations = \App\Models\Message::getConversations($currentUser->id);
        }

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
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    {{-- Self-hosted font CSS (subsetted woff2, ~53 KB total) --}}
    <link rel="preload" href="{{ asset('fonts/public-sans.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('fonts/public-sans.css') }}">
    </noscript>
    {{-- Self-hosted Font Awesome 6.5.1 (~360 KB woff2 + 103 KB CSS) --}}
    <link rel="preload" href="{{ asset('fonts/font-awesome/css/all.min.css') }}" as="style" fetchpriority="low" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('fonts/font-awesome/css/all.min.css') }}">
    </noscript>
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>
    <main class="page-content" id="main-content">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
