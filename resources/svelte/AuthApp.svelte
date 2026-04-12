<script>
  import { onMount, tick } from 'svelte';
  import { toast } from 'svelte-sonner';
  import * as Sheet from '$lib/components/ui/sheet/index.js';
  import { Toaster } from '$lib/components/ui/sonner/index.js';
  import FloatingChat from './components/FloatingChat.svelte';
  import NotificationPopover from './components/NotificationPopover.svelte';
  import SidebarNav from './components/SidebarNav.svelte';
  import UserMenuDropdown from './components/UserMenuDropdown.svelte';

  let {
    appName = 'CMOS',
    organizationName = 'HIMATEKKOM ITS',
    pageTitle = 'Dashboard',
    pageMeta = '',
    user = {},
    navSections = [],
    palette = {},
    csrfToken = '',
    links = {},
    endpoints = {},
    quickChat = null,
  } = $props();

  let isSidebarOpen = $state(false);
  let isUserMenuOpen = $state(false);
  let isNotificationsOpen = $state(false);
  let unreadCount = $state(0);
  let notifications = $state([]);
  let isLoadingNotifications = $state(false);
  let contentHost = $state(null);
  let themeMode = $state('dark');

  const toneMap = {
    primary: 'tone-primary',
    success: 'tone-success',
    warning: 'tone-warning',
    info: 'tone-info',
    secondary: 'tone-secondary',
  };

  const iconMap = {
    task_assigned: 'fas fa-tasks',
    deadline_reminder: 'fas fa-clock',
    evaluation_new: 'fas fa-star',
    announcement: 'fas fa-bullhorn',
  };

  const applyThemeMode = (value) => {
    themeMode = value;
    document.documentElement.setAttribute('data-theme', value);
  };

  const handleResize = () => {
    if (window.innerWidth >= 1024) {
      isSidebarOpen = false;
    }
  };

  const moveServerContent = async () => {
    await tick();

    const source = document.getElementById('server-page-content');

    if (!source || !contentHost) {
      return;
    }

    const fragment = document.createDocumentFragment();

    Array.from(source.childNodes).forEach((node) => {
      fragment.appendChild(node);
    });

    contentHost.appendChild(fragment);
    source.remove();
    document.body.classList.add('shell-mounted');
    document.dispatchEvent(new CustomEvent('cmos:content-mounted'));
  };

  const syncUnreadCount = async () => {
    if (!endpoints.notificationsUnread) {
      return;
    }

    try {
      const response = await fetch(endpoints.notificationsUnread, {
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
    if (!endpoints.notificationsRecent) {
      return;
    }

    isLoadingNotifications = true;

    try {
      const response = await fetch(endpoints.notificationsRecent, {
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

  const handleUserMenuOpenChange = (value) => {
    isUserMenuOpen = value;

    if (value) {
      isNotificationsOpen = false;
    }
  };

  const toggleThemeMode = () => {
    const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('cmos-theme', next);
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
    const savedTheme = localStorage.getItem('cmos-theme') || 'dark';

    applyThemeMode(savedTheme);

    await moveServerContent();
    await syncUnreadCount();

    window.addEventListener('resize', handleResize);

    return () => {
      window.removeEventListener('resize', handleResize);
    };
  });
</script>

<div class="min-h-screen bg-background text-foreground lg:grid lg:grid-cols-[248px_minmax(0,1fr)]" data-palette={Object.keys(palette || {}).length ? 'custom' : 'default'}>
  <aside class="hidden h-screen border-r border-sidebar-border bg-sidebar lg:block">
    <SidebarNav
      {appName}
      {organizationName}
      {navSections}
      {links}
    />
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
                  class="inline-flex h-10 w-10 items-center justify-center rounded-[10px] border border-border bg-card text-foreground transition-colors hover:bg-muted lg:hidden"
                  aria-label="Open navigation"
                >
                  <i class="fas fa-bars"></i>
                </button>
              {/snippet}
            </Sheet.Trigger>

            <Sheet.Content
              side="left"
              showCloseButton={false}
              class="w-[256px] border-r border-sidebar-border bg-sidebar p-0 text-sidebar-foreground sm:max-w-none"
            >
              <SidebarNav
                {appName}
                {organizationName}
                {navSections}
                {links}
                onNavigate={() => (isSidebarOpen = false)}
              />
            </Sheet.Content>
          </Sheet.Root>

          <div class="min-w-0">
            <h1 class="m-0 truncate text-xl font-semibold leading-tight text-foreground md:text-2xl">{pageTitle}</h1>
            {#if pageMeta}
              <p class="mt-1 max-w-[62ch] text-sm leading-6 text-muted-foreground">{pageMeta}</p>
            {/if}
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
          <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-[10px] border border-border bg-card text-foreground transition-colors hover:bg-muted"
            onclick={toggleThemeMode}
            aria-label="Toggle theme"
          >
            <i class={`fas ${themeMode === 'dark' ? 'fa-sun' : 'fa-moon'}`}></i>
          </button>

          <NotificationPopover
            open={isNotificationsOpen}
            unreadCount={unreadCount}
            notifications={notifications}
            isLoading={isLoadingNotifications}
            {csrfToken}
            {links}
            {endpoints}
            {formatTime}
            {toneForNotification}
            {iconForNotification}
            onOpenChange={handleNotificationsOpenChange}
          />

          <UserMenuDropdown
            open={isUserMenuOpen}
            {user}
            {links}
            {csrfToken}
            onOpenChange={handleUserMenuOpenChange}
          />
        </div>
      </div>
    </header>

    <main class="px-4 py-5 md:px-6 lg:px-8">
      <div bind:this={contentHost} class="min-h-full"></div>
    </main>
  </div>

  <Toaster position="top-right" richColors />
  <FloatingChat {quickChat} />
</div>
