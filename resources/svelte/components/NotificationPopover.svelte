<script>
  import * as Popover from '$lib/components/ui/popover/index.js';

  let {
    open = $bindable(false),
    unreadCount = 0,
    notifications = [],
    isLoading = false,
    csrfToken = '',
    links = {},
    endpoints = {},
    formatTime = (value) => value || '',
    toneForNotification = () => '',
    iconForNotification = () => 'fas fa-bell',
    onOpenChange = () => {},
    onMarkAllAsRead = async () => {},
  } = $props();

  let previousOpen = open;

  const formatNotificationTime = (value) => {
    if (!value) {
      return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return value;
    }

    return date.toLocaleString('id-ID', {
      timeZone: 'Asia/Jakarta',
      day: '2-digit',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  $effect(() => {
    if (open !== previousOpen) {
      previousOpen = open;
      onOpenChange(open);
    }
  });

  const navigate = (href) => {
    if (!href) {
      return;
    }

    open = false;
    window.location.href = href;
  };

  const markAllAsRead = async () => {
    if (!endpoints.notificationsMarkAll) {
      return;
    }

    open = false;
    await onMarkAllAsRead();
  };
</script>

<Popover.Root bind:open>
  <Popover.Trigger>
    {#snippet child({ props })}
      <button {...props} class={`shell-action-btn ${props.class || ''}`} aria-label="Notifikasi">
        <i class="fas fa-bell" aria-hidden="true"></i>
        {#if unreadCount > 0}
          <span class="shell-badge">{unreadCount}</span>
        {/if}
      </button>
    {/snippet}
  </Popover.Trigger>

  <Popover.Content align="end" sideOffset={10} class="w-[min(22rem,calc(100vw-1.5rem))] gap-0 p-0">
    <div class="shell-popover-header">
      <div>
        <strong>Notifikasi</strong>
      </div>
      <button type="button" class="shell-text-btn" onclick={markAllAsRead}>Tandai dibaca</button>
    </div>

    {#if isLoading}
      <div class="shell-empty">Memuat notifikasi...</div>
    {:else if notifications.length === 0}
      <div class="shell-empty">Belum ada notifikasi baru.</div>
    {:else}
      <div class="shell-notification-list">
        {#each notifications as notification (notification.id || `${notification.type}-${notification.created_at}-${notification.title}`)}
          <button type="button" class="shell-notification-item" onclick={() => navigate(notification.href || links.notifications || '#')}>
            <span class={`shell-notification-icon ${toneForNotification(notification.type)}`}>
              <i class={iconForNotification(notification.type)}></i>
            </span>
            <span class="shell-notification-copy">
              <strong>{notification.title}</strong>
              <span>{notification.message}</span>
              <small>{formatNotificationTime(notification.created_at)}</small>
            </span>
          </button>
        {/each}
      </div>
    {/if}

    <button type="button" class="shell-popover-footer" onclick={() => navigate(links.notifications || '#')}>
      Lihat semua notifikasi
    </button>
  </Popover.Content>
</Popover.Root>

<style>
  .shell-action-btn {
    position: relative;
    width: 2.75rem;
    height: 2.75rem;
    border: 1px solid var(--border);
    border-radius: 0.625rem;
    background: var(--card);
    color: var(--foreground);
    display: inline-grid;
    place-items: center;
    cursor: pointer;
    transition: background 160ms ease, border-color 160ms ease;
  }

  .shell-action-btn:hover {
    background: var(--muted);
  }

  .shell-action-btn:focus-visible,
  .shell-text-btn:focus-visible,
  .shell-notification-item:focus-visible,
  .shell-popover-footer:focus-visible {
    outline: 2px solid var(--ring);
    outline-offset: 2px;
  }

  .shell-badge {
    position: absolute;
    top: -0.2rem;
    right: -0.2rem;
    min-width: 1.1rem;
    height: 1.1rem;
    padding: 0 0.2rem;
    display: inline-grid;
    place-items: center;
    border-radius: 999px;
    background: var(--signal-danger);
    color: var(--white-soft);
    font-size: 0.66rem;
    font-weight: 700;
  }

  .shell-popover-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.9rem 1rem;
    border-bottom: 1px solid var(--border);
  }

  .shell-popover-header strong {
    display: block;
    font-size: 0.95rem;
  }
  .shell-text-btn {
    border: none;
    background: transparent;
    color: var(--brand-hover);
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
  }

  .shell-empty {
    padding: 1rem;
    color: var(--text-muted);
    font-size: 0.9rem;
  }

  .shell-notification-list {
    display: grid;
  }

  .shell-notification-item {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.8rem;
    padding: 0.9rem 1rem;
    width: 100%;
    border: 0;
    background: transparent;
    color: inherit;
    text-align: left;
    border-bottom: 1px solid var(--border);
  }

  .shell-notification-item:hover {
    background: var(--muted);
  }

  .shell-notification-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    display: inline-grid;
    place-items: center;
    background: var(--muted);
    color: var(--foreground);
  }

  .shell-notification-copy {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
  }

  .shell-notification-copy strong {
    font-size: 0.9rem;
  }

  .shell-notification-copy span,
  .shell-notification-copy small {
    color: var(--text-muted);
    font-size: 0.82rem;
    line-height: 1.5;
  }

  .shell-popover-footer {
    display: block;
    width: 100%;
    border: 0;
    background: transparent;
    padding: 0.9rem 1rem;
    color: var(--brand-hover);
    text-align: left;
    font-size: 0.84rem;
    font-weight: 600;
  }

  .tone-primary {
    background: color-mix(in srgb, var(--brand-primary) 18%, transparent);
    color: var(--brand-hover);
  }

  .tone-success {
    background: color-mix(in srgb, var(--signal-success) 16%, transparent);
    color: var(--signal-success);
  }

  .tone-warning {
    background: color-mix(in srgb, var(--signal-warning) 16%, transparent);
    color: var(--signal-warning);
  }

  .tone-info {
    background: color-mix(in srgb, var(--signal-info) 16%, transparent);
    color: var(--signal-info);
  }

  .tone-secondary {
    background: var(--muted);
    color: var(--text-muted);
  }
</style>
