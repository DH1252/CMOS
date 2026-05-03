<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class AuthShellData
{
    /**
     * @return array<string, mixed>
     */
    public function forRequest(Request $request): array
    {
        $currentUser = $request->user();

        if (! $currentUser instanceof User) {
            return [];
        }

        $appName = Setting::get('app_name', 'CMOS');
        $organizationName = Setting::get('organization_name', 'HIMATEKKOM ITS');
        $themeSettings = Setting::query()
            ->whereIn('key', array_merge(['theme_color'], ThemePalette::settingKeys(), ThemePalette::cssVariableKeys()))
            ->pluck('value', 'key')
            ->all();
        $themePayload = ThemePalette::payloadFromSettings($themeSettings);

        $navSections = [
            [
                'title' => 'Operasional',
                'description' => 'Area kerja utama untuk aktivitas harian.',
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'fas fa-house', 'href' => route('dashboard'), 'active' => $request->routeIs('dashboard'), 'meta' => 'Ringkasan utama workspace'],
                    ['label' => 'Task', 'icon' => 'fas fa-list-check', 'href' => route('tasks.index'), 'active' => $request->routeIs('tasks.*'), 'meta' => 'Tindak lanjut lintas tim'],
                    ['label' => 'Timeline', 'icon' => 'fas fa-calendar-days', 'href' => route('timelines.calendar'), 'active' => $request->routeIs('timelines.*'), 'meta' => 'Agenda dan kalender kerja'],
                    ['label' => 'Pesan', 'icon' => 'fas fa-comments', 'href' => route('messages.index'), 'active' => $request->routeIs('messages.*'), 'meta' => 'Percakapan dan unread'],
                ],
            ],
            [
                'title' => 'Koordinasi',
                'description' => 'Publikasi, program, dan evaluasi kabinet.',
                'items' => [
                    ['label' => 'Pengumuman', 'icon' => 'fas fa-bullhorn', 'href' => route('announcements.index'), 'active' => $request->routeIs('announcements.*'), 'meta' => 'Feed internal kabinet'],
                    ['label' => 'Informasi', 'icon' => 'fas fa-newspaper', 'href' => route('information-boards.index'), 'active' => $request->routeIs('information-boards.*'), 'meta' => 'Publikasi dan arsip resmi'],
                    [
                        'label' => 'Program kerja',
                        'icon' => 'fas fa-diagram-project',
                        'href' => $currentUser->hasRole(['admin', 'bph', 'kabinet']) ? route('programs.index') : route('programs.my'),
                        'active' => $request->routeIs('programs.*'),
                        'meta' => $currentUser->hasRole(['admin', 'bph', 'kabinet']) ? 'Daftar dan detail program' : 'Program yang sedang diikuti',
                    ],
                ],
            ],
        ];

        if ($currentUser->hasRole(['admin', 'bph'])) {
            $managementItems = [];

            if ($currentUser->isAdmin()) {
                $managementItems[] = ['label' => 'Data User', 'icon' => 'fas fa-users', 'href' => route('users.index'), 'active' => $request->routeIs('users.index') || $request->routeIs('users.show') || $request->routeIs('users.create') || $request->routeIs('users.edit')];
                $managementItems[] = ['label' => 'Import User CSV', 'icon' => 'fas fa-file-csv', 'href' => route('users.import'), 'active' => $request->routeIs('users.import*')];
            }

            $managementItems[] = ['label' => 'Departemen', 'icon' => 'fas fa-building', 'href' => route('departments.index'), 'active' => $request->routeIs('departments.*')];
            $managementItems[] = ['label' => 'Kabinet', 'icon' => 'fas fa-landmark', 'href' => route('cabinets.index'), 'active' => $request->routeIs('cabinets.*')];

            $navSections[] = [
                'title' => 'Struktur',
                'description' => 'Data kabinet, departemen, dan personel yang membentuk organisasi.',
                'items' => array_map(function (array $item): array {
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
                ['label' => 'Evaluasi Staff', 'icon' => 'fas fa-star', 'href' => route('evaluations.index'), 'active' => $request->routeIs('evaluations.*'), 'meta' => 'Hub departemen dan ranking'],
            ];

            if ($currentUser->hasRole(['admin', 'bph'])) {
                $evaluationItems[] = ['label' => 'Laporan', 'icon' => 'fas fa-chart-column', 'href' => route('reports.index'), 'active' => $request->routeIs('reports.*'), 'meta' => 'Ringkasan performa organisasi'];
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
                    ['label' => 'Nilai Saya', 'icon' => 'fas fa-star', 'href' => route('evaluations.my'), 'active' => $request->routeIs('evaluations.my'), 'meta' => 'Riwayat evaluasi pribadi'],
                ],
            ];
        }

        $navSections[] = [
            'title' => 'Referensi',
            'description' => 'Akses cepat ke dokumen, link, dan referensi kerja bersama.',
            'items' => array_values(array_filter([
                ['label' => 'Google Drive', 'icon' => 'fab fa-google-drive', 'href' => route('drives.index'), 'active' => $request->routeIs('drives.*'), 'meta' => 'Folder dan dokumen bersama'],
                ['label' => 'Kumpulan Link', 'icon' => 'fas fa-link', 'href' => route('links.index'), 'active' => $request->routeIs('links.*'), 'meta' => 'Tautan kerja yang sering dipakai'],
                $currentUser->hasRole(['admin', 'bph', 'kabinet']) ? ['label' => 'Proker Saya', 'icon' => 'fas fa-folder-open', 'href' => route('programs.my'), 'active' => $request->routeIs('programs.my'), 'meta' => 'Program yang sedang saya tangani'] : null,
            ])),
        ];

        if ($currentUser->isAdmin()) {
            $navSections[] = [
                'title' => 'Pengaturan',
                'description' => 'Kontrol identitas aplikasi dan pengaturan dasar workspace.',
                'items' => [
                    ['label' => 'Pengaturan', 'icon' => 'fas fa-gear', 'href' => route('settings.index'), 'active' => $request->routeIs('settings.*'), 'meta' => 'Identitas, warna, dan cadence evaluasi'],
                ],
            ];
        }

        return [
            'appName' => $appName,
            'organizationName' => $organizationName,
            'themeColor' => $themePayload['color'],
            'themeVariables' => $themePayload['variables'],
            'themeCustomCss' => $themePayload['customCss'],
            'csrfToken' => csrf_token(),
            'user' => [
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'email' => $currentUser->email,
                'role' => $currentUser->role?->name,
                'department' => $currentUser->department?->name,
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
                'notificationsClearAll' => route('notifications.clear-all'),
            ],
            'quickChat' => [
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
    }
}
