<script>
  import * as Card from "$lib/components/ui/card/index.js";
  import { Input } from "$lib/components/ui/input/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import { Textarea } from "$lib/components/ui/textarea/index.js";
  import FormActions from "../components/FormActions.svelte";
  import PageHeader from "../components/PageHeader.svelte";

  let {
    title = "Form",
    description = "",
    icon = "fas fa-pen",
    form = {
      action: "#",
      method: "POST",
      csrfToken: "",
      submitLabel: "Simpan",
      submitIcon: "fas fa-save",
    },
    fields = [],
    cancelAction = null,
    dangerAction = null,
  } = $props();
  const formId = "entity-form";

  const isChecked = (field) => {
    if (Array.isArray(field.value)) {
      return field.value.length > 0;
    }

    if (typeof field.value === "string") {
      return !["0", "false", "off", ""].includes(field.value.toLowerCase());
    }

    return Boolean(field.value);
  };

  const isSelected = (field, value) => {
    if (Array.isArray(field.value)) {
      return field.value.map(String).includes(String(value));
    }

    return String(field.value ?? "") === String(value);
  };

  const nativeSelectClass = (field) =>
    `entity-select ${field.multiple ? "entity-select-multiple" : ""}`;
</script>

<div class="mx-auto max-w-4xl">
  <Card.Root
    class="entity-form-card animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader {title} {description} {icon} compact={true} />
    </Card.Header>

    <Card.Content class="pt-5">
      <form
        id={formId}
        method="POST"
        action={form.action}
        enctype={form.enctype || undefined}
      >
        <input type="hidden" name="_token" value={form.csrfToken} />
        {#if form.method && form.method !== "POST"}
          <input type="hidden" name="_method" value={form.method} />
        {/if}

        <div class="entity-form-grid">
          {#each fields as field, index (field.name || index)}
            <div
              class={`entity-form-field ${field.span === "half" ? "entity-form-field-half" : ""} ${field.type === "checkbox" ? "entity-form-field-compact" : ""}`}
            >
              {#if field.type === "checkbox"}
                <label
                  class={`entity-checkbox ${field.error ? "is-invalid" : ""}`}
                >
                  <input
                    id={field.name}
                    name={field.name}
                    type="checkbox"
                    value={field.checkboxValue || "1"}
                    class="entity-checkbox-input"
                    checked={isChecked(field)}
                  />
                  <span class="entity-checkbox-copy">
                    <span class="entity-checkbox-label">{field.label}</span>
                    {#if field.note}
                      <span class="entity-checkbox-note">{field.note}</span>
                    {/if}
                  </span>
                </label>
              {:else}
                <Label for={field.name} class="entity-label">
                  {field.label}
                  {#if field.required}
                    <span class="entity-required">*</span>
                  {/if}
                </Label>

                {#if field.type === "textarea"}
                  <Textarea
                    id={field.name}
                    name={field.name}
                    rows={field.rows || 4}
                    class="entity-control entity-textarea"
                    aria-invalid={Boolean(field.error)}
                    placeholder={field.placeholder || ""}
                    value={field.value || ""}
                  />
                {:else if field.type === "select"}
                  <select
                    id={field.name}
                    name={field.name}
                    class={nativeSelectClass(field)}
                    aria-invalid={Boolean(field.error)}
                    required={field.required}
                    multiple={field.multiple}
                  >
                    {#if field.placeholder && !field.multiple}
                      <option value="">{field.placeholder}</option>
                    {/if}
                    {#each field.options || [] as option, optionIndex (option.value || optionIndex)}
                      <option
                        value={option.value}
                        selected={isSelected(field, option.value)}
                        >{option.label}</option
                      >
                    {/each}
                  </select>
                {:else}
                  <Input
                    id={field.name}
                    name={field.name}
                    type={field.type || "text"}
                    class="entity-control"
                    aria-invalid={Boolean(field.error)}
                    value={field.value || ""}
                    placeholder={field.placeholder || ""}
                    required={field.required}
                    min={field.min}
                    max={field.max}
                  />
                {/if}

                {#if field.note}
                  <small class="entity-field-note">{field.note}</small>
                {/if}
              {/if}

              {#if field.error}
                <div class="entity-field-error" role="alert">{field.error}</div>
              {/if}
            </div>
          {/each}
        </div>
      </form>

      <FormActions
        {formId}
        submitLabel={form.submitLabel || "Simpan"}
        submitIcon={form.submitIcon || "fas fa-save"}
        {cancelAction}
        {dangerAction}
        csrfToken={form.csrfToken}
      />
    </Card.Content>
  </Card.Root>
</div>

<style>
  .entity-form-card {
    overflow: visible;
  }

  .entity-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  .entity-form-field {
    grid-column: 1 / -1;
  }

  .entity-form-field-half {
    grid-column: span 1;
  }

  .entity-form-field-compact {
    align-self: end;
  }

  .entity-label {
    margin-bottom: 0.55rem;
  }

  .entity-required {
    color: var(--signal-danger);
  }

  .entity-control {
    background: color-mix(in srgb, var(--panel-bg) 92%, white);
  }

  .entity-textarea {
    min-height: 9rem;
  }

  .entity-select {
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

  .entity-select:focus {
    border-color: color-mix(in srgb, var(--brand-primary) 32%, white);
    box-shadow: 0 0 0 3px
      color-mix(in srgb, var(--brand-primary) 14%, transparent);
  }

  .entity-select[aria-invalid="true"] {
    border-color: color-mix(in srgb, var(--signal-danger) 45%, white);
    box-shadow: 0 0 0 3px
      color-mix(in srgb, var(--signal-danger) 12%, transparent);
  }

  .entity-select-multiple {
    min-height: 9rem;
    height: auto;
    padding-block: 0.75rem;
  }

  .entity-checkbox {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    min-height: 100%;
    padding: 1rem 1rem 1.05rem;
    border: 1px solid var(--line-soft);
    border-radius: 0.625rem;
    background: var(--background);
  }

  .entity-checkbox.is-invalid {
    border-color: color-mix(in srgb, var(--signal-danger) 35%, white);
  }

  .entity-checkbox-input {
    margin-top: 0.2rem;
    width: 1.1rem;
    height: 1.1rem;
    accent-color: var(--brand-primary);
  }

  .entity-checkbox-copy {
    display: grid;
    gap: 0.15rem;
  }

  .entity-checkbox-label {
    font-weight: 700;
    color: var(--text-soft);
  }

  .entity-checkbox-note {
    font-size: 0.86rem;
    color: var(--text-muted);
  }

  .entity-field-note {
    display: block;
    margin-top: 0.45rem;
    color: var(--text-muted);
  }

  .entity-field-error {
    margin-top: 0.45rem;
    font-size: 0.86rem;
    color: var(--signal-danger);
  }

  @media (max-width: 767px) {
    .entity-form-grid {
      grid-template-columns: minmax(0, 1fr);
    }

    .entity-form-field-half {
      grid-column: 1 / -1;
    }
  }
</style>
