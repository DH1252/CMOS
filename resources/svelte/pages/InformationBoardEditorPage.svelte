<script>
  import * as Card from '$lib/components/ui/card/index.js';
  import { Input } from '$lib/components/ui/input/index.js';
  import { Label } from '$lib/components/ui/label/index.js';
  import { Textarea } from '$lib/components/ui/textarea/index.js';
  import FormActions from '../components/FormActions.svelte';
  import PageHeader from '../components/PageHeader.svelte';

  let {
    title = 'Tulis Artikel',
    description = '',
    icon = 'fas fa-pen',
    form = {
      action: '#',
      method: 'POST',
      csrfToken: '',
      enctype: 'multipart/form-data',
      submitLabel: 'Simpan',
    },
    article = {
      title: '',
      excerpt: '',
      content: '',
      status: 'draft',
      publishedAt: '',
      metaTitle: '',
      metaDescription: '',
      categoryIds: [],
      coverImage: null,
    },
    categories = [],
    errors = {},
    cancelAction = null,
    dangerAction = null,
    editorId = 'information-board-content',
  } = $props();

  const isSelected = (value) => article.categoryIds?.map(String).includes(String(value));
  const fallbackImage = '/images/logokabinet.png';

  const handleImageError = (event) => {
    if (event.currentTarget.src.endsWith(fallbackImage)) {
      return;
    }

    event.currentTarget.src = fallbackImage;
  };
</script>

<div class="mx-auto max-w-6xl">
    <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border/70 pb-4">
        <PageHeader {title} {description} {icon} />
      </Card.Header>

      <Card.Content class="pt-5">
        <form id={`${editorId}-form`} method="POST" action={form.action} enctype={form.enctype || 'multipart/form-data'}>
          <input type="hidden" name="_token" value={form.csrfToken} />
          {#if form.method && form.method !== 'POST'}
            <input type="hidden" name="_method" value={form.method} />
          {/if}

          <div class="editor-grid">
            <div class="editor-main">
              <div class="editor-field">
                <Label for="article-title">
                  Judul
                  <span class="editor-required">*</span>
                </Label>
                <Input id="article-title" name="title" type="text" class="editor-input" aria-invalid={Boolean(errors.title)} value={article.title || ''} required />
                {#if errors.title}
                  <div class="editor-error" role="alert">{errors.title}</div>
                {/if}
              </div>

              <div class="editor-field">
                <Label for="article-excerpt">Ringkasan</Label>
                <Textarea id="article-excerpt" name="excerpt" rows="3" class="editor-textarea" aria-invalid={Boolean(errors.excerpt)} value={article.excerpt || ''} />
                {#if errors.excerpt}
                  <div class="editor-error" role="alert">{errors.excerpt}</div>
                {/if}
              </div>

              <div class="editor-field">
                <Label for="article-categories">Kategori</Label>
                <select id="article-categories" name="category_ids[]" class="editor-select editor-select-multiple" aria-invalid={Boolean(errors.category_ids || errors.category_ids_items)} multiple>
                  {#each categories as category, index (category.value || index)}
                    <option value={category.value} selected={isSelected(category.value)}>{category.label}</option>
                  {/each}
                </select>
                <small class="editor-help">Pilih satu atau beberapa kategori dengan Ctrl/Cmd + klik.</small>
                {#if errors.category_ids}
                  <div class="editor-error" role="alert">{errors.category_ids}</div>
                {/if}
                {#if errors.category_ids_items}
                  <div class="editor-error" role="alert">{errors.category_ids_items}</div>
                {/if}
              </div>

              <div class="editor-field">
                <Label for={editorId}>
                  Konten Artikel
                  <span class="editor-required">*</span>
                </Label>
                <input id={editorId} type="hidden" name="content" value={article.content || ''} />
                <trix-editor input={editorId} class={errors.content ? 'is-invalid' : ''}></trix-editor>
                {#if errors.content}
                  <div class="editor-error" role="alert">{errors.content}</div>
                {/if}
              </div>
            </div>

            <aside class="editor-side">
              <div class="editor-side-card">
                <PageHeader title="Publikasi" icon="fas fa-bullhorn" compact={true} headingTag="h4" />
                <div class="editor-field mt-4">
                  <Label for="article-status">
                    Status
                    <span class="editor-required">*</span>
                  </Label>
                  <select id="article-status" name="status" class="editor-select" aria-invalid={Boolean(errors.status)} required>
                    <option value="draft" selected={article.status === 'draft'}>Draft</option>
                    <option value="published" selected={article.status === 'published'}>Published</option>
                  </select>
                  {#if errors.status}
                    <div class="editor-error" role="alert">{errors.status}</div>
                  {/if}
                </div>

                <div class="editor-field">
                  <Label for="article-published-at">Tanggal Publish</Label>
                  <Input id="article-published-at" type="datetime-local" name="published_at" class="editor-input" aria-invalid={Boolean(errors.published_at)} value={article.publishedAt || ''} />
                  {#if errors.published_at}
                    <div class="editor-error" role="alert">{errors.published_at}</div>
                  {/if}
                </div>
              </div>

              <div class="editor-side-card">
                <PageHeader title="SEO" icon="fas fa-magnifying-glass-chart" compact={true} headingTag="h4" />
                <div class="editor-field mt-4">
                  <Label for="article-meta-title">Meta Title</Label>
                  <Input id="article-meta-title" type="text" name="meta_title" class="editor-input" aria-invalid={Boolean(errors.meta_title)} value={article.metaTitle || ''} />
                  {#if errors.meta_title}
                    <div class="editor-error" role="alert">{errors.meta_title}</div>
                  {/if}
                </div>

                <div class="editor-field">
                  <Label for="article-meta-description">Meta Description</Label>
                  <Textarea id="article-meta-description" name="meta_description" rows="4" class="editor-textarea" aria-invalid={Boolean(errors.meta_description)} value={article.metaDescription || ''} />
                  {#if errors.meta_description}
                    <div class="editor-error" role="alert">{errors.meta_description}</div>
                  {/if}
                </div>
              </div>

              <div class="editor-side-card">
                <PageHeader title="Cover" icon="fas fa-image" compact={true} headingTag="h4" />
                {#if article.coverImage}
                  <img src={article.coverImage} alt="Cover artikel" class="editor-cover-preview" onerror={handleImageError} />
                {/if}
                <div class="editor-field">
                  <Label for="article-cover-image">Gambar Cover</Label>
                  <Input id="article-cover-image" type="file" name="cover_image" accept="image/*" class="editor-input" aria-invalid={Boolean(errors.cover_image)} />
                  {#if errors.cover_image}
                    <div class="editor-error" role="alert">{errors.cover_image}</div>
                  {/if}
                </div>
              </div>
            </aside>
          </div>
        </form>

        <FormActions formId={`${editorId}-form`} submitLabel={form.submitLabel || 'Simpan'} submitIcon="fas fa-save" {cancelAction} {dangerAction} csrfToken={form.csrfToken} />
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

  .editor-side-card {
    padding: 1rem;
    border: 1px solid var(--line-soft);
    border-radius: 0.625rem;
    background: var(--background);
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
    transition: border-color 160ms ease, box-shadow 160ms ease;
  }

  .editor-select:focus {
    border-color: color-mix(in srgb, var(--brand-primary) 34%, var(--line-soft));
    box-shadow: 0 0 0 4px color-mix(in srgb, var(--brand-light) 70%, transparent);
  }

  .editor-select-multiple {
    min-height: 10rem;
    height: auto;
    padding-block: 0.75rem;
  }

  .editor-help {
    color: var(--text-muted);
    font-size: 0.82rem;
  }

  .editor-cover-preview {
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

  :global(trix-toolbar [data-trix-button-group='file-tools']) {
    display: none;
  }

  :global(trix-editor) {
    min-height: 18rem;
    border: 1px solid var(--line-soft);
    border-radius: 0.625rem;
    background: var(--background);
    color: var(--text-strong);
  }

  :global(trix-editor:focus) {
    outline: none;
    border-color: color-mix(in srgb, var(--brand-primary) 34%, var(--line-soft));
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-primary) 14%, transparent);
  }

  @media (max-width: 1023px) {
    .editor-grid {
      grid-template-columns: minmax(0, 1fr);
    }
  }
</style>
