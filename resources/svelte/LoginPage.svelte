<script>
  import { onMount } from "svelte";
  import { ArrowLeft, LogIn, ShieldCheck } from "lucide-svelte";
  import { Button } from "$lib/components/ui/button/index.js";
  import * as Card from "$lib/components/ui/card/index.js";
  import { Input } from "$lib/components/ui/input/index.js";
  import { Label } from "$lib/components/ui/label/index.js";
  import brandLogo from "../images/logokabinet.png?enhanced&w=96;192";
  import heroPhoto from "../images/himatekkom.jpg?enhanced&w=720;1200;1600";
  import OptimizedImage from "./components/OptimizedImage.svelte";

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

  onMount(() => {
    document.documentElement.setAttribute("data-theme", "public");
    document.documentElement.setAttribute("data-brand", themeColor || "purple");

    if (themeVariables && typeof themeVariables === "object") {
      Object.entries(themeVariables).forEach(([token, value]) => {
        if (typeof token !== "string" || typeof value !== "string") {
          return;
        }

        document.documentElement.style.setProperty(`--${token}`, value);
      });
    }

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
</script>

<svelte:head>
  <title>Login - {appName}</title>
  <meta
    name="description"
    content={`Masuk ke ${appName} untuk melanjutkan operasional organisasi.`}
  />
</svelte:head>

<div class="public-login">
  <header class="login-header">
    <div class="login-header-inner">
      <a href={homeUrl} class="login-brand" aria-label={appName}>
        <OptimizedImage
          src={brandLogo}
          alt=""
          class="login-brand-mark"
          loading="eager"
          decoding="async"
          fetchpriority="high"
          sizes="76px"
        />
        <span>{appName}</span>
      </a>
      <Button href={homeUrl} class="login-home-link">Beranda</Button>
    </div>
  </header>

  <main id="main-content" tabindex="-1" class="outline-none">
    <section class="login-hero" aria-labelledby="login-heading">
      <div class="login-hero-media" aria-hidden="true">
        <OptimizedImage
          src={heroPhoto}
          alt=""
          class="login-hero-img"
          loading="eager"
          decoding="async"
          fetchpriority="high"
          sizes="100vw"
        />
        <div class="login-hero-scrim"></div>
      </div>
      <span class="login-star login-star-left" aria-hidden="true"></span>
      <span class="login-star login-star-right" aria-hidden="true"></span>

      <div class="login-section-shell login-hero-grid">
        <div class="login-copy">
          <p>Portal Pengurus</p>
          <h1 id="login-heading">Masuk ke CMOS</h1>
          <div class="login-hero-rule" aria-hidden="true"></div>
          <span>
            Area kerja internal HIMATEKKOM ITS untuk menjaga agenda, publikasi,
            dan dokumentasi kabinet tetap tertata.
          </span>
        </div>

        <Card.Root class="login-card">
          <Card.Header class="login-card-header">
            <a
              href={homeUrl}
              data-native="true"
              class="login-back"
              aria-label="Kembali ke beranda"
            >
              <ArrowLeft size={17} />
              <span>Kembali</span>
            </a>

            <div class="login-form-title">
              <ShieldCheck size={22} />
              <div>
                <Card.Title>Masuk pengurus</Card.Title>
                <Card.Description>
                  Gunakan email organisasi yang sudah terdaftar.
                </Card.Description>
              </div>
            </div>
          </Card.Header>

          <Card.Content class="grid gap-5 pt-5">
            {#if alertMessage}
              <div
                class={`login-alert ${alertType === "info" ? "login-alert-info" : "login-alert-error"}`}
                role="alert"
              >
                {alertMessage}
              </div>
            {/if}

            <form method="POST" action={loginUrl} class="grid gap-5">
              <input type="hidden" name="_token" value={csrfToken} />

              <div class="login-fields">
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
                    class="login-input h-12 px-3"
                    aria-invalid={emailError ? "true" : "false"}
                    aria-describedby={emailError
                      ? "login-email-error"
                      : undefined}
                    placeholder="nama@organisasi.com"
                  />
                  {#if emailError}
                    <div id="login-email-error" class="login-error">
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
                    class="login-input h-12 px-3"
                    aria-invalid={passwordError ? "true" : "false"}
                    aria-describedby={passwordError
                      ? "login-password-error"
                      : undefined}
                    placeholder="Masukkan password"
                  />
                  {#if passwordError}
                    <div id="login-password-error" class="login-error">
                      {passwordError}
                    </div>
                  {/if}
                </div>
              </div>

              <div class="login-actions">
                <label class="login-remember">
                  <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    checked={remember}
                  />
                  <span>Simpan sesi di perangkat ini</span>
                </label>

                <Button type="submit" class="login-submit">
                  <LogIn size={17} />
                  <span>Masuk ke CMOS</span>
                </Button>
              </div>
            </form>
          </Card.Content>
        </Card.Root>
      </div>
    </section>
  </main>
</div>

<style>
  .public-login {
    --taling-yellow: #ffd344;
    --taling-purple: #2a0078;
    --taling-purple-deep: #5d0077;
    --taling-orange: #ff7a1a;
    --taling-ink: #222222;
    --taling-white: #fffdf8;
    --taling-cream: #fff4d3;
    min-height: 100vh;
    overflow: clip;
    background: var(--taling-white);
    color: var(--taling-ink);
    font-family: var(
      --taling-font-sans,
      "Public Sans",
      ui-sans-serif,
      system-ui
    );
  }

  .login-header {
    position: sticky;
    top: 0;
    z-index: 30;
    background: #fffdf8;
  }

  .login-header-inner,
  .login-section-shell {
    width: min(1248px, calc(100% - 3rem));
    margin-inline: auto;
  }

  .login-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 84px;
    gap: 2rem;
  }

  .login-brand {
    display: inline-flex;
    align-items: center;
    color: inherit;
    text-decoration: none;
  }

  .login-brand span {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
  }

  .login-brand :global(.login-brand-mark),
  .login-brand :global(img) {
    width: 76px;
    height: auto;
  }

  :global(.login-home-link) {
    display: inline-flex;
    width: auto;
    height: auto;
    min-height: 36px;
    padding: 0.55rem 1.9rem;
    border-radius: 999px;
    background: linear-gradient(
      90deg,
      var(--taling-orange),
      var(--taling-yellow)
    );
    color: var(--taling-purple);
    font-weight: 800;
    text-decoration: none;
  }

  .login-hero {
    position: relative;
    display: grid;
    min-height: calc(100vh - 84px);
    overflow: hidden;
    padding: 6rem 0 7rem;
    color: var(--taling-white);
  }

  .login-hero-media,
  .login-hero-scrim {
    position: absolute;
    inset: 0;
  }

  .login-hero-media :global(.login-hero-img),
  .login-hero-media :global(img) {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .login-hero-scrim {
    background:
      linear-gradient(180deg, rgba(42, 0, 120, 0.1), rgba(18, 8, 34, 0.82)),
      color-mix(in srgb, var(--taling-purple) 48%, transparent);
  }

  .login-hero-grid {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(24rem, 30rem);
    gap: clamp(2.5rem, 7vw, 6rem);
    align-items: center;
  }

  .login-copy {
    display: grid;
    gap: 1.35rem;
    max-width: 760px;
  }

  .login-copy p {
    margin: 0;
    color: var(--taling-yellow);
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .login-copy h1 {
    margin: 0;
    color: var(--taling-white);
    font-family: var(--taling-font-serif, "The Seasons", Georgia, serif);
    font-size: clamp(3.3rem, 7vw, 6.8rem);
    line-height: 1;
    text-shadow: 0 0 22px rgba(255, 253, 248, 0.72);
  }

  .login-copy span {
    max-width: 56ch;
    color: var(--taling-white);
    font-weight: 800;
    line-height: 1.45;
  }

  .login-hero-rule {
    width: min(534px, 68vw);
    height: 22px;
    background: var(--taling-yellow);
    box-shadow: 0 0 24px rgba(255, 211, 68, 0.52);
  }

  .login-card {
    border: 0;
    border-radius: 0;
    background: color-mix(
      in srgb,
      var(--taling-white) 94%,
      var(--taling-yellow)
    );
    color: var(--taling-ink);
    box-shadow: 18px 18px 0
      color-mix(in srgb, var(--taling-yellow) 52%, transparent);
  }

  .login-card-header {
    gap: 1.25rem;
    border-bottom: 2px solid
      color-mix(in srgb, var(--taling-purple) 24%, transparent);
    padding-bottom: 1.2rem;
  }

  .login-back {
    display: inline-flex;
    width: fit-content;
    align-items: center;
    gap: 0.45rem;
    color: var(--taling-purple);
    font-size: 0.9rem;
    font-weight: 900;
    text-decoration: none;
  }

  .login-form-title {
    display: flex;
    gap: 0.85rem;
    align-items: flex-start;
    color: var(--taling-purple);
  }

  .login-form-title :global([data-slot="card-title"]) {
    color: var(--taling-purple);
    font-family: var(--taling-font-serif, "The Seasons", Georgia, serif);
    font-size: clamp(1.8rem, 4vw, 2.4rem);
    letter-spacing: -0.035em;
  }

  .login-form-title :global([data-slot="card-description"]) {
    color: color-mix(in srgb, var(--taling-ink) 70%, transparent);
    line-height: 1.6;
  }

  .login-alert,
  .login-fields,
  .login-actions {
    border-top: 2px solid
      color-mix(in srgb, var(--taling-purple) 24%, transparent);
    background: transparent;
  }

  .login-alert {
    padding: 0.8rem 0;
    font-size: 0.92rem;
    font-weight: 800;
  }

  .login-alert-info {
    color: var(--taling-purple);
  }

  .login-alert-error,
  .login-error {
    color: oklch(0.46 0.18 28);
  }

  .login-fields {
    display: grid;
    gap: 1rem;
    padding-top: 1rem;
  }

  .login-fields :global(label) {
    color: var(--taling-purple);
    font-weight: 900;
  }

  :global(.login-input) {
    border: 2px solid color-mix(in srgb, var(--taling-purple) 42%, transparent) !important;
    border-radius: 0 !important;
    background: var(--taling-white) !important;
    color: var(--taling-ink) !important;
  }

  .login-actions {
    display: grid;
    gap: 1rem;
    padding-top: 1rem;
  }

  .login-remember {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: color-mix(in srgb, var(--taling-ink) 74%, transparent);
    font-size: 0.92rem;
    font-weight: 800;
  }

  .login-remember input {
    width: 1rem;
    height: 1rem;
    accent-color: var(--taling-purple);
  }

  :global(.login-submit) {
    display: inline-flex !important;
    min-height: 3rem !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.55rem !important;
    border-radius: 999px !important;
    background: linear-gradient(
      90deg,
      var(--taling-orange),
      var(--taling-yellow)
    ) !important;
    color: var(--taling-purple) !important;
    font-weight: 900 !important;
  }

  .login-star {
    position: absolute;
    z-index: 1;
    aspect-ratio: 1;
    background: var(--taling-yellow);
    clip-path: polygon(
      50% 0,
      59% 35%,
      98% 35%,
      66% 56%,
      78% 96%,
      50% 70%,
      22% 96%,
      34% 56%,
      2% 35%,
      41% 35%
    );
    opacity: 0.72;
  }

  .login-star-left {
    top: -112px;
    left: 16%;
    width: 355px;
  }

  .login-star-right {
    right: -118px;
    bottom: 42px;
    width: 270px;
  }

  @media (max-width: 819px) {
    .login-header-inner,
    .login-section-shell {
      width: min(100% - 1.5rem, 620px);
    }

    .login-header-inner {
      min-height: 72px;
    }

    .login-brand :global(.login-brand-mark),
    .login-brand :global(img) {
      width: 58px;
    }

    .login-hero {
      padding: 4.25rem 0 5rem;
    }

    .login-hero-grid {
      grid-template-columns: 1fr;
    }

    .login-star-left {
      left: -88px;
      width: 255px;
    }

    .login-star-right {
      right: -104px;
      bottom: 86px;
      width: 210px;
    }
  }
</style>
