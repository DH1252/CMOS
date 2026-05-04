<script>
  import { onMount } from 'svelte';
  import Editor from '@tinymce/tinymce-svelte';
  import { tinymceBaseStyle } from '../lib/tinymceContentStyle.js';

  let {
    id = 'tinymce-editor',
    name = 'content',
    content = '',
    tools = [
      'undo', 'redo',
      'fontFamily',
      'heading',
      'bold', 'italic', 'underline', 'strike',
      'textColor', 'highlight',
      'subscript', 'superscript',
      'clearFormat',
      'alignLeft', 'alignCenter', 'alignRight', 'alignJustify',
      'bulletList', 'orderedList',
      'blockquote', 'codeBlock',
      'link', 'image', 'horizontalRule',
      'table',
    ],
    uploadUrl = '',
    csrfToken = '',
    placeholder = '',
    error = false,
    disabled = false,
    backgroundColor = '',
  } = $props();

  // svelte-ignore state_referenced_locally
  let value = $state(content);
  let isVisible = $state(false);
  let containerRef = $state(null);

  const toolMap = {
    undo: 'undo',
    redo: 'redo',
    fontFamily: 'fontfamily',
    heading: 'blocks',
    bold: 'bold',
    italic: 'italic',
    underline: 'underline',
    strike: 'strikethrough',
    textColor: 'forecolor',
    highlight: 'backcolor',
    subscript: 'subscript',
    superscript: 'superscript',
    clearFormat: 'removeformat',
    alignLeft: 'alignleft',
    alignCenter: 'aligncenter',
    alignRight: 'alignright',
    alignJustify: 'alignjustify',
    bulletList: 'bullist',
    orderedList: 'numlist',
    blockquote: 'blockquote',
    codeBlock: 'codesample',
    link: 'link',
    image: 'image',
    horizontalRule: 'hr',
    table: 'table',
  };

  const buildToolbar = (toolList) => {
    const groups = [
      ['undo', 'redo'],
      ['fontFamily', 'heading'],
      ['bold', 'italic', 'underline', 'strike'],
      ['textColor', 'highlight'],
      ['subscript', 'superscript', 'clearFormat'],
      ['alignLeft', 'alignCenter', 'alignRight', 'alignJustify'],
      ['bulletList', 'orderedList'],
      ['blockquote', 'codeBlock'],
      ['link', 'image', 'horizontalRule'],
      ['table'],
    ];

    return groups
      .map((group) => group.filter((t) => toolList.includes(t)).map((t) => toolMap[t]).join(' '))
      .filter((g) => g !== '')
      .join(' | ');
  };

  const buildPlugins = (toolList) => {
    const base = ['lists', 'link', 'image', 'table', 'codesample', 'autolink', 'charmap', 'code', 'help', 'wordcount'];

    if (toolList.includes('bulletList') || toolList.includes('orderedList')) {
      base.push('advlist');
    }

    return [...new Set(base)].join(' ');
  };

  const imagesUploadHandler = (blobInfo, progress) => {
    return new Promise((resolve, reject) => {
      if (!uploadUrl || !csrfToken) {
        reject('Upload not configured');
        return;
      }

      const xhr = new XMLHttpRequest();
      xhr.withCredentials = false;
      xhr.open('POST', uploadUrl);
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

      xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
          progress(e.loaded / e.total * 100);
        }
      };

      xhr.onload = () => {
        if (xhr.status < 200 || xhr.status >= 300) {
          reject(`HTTP Error: ${xhr.status}`);
          return;
        }

        let json;
        try {
          json = JSON.parse(xhr.responseText);
        } catch {
          reject(`Invalid JSON: ${xhr.responseText}`);
          return;
        }

        if (!json?.url) {
          reject(json?.message || 'Upload failed: No URL returned');
          return;
        }

        resolve(json.url);
      };

      xhr.onerror = () => reject('Upload failed (network error)');
      xhr.onabort = () => reject('Upload aborted');

      const formData = new FormData();
      formData.append('attachment', blobInfo.blob(), blobInfo.filename());
      xhr.send(formData);
    });
  };

  const isDarkMode = () => {
    if (typeof document === 'undefined') {
      return false;
    }
    return document.documentElement.getAttribute('data-theme') === 'dark';
  };

  let skin = $state(isDarkMode() ? 'oxide-dark' : 'oxide');

  const fontFamilyFormats = [
    'Andale Mono=andale mono,times',
    'Arial=arial,helvetica,sans-serif',
    'Arial Black=arial black,avant garde',
    'Book Antiqua=book antiqua,palatino',
    'Comic Sans MS=comic sans ms,sans-serif',
    'Courier New=courier new,courier',
    'Georgia=georgia,palatino',
    'Helvetica=helvetica',
    'Impact=impact,chicago',
    'Inter=inter,sans-serif',
    'Lato=lato,sans-serif',
    'Montserrat=montserrat,sans-serif',
    'Open Sans=open sans,sans-serif',
    'Poppins=poppins,sans-serif',
    'Public Sans=public sans,sans-serif',
    'Roboto=roboto,sans-serif',
    'Symbol=symbol',
    'Tahoma=tahoma,arial,helvetica,sans-serif',
    'Terminal=terminal,monaco',
    'Times New Roman=times new roman,times',
    'Trebuchet MS=trebuchet ms,geneva',
    'Verdana=verdana,geneva',
    'Webdings=webdings',
    'Wingdings=wingdings,zapf dingbats',
  ].join('; ');

  const contentStyle = tinymceBaseStyle;

  const colorMap = [
    '000000', 'Black',
    '4B5563', 'Gray',
    '9CA3AF', 'Light Gray',
    'E5E7EB', 'Silver',
    'FFFFFF', 'White',
    'EF4444', 'Red',
    'F97316', 'Orange',
    'F59E0B', 'Amber',
    '84CC16', 'Lime',
    '22C55E', 'Green',
    '14B8A6', 'Teal',
    '06B6D4', 'Cyan',
    '3B82F6', 'Blue',
    '6366F1', 'Indigo',
    '8B5CF6', 'Violet',
    'A855F7', 'Purple',
    'D946EF', 'Fuchsia',
    'EC4899', 'Pink',
    'F43F5E', 'Rose',
    '78350F', 'Brown',
    'B45309', 'Dark Orange',
    '1E3A5F', 'Navy',
    '064E3B', 'Dark Green',
    '7F1D1D', 'Dark Red',
    '312E81', 'Dark Indigo',
    '701A75', 'Dark Purple',
  ];

  const conf = $derived({
    height: 500,
    min_height: 300,
    menubar: false,
    statusbar: true,
    branding: false,
    promotion: false,
    license_key: 'gpl',
    skin,
    plugins: buildPlugins(tools),
    toolbar: buildToolbar(tools),
    block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4',
    font_family_formats: fontFamilyFormats,
    font_size_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 22pt 24pt 26pt 28pt 36pt 48pt 72pt',
    placeholder,
    automatic_uploads: true,
    images_upload_handler: imagesUploadHandler,
    images_file_types: 'jpeg,jpg,png,gif,webp',
    image_title: true,
    image_advtab: true,
    image_dimensions: true,
    image_description: true,
    image_caption: true,
    object_resizing: 'img',
    relative_urls: false,
    remove_script_host: false,
    convert_urls: false,
    link_title: true,
    link_default_target: '_blank',
    link_context_toolbar: true,
    table_use_colgroups: true,
    table_default_attributes: {},
    table_default_styles: { 'border-collapse': 'collapse', width: '100%' },
    table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | cellprops mergecells splitcells',
    codesample_languages: [
      { text: 'HTML/XML', value: 'markup' },
      { text: 'JavaScript', value: 'javascript' },
      { text: 'CSS', value: 'css' },
      { text: 'PHP', value: 'php' },
      { text: 'Ruby', value: 'ruby' },
      { text: 'Python', value: 'python' },
      { text: 'Java', value: 'java' },
      { text: 'C', value: 'c' },
      { text: 'C++', value: 'cpp' },
      { text: 'C#', value: 'csharp' },
      { text: 'SQL', value: 'sql' },
      { text: 'Bash', value: 'bash' },
    ],
    color_map: colorMap,
    color_cols: 7,
    custom_colors: true,
    content_style: backgroundColor ? `${contentStyle} body { background-color: ${backgroundColor}; }` : contentStyle,
  });

  onMount(() => {
    if (!containerRef) {
      return;
    }

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(
        (entries) => {
          for (const entry of entries) {
            if (entry.isIntersecting) {
              isVisible = true;
              observer.disconnect();
            }
          }
        },
        { rootMargin: '200px 0px' }
      );

      observer.observe(containerRef);

      return () => observer.disconnect();
    } else {
      isVisible = true;
    }
  });

  onMount(() => {
    const handleThemeChange = () => {
      skin = isDarkMode() ? 'oxide-dark' : 'oxide';
    };

    const observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
          handleThemeChange();
        }
      }
    });

    observer.observe(document.documentElement, { attributes: true });

    return () => {
      observer.disconnect();
    };
  });
</script>

<div bind:this={containerRef} class="tinymce-shell" class:is-invalid={Boolean(error)}>
  {#if isVisible}
    <Editor
      {id}
      licenseKey="gpl"
      scriptSrc="/tinymce/tinymce.min.js"
      bind:value
      {conf}
      {disabled}
    />
  {:else}
    <div class="tinymce-placeholder" aria-busy="true" aria-label="Memuat editor artikel...">
      <div class="placeholder-toolbar">
        <div class="placeholder-toolbar-row">
          {#each Array(8) as _, i}
            <div class="placeholder-btn" style="width: {1.5 + (i % 3) * 0.5}rem"></div>
          {/each}
        </div>
        <div class="placeholder-toolbar-row">
          {#each Array(10) as _, i}
            <div class="placeholder-btn" style="width: {1.5 + (i % 4) * 0.5}rem"></div>
          {/each}
        </div>
      </div>
      <div class="placeholder-content">
        {#if placeholder}
          <span class="placeholder-text">{placeholder}</span>
        {:else}
          {#each Array(6) as _, i}
            <div class="placeholder-line" style="width: {85 - (i % 3) * 20}%;"></div>
          {/each}
        {/if}
      </div>
    </div>
  {/if}
  <input type="hidden" {name} {value} />
</div>

<style>
  .tinymce-shell {
    border: 1px solid var(--line-soft);
    border-radius: 0.625rem;
    overflow: hidden;
    background: var(--background);
  }

  .tinymce-shell.is-invalid {
    border-color: var(--signal-danger);
  }

  .tinymce-shell :global(.tinymce-wrapper) {
    display: block;
  }

  .tinymce-shell :global(.tox-tinymce) {
    border: none !important;
    border-radius: 0 !important;
  }

  .tinymce-shell :global(.tox-editor-header) {
    background: color-mix(in srgb, var(--muted) 40%, transparent) !important;
    border-bottom: 1px solid var(--line-soft) !important;
  }

  .tinymce-shell :global(.tox-toolbar__primary) {
    background: transparent !important;
  }

  .tinymce-shell :global(.tox-toolbar-overlord) {
    background: transparent !important;
  }

  .tinymce-shell :global(.tox-mbtn) {
    color: var(--text-strong) !important;
  }

  .tinymce-shell :global(.tox-statusbar) {
    background: color-mix(in srgb, var(--muted) 40%, transparent) !important;
    border-top: 1px solid var(--line-soft) !important;
  }

  .tinymce-shell :global(.tox-statusbar__text-container) {
    color: var(--text-muted) !important;
  }

  .tinymce-placeholder {
    display: grid;
    gap: 0;
  }

  .placeholder-toolbar {
    display: grid;
    gap: 0.3rem;
    padding: 0.5rem;
    background: color-mix(in srgb, var(--muted) 40%, transparent);
    border-bottom: 1px solid var(--line-soft);
  }

  .placeholder-toolbar-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    align-items: center;
  }

  .placeholder-btn {
    height: 1.5rem;
    border-radius: 0.25rem;
    background: color-mix(in srgb, var(--muted) 60%, transparent);
    animation: shimmer 1.5s ease-in-out infinite;
  }

  .placeholder-content {
    min-height: 18rem;
    height: 22rem;
    padding: 0.75rem 1rem;
    display: grid;
    gap: 0.5rem;
    align-content: start;
  }

  .placeholder-line {
    height: 0.9rem;
    border-radius: 0.25rem;
    background: color-mix(in srgb, var(--muted) 50%, transparent);
    animation: shimmer 1.5s ease-in-out infinite;
  }

  .placeholder-text {
    color: var(--text-muted);
    font-size: 0.9rem;
  }

  @keyframes shimmer {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 0.85; }
  }
</style>
