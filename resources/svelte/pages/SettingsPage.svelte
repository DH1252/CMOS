<script>
  import { Button } from "$lib/components/ui/button/index.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import { Input } from "$lib/components/ui/input/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import PageHeader from "../components/PageHeader.svelte";

  let {
    title = "Pengaturan Aplikasi",
    description = "",
    form = {
      action: "#",
      csrfToken: "",
      spoofMethod: "PUT",
    },
    values = {},
    errors = {},
  } = $props();
</script>

<div class="mx-auto max-w-5xl">
  <Card.Root
    class="animate-fadeIn rounded-[10px] border border-border bg-card shadow-none"
  >
    <Card.Header class="border-b border-border/70 pb-4">
      <PageHeader {title} {description} icon="fas fa-gear" />
    </Card.Header>

    <Card.Content class="pt-5">
      <form action={form.action} method="POST" class="grid gap-5">
        <input type="hidden" name="_token" value={form.csrfToken} />
        {#if form.spoofMethod}
          <input type="hidden" name="_method" value={form.spoofMethod} />
        {/if}

        <section
          class="grid gap-5 rounded-[10px] border border-border bg-background px-5 py-5 lg:grid-cols-[16rem_minmax(0,1fr)]"
        >
          <div>
            <div class="text-sm font-medium text-brand-primary">
              Identitas sistem
            </div>
            <h3 class="mt-2 text-xl font-semibold text-foreground">
              Nama aplikasi dan organisasi
            </h3>
            <p class="mt-2 text-sm leading-7 text-muted-foreground">
              Nama ini tampil pada shell internal, halaman login, dan konteks
              komunikasi organisasi.
            </p>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
              <Label for="settings-app-name">Nama aplikasi</Label>
              <Input
                id="settings-app-name"
                type="text"
                name="app_name"
                aria-invalid={Boolean(errors.app_name)}
                value={values.appName || ""}
                placeholder="CMOS"
              />
              {#if errors.app_name}
                <div class="text-sm text-[var(--signal-danger)]" role="alert">
                  {errors.app_name}
                </div>
              {/if}
            </div>

            <div class="grid gap-2">
              <Label for="settings-organization-name">Nama organisasi</Label>
              <Input
                id="settings-organization-name"
                type="text"
                name="organization_name"
                aria-invalid={Boolean(errors.organization_name)}
                value={values.organizationName || ""}
                placeholder="HIMATEKKOM ITS"
              />
              {#if errors.organization_name}
                <div class="text-sm text-[var(--signal-danger)]" role="alert">
                  {errors.organization_name}
                </div>
              {/if}
            </div>
          </div>
        </section>

        <section
          class="grid gap-5 rounded-[10px] border border-border bg-background px-5 py-5 lg:grid-cols-[16rem_minmax(0,1fr)]"
        >
          <div>
            <div class="text-sm font-medium text-brand-primary">
              Ritme evaluasi
            </div>
            <h3 class="mt-2 text-xl font-semibold text-foreground">
              Atur periode evaluasi default
            </h3>
            <p class="mt-2 text-sm leading-7 text-muted-foreground">
              Cadence ini menjadi rujukan saat pengurus meninjau evaluasi staff
              dan menyiapkan pengingat periodik.
            </p>
          </div>

          <div class="grid max-w-sm gap-2">
            <Label for="settings-evaluation-period">Periode evaluasi</Label>
            <select
              id="settings-evaluation-period"
              name="evaluation_period"
              class="h-11 rounded-[10px] border border-border bg-card px-3 text-sm text-foreground transition-colors outline-none focus:border-brand-primary"
              aria-invalid={Boolean(errors.evaluation_period)}
            >
              {#each values.periodOptions || [] as option, index (option.value || index)}
                <option
                  value={option.value}
                  selected={option.value === values.evaluationPeriod}
                  >{option.label}</option
                >
              {/each}
            </select>
            {#if errors.evaluation_period}
              <div class="text-sm text-[var(--signal-danger)]" role="alert">
                {errors.evaluation_period}
              </div>
            {/if}
          </div>
        </section>

        <section
          class="grid gap-5 rounded-[10px] border border-border bg-background px-5 py-5 lg:grid-cols-[16rem_minmax(0,1fr)]"
        >
          <div>
            <div class="text-sm font-medium text-brand-primary">
              Waktu aplikasi
            </div>
            <h3 class="mt-2 text-xl font-semibold text-foreground">
              Zona waktu operasional
            </h3>
            <p class="mt-2 text-sm leading-7 text-muted-foreground">
              Digunakan untuk jadwal otomatis, input waktu lokal, dan tampilan
              tanggal. Data tetap disimpan dalam UTC.
            </p>
          </div>

          <div class="grid max-w-lg gap-2">
            <Label for="settings-app-timezone">Zona waktu</Label>
            <select
              id="settings-app-timezone"
              name="app_timezone"
              class="h-11 rounded-[10px] border border-border bg-card px-3 text-sm text-foreground transition-colors outline-none focus:border-brand-primary"
              aria-invalid={Boolean(errors.app_timezone)}
            >
              {#each values.timezoneOptions || [] as option, index (option.value || index)}
                <option
                  value={option.value}
                  selected={option.value === values.appTimezone}
                  >{option.label}</option
                >
              {/each}
            </select>
            {#if errors.app_timezone}
              <div class="text-sm text-[var(--signal-danger)]" role="alert">
                {errors.app_timezone}
              </div>
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
