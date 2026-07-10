<script>
  import Navbar from "../components/landing/Navbar.svelte";
  import Footer from "../components/landing/Footer.svelte";
  import { inertiaEnhance } from "../lib/inertia-enhance.js";

  let {
    organizationName = "HIMATEKKOM ITS",
    homeUrl = "/",
    loginUrl = "/login",
    infoUrl = "/informasi",
    acaraUrl = "/acara",
    departemenUrl = "/departemen",
    kompetisiUrl = "/kompetisi",
    tentangUrl = "/tentang",
    seo = null,
    visionMission = null,
    history = null,
    footer = null,
  } = $props();

  const pageTitle = `Tentang Kami - ${organizationName}`;
  const pageDescription = `Profil, visi misi, dan sejarah Kabinet Sentra Sinergi ${organizationName}.`;

  const footerDescription =
    footer?.description ??
    "Kabinet Sentra Sinergi, Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember.";
</script>

<svelte:head>
  <title>{seo?.title || pageTitle}</title>
  <meta name="description" content={seo?.description || pageDescription} />
  {#if seo?.canonical}
    <link rel="canonical" href={seo.canonical} />
  {/if}
  <meta property="og:type" content={seo?.type || "website"} />
  <meta property="og:title" content={seo?.title || pageTitle} />
  <meta property="og:description" content={seo?.description || pageDescription} />
</svelte:head>

<div
  use:inertiaEnhance
  class="min-h-screen w-full bg-white font-['Plus_Jakarta_Sans',sans-serif] text-[#222]"
>
  <Navbar {homeUrl} {loginUrl} />

  <main id="main-content" tabindex="-1" class="outline-none">
    <!-- Hero -->
    <section
      class="relative isolate flex min-h-[420px] w-full items-center justify-center overflow-hidden bg-gradient-to-br from-[#5d0077] to-[#2a0078] text-white"
    >
      <div
        class="pointer-events-none absolute inset-0 opacity-20 mix-blend-overlay"
        style="background-image: url('/images/figma-taling/hero-bg.png'); background-size: cover; background-position: center;"
      ></div>
      <div class="relative z-10 mx-auto max-w-3xl px-6 py-24 text-center">
        <span
          class="text-sm font-bold tracking-wider text-[#ffd344] uppercase"
        >
          {organizationName}
        </span>
        <h1
          class="mt-3 font-['The_Seasons'] text-5xl font-bold text-white md:text-7xl"
        >
          Tentang Kami
        </h1>
        <p class="mx-auto mt-5 max-w-xl text-white/80">
          Mengenal lebih dekat visi, misi, dan perjalanan Kabinet Sentra
          Sinergi HIMATEKKOM ITS.
        </p>
      </div>
    </section>

    <!-- Visi & Misi -->
    {#if visionMission}
      <section class="w-full bg-white py-20">
        <div class="mx-auto max-w-5xl px-6">
          <span
            class="text-xs font-bold tracking-widest text-[#ff7a1a] uppercase"
          >
            {visionMission.eyebrow ?? "Profil Kabinet"}
          </span>
          <h2
            class="mt-2 font-['The_Seasons'] text-3xl font-bold text-[#2a0078] md:text-5xl"
          >
            {visionMission.title ?? "Visi & Misi"}
          </h2>

          <div class="mt-12 grid gap-10 md:grid-cols-2">
            <div class="rounded-2xl border border-[#2a0078]/10 bg-[#fff4d3]/40 p-8">
              <h3 class="text-sm font-bold tracking-wider text-[#2a0078] uppercase">
                {visionMission.visionLabel ?? "Visi"}
              </h3>
              <p class="mt-4 leading-relaxed text-[#222]">
                {visionMission.vision}
              </p>
            </div>

            <div class="rounded-2xl border border-[#2a0078]/10 bg-white p-8 shadow-sm">
              <h3 class="text-sm font-bold tracking-wider text-[#2a0078] uppercase">
                {visionMission.missionLabel ?? "Misi"}
              </h3>
              <ul class="mt-4 flex flex-col gap-4">
                {#each visionMission.missionItems ?? [] as item}
                  <li class="flex items-start gap-3">
                    <span
                      class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full bg-gradient-to-tr from-[#ff7a1a] to-[#ffd344]"
                    ></span>
                    <span class="leading-relaxed text-[#222]">{item}</span>
                  </li>
                {/each}
              </ul>
            </div>
          </div>
        </div>
      </section>
    {/if}

    <!-- Sejarah -->
    {#if history}
      <section class="relative w-full overflow-hidden bg-[#2a0078] py-20 text-white">
        <div
          class="pointer-events-none absolute inset-0 bg-cover bg-center opacity-10 mix-blend-overlay"
          style="background-image: url('/images/figma-taling/hero-bg.png');"
        ></div>

        <div class="relative z-10 mx-auto max-w-4xl px-6">
          <span class="text-xs font-bold tracking-widest text-[#ffd344] uppercase">
            {history.eyebrow ?? "Perjalanan Kami"}
          </span>
          <h2 class="mt-2 font-['The_Seasons'] text-3xl font-bold text-white md:text-5xl">
            {history.title ?? "Sejarah Kabinet"}
          </h2>
          {#if history.intro}
            <p class="mt-4 max-w-2xl text-white/80">{history.intro}</p>
          {/if}

          <div class="mt-12 flex flex-col gap-8 border-l border-white/15 pl-8">
            {#each history.timeline ?? [] as item}
              <div class="relative">
                <span
                  class="absolute -left-[calc(2rem+5px)] top-1.5 h-2.5 w-2.5 rounded-full bg-[#ffd344]"
                ></span>
                <span class="text-sm font-bold tracking-wider text-[#ffd344]">
                  {item.year}
                </span>
                <h3 class="mt-1 text-xl font-bold text-white">{item.title}</h3>
                <p class="mt-2 max-w-2xl leading-relaxed text-white/75">
                  {item.description}
                </p>
              </div>
            {/each}
          </div>
        </div>
      </section>
    {/if}

    <!-- Closing CTA -->
    <section class="w-full bg-white py-16 text-center">
      <div class="mx-auto max-w-2xl px-6">
        <h2 class="font-['The_Seasons'] text-2xl font-bold text-[#2a0078] md:text-4xl">
          Kenali lebih jauh HIMATEKKOM ITS
        </h2>
        <p class="mt-4 text-[#444]">
          Jelajahi departemen dan program kerja kabinet untuk melihat
          bagaimana kami bekerja.
        </p>
        <a 
          href={departemenUrl}
          class="mt-6 inline-flex items-center justify-center rounded-full bg-gradient-to-r from-[#ff7a1a] to-[#ffd344] px-8 py-3 text-sm font-bold tracking-wide text-[#2a0078] uppercase shadow-lg"
        >
          Lihat Departemen
        </a>
      </div>
    </section>
  </main>

  <Footer
    {infoUrl}
    {acaraUrl}
    {departemenUrl}
    {tentangUrl}
    {kompetisiUrl}
    {organizationName}
    {footerDescription}
  />
</div>