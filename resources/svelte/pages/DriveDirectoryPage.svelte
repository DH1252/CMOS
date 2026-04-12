<script>
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import { Input } from '$lib/components/ui/input/index.js';
  import EmptyStatePanel from '../components/EmptyStatePanel.svelte';
  import PageHeader from '../components/PageHeader.svelte';
  import StatusBadge from '../components/StatusBadge.svelte';

  let {
    title = 'Google Drive Organisasi',
    description = '',
    icon = 'fab fa-google-drive',
    primaryAction = null,
    groups = [],
    emptyState = {
      title: 'Belum ada drive',
      text: 'Akun Google Drive organisasi akan muncul di halaman ini setelah ditambahkan.',
    },
    csrfToken = '',
  } = $props();

  let credentialPanel = $state(null);
  let activeDriveId = $state(null);

  const flattenedDrives = () =>
    groups.flatMap((group) =>
      (group.cards || []).map((card) => ({
        ...card,
        groupName: group.name,
        groupIcon: group.icon,
      })),
    );

  const drives = $derived.by(() => flattenedDrives());
  const activeDrive = $derived.by(() => drives.find((drive) => drive.id === activeDriveId) || drives[0] || null);

  const confirmSubmission = async (event, action) => {
    if (!action?.confirm) {
      return;
    }

    event.preventDefault();

    const text = action.confirmText || `Lanjutkan tindakan untuk ${action.confirm}?`;

    if (window.Swal) {
      const result = await window.Swal.fire({
        title: action.confirmTitle || 'Konfirmasi',
        text,
        icon: action.confirmIcon || 'warning',
        showCancelButton: true,
        confirmButtonText: action.confirmButtonText || 'Lanjutkan',
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

  const notify = (titleText, text, icon = 'success') => {
    if (window.Swal) {
      window.Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title: titleText,
        text,
        showConfirmButton: false,
        timer: 1800,
      });
      return;
    }

    window.alert(text);
  };

  const copyValue = async (value, label) => {
    try {
      if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(value);
      } else {
        const input = document.createElement('input');
        input.value = value;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        input.remove();
      }

      notify('Disalin', `${label} berhasil disalin.`);
    } catch (error) {
      notify('Gagal menyalin', `Tidak bisa menyalin ${label.toLowerCase()}.`, 'error');
    }
  };

  const selectDrive = (drive) => {
    activeDriveId = drive.id;
    requestAnimationFrame(() => {
      credentialPanel?.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
    });
  };
</script>

<Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
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
  <Card.Root class="mt-4 animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Content class="pt-5">
      <EmptyStatePanel title={emptyState.title} text={emptyState.text} icon="fab fa-google-drive" tone="secondary" />
    </Card.Content>
  </Card.Root>
{:else}
  {#if activeDrive}
    <Card.Root bind:this={credentialPanel} class="access-stage animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
      <Card.Content class="pt-5">
        <div class="access-stage-head">
          <div>
            <div class="access-stage-kicker">Akses cepat</div>
            <h4>{activeDrive.title}</h4>
            <p>{activeDrive.groupName}</p>
          </div>

          <div class="access-stage-badges">
            {#each activeDrive.badges || [] as badge, index (badge.label || index)}
              <StatusBadge label={badge.label} icon={badge.icon || ''} tone={badge.tone || 'secondary'} />
            {/each}
          </div>
        </div>

        <div class="access-stage-grid">
          <div class="access-stage-field">
            <label for="drive-access-email">Email Google</label>
            <div class="access-stage-input">
              <Input id="drive-access-email" type="text" class="access-stage-control" value={activeDrive.email} readonly />
              <Button type="button" variant="secondary" size="sm" onclick={() => copyValue(activeDrive.email, 'Email')}>
                <i class="fas fa-copy"></i>
                <span>Salin</span>
              </Button>
            </div>
          </div>

          <div class="access-stage-field">
            <label for="drive-access-password">Password</label>
            <div class="access-stage-input">
              <Input id="drive-access-password" type="text" class="access-stage-control" value={activeDrive.password} readonly />
              <Button type="button" variant="secondary" size="sm" onclick={() => copyValue(activeDrive.password, 'Password')}>
                <i class="fas fa-copy"></i>
                <span>Salin</span>
              </Button>
            </div>
          </div>
        </div>

        <div class="access-stage-foot">
          <p>
            Gunakan kredensial ini untuk masuk ke Google Drive organisasi, lalu buka folder yang sesuai dengan kebutuhan kerja departemen.
          </p>
          <Button href={activeDrive.href} target="_blank" rel="noreferrer">
            <i class="fab fa-google-drive"></i>
            <span>Buka Google Drive</span>
          </Button>
        </div>
      </Card.Content>
    </Card.Root>
  {/if}

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
          <StatusBadge label={`${group.cards.length} akun`} tone="secondary" />
        </div>

        <div class="directory-grid">
          {#each group.cards as card, cardIndex (card.id || cardIndex)}
            <Card.Root class={`directory-card ${activeDrive?.id === card.id ? 'directory-card-active' : ''}`.trim()}>
              <Card.Content class="pt-5">
                <div class="directory-card-top">
                  <div class="directory-card-icon">
                    <i class={card.icon || group.icon || icon}></i>
                  </div>

                  {#if card.badges?.length}
                    <div class="directory-card-badges">
                      {#each card.badges as badge, badgeIndex (badge.label || badgeIndex)}
                        <StatusBadge label={badge.label} icon={badge.icon || ''} tone={badge.tone || 'secondary'} />
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
                        <div class={line.muted ? 'text-muted fs-sm' : 'fs-sm'}>{line.text}</div>
                      {/each}
                    </div>
                  {/if}
                </div>

                <div class="directory-card-actions">
                  <Button type="button" variant={activeDrive?.id === card.id ? 'secondary' : 'default'} size="sm" onclick={() => selectDrive(card)}>
                    <i class="fas fa-key"></i>
                    <span>{activeDrive?.id === card.id ? 'Terpilih' : 'Lihat Kredensial'}</span>
                  </Button>

                  {#if card.editHref}
                    <Button href={card.editHref} variant="secondary" size="icon-sm" title="Edit" aria-label="Edit">
                      <i class="fas fa-pen"></i>
                    </Button>
                  {/if}

                  {#if card.deleteAction}
                    <form method="POST" action={card.deleteAction} onsubmit={(event) => confirmSubmission(event, card)}>
                      <input type="hidden" name="_token" value={csrfToken} />
                      {#if card.deleteMethod}
                        <input type="hidden" name="_method" value={card.deleteMethod} />
                      {/if}
                      <Button type="submit" variant="destructive" size="icon-sm" title="Hapus" aria-label="Hapus">
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

  .access-stage {
    margin-top: 1.5rem;
    background: var(--card);
  }

  .access-stage-head {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
  }

  .access-stage-kicker {
    margin-bottom: 0.35rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--text-muted);
  }

  .access-stage-head h4 {
    margin: 0 0 0.25rem;
    font-size: 1.25rem;
    font-weight: 600;
  }

  .access-stage-head p {
    margin: 0;
    color: var(--text-soft);
  }

  .access-stage-badges {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 0.35rem;
  }

  .access-stage-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-top: 1.25rem;
  }

  .access-stage-field {
    display: grid;
    gap: 0.45rem;
  }

  .access-stage-field label {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-muted);
  }

  .access-stage-input {
    display: flex;
    gap: 0.65rem;
  }

  :global(.access-stage-control) {
    background: var(--background);
    flex: 1;
  }

  .access-stage-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-top: 1.25rem;
  }

  .access-stage-foot p {
    margin: 0;
    max-width: 58ch;
    color: var(--text-soft);
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
    transition: background 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
  }

  .directory-card-active {
    border-color: color-mix(in srgb, var(--brand-primary) 34%, var(--line-soft));
    box-shadow: none;
    background: color-mix(in srgb, var(--brand-light) 10%, var(--card));
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
    .access-stage-head,
    .directory-group-head {
      align-items: flex-start;
      flex-direction: column;
    }

    .access-stage-badges {
      justify-content: flex-start;
    }

    .access-stage-grid {
      grid-template-columns: minmax(0, 1fr);
    }

    .access-stage-input,
    .access-stage-foot,
    .directory-card-actions {
      flex-wrap: wrap;
    }
  }
</style>
