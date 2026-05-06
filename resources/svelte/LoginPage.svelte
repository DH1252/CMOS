<script>
  import { onMount } from 'svelte';

  let {
    appName = 'CMOS',
    themeColor = 'purple',
    themeVariables = null,
    loginUrl = '/login',
    homeUrl = '/',
    csrfToken = '',
    email = '',
    alertMessage = '',
    alertType = '',
    emailError = '',
    passwordError = '',
    remember = false,
  } = $props();

  let emailInput;

  onMount(() => {
    document.documentElement.setAttribute('data-theme', 'dark');
    document.documentElement.setAttribute('data-brand', themeColor || 'purple');

    const runWithPostHog = window.__CMOS_WITH_POSTHOG__;

    if (typeof runWithPostHog === 'function') {
      void runWithPostHog((posthogClient) => {
        posthogClient.capture('login_page_viewed');
      });
    }

    if (window.matchMedia?.('(pointer: coarse)').matches) {
      return;
    }

    try {
      emailInput?.focus({ preventScroll: true });
    } catch {
      emailInput?.focus();
    }
  });

  $effect(() => {
    document.documentElement.setAttribute('data-brand', themeColor || 'purple');
  });

  $effect(() => {
    if (!themeVariables || typeof themeVariables !== 'object') {
      return;
    }

    Object.entries(themeVariables).forEach(([token, value]) => {
      if (typeof token !== 'string' || typeof value !== 'string') {
        return;
      }

      document.documentElement.style.setProperty(`--${token}`, value);
    });
  });
</script>

<svelte:head>
  <title>Login - {appName}</title>
  <meta name="description" content={`Masuk ke ${appName} untuk melanjutkan operasional organisasi.`} />
</svelte:head>

<div class="min-h-screen bg-background px-5 py-10 text-foreground lg:px-8">
  <div class="mx-auto grid max-w-[980px] gap-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
    <section class="space-y-5 rounded-[10px] border border-border bg-card px-6 py-8 lg:px-8 lg:py-10">
      <a href={homeUrl} data-native="true" class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground no-underline transition-colors hover:text-foreground" aria-label="Kembali ke beranda">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        <span>Kembali ke beranda</span>
      </a>

      <div class="max-w-[54ch] space-y-3">
        <p class="text-sm font-medium text-[color:var(--brand-hover)]">Akses internal organisasi</p>
        <h1 class="text-4xl font-semibold leading-tight text-foreground lg:text-5xl">Masuk ke {appName}.</h1>
        <p class="text-base leading-7 text-muted-foreground">
          Gunakan akun organisasi untuk melanjutkan task, timeline, evaluasi, dan arsip kerja kabinet.
        </p>
      </div>
    </section>

    <section class="rounded-[10px] border border-border bg-card px-6 py-8 lg:px-8 lg:py-10">
      <div class="max-w-md">
        <h2 class="m-0 text-2xl font-semibold tracking-tight text-foreground">Masuk</h2>
        <p class="mt-2 text-sm leading-6 text-muted-foreground">Gunakan email organisasi dan password akun yang masih berlaku.</p>
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
            aria-describedby={emailError ? 'login-email-error' : undefined}
            placeholder="nama@organisasi.com"
          />
          {#if emailError}
            <div id="login-email-error" class="text-sm text-[var(--signal-danger)]">{emailError}</div>
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
            aria-describedby={passwordError ? 'login-password-error' : undefined}
            placeholder="Masukkan password"
          />
          {#if passwordError}
            <div id="login-password-error" class="text-sm text-[var(--signal-danger)]">{passwordError}</div>
          {/if}
        </div>

        <label class="flex items-center gap-3 text-sm text-muted-foreground">
          <input type="checkbox" name="remember" value="1" checked={remember} class="h-4 w-4 rounded border-border bg-background text-brand-primary" />
          <span>Simpan sesi di perangkat ini</span>
        </label>

        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-[10px] bg-brand-primary px-4 text-sm font-semibold text-[var(--primary-foreground)] transition-colors hover:bg-brand-hover">
          Masuk ke CMOS
        </button>
      </form>
    </section>
  </div>
</div>
