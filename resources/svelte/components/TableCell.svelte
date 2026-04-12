<script>
  import { Button } from '$lib/components/ui/button/index.js';
  import { Progress } from '$lib/components/ui/progress/index.js';
  import StatusBadge from './StatusBadge.svelte';

  let {
    cell = {},
    csrfToken = '',
  } = $props();

  const badgeTone = (tone) => {
    if (tone === 'danger') return 'danger';
    if (tone === 'success') return 'success';
    if (tone === 'warning') return 'warning';
    if (tone === 'info') return 'info';
    if (tone === 'primary') return 'primary';
    return 'secondary';
  };

  const buttonVariant = (tone) => {
    if (tone === 'primary') return 'default';
    if (tone === 'danger') return 'destructive';
    return 'outline';
  };

  const buttonToneClass = (tone) => {
    if (tone === 'success') return 'table-action-button success';
    if (tone === 'info') return 'table-action-button info';
    return 'table-action-button';
  };

  const progressClass = (tone) => (tone === 'success' ? 'table-progress success' : 'table-progress');
  const fallbackAvatar = (name = 'User') => {
    const initial = (name || 'User').trim().charAt(0).toUpperCase() || 'U';
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="32" fill="#251d39"/><text x="50%" y="50%" dy=".35em" fill="#f5c518" font-family="Public Sans, Arial, sans-serif" font-size="28" font-weight="700" text-anchor="middle">${initial}</text></svg>`;

    return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
  };

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || cell.title || 'User');

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };

  const confirmSubmission = async (event, item) => {
    if (!item?.confirm) {
      return;
    }

    event.preventDefault();

    const title = item.confirmTitle || 'Konfirmasi';
    const text = item.confirmText || `Lanjutkan tindakan untuk ${item.confirm}?`;

    if (window.Swal) {
      const result = await window.Swal.fire({
        title,
        text,
        icon: item.confirmIcon || 'warning',
        showCancelButton: true,
        confirmButtonText: item.confirmButtonText || 'Lanjutkan',
        cancelButtonText: 'Batal',
      });

      if (result.isConfirmed) {
        event.currentTarget.submit();
      }
      return;
    }

    if (window.confirm(text)) {
      event.currentTarget.submit();
    }
  };
</script>

{#if cell.type === 'avatar'}
  <div class={`table-avatar ${cell.className || ''}`.trim()}>
    {#if cell.image}
      <img src={cell.image} alt={cell.title || 'avatar'} class={cell.size === 'md' ? 'avatar-md' : 'avatar-sm'} onerror={handleImageError} />
    {/if}
    <div class="table-avatar-copy">
      {#if cell.href}
        <a href={cell.href} class="table-link table-link-strong">{cell.title}</a>
      {:else}
        <div class="table-link-strong">{cell.title}</div>
      {/if}
      {#if cell.subtitle}
        <div class="table-subtle-text">{cell.subtitle}</div>
      {/if}
    </div>
  </div>
{:else if cell.type === 'stack'}
  <div class={`table-stack ${cell.className || ''}`.trim()}>
    {#each cell.lines || [] as line, index (index)}
      {#if line.href}
        <a href={line.href} class={`${line.className || ''} ${line.muted ? 'table-subtle-text' : 'table-link-strong'}`.trim()}>{line.text}</a>
      {:else}
        <div class={`${line.className || ''} ${line.muted ? 'table-subtle-text' : ''}`.trim()}>{line.text}</div>
      {/if}
    {/each}
  </div>
{:else if cell.type === 'badge'}
  <StatusBadge label={cell.label} icon={cell.icon} tone={badgeTone(cell.tone)} />
{:else if cell.type === 'badges'}
  <div class="table-badges">
    {#each cell.items || [] as badge, badgeIndex (badgeIndex)}
      <StatusBadge label={badge.label} icon={badge.icon} tone={badgeTone(badge.tone)} />
    {/each}
  </div>
{:else if cell.type === 'progress'}
  <div class="table-progress-wrap">
    <Progress value={cell.value || 0} class={progressClass(cell.tone)} />
    <span class="table-progress-label">{cell.label || `${cell.value || 0}%`}</span>
  </div>
{:else if cell.type === 'actions'}
  <div class="table-actions">
    {#each cell.items || [] as item, itemIndex (itemIndex)}
      {#if item.href}
        <Button
          href={item.href}
          variant={buttonVariant(item.tone)}
          size="icon-sm"
          class={buttonToneClass(item.tone)}
          title={item.label}
          aria-label={item.label}
        >
          <i class={item.icon}></i>
        </Button>
      {:else if item.action}
        <form method="POST" action={item.action} onsubmit={(event) => confirmSubmission(event, item)}>
          <input type="hidden" name="_token" value={csrfToken} />
          {#if item.method}
            <input type="hidden" name="_method" value={item.method} />
          {/if}
          <Button
            type="submit"
            variant={buttonVariant(item.tone)}
            size="icon-sm"
            class={buttonToneClass(item.tone)}
            title={item.label}
            aria-label={item.label}
          >
            <i class={item.icon}></i>
          </Button>
        </form>
      {/if}
    {/each}
  </div>
{:else}
  {#if cell.href}
    <a href={cell.href} class={`${cell.className || ''} ${cell.muted ? 'table-subtle-text' : 'table-link-strong'}`.trim()}>{cell.text}</a>
  {:else}
    <span class={`${cell.className || ''} ${cell.muted ? 'table-subtle-text' : ''}`.trim()}>{cell.text}</span>
  {/if}
{/if}

<style>
  .table-avatar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
  }

  .table-avatar-copy,
  .table-stack {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    min-width: 0;
  }

  .table-badges,
  .table-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    align-items: center;
  }

  .table-progress-wrap {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    min-width: 10rem;
  }

  .table-progress-wrap :global([data-slot='progress']) {
    flex: 1;
  }

  .table-progress :global([data-slot='progress-indicator']) {
    background: color-mix(in srgb, var(--brand-primary) 78%, black);
  }

  .table-progress.success :global([data-slot='progress-indicator']) {
    background: color-mix(in srgb, var(--signal-success) 78%, black);
  }

  .table-progress-label {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-soft);
  }

  .table-action-button {
    box-shadow: none;
  }

  .table-action-button.success {
    border-color: color-mix(in srgb, var(--signal-success) 20%, transparent);
    background: color-mix(in srgb, var(--signal-success) 10%, white);
    color: color-mix(in srgb, var(--signal-success) 76%, black);
  }

  .table-action-button.info {
    border-color: color-mix(in srgb, var(--signal-info) 20%, transparent);
    background: color-mix(in srgb, var(--signal-info) 10%, white);
    color: color-mix(in srgb, var(--signal-info) 76%, black);
  }

  .table-link-strong {
    color: var(--text-strong);
    font-weight: 700;
    text-decoration: none;
    overflow-wrap: anywhere;
  }

  .table-link-strong:hover {
    color: color-mix(in srgb, var(--brand-primary) 80%, black);
  }

  .table-subtle-text {
    color: var(--text-muted);
    text-decoration: none;
    overflow-wrap: anywhere;
  }

  form {
    margin: 0;
  }
</style>
