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
  let customPrimary = $state('#7C3AED');
  let customHover = $state('#6D28D9');
  let customSoft = $state('#A78BFA');
  let customLight = $state('#EDE9FE');
  let customSecondary = $state('#5B2BA9');
  let customSecondarySoft = $state('#E9E0F8');
  let customPrimaryForeground = $state('#FFFFFF');
  let iframeRef = $state(null);
  let iframeLoaded = $state(false);

  const activeColor = $derived(selectedColor || values.themeColor || colors[0]?.name || 'purple');

  const selectedPalette = $derived.by(
    () => colors.find((color) => color.name === activeColor) || colors[0] || null,
  );

  const syncFromPreset = (name) => {
    const preset = colors.find((color) => color.name === name);

    if (!preset) {
      return;
    }

    customPrimary = preset.primary;
    customHover = preset.hover;
    customSoft = preset.soft;
    customLight = preset.light;
    customSecondary = preset.secondary;
    customSecondarySoft = preset.secondarySoft;
    customPrimaryForeground = preset.primaryForeground;
  };

  const selectPreset = (name) => {
    selectedColor = name;
    syncFromPreset(name);
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
      const value = values.customCss?.[key];
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

    customPrimary = values.themePrimary || '#7C3AED';
    customHover = values.themeHover || '#6D28D9';
    customSoft = values.themeSoft || '#A78BFA';
    customLight = values.themeLight || '#EDE9FE';
    customSecondary = values.themeSecondary || '#5B2BA9';
    customSecondarySoft = values.themeSecondarySoft || '#E9E0F8';
    customPrimaryForeground = values.themePrimaryForeground || '#FFFFFF';
    hasHydratedCustomColors = true;
  });

  $effect(() => {
    if (hasHydratedCustomColors && iframeLoaded) {
      injectPreviewStyles();
    }
  });
</script>

{#snippet cssInput(name, label, rawValue)}
  {@const safeValue = typeof rawValue === 'string' && rawValue.startsWith('#') ? rawValue : '#000000'}
  <label class="grid gap-2 text-sm text-foreground">
    <span class="font-medium">{label}</span>
    <div class="flex items-center gap-2">
      <input
        type="color"
        {name}
        value={safeValue}
        oninput={(e) => {
          if (values.customCss) {
            values.customCss[name] = e.currentTarget.value;
            injectPreviewStyles();
          }
        }}
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
              <div class="text-sm font-medium text-brand-primary">Paleta warna</div>
              <h3 class="mt-2 text-xl font-semibold text-foreground">Pilih warna utama</h3>
              <p class="mt-2 text-sm leading-7 text-muted-foreground">
                Paleta dasar yang menjadi identitas brand. Warna ini juga digunakan di aplikasi internal.
              </p>
            </div>

            <div class="grid gap-4">
              <input type="hidden" name="theme_color" value={activeColor} />
              <input type="hidden" name="theme_primary" value={customPrimary} />
              <input type="hidden" name="theme_hover" value={customHover} />
              <input type="hidden" name="theme_soft" value={customSoft} />
              <input type="hidden" name="theme_light" value={customLight} />
              <input type="hidden" name="theme_secondary" value={customSecondary} />
              <input type="hidden" name="theme_secondary_soft" value={customSecondarySoft} />
              <input type="hidden" name="theme_primary_foreground" value={customPrimaryForeground} />

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
                  <input type="color" bind:value={customPrimary} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Hover</span>
                  <input type="color" bind:value={customHover} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Soft</span>
                  <input type="color" bind:value={customSoft} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Light</span>
                  <input type="color" bind:value={customLight} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Secondary</span>
                  <input type="color" bind:value={customSecondary} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Secondary Soft</span>
                  <input type="color" bind:value={customSecondarySoft} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
                <label class="grid gap-2 text-sm text-foreground">
                  <span class="font-medium">Primary Foreground</span>
                  <input type="color" bind:value={customPrimaryForeground} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
                </label>
              </div>

              {#if selectedPalette}
                <div class="grid gap-3 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-[auto_minmax(0,1fr)] md:items-center">
                  <div class="h-12 w-12 rounded-[10px]" style={`background:${selectedPalette.primary}`}></div>
                  <div>
                    <strong class="block text-sm text-foreground">{selectedPalette.label}</strong>
                    <p class="mt-1 text-sm leading-6 text-muted-foreground">Warna dasar brand untuk seluruh situs.</p>
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
                  {@render cssInput('css_landing_text_strong', 'Teks Utama', values.customCss?.css_landing_text_strong)}
                  {@render cssInput('css_landing_text_soft', 'Teks Lembut', values.customCss?.css_landing_text_soft)}
                  {@render cssInput('css_landing_text_muted', 'Teks Redup', values.customCss?.css_landing_text_muted)}
                </div>
              </div>
              <div>
                <h4 class="text-sm font-semibold text-foreground mb-3">Latar & Permukaan</h4>
                <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-2 lg:grid-cols-3">
                  {@render cssInput('css_landing_page_bg', 'Latar Halaman', values.customCss?.css_landing_page_bg)}
                  {@render cssInput('css_landing_page_bg_soft', 'Latar Halaman Lembut', values.customCss?.css_landing_page_bg_soft)}
                  {@render cssInput('css_landing_panel_bg', 'Latar Panel', values.customCss?.css_landing_panel_bg)}
                  {@render cssInput('css_landing_panel_muted', 'Panel Redup', values.customCss?.css_landing_panel_muted)}
                  {@render cssInput('css_landing_line_soft', 'Garis Lembut', values.customCss?.css_landing_line_soft)}
                </div>
              </div>
              <div>
                <h4 class="text-sm font-semibold text-foreground mb-3">Brand</h4>
                <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-2 lg:grid-cols-3">
                  {@render cssInput('css_landing_brand_primary', 'Brand Primary', values.customCss?.css_landing_brand_primary)}
                  {@render cssInput('css_landing_brand_hover', 'Brand Hover', values.customCss?.css_landing_brand_hover)}
                  {@render cssInput('css_landing_brand_soft', 'Brand Soft', values.customCss?.css_landing_brand_soft)}
                  {@render cssInput('css_landing_brand_light', 'Brand Light', values.customCss?.css_landing_brand_light)}
                  {@render cssInput('css_landing_brand_secondary', 'Brand Secondary', values.customCss?.css_landing_brand_secondary)}
                  {@render cssInput('css_landing_brand_secondary_soft', 'Brand Secondary Soft', values.customCss?.css_landing_brand_secondary_soft)}
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
