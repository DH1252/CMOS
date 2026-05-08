<script>
  import { Progress } from "$lib/components/ui/progress/index.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import EmptyStatePanel from "../components/EmptyStatePanel.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import StatusBadge from "../components/StatusBadge.svelte";

  let {
    title = "Evaluasi Staff",
    description = "",
    month = { value: "", label: "" },
    months = [],
    monthAction = "",
    monthParam = "month",
    ranking = [],
    departments = [],
    emptyRanking = {
      title: "Belum ada data evaluasi",
      text: "Belum ada data evaluasi.",
    },
    emptyDepartments = {
      title: "Belum ada departemen",
      text: "Belum ada departemen.",
    },
  } = $props();

  let monthForm = $state(null);
  let selectedMonth = $state("");
  const fallbackAvatar = (name = "User") =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(name || "User")}&background=251d39&color=f5c518&bold=true`;

  $effect(() => {
    selectedMonth = month?.value || "";
  });

  const submitMonth = () => {
    monthForm?.requestSubmit();
  };

  const safeNumber = (value) => {
    const numeric = Number(value ?? 0);
    return Number.isFinite(numeric) ? numeric : 0;
  };

  const completionPercent = (department) => {
    if (!department?.totalStaff) {
      return 0;
    }

    return Math.round(
      (safeNumber(department.evaluatedStaff) / department.totalStaff) * 100,
    );
  };

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || "User");

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };
</script>

<div class="grid gap-4 xl:grid-cols-[minmax(0,1.08fr)_22rem]">
  <div class="grid gap-4">
    <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader {title} {description} icon="fas fa-users-gear" />
      </Card.Header>
      <Card.Content class="grid gap-5 pt-5">
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_15rem] lg:items-end">
          <div class="space-y-3">
            <div class="text-sm text-muted-foreground">Periode aktif</div>
            <div class="text-2xl font-semibold text-foreground">
              {month?.label || "Belum dipilih"}
            </div>
            <p class="max-w-[58ch] text-sm leading-7 text-muted-foreground">
              Pilih periode, lalu masuk ke departemen untuk meninjau evaluasi
              staff.
            </p>
          </div>

          <form
            method="GET"
            action={monthAction}
            bind:this={monthForm}
            class="grid gap-2"
          >
            <Label for="evaluation-month-select">Periode evaluasi</Label>
            <select
              id="evaluation-month-select"
              name={monthParam}
              class="h-11 rounded-[10px] border border-border bg-background px-3 text-sm text-foreground transition-colors outline-none focus:border-brand-primary"
              bind:value={selectedMonth}
              onchange={submitMonth}
              aria-label="Pilih bulan evaluasi"
            >
              {#each months as option, index (option.value || index)}
                <option value={option.value}>{option.label}</option>
              {/each}
            </select>
          </form>
        </div>

        {#if departments.length}
          <div class="grid gap-4 md:grid-cols-2">
            {#each departments as department, index (department.href || department.name || index)}
              <a
                href={department.href}
                class="rounded-[10px] border border-border bg-background px-5 py-5 text-inherit no-underline transition-colors hover:bg-muted"
              >
                <div class="flex items-start justify-between gap-4">
                  <div class="min-w-0">
                    <h3
                      class="text-xl leading-snug font-semibold text-foreground"
                    >
                      {department.name}
                    </h3>
                    <p class="mt-2 text-sm leading-7 text-muted-foreground">
                      {department.description}
                    </p>
                  </div>
                  <div class="text-sm font-semibold text-brand-primary">
                    {completionPercent(department)}%
                  </div>
                </div>

                <div class="mt-4 h-2 overflow-hidden rounded-full bg-muted">
                  <div
                    class="h-full rounded-full bg-brand-primary"
                    style={`width:${completionPercent(department)}%`}
                  ></div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                  <StatusBadge
                    label={`${department.totalStaff} staff`}
                    icon="fas fa-user-group"
                    tone="secondary"
                  />
                  <StatusBadge
                    label={`${department.evaluatedStaff} dinilai`}
                    icon="fas fa-check-circle"
                    tone="success"
                  />
                </div>
              </a>
            {/each}
          </div>
        {:else}
          <EmptyStatePanel
            title={emptyDepartments.title}
            text={emptyDepartments.text}
            icon="fas fa-building"
            tone="info"
            compact={true}
          />
        {/if}
      </Card.Content>
    </Card.Root>
  </div>

  <Card.Root
    class="rounded-[10px] border border-border bg-card shadow-none xl:sticky xl:top-[5.5rem] xl:self-start"
  >
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader
        title="Best staff of the month"
        description={month?.label || ""}
        icon="fas fa-trophy"
        compact={true}
        headingTag="h3"
      />
    </Card.Header>
    <Card.Content class="grid gap-3 pt-5">
      {#if ranking.length}
        {#each ranking as item, index (item.name || index)}
          <div
            class="grid grid-cols-[auto_auto_minmax(0,1fr)_auto] items-center gap-3 rounded-[10px] border border-border bg-background px-4 py-3"
          >
            <div
              class="flex h-8 w-8 items-center justify-center rounded-full border border-border text-xs font-semibold text-muted-foreground"
            >
              {index + 1}
            </div>
            <img
              src={item.avatar || fallbackAvatar(item.name)}
              alt={item.name}
              class="h-10 w-10 rounded-full object-cover"
              width="40"
              height="40"
              loading="lazy"
              decoding="async"
              onerror={handleImageError}
            />
            <div class="min-w-0">
              <strong class="block truncate text-sm text-foreground"
                >{item.name}</strong
              >
              <span class="block truncate text-sm text-muted-foreground"
                >{item.department}</span
              >
            </div>
            <div class="text-right">
              <div class="text-sm font-semibold text-brand-primary">
                {item.score}
              </div>
              {#if item.grade}
                <span
                  class="mt-1 inline-flex rounded-[8px] bg-muted px-2 py-1 text-xs text-muted-foreground"
                  >{item.grade.label}</span
                >
              {/if}
            </div>
          </div>
        {/each}
      {:else}
        <EmptyStatePanel
          title={emptyRanking.title}
          text={emptyRanking.text}
          icon="fas fa-chart-line"
          tone="warning"
          compact={true}
        />
      {/if}
    </Card.Content>
  </Card.Root>
</div>
