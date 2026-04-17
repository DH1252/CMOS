<script>
  import { onDestroy, onMount } from 'svelte';
  import { inertiaEnhance } from '$lib/inertia-enhance.js';
  import * as Sheet from '$lib/components/ui/sheet/index.js';
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
  let deferredUiCleanup = $state(null);
  let NotificationPopoverComponent = $state(null);
  let FloatingChatComponent = $state(null);
  let ToasterComponent = $state(null);
  let shellComponentLoads = $state({
    notifications: null,
    floatingChat: null,
  });
  let floatingChatInitiallyOpen = $state(false);
  let shellActivityPrimed = $state(false);
  let shellActivityPromise = $state(null);
  let sonnerLoadPromise = $state(null);
  let liveUpdatesModulePromise = $state(null);

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

  const ensureSonnerLoaded = async () => {
    if (ToasterComponent) {
      return ToasterComponent;
    }

    if (!sonnerLoadPromise) {
      sonnerLoadPromise = Promise.all([
        import('$lib/components/ui/sonner/index.js'),
        import('svelte-sonner'),
      ]).then(([toasterModule, sonnerModule]) => {
        ToasterComponent = toasterModule.Toaster;
        return sonnerModule.toast;
      });
    }

    return sonnerLoadPromise;
  };

  const showShellToastError = async (message, id) => {
    const toast = await ensureSonnerLoaded();
    toast.error(message, { id });
  };

  const ensureLiveUpdatesModule = async () => {
    if (!liveUpdatesModulePromise) {
      liveUpdatesModulePromise = import('$lib/live-updates.js');
    }

    return liveUpdatesModulePromise;
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
          const { subscribeToLiveUpdates } = await ensureLiveUpdatesModule();

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
      void showShellToastError('Gagal memuat notifikasi terbaru.', 'shell-notifications');
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
      void showShellToastError('Tidak bisa menandai semua notifikasi.', 'shell-notifications-mark-all');
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
    deferredUiCleanup = scheduleAfterPaint(() => {
      void ensureSonnerLoaded();
    }, 2500);
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
    deferredUiCleanup?.();
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
              <i class="fas fa-bell" aria-hidden="true"></i>
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
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <span>{flash.success}</span>
          </div>
        {/if}

        {#if flash.error}
          <div class="alert alert-danger animate-fadeIn">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <span>{flash.error}</span>
          </div>
        {/if}

        {#if flash.warning}
          <div class="alert alert-warning animate-fadeIn">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            <span>{flash.warning}</span>
          </div>
        {/if}

        {#if flash.info}
          <div class="alert alert-info animate-fadeIn">
            <i class="fas fa-circle-info" aria-hidden="true"></i>
            <span>{flash.info}</span>
          </div>
        {/if}

        {#if errorMessages.length}
          <div class="alert alert-danger animate-fadeIn">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
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

  {#if ToasterComponent}
    <ToasterComponent position="top-right" richColors />
  {/if}
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
        <i class="fas fa-comments text-lg" aria-hidden="true"></i>
      </button>
    </div>
  {/if}
</div>
