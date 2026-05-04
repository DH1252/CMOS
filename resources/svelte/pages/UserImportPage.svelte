<script>
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import EmptyStatePanel from '../components/EmptyStatePanel.svelte';
  import PageHeader from '../components/PageHeader.svelte';
  import StatusBadge from '../components/StatusBadge.svelte';

  let {
    title = 'Import User dari CSV',
    description = '',
    form = {
      action: '#',
      csrfToken: '',
      templateUrl: '#',
    },
    roles = [],
    departments = [],
    errors = {},
    results = {
      success: [],
      errors: [],
    },
  } = $props();

  let fileName = $state('');

  const handleFileChange = (event) => {
    fileName = event.currentTarget.files?.[0]?.name || '';
  };
</script>

<Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
  <Card.Header class="border-b border-border/70 pb-4">
    <PageHeader {title} {description} icon="fas fa-file-csv" />
  </Card.Header>
</Card.Root>

<div class="import-grid">
  <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader
        title="Upload Data"
        icon="fas fa-upload"
        compact={true}
        headingTag="h3"
      />
    </Card.Header>

    <Card.Content class="pt-5">
      <form action={form.action} method="POST" enctype="multipart/form-data" class="import-form">
        <input type="hidden" name="_token" value={form.csrfToken} />

        <label class="import-dropzone" for="import-csv-file">
          <i class="fas fa-cloud-arrow-up"></i>
          <strong>{fileName || 'Pilih file CSV untuk diunggah'}</strong>
          <span>Maksimal 2MB, format `.csv` atau `.txt`.</span>
          <input id="import-csv-file" type="file" name="csv_file" accept=".csv,.txt" required onchange={handleFileChange} hidden />
        </label>

        {#if errors.csv_file}
          <div class="import-error" role="alert">{errors.csv_file}</div>
        {/if}

        <div class="import-actions">
          <Button type="submit">
            <i class="fas fa-upload"></i>
            <span>Import Users</span>
          </Button>
          <Button href={form.templateUrl} variant="secondary" data-native="true">
            <i class="fas fa-download"></i>
            <span>Download Template</span>
          </Button>
        </div>
      </form>
    </Card.Content>
  </Card.Root>

  <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader
        title="Panduan Format"
        icon="fas fa-circle-info"
        compact={true}
        headingTag="h3"
      />
    </Card.Header>

    <Card.Content class="import-guide pt-5">
      <div class="import-guide-table-wrap">
        <table class="import-guide-table">
          <thead>
            <tr>
              <th>Kolom</th>
              <th>Keterangan</th>
              <th>Wajib</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>name</code></td>
              <td>Nama lengkap user</td>
              <td>Ya</td>
            </tr>
            <tr>
              <td><code>email</code></td>
              <td>Email unik yang belum terdaftar</td>
              <td>Ya</td>
            </tr>
            <tr>
              <td><code>password</code></td>
              <td>Password minimal 6 karakter</td>
              <td>Ya</td>
            </tr>
            <tr>
              <td><code>role</code></td>
              <td>Gunakan salah satu slug role resmi</td>
              <td>Ya</td>
            </tr>
            <tr>
              <td><code>department</code></td>
              <td>Nama departemen, opsional untuk role non-staff</td>
              <td>Tidak</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="import-meta-row">
        <div>
          <span>Role tersedia</span>
          <div class="import-chip-row">
            {#each roles as role, index (role || index)}
              <StatusBadge label={role} tone="secondary" />
            {/each}
          </div>
        </div>

        <div>
          <span>Contoh departemen</span>
          <div class="import-chip-row">
            {#each departments.slice(0, 5) as department, index (department || index)}
              <StatusBadge label={department} tone="info" />
            {/each}
            {#if departments.length > 5}
              <StatusBadge label={`+${departments.length - 5} lainnya`} tone="secondary" />
            {/if}
          </div>
        </div>
      </div>

      <div class="import-sample">
        <strong>Contoh CSV</strong>
        <pre>name,email,password,role,department
John Doe,john@example.com,password123,staff,Divisi IT
Jane Doe,jane@example.com,password456,kabinet,Divisi Humas
Admin User,admin@example.com,securepass,bph,</pre>
      </div>
    </Card.Content>
  </Card.Root>
</div>

{#if results.success?.length || results.errors?.length}
  <div class="import-result-grid">
    {#if results.success?.length}
      <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
        <Card.Header class="border-b border-border/70 pb-4">
          <PageHeader
            title={`Berhasil (${results.success.length})`}
            icon="fas fa-circle-check"
            compact={true}
            headingTag="h3"
          />
        </Card.Header>

        <Card.Content class="import-results pt-5">
          {#each results.success as item, index (item.row || index)}
            <article class="import-result-item import-result-item-success">
              <span>Row {item.row}</span>
              <div>
                <strong>{item.data}</strong>
                <p>{item.message}</p>
              </div>
            </article>
          {/each}
        </Card.Content>
      </Card.Root>
    {/if}

    {#if results.errors?.length}
      <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
        <Card.Header class="border-b border-border/70 pb-4">
          <PageHeader
            title={`Gagal (${results.errors.length})`}
            icon="fas fa-circle-xmark"
            compact={true}
            headingTag="h3"
          />
        </Card.Header>

        <Card.Content class="import-results pt-5">
          {#each results.errors as item, index (item.row || index)}
            <article class="import-result-item import-result-item-error">
              <span>Row {item.row}</span>
              <div>
                <strong>{item.data}</strong>
                <p>{item.message}</p>
              </div>
            </article>
          {/each}
        </Card.Content>
      </Card.Root>
    {/if}
  </div>
{:else}
  <Card.Root class="mt-4 animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Content class="pt-5">
      <EmptyStatePanel
        title="Belum ada hasil impor"
        text="Unggah file CSV untuk melihat hasil impor."
        icon="fas fa-file-import"
        tone="secondary"
        compact={true}
      />
    </Card.Content>
  </Card.Root>
{/if}

<style>
  .import-grid,
  .import-result-grid {
    display: grid;
    gap: 1rem;
    margin-top: 1rem;
  }

  .import-grid {
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  }

  .import-result-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  }

  .import-form {
    display: grid;
    gap: 1rem;
  }

  .import-dropzone {
    display: grid;
    gap: 0.55rem;
    padding: 2rem 1.25rem;
    border-radius: 0.625rem;
    border: 1px dashed color-mix(in srgb, var(--brand-primary) 36%, var(--line-soft));
    background: var(--background);
    cursor: pointer;
    text-align: center;
    transition: background 160ms ease, border-color 160ms ease;
  }

  .import-dropzone:hover {
    background: var(--muted);
  }

  .import-dropzone i {
    font-size: 2.2rem;
    color: var(--brand-primary);
  }

  .import-dropzone span {
    color: var(--text-muted);
    font-size: 0.88rem;
  }

  .import-error {
    color: var(--signal-danger);
    font-size: 0.85rem;
  }

  .import-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .import-guide-table-wrap {
    overflow-x: auto;
  }

  .import-guide-table {
    width: 100%;
    border-collapse: collapse;
  }

  .import-guide-table th,
  .import-guide-table td {
    padding: 0.8rem 0.85rem;
    border-bottom: 1px solid var(--line-soft);
    text-align: left;
  }

  .import-guide-table th {
    color: var(--text-muted);
    font-size: 0.78rem;
    font-weight: 600;
  }

  :global(.import-guide) :global(code) {
    padding: 0.2rem 0.45rem;
    border-radius: 0.45rem;
    background: color-mix(in srgb, var(--brand-light) 24%, transparent);
    color: var(--brand-primary);
  }

  .import-meta-row {
    display: grid;
    gap: 1rem;
    margin-top: 1rem;
  }

  .import-meta-row span:first-child {
    display: block;
    margin-bottom: 0.45rem;
    color: var(--text-muted);
    font-size: 0.74rem;
    font-weight: 600;
  }

  .import-chip-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .import-sample {
    margin-top: 1rem;
    padding: 1rem;
    border-radius: 0.625rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
  }

  .import-sample strong {
    display: block;
    margin-bottom: 0.65rem;
  }

  .import-sample pre {
    margin: 0;
    overflow-x: auto;
    white-space: pre;
    color: var(--text-soft);
    font-size: 0.84rem;
  }

  :global(.import-results) {
    display: grid;
    gap: 0.8rem;
    max-height: 24rem;
    overflow-y: auto;
  }

  .import-result-item {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.8rem;
    padding: 0.95rem 1rem;
    border-radius: 0.625rem;
    border: 1px solid var(--line-soft);
    background: var(--background);
  }

  .import-result-item span {
    min-width: 4rem;
    color: var(--text-muted);
    font-size: 0.78rem;
    font-weight: 700;
  }

  .import-result-item p {
    margin: 0.2rem 0 0;
    color: var(--text-soft);
    line-height: 1.55;
  }

  .import-result-item-success {
    background: color-mix(in srgb, var(--signal-success) 10%, white);
  }

  .import-result-item-error {
    background: color-mix(in srgb, var(--signal-danger) 8%, white);
  }

  @media (max-width: 700px) {
    .import-actions :global([data-slot='button']) {
      width: 100%;
    }
  }
</style>
