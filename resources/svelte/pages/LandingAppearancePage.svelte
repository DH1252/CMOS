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
  let iframeRef = $state(null);
  let iframeReady = $state(false);
  let landingCss = $state({});
  let saving = $state(false);

  $effect(() => {
    Object.assign(landingCss, values.customCss || {});
  });

  const activeColor = $derived(selectedColor || 'none');

  const selectedPalette = $derived.by(
    () => colors.find((color) => color.name === activeColor) || null,
  );

  const syncLandingBrandFromPreset = (presetName) => {
    const preset = colors.find((color) => color.name === presetName);

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

  const selectPreset = (presetName) => {
    selectedColor = presetName;
    syncLandingBrandFromPreset(presetName);
  };

  const landingCssKeys = [
    { key: 'css_landing_text_strong', var: 'text-strong', label: 'Teks Utama' },
    { key: 'css_landing_text_soft', var: 'text-soft', label: 'Teks Lembut' },
    { key: 'css_landing_text_muted', var: 'text-muted', label: 'Teks Redup' },
    { key: 'css_landing_page_bg', var: 'page-bg', label: 'Latar Halaman' },
    { key: 'css_landing_page_bg_soft', var: 'page-bg-soft', label: 'Latar Halaman Lembut' },
    { key: 'css_landing_panel_bg', var: 'panel-bg', label: 'Latar Panel' },
    { key: 'css_landing_panel_muted', var: 'panel-muted', label: 'Panel Redup' },
    { key: 'css_landing_line_soft', var: 'line-soft', label: 'Garis Lembut' },
    { key: 'css_landing_brand_primary', var: 'brand-primary', label: 'Brand Primary' },
    { key: 'css_landing_brand_hover', var: 'brand-hover', label: 'Brand Hover' },
    { key: 'css_landing_brand_soft', var: 'brand-soft', label: 'Brand Soft' },
    { key: 'css_landing_brand_light', var: 'brand-light', label: 'Brand Light' },
    { key: 'css_landing_brand_secondary', var: 'brand-secondary', label: 'Brand Secondary' },
    { key: 'css_landing_brand_secondary_soft', var: 'brand-secondary-soft', label: 'Brand Secondary Soft' },
  ];

  const buildPreviewCss = () => {
    const vars = [];

    for (const { key, var: cssVar } of landingCssKeys) {
      const value = landingCss[key];
      if (typeof value === 'string' && value.startsWith('#')) {
        vars.push(`  --${cssVar}: ${value};`);
      }
    }

    const css = vars.join('\n');
    return css ? `[data-theme="public"] {\n${css}\n}` : '';
  };

  const injectPreviewStyles = () => {
    if (!iframeRef || !iframeReady) {
      return;
    }

    try {
      const win = iframeRef.contentWindow;
      if (win) {
        win.postMessage({ type: 'preview-css', css: buildPreviewCss() }, '*');
      }
    } catch {
      // Silent fail
    }
  };

  const markIframeReady = () => {
    iframeReady = true;
    injectPreviewStyles();
  };

  const checkIframe = () => {
    if (!iframeRef) {
      return;
    }

    try {
      if (iframeRef.contentDocument?.readyState === 'complete') {
        markIframeReady();
        return;
      }
    } catch {
      // Cross-origin or not ready yet
    }

    iframeRef.addEventListener('load', markIframeReady, { once: true });
  };

  $effect(() => {
    checkIframe();
  });

  $effect(() => {
    if (!iframeReady) {
      return;
    }

    landingCssKeys.forEach(({ key }) => landingCss[key]);
    injectPreviewStyles();
  });
</script>

<div class="mx-auto max-w-7xl">
  <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader {title} {description} icon="fas fa-palette" />
    </Card.Header>

    <Card.Content class="pt-5">
      <form action={form.action} method="POST" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_24rem]">
        <input type="hidden" name="_token" value={form.csrfToken} />
        <input type="hidden" name="_method" value={form.spoofMethod} />

        {#each landingCssKeys as { key } (key)}
          <input type="hidden" name={key} value={landingCss[key] || ''} />
        {/each}

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

            {#snippet colorGroup(heading, keys)}
              <div>
                <h4 class="text-sm font-semibold text-foreground mb-3">{heading}</h4>
                <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-2 lg:grid-cols-3">
                  {#each keys as k (k)}
                    {@const meta = landingCssKeys.find((x) => x.key === k)}
                    <label class="grid gap-2 text-sm text-foreground">
                      <span class="font-medium">{meta?.label || k}</span>
                      <div class="flex items-center gap-2">
                        <input
                          type="color"
                          bind:value={() => landingCss[k], (v) => landingCss[k] = v}
                          class="h-10 w-full rounded-[8px] border border-border bg-background p-1"
                        />
                        <span class="text-xs text-muted-foreground font-mono w-20">
                          {typeof landingCss[k] === 'string' && landingCss[k].startsWith('#') ? landingCss[k] : '#000000'}
                        </span>
                      </div>
                    </label>
                  {/each}
                </div>
              </div>
            {/snippet}

            <div class="grid gap-5">
              {@render colorGroup('Teks', ['css_landing_text_strong', 'css_landing_text_soft', 'css_landing_text_muted'])}
              {@render colorGroup('Latar & Permukaan', ['css_landing_page_bg', 'css_landing_page_bg_soft', 'css_landing_panel_bg', 'css_landing_panel_muted', 'css_landing_line_soft'])}
              {@render colorGroup('Brand', ['css_landing_brand_primary', 'css_landing_brand_hover', 'css_landing_brand_soft', 'css_landing_brand_light', 'css_landing_brand_secondary', 'css_landing_brand_secondary_soft'])}
            </div>
          </section>

          <div class="flex justify-end border-t border-border pt-5">
            <Button type="submit">
              <i class="fas fa-save"></i>
              <span>Simpan pengaturan</span>
            </Button>
          </div>
        </div>

        <div class="lg:sticky lg:top-5 self-start h-fit">
          <div class="text-sm font-medium text-brand-primary mb-2">Pratinjau</div>
          <h3 class="text-xl font-semibold text-foreground mb-3">Tampilan Landing Page</h3>
          <div class="rounded-[10px] border border-border bg-card p-1">
            <iframe
              bind:this={iframeRef}
              src={previewUrl}
              title="Pratinjau Landing Page"
              class="w-full rounded-[8px] border-0 bg-background"
              style="height: 640px;"
            ></iframe>
          </div>
          <p class="mt-2 text-xs text-muted-foreground">Pratinjau diperbarui secara otomatis saat warna diubah.</p>
        </div>
      </form>
    </Card.Content>
  </Card.Root>
</div>
