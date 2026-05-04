<script>
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import PageHeader from '../components/PageHeader.svelte';

  let {
    title = 'Tampilan Landing Page',
    description = '',
    form = {
      action: '#',
      csrfToken: '',
      spoofMethod: 'PUT',
    },
    values = {},
    colors = [],
    previewUrl = '/',
    errors = {},
  } = $props();

  let selectedColor = $state(null);
  let hasHydratedCustomColors = $state(false);
  let iframeRef = $state(null);
  let iframeLoaded = $state(false);

  // Deep clone incoming customCss into reactive state so mutations work
  let landingCss = $state({});

  const activeColor = $derived(selectedColor || values.themeColor || colors[0]?.name || 'purple');

  const selectedPalette = $derived.by(
    () => colors.find((color) => color.name === activeColor) || colors[0] || null,
  );

  const syncLandingBrandFromPreset = (name) => {
    const preset = colors.find((color) => color.name === name);

    if (!preset) {
      return;
    }

    landingCss['css_landing_brand_primary'] = preset.primary;
    landingCss['css_landing_brand_hover'] = preset.hover;
    landingCss['css_landing_brand_soft'] = preset.soft;
    landingCss['css_landing_brand_light'] = preset.light;
    landingCss['css_landing_brand_secondary'] = preset.secondary;
    landingCss['css_landing_brand_secondary_soft'] = preset.secondarySoft;
  };

  const selectPreset = (name) => {
    selectedColor = name;
    syncLandingBrandFromPreset(name);
    injectPreviewStyles();
  };

  const landingCssKeys = [
    { key: 'css_landing_text_strong', var: 'text-strong' },
    { key: 'css_landing_text_soft', var: 'text-soft' },
    { key: 'css_landing_text_muted', var: 'text-muted' },
    { key: 'css_landing_page_bg', var: 'page-bg' },
    { key: 'css_landing_page_bg_soft', var: 'page-bg-soft' },
    { key: 'css_landing_panel_bg', var: 'panel-bg' },
    { key: 'css_landing_panel_muted', var: 'panel-muted' },
    { key: 'css_landing_line_soft', var: 'line-soft' },
    { key: 'css_landing_brand_primary', var: 'brand-primary' },
    { key: 'css_landing_brand_hover', var: 'brand-hover' },
    { key: 'css_landing_brand_soft', var: 'brand-soft' },
    { key: 'css_landing_brand_light', var: 'brand-light' },
    { key: 'css_landing_brand_secondary', var: 'brand-secondary' },
    { key: 'css_landing_brand_secondary_soft', var: 'brand-secondary-soft' },
  ];

  const getLandingCssVariables = () => {
    const vars = [];

    for (const { key, var: cssVar } of landingCssKeys) {
      const value = landingCss[key];
      if (typeof value === 'string' && value.startsWith('#')) {
        vars.push(`  --${cssVar}: ${value};`);
      }
    }

    return vars.join('\n');
  };

  const injectPreviewStyles = () => {
    if (!iframeRef || !iframeRef.contentDocument || !iframeLoaded) {
      return;
    }

    const doc = iframeRef.contentDocument;
    let style = doc.getElementById('preview-style');

    if (!style) {
      style = doc.createElement('style');
      style.id = 'preview-style';
      doc.head.appendChild(style);
    }

    const css = getLandingCssVariables();

    style.textContent = css ? `[data-theme="public"] {\n${css}\n}` : '';
  };

  const handleIframeLoad = () => {
    iframeLoaded = true;
    injectPreviewStyles();
  };

  $effect(() => {
    if (hasHydratedCustomColors) {
      return;
    }

    landingCss = { ...(values.customCss || {}) };
    hasHydratedCustomColors = true;
  });

  $effect(() => {
    if (hasHydratedCustomColors && iframeLoaded) {
      injectPreviewStyles();
    }
  });
</script>

{#snippet cssInput(name, label)}
  {@const safeValue = typeof landingCss[name] === 'string' && landingCss[name].startsWith('#') ? landingCss[name] : '#000000'}
  <label class="grid gap-2 text-sm text-foreground">
    <span class="font-medium">{label}</span>
    <div class="flex items-center gap-2">
      <input
        type="color"
        {name}
        bind:value={landingCss[name]}
        oninput={injectPreviewStyles}
        class="h-10 w-full rounded-[8px] border border-border bg-background p-1"
      />
      <span class="text-xs text-muted-foreground font-mono w-20">{safeValue}</span>
    </div>
  </label>
{/snippet}

<div class="mx-auto max-w-7xl">
  <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader {title} {description} icon="fas fa-palette" />
    </Card.Header>

    <Card.Content class="pt-5">
      <form action={form.action} method="POST" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_24rem]">
        <input type="hidden" name="_token" value={form.csrfToken} />
        {#if form.spoofMethod}
          <input type="hidden" name="_method" value={form.spoofMethod} />
        {/if}

        <div class="grid gap-5">
          <section class="grid gap-5 rounded-[10px] border border-border bg-background px-5 py-5">
            <div>
              <div class="text-sm font-medium text-brand-primary">Paleta warna landing</div>
              <h3 class="mt-2 text-xl font-semibold text-foreground">Pilih warna utama halaman publik</h3>
              <p class="mt-2 text-sm leading-7 text-muted-foreground">
                Paleta ini hanya diterapkan pada landing page. Warna aplikasi internal tetap dikelola di Pengaturan Umum.
              </p>
            </div>

            <div class="grid gap-4">
              <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                {#each colors as color, index (color.name || index)}
                  <button
                    type="button"
                    class={`rounded-[10px] border px-4 py-4 text-left transition-colors ${activeColor === color.name ? 'border-brand-primary bg-card' : 'border-border bg-card hover:bg-muted'}`}
                    onclick={() => selectPreset(color.name)}
                    aria-pressed={activeColor === color.name}
                  >
                    <span class="block h-8 rounded-[8px]" style={`background:${color.primary}`}></span>
                    <span class="mt-2 block h-2 rounded-[6px]" style={`background:${color.secondary}`}></span>
                    <strong class="mt-3 block text-sm text-foreground">{color.label}</strong>
                    <span class="mt-1 block text-xs text-muted-foreground">{color.primary}</span>
                  </button>
                {/each}
              </div>

              <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-2 lg:grid-cols-4">
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Primary</span>
                  <input type="color" bind:value={landingCss['css_landing_brand_primary']} oninput={injectPreviewStyles} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Hover</span>
                  <input type="color" bind:value={landingCss['css_landing_brand_hover']} oninput={injectPreviewStyles} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Soft</span>
                  <input type="color" bind:value={landingCss['css_landing_brand_soft']} oninput={injectPreviewStyles} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Light</span>
                  <input type="color" bind:value={landingCss['css_landing_brand_light']} oninput={injectPreviewStyles} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Secondary</span>
                  <input type="color" bind:value={landingCss['css_landing_brand_secondary']} oninput={injectPreviewStyles} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Secondary Soft</span>
                  <input type="color" bind:value={landingCss['css_landing_brand_secondary_soft']} oninput={injectPreviewStyles} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
              </div>

              {#if selectedPalette}
                <div class="grid gap-3 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-[auto_minmax(0,1fr)] md:items-center">
                  <div class="h-12 w-12 rounded-[10px]" style={`background:${selectedPalette.primary}`}></div>
                  <div>
                    <strong class="block text-sm text-foreground">{selectedPalette.label}</strong>
                    <p class="mt-1 text-sm leading-6 text-muted-foreground">Warna brand khusus landing page. Tidak mempengaruhi tampilan aplikasi internal.</p>
                  </div>
                </div>
              {/if}
            </div>
          </section>

          <section class="grid gap-5 rounded-[10px] border border-border bg-background px-5 py-5">
            <div>
              <div class="text-sm font-medium text-brand-primary">Warna Landing Page</div>
              <h3 class="mt-2 text-xl font-semibold text-foreground">Override warna khusus halaman publik</h3>
              <p class="mt-2 text-sm leading-7 text-muted-foreground">
                Atur warna teks, latar, panel, dan brand yang hanya diterapkan pada landing page. Kosongkan untuk menggunakan nilai default.
              </p>
            </div>

            <div class="grid gap-5">
              <div>
                <h4 class="text-sm font-semibold text-foreground mb-3">Teks</h4>
                <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-3">
                  {@render cssInput('css_landing_text_strong', 'Teks Utama')}
                  {@render cssInput('css_landing_text_soft', 'Teks Lembut')}
                  {@render cssInput('css_landing_text_muted', 'Teks Redup')}
                </div>
              </div>
              <div>
                <h4 class="text-sm font-semibold text-foreground mb-3">Latar & Permukaan</h4>
                <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-2 lg:grid-cols-3">
                  {@render cssInput('css_landing_page_bg', 'Latar Halaman')}
                  {@render cssInput('css_landing_page_bg_soft', 'Latar Halaman Lembut')}
                  {@render cssInput('css_landing_panel_bg', 'Latar Panel')}
                  {@render cssInput('css_landing_panel_muted', 'Panel Redup')}
                  {@render cssInput('css_landing_line_soft', 'Garis Lembut')}
                </div>
              </div>
              <div>
                <h4 class="text-sm font-semibold text-foreground mb-3">Brand</h4>
                <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-2 lg:grid-cols-3">
                  {@render cssInput('css_landing_brand_primary', 'Brand Primary')}
                  {@render cssInput('css_landing_brand_hover', 'Brand Hover')}
                  {@render cssInput('css_landing_brand_soft', 'Brand Soft')}
                  {@render cssInput('css_landing_brand_light', 'Brand Light')}
                  {@render cssInput('css_landing_brand_secondary', 'Brand Secondary')}
                  {@render cssInput('css_landing_brand_secondary_soft', 'Brand Secondary Soft')}
                </div>
              </div>
            </div>
          </section>

          <div class="flex justify-end border-t border-border pt-5">
            <Button type="submit">
              <i class="fas fa-save"></i>
              <span>Simpan pengaturan</span>
            </Button>
          </div>
        </div>

        <div class="lg:sticky lg:top-5 h-fit">
          <div class="text-sm font-medium text-brand-primary mb-2">Pratinjau</div>
          <h3 class="text-xl font-semibold text-foreground mb-3">Tampilan Landing Page</h3>
          <div class="rounded-[10px] border border-border bg-card p-1">
            <iframe
              bind:this={iframeRef}
              src={previewUrl}
              title="Pratinjau Landing Page"
              class="w-full rounded-[8px] border-0 bg-background"
              style="height: 640px;"
              onload={handleIframeLoad}
            ></iframe>
          </div>
          <p class="mt-2 text-xs text-muted-foreground">Pratinjau diperbarui secara otomatis saat warna diubah.</p>
        </div>
      </form>
    </Card.Content>
  </Card.Root>
</div>
