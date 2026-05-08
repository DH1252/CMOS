<script>
  import { onMount } from "svelte";
  import { Button } from "$lib/components/ui/button/index.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import PageHeader from "../components/PageHeader.svelte";

  let {
    title = "Tampilan Landing Page",
    description = "",
    form = {
      action: "#",
      csrfToken: "",
      spoofMethod: "PUT",
    },
    values = {},
    colors = [],
    previewUrl = "/",
    errors = {},
  } = $props();

  let selectedColor = $state(null);
  let iframeRef = $state(null);
  let iframeReady = $state(false);

  const activeColor = $derived(selectedColor || "none");

  const selectedPalette = $derived.by(
    () => colors.find((color) => color.name === activeColor) || null,
  );

  const landingCssKeys = [
    {
      key: "css_landing_terminal_bg",
      var: "landing-terminal-bg",
      label: "Latar Terminal",
    },
    {
      key: "css_landing_terminal_hero_bg",
      var: "landing-terminal-hero-bg",
      label: "Latar Gambar Hero",
    },
    {
      key: "css_landing_terminal_panel",
      var: "landing-terminal-panel",
      label: "Latar Panel Terminal",
    },
    {
      key: "css_landing_terminal_panel_soft",
      var: "landing-terminal-panel-soft",
      label: "Latar Hover Panel",
    },
    {
      key: "css_landing_terminal_line",
      var: "landing-terminal-line",
      label: "Garis dan Border",
    },
    {
      key: "css_landing_terminal_text",
      var: "landing-terminal-text",
      label: "Teks Kuat Umum",
    },
    {
      key: "css_landing_terminal_heading",
      var: "landing-terminal-heading",
      label: "Judul Halaman dan Bagian",
    },
    {
      key: "css_landing_terminal_soft",
      var: "landing-terminal-soft",
      label: "Teks Isi",
    },
    {
      key: "css_landing_terminal_muted",
      var: "landing-terminal-muted",
      label: "Teks Metadata",
    },
    {
      key: "css_landing_terminal_accent",
      var: "landing-terminal-accent",
      label: "Latar Tombol Utama",
    },
    {
      key: "css_landing_terminal_interactive",
      var: "landing-terminal-interactive",
      label: "Link Aktif dan Fokus",
    },
    {
      key: "css_landing_terminal_command",
      var: "landing-terminal-command",
      label: "Prompt dan Kursor",
    },
    {
      key: "css_landing_terminal_frame_accent",
      var: "landing-terminal-frame-accent",
      label: "Aksen Hero dan Foto",
    },
    {
      key: "css_landing_terminal_icon",
      var: "landing-terminal-icon",
      label: "Ikon Kategori Program",
    },
    {
      key: "css_landing_terminal_button_text",
      var: "landing-terminal-button-text",
      label: "Teks Tombol Utama",
    },
    {
      key: "css_landing_terminal_button_hover",
      var: "landing-terminal-button-hover",
      label: "Latar Hover Tombol Utama",
    },
    {
      key: "css_landing_terminal_button_secondary_text",
      var: "landing-terminal-button-secondary-text",
      label: "Teks Tombol Sekunder",
    },
    {
      key: "css_landing_terminal_button_secondary_hover",
      var: "landing-terminal-button-secondary-hover",
      label: "Latar Hover Tombol Sekunder",
    },
  ];

  const initialValues = $derived(values.customCss || {});

  const getFormValue = (key) => {
    const input = document.querySelector(`input[data-css-key="${key}"]`);
    return input ? input.value : initialValues[key] || "#000000";
  };

  const buildPreviewVars = () => {
    const vars = {};
    for (const { key, var: cssVar } of landingCssKeys) {
      const value = getFormValue(key);
      if (typeof value === "string" && value.startsWith("#")) {
        vars[`--${cssVar}`] = value;
      }
    }
    return vars;
  };

  const sendPreview = () => {
    if (!iframeRef || !iframeReady) return;
    try {
      iframeRef.contentWindow?.postMessage(
        { type: "preview-css", vars: buildPreviewVars() },
        "*",
      );
    } catch {
      /* silent */
    }
  };

  const onColorInput = (e) => {
    const key = e.currentTarget.dataset.cssKey;
    const hex = e.currentTarget.value;
    const hexDisplay =
      e.currentTarget.parentElement.querySelector(".hex-display");
    if (hexDisplay) hexDisplay.textContent = hex;

    const hiddenInput = document.querySelector(
      `input[type="hidden"][name="${key}"]`,
    );
    if (hiddenInput) hiddenInput.value = hex;

    sendPreview();
  };

  const applyPreset = (presetName) => {
    selectedColor = presetName;
    const preset = colors.find((c) => c.name === presetName);
    if (!preset) return;

    const brandMap = {
      css_landing_terminal_line: preset.secondary,
      css_landing_terminal_accent: preset.primary,
      css_landing_terminal_interactive: preset.hover,
      css_landing_terminal_command: preset.soft,
      css_landing_terminal_frame_accent: preset.secondary,
      css_landing_terminal_icon: preset.primary,
      css_landing_terminal_button_text: preset.primaryForeground,
      css_landing_terminal_button_hover: preset.hover,
      css_landing_terminal_button_secondary_hover: preset.secondarySoft,
    };

    for (const [key, value] of Object.entries(brandMap)) {
      const input = document.querySelector(`input[data-css-key="${key}"]`);
      if (input) input.value = value;
      const hiddenInput = document.querySelector(
        `input[type="hidden"][name="${key}"]`,
      );
      if (hiddenInput) hiddenInput.value = value;
      const hexDisplay = input?.parentElement?.querySelector(".hex-display");
      if (hexDisplay) hexDisplay.textContent = value;
    }

    sendPreview();
  };

  onMount(() => {
    const onLoad = () => {
      iframeReady = true;
      sendPreview();
    };

    if (iframeRef?.contentDocument?.readyState === "complete") {
      onLoad();
    } else {
      iframeRef?.addEventListener("load", onLoad, { once: true });
    }
  });
</script>

<div class="mx-auto max-w-7xl">
  <Card.Root
    class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader {title} {description} icon="fas fa-palette" />
    </Card.Header>

    <Card.Content class="pt-5">
      <form
        action={form.action}
        method="POST"
        class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_24rem]"
      >
        <input type="hidden" name="_token" value={form.csrfToken} />
        <input type="hidden" name="_method" value={form.spoofMethod} />

        {#each landingCssKeys as { key } (key)}
          <input type="hidden" name={key} value={initialValues[key] || ""} />
        {/each}

        <div class="grid gap-5">
          <section
            class="grid gap-5 rounded-[10px] border border-border bg-background px-5 py-5"
          >
            <div>
              <div class="text-sm font-medium text-brand-primary">
                Paleta warna landing
              </div>
              <h3 class="mt-2 text-xl font-semibold text-foreground">
                Pilih warna utama halaman publik
              </h3>
              <p class="mt-2 text-sm leading-7 text-muted-foreground">
                Paleta ini hanya diterapkan pada landing page. Warna aplikasi
                internal tetap dikelola di Pengaturan Umum.
              </p>
            </div>

            <div class="grid gap-4">
              <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                {#each colors as color, index (color.name || index)}
                  <button
                    type="button"
                    class={`rounded-[10px] border px-4 py-4 text-left transition-colors ${activeColor === color.name ? "border-brand-primary bg-card" : "border-border bg-card hover:bg-muted"}`}
                    onclick={() => applyPreset(color.name)}
                    aria-pressed={activeColor === color.name}
                  >
                    <span
                      class="block h-8 rounded-[8px]"
                      style={`background:${color.primary}`}
                    ></span>
                    <span
                      class="mt-2 block h-2 rounded-[6px]"
                      style={`background:${color.secondary}`}
                    ></span>
                    <strong class="mt-3 block text-sm text-foreground"
                      >{color.label}</strong
                    >
                    <span class="mt-1 block text-xs text-muted-foreground"
                      >{color.primary}</span
                    >
                  </button>
                {/each}
              </div>

              {#if selectedPalette}
                <div
                  class="grid gap-3 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-[auto_minmax(0,1fr)] md:items-center"
                >
                  <div
                    class="h-12 w-12 rounded-[10px]"
                    style={`background:${selectedPalette.primary}`}
                  ></div>
                  <div>
                    <strong class="block text-sm text-foreground"
                      >{selectedPalette.label}</strong
                    >
                    <p class="mt-1 text-sm leading-6 text-muted-foreground">
                      Preset ini menata border, tombol utama, link aktif,
                      prompt, dan aksen hero pada landing page terminal.
                    </p>
                  </div>
                </div>
              {/if}
            </div>
          </section>

          <section
            class="grid gap-5 rounded-[10px] border border-border bg-background px-5 py-5"
          >
            <div>
              <div class="text-sm font-medium text-brand-primary">
                Warna Landing Page
              </div>
              <h3 class="mt-2 text-xl font-semibold text-foreground">
                Override warna terminal landing page
              </h3>
              <p class="mt-2 text-sm leading-7 text-muted-foreground">
                Hanya kontrol yang benar-benar dipakai landing page ditampilkan
                di sini. Kosongkan untuk kembali ke nilai default.
              </p>
            </div>

            {#snippet colorGroup(heading, keys)}
              <div>
                <h4 class="mb-3 text-sm font-semibold text-foreground">
                  {heading}
                </h4>
                <div
                  class="grid gap-4 rounded-[10px] border border-border bg-card px-4 py-4 md:grid-cols-2 lg:grid-cols-3"
                >
                  {#each keys as k (k)}
                    {@const meta = landingCssKeys.find((x) => x.key === k)}
                    <label class="grid gap-2 text-sm text-foreground">
                      <span class="font-medium">{meta?.label || k}</span>
                      <div class="flex items-center gap-2">
                        <input
                          type="color"
                          data-css-key={k}
                          value={initialValues[k] &&
                          initialValues[k].startsWith("#")
                            ? initialValues[k]
                            : "#000000"}
                          oninput={onColorInput}
                          class="h-10 w-full rounded-[8px] border border-border bg-background p-1"
                        />
                        <span
                          class="hex-display w-20 font-mono text-xs text-muted-foreground"
                        >
                          {initialValues[k] && initialValues[k].startsWith("#")
                            ? initialValues[k]
                            : "#000000"}
                        </span>
                      </div>
                    </label>
                  {/each}
                </div>
              </div>
            {/snippet}

            <div class="grid gap-5">
              {@render colorGroup("Terminal Landing Page", [
                "css_landing_terminal_bg",
                "css_landing_terminal_hero_bg",
                "css_landing_terminal_panel",
                "css_landing_terminal_panel_soft",
                "css_landing_terminal_line",
                "css_landing_terminal_text",
                "css_landing_terminal_heading",
                "css_landing_terminal_soft",
                "css_landing_terminal_muted",
                "css_landing_terminal_accent",
                "css_landing_terminal_interactive",
                "css_landing_terminal_command",
                "css_landing_terminal_frame_accent",
                "css_landing_terminal_icon",
                "css_landing_terminal_button_text",
                "css_landing_terminal_button_hover",
                "css_landing_terminal_button_secondary_text",
                "css_landing_terminal_button_secondary_hover",
              ])}
            </div>
          </section>

          <div class="flex justify-end border-t border-border pt-5">
            <Button type="submit">
              <i class="fas fa-save"></i>
              <span>Simpan pengaturan</span>
            </Button>
          </div>
        </div>

        <div class="h-fit self-start lg:sticky lg:top-5">
          <div class="mb-2 text-sm font-medium text-brand-primary">
            Pratinjau
          </div>
          <h3 class="mb-3 text-xl font-semibold text-foreground">
            Tampilan Landing Page
          </h3>
          <div class="rounded-[10px] border border-border bg-card p-1">
            <iframe
              bind:this={iframeRef}
              src={previewUrl}
              title="Pratinjau Landing Page"
              class="w-full rounded-[8px] border-0 bg-background"
              style="height: 640px;"
            ></iframe>
          </div>
          <p class="mt-2 text-xs text-muted-foreground">
            Pratinjau diperbarui secara otomatis saat warna diubah.
          </p>
        </div>
      </form>
    </Card.Content>
  </Card.Root>
</div>
