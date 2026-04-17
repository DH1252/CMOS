<script>
  import { onDestroy, onMount } from 'svelte';
  import { subscribeToLiveUpdates } from '$lib/live-updates.js';
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import EmptyStatePanel from '../components/EmptyStatePanel.svelte';
  import PageHeader from '../components/PageHeader.svelte';
  import StatusBadge from '../components/StatusBadge.svelte';

  let {
    title = 'Notifikasi',
    description = '',
    csrfToken = '',
    refreshUrl = '',
    realtimeSnapshot = '',
    notifications = $bindable([]),
    unreadCount = $bindable(0),
    pagination = null,
  } = $props();

  let isClearingAll = $state(false);
  let deletingId = $state(null);
  let paginationState = $state(null);
  let liveUpdatesCleanup = $state(null);

  $effect(() => {
    if (!paginationState && pagination) {
      paginationState = pagination;
    }
  });

  const formatRelativeTime = (value) => {
    if (!value) {
      return '-';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return value;
    }

    const diffMinutes = Math.round((date.getTime() - Date.now()) / 60000);

    if (Math.abs(diffMinutes) < 1) {
      return 'baru saja';
    }

    const formatter = new Intl.RelativeTimeFormat('id-ID', { numeric: 'auto' });

    if (Math.abs(diffMinutes) < 60) {
      return formatter.format(diffMinutes, 'minute');
    }

    const diffHours = Math.round(diffMinutes / 60);

    if (Math.abs(diffHours) < 24) {
      return formatter.format(diffHours, 'hour');
    }

    return formatter.format(Math.round(diffHours / 24), 'day');
  };

  const toast = (toastTitle, text, icon = 'success') => {
    if (window.Swal) {
      window.Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title: toastTitle,
        text,
        showConfirmButton: false,
        timer: 2000,
      });
      return;
    }

    if (icon === 'error') {
      window.alert(text);
    }
  };

  const request = async (url, method = 'POST') => {
    const response = await fetch(url, {
      method,
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    if (!response.ok) {
      throw new Error(`Request failed with status ${response.status}`);
    }

    return response;
  };

  const refreshNotifications = async () => {
    if (!refreshUrl) {
      return;
    }

    try {
      const response = await fetch(refreshUrl, {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        cache: 'no-store',
      });

      if (!response.ok) {
        return;
      }

      const data = await response.json();

      notifications = Array.isArray(data.notifications) ? data.notifications : [];
      unreadCount = Number(data.unreadCount || 0);
      paginationState = data.pagination || paginationState;
    } catch (error) {
      console.error('Failed to refresh notifications inbox', error);
    }
  };

  const clearAllNotifications = async () => {
    if (!paginationState?.clearAllUrl || notifications.length < 1 || isClearingAll) {
      return;
    }

    isClearingAll = true;

    try {
      await request(paginationState.clearAllUrl, 'DELETE');
      notifications = [];
      unreadCount = 0;
      toast('Notifikasi dibersihkan', 'Semua notifikasi telah dihapus.');
    } catch (error) {
      toast('Gagal membersihkan', 'Tidak bisa menghapus semua notifikasi.', 'error');
    } finally {
      isClearingAll = false;
    }
  };

  const openNotification = async (item) => {
    try {
      if (!item.readAt) {
        await request(item.readUrl);
        notifications = notifications.map((notification) =>
          notification.id === item.id
            ? { ...notification, readAt: new Date().toISOString() }
            : notification,
        );
        unreadCount = Math.max(unreadCount - 1, 0);
      }
    } catch (error) {
      toast('Status belum tersimpan', 'Notifikasi akan tetap dibuka.', 'error');
    } finally {
      window.location.href = item.href;
    }
  };

  const deleteNotification = async (item) => {
    if (deletingId === item.id) {
      return;
    }

    if (window.Swal) {
      const result = await window.Swal.fire({
        title: 'Hapus notifikasi?',
        text: 'Notifikasi ini akan dihapus dari kotak masuk.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
      });

      if (!result.isConfirmed) {
        return;
      }
    } else if (!window.confirm('Hapus notifikasi ini?')) {
      return;
    }

    deletingId = item.id;

    try {
      await request(item.deleteUrl, 'DELETE');
      notifications = notifications.filter((notification) => notification.id !== item.id);
      if (!item.readAt) {
        unreadCount = Math.max(unreadCount - 1, 0);
      }
      toast('Notifikasi dihapus', 'Item telah dikeluarkan dari kotak masuk.');
    } catch (error) {
      toast('Gagal menghapus', 'Tidak bisa menghapus notifikasi saat ini.', 'error');
    } finally {
      deletingId = null;
    }
  };

  onMount(() => {
    if (!realtimeSnapshot) {
      return;
    }

    liveUpdatesCleanup = subscribeToLiveUpdates(
      realtimeSnapshot,
      async ({ changed }) => {
        if (!changed.includes('notifications')) {
          return;
        }

        await refreshNotifications();
      },
      { interval: 7000 },
    );
  });

  onDestroy(() => {
    liveUpdatesCleanup?.();
  });
</script>

<Card.Root class="animate-fadeIn notification-intro rounded-[10px] border border-border bg-card shadow-none">
  <Card.Header class="notification-intro-head border-b border-border/70 pb-4">
    <div class="notification-intro-copy">
      <PageHeader {title} {description} icon="fas fa-bell" />
    </div>

    <div class="notification-summary">
      <div>
    <span>Belum dibaca</span>
        <strong>{unreadCount}</strong>
      </div>

      {#if notifications.length > 0}
        <Button type="button" variant="secondary" size="sm" onclick={clearAllNotifications} disabled={isClearingAll}>
          <i class="fas fa-trash"></i>
          <span>{isClearingAll ? 'Memproses...' : 'Bersihkan Semua'}</span>
        </Button>
      {/if}
    </div>
  </Card.Header>
</Card.Root>

<section class="notification-list">
  {#if notifications.length}
    {#each notifications as item, index (item.id || index)}
      <article class={`notification-card animate-fadeIn ${item.readAt ? '' : 'notification-card-unread'}`.trim()}>
        <button type="button" class="notification-main" onclick={() => openNotification(item)}>
          <div class={`notification-icon ${item.tone || 'secondary'}`.trim()}>
            <i class={item.icon}></i>
          </div>

          <div class="notification-content">
            <div class="notification-topline">
              <strong>{item.title}</strong>
              {#if !item.readAt}
                <StatusBadge label="Baru" tone="primary" />
              {/if}
            </div>
            <p>{item.message}</p>
            <span>{formatRelativeTime(item.createdAt)}</span>
          </div>
        </button>

        <div class="notification-actions">
          <Button type="button" variant="secondary" size="sm" onclick={() => openNotification(item)}>
            <i class="fas fa-arrow-up-right-from-square"></i>
            <span>Buka</span>
          </Button>
          <Button
            type="button"
            variant="destructive"
            size="sm"
            onclick={() => deleteNotification(item)}
            disabled={deletingId === item.id}
          >
            <i class="fas fa-trash"></i>
            <span>{deletingId === item.id ? 'Menghapus...' : 'Hapus'}</span>
          </Button>
        </div>
      </article>
    {/each}
  {:else}
    <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
      <Card.Content class="pt-5">
        <EmptyStatePanel
          title="Tidak ada notifikasi"
          text="Semua pembaruan akan muncul di sini setelah aktivitas baru masuk."
          icon="fas fa-bell-slash"
          tone="primary"
          compact={true}
        />
      </Card.Content>
    </Card.Root>
  {/if}
</section>

{#if paginationState && paginationState.lastPage > 1}
  <nav class="notification-pagination">
    <Button href={paginationState.previousUrl || undefined} variant="secondary" size="sm" disabled={!paginationState.previousUrl}>
      <i class="fas fa-arrow-left"></i>
      <span>Sebelumnya</span>
    </Button>

    <span>Halaman {paginationState.currentPage} dari {paginationState.lastPage}</span>

    <Button href={paginationState.nextUrl || undefined} variant="secondary" size="sm" disabled={!paginationState.nextUrl}>
      <span>Selanjutnya</span>
      <i class="fas fa-arrow-right"></i>
    </Button>
  </nav>
{/if}

<style>
  .notification-intro,
  .notification-list {
    margin-bottom: 1rem;
  }

  .notification-intro-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
  }

  .notification-intro-copy {
    min-width: 0;
    flex: 1;
  }

  .notification-summary {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0.9rem;
    border: 1px solid color-mix(in srgb, var(--brand-light) 34%, var(--line-soft));
    border-radius: 0.75rem;
    background: color-mix(in srgb, var(--brand-light) 18%, var(--card));
  }

  .notification-summary span {
    display: block;
    color: var(--text-muted);
    font-size: 0.78rem;
  }

  .notification-summary strong {
    display: block;
    margin-top: 0.15rem;
    font-size: 1.6rem;
    line-height: 1;
    color: color-mix(in srgb, var(--brand-hover) 72%, black);
  }

  .notification-list {
    display: grid;
    gap: 0.9rem;
  }

  .notification-card {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 1rem;
    padding: 1rem;
    border-radius: 0.625rem;
    background: var(--card);
    border: 1px solid var(--line-soft);
    box-shadow: none;
  }

  .notification-card-unread {
    background: color-mix(in srgb, var(--brand-light) 20%, var(--card));
    border-color: color-mix(in srgb, var(--brand-primary) 28%, var(--line-soft));
  }

  .notification-main {
    display: grid;
    grid-template-columns: auto 1fr;
    align-items: start;
    gap: 0.95rem;
    padding: 0;
    background: transparent;
    border: none;
    text-align: left;
    color: inherit;
    cursor: pointer;
  }

  .notification-icon {
    width: 3rem;
    height: 3rem;
    display: grid;
    place-items: center;
    border-radius: 0.625rem;
    color: var(--foreground);
    flex-shrink: 0;
  }

  .notification-icon.primary {
    background: color-mix(in srgb, var(--brand-light) 70%, white);
    color: color-mix(in srgb, var(--brand-hover) 78%, black);
  }

  .notification-icon.warning {
    background: color-mix(in srgb, var(--signal-warning) 18%, transparent);
    color: var(--signal-warning);
  }

  .notification-icon.success {
    background: color-mix(in srgb, var(--signal-success) 18%, transparent);
    color: var(--signal-success);
  }

  .notification-icon.info {
    background: color-mix(in srgb, var(--signal-info) 18%, transparent);
    color: var(--signal-info);
  }

  .notification-icon.secondary {
    background: color-mix(in srgb, var(--brand-secondary-soft) 42%, white);
    color: color-mix(in srgb, var(--brand-secondary) 48%, var(--text-strong));
  }

  .notification-content {
    min-width: 0;
  }

  .notification-topline {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
  }

  .notification-content strong {
    font-weight: 600;
    color: var(--text-strong);
  }

  .notification-content p {
    margin: 0.35rem 0 0.55rem;
    color: var(--text-soft);
    line-height: 1.65;
  }

  .notification-content span:last-child {
    color: var(--text-muted);
    font-size: 0.82rem;
  }

  .notification-actions {
    display: flex;
    align-items: center;
    gap: 0.65rem;
  }

  .notification-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 0;
  }

  .notification-pagination span {
    color: var(--text-soft);
    font-size: 0.9rem;
  }

  @media (max-width: 820px) {
    .notification-intro-head,
    .notification-card {
      grid-template-columns: 1fr;
    }

    .notification-summary {
      justify-content: space-between;
      width: 100%;
    }

    .notification-actions {
      width: 100%;
      flex-wrap: wrap;
    }

    .notification-actions :global([data-slot='button']) {
      flex: 1 1 12rem;
    }

    .notification-pagination {
      flex-direction: column;
    }
  }
</style>
