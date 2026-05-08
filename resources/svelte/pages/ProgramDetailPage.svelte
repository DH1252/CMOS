<script>
  import { Button } from "$lib/components/ui/button/index.js";
  import {
    shouldSkipFormConfirmation,
    submitConfirmedForm,
  } from "$lib/confirmable-form.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import { Progress } from "$lib/components/ui/progress/index.js";
  import DataTable from "../components/DataTable.svelte";
  import EmptyStatePanel from "../components/EmptyStatePanel.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import StatusBadge from "../components/StatusBadge.svelte";

  let {
    summary = {},
    pics = {},
    team = {},
    tasks = {},
    timelines = {},
    csrfToken = "",
  } = $props();

  const submitRemoval = async (event, memberName) => {
    const form = event.currentTarget;

    if (shouldSkipFormConfirmation(form)) {
      return;
    }

    event.preventDefault();

    const text = `Hapus ${memberName} dari tim program ini?`;

    if (window.Swal) {
      const result = await window.Swal.fire({
        title: "Konfirmasi",
        text,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Hapus",
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

  const fallbackAvatar = (name = "User") =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(name || "User")}&background=251d39&color=f5c518&bold=true`;

  const handleImageError = (event) => {
    const nextSrc = fallbackAvatar(event.currentTarget.alt || "User");

    if (event.currentTarget.src === nextSrc) {
      return;
    }

    event.currentTarget.src = nextSrc;
  };
</script>

<div class="row">
  <div class="col-lg-4 col-12">
    <Card.Root
      class="animate-fadeIn mb-4 rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Content class="pt-5">
        <div class="detail-summary-head">
          <div class="detail-summary-icon">
            <i class="fas fa-diagram-project"></i>
          </div>
          <div>
            <h3 class="detail-summary-title">{summary.name}</h3>
            <StatusBadge
              label={summary.statusLabel}
              tone={summary.statusTone || "secondary"}
            />
          </div>
        </div>

        {#if summary.description}
          <p class="detail-summary-description">{summary.description}</p>
        {/if}

        <div class="detail-facts">
          {#each summary.facts || [] as fact, index (fact.label || index)}
            <div class="detail-fact-row">
              <span>{fact.label}</span>
              <strong>{fact.value}</strong>
            </div>
          {/each}
        </div>

        <div class="program-progress-shell">
          <div class="program-progress-head">
            <span>Progress</span>
            <strong>{summary.progress || 0}%</strong>
          </div>
          <Progress
            value={Number(summary.progress || 0)}
            class={`program-progress-bar ${Number(summary.progress || 0) >= 100 ? "success" : ""}`.trim()}
          />
        </div>

        <div class="program-summary-actions">
          {#each summary.actions || [] as action, index (action.href || action.label || index)}
            <Button
              href={action.href}
              variant={actionVariant(action)}
              class="flex-1"
            >
              {#if action.icon}
                <i class={action.icon}></i>
              {/if}
              <span>{action.label}</span>
            </Button>
          {/each}
        </div>
      </Card.Content>
    </Card.Root>

    <Card.Root
      class="animate-fadeIn mb-4 rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader
          title="PIC Program"
          icon="fas fa-star"
          compact={true}
          headingTag="h3"
        />
      </Card.Header>
      <Card.Content class="pt-5">
        {#if !pics.items?.length}
          <EmptyStatePanel
            title="Belum ada PIC"
            text="Belum ada penanggung jawab."
            icon="fas fa-star-half-stroke"
            tone="secondary"
            compact={true}
          />
        {:else}
          <div class="detail-list">
            {#each pics.items as pic, index (pic.name || index)}
              <div class="program-member">
                <div class="program-member-main">
                  <img
                    src={pic.avatar || fallbackAvatar(pic.name)}
                    alt={pic.name}
                    class="avatar-sm"
                    onerror={handleImageError}
                  />
                  <div class="program-member-copy">
                    <div class="fw-semibold">{pic.name}</div>
                    <StatusBadge label="PIC" tone="primary" />
                  </div>
                </div>
                {#if pic.removeAction}
                  <form
                    method="POST"
                    action={pic.removeAction}
                    onsubmit={(event) => submitRemoval(event, pic.name)}
                  >
                    <input type="hidden" name="_token" value={csrfToken} />
                    <input type="hidden" name="_method" value="DELETE" />
                    <Button
                      type="submit"
                      variant="destructive"
                      size="icon-sm"
                      aria-label={`Hapus ${pic.name}`}
                    >
                      <i class="fas fa-xmark"></i>
                    </Button>
                  </form>
                {/if}
              </div>
            {/each}
          </div>
        {/if}

        {#if pics.addAction}
          <form
            method="POST"
            action={pics.addAction}
            class="program-inline-form"
          >
            <input type="hidden" name="_token" value={csrfToken} />
            <div class="program-inline-field">
              <Label for="pic_user_id">Tambah PIC</Label>
              <select
                id="pic_user_id"
                name="user_id"
                class="program-select"
                required
              >
                <option value="">-- Pilih User --</option>
                {#each pics.availableUsers || [] as user, index (user.value || index)}
                  <option value={user.value}>{user.label}</option>
                {/each}
              </select>
            </div>
            <Button type="submit" class="w-full">
              <i class="fas fa-user-plus"></i>
              <span>Tambah PIC</span>
            </Button>
          </form>
        {/if}
      </Card.Content>
    </Card.Root>

    <Card.Root
      class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader
          title="Tim Program"
          icon="fas fa-users"
          compact={true}
          headingTag="h3"
        />
      </Card.Header>
      <Card.Content class="pt-5">
        {#if !team.members?.length}
          <EmptyStatePanel
            title="Belum ada anggota tim"
            text="Belum ada anggota."
            icon="fas fa-users-slash"
            tone="secondary"
            compact={true}
          />
        {:else}
          <div class="detail-list">
            {#each team.members as member, index (member.name || index)}
              <div class="program-member">
                <div class="program-member-main">
                  <img
                    src={member.avatar || fallbackAvatar(member.name)}
                    alt={member.name}
                    class="avatar-sm"
                    onerror={handleImageError}
                  />
                  <div class="program-member-copy">
                    <div class="fw-semibold">{member.name}</div>
                    <StatusBadge
                      label={member.roleLabel}
                      tone={member.roleTone || "secondary"}
                    />
                  </div>
                </div>
                {#if member.removeAction}
                  <form
                    method="POST"
                    action={member.removeAction}
                    onsubmit={(event) => submitRemoval(event, member.name)}
                  >
                    <input type="hidden" name="_token" value={csrfToken} />
                    <input type="hidden" name="_method" value="DELETE" />
                    <Button
                      type="submit"
                      variant="destructive"
                      size="icon-sm"
                      aria-label={`Hapus ${member.name}`}
                    >
                      <i class="fas fa-xmark"></i>
                    </Button>
                  </form>
                {/if}
              </div>
            {/each}
          </div>
        {/if}

        {#if team.addAction}
          <form
            method="POST"
            action={team.addAction}
            class="program-inline-form"
          >
            <input type="hidden" name="_token" value={csrfToken} />
            <div class="program-inline-field">
              <Label for="user_id">Tambah anggota</Label>
              <select
                id="user_id"
                name="user_id"
                class="program-select"
                required
              >
                <option value="">-- Pilih User --</option>
                {#each team.availableUsers || [] as user, index (user.value || index)}
                  <option value={user.value}>{user.label}</option>
                {/each}
              </select>
            </div>
            <div class="program-inline-field">
              <Label for="role">Peran</Label>
              <select id="role" name="role" class="program-select" required>
                <option value="member">Member</option>
                <option value="leader">Leader</option>
              </select>
            </div>
            <Button type="submit" class="w-full">
              <i class="fas fa-user-plus"></i>
              <span>Tambah ke Tim</span>
            </Button>
          </form>
        {/if}
      </Card.Content>
    </Card.Root>
  </div>

  <div class="col-lg-8 col-12">
    <Card.Root
      class="animate-fadeIn mb-4 rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader
          title="Task Program"
          icon="fas fa-list-check"
          action={tasks.createAction
            ? {
                href: tasks.createAction,
                label: "Tambah Task",
                icon: "fas fa-plus",
                tone: "primary",
              }
            : null}
          compact={true}
          headingTag="h3"
        />
      </Card.Header>

      <Card.Content class="px-0 pb-0">
        <DataTable
          columns={tasks.columns || []}
          rows={tasks.rows || []}
          {csrfToken}
          emptyState={{
            title: "Belum ada task",
            text: "Belum ada task.",
            icon: "fas fa-list-check",
          }}
        />
      </Card.Content>
    </Card.Root>

    <Card.Root
      class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
    >
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader
          title="Timeline Program"
          icon="fas fa-calendar-days"
          compact={true}
          headingTag="h3"
        />
      </Card.Header>

      <Card.Content class="pt-5">
        {#if !timelines.items?.length}
          <EmptyStatePanel
            title="Belum ada timeline"
            text="Belum ada agenda."
            icon="fas fa-calendar-xmark"
            tone="secondary"
            compact={true}
          />
        {:else}
          <div class="detail-list">
            {#each timelines.items as timeline, index (timeline.title || index)}
              <div class="program-timeline">
                <div
                  class="program-timeline-color"
                  style={`background:${timeline.color}`}
                ></div>
                <div class="program-timeline-copy">
                  <div class="fw-semibold">{timeline.title}</div>
                  <div class="fs-sm text-muted">{timeline.range}</div>
                  {#if timeline.description}
                    <div class="fs-sm text-muted">{timeline.description}</div>
                  {/if}
                </div>
              </div>
            {/each}
          </div>
        {/if}
      </Card.Content>
    </Card.Root>
  </div>
</div>

<style>
  .detail-summary-head {
    display: flex;
    gap: 1rem;
    align-items: center;
  }

  .detail-summary-icon {
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 0.625rem;
    display: grid;
    place-items: center;
    background: color-mix(in srgb, var(--brand-light) 18%, transparent);
    color: var(--brand-primary);
    font-size: 1.45rem;
  }

  .detail-summary-title {
    margin: 0 0 0.4rem;
    font-weight: 600;
  }

  .detail-summary-description {
    margin: 1rem 0 0;
    color: var(--text-soft);
  }

  .detail-facts,
  .detail-list {
    display: grid;
    gap: 0.75rem;
    margin-top: 1rem;
  }

  .detail-fact-row {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
  }

  .detail-fact-row span {
    color: var(--text-muted);
  }

  .detail-fact-row strong {
    text-align: right;
  }

  .program-progress-shell {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--line-soft);
  }

  .program-progress-head {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
  }

  .program-progress-head span {
    color: var(--text-muted);
  }

  .program-progress-head strong {
    font-weight: 600;
    color: var(--brand-primary);
  }

  .program-progress-bar :global([data-slot="progress-indicator"]) {
    background: var(--brand-primary);
  }

  .program-progress-bar.success :global([data-slot="progress-indicator"]) {
    background: color-mix(in srgb, var(--signal-success) 78%, black);
  }

  .program-summary-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1.5rem;
  }

  .program-member,
  .program-timeline {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid var(--line-soft);
  }

  .program-member:last-child,
  .program-timeline:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }

  .program-member-main {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .program-member-copy {
    display: grid;
    gap: 0.3rem;
  }

  .program-inline-form {
    display: grid;
    gap: 1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--line-soft);
  }

  .program-inline-field {
    display: grid;
    gap: 0.45rem;
  }

  .program-select {
    width: 100%;
    min-width: 0;
    height: 2.5rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--line-soft);
    border-radius: 0.5rem;
    background: var(--background);
    color: var(--text-strong);
    outline: none;
    transition:
      border-color 160ms ease,
      box-shadow 160ms ease;
  }

  .program-select:focus {
    border-color: color-mix(in srgb, var(--brand-primary) 32%, white);
    box-shadow: 0 0 0 3px
      color-mix(in srgb, var(--brand-primary) 15%, transparent);
  }

  .program-timeline {
    justify-content: flex-start;
  }

  .program-timeline-color {
    width: 0.65rem;
    min-width: 0.65rem;
    height: 3.4rem;
    border-radius: 999px;
  }

  .program-timeline-copy {
    display: grid;
    gap: 0.22rem;
  }

  @media (max-width: 767px) {
    .program-member,
    .program-timeline,
    .detail-fact-row {
      flex-direction: column;
      align-items: flex-start;
    }
  }
</style>
