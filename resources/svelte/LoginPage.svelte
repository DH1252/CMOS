<script>
  import { onMount } from "svelte";
  import { Button } from "$lib/components/ui/button/index.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import { Input } from "$lib/components/ui/input/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import PageHeader from "./components/PageHeader.svelte";

  let {
    appName = "CMOS",
    themeColor = "purple",
    themeVariables = null,
    loginUrl = "/login",
    homeUrl = "/",
    csrfToken = "",
    email = "",
    alertMessage = "",
    alertType = "",
    emailError = "",
    passwordError = "",
    remember = false,
  } = $props();

  let emailInput = $state(null);

  const readThemeMode = () => {
    try {
      const saved = localStorage.getItem("cmos-theme");
      return saved === "light" || saved === "dark" ? saved : "dark";
    } catch {
      return "dark";
    }
  };

  onMount(() => {
    document.documentElement.setAttribute("data-theme", readThemeMode());
    document.documentElement.setAttribute("data-brand", themeColor || "purple");

    const runWithPostHog = window.__CMOS_WITH_POSTHOG__;

    if (typeof runWithPostHog === "function") {
      void runWithPostHog((posthogClient) => {
        posthogClient.capture("login_page_viewed");
      });
    }

    if (window.matchMedia?.("(pointer: coarse)").matches) {
      return;
    }

    try {
      emailInput?.focus({ preventScroll: true });
    } catch {
      emailInput?.focus();
    }
  });

  $effect(() => {
    document.documentElement.setAttribute("data-brand", themeColor || "purple");
  });

  $effect(() => {
    if (!themeVariables || typeof themeVariables !== "object") {
      return;
    }

    Object.entries(themeVariables).forEach(([token, value]) => {
      if (typeof token !== "string" || typeof value !== "string") {
        return;
      }

      document.documentElement.style.setProperty(`--${token}`, value);
    });
  });
</script>

<svelte:head>
  <title>Login - {appName}</title>
  <meta
    name="description"
    content={`Masuk ke ${appName} untuk melanjutkan operasional organisasi.`}
  />
</svelte:head>

<div
  class="min-h-screen bg-background px-5 py-8 text-foreground lg:px-8 lg:py-10"
>
  <div class="mx-auto max-w-[720px]">
    <Card.Root class="rounded-[10px] border border-border bg-card shadow-none">
      <Card.Header class="border-b border-border pb-4">
        <a
          href={homeUrl}
          data-native="true"
          class="mb-4 inline-flex items-center gap-2 rounded-[10px] border border-border bg-background px-3.5 py-2 text-sm font-medium text-foreground no-underline transition-colors hover:border-brand-primary hover:bg-muted/70"
          aria-label="Kembali ke beranda"
        >
          <i class="fas fa-arrow-left" aria-hidden="true"></i>
          <span>Kembali</span>
        </a>
        <PageHeader
          title={`Masuk ke ${appName}`}
          icon="fas fa-right-to-bracket"
        />
      </Card.Header>

      <Card.Content class="grid gap-5 pt-5">
        {#if alertMessage}
          <div
            class={`rounded-[10px] border px-4 py-3 text-sm ${alertType === "info" ? "border-[color:color-mix(in_srgb,var(--signal-info)_30%,transparent)] bg-[color:color-mix(in_srgb,var(--signal-info)_12%,transparent)] text-[var(--signal-info)]" : "border-[color:color-mix(in_srgb,var(--signal-danger)_30%,transparent)] bg-[color:color-mix(in_srgb,var(--signal-danger)_12%,transparent)] text-[var(--signal-danger)]"}`}
            role="alert"
          >
            {alertMessage}
          </div>
        {/if}

        <form method="POST" action={loginUrl} class="grid gap-5">
          <input type="hidden" name="_token" value={csrfToken} />

          <div
            class="grid gap-4 rounded-[10px] border border-border bg-background px-4 py-4"
          >
            <div class="grid gap-2">
              <Label for="email">Email organisasi</Label>
              <Input
                bind:ref={emailInput}
                id="email"
                type="email"
                name="email"
                value={email}
                required
                autocomplete="email"
                class="h-11 rounded-[10px] bg-background px-3"
                aria-invalid={emailError ? "true" : "false"}
                aria-describedby={emailError ? "login-email-error" : undefined}
                placeholder="nama@organisasi.com"
              />
              {#if emailError}
                <div
                  id="login-email-error"
                  class="text-sm text-[var(--signal-danger)]"
                >
                  {emailError}
                </div>
              {/if}
            </div>

            <div class="grid gap-2">
              <Label for="password">Password</Label>
              <Input
                id="password"
                type="password"
                name="password"
                required
                minlength="6"
                autocomplete="current-password"
                class="h-11 rounded-[10px] bg-background px-3"
                aria-invalid={passwordError ? "true" : "false"}
                aria-describedby={passwordError
                  ? "login-password-error"
                  : undefined}
                placeholder="Masukkan password"
              />
              {#if passwordError}
                <div
                  id="login-password-error"
                  class="text-sm text-[var(--signal-danger)]"
                >
                  {passwordError}
                </div>
              {/if}
            </div>
          </div>

          <div
            class="flex flex-col gap-4 rounded-[10px] border border-border bg-background px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
          >
            <label
              class="flex items-center gap-3 text-sm text-muted-foreground"
            >
              <input
                type="checkbox"
                name="remember"
                value="1"
                checked={remember}
                class="h-4 w-4 rounded border-border bg-background text-brand-primary"
              />
              <span>Simpan sesi di perangkat ini</span>
            </label>

            <Button type="submit" class="h-11 px-4 shadow-none">
              <i class="fas fa-right-to-bracket"></i>
              <span>Masuk ke CMOS</span>
            </Button>
          </div>
        </form>
      </Card.Content>
    </Card.Root>
  </div>
</div>
