<script>
  import { Button } from '$lib/components/ui/button/index.js';
  import * as Card from '$lib/components/ui/card/index.js';
  import { Input } from '$lib/components/ui/input/index.js';
  import { Label } from '$lib/components/ui/label/index.js';
  import PageHeader from '../components/PageHeader.svelte';

  let {
    title = 'Pengaturan Aplikasi',
    description = '',
    form = {
      action: '#',
      csrfToken: '',
      spoofMethod: 'PUT',
    },
    values = {},
    colors = [],
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
  let cssTab = $state('light');
  const activeColor = $derived(selectedColor || values.themeColor || colors[0]?.name || 'purple');

  const selectedPalette = $derived.by(
    () => colors.find((color) => color.name === activeColor) || colors[0] || null,
  );

  const palettePreview = $derived.by(() => ({
    primary: customPrimary,
    hover: customHover,
    soft: customSoft,
    light: customLight,
    secondary: customSecondary,
    secondarySoft: customSecondarySoft,
    primaryForeground: customPrimaryForeground,
  }));

  const applyPreviewTheme = () => {
    if (typeof document === 'undefined') {
      return;
    }

    document.documentElement.setAttribute('data-brand', activeColor);
    document.documentElement.style.setProperty('--brand-primary-base', customPrimary);
    document.documentElement.style.setProperty('--brand-hover-base', customHover);
    document.documentElement.style.setProperty('--brand-soft-base', customSoft);
    document.documentElement.style.setProperty('--brand-light-base', customLight);
    document.documentElement.style.setProperty('--brand-secondary-base', customSecondary);
    document.documentElement.style.setProperty('--brand-secondary-soft-base', customSecondarySoft);
    document.documentElement.style.setProperty('--primary-foreground-base', customPrimaryForeground);
  };

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
    applyPreviewTheme();
  });
</script>

{#snippet cssInput(name, label, rawValue)}
  {@const safeValue = typeof rawValue === 'string' && rawValue.startsWith('#') ? rawValue : '#000000'}
  <label class="grid gap-2 text-sm text-foreground">
    <span class="font-medium">{label}</span>
    <div class="flex items-center gap-2">
      <input type="color" {name} value={safeValue} class="h-10 w-full rounded-[8px] border border-border bg-background p-1" />
      <span class="text-xs text-muted-foreground font-mono w-20">{safeValue}</span>
    </div>
  </label>
{/snippet}

<div class="mx-auto max-w-5xl">
  <Card.Root class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none">
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader {title} {description} icon="fas fa-gear" />
    </Card.Header>

    <Card.Content class="pt-5">
      <form action={form.action} method="POST" class="grid gap-5">
        <input type="hidden" name="_token" value={form.csrfToken} />
        {#if form.spoofMethod}
          <input type="hidden" name="_method" value={form.spoofMethod} />
        {/if}

        <section class="grid gap-5 rounded-[10px] border border-border bg-background px-5 py-5 lg:grid-cols-[16rem_minmax(0,1fr)]">
          <div>
            <div class="text-sm font-medium text-brand-primary">Identitas sistem</div>
            <h3 class="mt-2 text-xl font-semibold text-foreground">Nama aplikasi dan organisasi</h3>
            <p class="mt-2 text-sm leading-7 text-muted-foreground">Nama ini tampil pada shell internal, halaman login, dan konteks komunikasi organisasi.</p>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
              <Label for="settings-app-name">Nama aplikasi</Label>
              <Input
                id="settings-app-name"
                type="text"
                name="app_name"
                aria-invalid={Boolean(errors.app_name)}
                value={values.appName || ''}
                placeholder="CMOS"
              />
              {#if errors.app_name}
                <div class="text-sm text-[var(--signal-danger)]" role="alert">{errors.app_name}</div>
              {/if}
            </div>

            <div class="grid gap-2">
              <Label for="settings-organization-name">Nama organisasi</Label>
              <Input
                id="settings-organization-name"
                type="text"
                name="organization_name"
                aria-invalid={Boolean(errors.organization_name)}
                value={values.organizationName || ''}
                placeholder="HIMATEKKOM ITS"
              />
              {#if errors.organization_name}
                <div class="text-sm text-[var(--signal-danger)]" role="alert">{errors.organization_name}</div>
              {/if}
            </div>
          </div>
        </section>

        <section class="grid gap-5 rounded-[10px] border border-border bg-background px-5 py-5 lg:grid-cols-[16rem_minmax(0,1fr)]">
          <div>
            <div class="text-sm font-medium text-brand-primary">Warna halaman</div>
            <h3 class="mt-2 text-xl font-semibold text-foreground">Atur warna utama untuk seluruh situs</h3>
            <p class="mt-2 text-sm leading-7 text-muted-foreground">Pilihan ini mengubah warna tombol, tautan, fokus, panel, dan elemen pendukung di halaman publik, login, dan workspace internal.</p>
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

            <div class="grid gap-3 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-2 md:items-center">
              <div class="grid gap-2">
                <strong class="text-sm text-foreground">Teks tombol utama</strong>
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class={`h-9 rounded-[8px] border px-3 text-sm font-medium ${customPrimaryForeground === '#FFFFFF' ? 'border-brand-primary bg-brand-primary text-white' : 'border-border bg-card text-foreground'}`}
                    onclick={() => (customPrimaryForeground = '#FFFFFF')}
                  >
                    Putih
                  </button>
                  <button
                    type="button"
                    class={`h-9 rounded-[8px] border px-3 text-sm font-medium ${customPrimaryForeground === '#0F172A' ? 'border-brand-primary bg-brand-primary text-[var(--primary-foreground)]' : 'border-border bg-card text-foreground'}`}
                    onclick={() => (customPrimaryForeground = '#0F172A')}
                  >
                    Gelap
                  </button>
                </div>
              </div>
              <div class="flex items-center justify-start md:justify-end">
                <div class="inline-flex h-10 items-center rounded-[8px] border border-border px-3 text-sm font-semibold" style={`background:${palettePreview.primary};color:${palettePreview.primaryForeground}`}>
                  Preview Tombol
                </div>
              </div>
            </div>

            {#if selectedPalette}
              <div class="grid gap-3 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-[auto_minmax(0,1fr)] md:items-center">
                <div class="h-12 w-12 rounded-[10px]" style={`background:${selectedPalette.primary}`}></div>
                <div>
                  <strong class="block text-sm text-foreground">{selectedPalette.label}</strong>
                  <p class="mt-1 text-sm leading-6 text-muted-foreground">Warna ini diterapkan untuk seluruh situs, termasuk halaman publik, login, dan workspace internal.</p>
                </div>
              </div>
            {/if}
          </div>
        </section>

        <section class="grid gap-5 rounded-[10px] border border-border bg-background px-5 py-5 lg:grid-cols-[16rem_minmax(0,1fr)]">
          <div>
            <div class="text-sm font-medium text-brand-primary">Variabel CSS</div>
            <h3 class="mt-2 text-xl font-semibold text-foreground">Kustomisasi warna teks, permukaan, dan sinyal</h3>
            <p class="mt-2 text-sm leading-7 text-muted-foreground">Atur warna teks, latar, panel, dan sinyal secara langsung. Nilai kosong akan menggunakan default sistem.</p>
          </div>

          <div class="grid gap-4">
            <div class="flex gap-2">
              <button type="button" onclick={() => (cssTab = 'light')} class={`h-9 rounded-[8px] border px-4 text-sm font-medium transition-colors ${cssTab === 'light' ? 'border-brand-primary bg-brand-primary text-white' : 'border-border bg-card text-foreground hover:bg-muted'}`}>Mode Terang</button>
              <button type="button" onclick={() => (cssTab = 'dark')} class={`h-9 rounded-[8px] border px-4 text-sm font-medium transition-colors ${cssTab === 'dark' ? 'border-brand-primary bg-brand-primary text-white' : 'border-border bg-card text-foreground hover:bg-muted'}`}>Mode Gelap</button>
              <button type="button" onclick={() => (cssTab = 'signal')} class={`h-9 rounded-[8px] border px-4 text-sm font-medium transition-colors ${cssTab === 'signal' ? 'border-brand-primary bg-brand-primary text-white' : 'border-border bg-card text-foreground hover:bg-muted'}`}>Sinyal</button>
            </div>

            {#if cssTab === 'light'}
              <div class="grid gap-5">
                <div>
                  <h4 class="text-sm font-semibold text-foreground mb-3">Teks</h4>
                  <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-3">
                    {@render cssInput('css_text_strong', 'Teks Utama', values.customCss?.light?.css_text_strong)}
                    {@render cssInput('css_text_soft', 'Teks Lembut', values.customCss?.light?.css_text_soft)}
                    {@render cssInput('css_text_muted', 'Teks Redup', values.customCss?.light?.css_text_muted)}
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-semibold text-foreground mb-3">Latar & Permukaan</h4>
                  <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-2 lg:grid-cols-3">
                    {@render cssInput('css_page_bg', 'Latar Halaman', values.customCss?.light?.css_page_bg)}
                    {@render cssInput('css_page_bg_soft', 'Latar Halaman Lembut', values.customCss?.light?.css_page_bg_soft)}
                    {@render cssInput('css_panel_bg', 'Latar Panel', values.customCss?.light?.css_panel_bg)}
                    {@render cssInput('css_panel_muted', 'Panel Redup', values.customCss?.light?.css_panel_muted)}
                    {@render cssInput('css_line_soft', 'Garis Lembut', values.customCss?.light?.css_line_soft)}
                  </div>
                </div>
              </div>
            {:else if cssTab === 'dark'}
              <div class="grid gap-5">
                <div>
                  <h4 class="text-sm font-semibold text-foreground mb-3">Teks</h4>
                  <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-3">
                    {@render cssInput('css_dark_text_strong', 'Teks Utama (Gelap)', values.customCss?.dark?.css_dark_text_strong)}
                    {@render cssInput('css_dark_text_soft', 'Teks Lembut (Gelap)', values.customCss?.dark?.css_dark_text_soft)}
                    {@render cssInput('css_dark_text_muted', 'Teks Redup (Gelap)', values.customCss?.dark?.css_dark_text_muted)}
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-semibold text-foreground mb-3">Latar & Permukaan</h4>
                  <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-2 lg:grid-cols-3">
                    {@render cssInput('css_dark_page_bg', 'Latar Halaman (Gelap)', values.customCss?.dark?.css_dark_page_bg)}
                    {@render cssInput('css_dark_page_bg_soft', 'Latar Halaman Lembut (Gelap)', values.customCss?.dark?.css_dark_page_bg_soft)}
                    {@render cssInput('css_dark_panel_bg', 'Latar Panel (Gelap)', values.customCss?.dark?.css_dark_panel_bg)}
                    {@render cssInput('css_dark_panel_muted', 'Panel Redup (Gelap)', values.customCss?.dark?.css_dark_panel_muted)}
                    {@render cssInput('css_dark_line_soft', 'Garis Lembut (Gelap)', values.customCss?.dark?.css_dark_line_soft)}
                  </div>
                </div>
              </div>
            {:else}
              <div>
                <h4 class="text-sm font-semibold text-foreground mb-3">Status & Sinyal</h4>
                <div class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-2 lg:grid-cols-4">
                  {@render cssInput('css_signal_success', 'Sukses', values.customCss?.light?.css_signal_success)}
                  {@render cssInput('css_signal_warning', 'Peringatan', values.customCss?.light?.css_signal_warning)}
                  {@render cssInput('css_signal_danger', 'Bahaya', values.customCss?.light?.css_signal_danger)}
                  {@render cssInput('css_signal_info', 'Info', values.customCss?.light?.css_signal_info)}
                </div>
              </div>
            {/if}
          </div>
        </section>

        <section class="grid gap-5 rounded-[10px] border border-border bg-background px-5 py-5 lg:grid-cols-[16rem_minmax(0,1fr)]">
          <div>
            <div class="text-sm font-medium text-brand-primary">Ritme evaluasi</div>
            <h3 class="mt-2 text-xl font-semibold text-foreground">Atur periode evaluasi default</h3>
            <p class="mt-2 text-sm leading-7 text-muted-foreground">Cadence ini menjadi rujukan saat pengurus meninjau evaluasi staff dan menyiapkan pengingat periodik.</p>
          </div>

          <div class="grid gap-2 max-w-sm">
            <Label for="settings-evaluation-period">Periode evaluasi</Label>
            <select
              id="settings-evaluation-period"
              name="evaluation_period"
              class="h-11 rounded-[10px] border border-border bg-card px-3 text-sm text-foreground outline-none transition-colors focus:border-brand-primary"
              aria-invalid={Boolean(errors.evaluation_period)}
            >
              {#each values.periodOptions || [] as option, index (option.value || index)}
                <option value={option.value} selected={option.value === values.evaluationPeriod}>{option.label}</option>
              {/each}
            </select>
            {#if errors.evaluation_period}
              <div class="text-sm text-[var(--signal-danger)]" role="alert">{errors.evaluation_period}</div>
            {/if}
          </div>
        </section>

        <div class="flex justify-end border-t border-border pt-5">
          <Button type="submit">
            <i class="fas fa-save"></i>
            <span>Simpan pengaturan</span>
          </Button>
        </div>
      </form>
    </Card.Content>
  </Card.Root>
</div>
