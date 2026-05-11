<script>
  import * as Card from "$lib/components/ui/card/index.js";
  import fallbackImageAsset from "../../images/logokabinet.png?enhanced&w=320;640";
  import { Input } from "$lib/components/ui/input/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import { Textarea } from "$lib/components/ui/textarea/index.js";
  import FormActions from "../components/FormActions.svelte";
  import OptimizedImage from "../components/OptimizedImage.svelte";
  import PageHeader from "../components/PageHeader.svelte";
  import TinymceEditor from "../components/TinymceEditor.svelte";

  let {
    title = "Tulis Artikel",
    description = "",
    icon = "fas fa-pen",
    form = {
      action: "#",
      method: "POST",
      csrfToken: "",
      enctype: "multipart/form-data",
      submitLabel: "Simpan",
    },
    article = {
      title: "",
      excerpt: "",
      content: "",
      status: "draft",
      publishMode: "immediately",
      publishedAt: "",
      metaTitle: "",
      metaDescription: "",
      categoryIds: [],
      coverImage: null,
    },
    categories = [],
    errors = {},
    cancelAction = null,
    dangerAction = null,
    editorId = "information-board-content",
    previewTheme = {},
  } = $props();

  const isSelected = (value) =>
    article.categoryIds?.map(String).includes(String(value));
  const fallbackImage = fallbackImageAsset.original ?? fallbackImageAsset;
  let formStateInitialized = $state(false);
  let statusValue = $state("draft");
  let publishModeValue = $state("immediately");
  const isPublished = $derived(statusValue === "published");
  const isScheduled = $derived(isPublished && publishModeValue === "scheduled");

  $effect(() => {
    if (formStateInitialized) {
      return;
    }

    statusValue = article.status || "draft";
    publishModeValue = article.publishMode || "immediately";
    formStateInitialized = true;
  });

  const handleImageError = (event) => {
    if (event.currentTarget.src.endsWith(fallbackImage)) {
      return;
    }

    event.currentTarget.src = fallbackImage;
  };
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
              <Label for="article-title">
                Judul
                <span class="editor-required">*</span>
              </Label>
              <Input
                id="article-title"
                name="title"
                type="text"
                class="editor-input"
                aria-invalid={Boolean(errors.title)}
                value={article.title || ""}
                required
              />
              {#if errors.title}
                <div class="editor-error" role="alert">{errors.title}</div>
              {/if}
            </div>

            <div class="editor-field">
              <Label for="article-excerpt">Ringkasan</Label>
              <Textarea
                id="article-excerpt"
                name="excerpt"
                rows="3"
                class="editor-textarea"
                aria-invalid={Boolean(errors.excerpt)}
                value={article.excerpt || ""}
              />
              {#if errors.excerpt}
                <div class="editor-error" role="alert">{errors.excerpt}</div>
              {/if}
            </div>

            <div class="editor-field">
              <Label for="article-categories">Kategori</Label>
              <select
                id="article-categories"
                name="category_ids[]"
                class="editor-select editor-select-multiple"
                aria-invalid={Boolean(
                  errors.category_ids || errors.category_ids_items,
                )}
                multiple
              >
                {#each categories as category, index (category.value || index)}
                  <option
                    value={category.value}
                    selected={isSelected(category.value)}
                    >{category.label}</option
                  >
                {/each}
              </select>
              {#if errors.category_ids}
                <div class="editor-error" role="alert">
                  {errors.category_ids}
                </div>
              {/if}
              {#if errors.category_ids_items}
                <div class="editor-error" role="alert">
                  {errors.category_ids_items}
                </div>
              {/if}
            </div>

            <div class="editor-field">
              <Label for={editorId}>
                Konten Artikel
                <span class="editor-required">*</span>
              </Label>
              <div class="editor-rich-frame">
                <TinymceEditor
                  id={editorId}
                  name="content"
                  content={article.content || ""}
                  tools={[
                    "undo",
                    "redo",
                    "fontFamily",
                    "heading",
                    "bold",
                    "italic",
                    "underline",
                    "strike",
                    "textColor",
                    "highlight",
                    "subscript",
                    "superscript",
                    "clearFormat",
                    "alignLeft",
                    "alignCenter",
                    "alignRight",
                    "alignJustify",
                    "bulletList",
                    "orderedList",
                    "blockquote",
                    "codeBlock",
                    "link",
                    "image",
                    "horizontalRule",
                    "table",
                  ]}
                  uploadUrl={form.attachmentUploadUrl}
                  csrfToken={form.csrfToken}
                  error={Boolean(errors.content)}
                  placeholder="Tulis konten artikel..."
                  {previewTheme}
                />
              </div>

              {#if errors.content}
                <div class="editor-error" role="alert">{errors.content}</div>
              {/if}
            </div>
          </div>

          <aside class="editor-side">
            <div class="editor-side-card">
              <PageHeader
                title="Publikasi"
                icon="fas fa-bullhorn"
                compact={true}
                headingTag="h4"
              />
              <div class="editor-field mt-4">
                <Label for="article-status">
                  Status
                  <span class="editor-required">*</span>
                </Label>
                <select
                  id="article-status"
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
                        >Tayang saat artikel disimpan.</span
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
                <Label for="article-published-at">Tanggal Publish</Label>
                <Input
                  id="article-published-at"
                  type="datetime-local"
                  name="published_at"
                  class="editor-input"
                  aria-invalid={Boolean(errors.published_at)}
                  value={article.publishedAt || ""}
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
                <Label for="article-meta-title">Meta Title</Label>
                <Input
                  id="article-meta-title"
                  type="text"
                  name="meta_title"
                  class="editor-input"
                  aria-invalid={Boolean(errors.meta_title)}
                  value={article.metaTitle || ""}
                />
                {#if errors.meta_title}
                  <div class="editor-error" role="alert">
                    {errors.meta_title}
                  </div>
                {/if}
              </div>

              <div class="editor-field">
                <Label for="article-meta-description">Meta Description</Label>
                <Textarea
                  id="article-meta-description"
                  name="meta_description"
                  rows="4"
                  class="editor-textarea"
                  aria-invalid={Boolean(errors.meta_description)}
                  value={article.metaDescription || ""}
                />
                {#if errors.meta_description}
                  <div class="editor-error" role="alert">
                    {errors.meta_description}
                  </div>
                {/if}
              </div>
            </div>

            <div class="editor-side-card">
              <PageHeader
                title="Cover"
                icon="fas fa-image"
                compact={true}
                headingTag="h4"
              />
              {#if article.coverImage}
                <OptimizedImage
                  src={article.coverImage}
                  alt="Cover artikel"
                  class="editor-cover-preview"
                  loading="lazy"
                  decoding="async"
                  sizes="(min-width: 992px) 22rem, 100vw"
                  onerror={handleImageError}
                />
              {/if}
              <div class="editor-field">
                <Label for="article-cover-image">Gambar Cover</Label>
                <Input
                  id="article-cover-image"
                  type="file"
                  name="cover_image"
                  accept="image/*"
                  class="editor-input"
                  aria-invalid={Boolean(errors.cover_image)}
                />
                {#if errors.cover_image}
                  <div class="editor-error" role="alert">
                    {errors.cover_image}
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

  :global(.editor-input),
  :global(.editor-textarea) {
    background: var(--background);
  }

  .editor-select {
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

  .editor-select:focus {
    border-color: color-mix(
      in srgb,
      var(--brand-primary) 34%,
      var(--line-soft)
    );
    box-shadow: 0 0 0 4px
      color-mix(in srgb, var(--brand-light) 70%, transparent);
  }

  .editor-select-multiple {
    min-height: 10rem;
    height: auto;
    padding-block: 0.75rem;
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
