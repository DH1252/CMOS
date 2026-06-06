<script>
  import * as Card from "$lib/components/ui/card/index.js";
  import { Input } from "$lib/components/ui/input/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import FormActions from "../components/FormActions.svelte";
  import OptimizedImage from "../components/OptimizedImage.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import TinymceEditor from "../components/TinymceEditor.svelte";

  let {
    title = "Form Acara",
    description = "",
    icon = "fas fa-calendar-plus",
    form = {
      action: "#",
      method: "POST",
      csrfToken: "",
      enctype: "multipart/form-data",
      submitLabel: "Simpan",
    },
    event = {
      title: "",
      description: "",
      location: "",
      startsAt: "",
      endsAt: "",
      status: "draft",
      publishMode: "immediately",
      publishedAt: "",
      metaTitle: "",
      metaDescription: "",
      poster: null,
    },
    errors = {},
    cancelAction = null,
    dangerAction = null,
    editorId = "event-content",
  } = $props();

  let formStateInitialized = $state(false);
  let statusValue = $state("draft");
  let publishModeValue = $state("immediately");
  const isPublished = $derived(statusValue === "published");
  const isScheduled = $derived(isPublished && publishModeValue === "scheduled");

  $effect(() => {
    if (formStateInitialized) {
      return;
    }

    statusValue = event.status || "draft";
    publishModeValue = event.publishMode || "immediately";
    formStateInitialized = true;
  });
</script>

<div class="w-full">
  <Card.Root
    class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader {title} {description} {icon} />
    </Card.Header>

    <Card.Content class="pt-5">
      <form
        id={`${editorId}-form`}
        method="POST"
        action={form.action}
        enctype={form.enctype || "multipart/form-data"}
      >
        <input type="hidden" name="_token" value={form.csrfToken} />
        {#if form.method && form.method !== "POST"}
          <input type="hidden" name="_method" value={form.method} />
        {/if}

        <div class="editor-grid">
          <div class="editor-main">
            <div class="editor-field">
              <Label for="event-title">
                Judul
                <span class="editor-required">*</span>
              </Label>
              <Input
                id="event-title"
                name="title"
                type="text"
                class="editor-input"
                aria-invalid={Boolean(errors.title)}
                value={event.title || ""}
                required
              />
              {#if errors.title}
                <div class="editor-error" role="alert">{errors.title}</div>
              {/if}
            </div>

            <div class="editor-field">
              <Label for="event-location">Lokasi</Label>
              <Input
                id="event-location"
                name="location"
                type="text"
                class="editor-input"
                placeholder="Contoh: Gedung Teknik Komputer ITS atau Daring (Zoom)"
                aria-invalid={Boolean(errors.location)}
                value={event.location || ""}
              />
              {#if errors.location}
                <div class="editor-error" role="alert">{errors.location}</div>
              {/if}
            </div>

            <div class="editor-field">
              <Label for={editorId}>
                Deskripsi Acara
                <span class="editor-required">*</span>
              </Label>
              <div class="editor-rich-frame">
                <TinymceEditor
                  id={editorId}
                  name="description"
                  content={event.description || ""}
                  tools={[
                    "undo",
                    "redo",
                    "heading",
                    "bold",
                    "italic",
                    "underline",
                    "strike",
                    "textColor",
                    "highlight",
                    "alignLeft",
                    "alignCenter",
                    "alignRight",
                    "alignJustify",
                    "bulletList",
                    "orderedList",
                    "blockquote",
                    "link",
                    "horizontalRule",
                    "table",
                  ]}
                  csrfToken={form.csrfToken}
                  error={Boolean(errors.description)}
                  placeholder="Tulis deskripsi acara..."
                />
              </div>

              {#if errors.description}
                <div class="editor-error" role="alert">
                  {errors.description}
                </div>
              {/if}
            </div>
          </div>

          <aside class="editor-side">
            <div class="editor-side-card">
              <PageHeader
                title="Waktu Acara"
                icon="fas fa-clock"
                compact={true}
                headingTag="h4"
              />
              <div class="editor-field mt-4">
                <Label for="event-starts-at">
                  Mulai
                  <span class="editor-required">*</span>
                </Label>
                <Input
                  id="event-starts-at"
                  type="datetime-local"
                  name="starts_at"
                  class="editor-input"
                  aria-invalid={Boolean(errors.starts_at)}
                  value={event.startsAt || ""}
                  required
                />
                {#if errors.starts_at}
                  <div class="editor-error" role="alert">
                    {errors.starts_at}
                  </div>
                {/if}
              </div>

              <div class="editor-field">
                <Label for="event-ends-at">Selesai</Label>
                <Input
                  id="event-ends-at"
                  type="datetime-local"
                  name="ends_at"
                  class="editor-input"
                  aria-invalid={Boolean(errors.ends_at)}
                  value={event.endsAt || ""}
                />
                {#if errors.ends_at}
                  <div class="editor-error" role="alert">{errors.ends_at}</div>
                {/if}
              </div>
            </div>

            <div class="editor-side-card">
              <PageHeader
                title="Publikasi"
                icon="fas fa-bullhorn"
                compact={true}
                headingTag="h4"
              />
              <div class="editor-field mt-4">
                <Label for="event-status">
                  Status
                  <span class="editor-required">*</span>
                </Label>
                <select
                  id="event-status"
                  name="status"
                  bind:value={statusValue}
                  class="editor-select"
                  aria-invalid={Boolean(errors.status)}
                  required
                >
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                </select>
                {#if errors.status}
                  <div class="editor-error" role="alert">{errors.status}</div>
                {/if}
              </div>

              {#if isPublished}
                <div class="editor-field">
                  <span class="editor-label">Waktu Publikasi</span>
                  <div
                    class="editor-choice-grid"
                    role="radiogroup"
                    aria-label="Waktu publikasi"
                  >
                    <label
                      class={`editor-choice ${publishModeValue === "immediately" ? "editor-choice-active" : ""}`}
                    >
                      <input
                        class="sr-only"
                        type="radio"
                        name="publish_mode"
                        value="immediately"
                        checked={publishModeValue === "immediately"}
                        onchange={() => (publishModeValue = "immediately")}
                      />
                      <span class="editor-choice-title">Posting sekarang</span>
                      <span class="editor-choice-copy"
                        >Tayang saat acara disimpan.</span
                      >
                    </label>

                    <label
                      class={`editor-choice ${publishModeValue === "scheduled" ? "editor-choice-active" : ""}`}
                    >
                      <input
                        class="sr-only"
                        type="radio"
                        name="publish_mode"
                        value="scheduled"
                        checked={publishModeValue === "scheduled"}
                        onchange={() => (publishModeValue = "scheduled")}
                      />
                      <span class="editor-choice-title">Jadwalkan</span>
                      <span class="editor-choice-copy"
                        >Tayang pada waktu yang ditentukan.</span
                      >
                    </label>
                  </div>
                  {#if errors.publish_mode}
                    <div class="editor-error" role="alert">
                      {errors.publish_mode}
                    </div>
                  {/if}
                </div>
              {/if}

              <div
                class={`editor-field ${isScheduled ? "" : "editor-field-hidden"}`}
              >
                <Label for="event-published-at">Tanggal Publish</Label>
                <Input
                  id="event-published-at"
                  type="datetime-local"
                  name="published_at"
                  class="editor-input"
                  aria-invalid={Boolean(errors.published_at)}
                  value={event.publishedAt || ""}
                  disabled={!isScheduled}
                />
                {#if errors.published_at}
                  <div class="editor-error" role="alert">
                    {errors.published_at}
                  </div>
                {/if}
              </div>
            </div>

            <div class="editor-side-card">
              <PageHeader
                title="SEO"
                icon="fas fa-magnifying-glass-chart"
                compact={true}
                headingTag="h4"
              />
              <div class="editor-field mt-4">
                <Label for="event-meta-title">Meta Title</Label>
                <Input
                  id="event-meta-title"
                  type="text"
                  name="meta_title"
                  class="editor-input"
                  aria-invalid={Boolean(errors.meta_title)}
                  value={event.metaTitle || ""}
                />
                {#if errors.meta_title}
                  <div class="editor-error" role="alert">
                    {errors.meta_title}
                  </div>
                {/if}
              </div>

              <div class="editor-field">
                <Label for="event-meta-description">Meta Description</Label>
                <textarea
                  id="event-meta-description"
                  name="meta_description"
                  rows="4"
                  class="editor-select"
                  aria-invalid={Boolean(errors.meta_description)}
                  >{event.metaDescription || ""}</textarea
                >
                {#if errors.meta_description}
                  <div class="editor-error" role="alert">
                    {errors.meta_description}
                  </div>
                {/if}
              </div>
            </div>

            <div class="editor-side-card">
              <PageHeader
                title="Poster"
                icon="fas fa-image"
                compact={true}
                headingTag="h4"
              />
              {#if event.poster}
                <OptimizedImage
                  src={event.poster}
                  alt="Poster acara"
                  class="editor-cover-preview"
                  loading="lazy"
                  decoding="async"
                  sizes="(min-width: 992px) 22rem, 100vw"
                />
              {/if}
              <div class="editor-field">
                <Label for="event-poster-image">Gambar Poster</Label>
                <Input
                  id="event-poster-image"
                  type="file"
                  name="poster_image"
                  accept="image/*"
                  class="editor-input"
                  aria-invalid={Boolean(errors.poster_image)}
                />
                {#if errors.poster_image}
                  <div class="editor-error" role="alert">
                    {errors.poster_image}
                  </div>
                {/if}
              </div>
            </div>
          </aside>
        </div>
      </form>

      <FormActions
        formId={`${editorId}-form`}
        submitLabel={form.submitLabel || "Simpan"}
        submitIcon="fas fa-save"
        {cancelAction}
        {dangerAction}
        csrfToken={form.csrfToken}
      />
    </Card.Content>
  </Card.Root>
</div>

<style>
  .editor-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.9fr);
    gap: 1rem;
  }

  .editor-main,
  .editor-side {
    display: grid;
    gap: 1rem;
  }

  .editor-field {
    display: grid;
    gap: 0.45rem;
  }

  .editor-field-hidden {
    display: none;
  }

  .editor-label {
    font-size: 0.95rem;
    font-weight: 500;
    color: var(--text-strong);
  }

  .editor-choice-grid {
    display: grid;
    gap: 0.6rem;
  }

  .editor-choice {
    display: grid;
    gap: 0.15rem;
    padding: 0.8rem 0.9rem;
    border: 1px solid var(--line-soft);
    border-radius: 0.5rem;
    background: var(--background);
    cursor: pointer;
    transition:
      border-color 160ms ease,
      background-color 160ms ease;
  }

  .editor-choice:hover {
    background: color-mix(in srgb, var(--background) 90%, white);
  }

  .editor-choice-active {
    border-color: color-mix(
      in srgb,
      var(--brand-primary) 34%,
      var(--line-soft)
    );
    background: color-mix(in srgb, var(--brand-light) 35%, var(--background));
  }

  .editor-choice-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-strong);
  }

  .editor-choice-copy {
    font-size: 0.82rem;
    color: var(--text-muted);
    line-height: 1.5;
  }

  .editor-side-card {
    padding: 1rem;
    border: 1px solid var(--line-soft);
    border-radius: 0.625rem;
    background: var(--background);
  }

  .editor-side-card :global([data-slot="button"]) {
    width: 100%;
  }

  .editor-required {
    color: var(--signal-danger);
  }

  :global(.editor-input) {
    background: var(--background);
  }

  .editor-select {
    width: 100%;
    min-width: 0;
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

  select.editor-select {
    height: 2.5rem;
  }

  .editor-select:focus {
    border-color: color-mix(
      in srgb,
      var(--brand-primary) 34%,
      var(--line-soft)
    );
    box-shadow: 0 0 0 4px
      color-mix(in srgb, var(--brand-light) 70%, transparent);
  }

  :global(.editor-cover-preview) {
    display: block;
    width: 100%;
    margin-bottom: 1rem;
    border-radius: 0.625rem;
    border: 1px solid var(--line-soft);
    object-fit: cover;
  }

  .editor-error {
    color: var(--signal-danger);
    font-size: 0.85rem;
  }

  .editor-side-card :global([data-slot="button"].button-variant-default) {
    background: var(--brand-primary);
    color: #241a0f;
    border-color: color-mix(in srgb, var(--brand-primary) 60%, black);
  }

  .editor-side-card :global([data-slot="button"].button-variant-secondary) {
    background: var(--background);
    color: var(--foreground);
    border-color: var(--line-soft);
  }

  .editor-side-card :global([data-slot="button"].button-variant-outline) {
    background: var(--background);
    color: var(--foreground);
    border-color: var(--line-soft);
  }

  .editor-side-card :global([data-slot="button"].button-variant-destructive) {
    background: color-mix(in srgb, var(--signal-danger) 12%, white);
    color: color-mix(in srgb, var(--signal-danger) 80%, black);
    border-color: color-mix(
      in srgb,
      var(--signal-danger) 24%,
      var(--line-soft)
    );
  }

  @media (max-width: 1023px) {
    .editor-grid {
      grid-template-columns: minmax(0, 1fr);
    }
  }
</style>
