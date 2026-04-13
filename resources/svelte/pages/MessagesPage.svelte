<script>
  import { onDestroy, onMount, tick } from 'svelte';
  import { subscribeToLiveUpdates } from '$lib/live-updates.js';
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import { Input } from '$lib/components/ui/input/index.js';
  import EmptyStatePanel from '../components/EmptyStatePanel.svelte';
  import PageHeader from '../components/PageHeader.svelte';
  import StatusBadge from '../components/StatusBadge.svelte';

  let {
    title = 'Pesan Internal',
    description = '',
    csrfToken = '',
    contacts = [],
    conversations = [],
    endpoints = {},
    initialUserId = null,
  } = $props();

  const fallbackAvatar = (name = 'User') => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=251d39&color=f5c518&bold=true`;

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || 'User');

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };

  let search = $state('');
  let draft = $state('');
  let activeUserId = $state(null);
  let activeUser = $state(null);
  let contactDirectory = $state([]);
  let messages = $state([]);
  let conversationSummaries = $state([]);
  let isLoadingConversation = $state(false);
  let isSending = $state(false);
  let liveUpdatesCleanup = $state(null);
  let messageViewport = $state(null);

  $effect(() => {
    if (!contactDirectory.length && contacts.length) {
      contactDirectory = contacts.map((item) => ({ ...item }));
    }
  });

  $effect(() => {
    if (!conversationSummaries.length && conversations.length) {
      conversationSummaries = conversations.map((item) => ({ ...item }));
    }
  });

  $effect(() => {
    if (!activeUserId) {
      activeUserId = initialUserId || conversations[0]?.id || contacts[0]?.id || null;
    }
  });

  const endpointFor = (base, id) => `${String(base || '').replace(/\/$/, '')}/${id}`;

  const sortContacts = (items) =>
    [...items].sort((left, right) => {
      if (left.unreadCount !== right.unreadCount) {
        return right.unreadCount - left.unreadCount;
      }

      const leftTime = left.lastMessageAt ? new Date(left.lastMessageAt).getTime() : 0;
      const rightTime = right.lastMessageAt ? new Date(right.lastMessageAt).getTime() : 0;

      if (leftTime !== rightTime) {
        return rightTime - leftTime;
      }

      return (left.name || 'Kontak').localeCompare(right.name || 'Kontak', 'id');
    });

  const buildContacts = () => {
    const summaryMap = new Map((conversationSummaries || []).map((summary) => [Number(summary.id), summary]));

    return sortContacts(
      contactDirectory.map((contact) => {
        const summary = summaryMap.get(Number(contact.id)) || {};
        return {
          ...contact,
          unreadCount: summary.unreadCount || 0,
          lastMessage: summary.lastMessage || '',
          lastMessageAt: summary.lastMessageAt || null,
          lastMessageLabel: summary.lastMessageLabel || '',
        };
      }),
    );
  };

  const visibleContacts = $derived.by(() => {
    const allContacts = buildContacts();
    const keyword = search.trim().toLowerCase();

    if (keyword === '') {
      return allContacts;
    }

    return allContacts.filter((contact) => {
      const haystack = `${contact.name} ${contact.role || ''} ${contact.department || ''}`.toLowerCase();

      return haystack.includes(keyword);
    });
  });

  const formatMessageTime = (value) => {
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

  const formatMessageDay = (value) => {
    if (!value) {
      return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return value;
    }

    return date.toLocaleDateString('id-ID', {
      day: '2-digit',
      month: 'short',
    });
  };

  const groupedMessages = $derived.by(() =>
    messages.reduce((groups, message) => {
      const groupDate = formatMessageDay(message.created_at);
      const lastGroup = groups[groups.length - 1];
      if (!lastGroup || lastGroup.date !== groupDate) {
        groups.push({
          date: groupDate,
          items: [message],
        });
        return groups;
      }

      lastGroup.items.push(message);
      return groups;
    }, []),
  );

  const previewText = (item) => {
    const text = item.lastMessage || 'Belum ada pesan';
    return text.length > 52 ? `${text.slice(0, 51)}...` : text;
  };

  const scrollToBottom = async () => {
    await tick();
    if (messageViewport) {
      messageViewport.scrollTop = messageViewport.scrollHeight;
    }
  };

  $effect(() => {
    const activeConversationId = activeUserId;
    const viewport = messageViewport;
    const totalMessages = messages.length;
    const lastMessageKey = messages[totalMessages - 1]?.id || messages[totalMessages - 1]?.created_at || null;

    if (!activeConversationId || !viewport) {
      return;
    }

    void lastMessageKey;
    void scrollToBottom();
  });

  const syncSummary = (userId, payload) => {
    const lastMessage = payload[payload.length - 1] || null;
    const existing = conversationSummaries.find((item) => Number(item.id) === Number(userId));
    const contact = contactDirectory.find((item) => Number(item.id) === Number(userId));
    const nextSummary = {
      ...(contact || {}),
      ...(existing || {}),
      id: Number(userId),
      name: existing?.name || contact?.name || activeUser?.name || 'Kontak',
      avatar: existing?.avatar || contact?.avatar || activeUser?.avatar || null,
      role: existing?.role || contact?.role || activeUser?.role || '',
      department: existing?.department || contact?.department || activeUser?.department || null,
      lastMessage: lastMessage?.content || existing?.lastMessage || '',
      lastMessageAt: lastMessage?.created_at_raw || lastMessage?.created_at || existing?.lastMessageAt || null,
      lastMessageLabel: lastMessage?.created_at || existing?.lastMessageLabel || '',
      unreadCount: 0,
    };

    conversationSummaries = sortContacts([
      nextSummary,
      ...conversationSummaries.filter((item) => Number(item.id) !== Number(userId)),
    ]);
  };

  const loadConversation = async (userId, shouldScroll = true) => {
    if (!userId) {
      return;
    }

    isLoadingConversation = true;

    try {
      const response = await fetch(endpointFor(endpoints.conversationBase, userId), {
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
        ...(contactDirectory.find((contact) => Number(contact.id) === Number(userId)) || {}),
        ...(data.user || {}),
      };
      messages = Array.isArray(data.messages) ? data.messages : [];
      syncSummary(userId, messages);

      if (shouldScroll) {
        await scrollToBottom();
      }
    } catch (error) {
      console.error('Failed to load conversation', error);
    } finally {
      isLoadingConversation = false;
    }
  };

  const loadSidebarData = async () => {
    if (!endpoints.sidebarData) {
      return;
    }

    try {
      const response = await fetch(endpoints.sidebarData, {
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

      contactDirectory = Array.isArray(data.users)
        ? data.users.map((item) => ({ ...item }))
        : [];
      conversationSummaries = Array.isArray(data.conversations)
        ? sortContacts(data.conversations.map((item) => ({ ...item })))
        : [];
    } catch (error) {
      console.error('Failed to refresh message sidebar', error);
    }
  };

  const sendMessage = async () => {
    if (!activeUserId || !draft.trim() || isSending) {
      return;
    }

    isSending = true;

    try {
      const response = await fetch(endpointFor(endpoints.sendBase, activeUserId), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken,
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
    } catch (error) {
      console.error('Failed to send message', error);
    } finally {
      isSending = false;
    }
  };

  onMount(async () => {
    if (!activeUserId && contacts[0]?.id) {
      activeUserId = contacts[0].id;
    }

    if (activeUserId) {
      await loadConversation(activeUserId);
    }

    liveUpdatesCleanup = subscribeToLiveUpdates(
      endpoints.realtimeSnapshot,
      async ({ changed }) => {
        if (!changed.includes('messages')) {
          return;
        }

        await loadSidebarData();

        if (activeUserId) {
          await loadConversation(activeUserId, false);
        }
      },
      { interval: 7000 },
    );
  });

  onDestroy(() => {
    liveUpdatesCleanup?.();
  });
</script>

<Card.Root class="animate-fadeIn messages-intro rounded-[10px] border border-border bg-card shadow-none">
  <Card.Header class="border-b border-border/70 pb-4">
    <PageHeader {title} {description} icon="fas fa-comments" />
  </Card.Header>
</Card.Root>

<div class="messages-layout">
  <Card.Root class="animate-fadeIn messages-sidebar rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader
        title="Kontak Organisasi"
        icon="fas fa-user-group"
        compact={true}
        headingTag="h3"
      />
    </Card.Header>

    <Card.Content class="messages-sidebar-body pt-5">
      <div class="messages-search">
        <i class="fas fa-magnifying-glass"></i>
        <Input
          type="search"
          class="messages-search-input"
          placeholder="Cari nama, role, atau departemen..."
          bind:value={search}
        />
      </div>

      <div class="messages-contact-list">
        {#if visibleContacts.length}
          {#each visibleContacts as contact, index (contact.id || index)}
            <button
              type="button"
              class={`messages-contact ${Number(activeUserId) === Number(contact.id) ? 'messages-contact-active' : ''}`.trim()}
              onclick={() => loadConversation(contact.id)}
            >
              <img src={contact.avatar || fallbackAvatar(contact.name)} alt={contact.name} class="avatar-md" onerror={handleImageError} />
              <div class="messages-contact-copy">
                <div class="messages-contact-topline">
                  <strong>{contact.name}</strong>
                  {#if contact.unreadCount > 0}
                    <StatusBadge label={String(contact.unreadCount)} tone="primary" className="messages-count-badge" />
                  {/if}
                </div>
                <span>{contact.role}{contact.department ? ` • ${contact.department}` : ''}</span>
                <p>{previewText(contact)}</p>
              </div>
            </button>
          {/each}
        {:else}
          <EmptyStatePanel
            title="Kontak tidak ditemukan"
            text="Tidak ada kontak yang sesuai dengan pencarian."
            icon="fas fa-user-slash"
            tone="secondary"
            compact={true}
          />
        {/if}
      </div>
    </Card.Content>
  </Card.Root>

  <Card.Root class="animate-fadeIn messages-conversation rounded-[10px] border border-border bg-card shadow-none">
    {#if activeUser}
      <div class="messages-thread-head">
        <div class="messages-thread-user">
          <img src={activeUser.avatar || fallbackAvatar(activeUser.name)} alt={activeUser.name} class="avatar-md" onerror={handleImageError} />
          <div>
            <strong>{activeUser.name}</strong>
            <span>{activeUser.role}{activeUser.department ? ` • ${activeUser.department}` : ''}</span>
          </div>
        </div>
      </div>

      <div bind:this={messageViewport} class="messages-thread-body">
        {#if isLoadingConversation && !messages.length}
          <EmptyStatePanel title="Memuat percakapan..." text="Riwayat pesan sedang disiapkan." icon="fas fa-spinner" tone="info" compact={true} />
        {:else if groupedMessages.length}
          {#each groupedMessages as group, groupIndex (group.date || groupIndex)}
            <div class="messages-date-chip">{group.date}</div>

            {#each group.items as message, messageIndex (message.id || `${groupIndex}-${messageIndex}`)}
              <article class={`messages-bubble ${message.is_mine ? 'messages-bubble-mine' : 'messages-bubble-theirs'}`.trim()}>
                <p>{message.content}</p>
                <span>{formatMessageTime(message.created_at)}{message.is_mine ? message.is_read ? ' • Dibaca' : ' • Terkirim' : ''}</span>
              </article>
            {/each}
          {/each}
        {:else}
          <EmptyStatePanel
            title="Belum ada pesan"
            text="Mulai percakapan dengan kontak ini."
            icon="fas fa-comments"
            tone="secondary"
            compact={true}
          />
        {/if}
      </div>

      <form class="messages-composer" onsubmit={(event) => {
        event.preventDefault();
        void sendMessage();
      }}>
        <Input
          type="text"
          class="messages-composer-input"
          placeholder={`Tulis pesan untuk ${activeUser.name}...`}
          bind:value={draft}
        />
        <Button type="submit" disabled={!draft.trim() || isSending}>
          <i class="fas fa-paper-plane"></i>
          <span>{isSending ? 'Mengirim...' : 'Kirim'}</span>
        </Button>
      </form>
    {:else}
      <div class="messages-thread-empty messages-thread-empty-full">
        <EmptyStatePanel
          title="Pilih kontak"
          text="Pilih kontak di panel kiri untuk mulai berkomunikasi."
          icon="fas fa-comments"
          tone="secondary"
          compact={true}
        />
      </div>
    {/if}
  </Card.Root>
</div>

<style>
  .messages-layout {
    display: grid;
    grid-template-columns: minmax(0, 24rem) minmax(0, 1fr);
    gap: 1rem;
    margin-top: 1rem;
  }

  .messages-sidebar,
  .messages-conversation {
    min-height: 42rem;
  }

  .messages-sidebar-body {
    display: grid;
    gap: 1rem;
    height: calc(100% - 1px);
  }

  .messages-search {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border-radius: 0.5rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
    color: var(--text-muted);
  }

  :global(.messages-search-input) {
    flex: 1;
    border: none;
    background: transparent;
    box-shadow: none;
  }

  :global(.messages-search-input:focus) {
    box-shadow: none;
  }

  .messages-contact-list {
    display: grid;
    gap: 0.65rem;
    align-content: start;
    max-height: 34rem;
    overflow-y: auto;
  }

  .messages-contact {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.85rem;
    align-items: start;
    padding: 0.85rem 0.9rem;
    border-radius: 0.625rem;
    border: 1px solid var(--line-soft);
    background: var(--background);
    text-align: left;
    color: inherit;
    cursor: pointer;
    transition: background 160ms ease, border-color 160ms ease;
  }

  .messages-contact:hover {
    background: var(--muted);
    border-color: color-mix(in srgb, var(--brand-primary) 20%, var(--line-soft));
  }

  .messages-contact:focus-visible {
    outline: 2px solid color-mix(in srgb, var(--brand-primary) 48%, transparent);
    outline-offset: 2px;
  }

  .messages-contact-active {
    background: color-mix(in srgb, var(--brand-light) 18%, var(--background));
    border-color: color-mix(in srgb, var(--brand-primary) 24%, var(--line-soft));
  }

  .messages-contact-copy {
    min-width: 0;
  }

  .messages-contact-topline {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: space-between;
  }

  .messages-contact-copy span {
    display: block;
    margin-top: 0.15rem;
    color: var(--text-muted);
    font-size: 0.78rem;
  }

  .messages-contact-copy p {
    margin: 0.35rem 0 0;
    color: var(--text-soft);
    line-height: 1.5;
    font-size: 0.86rem;
  }

  .messages-thread-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.05rem 1.2rem;
    border-bottom: 1px solid var(--line-soft);
  }

  .messages-thread-user {
    display: flex;
    align-items: center;
    gap: 0.85rem;
  }

  .messages-thread-user strong {
    display: block;
    font-weight: 600;
  }

  .messages-thread-user span {
    color: var(--text-muted);
    font-size: 0.82rem;
  }

  .messages-thread-body {
    display: grid;
    gap: 0.75rem;
    align-content: start;
    min-height: 28rem;
    max-height: 31rem;
    overflow-y: auto;
    padding: 1.2rem;
    background: var(--background);
  }

  .messages-date-chip {
    justify-self: center;
    padding: 0.3rem 0.7rem;
    border-radius: 0.5rem;
    background: var(--card);
    border: 1px solid var(--line-soft);
    color: var(--text-muted);
    font-size: 0.75rem;
    font-weight: 700;
  }

  .messages-bubble {
    max-width: min(75%, 34rem);
    padding: 0.9rem 1rem;
    border-radius: 0.625rem;
    box-shadow: none;
  }

  .messages-bubble p {
    margin: 0;
    white-space: pre-wrap;
    line-height: 1.6;
  }

  .messages-bubble span {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.74rem;
    opacity: 0.72;
  }

  .messages-bubble-mine {
    justify-self: end;
    background: var(--brand-primary);
    color: #1a1a2e;
    border-bottom-right-radius: 0.35rem;
  }

  .messages-bubble-theirs {
    background: var(--card);
    border: 1px solid var(--line-soft);
    color: var(--text-strong);
    border-bottom-left-radius: 0.35rem;
  }

  .messages-composer {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 1rem 1.2rem 1.2rem;
    border-top: 1px solid var(--line-soft);
  }

  :global(.messages-composer-input) {
    flex: 1;
    background: var(--background);
  }

  .messages-thread-empty,
  .messages-empty-list {
    display: grid;
    place-items: center;
    gap: 0.5rem;
    color: var(--text-muted);
    text-align: center;
    padding: 2rem 1rem;
  }

  .messages-thread-empty-full {
    min-height: 100%;
  }

  @media (max-width: 980px) {
    .messages-layout {
      grid-template-columns: 1fr;
    }

    .messages-sidebar,
    .messages-conversation {
      min-height: auto;
    }
  }

  @media (max-width: 680px) {
    .messages-composer {
      flex-direction: column;
      align-items: stretch;
    }

    .messages-composer :global([data-slot='button']) {
      width: 100%;
    }

    .messages-bubble {
      max-width: 88%;
    }
  }
</style>
