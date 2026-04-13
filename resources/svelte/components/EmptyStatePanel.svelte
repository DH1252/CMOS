<script>
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Empty from '$lib/components/ui/empty/index.js';

  let {
    title = 'Belum ada data',
    text = '',
    icon = 'fas fa-inbox',
    action = null,
    compact = false,
    tone = 'secondary',
  } = $props();

  const mediaToneClass = (value) => {
    if (value === 'primary') {
      return 'bg-brand-light/35 text-brand-primary';
    }

    if (value === 'success') {
      return 'bg-[color:color-mix(in_srgb,var(--signal-success)_16%,transparent)] text-[var(--signal-success)]';
    }

    if (value === 'warning') {
      return 'bg-[color:color-mix(in_srgb,var(--signal-warning)_16%,transparent)] text-[var(--signal-warning)]';
    }

    if (value === 'danger') {
      return 'bg-[color:color-mix(in_srgb,var(--signal-danger)_16%,transparent)] text-[var(--signal-danger)]';
    }

    if (value === 'info') {
      return 'bg-[color:color-mix(in_srgb,var(--signal-info)_16%,transparent)] text-[var(--signal-info)]';
    }

    return 'bg-muted text-muted-foreground';
  };

  const actionVariant = (item) => {
    if (item?.variant) {
      return item.variant;
    }

    if (item?.tone === 'secondary') {
      return 'secondary';
    }

    if (item?.tone === 'ghost') {
      return 'ghost';
    }

    if (item?.tone === 'danger') {
      return 'destructive';
    }

    return 'default';
  };

  const containerToneClass = (value) => {
    if (value === 'primary') {
      return 'empty-state-primary';
    }

    if (value === 'success') {
      return 'empty-state-success';
    }

    if (value === 'warning') {
      return 'empty-state-warning';
    }

    if (value === 'danger') {
      return 'empty-state-danger';
    }

    if (value === 'info') {
      return 'empty-state-info';
    }

    return 'empty-state-secondary';
  };
</script>

<Empty.Root class={`rounded-[10px] border ${containerToneClass(tone)} ${compact ? 'min-h-[8rem] px-4 py-5' : 'min-h-[11rem] px-6 py-8'}`}>
  <Empty.Header class="flex flex-col items-center text-center">
    <Empty.Media variant="icon" class={`mb-3 grid h-10 w-10 place-items-center rounded-[10px] text-sm ${mediaToneClass(tone)}`}>
      <i class={icon}></i>
    </Empty.Media>
    <Empty.Title class="m-0 text-base font-semibold text-foreground">{title}</Empty.Title>
    {#if text}
      <Empty.Description class="mt-2 max-w-sm text-sm leading-6 text-muted-foreground">{text}</Empty.Description>
    {/if}
  </Empty.Header>

  {#if action}
    <Empty.Content class="mt-4 flex justify-center">
      <Button href={action.href} variant={actionVariant(action)} size={compact ? 'sm' : 'default'}>
        {#if action.icon}
          <i class={`${action.icon} mr-1.5`}></i>
        {/if}
        <span>{action.label}</span>
      </Button>
    </Empty.Content>
  {/if}
</Empty.Root>

<style>
  .empty-state-primary {
    border-color: color-mix(in srgb, var(--brand-primary) 24%, var(--border));
    background: color-mix(in srgb, var(--brand-light) 32%, var(--card));
  }

  .empty-state-success {
    border-color: color-mix(in srgb, var(--signal-success) 22%, var(--border));
    background: color-mix(in srgb, var(--signal-success) 10%, var(--card));
  }

  .empty-state-warning {
    border-color: color-mix(in srgb, var(--signal-warning) 24%, var(--border));
    background: color-mix(in srgb, var(--signal-warning) 10%, var(--card));
  }

  .empty-state-danger {
    border-color: color-mix(in srgb, var(--signal-danger) 24%, var(--border));
    background: color-mix(in srgb, var(--signal-danger) 10%, var(--card));
  }

  .empty-state-info {
    border-color: color-mix(in srgb, var(--signal-info) 24%, var(--border));
    background: color-mix(in srgb, var(--signal-info) 10%, var(--card));
  }

  .empty-state-secondary {
    border-color: var(--border);
    background: var(--card);
  }
</style>
