<script>
  import { inertiaEnhance } from "$lib/inertia-enhance.js";
  import brandLogo from "../images/logokabinet.png?enhanced&w=96;192";
  import heroPhoto from "../images/himatekkom.jpg?enhanced&w=720;1200;1600";
  import OptimizedImage from "./components/OptimizedImage.svelte";
  import Navbar from "./components/landing/Navbar.svelte";
  import Footer from "./components/landing/Footer.svelte";
  import { Button } from "$lib/components/ui/button/index.js";

  let {
    pageTitle = "Segera hadir",
    organizationName = "HIMATEKKOM ITS",
    description = "Halaman ini sedang kami siapkan. Nantikan pembaruannya.",
    homeUrl = "/",
    loginUrl = "/login",
    infoUrl = "/informasi",
    acaraUrl = "/acara",
    seo = null,
  } = $props();

  const documentTitle = $derived(
    seo?.title || `${pageTitle} - ${organizationName}`,
  );
</script>

<svelte:head>
  <title>{documentTitle}</title>
  <meta name="description" content={seo?.description || description} />
  {#if seo?.canonical}
    <link rel="canonical" href={seo.canonical} />
  {/if}
  <meta name="robots" content="noindex" />
</svelte:head>

<div use:inertiaEnhance class="coming-page">
  <Navbar {homeUrl} {loginUrl} />

  <main id="main-content" tabindex="-1" class="outline-none">
    <section class="coming-hero" aria-labelledby="coming-heading">
      <div class="coming-hero-media" aria-hidden="true">
        <OptimizedImage
          src={heroPhoto}
          alt=""
          class="coming-hero-img"
          loading="eager"
          decoding="async"
          fetchpriority="high"
          sizes="100vw"
        />
        <div class="coming-hero-scrim"></div>
      </div>

      <span class="coming-star coming-star-left" aria-hidden="true"></span>
      <span class="coming-star coming-star-right" aria-hidden="true"></span>

      <div class="coming-hero-center">
        <p>{pageTitle}</p>
        <h1 id="coming-heading">Segera hadir</h1>
        <div class="coming-hero-rule" aria-hidden="true"></div>
      </div>
    </section>

    <section class="coming-note">
      <div class="coming-section-shell coming-note-grid">
        <p class="coming-outline">Dalam Proses</p>
        <div>
          <h2>{pageTitle}</h2>
          <p>{description}</p>
          <a href={homeUrl} class="coming-section-link">
            <i class="fas fa-arrow-left text-[18px]" aria-hidden="true"></i>
            Kembali ke beranda
          </a>
        </div>
      </div>
    </section>
  </main>

  <Footer
    {infoUrl}
    {acaraUrl}
    departemenUrl="/departemen"
    tentangUrl="/tentang"
    kompetisiUrl="/kompetisi"
    {organizationName}
  />
</div>

<style>
  .coming-page {
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
    font-family: var(--taling-font-sans);
  }

  .coming-section-shell {
    width: min(1248px, calc(100% - 3rem));
    margin-inline: auto;
  }

  .coming-section-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
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

  .coming-hero {
    position: relative;
    display: grid;
    min-height: 720px;
    overflow: hidden;
    color: var(--taling-white);
  }

  .coming-hero-media,
  .coming-hero-scrim {
    position: absolute;
    inset: 0;
  }

  .coming-hero-media :global(.coming-hero-img),
  .coming-hero-media :global(img) {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .coming-hero-scrim {
    background:
      linear-gradient(180deg, rgba(42, 0, 120, 0.12), rgba(18, 8, 34, 0.76)),
      color-mix(in srgb, var(--taling-purple) 44%, transparent);
  }

  .coming-hero-center {
    position: relative;
    z-index: 2;
    display: grid;
    place-items: center;
    align-self: center;
    width: min(900px, calc(100% - 2rem));
    margin: 0 auto;
    text-align: center;
  }

  .coming-hero-center p {
    margin: 0 0 1rem;
    color: var(--taling-yellow);
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .coming-hero h1 {
    margin: 0;
    color: var(--taling-white);
    font-family: var(--taling-font-serif);
    font-size: clamp(3.3rem, 7vw, 6.8rem);
    line-height: 1;
    text-shadow: 0 0 22px rgba(255, 253, 248, 0.72);
  }

  .coming-hero-rule {
    width: min(534px, 68vw);
    height: 22px;
    margin-top: 1.9rem;
    background: var(--taling-yellow);
    box-shadow: 0 0 24px rgba(255, 211, 68, 0.52);
  }

  .coming-star {
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

  .coming-star-left {
    top: -112px;
    left: 16%;
    width: 355px;
  }

  .coming-star-right {
    right: -118px;
    bottom: 42px;
    width: 270px;
  }

  .coming-note {
    padding: 6rem 0 7rem;
    background: linear-gradient(160deg, #f6bb2f 0%, #ff8a1f 54%, #c85910 100%);
    color: #1f1520;
  }

  .coming-note-grid {
    display: grid;
    grid-template-columns: minmax(260px, 0.9fr) minmax(300px, 1.1fr);
    gap: clamp(2.5rem, 7vw, 6rem);
    align-items: center;
  }

  .coming-outline {
    margin: 0;
    color: transparent;
    font-family: var(--taling-font-sans);
    font-size: clamp(3rem, 7vw, 6.6rem);
    font-weight: 900;
    letter-spacing: 0.05em;
    line-height: 0.9;
    text-transform: uppercase;
    -webkit-text-stroke: 2px var(--taling-purple);
  }

  .coming-note h2 {
    margin: 0 0 1.2rem;
    color: #1f1520;
    font-family: var(--taling-font-serif);
    font-size: clamp(2.3rem, 4.5vw, 4rem);
    line-height: 1;
  }

  .coming-note p:not(.coming-outline) {
    margin: 0 0 1.5rem;
    max-width: 60ch;
    color: #241422;
    font-weight: 800;
    line-height: 1.48;
  }

  .coming-section-link {
    width: fit-content;
    gap: 0.5rem;
    background: var(--taling-purple);
    color: var(--taling-white);
  }

  @media (max-width: 819px) {
    .coming-section-shell {
      width: min(100% - 1.5rem, 620px);
    }

    .coming-hero {
      min-height: 640px;
    }

    .coming-star-left {
      left: -88px;
      width: 255px;
    }

    .coming-star-right {
      right: -104px;
      bottom: 86px;
      width: 210px;
    }

    .coming-note {
      padding: 4.5rem 0 5rem;
    }

    .coming-note-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
