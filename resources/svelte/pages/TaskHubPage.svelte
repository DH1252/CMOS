<script>
  import { Progress } from "$lib/components/ui/progress/index.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import Breadcrumbs from "../components/Breadcrumbs.svelte";
  import EmptyStatePanel from "../components/EmptyStatePanel.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import StatusBadge from "../components/StatusBadge.svelte";

  let {
    title = "Tasks",
    description = "",
    icon = "fas fa-tasks",
    breadcrumbs = [],
    cards = [],
    overdueTasks = [],
    emptyState = {
      title: "Belum ada data",
      text: "Belum ada data.",
    },
  } = $props();

  const iconToneClass = (tone) => {
    if (tone === "info")
      return "bg-[color:color-mix(in_srgb,var(--signal-info)_16%,transparent)] text-[var(--signal-info)]";
    if (tone === "success")
      return "bg-[color:color-mix(in_srgb,var(--signal-success)_16%,transparent)] text-[var(--signal-success)]";
    if (tone === "warning")
      return "bg-[color:color-mix(in_srgb,var(--signal-warning)_16%,transparent)] text-[var(--signal-warning)]";
    if (tone === "danger")
      return "bg-[color:color-mix(in_srgb,var(--signal-danger)_16%,transparent)] text-[var(--signal-danger)]";
    return "bg-brand-light/20 text-brand-primary";
  };

  const statusLabel = (status) => {
    if (status === "in_progress") return "Berjalan";
    if (status === "done") return "Selesai";
    if (status === "pending") return "Tertunda";
    return "Todo";
  };
  const fallbackAvatar = (name = "User") =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(name || "User")}&background=251d39&color=f5c518&bold=true`;
  const handleImageError = (e, name) => {
    e.currentTarget.src = fallbackAvatar(name);
  };
</script>

<Breadcrumbs items={breadcrumbs} />

<Card.Root
  class="animate-fadeIn mb-4 rounded-[10px] border border-border bg-card shadow-none"
>
  <Card.Header class="border-b border-border/70 pb-4">
    <PageHeader {title} {description} {icon} />
  </Card.Header>
</Card.Root>

{#if overdueTasks.length > 0}
  <Card.Root
    class="animate-fadeIn mb-4 rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Header
      class="flex flex-col gap-3 border-b border-border/70 pb-4 sm:flex-row sm:items-center sm:justify-between"
    >
      <PageHeader
        title={`Task Terlambat (${overdueTasks.length})`}
        description="Task lewat tenggat dan belum selesai."
        icon="fas fa-triangle-exclamation"
        compact={true}
        headingTag="h2"
      />
    </Card.Header>
    <Card.Content class="pt-0">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        {#each overdueTasks as task, index (task.id || index)}
          <a
            href={task.showHref}
            class="flex flex-col gap-2 border-b border-l border-border/40 p-4 no-underline transition-colors hover:bg-muted/60"
          >
            <div class="flex items-start gap-2">
              <div class="min-w-0 flex-1">
                <p class="m-0 truncate text-sm font-semibold text-foreground">
                  {task.title}
                </p>
                {#if task.program_name || task.department_name}
                  <p class="m-0 mt-0.5 truncate text-xs text-muted-foreground">
                    {task.program_name || task.department_name}
                  </p>
                {/if}
              </div>
              <StatusBadge
                label={task.priority_label}
                tone={task.priority === "high"
                  ? "danger"
                  : task.priority === "medium"
                    ? "primary"
                    : "info"}
              />
            </div>
            <div class="flex items-center gap-2 text-xs text-muted-foreground">
              {#if task.assignee_name}
                <img
                  src={task.assignee_avatar ||
                    fallbackAvatar(task.assignee_name)}
                  alt={task.assignee_name}
                  class="h-4 w-4 rounded-full"
                  width="16"
                  height="16"
                  loading="lazy"
                  onerror={(e) => handleImageError(e, task.assignee_name)}
                />
                <span class="truncate">{task.assignee_name}</span>
              {:else}
                <i class="fas fa-user-slash"></i>
                <span>Unassigned</span>
              {/if}
            </div>
            <div
              class="flex items-center gap-2 text-xs font-semibold text-[var(--signal-danger)]"
            >
              <i class="fas fa-calendar-alt"></i>
              {task.deadline_fmt}
              <span class="text-muted-foreground font-normal">·</span>
              <span class="text-muted-foreground font-normal"
                >{statusLabel(task.status)}</span
              >
            </div>
          </a>
        {/each}
      </div>
    </Card.Content>
  </Card.Root>
{/if}

{#if !cards.length}
  <Card.Root
    class="animate-fadeIn mt-4 rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Content class="pt-5">
      <EmptyStatePanel
        title={emptyState.title}
        text={emptyState.text}
        icon="fas fa-folder-open"
        tone="primary"
        compact={true}
      />
    </Card.Content>
  </Card.Root>
{:else}
  <div
    class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
  >
    {#each cards as card, index (card.href || card.title || index)}
      <a
        href={card.href}
        class={`flex min-h-full flex-col gap-4 rounded-[10px] border border-border bg-card p-5 text-foreground no-underline transition-colors hover:bg-muted/60 ${card.featured ? "border-brand-primary/25" : ""}`.trim()}
      >
        <div class="flex items-start gap-4">
          <div
            class={`flex h-12 w-12 shrink-0 items-center justify-center rounded-[10px] ${iconToneClass(card.tone)}`}
          >
            <i class={card.icon || "fas fa-folder"}></i>
          </div>

          <div class="min-w-0 flex-1">
            <h4 class="m-0 mb-1 text-lg font-semibold text-foreground">
              {card.title}
            </h4>
            <p class="m-0 leading-relaxed text-muted-foreground">
              {card.description}
            </p>
          </div>
        </div>

        {#if card.progress != null}
          <div class="flex items-center gap-3">
            <Progress value={card.progress} class="h-2 flex-1" />
            <span
              class="min-w-[2.8rem] text-right text-sm font-bold text-muted-foreground"
              >{card.progress}%</span
            >
          </div>
        {/if}

        {#if card.stats?.length}
          <div class="flex flex-wrap gap-2 border-t border-border/70 pt-3">
            {#each card.stats as stat, statIndex (stat.label || statIndex)}
              <StatusBadge
                label={stat.label}
                icon={stat.icon}
                tone={stat.tone || "secondary"}
                className="shadow-none"
              />
            {/each}
          </div>
        {/if}
      </a>
    {/each}
  </div>
{/if}
