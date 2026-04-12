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

  const curatedAccentNames = ['purple', 'indigo', 'slate', 'amber'];
  let selectedColor = $state(null);
  const activeColor = $derived(selectedColor || values.themeColor || colors[0]?.name || 'purple');

  const visibleColors = $derived.by(() => {
    const filtered = colors.filter((color) => curatedAccentNames.includes(color.name));

    if (!filtered.find((color) => color.name === activeColor)) {
      const current = colors.find((color) => color.name === activeColor);

      if (current) {
        return [current, ...filtered.filter((color) => color.name !== current.name)];
      }
    }

    return filtered;
  });

  const selectedPalette = $derived.by(
    () => colors.find((color) => color.name === activeColor) || colors[0] || null,
  );
</script>

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
            <div class="text-sm font-medium text-brand-primary">Aksen workspace</div>
            <h3 class="mt-2 text-xl font-semibold text-foreground">Pilih warna bantu untuk shell internal</h3>
            <p class="mt-2 text-sm leading-7 text-muted-foreground">Identitas utama tetap mengikuti nuansa ungu-emas CMOS. Pilihan ini mengubah aksen kerja internal tanpa mengubah arah brand utamanya.</p>
          </div>

          <div class="grid gap-4">
            <input type="hidden" name="theme_color" value={activeColor} />

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-2">
              {#each visibleColors as color, index (color.name || index)}
                <button
                  type="button"
                  class={`rounded-[10px] border px-4 py-4 text-left transition-colors ${activeColor === color.name ? 'border-brand-primary bg-card' : 'border-border bg-card hover:bg-muted'}`}
                  onclick={() => (selectedColor = color.name)}
                  aria-pressed={activeColor === color.name}
                >
                  <span class="block h-8 rounded-[8px]" style={`background:${color.hex}`}></span>
                  <strong class="mt-3 block text-sm text-foreground">{color.label}</strong>
                  <span class="mt-1 block text-xs text-muted-foreground">{color.hex}</span>
                </button>
              {/each}
            </div>

            {#if selectedPalette}
              <div class="grid gap-3 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-[auto_minmax(0,1fr)] md:items-center">
                <div class="h-12 w-12 rounded-[10px]" style={`background:${selectedPalette.hex}`}></div>
                <div>
                  <strong class="block text-sm text-foreground">{selectedPalette.label}</strong>
                  <p class="mt-1 text-sm leading-6 text-muted-foreground">Aksen ini dipakai sebagai warna bantu di workspace internal. Identitas utama tetap mengikuti arah ungu-emas CMOS.</p>
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
