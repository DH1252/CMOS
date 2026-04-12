<script>
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu/index.js';

  let {
    open = $bindable(false),
    user = {},
    links = {},
    csrfToken = '',
    onOpenChange = () => {},
  } = $props();

  let logoutForm;
  let previousOpen = open;

  const navigate = (href) => {
    if (!href) {
      return;
    }

    window.location.assign(href);
  };

  const initialsFor = (value) => (value || 'U').trim().charAt(0).toUpperCase();
  const fallbackAvatar = (name = 'User') => `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'User')}&background=251d39&color=f5c518&bold=true`;

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || user.name || 'User');

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };

  $effect(() => {
    if (open !== previousOpen) {
      previousOpen = open;
      onOpenChange(open);
    }
  });
</script>

<DropdownMenu.Root bind:open>
  <DropdownMenu.Trigger>
    {#snippet child({ props })}
      <button {...props} class={`shell-user-btn ${props.class || ''}`} aria-label="Open user menu">
        {#if user.avatarUrl}
          <img src={user.avatarUrl || fallbackAvatar(user.name)} alt={user.name} onerror={handleImageError} />
        {:else}
          <span class="shell-user-avatar-fallback">{initialsFor(user.name)}</span>
        {/if}
        <span class="shell-user-copy">
          <strong>{user.name}</strong>
          <small>{user.roleName}</small>
        </span>
        <i class="fas fa-chevron-down"></i>
      </button>
    {/snippet}
  </DropdownMenu.Trigger>

  <DropdownMenu.Content align="end" sideOffset={10} class="w-60 p-1">
    <div class="shell-user-summary">
      <strong>{user.name}</strong>
      <small>{user.roleName}</small>
    </div>

    <DropdownMenu.Item onSelect={() => navigate(links.profile)}>
      {#snippet child({ props })}
        <div {...props} class={`shell-menu-item ${props.class || ''}`}>
          <i class="fas fa-user-pen"></i>
          <span>Profil</span>
        </div>
      {/snippet}
    </DropdownMenu.Item>

    {#if links.settings}
      <DropdownMenu.Item onSelect={() => navigate(links.settings)}>
        {#snippet child({ props })}
          <div {...props} class={`shell-menu-item ${props.class || ''}`}>
            <i class="fas fa-gear"></i>
            <span>Pengaturan</span>
          </div>
        {/snippet}
      </DropdownMenu.Item>
    {/if}

    <DropdownMenu.Item onSelect={() => navigate(links.notifications)}>
      {#snippet child({ props })}
        <div {...props} class={`shell-menu-item ${props.class || ''}`}>
          <i class="fas fa-bell"></i>
          <span>Notifikasi</span>
        </div>
      {/snippet}
    </DropdownMenu.Item>

    <DropdownMenu.Separator class="my-1 h-px bg-border" />

    <form bind:this={logoutForm} method="POST" action={links.logout || '#'} class="hidden">
      <input type="hidden" name="_token" value={csrfToken} />
    </form>

    <DropdownMenu.Item onSelect={() => logoutForm?.requestSubmit()}>
      {#snippet child({ props })}
        <div {...props} class={`shell-menu-item shell-menu-item-danger ${props.class || ''}`}>
          <i class="fas fa-right-from-bracket"></i>
          <span>Logout</span>
        </div>
      {/snippet}
    </DropdownMenu.Item>
  </DropdownMenu.Content>
</DropdownMenu.Root>

<style>
  .shell-user-btn {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    min-width: 0;
    padding: 0.3rem 0.55rem 0.3rem 0.3rem;
    border: 1px solid var(--border);
    border-radius: 0.625rem;
    background: var(--card);
    color: inherit;
    cursor: pointer;
    transition: background 160ms ease, border-color 160ms ease;
  }

  .shell-user-btn:hover {
    background: var(--muted);
  }

  .shell-user-btn img,
  .shell-user-avatar-fallback {
    width: 2rem;
    height: 2rem;
    border-radius: 999px;
    flex-shrink: 0;
  }

  .shell-user-btn img {
    object-fit: cover;
  }

  .shell-user-avatar-fallback {
    display: inline-grid;
    place-items: center;
    background: var(--brand-primary);
    color: #1a1a2e;
    font-size: 0.82rem;
    font-weight: 700;
  }

  .shell-user-copy {
    display: flex;
    min-width: 0;
    flex-direction: column;
    align-items: flex-start;
  }

  .shell-user-copy strong,
  .shell-user-copy small {
    max-width: 9rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .shell-user-copy strong {
    font-size: 0.88rem;
  }

  .shell-user-copy small,
  .shell-user-summary small {
    color: var(--text-muted);
    font-size: 0.76rem;
  }

  .shell-user-summary {
    padding: 0.75rem;
    border-bottom: 1px solid var(--border);
    margin-bottom: 0.25rem;
  }

  .shell-user-summary strong {
    display: block;
    font-size: 0.92rem;
  }

  .shell-menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 0.5rem;
    color: inherit;
    cursor: pointer;
  }

  .shell-menu-item-danger {
    color: var(--signal-danger);
  }

  @media (max-width: 760px) {
    .shell-user-copy {
      display: none;
    }
  }
</style>
