<script>
  import { Button } from "$lib/components/ui/button/index.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import { Progress } from "$lib/components/ui/progress/index.js";
  import EmptyStatePanel from "../components/EmptyStatePanel.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import StatusBadge from "../components/StatusBadge.svelte";

  let {
    summary = {},
    facts = [],
    progress = {},
    assignee = null,
    meta = [],
  } = $props();

  let progressValue = $state(0);
  let progressValueInitialized = $state(false);
  const fallbackAvatar = (name = "User") =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(name || "User")}&background=251d39&color=f5c518&bold=true`;

  $effect(() => {
    if (!progressValueInitialized) {
      progressValue = Number(progress?.value || 0);
      progressValueInitialized = true;
    }
  });

  const actionVariant = (action) => {
    if (action?.variant) {
      return action.variant;
    }

    if (action?.tone === "primary") {
      return "default";
    }

    if (action?.tone === "danger") {
      return "destructive";
    }

    return "secondary";
  };

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || "User");

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };
</script>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
  <div class="lg:col-span-2">
    <Card.Root
      class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader
          title={summary.title || "Detail Task"}
          description={summary.description || ""}
          icon="fas fa-clipboard-check"
        />
        {#if summary.badges?.length}
          <div class="mt-4 flex flex-wrap gap-2">
            {#each summary.badges as badge, index (badge.label || index)}
              <StatusBadge
                label={badge.label}
                tone={badge.tone || "secondary"}
              />
            {/each}
          </div>
        {/if}
      </Card.Header>

      <Card.Content class="pt-5">
        {#if facts.length}
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {#each facts as fact, index (fact.label || index)}
              <div
                class="rounded-[10px] border border-border bg-background p-4"
              >
                <div class="mb-1 text-xs font-medium text-muted-foreground">
                  {fact.label}
                </div>
                {#if fact.href}
                  <a
                    href={fact.href}
                    class={`font-semibold text-foreground transition-colors hover:text-brand-primary ${fact.className || ""}`}
                    >{fact.value}</a
                  >
                {:else}
                  <div
                    class={`font-semibold text-foreground ${fact.className || ""}`}
                  >
                    {fact.value}
                  </div>
                {/if}
              </div>
            {/each}
          </div>
        {/if}

        {#if progress}
          <div
            class="mt-5 rounded-[10px] border border-border bg-background p-4 shadow-none"
          >
            <div class="mb-3 flex items-center justify-between gap-3">
              <Label
                class="text-xs font-medium text-muted-foreground"
                for="task-progress-range">Progress</Label
              >
              <strong class="text-lg font-semibold text-brand-primary"
                >{progressValue}%</strong
              >
            </div>

            <Progress value={progressValue} class="h-3" />

            {#if progress.canUpdate}
              <form
                method="POST"
                action={progress.action}
                class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center"
              >
                <input type="hidden" name="_token" value={progress.csrfToken} />
                <input type="hidden" name="_method" value="PATCH" />
                <input
                  id="task-progress-range"
                  type="range"
                  name="progress"
                  min="0"
                  max="100"
                  step="5"
                  class="flex-1 accent-[var(--brand-primary)]"
                  bind:value={progressValue}
                />
                <Button
                  type="submit"
                  size="sm"
                  class="w-full shrink-0 sm:w-auto"
                >
                  <i class="fas fa-save"></i>
                  <span>Update</span>
                </Button>
              </form>
            {/if}
          </div>
        {/if}

        {#if summary.actions?.length}
          <div class="mt-6 flex flex-wrap gap-3">
            {#each summary.actions as action, index (action.href || action.label || index)}
              <Button href={action.href} variant={actionVariant(action)}>
                <i class={action.icon}></i>
                <span>{action.label}</span>
              </Button>
            {/each}
          </div>
        {/if}
      </Card.Content>
    </Card.Root>
  </div>

  <div class="flex flex-col gap-4">
    <Card.Root
      class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader
          title="Ditugaskan Kepada"
          icon="fas fa-user"
          compact={true}
          headingTag="h3"
        />
      </Card.Header>

      <Card.Content class="pt-5">
        {#if assignee}
          <div class="grid justify-items-center gap-2 text-center">
            <img
              src={assignee.avatar || fallbackAvatar(assignee.name)}
              alt={assignee.name}
              class="h-20 w-20 rounded-full border border-border object-cover shadow-none"
              onerror={handleImageError}
            />
            <h5 class="m-0 mt-1 text-lg font-semibold">{assignee.name}</h5>
            <p class="m-0 text-sm text-muted-foreground">{assignee.email}</p>
            <StatusBadge
              label={assignee.roleLabel}
              tone={assignee.roleTone || "secondary"}
            />
          </div>
        {:else}
          <EmptyStatePanel
            title="Belum ada assignee"
            text="Belum ditugaskan."
            icon="fas fa-user-slash"
            tone="secondary"
            compact={true}
          />
        {/if}
      </Card.Content>
    </Card.Root>

    <Card.Root
      class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader
          title="Info"
          icon="fas fa-info-circle"
          compact={true}
          headingTag="h3"
        />
      </Card.Header>

      <Card.Content class="pt-5">
        <div class="grid gap-3">
          {#each meta as item, index (item.label || index)}
            <div
              class="flex flex-col items-start gap-1 border-b border-border/40 py-2 last:border-0 sm:flex-row sm:items-center sm:justify-between sm:gap-3"
            >
              <span class="text-sm text-muted-foreground">{item.label}</span>
              <strong class="text-sm sm:text-right">{item.value}</strong>
            </div>
          {/each}
        </div>
      </Card.Content>
    </Card.Root>
  </div>
</div>
