<script>
  import * as Card from '$lib/components/ui/card/index.js';

  let {
    label = '',
    value = '',
    icon = 'fas fa-chart-line',
    description = '',
    tone = 'primary',
  } = $props();

  const toneClass = (value) => {
    if (value === 'success') {
      return 'metric-card-success';
    }

    if (value === 'warning') {
      return 'metric-card-warning';
    }

    if (value === 'danger') {
      return 'metric-card-danger';
    }

    if (value === 'info') {
      return 'metric-card-info';
    }

    return 'metric-card-primary';
  };
</script>

<Card.Root size="sm" class="h-full rounded-[10px] border border-border bg-card shadow-none">
  <Card.Content class="grid gap-3 p-5">
    <div class="flex items-start justify-between gap-3">
      <div class="space-y-1">
        <div class="text-sm text-muted-foreground">{label}</div>
        <div class="text-3xl font-semibold leading-none text-foreground">{value}</div>
      </div>
      <div class={`inline-flex h-10 w-10 items-center justify-center rounded-[10px] text-sm ${toneClass(tone)}`}>
        <i class={icon}></i>
      </div>
    </div>

    {#if description}
      <div class="border-t border-border pt-3 text-sm leading-6 text-muted-foreground">{description}</div>
    {/if}
  </Card.Content>
</Card.Root>

<style>
  .metric-card-primary {
    background: color-mix(in srgb, var(--brand-light) 64%, white);
    color: color-mix(in srgb, var(--brand-hover) 88%, black);
  }

  .metric-card-success {
    background: color-mix(in srgb, var(--signal-success) 16%, white);
    color: color-mix(in srgb, var(--signal-success) 82%, black);
  }

  .metric-card-warning {
    background: color-mix(in srgb, var(--signal-warning) 18%, white);
    color: color-mix(in srgb, var(--signal-warning) 82%, black);
  }

  .metric-card-danger {
    background: color-mix(in srgb, var(--signal-danger) 16%, white);
    color: color-mix(in srgb, var(--signal-danger) 84%, black);
  }

  .metric-card-info {
    background: color-mix(in srgb, var(--signal-info) 14%, white);
    color: color-mix(in srgb, var(--signal-info) 78%, black);
  }
</style>
