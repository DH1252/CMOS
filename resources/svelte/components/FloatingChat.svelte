<script>
  import { onDestroy, onMount, tick } from 'svelte';
  import { Button } from '$lib/components/ui/button/index.js';
  import { Input } from '$lib/components/ui/input/index.js';
  import StatusBadge from './StatusBadge.svelte';

  export let quickChat = null;

  let isOpen = false;
  let activeUserId = null;
  let activeUser = null;
  let search = '';
  let draft = '';
  let unreadCount = 0;
  let messages = [];
  let users = quickChat?.users?.map((item) => ({ ...item })) || [];
  let summaries = quickChat?.conversations?.map((item) => ({ ...item })) || [];
  let visibleUsers = [];
  let hasLoadedDirectory = users.length > 0;
  let isLoadingDirectory = false;
  let directoryError = '';
  let pollTimer = null;
  let isPollingActive = false;
  let visibilityHandler = null;
  let isSending = false;
  let messageViewport;
  const fallbackAvatar = (name = 'User') => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=251d39&color=f5c518&bold=true`;

  const endpointFor = (base, id) => `${String(base || '').replace(/\/$/, '')}/${id}`;

  const sortedUsers = (items) =>
    [...items].sort((left, right) => {
      if (left.unreadCount !== right.unreadCount) {
        return right.unreadCount - left.unreadCount;
      }

      const leftTime = left.lastMessageAt ? new Date(left.lastMessageAt).getTime() : 0;
      const rightTime = right.lastMessageAt ? new Date(right.lastMessageAt).getTime() : 0;

      if (leftTime !== rightTime) {
        return rightTime - leftTime;
      }

      return left.name.localeCompare(right.name, 'id');
    });

  const buildUsers = () => {
    const summaryMap = new Map(
      (summaries || []).map((summary) => [Number(summary.id), summary]),
    );

    return sortedUsers(
      users.map((user) => {
        const summary = summaryMap.get(Number(user.id)) || {};
        return {
          ...user,
          unreadCount: summary.unreadCount || 0,
          lastMessage: summary.lastMessage || '',
          lastMessageAt: summary.lastMessageAt || null,
        };
      }),
    );
  };

  const ensureDirectoryData = async () => {
    if (hasLoadedDirectory || isLoadingDirectory || !quickChat?.endpoints?.sidebarData) {
      return;
    }

    isLoadingDirectory = true;
    directoryError = '';

    try {
      const response = await fetch(quickChat.endpoints.sidebarData, {
        headers: {
          Accept: 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error('Failed to load chat directory');
      }

      const data = await response.json();
      users = Array.isArray(data.users) ? data.users : [];
      summaries = Array.isArray(data.conversations)
        ? data.conversations.map((item) => ({ ...item }))
        : [];
      hasLoadedDirectory = true;
    } catch (error) {
      console.error('Failed to load floating chat directory', error);
      directoryError = 'Kontak belum dapat dimuat.';
    } finally {
      isLoadingDirectory = false;
    }
  };

  const previewText = (item) => {
    const text = item.lastMessage || 'Belum ada pesan';
    return text.length > 34 ? `${text.slice(0, 33)}...` : text;
  };

  const formatTime = (value) => {
    if (!value) {
      return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return value;
    }

    return date.toLocaleTimeString('id-ID', {
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || 'User');

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };

  const scrollToBottom = async () => {
    await tick();
    if (messageViewport) {
      messageViewport.scrollTop = messageViewport.scrollHeight;
    }
  };

  const syncUnreadBadge = async () => {
    if (!quickChat?.endpoints?.unread) {
      return;
    }

    try {
      const response = await fetch(quickChat.endpoints.unread, {
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
      console.error('Failed to fetch message unread count', error);
    }
  };

  const syncSummary = (userId, payload) => {
    const lastMessage = payload[payload.length - 1] || null;
    const existing = summaries.find((item) => Number(item.id) === Number(userId));
    const nextSummary = {
      ...(existing || {}),
      id: Number(userId),
      unreadCount: 0,
      lastMessage: lastMessage?.content || existing?.lastMessage || '',
      lastMessageAt: lastMessage?.created_at || existing?.lastMessageAt || null,
    };

    summaries = sortedUsers([
      nextSummary,
      ...summaries.filter((item) => Number(item.id) !== Number(userId)),
    ]);
  };

  const loadConversation = async (userId, shouldScroll = true) => {
    if (!quickChat?.endpoints?.conversationBase || !userId) {
      return;
    }

    try {
      const response = await fetch(endpointFor(quickChat.endpoints.conversationBase, userId), {
        headers: {
          Accept: 'application/json',
        },
      });

      if (!response.ok) {
        return;
      }

      const data = await response.json();
      activeUserId = Number(userId);
      activeUser = {
        ...(users.find((user) => Number(user.id) === Number(userId)) || {}),
        ...(data.user || {}),
      };
      messages = Array.isArray(data.messages) ? data.messages : [];
      syncSummary(userId, messages);
      unreadCount = Math.max(unreadCount - 1, 0);

      if (shouldScroll) {
        await scrollToBottom();
      }
    } catch (error) {
      console.error('Failed to load floating chat conversation', error);
    }
  };

  const sendMessage = async () => {
    if (!activeUserId || !draft.trim() || isSending) {
      return;
    }

    isSending = true;

    try {
      const response = await fetch(endpointFor(quickChat.endpoints.sendBase, activeUserId), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': quickChat.csrfToken,
        },
        body: JSON.stringify({
          content: draft.trim(),
        }),
      });

      if (!response.ok) {
        return;
      }

      draft = '';
      await loadConversation(activeUserId);
      await syncUnreadBadge();
    } catch (error) {
      console.error('Failed to send floating chat message', error);
    } finally {
      isSending = false;
    }
  };

  const toggleChat = async () => {
    isOpen = !isOpen;
    if (isOpen) {
      await Promise.all([syncUnreadBadge(), ensureDirectoryData()]);
    }
  };

  const backToList = () => {
    activeUserId = null;
    activeUser = null;
    messages = [];
  };

  const startPolling = () => {
    if (pollTimer || isPollingActive) {
      return;
    }

    isPollingActive = true;

    const scheduleNext = () => {
      if (!isPollingActive) {
        return;
      }

      pollTimer = window.setTimeout(async () => {
        if (!isPollingActive) {
          return;
        }

        if (!document.hidden) {
          await syncUnreadBadge();

          if (isOpen && activeUserId) {
            await loadConversation(activeUserId, false);
          }
        }

        scheduleNext();
      }, 15000);
    };

    scheduleNext();
  };

  const stopPolling = () => {
    isPollingActive = false;

    if (!pollTimer) {
      return;
    }

    window.clearTimeout(pollTimer);
    pollTimer = null;
  };

  $: {
    const allUsers = buildUsers();
    const keyword = search.trim().toLowerCase();

    visibleUsers = keyword === ''
      ? allUsers
      : allUsers.filter((user) => {
          const haystack = `${user.name} ${user.role || ''} ${user.department || ''}`.toLowerCase();

          return haystack.includes(keyword);
        });
  }

  $: if (isOpen && !document.hidden) {
    startPolling();
  } else {
    stopPolling();
  }

  onMount(() => {
    syncUnreadBadge();

    visibilityHandler = () => {
      if (document.hidden) {
        stopPolling();
        return;
      }

      syncUnreadBadge();
      if (isOpen && activeUserId) {
        loadConversation(activeUserId, false);
      }
    };

    document.addEventListener('visibilitychange', visibilityHandler);
  });

  onDestroy(() => {
    if (visibilityHandler) {
      document.removeEventListener('visibilitychange', visibilityHandler);
    }
    stopPolling();
  });
</script>

{#if quickChat}
  <div class="floating-chat-shell">
    <button type="button" class="floating-chat-toggle" onclick={toggleChat} aria-label="Pesan cepat">
      <i class="fas fa-comments"></i>
      {#if unreadCount > 0}
        <span class="floating-chat-badge">{unreadCount}</span>
      {/if}
    </button>

    {#if isOpen}
      <section class="floating-chat-panel">
        <header class="floating-chat-head">
          {#if activeUser}
            <div class="floating-chat-head-user">
              <button type="button" class="floating-chat-icon" onclick={backToList} aria-label="Kembali">
                <i class="fas fa-arrow-left"></i>
              </button>
              <img src={activeUser.avatar || fallbackAvatar(activeUser.name)} alt={activeUser.name} class="avatar-sm" onerror={handleImageError} />
              <div>
                <strong>{activeUser.name}</strong>
                <span>{activeUser.role || 'Kontak organisasi'}</span>
              </div>
            </div>
          {:else}
            <div>
              <strong>Pesan cepat</strong>
              <span>Koordinasi singkat tanpa pindah halaman.</span>
            </div>
          {/if}

          <button type="button" class="floating-chat-icon" onclick={toggleChat} aria-label="Tutup">
            <i class="fas fa-xmark"></i>
          </button>
        </header>

        {#if activeUser}
          <div bind:this={messageViewport} class="floating-chat-messages">
            {#if messages.length}
              {#each messages as message}
                <article class={`floating-chat-bubble ${message.is_mine ? 'floating-chat-bubble-mine' : 'floating-chat-bubble-theirs'}`}>
                  <p>{message.content}</p>
                  <span>{formatTime(message.created_at)}</span>
                </article>
              {/each}
            {:else}
              <div class="floating-chat-empty">
                <i class="fas fa-comments"></i>
                <p>Belum ada pesan. Mulai percakapan baru sekarang.</p>
              </div>
            {/if}
          </div>

          <form class="floating-chat-composer" onsubmit={(event) => {
            event.preventDefault();
            void sendMessage();
          }}>
            <Input type="text" class="floating-chat-composer-input" placeholder={`Tulis pesan untuk ${activeUser.name}...`} bind:value={draft} />
            <Button
              type="submit"
              size="icon-sm"
              class="floating-chat-send"
              aria-label="Kirim pesan"
              title="Kirim pesan"
              disabled={!draft.trim() || isSending}
            >
              <i class="fas fa-paper-plane"></i>
            </Button>
          </form>
        {:else}
          <div class="floating-chat-list-shell">
            <label class="floating-chat-search">
              <i class="fas fa-magnifying-glass"></i>
              <Input type="search" class="floating-chat-search-input" placeholder="Cari user..." bind:value={search} />
            </label>

            <div class="floating-chat-user-list">
              {#if isLoadingDirectory}
                <div class="floating-chat-empty">
                  <i class="fas fa-spinner fa-spin"></i>
                  <p>Memuat kontak...</p>
                </div>
              {:else if directoryError}
                <div class="floating-chat-empty">
                  <i class="fas fa-circle-exclamation"></i>
                  <p>{directoryError}</p>
                </div>
              {:else if visibleUsers.length}
                {#each visibleUsers as user}
                  <button type="button" class="floating-chat-user" onclick={() => loadConversation(user.id)}>
                    <img src={user.avatar || fallbackAvatar(user.name)} alt={user.name} class="avatar-sm" onerror={handleImageError} />
                    <div class="floating-chat-user-copy">
                      <div class="floating-chat-user-topline">
                        <strong>{user.name}</strong>
                        {#if user.unreadCount > 0}
                          <StatusBadge label={String(user.unreadCount)} tone="primary" />
                        {/if}
                      </div>
                      <span>{user.role || 'Anggota'}</span>
                      <p>{previewText(user)}</p>
                    </div>
                  </button>
                {/each}
              {:else}
                <div class="floating-chat-empty">
                  <i class="fas fa-users-slash"></i>
                  <p>Tidak ada kontak yang cocok.</p>
                </div>
              {/if}
            </div>

            <Button href={quickChat.link} variant="secondary" size="sm" class="floating-chat-link-button">
              <i class="fas fa-up-right-from-square"></i>
              <span>Buka Halaman Pesan</span>
            </Button>
          </div>
        {/if}
      </section>
    {/if}
  </div>
{/if}

<style>
  .floating-chat-shell {
    position: fixed;
    right: 1.4rem;
    bottom: 1.4rem;
    z-index: 40;
  }

  .floating-chat-toggle {
    position: relative;
    width: 3.9rem;
    height: 3.9rem;
    display: grid;
    place-items: center;
    border: 1px solid var(--border);
    border-radius: 0.625rem;
    background: var(--brand-primary);
    color: #1a1a2e;
    cursor: pointer;
    box-shadow: none;
  }

  .floating-chat-badge {
    position: absolute;
    top: -0.3rem;
    right: -0.3rem;
    min-width: 1.4rem;
    height: 1.4rem;
    padding: 0 0.3rem;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: var(--signal-danger);
    color: var(--white-soft);
    font-size: 0.72rem;
    font-weight: 800;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--page-bg) 84%, transparent);
  }

  .floating-chat-panel {
    position: absolute;
    right: 0;
    bottom: calc(100% + 0.9rem);
    width: min(24rem, calc(100vw - 2rem));
    height: min(34rem, calc(100vh - 8rem));
    display: grid;
    grid-template-rows: auto 1fr auto;
    border-radius: 0.625rem;
    border: 1px solid var(--line-soft);
    background: var(--panel-bg);
    box-shadow: none;
    overflow: hidden;
  }

  .floating-chat-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    padding: 1rem 1rem 0.9rem;
    background: var(--panel-bg);
    border-bottom: 1px solid var(--line-soft);
  }

  .floating-chat-head strong {
    display: block;
    font-weight: 600;
  }

  .floating-chat-head span {
    display: block;
    color: var(--text-muted);
    font-size: 0.8rem;
  }

  .floating-chat-head-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
  }

  .floating-chat-icon {
    width: 2.2rem;
    height: 2.2rem;
    display: grid;
    place-items: center;
    border-radius: 0.5rem;
    border: 1px solid var(--line-soft);
    background: color-mix(in srgb, var(--panel-bg) 90%, white);
    color: var(--text-strong);
    cursor: pointer;
  }

  .floating-chat-icon:focus-visible,
  .floating-chat-user:focus-visible {
    outline: 2px solid color-mix(in srgb, var(--brand-primary) 48%, transparent);
    outline-offset: 2px;
  }

  .floating-chat-list-shell {
    display: grid;
    gap: 0.85rem;
    padding: 1rem;
    min-height: 0;
  }

  .floating-chat-search {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.8rem 0.9rem;
    border-radius: 0.5rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
    color: var(--text-muted);
  }

  :global(.floating-chat-search-input) {
    flex: 1;
    border: none;
    background: transparent;
    color: var(--text-strong);
    box-shadow: none;
  }

  :global(.floating-chat-search-input:focus) {
    box-shadow: none;
  }

  .floating-chat-user-list {
    display: grid;
    gap: 0.65rem;
    align-content: start;
    overflow-y: auto;
    min-height: 0;
  }

  .floating-chat-user {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.75rem;
    align-items: start;
    padding: 0.8rem 0.85rem;
    border-radius: 0.5rem;
    border: 1px solid var(--line-soft);
    background: var(--background);
    color: inherit;
    text-align: left;
    cursor: pointer;
  }

  .floating-chat-user:hover {
    background: var(--muted);
    border-color: color-mix(in srgb, var(--brand-primary) 18%, var(--line-soft));
  }

  .floating-chat-user-copy {
    min-width: 0;
  }

  .floating-chat-user-topline {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    justify-content: space-between;
  }

  .floating-chat-user-copy span {
    display: block;
    margin-top: 0.12rem;
    color: var(--text-muted);
    font-size: 0.76rem;
  }

  .floating-chat-user-copy p {
    margin: 0.3rem 0 0;
    color: var(--text-soft);
    font-size: 0.82rem;
  }

  .floating-chat-messages {
    display: grid;
    gap: 0.7rem;
    align-content: start;
    min-height: 0;
    overflow-y: auto;
    padding: 1rem;
    background: var(--background);
  }

  .floating-chat-bubble {
    max-width: 78%;
    padding: 0.75rem 0.9rem;
    border-radius: 0.5rem;
    box-shadow: none;
  }

  .floating-chat-bubble p {
    margin: 0;
    white-space: pre-wrap;
    line-height: 1.55;
  }

  .floating-chat-bubble span {
    display: block;
    margin-top: 0.4rem;
    font-size: 0.72rem;
    opacity: 0.72;
  }

  .floating-chat-bubble-mine {
    justify-self: end;
    background: var(--brand-primary);
    color: #1a1a2e;
    border-bottom-right-radius: 0.3rem;
  }

  .floating-chat-bubble-theirs {
    background: var(--card);
    border: 1px solid var(--line-soft);
    color: var(--text-strong);
    border-bottom-left-radius: 0.3rem;
  }

  .floating-chat-composer {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.9rem 1rem 1rem;
    border-top: 1px solid var(--line-soft);
  }

  :global(.floating-chat-composer-input) {
    flex: 1;
    background: color-mix(in srgb, var(--panel-bg) 92%, white);
  }

  :global(.floating-chat-send) {
    width: 2.7rem;
    height: 2.7rem;
    border-radius: 0.5rem;
    background: var(--brand-primary);
    color: #1a1a2e;
    box-shadow: none;
  }

  :global(.floating-chat-send:disabled) {
    opacity: 0.55;
    cursor: default;
  }

  :global(.floating-chat-link-button) {
    width: 100%;
    box-shadow: none;
  }

  .floating-chat-empty {
    display: grid;
    place-items: center;
    gap: 0.4rem;
    text-align: center;
    color: var(--text-muted);
    padding: 2rem 1rem;
  }

  .floating-chat-empty i {
    font-size: 1.8rem;
  }

  @media (max-width: 720px) {
    .floating-chat-shell {
      right: 1rem;
      bottom: 1rem;
    }

    .floating-chat-panel {
      width: min(22rem, calc(100vw - 1rem));
      right: -0.2rem;
      bottom: calc(100% + 0.7rem);
    }
  }
</style>
