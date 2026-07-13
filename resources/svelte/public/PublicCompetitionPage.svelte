<script>
  import Navbar from "../components/landing/Navbar.svelte";
  import Footer from "../components/landing/Footer.svelte";
  import { inertiaEnhance } from "../lib/inertia-enhance.js";
  import { fly, fade } from "svelte/transition";
  import { cubicOut } from "svelte/easing";

  let {
    organizationName = "HIMATEKKOM ITS",
    homeUrl = "/",
    loginUrl = "/login",
    infoUrl = "/informasi",
    acaraUrl = "/acara",
    departemenUrl = "/departemen",
    kompetisiUrl = "/kompetisi",
    tentangUrl = "/tentang",
    competitions = [],
    seo = null,
  } = $props();

  const pageTitle = $derived(`Kompetisi & Ajang - ${organizationName}`);
  const pageDescription = $derived(
    `Temukan daftar kompetisi, lomba, dan ajang prestasi akademik & non-akademik eksternal terupdate untuk mahasiswa Teknik Komputer dan umum.`,
  );

  // Filter and search states
  let searchQuery = $state("");
  let selectedMonth = $state("All");
  let selectedStatus = $state("All");

  // Track currently active/selected competition for the side-drawer panel
  let activeCompetition = $state(null);

  const availableMonths = $derived([
    "All",
    ...new Set(competitions.map((c) => c.month).filter(Boolean)),
  ]);

  const filteredCompetitions = $derived(
    competitions.filter((comp) => {
      const query = searchQuery.trim().toLowerCase();
      const matchesSearch =
        query === "" ||
        comp.name.toLowerCase().includes(query) ||
        comp.organizer.toLowerCase().includes(query) ||
        comp.description.toLowerCase().includes(query);

      const matchesMonth =
        selectedMonth === "All" || comp.month === selectedMonth;
      const matchesStatus =
        selectedStatus === "All" ||
        comp.status.toLowerCase() === selectedStatus.toLowerCase();

      return matchesSearch && matchesMonth && matchesStatus;
    }),
  );

  // Stop background body scrolling when the drawer panel is open
  $effect(() => {
    if (activeCompetition) {
      document.body.classList.add("overflow-hidden");
    } else {
      document.body.classList.remove("overflow-hidden");
    }
    return () => {
      document.body.classList.remove("overflow-hidden");
    };
  });
</script>

<svelte:head>
  <title>{seo?.title || pageTitle}</title>
  <meta name="description" content={seo?.description || pageDescription} />
  <meta property="og:title" content={seo?.title || pageTitle} />
  <meta
    property="og:description"
    content={seo?.description || pageDescription}
  />
  <meta property="og:type" content="website" />
</svelte:head>

<div
  use:inertiaEnhance
  class="min-h-screen w-full bg-slate-50 font-['Plus_Jakarta_Sans',sans-serif] text-[#222]"
>
  <Navbar {homeUrl} {loginUrl} />

  <main id="main-content" tabindex="-1" class="outline-none">
    <!-- Hero Header -->
    <section
      class="relative isolate flex min-h-[380px] w-full items-center justify-center overflow-hidden bg-gradient-to-br from-[#2a0078] via-[#40008c] to-[#5d0077] text-white"
    >
      <!-- Background pattern -->
      <div
        class="pointer-events-none absolute inset-0 opacity-15 mix-blend-overlay"
        style="background-image: url('/images/figma-taling/hero-bg.png'); background-size: cover; background-position: center;"
      ></div>

      <div class="relative z-10 mx-auto max-w-4xl px-6 py-20 text-center">
        <span
          class="rounded-full bg-yellow-400/20 px-4 py-1.5 text-xs font-bold tracking-wider text-[#ffd344] uppercase"
        >
          Info Prestasi & Ajang
        </span>
        <h1
          class="mt-4 font-['The_Seasons'] text-4xl font-bold text-white md:text-6xl"
        >
          Kompetisi & Lomba
        </h1>
        <p
          class="mx-auto mt-4 max-w-2xl text-white/80 text-sm md:text-base leading-relaxed"
        >
          Pusat informasi kompetisi terkurasi untuk membantu mahasiswa
          HIMATEKKOM ITS menyalurkan minat, bakat, serta meraih prestasi di
          tingkat regional, nasional, dan internasional.
        </p>
      </div>
    </section>

    <!-- Search & Filters Toolbar -->
    <section
      class="sticky top-0 z-30 w-full border-b border-gray-200 bg-white/90 py-5 backdrop-blur-md shadow-sm"
    >
      <div class="mx-auto max-w-7xl px-6">
        <div
          class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
        >
          <!-- Search Bar -->
          <div class="relative flex-1 max-w-md">
            <span
              class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400"
            >
              <i class="fas fa-search text-[16px]"></i>
            </span>
            <input
              type="text"
              placeholder="Cari nama lomba, organizer, atau deskripsi..."
              bind:value={searchQuery}
              class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-10 pr-4 text-sm font-medium transition-all duration-200 placeholder:text-gray-400 hover:border-gray-300 focus:border-[#2a0078] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#2a0078]"
            />
          </div>

          <!-- Filters -->
          <div class="flex flex-wrap items-center gap-4 text-xs md:text-sm">
            <!-- Filter Month -->
            <div class="flex items-center gap-2">
              <span
                class="font-bold text-gray-500 uppercase tracking-wider text-[11px]"
                >Bulan:</span
              >
              <div class="flex flex-wrap gap-1.5">
                {#each availableMonths as m}
                  <button
                    type="button"
                    onclick={() => (selectedMonth = m)}
                    class="rounded-lg px-3.5 py-1.5 font-bold transition-all duration-150 {selectedMonth ===
                    m
                      ? 'bg-[#2a0078] text-white shadow-sm'
                      : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}"
                  >
                    {m === "All" ? "Semua" : m}
                  </button>
                {/each}
              </div>
            </div>

            <!-- Filter Status -->
            <div class="flex items-center gap-2">
              <span
                class="font-bold text-gray-500 uppercase tracking-wider text-[11px]"
                >Status:</span
              >
              <div class="flex gap-1.5">
                {#each ["All", "Open", "Closed"] as s}
                  <button
                    type="button"
                    onclick={() => (selectedStatus = s)}
                    class="rounded-lg px-3.5 py-1.5 font-bold transition-all duration-150 {selectedStatus ===
                    s
                      ? 'bg-[#2a0078] text-white shadow-sm'
                      : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}"
                  >
                    {s === "All" ? "Semua" : s === "Open" ? "Buka" : "Tutup"}
                  </button>
                {/each}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Competitions Grid Section -->
    <section class="mx-auto max-w-7xl px-6 py-12">
      {#if filteredCompetitions.length === 0}
        <div
          class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white py-20 text-center"
        >
          <div
            class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-500"
          >
            <i class="fas fa-folder-open text-2xl"></i>
          </div>
          <h3 class="mt-4 text-lg font-bold text-gray-900">
            Tidak ada kompetisi ditemukan
          </h3>
          <p class="mt-2 max-w-md text-sm text-gray-500">
            Silakan coba ubah filter pencarian Anda atau periksa kembali bulan
            lain.
          </p>
        </div>
      {:else}
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
          {#each filteredCompetitions as comp (comp.month + "-" + comp.no)}
            <!-- Card Wrapper -->
            <!-- svelte-ignore a11y_click_events_have_key_events -->
            <!-- svelte-ignore a11y_no_static_element_interactions -->
            <div
              onclick={() => (activeCompetition = comp)}
              class="group flex cursor-pointer flex-col justify-between overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-md"
            >
              <div class="p-6">
                <!-- Badges -->
                <div class="flex items-center justify-between gap-2">
                  <span
                    class="rounded-full bg-indigo-50 px-3 py-1 text-[11px] font-bold text-[#2a0078]"
                  >
                    {comp.month}
                  </span>

                  <span
                    class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider {comp.status.toLowerCase() ===
                    'open'
                      ? 'bg-emerald-50 text-emerald-700'
                      : 'bg-rose-50 text-rose-700'}"
                  >
                    {comp.status}
                  </span>
                </div>

                <!-- Title & Organizer -->
                <h3
                  class="mt-4 line-clamp-2 text-lg font-extrabold text-gray-900 leading-snug group-hover:text-[#2a0078] transition-colors"
                >
                  {comp.name}
                </h3>
                <p class="mt-1 text-xs font-bold text-gray-500">
                  Oleh: <span class="text-[#ff7a1a]">{comp.organizer}</span>
                </p>

                <!-- Description Preview (Truncated) -->
                <div class="mt-4 border-t border-gray-100 pt-4">
                  <p
                    class="line-clamp-3 text-sm leading-relaxed text-gray-600 whitespace-pre-line"
                  >
                    {comp.description}
                  </p>
                </div>

                <!-- Timeline / Schedule -->
                {#if comp.timeline}
                  <div class="mt-4 rounded-xl bg-gray-50 p-4 text-xs">
                    <div class="flex items-start gap-2 text-gray-700">
                      <span class="mt-0.5 text-gray-400">
                        <i class="far fa-calendar-alt text-[14px]"></i>
                      </span>
                      <div class="line-clamp-2 font-medium leading-relaxed">
                        {comp.timeline}
                      </div>
                    </div>
                  </div>
                {/if}
              </div>

              <!-- Action Trigger -->
              <div class="border-t border-gray-100 bg-gray-50/50 p-6">
                <button
                  type="button"
                  onclick={(e) => {
                    e.stopPropagation();
                    activeCompetition = comp;
                  }}
                  class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#2a0078] to-[#40008c] py-2.5 text-center text-xs font-bold tracking-wider text-white uppercase shadow-sm transition-all duration-150 hover:opacity-95 active:scale-95"
                >
                  <span>Detail Kompetisi</span>
                  <i class="fas fa-arrow-right text-[11px]"></i>
                </button>
              </div>
            </div>
          {/each}
        </div>
      {/if}
    </section>

    <!-- Slide-over Expandable Drawer Overlay -->
    {#if activeCompetition}
      <!-- Backdrop overlay -->
      <!-- svelte-ignore a11y_click_events_have_key_events -->
      <!-- svelte-ignore a11y_no_static_element_interactions -->
      <div
        transition:fade={{ duration: 200 }}
        class="fixed inset-0 z-40 bg-black/40 backdrop-blur-xs"
        onclick={() => (activeCompetition = null)}
      ></div>

      <!-- Drawer Panel container -->
      <div
        transition:fly={{ x: 500, duration: 300, easing: cubicOut }}
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-xl flex-col bg-white shadow-2xl border-l border-gray-100"
      >
        <!-- Header -->
        <div
          class="flex items-center justify-between border-b border-gray-100 p-6"
        >
          <div class="flex items-center gap-2.5">
            <span
              class="rounded-full bg-indigo-50 px-3.5 py-1 text-xs font-bold text-[#2a0078]"
            >
              {activeCompetition.month}
            </span>
            <span
              class="rounded-full px-2.5 py-1 text-xs font-bold uppercase tracking-wider {activeCompetition.status.toLowerCase() ===
              'open'
                ? 'bg-emerald-50 text-emerald-700'
                : 'bg-rose-50 text-rose-700'}"
            >
              {activeCompetition.status}
            </span>
          </div>

          <button
            type="button"
            onclick={() => (activeCompetition = null)}
            class="flex h-10 w-10 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-900 transition-colors"
            aria-label="Tutup Detail"
          >
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>

        <!-- Scrollable content area -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8">
          <h2
            class="font-['The_Seasons'] text-2xl font-bold text-gray-900 leading-snug md:text-3xl"
          >
            {activeCompetition.name}
          </h2>
          <p class="mt-2 text-xs font-bold text-gray-500 md:text-sm">
            Oleh: <span class="text-[#ff7a1a]"
              >{activeCompetition.organizer}</span
            >
          </p>

          <!-- Timeline section -->
          {#if activeCompetition.timeline}
            <div class="mt-8 border-t border-gray-100 pt-6">
              <h3
                class="text-xs font-bold tracking-widest text-gray-400 uppercase"
              >
                Jadwal & Timeline
              </h3>
              <div
                class="mt-3 rounded-2xl bg-gray-50 p-5 border border-gray-100"
              >
                <div class="flex items-start gap-3 text-gray-700">
                  <span class="mt-0.5 text-[#ff7a1a]">
                    <i class="far fa-calendar-alt text-lg"></i>
                  </span>
                  <div
                    class="whitespace-pre-line text-sm font-semibold leading-relaxed"
                  >
                    {activeCompetition.timeline}
                  </div>
                </div>
              </div>
            </div>
          {/if}

          <!-- Description section -->
          <div class="mt-8 border-t border-gray-100 pt-6">
            <h3
              class="text-xs font-bold tracking-widest text-gray-400 uppercase"
            >
              Deskripsi Lengkap
            </h3>
            <p
              class="mt-4 text-sm leading-relaxed text-gray-600 whitespace-pre-line md:text-base"
            >
              {activeCompetition.description}
            </p>
          </div>
        </div>

        <!-- Footer link controls -->
        {#if (activeCompetition.links && activeCompetition.links.length > 0) || activeCompetition.link || activeCompetition.qr_code_link}
          <div
            class="border-t border-gray-100 bg-gray-50/50 p-6 flex flex-col gap-3"
          >
            {#if activeCompetition.links && activeCompetition.links.length > 0}
              {#each activeCompetition.links as linkItem}
                <a
                  href={linkItem.url}
                  target="_blank"
                  rel="noopener noreferrer"
                  class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#2a0078] to-[#40008c] py-3.5 text-center text-sm font-bold tracking-wider text-white uppercase shadow-md transition-all duration-150 hover:opacity-95 active:scale-95"
                >
                  <span>{linkItem.label || "Daftar / Ikuti Lomba"}</span>
                  <i class="fas fa-external-link-alt text-[12px]"></i>
                </a>
              {/each}
            {:else if activeCompetition.link}
              <a
                href={activeCompetition.link}
                target="_blank"
                rel="noopener noreferrer"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#2a0078] to-[#40008c] py-3.5 text-center text-sm font-bold tracking-wider text-white uppercase shadow-md transition-all duration-150 hover:opacity-95 active:scale-95"
              >
                <span>Daftar / Ikuti Lomba</span>
                <i class="fas fa-external-link-alt text-[12px]"></i>
              </a>
            {/if}

            {#if activeCompetition.qr_code_link}
              <a
                href={activeCompetition.qr_code_link}
                target="_blank"
                rel="noopener noreferrer"
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white py-3.5 text-center text-sm font-bold tracking-wider text-gray-700 uppercase transition-all duration-150 hover:bg-gray-50 active:scale-95"
              >
                <span>Panduan / Link Pendukung</span>
                <i class="fas fa-qrcode text-[12px]"></i>
              </a>
            {/if}
          </div>
        {/if}
      </div>
    {/if}

    <!-- Closing CTA Section -->
    <section
      class="w-full bg-[#2a0078] py-20 text-center text-white relative overflow-hidden"
    >
      <!-- Background pattern -->
      <div
        class="pointer-events-none absolute inset-0 opacity-10 mix-blend-overlay"
        style="background-image: url('/images/figma-taling/hero-bg.png');"
      ></div>

      <div class="relative z-10 mx-auto max-w-3xl px-6">
        <h2
          class="font-['The_Seasons'] text-3xl font-bold text-white md:text-5xl"
        >
          Ingin Berkolaborasi / Mengajukan Lomba?
        </h2>
        <p class="mx-auto mt-4 max-w-xl text-white/80 text-sm md:text-base">
          Punya informasi kompetisi yang ingin dibagikan ke mahasiswa Teknik
          Komputer? Hubungi kami melalui BPH atau Departemen terkait untuk
          mempublikasikannya di sini.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
          <a
            href={departemenUrl}
            class="rounded-full bg-gradient-to-r from-[#ff7a1a] to-[#ffd344] px-8 py-3.5 text-sm font-bold tracking-wider text-[#2a0078] uppercase shadow-lg transition-transform hover:scale-105"
          >
            Hubungi Departemen
          </a>
        </div>
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
  />
</div>
