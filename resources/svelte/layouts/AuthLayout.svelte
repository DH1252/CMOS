<script>
  import { onDestroy, onMount } from 'svelte';
  import { toast } from 'svelte-sonner';
  import { subscribeToLiveUpdates } from '$lib/live-updates.js';
  import { inertiaEnhance } from '$lib/inertia-enhance.js';
  import * as Sheet from '$lib/components/ui/sheet/index.js';
  import { Toaster } from '$lib/components/ui/sonner/index.js';
  import FloatingChat from '../components/FloatingChat.svelte';
  import NotificationPopover from '../components/NotificationPopover.svelte';
  import SidebarNav from '../components/SidebarNav.svelte';
  import UserMenuDropdown from '../components/UserMenuDropdown.svelte';

  let {
    children,
    shell = {},
    flash = {},
    errors = {},
    pageTitle = '',
    pageMeta = '',
    title = '',
    description = '',
  } = $props();

  let isSidebarOpen = $state(false);
  let isUserMenuOpen = $state(false);
  let isNotificationsOpen = $state(false);
  let unreadCount = $state(0);
  let notifications = $state([]);
  let isLoadingNotifications = $state(false);
  let themeMode = $state('dark');
  let liveUpdatesCleanup = $state(null);

  const toneMap = {
    primary: 'tone-primary',
    success: 'tone-success',
    warning: 'tone-warning',
    info: 'tone-info',
    secondary: 'tone-secondary',
  };

  const readThemeMode = () => {
    try {
      const saved = localStorage.getItem('cmos-theme');
      return saved === 'light' || saved === 'dark' ? saved : 'dark';
    } catch (error) {
      return 'dark';
    }
  };

  const persistThemeMode = (value) => {
    try {
      localStorage.setItem('cmos-theme', value);
    } catch (error) {
      // ignore storage access failures
    }
  };

  const iconMap = {
    task_assigned: 'fas fa-tasks',
    deadline_reminder: 'fas fa-clock',
    evaluation_new: 'fas fa-star',
    announcement: 'fas fa-bullhorn',
  };

  const effectiveTitle = $derived(pageTitle || title || 'Dashboard');
  const effectiveMeta = $derived(pageMeta || description || '');
  const shellUser = $derived(shell.user || {});
  const shellLinks = $derived(shell.links || {});
  const shellEndpoints = $derived(shell.endpoints || {});
  const shellNavSections = $derived(shell.navSections || []);
  const shellQuickChat = $derived(shell.quickChat || null);
  const shellAppName = $derived(shell.appName || 'CMOS');
  const shellOrganizationName = $derived(shell.organizationName || 'HIMATEKKOM ITS');
  const shellCsrfToken = $derived(shell.csrfToken || '');
  const errorMessages = $derived(Object.values(errors || {}).flat().filter(Boolean));

  const applyThemeMode = (value) => {
    themeMode = value;
    document.documentElement.setAttribute('data-theme', value);
  };

  const handleResize = () => {
    if (window.innerWidth >= 1024) {
      isSidebarOpen = false;
    }
  };

  const syncUnreadCount = async () => {
    if (!shellEndpoints.notificationsUnread) {
      return;
    }

    try {
      const response = await fetch(shellEndpoints.notificationsUnread, {
        headers: {
          Accept: 'application/json',
        },
      });

      if (!response.ok) {
        return;
      }

      const data = await response.json();
      unreadCount = Number(data.count || 0);
    } catch (error) {
      console.error('Failed to fetch unread count', error);
    }
  };

  const loadNotifications = async () => {
    if (!shellEndpoints.notificationsRecent) {
      return;
    }

    isLoadingNotifications = true;

    try {
      const response = await fetch(shellEndpoints.notificationsRecent, {
        headers: {
          Accept: 'application/json',
        },
      });

      if (!response.ok) {
        return;
      }

      const data = await response.json();
      notifications = Array.isArray(data.notifications) ? data.notifications : [];
      unreadCount = Number(data.unread_count || 0);
    } catch (error) {
      console.error('Failed to fetch notifications', error);
      toast.error('Gagal memuat notifikasi terbaru.', { id: 'shell-notifications' });
    } finally {
      isLoadingNotifications = false;
    }
  };

  const handleNotificationsOpenChange = (value) => {
    isNotificationsOpen = value;

    if (value) {
      isUserMenuOpen = false;
      void loadNotifications();
    }
  };

  const markAllNotificationsAsRead = async () => {
    if (!shellEndpoints.notificationsMarkAll || unreadCount < 1) {
      return;
    }

    try {
      const response = await fetch(shellEndpoints.notificationsMarkAll, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': shellCsrfToken,
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}`);
      }

      const readAt = new Date().toISOString();

      unreadCount = 0;
      notifications = notifications.map((notification) => ({
        ...notification,
        read_at: notification.read_at || readAt,
        readAt: notification.readAt || readAt,
      }));
    } catch (error) {
      console.error('Failed to mark all notifications as read', error);
      toast.error('Tidak bisa menandai semua notifikasi.', { id: 'shell-notifications-mark-all' });
    }
  };

  const handleUserMenuOpenChange = (value) => {
    isUserMenuOpen = value;

    if (value) {
      isNotificationsOpen = false;
    }
  };

  const toggleThemeMode = () => {
    const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    persistThemeMode(next);
    applyThemeMode(next);
  };

  const formatTime = (value) => {
    if (!value) {
      return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return '';
    }

    return date.toLocaleString('id-ID', {
      timeZone: 'Asia/Jakarta',
      day: '2-digit',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const iconForNotification = (type) => iconMap[type] || 'fas fa-bell';

  const toneForNotification = (type) => {
    if (type === 'task_assigned') {
      return toneMap.primary;
    }

    if (type === 'deadline_reminder') {
      return toneMap.warning;
    }

    if (type === 'evaluation_new') {
      return toneMap.success;
    }

    if (type === 'announcement') {
      return toneMap.info;
    }

    return toneMap.secondary;
  };

  onMount(async () => {
    const savedTheme = readThemeMode();

    applyThemeMode(savedTheme);
    window.__CMOS_AUTH_PROPS__ = shell;
    await syncUnreadCount();

    if (shellEndpoints.realtimeSnapshot) {
      liveUpdatesCleanup = subscribeToLiveUpdates(
        shellEndpoints.realtimeSnapshot,
        async ({ changed, payload }) => {
          if (!changed.includes('notifications')) {
            return;
          }

          unreadCount = Number(payload.notifications?.unreadCount || 0);

          if (isNotificationsOpen) {
            await loadNotifications();
          }
        },
        { interval: 7000 },
      );
    }

    window.addEventListener('resize', handleResize);
  });

  onDestroy(() => {
    window.removeEventListener('resize', handleResize);
    liveUpdatesCleanup?.();

    if (window.__CMOS_AUTH_PROPS__ === shell) {
      delete window.__CMOS_AUTH_PROPS__;
    }
  });
</script>

<svelte:head>
  <title>{effectiveTitle} - {shellAppName}</title>
  {#if effectiveMeta}
    <meta name="description" content={effectiveMeta} />
  {/if}
</svelte:head>

<div class="min-h-screen bg-background text-foreground lg:grid lg:grid-cols-[248px_minmax(0,1fr)]" use:inertiaEnhance>
  <aside class="hidden h-screen border-r border-sidebar-border bg-sidebar lg:block">
    <SidebarNav appName={shellAppName} organizationName={shellOrganizationName} navSections={shellNavSections} links={shellLinks} />
  </aside>

  <div class="min-w-0">
    <header class="sticky top-0 z-20 border-b border-border bg-background/95 backdrop-blur-sm">
      <div class="flex items-start justify-between gap-4 px-4 py-4 md:px-6 lg:px-8">
        <div class="flex min-w-0 items-start gap-3">
          <Sheet.Root bind:open={isSidebarOpen}>
            <Sheet.Trigger>
              {#snippet child({ props })}
                <button
                  {...props}
                  type="button"
                  class="inline-flex h-11 w-11 items-center justify-center rounded-[10px] border border-border bg-card text-foreground transition-colors hover:bg-muted lg:hidden"
                  aria-label="Buka navigasi"
                >
                  <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
              {/snippet}
            </Sheet.Trigger>

            <Sheet.Content
              side="left"
              showCloseButton={false}
              class="w-[256px] border-r border-sidebar-border bg-sidebar p-0 text-sidebar-foreground sm:max-w-none"
            >
              <SidebarNav
                appName={shellAppName}
                organizationName={shellOrganizationName}
                navSections={shellNavSections}
                links={shellLinks}
                onNavigate={() => (isSidebarOpen = false)}
              />
            </Sheet.Content>
          </Sheet.Root>

          <div class="min-w-0">
            <h1 class="m-0 truncate text-xl font-semibold leading-tight text-foreground md:text-2xl">{effectiveTitle}</h1>
            {#if effectiveMeta}
              <p class="mt-1 max-w-[62ch] text-sm leading-6 text-muted-foreground">{effectiveMeta}</p>
            {/if}
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
          <button
            type="button"
            class="inline-flex h-11 w-11 items-center justify-center rounded-[10px] border border-border bg-card text-foreground transition-colors hover:bg-muted"
            onclick={toggleThemeMode}
            aria-label={themeMode === 'dark' ? 'Aktifkan tema terang' : 'Aktifkan tema gelap'}
            aria-pressed={themeMode === 'light'}
          >
            <i class={`fas ${themeMode === 'dark' ? 'fa-sun' : 'fa-moon'}`} aria-hidden="true"></i>
          </button>

          <NotificationPopover
            open={isNotificationsOpen}
            {unreadCount}
            {notifications}
            isLoading={isLoadingNotifications}
            csrfToken={shellCsrfToken}
            links={shellLinks}
            endpoints={shellEndpoints}
          {formatTime}
          {toneForNotification}
          {iconForNotification}
          onOpenChange={handleNotificationsOpenChange}
          onMarkAllAsRead={markAllNotificationsAsRead}
        />

          <UserMenuDropdown
            open={isUserMenuOpen}
            user={shellUser}
            links={shellLinks}
            csrfToken={shellCsrfToken}
            onOpenChange={handleUserMenuOpenChange}
          />
        </div>
      </div>
    </header>

    <main id="main-content" class="px-4 py-5 md:px-6 lg:px-8">
      <div class="grid gap-4">
        {#if flash.success}
          <div class="alert alert-success animate-fadeIn">
            <i class="fas fa-check-circle"></i>
            <span>{flash.success}</span>
          </div>
        {/if}

        {#if flash.error}
          <div class="alert alert-danger animate-fadeIn">
            <i class="fas fa-exclamation-circle"></i>
            <span>{flash.error}</span>
          </div>
        {/if}

        {#if flash.warning}
          <div class="alert alert-warning animate-fadeIn">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{flash.warning}</span>
          </div>
        {/if}

        {#if flash.info}
          <div class="alert alert-info animate-fadeIn">
            <i class="fas fa-circle-info"></i>
            <span>{flash.info}</span>
          </div>
        {/if}

        {#if errorMessages.length}
          <div class="alert alert-danger animate-fadeIn">
            <i class="fas fa-exclamation-circle"></i>
            <div>
              <strong>Terjadi kesalahan:</strong>
              <ul class="mb-0 mt-1">
                {#each errorMessages as error, index (`${error}-${index}`)}
                  <li>{error}</li>
                {/each}
              </ul>
            </div>
          </div>
        {/if}

        {@render children()}
      </div>
    </main>
  </div>

  <Toaster position="top-right" richColors />
  <FloatingChat quickChat={shellQuickChat} />
</div>
