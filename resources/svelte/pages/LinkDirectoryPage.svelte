<script>
  import { Button } from "$lib/components/ui/button/index.js";
  import {
    shouldSkipFormConfirmation,
    submitConfirmedForm,
  } from "$lib/confirmable-form.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import EmptyStatePanel from "../components/EmptyStatePanel.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import StatusBadge from "../components/StatusBadge.svelte";

  let {
    title = "Kumpulan Link",
    description = "",
    icon = "fas fa-link",
    primaryAction = null,
    groups = [],
    emptyState = {
      title: "Belum ada link",
      text: "Belum ada tautan.",
    },
    csrfToken = "",
  } = $props();

  const confirmSubmission = async (event, action) => {
    const form = event.currentTarget;

    if (shouldSkipFormConfirmation(form)) {
      return;
    }

    if (!action?.confirm) {
      return;
    }

    event.preventDefault();

    const text =
      action.confirmText || `Lanjutkan tindakan untuk ${action.confirm}?`;

    if (window.Swal) {
      const result = await window.Swal.fire({
        title: action.confirmTitle || "Konfirmasi",
        text,
        icon: action.confirmIcon || "warning",
        showCancelButton: true,
        confirmButtonText: action.confirmButtonText || "Lanjutkan",
        cancelButtonText: "Batal",
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

<Card.Root
  class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
>
  <Card.Header class="directory-intro-head border-b border-border/70 pb-4">
    <div class="directory-intro-copy-wrap">
      <PageHeader {title} {description} {icon} />
    </div>

    {#if primaryAction}
      <Button href={primaryAction.href}>
        {#if primaryAction.icon}
          <i class={primaryAction.icon}></i>
        {/if}
        <span>{primaryAction.label}</span>
      </Button>
    {/if}
  </Card.Header>
</Card.Root>

{#if !groups.length}
  <Card.Root
    class="animate-fadeIn mt-4 rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Content class="pt-5">
      <EmptyStatePanel
        title={emptyState.title}
        text={emptyState.text}
        icon="fas fa-link"
        tone="secondary"
      />
    </Card.Content>
  </Card.Root>
{:else}
  <div class="directory-groups">
    {#each groups as group, index (group.name || index)}
      <section class="directory-group animate-fadeIn">
        <div class="directory-group-head">
          <div class="directory-group-title">
            <div class="directory-group-icon">
              <i class={group.icon || icon}></i>
            </div>
            <div>
              <h4>{group.name}</h4>
              {#if group.description}
                <p>{group.description}</p>
              {/if}
            </div>
          </div>
          <StatusBadge label={`${group.cards.length} link`} tone="secondary" />
        </div>

        <div class="directory-grid">
          {#each group.cards as card, cardIndex (card.href || cardIndex)}
            <Card.Root
              class="directory-card rounded-[10px] border border-border bg-card shadow-none"
            >
              <Card.Content class="pt-5">
                <div class="directory-card-top">
                  <div class="directory-card-icon">
                    <i class={card.icon || group.icon || icon}></i>
                  </div>

                  {#if card.badges?.length}
                    <div class="directory-card-badges">
                      {#each card.badges as badge, badgeIndex (badge.label || badgeIndex)}
                        <StatusBadge
                          label={badge.label}
                          icon={badge.icon || ""}
                          tone={badge.tone || "secondary"}
                        />
                      {/each}
                    </div>
                  {/if}
                </div>

                <div class="directory-card-body">
                  <h5>{card.title}</h5>
                  {#if card.description}
                    <p>{card.description}</p>
                  {/if}

                  {#if card.meta?.length}
                    <div class="directory-card-meta">
                      {#each card.meta as line, lineIndex (line.text || lineIndex)}
                        <div class={line.muted ? "fs-sm text-muted" : "fs-sm"}>
                          {line.text}
                        </div>
                      {/each}
                    </div>
                  {/if}
                </div>

                <div class="directory-card-actions">
                  <Button
                    href={card.href}
                    target="_blank"
                    rel="noreferrer"
                    size="sm"
                  >
                    <i class="fas fa-arrow-up-right-from-square"></i>
                    <span>{card.primaryLabel || "Buka Link"}</span>
                  </Button>

                  {#if card.editHref}
                    <Button
                      href={card.editHref}
                      variant="secondary"
                      size="icon-sm"
                      title="Edit"
                      aria-label="Edit"
                    >
                      <i class="fas fa-pen"></i>
                    </Button>
                  {/if}

                  {#if card.deleteAction}
                    <form
                      method="POST"
                      action={card.deleteAction}
                      onsubmit={(event) => confirmSubmission(event, card)}
                    >
                      <input type="hidden" name="_token" value={csrfToken} />
                      {#if card.deleteMethod}
                        <input
                          type="hidden"
                          name="_method"
                          value={card.deleteMethod}
                        />
                      {/if}
                      <Button
                        type="submit"
                        variant="destructive"
                        size="icon-sm"
                        title="Hapus"
                        aria-label="Hapus"
                      >
                        <i class="fas fa-trash"></i>
                      </Button>
                    </form>
                  {/if}
                </div>
              </Card.Content>
            </Card.Root>
          {/each}
        </div>
      </section>
    {/each}
  </div>
{/if}

<style>
  .directory-intro-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
  }

  .directory-intro-copy-wrap {
    min-width: 0;
    flex: 1;
  }

  .directory-groups {
    display: grid;
    gap: 2rem;
    margin-top: 1.5rem;
  }

  .directory-group {
    display: grid;
    gap: 1rem;
  }

  .directory-group-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 1rem;
  }

  .directory-group-title {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .directory-group-title h4 {
    margin: 0 0 0.2rem;
    font-size: 1.1rem;
    font-weight: 600;
  }

  .directory-group-title p {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.92rem;
  }

  .directory-group-icon,
  .directory-card-icon {
    display: grid;
    place-items: center;
    border-radius: 0.625rem;
    background: color-mix(in srgb, var(--brand-light) 16%, transparent);
    color: var(--brand-primary);
    box-shadow: none;
  }

  .directory-group-icon {
    width: 3.25rem;
    height: 3.25rem;
    font-size: 1rem;
  }

  .directory-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
  }

  .directory-card {
    min-height: 100%;
  }

  .directory-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .directory-card-icon {
    width: 3rem;
    height: 3rem;
    font-size: 1rem;
  }

  .directory-card-badges {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 0.35rem;
  }

  .directory-card-body h5 {
    margin: 1rem 0 0.35rem;
    font-size: 1rem;
  }

  .directory-card-body p {
    margin: 0;
    color: var(--text-soft);
    font-size: 0.93rem;
  }

  .directory-card-meta {
    display: grid;
    gap: 0.3rem;
    margin-top: 0.75rem;
  }

  .directory-card-actions,
  form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
  }

  .directory-card-actions {
    margin-top: 1rem;
    flex-wrap: wrap;
  }

  @media (max-width: 767px) {
    .directory-group-head {
      align-items: flex-start;
      flex-direction: column;
    }

    .directory-card-actions {
      flex-wrap: wrap;
    }
  }
</style>
