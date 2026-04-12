<script>
  import { Badge } from '$lib/components/ui/badge/index.js';

  let {
    label = '',
    icon = '',
    tone = 'secondary',
    className = '',
  } = $props();

  const variantFor = (value) => {
    if (value === 'secondary') {
      return 'secondary';
    }

    return 'outline';
  };

  const toneClass = (value) => {
    if (value === 'primary') {
      return 'status-badge-primary';
    }

    if (value === 'success') {
      return 'status-badge-success';
    }

    if (value === 'warning') {
      return 'status-badge-warning';
    }

    if (value === 'danger') {
      return 'status-badge-danger';
    }

    if (value === 'info') {
      return 'status-badge-info';
    }

    return 'status-badge-secondary';
  };
</script>

<Badge variant={variantFor(tone)} class={`status-badge ${toneClass(tone)} ${className}`.trim()}>
  {#if icon}
    <i class={icon}></i>
  {/if}
  <span>{label}</span>
</Badge>

<style>
  .status-badge {
    gap: 0.3rem;
    min-height: 1.55rem;
    padding-inline: 0.5rem;
    border-width: 1px;
    border-radius: 0.45rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0;
  }

  .status-badge-primary {
    border-color: color-mix(in srgb, var(--brand-primary) 32%, transparent);
    background: color-mix(in srgb, var(--brand-primary) 14%, transparent);
    color: var(--brand-primary);
  }

  .status-badge-success {
    border-color: color-mix(in srgb, var(--signal-success) 32%, transparent);
    background: color-mix(in srgb, var(--signal-success) 14%, transparent);
    color: var(--signal-success);
  }

  .status-badge-warning {
    border-color: color-mix(in srgb, var(--signal-warning) 32%, transparent);
    background: color-mix(in srgb, var(--signal-warning) 14%, transparent);
    color: var(--signal-warning);
  }

  .status-badge-danger {
    border-color: color-mix(in srgb, var(--signal-danger) 32%, transparent);
    background: color-mix(in srgb, var(--signal-danger) 14%, transparent);
    color: var(--signal-danger);
  }

  .status-badge-info {
    border-color: color-mix(in srgb, var(--signal-info) 32%, transparent);
    background: color-mix(in srgb, var(--signal-info) 14%, transparent);
    color: var(--signal-info);
  }

  .status-badge-secondary {
    border-color: var(--border);
    background: var(--muted);
    color: var(--text-muted);
  }
</style>
