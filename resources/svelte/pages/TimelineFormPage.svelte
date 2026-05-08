<script>
  import * as Card from "$lib/components/ui/card/index.js";
  import { Input } from "$lib/components/ui/input/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import { Textarea } from "$lib/components/ui/textarea/index.js";
  import FormActions from "../components/FormActions.svelte";
  import PageHeader from "../components/PageHeader.svelte";

  let {
    title = "Form Timeline",
    description = "",
    form = {
      action: "#",
      method: "POST",
      spoofMethod: null,
      csrfToken: "",
    },
    submitLabel = "Simpan Timeline",
    values = {},
    departments = [],
    programs = [],
    errors = {},
    cancelAction = null,
  } = $props();

  const formId = "timeline-form";
  let selectedType = $state("global");
  let startDate = $state("");
  let endDate = $state("");
  let timelineFormInitialized = $state(false);

  const typeColors = {
    global: "#7751DE",
    department: "#D4A017",
    program: "#3F7A50",
  };

  const selectedColor = $derived(typeColors[selectedType] || "#786F62");

  $effect(() => {
    if (!timelineFormInitialized) {
      selectedType = values.type || "global";
      startDate = values.start_date || "";
      endDate = values.end_date || "";
      timelineFormInitialized = true;
    }
  });

  const typeLabels = {
    global: "Global",
    department: "Departemen",
    program: "Program",
  };

  const durationLabel = () => {
    if (!startDate || !endDate) {
      return "Atur tanggal mulai dan selesai.";
    }

    const start = new Date(startDate);
    const end = new Date(endDate);
    if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
      return "Format tanggal tidak valid.";
    }

    const diff = Math.round((end - start) / 86400000) + 1;
    if (diff < 1) {
      return "Tanggal selesai harus sama atau setelah tanggal mulai.";
    }

    if (diff === 1) {
      return "Agenda berlangsung 1 hari.";
    }

    return `Agenda berlangsung ${diff} hari.`;
  };
</script>

<div class="row justify-center">
  <div class="col-lg-10 col-12">
    <div class="timeline-form-shell">
      <section class="timeline-form-side animate-fadeIn">
        <div class="timeline-form-hero">
          <p class="timeline-form-kicker">Timeline Planner</p>
          <h3>{title}</h3>
          {#if description}
            <p>{description}</p>
          {/if}
        </div>

        <div class="timeline-form-preview">
          <div
            class="timeline-preview-swatch"
            style={`background:${selectedColor};`}
          ></div>
          <div>
            <small>Tipe</small>
            <strong>{typeLabels[selectedType] || "Timeline"}</strong>
          </div>
        </div>

        <div class="timeline-form-side-card">
          <small>Rentang</small>
          <strong>{durationLabel()}</strong>
        </div>
      </section>

      <Card.Root
        class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
      >
        <Card.Header class="border-b border-border/70 pb-4">
          <PageHeader {title} {description} icon="fas fa-calendar-days" />
        </Card.Header>

        <Card.Content class="pt-5">
          <form
            id={formId}
            method="POST"
            action={form.action}
            class="timeline-form-grid"
          >
            <input type="hidden" name="_token" value={form.csrfToken} />
            {#if form.spoofMethod}
              <input type="hidden" name="_method" value={form.spoofMethod} />
            {/if}

            <div class="timeline-form-field">
              <Label for="timeline-title">
                Judul
                <span class="timeline-required">*</span>
              </Label>
              <Input
                id="timeline-title"
                name="title"
                type="text"
                class="timeline-form-input"
                aria-invalid={Boolean(errors.title)}
                value={values.title || ""}
                required
              />
              {#if errors.title}
                <div class="timeline-form-error" role="alert">
                  {errors.title}
                </div>
              {/if}
            </div>

            <div class="timeline-form-field">
              <Label for="timeline-description">Deskripsi</Label>
              <Textarea
                id="timeline-description"
                name="description"
                rows="4"
                class="timeline-form-textarea"
                aria-invalid={Boolean(errors.description)}
                value={values.description || ""}
              />
              {#if errors.description}
                <div class="timeline-form-error" role="alert">
                  {errors.description}
                </div>
              {/if}
            </div>

            <div class="timeline-form-field timeline-form-field-half">
              <Label for="timeline-type">
                Tipe
                <span class="timeline-required">*</span>
              </Label>
              <select
                id="timeline-type"
                name="type"
                class="timeline-form-select"
                bind:value={selectedType}
                aria-invalid={Boolean(errors.type)}
                required
              >
                <option value="global">Global</option>
                <option value="department">Departemen</option>
                <option value="program">Program</option>
              </select>
              {#if errors.type}
                <div class="timeline-form-error" role="alert">
                  {errors.type}
                </div>
              {/if}
            </div>

            <div class="timeline-form-field timeline-form-field-half">
              <Label for="timeline-color">Warna (otomatis)</Label>
              <div
                id="timeline-color"
                class="timeline-color-field"
                aria-live="polite"
              >
                <span
                  class="timeline-color-swatch"
                  style={`background:${selectedColor};`}
                  aria-hidden="true"
                ></span>
                <span class="timeline-color-code">{selectedColor}</span>
              </div>
            </div>

            {#if selectedType === "department"}
              <div class="timeline-form-field">
                <Label for="timeline-department">
                  Departemen
                  <span class="timeline-required">*</span>
                </Label>
                <select
                  id="timeline-department"
                  name="department_id"
                  class="timeline-form-select"
                  aria-invalid={Boolean(errors.department_id)}
                >
                  <option value="">-- Pilih Departemen --</option>
                  {#each departments as department, index (department.value || index)}
                    <option
                      value={department.value}
                      selected={String(values.department_id || "") ===
                        String(department.value)}>{department.label}</option
                    >
                  {/each}
                </select>
                {#if errors.department_id}
                  <div class="timeline-form-error" role="alert">
                    {errors.department_id}
                  </div>
                {/if}
              </div>
            {/if}

            {#if selectedType === "program"}
              <div class="timeline-form-field">
                <Label for="timeline-program">
                  Program
                  <span class="timeline-required">*</span>
                </Label>
                <select
                  id="timeline-program"
                  name="program_id"
                  class="timeline-form-select"
                  aria-invalid={Boolean(errors.program_id)}
                >
                  <option value="">-- Pilih Program --</option>
                  {#each programs as program, index (program.value || index)}
                    <option
                      value={program.value}
                      selected={String(values.program_id || "") ===
                        String(program.value)}>{program.label}</option
                    >
                  {/each}
                </select>
                {#if errors.program_id}
                  <div class="timeline-form-error" role="alert">
                    {errors.program_id}
                  </div>
                {/if}
              </div>
            {/if}

            <div class="timeline-form-field timeline-form-field-half">
              <Label for="timeline-start">
                Tanggal Mulai
                <span class="timeline-required">*</span>
              </Label>
              <Input
                id="timeline-start"
                name="start_date"
                type="date"
                class="timeline-form-input"
                aria-invalid={Boolean(errors.start_date)}
                bind:value={startDate}
                required
              />
              {#if errors.start_date}
                <div class="timeline-form-error" role="alert">
                  {errors.start_date}
                </div>
              {/if}
            </div>

            <div class="timeline-form-field timeline-form-field-half">
              <Label for="timeline-end">
                Tanggal Selesai
                <span class="timeline-required">*</span>
              </Label>
              <Input
                id="timeline-end"
                name="end_date"
                type="date"
                class="timeline-form-input"
                aria-invalid={Boolean(errors.end_date)}
                bind:value={endDate}
                required
              />
              {#if errors.end_date}
                <div class="timeline-form-error" role="alert">
                  {errors.end_date}
                </div>
              {/if}
            </div>
          </form>

          <FormActions
            {formId}
            {submitLabel}
            submitIcon="fas fa-save"
            {cancelAction}
          />
        </Card.Content>
      </Card.Root>
    </div>
  </div>
</div>

<style>
  .timeline-form-shell {
    display: grid;
    grid-template-columns: minmax(0, 18rem) minmax(0, 1fr);
    gap: 1.25rem;
  }

  .timeline-form-side {
    display: grid;
    gap: 1rem;
    align-content: start;
  }

  .timeline-form-hero,
  .timeline-form-side-card,
  .timeline-form-preview {
    padding: 1.15rem;
    border-radius: 0.625rem;
    background: var(--background);
    border: 1px solid var(--line-soft);
    box-shadow: none;
  }

  .timeline-form-kicker,
  .timeline-form-side-card small,
  .timeline-form-preview small {
    display: block;
    margin: 0 0 0.35rem;
    color: var(--text-muted);
    font-size: 0.78rem;
    font-weight: 600;
  }

  .timeline-form-hero h3 {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 600;
  }

  .timeline-form-hero p:last-child {
    margin: 0.5rem 0 0;
    color: var(--text-soft);
  }

  .timeline-form-preview {
    display: flex;
    align-items: center;
    gap: 0.9rem;
  }

  .timeline-preview-swatch {
    width: 3.1rem;
    height: 3.1rem;
    border-radius: 1rem;
    flex-shrink: 0;
    box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.28);
  }

  .timeline-form-preview strong,
  .timeline-form-side-card strong {
    font-size: 1rem;
  }

  .timeline-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .timeline-form-field {
    grid-column: 1 / -1;
    display: grid;
    gap: 0.45rem;
  }

  .timeline-form-field-half {
    grid-column: span 1;
  }

  .timeline-required {
    color: var(--signal-danger);
  }

  :global(.timeline-form-input),
  :global(.timeline-form-textarea) {
    background: var(--background);
  }

  .timeline-form-select {
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

  .timeline-form-select:focus {
    border-color: color-mix(in srgb, var(--brand-primary) 32%, white);
    box-shadow: 0 0 0 3px
      color-mix(in srgb, var(--brand-primary) 15%, transparent);
  }

  .timeline-form-select[aria-invalid="true"] {
    border-color: color-mix(in srgb, var(--signal-danger) 42%, white);
    box-shadow: 0 0 0 3px
      color-mix(in srgb, var(--signal-danger) 12%, transparent);
  }

  .timeline-form-error {
    color: var(--signal-danger);
    font-size: 0.85rem;
  }

  .timeline-color-field {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.55rem 0.7rem;
    border-radius: 0.625rem;
    border: 1px solid var(--line-soft);
    background: var(--background);
  }

  .timeline-color-swatch {
    width: 3rem;
    height: 2.25rem;
    border-radius: 0.5rem;
    border: 1px solid color-mix(in srgb, var(--line-soft) 75%, transparent);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.18);
  }

  .timeline-color-code {
    font-weight: 700;
    color: var(--text-soft);
  }

  @media (max-width: 1024px) {
    .timeline-form-shell {
      grid-template-columns: minmax(0, 1fr);
    }
  }

  @media (max-width: 767px) {
    .timeline-form-grid {
      grid-template-columns: minmax(0, 1fr);
    }

    .timeline-form-field-half {
      grid-column: 1 / -1;
    }
  }
</style>
