<script>
  import * as Card from '$lib/components/ui/card/index.js';
  import { Input } from '$lib/components/ui/input/index.js';
  import { Label } from '$lib/components/ui/label/index.js';
  import { Textarea } from '$lib/components/ui/textarea/index.js';
  import FormActions from '../components/FormActions.svelte';
  import PageHeader from '../components/PageHeader.svelte';

  let {
    title = 'Tambah Task',
    description = '',
    form = {
      action: '#',
      method: 'POST',
      csrfToken: '',
    },
    taskType = 'program',
    typeLocked = false,
    typeId = null,
    values = {},
    users = [],
    programs = [],
    departments = [],
    errors = {},
    cancelAction = null,
  } = $props();

  const formId = 'task-form';
  let selectedType = $state('program');
  let selectedTypeInitialized = $state(false);

  $effect(() => {
    if (!selectedTypeInitialized) {
      selectedType = taskType || 'program';
      selectedTypeInitialized = true;
    }
  });
</script>

<div class="row justify-center">
  <div class="col-12 col-lg-8">
    <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader {title} {description} icon="fas fa-plus" />
      </Card.Header>

      <Card.Content class="pt-5">
        <form id={formId} method="POST" action={form.action} class="task-form-grid">
          <input type="hidden" name="_token" value={form.csrfToken} />
          <input type="hidden" name="type" value={selectedType} />

          {#if typeLocked && selectedType === 'program' && typeId}
            <input type="hidden" name="program_id" value={typeId} />
          {/if}
          {#if typeLocked && selectedType === 'department' && typeId}
            <input type="hidden" name="department_id" value={typeId} />
          {/if}

          <div class="task-form-field">
            <Label for="task-title">
              Judul Task
              <span class="task-required">*</span>
            </Label>
            <Input id="task-title" name="title" type="text" class="task-form-input" aria-invalid={Boolean(errors.title)} value={values.title || ''} required />
            {#if errors.title}
              <div class="task-form-error" role="alert">{errors.title}</div>
            {/if}
          </div>

          <div class="task-form-field">
            <Label for="task-description">Deskripsi</Label>
            <Textarea id="task-description" name="description" rows="4" class="task-form-textarea" aria-invalid={Boolean(errors.description)} value={values.description || ''} />
            {#if errors.description}
              <div class="task-form-error" role="alert">{errors.description}</div>
            {/if}
          </div>

          {#if !typeLocked}
            <div class="task-form-field task-form-field-half">
              <Label for="task-type">
                Tipe Task
                <span class="task-required">*</span>
              </Label>
              <select id="task-type" class="task-form-select" bind:value={selectedType}>
                <option value="program">Program</option>
                <option value="department">Departemen</option>
                <option value="global">Global</option>
              </select>
            </div>
          {/if}

          {#if !typeLocked && selectedType === 'program'}
            <div class="task-form-field task-form-field-half">
              <Label for="task-program">
                Program
                <span class="task-required">*</span>
              </Label>
              <select id="task-program" name="program_id" class="task-form-select" aria-invalid={Boolean(errors.program_id)}>
                <option value="">-- Pilih Program --</option>
                {#each programs as program, index (program.value || index)}
                  <option value={program.value} selected={String(values.program_id || '') === String(program.value)}>{program.label}</option>
                {/each}
              </select>
              {#if errors.program_id}
                <div class="task-form-error" role="alert">{errors.program_id}</div>
              {/if}
            </div>
          {/if}

          {#if !typeLocked && selectedType === 'department'}
            <div class="task-form-field task-form-field-half">
              <Label for="task-department">
                Departemen
                <span class="task-required">*</span>
              </Label>
              <select id="task-department" name="department_id" class="task-form-select" aria-invalid={Boolean(errors.department_id)}>
                <option value="">-- Pilih Departemen --</option>
                {#each departments as department, index (department.value || index)}
                  <option value={department.value} selected={String(values.department_id || '') === String(department.value)}>{department.label}</option>
                {/each}
              </select>
              {#if errors.department_id}
                <div class="task-form-error" role="alert">{errors.department_id}</div>
              {/if}
            </div>
          {/if}

          <div class="task-form-field task-form-field-half">
            <Label for="task-assignee">Ditugaskan Kepada</Label>
            <select id="task-assignee" name="assigned_to" class="task-form-select" aria-invalid={Boolean(errors.assigned_to)}>
              <option value="">-- Pilih Staff --</option>
              {#each users as user, index (user.value || index)}
                <option value={user.value} selected={String(values.assigned_to || '') === String(user.value)}>{user.label}</option>
              {/each}
            </select>
            {#if errors.assigned_to}
              <div class="task-form-error" role="alert">{errors.assigned_to}</div>
            {/if}
          </div>

          <div class="task-form-field task-form-field-half">
            <Label for="task-priority">
              Prioritas
              <span class="task-required">*</span>
            </Label>
            <select id="task-priority" name="priority" class="task-form-select" aria-invalid={Boolean(errors.priority)} required>
              <option value="low" selected={String(values.priority || '') === 'low'}>Rendah</option>
              <option value="medium" selected={String(values.priority || 'medium') === 'medium'}>Sedang</option>
              <option value="high" selected={String(values.priority || '') === 'high'}>Tinggi</option>
            </select>
            {#if errors.priority}
              <div class="task-form-error" role="alert">{errors.priority}</div>
            {/if}
          </div>

          <div class="task-form-field task-form-field-half">
            <Label for="task-deadline">Deadline</Label>
            <Input id="task-deadline" name="deadline" type="date" class="task-form-input" aria-invalid={Boolean(errors.deadline)} value={values.deadline || ''} />
            {#if errors.deadline}
              <div class="task-form-error" role="alert">{errors.deadline}</div>
            {/if}
          </div>
        </form>

        <FormActions formId={formId} submitLabel="Simpan" submitIcon="fas fa-save" {cancelAction} />
      </Card.Content>
    </Card.Root>
  </div>
</div>

<style>
  .task-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .task-form-field {
    grid-column: 1 / -1;
    display: grid;
    gap: 0.45rem;
  }

  .task-form-field-half {
    grid-column: span 1;
  }

  .task-required {
    color: var(--signal-danger);
  }

  :global(.task-form-input),
  :global(.task-form-textarea) {
    background: var(--background);
  }

  .task-form-select {
    width: 100%;
    min-width: 0;
    height: 2.5rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--line-soft);
    border-radius: 0.5rem;
    background: var(--background);
    color: var(--text-strong);
    outline: none;
    transition: border-color 160ms ease, box-shadow 160ms ease;
  }

  .task-form-select:focus {
    border-color: color-mix(in srgb, var(--brand-primary) 32%, white);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-primary) 15%, transparent);
  }

  .task-form-select[aria-invalid='true'] {
    border-color: color-mix(in srgb, var(--signal-danger) 42%, white);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--signal-danger) 12%, transparent);
  }

  .task-form-error {
    color: var(--signal-danger);
    font-size: 0.85rem;
  }

  @media (max-width: 767px) {
    .task-form-grid {
      grid-template-columns: minmax(0, 1fr);
    }

    .task-form-field-half {
      grid-column: 1 / -1;
    }
  }
</style>
