<script>
  import { onDestroy, onMount } from 'svelte';
  import { toast } from 'svelte-sonner';
  import { subscribeToLiveUpdates } from '$lib/live-updates.js';
  import { inertiaEnhance } from '$lib/inertia-enhance.js';
  import * as Sheet from '$lib/components/ui/sheet/index.js';
  import { Toaster } from '$lib/components/ui/sonner/index.js';
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
  let deferredShellBootCleanup = $state(null);
  let NotificationPopoverComponent = $state(null);
  let FloatingChatComponent = $state(null);
  let shellComponentLoads = $state({
    notifications: null,
    floatingChat: null,
  });
  let floatingChatInitiallyOpen = $state(false);
  let shellActivityPrimed = $state(false);
  let shellActivityPromise = $state(null);

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

  const scheduleAfterPaint = (callback, timeout = 1200) => {
    if (typeof window === 'undefined') {
      callback();
      return () => {};
    }

    if (typeof window.requestIdleCallback === 'function') {
      const handle = window.requestIdleCallback(() => callback(), { timeout });

      return () => window.cancelIdleCallback?.(handle);
    }

    const handle = window.setTimeout(callback, timeout);

    return () => window.clearTimeout(handle);
  };

  const ensureNotificationPopoverLoaded = async () => {
    if (NotificationPopoverComponent) {
      return NotificationPopoverComponent;
    }

    if (!shellComponentLoads.notifications) {
      shellComponentLoads = {
        ...shellComponentLoads,
        notifications: import('../components/NotificationPopover.svelte').then((module) => {
          NotificationPopoverComponent = module.default;
          return module.default;
        }),
      };
    }

    return shellComponentLoads.notifications;
  };

  const ensureFloatingChatLoaded = async () => {
    if (FloatingChatComponent) {
      return FloatingChatComponent;
    }

    if (!shellComponentLoads.floatingChat) {
      shellComponentLoads = {
        ...shellComponentLoads,
        floatingChat: import('../components/FloatingChat.svelte').then((module) => {
          FloatingChatComponent = module.default;
          return module.default;
        }),
      };
    }

    return shellComponentLoads.floatingChat;
  };

  const primeShellActivity = async () => {
    if (shellActivityPrimed) {
      return;
    }

    if (!shellActivityPromise) {
      shellActivityPromise = (async () => {
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

        shellActivityPrimed = true;
      })();
    }

    await shellActivityPromise;
  };

  const openNotifications = async () => {
    await primeShellActivity();
    await ensureNotificationPopoverLoaded();
    isNotificationsOpen = true;
    isUserMenuOpen = false;
  };

  const openFloatingChat = async () => {
    floatingChatInitiallyOpen = true;
    await ensureFloatingChatLoaded();
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
    const interactionEvents = ['pointerdown', 'keydown', 'touchstart'];

    const activateShellActivity = () => {
      void primeShellActivity();

      interactionEvents.forEach((eventName) => {
        window.removeEventListener(eventName, activateShellActivity);
      });
    };

    interactionEvents.forEach((eventName) => {
      window.addEventListener(eventName, activateShellActivity, { once: true, passive: true });
    });

    deferredShellBootCleanup = scheduleAfterPaint(() => {
      activateShellActivity();
    }, 14000);

    window.addEventListener('resize', handleResize);

    return () => {
      interactionEvents.forEach((eventName) => {
        window.removeEventListener(eventName, activateShellActivity);
      });
    };
  });

  onDestroy(() => {
    window.removeEventListener('resize', handleResize);
    deferredShellBootCleanup?.();
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
                  <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current" stroke-width="1.8" stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16"></path>
                  </svg>
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
            {#if themeMode === 'dark'}
              <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="4.5"></circle>
                <path d="M12 2.5v2.2M12 19.3v2.2M4.9 4.9l1.6 1.6M17.5 17.5l1.6 1.6M2.5 12h2.2M19.3 12h2.2M4.9 19.1l1.6-1.6M17.5 6.5l1.6-1.6"></path>
              </svg>
            {:else}
              <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.8A8.8 8.8 0 1 1 11.2 3 7 7 0 0 0 21 12.8z"></path>
              </svg>
            {/if}
          </button>

          {#if NotificationPopoverComponent}
            <NotificationPopoverComponent
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
          {:else}
            <button
              type="button"
              class="relative inline-grid h-11 w-11 place-items-center rounded-[10px] border border-border bg-card text-foreground transition-colors hover:bg-muted"
              onclick={() => void openNotifications()}
              aria-label="Notifikasi"
            >
              <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18H9"></path>
                <path d="M18 16.5H6l1.2-1.4c.6-.7.8-1.6.8-2.5V10a4 4 0 1 1 8 0v2.6c0 .9.3 1.8.8 2.5L18 16.5z"></path>
              </svg>
              {#if unreadCount > 0}
                <span class="absolute -right-[0.2rem] -top-[0.2rem] inline-grid h-[1.1rem] min-w-[1.1rem] place-items-center rounded-full bg-[var(--signal-danger)] px-[0.2rem] text-[0.66rem] font-bold text-[var(--white-soft)]">{unreadCount}</span>
              {/if}
            </button>
          {/if}

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
            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 shrink-0 fill-none stroke-current" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 7 10 17l-5-5"></path>
            </svg>
            <span>{flash.success}</span>
          </div>
        {/if}

        {#if flash.error}
          <div class="alert alert-danger animate-fadeIn">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 shrink-0 fill-none stroke-current" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="9"></circle>
              <path d="M12 8v5"></path>
              <path d="M12 16h.01"></path>
            </svg>
            <span>{flash.error}</span>
          </div>
        {/if}

        {#if flash.warning}
          <div class="alert alert-warning animate-fadeIn">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 shrink-0 fill-none stroke-current" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 4 3.5 19h17L12 4z"></path>
              <path d="M12 9v4"></path>
              <path d="M12 16h.01"></path>
            </svg>
            <span>{flash.warning}</span>
          </div>
        {/if}

        {#if flash.info}
          <div class="alert alert-info animate-fadeIn">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 shrink-0 fill-none stroke-current" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="9"></circle>
              <path d="M12 10.5v4"></path>
              <path d="M12 8h.01"></path>
            </svg>
            <span>{flash.info}</span>
          </div>
        {/if}

        {#if errorMessages.length}
          <div class="alert alert-danger animate-fadeIn">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 shrink-0 fill-none stroke-current" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="9"></circle>
              <path d="M12 8v5"></path>
              <path d="M12 16h.01"></path>
            </svg>
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
  {#if FloatingChatComponent}
    <FloatingChatComponent quickChat={shellQuickChat} initiallyOpen={floatingChatInitiallyOpen} />
  {:else if shellQuickChat}
        <div class="fixed bottom-4 right-4 z-40 md:bottom-6 md:right-6">
          <button
            type="button"
            class="relative inline-grid h-14 w-14 place-items-center rounded-full bg-[var(--brand-primary)] text-[var(--primary-foreground)] shadow-lg transition-transform hover:scale-[1.02]"
            onclick={() => void openFloatingChat()}
            aria-label="Pesan cepat"
          >
        <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 10h10M7 14h6"></path>
          <path d="M5 18.5V6.8A2.8 2.8 0 0 1 7.8 4h8.4A2.8 2.8 0 0 1 19 6.8v5.9a2.8 2.8 0 0 1-2.8 2.8H10l-5 3z"></path>
        </svg>
          </button>
        </div>
  {/if}
</div>
