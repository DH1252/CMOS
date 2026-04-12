<script>
  import { onMount } from 'svelte';

  let {
    appName = 'CMOS',
    loginUrl = '/login',
    homeUrl = '/',
    csrfToken = '',
    email = '',
    alertMessage = '',
    alertType = '',
    emailError = '',
    passwordError = '',
    remember = false,
    themeColor = 'purple',
  } = $props();

  let emailInput;

  const focusAreas = [
    'Task, timeline, dan evaluasi tetap berada dalam satu workspace.',
    'Dokumen, link penting, dan komunikasi internal tidak tercecer di banyak tempat.',
    'Halaman ini hanya untuk pengurus dan anggota dengan akun organisasi yang aktif.',
  ];

  onMount(() => {
    emailInput?.focus();
  });
</script>

<div class="min-h-screen bg-background px-5 py-10 text-foreground lg:px-8" data-theme-color={themeColor}>
  <div class="mx-auto grid max-w-[1080px] gap-8 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-start">
    <section class="space-y-8 rounded-[10px] border border-border bg-card px-6 py-8 lg:px-8 lg:py-10">
      <a href={homeUrl} class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground no-underline transition-colors hover:text-foreground" aria-label="Kembali ke beranda">
        <i class="fas fa-arrow-left"></i>
        <span>Kembali ke beranda</span>
      </a>

      <div class="max-w-[58ch] space-y-4">
        <p class="text-sm font-medium text-brand-primary">Akses internal organisasi</p>
        <h1 class="text-4xl font-semibold leading-tight text-foreground lg:text-5xl">Masuk ke {appName} untuk melanjutkan koordinasi kerja kabinet.</h1>
        <p class="text-base leading-8 text-muted-foreground">
          Workspace ini dipakai untuk memantau program, menindaklanjuti task, menyusun timeline, mengevaluasi staff, dan menjaga arsip kerja tetap tersambung.
        </p>
      </div>

      <div class="grid gap-4 border-t border-border pt-5 md:grid-cols-3">
        {#each focusAreas as item (item)}
          <div class="border-t border-border pt-3 text-sm leading-6 text-muted-foreground first:border-t-0 first:pt-0 md:border-t-0 md:pt-0">
            {item}
          </div>
        {/each}
      </div>
    </section>

    <section class="rounded-[10px] border border-border bg-card px-6 py-8 lg:px-8 lg:py-10">
      <div class="max-w-md">
        <p class="text-sm font-medium text-brand-primary">Akun organisasi</p>
        <h2 class="mt-2 text-2xl font-semibold tracking-tight text-foreground">Masuk ke workspace</h2>
        <p class="mt-2 text-sm leading-6 text-muted-foreground">Gunakan email organisasi dan password akun yang sudah terdaftar.</p>
      </div>

      {#if alertMessage}
        <div class={`mt-6 rounded-[10px] border px-4 py-3 text-sm ${alertType === 'info' ? 'border-[color:color-mix(in_srgb,var(--signal-info)_30%,transparent)] bg-[color:color-mix(in_srgb,var(--signal-info)_12%,transparent)] text-[var(--signal-info)]' : 'border-[color:color-mix(in_srgb,var(--signal-danger)_30%,transparent)] bg-[color:color-mix(in_srgb,var(--signal-danger)_12%,transparent)] text-[var(--signal-danger)]'}`} role="alert">
          {alertMessage}
        </div>
      {/if}

      <form method="POST" action={loginUrl} class="mt-6 grid gap-5">
        <input type="hidden" name="_token" value={csrfToken} />

        <div class="grid gap-2">
          <label class="text-sm font-medium text-foreground" for="email">Email organisasi</label>
          <input
            bind:this={emailInput}
            id="email"
            type="email"
            name="email"
            value={email}
            required
            autocomplete="email"
            class={`h-11 rounded-[10px] border bg-background px-3 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground ${emailError ? 'border-[var(--signal-danger)]' : 'border-border focus:border-brand-primary'}`}
            aria-invalid={emailError ? 'true' : 'false'}
            placeholder="nama@organisasi.com"
          />
          {#if emailError}
            <div class="text-sm text-[var(--signal-danger)]">{emailError}</div>
          {/if}
        </div>

        <div class="grid gap-2">
          <label class="text-sm font-medium text-foreground" for="password">Password</label>
          <input
            id="password"
            type="password"
            name="password"
            required
            minlength="6"
            autocomplete="current-password"
            class={`h-11 rounded-[10px] border bg-background px-3 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground ${passwordError ? 'border-[var(--signal-danger)]' : 'border-border focus:border-brand-primary'}`}
            aria-invalid={passwordError ? 'true' : 'false'}
            placeholder="Masukkan password"
          />
          {#if passwordError}
            <div class="text-sm text-[var(--signal-danger)]">{passwordError}</div>
          {/if}
        </div>

        <label class="flex items-center gap-3 text-sm text-muted-foreground">
          <input type="checkbox" name="remember" value="1" checked={remember} class="h-4 w-4 rounded border-border bg-background text-brand-primary" />
          <span>Ingat sesi saya di perangkat ini</span>
        </label>

        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-[10px] bg-brand-primary px-4 text-sm font-semibold text-[#1a1a2e] transition-colors hover:bg-brand-hover">
          Masuk ke workspace
        </button>
      </form>
    </section>
  </div>
</div>
