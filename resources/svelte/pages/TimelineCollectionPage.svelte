<script>
  import { Button } from '$lib/components/ui/button/index.js';
  import { shouldSkipFormConfirmation, submitConfirmedForm } from '$lib/confirmable-form.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import Breadcrumbs from '../components/Breadcrumbs.svelte';
  import EmptyStatePanel from '../components/EmptyStatePanel.svelte';
  import PageHeader from '../components/PageHeader.svelte';
  import StatusBadge from '../components/StatusBadge.svelte';

  let {
    title = 'Timeline',
    description = '',
    icon = 'fas fa-calendar-alt',
    breadcrumbs = [],
    actions = [],
    summary = [],
    sidebar = null,
    items = [],
    emptyState = {
      title: 'Belum ada timeline',
      text: 'Timeline akan muncul di halaman ini setelah tersedia.',
      action: null,
    },
  } = $props();

  const buttonVariant = (action) => {
    if (action?.variant) {
      return action.variant;
    }

    if (action?.tone === 'danger') {
      return 'destructive';
    }

    if (action?.tone === 'secondary') {
      return 'secondary';
    }

    if (action?.tone === 'outline') {
      return 'outline';
    }

    return 'default';
  };

  const buttonSize = (action) => {
    if (action?.iconOnly) {
      return 'icon-sm';
    }

    if (action?.size === 'sm') {
      return 'sm';
    }

    return 'default';
  };

  const buttonClass = (action) => `timeline-card-action-button ${action?.iconOnly ? 'timeline-card-action-button-icon' : ''}`.trim();

  const confirmSubmission = async (event, action) => {
    const form = event.currentTarget;

    if (shouldSkipFormConfirmation(form)) {
      return;
    }

    if (!action?.confirm) {
      return;
    }

    event.preventDefault();

    const title = action.confirmTitle || 'Konfirmasi';
    const text = action.confirmText || `Lanjutkan tindakan untuk ${action.confirm}?`;

    if (window.Swal) {
      const result = await window.Swal.fire({
        title,
        text,
        icon: action.confirmIcon || 'warning',
        showCancelButton: true,
        confirmButtonText: action.confirmButtonText || 'Lanjutkan',
        cancelButtonText: action.cancelButtonText || 'Batal',
      });

      if (result.isConfirmed) {
        submitConfirmedForm(form);
      }

      return;
    }

    if (window.confirm(text)) {
      submitConfirmedForm(form);
    }
  };
</script>

<Breadcrumbs items={breadcrumbs} />

<div class={`timeline-collection ${sidebar ? 'timeline-collection-with-sidebar' : ''}`.trim()}>
  {#if sidebar}
    <aside class="timeline-sidebar">
      <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
        <Card.Header class="border-b border-border/70 pb-4">
          <PageHeader
            title={sidebar.title}
            description={sidebar.description || ''}
            icon={sidebar.icon || 'fas fa-filter'}
            compact={true}
            headingTag="h3"
          />
        </Card.Header>

        <Card.Content class="timeline-sidebar-body pt-5">
          {#each sidebar.items || [] as option, optionIndex (option.href || option.label || optionIndex)}
            <a href={option.href} class={`timeline-sidebar-link ${option.active ? 'timeline-sidebar-link-active' : ''}`.trim()}>
              <span>{option.label}</span>
              {#if option.meta}
                <small>{option.meta}</small>
              {/if}
            </a>
          {/each}
        </Card.Content>
      </Card.Root>
    </aside>
  {/if}

  <section class="timeline-main">
    <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="timeline-head border-b border-border/70 pb-4">
        <div class="timeline-head-copy">
          <PageHeader {title} {description} {icon} />
        </div>

        {#if actions.length}
          <div class="timeline-head-actions">
            {#each actions as action, index (action.href || action.label || index)}
              <Button href={action.href} variant={buttonVariant(action)} size={buttonSize(action)}>
                {#if action.icon}
                  <i class={action.icon}></i>
                {/if}
                <span>{action.label}</span>
              </Button>
            {/each}
          </div>
        {/if}
      </Card.Header>

      {#if summary.length}
        <div class="timeline-summary-strip">
          {#each summary as item, index (item.label || index)}
            <div class="timeline-summary-box">
              <span>{item.label}</span>
              <strong>{item.value}</strong>
            </div>
          {/each}
        </div>
      {/if}

      <Card.Content class="pt-5">
        {#if items.length}
          <div class="timeline-item-list">
            {#each items as item, itemIndex (item.title || itemIndex)}
              <article class="timeline-card">
                <div class="timeline-card-main">
                  <div class="timeline-card-top">
                    <div class="timeline-card-copy">
                      <div class="timeline-card-badges">
                        {#if item.scope}
                          <StatusBadge label={item.scope.label} tone={item.scope.tone || 'secondary'} />
                        {/if}
                        {#if item.status}
                          <StatusBadge label={item.status.label} tone={item.status.tone || 'secondary'} />
                        {/if}
                      </div>

                      <h4>{item.title}</h4>
                      <p>{item.description}</p>
                    </div>

                    {#if item.actions?.length}
                      <div class="timeline-card-actions">
                        {#each item.actions as action, actionIndex (action.href || action.label || actionIndex)}
                          {#if action.method}
                            <form
                              method="POST"
                              action={action.href}
                              class="d-inline-flex"
                              data-native="true"
                              onsubmit={(event) => confirmSubmission(event, action)}
                            >
                              {#if action.csrfToken}
                                <input type="hidden" name="_token" value={action.csrfToken} />
                              {/if}
                              {#if action.spoofMethod}
                                <input type="hidden" name="_method" value={action.spoofMethod} />
                              {/if}
                              <Button
                                type="submit"
                                variant={buttonVariant(action)}
                                size={buttonSize(action)}
                                class={buttonClass(action)}
                                aria-label={action.label}
                                title={action.label}
                              >
                                <i class={action.icon}></i>
                                {#if !action.iconOnly}
                                  <span>{action.label}</span>
                                {/if}
                              </Button>
                            </form>
                          {:else}
                            <Button
                              href={action.href}
                              variant={buttonVariant(action)}
                              size={buttonSize(action)}
                              class={buttonClass(action)}
                              aria-label={action.label}
                              title={action.label}
                            >
                              <i class={action.icon}></i>
                              {#if !action.iconOnly}
                                <span>{action.label}</span>
                              {/if}
                            </Button>
                          {/if}
                        {/each}
                      </div>
                    {/if}
                  </div>

                  <div class="timeline-range">
                    <i class="fas fa-calendar-day"></i>
                    <span>{item.range}</span>
                  </div>

                  {#if item.meta?.length}
                    <div class="timeline-meta-list">
                      {#each item.meta as meta, metaIndex (meta.label || metaIndex)}
                        <div class="timeline-meta-item">
                          <i class={meta.icon}></i>
                          <span>{meta.label}</span>
                        </div>
                      {/each}
                    </div>
                  {/if}
                </div>
              </article>
            {/each}
          </div>
        {:else}
          <EmptyStatePanel
            title={emptyState.title}
            text={emptyState.text}
            action={emptyState.action}
            icon="fas fa-calendar-xmark"
            tone="primary"
          />
        {/if}
      </Card.Content>
    </Card.Root>
  </section>
</div>

<style>
  .timeline-collection {
    display: grid;
    gap: 1.25rem;
  }

  .timeline-collection-with-sidebar {
    grid-template-columns: minmax(0, 18rem) minmax(0, 1fr);
  }

  .timeline-sidebar,
  .timeline-main {
    min-width: 0;
  }

  .timeline-sidebar-body {
    display: grid;
    gap: 0.65rem;
  }

  .timeline-sidebar-link {
    display: grid;
    gap: 0.2rem;
    padding: 0.9rem 1rem;
    border-radius: 0.625rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
    color: inherit;
    text-decoration: none;
    transition: background 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
  }

  .timeline-sidebar-link:hover {
    background: var(--muted);
    border-color: color-mix(in srgb, var(--brand-primary) 18%, var(--line-soft));
    box-shadow: none;
  }

  .timeline-sidebar-link small {
    color: var(--text-muted);
  }

  .timeline-sidebar-link-active {
    background: color-mix(in srgb, var(--brand-light) 14%, var(--background));
    border-color: color-mix(in srgb, var(--brand-primary) 24%, var(--line-soft));
  }

  .timeline-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }

  .timeline-head-copy {
    min-width: 0;
    flex: 1;
  }

  .timeline-head-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
    justify-content: flex-end;
  }

  .timeline-summary-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 0.75rem;
    padding: 1rem 1.25rem 0;
    border-top: 1px solid var(--line-soft);
  }

  .timeline-summary-box {
    display: grid;
    gap: 0.2rem;
    padding: 0.75rem 0 0;
    border-radius: 0.625rem;
    background: transparent;
    border: 0;
    border-top: 1px solid var(--line-soft);
  }

  .timeline-summary-box span {
    color: var(--text-muted);
    font-size: 0.76rem;
    font-size: 0.78rem;
  }

  .timeline-summary-box strong {
    font-size: 1.18rem;
    font-weight: 600;
  }

  .timeline-item-list {
    display: grid;
    gap: 0.9rem;
  }

  .timeline-card {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 0.95rem;
    padding: 1rem;
    border-radius: 0.625rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
    box-shadow: none;
  }

  .timeline-card-main {
    display: grid;
    gap: 0.8rem;
    min-width: 0;
  }

  .timeline-card-top {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
  }

  .timeline-card-copy {
    min-width: 0;
  }

  .timeline-card-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-bottom: 0.55rem;
  }

  .timeline-card-copy h4 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
  }

  .timeline-card-copy p {
    margin: 0.3rem 0 0;
    color: var(--text-soft);
  }

  .timeline-card-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    justify-content: flex-end;
  }

  .timeline-card-action-button {
    box-shadow: none;
  }

  .timeline-card-action-button-icon {
    min-width: 0;
  }

  .timeline-card-actions form {
    margin: 0;
  }

  .timeline-range {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    color: var(--text-soft);
    font-weight: 600;
  }

  .timeline-meta-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;
    padding-top: 0.8rem;
    border-top: 1px solid var(--line-soft);
  }

  .timeline-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    color: var(--text-muted);
    font-size: 0.84rem;
  }

  @media (max-width: 1024px) {
    .timeline-collection-with-sidebar {
      grid-template-columns: minmax(0, 1fr);
    }
  }

  @media (max-width: 767px) {
    .timeline-head,
    .timeline-card-top {
      flex-direction: column;
    }

    .timeline-head-actions,
    .timeline-card-actions {
      justify-content: flex-start;
    }

    .timeline-card {
      grid-template-columns: minmax(0, 1fr);
    }

    .timeline-summary-box {
      border-left: 0;
      border-top: 1px solid var(--line-soft);
      padding: 0.75rem 0 0;
    }
  }
</style>
