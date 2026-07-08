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
  } = $props();

  const assetBase = "/images/figma-taling";

  const departments = [
    {
      slug: "bph",
      name: "Badan Pengurus Harian (BPH/BPI)",
      desc: "Inti koordinasi organisasi dan manajemen.",
    },
    {
      slug: "personalia",
      name: "Personalia",
      desc: "Harmonisasi internal dan pengawasan kinerja.",
    },
    {
      slug: "risprof",
      name: "Riset & Keprofesian",
      desc: "Pengembangan kompetensi ilmiah dan karir.",
    },
    {
      slug: "kwu",
      name: "Kewirausahaan",
      desc: "Kemitraan bisnis dan pendanaan mandiri.",
    },
    {
      slug: "psdm",
      name: "PSDM",
      desc: "Pengembangan bakat minat dan kaderisasi mahasiswa.",
    },
    {
      slug: "dagri",
      name: "Dalam Negeri",
      desc: "Sinergi komunitas dan internal engagement.",
    },
    {
      slug: "hublu",
      name: "Hubungan Luar",
      desc: "Relasi eksternal dan ekspansi kemitraan.",
    },
    {
      slug: "kesma",
      name: "Kesejahteraan Mahasiswa",
      desc: "Advokasi akademik dan layanan mahasiswa.",
    },
    {
      slug: "medfo",
      name: "Media & Informasi",
      desc: "Kreatif visual dan komunikasi publik.",
    },
    {
      slug: "kaderisasi",
      name: "Kaderisasi",
      desc: "Pembinaan karakter dan kepemimpinan.",
    },
  ];

  const tentangUrl = "/tentang";
  const resolvedNavigation = [
    { href: homeUrl, label: "Beranda" },
    { href: "/departemen", label: "Departemen" },
    { href: "/kompetisi", label: "Kompetisi" },
    { href: "/tentang", label: "Tentang Kami" },
  ];

  const navigationItems = resolvedNavigation.map((item) => {
    if (item.label === "Departemen") {
      return {
        ...item,
        children: [
          { href: "/departemen", label: "Departemen" },
          { href: "/departemen/overview", label: "Detail departemen" },
          { href: tentangUrl, label: "Sejarah himpunan" },
        ],
      };
    }
    if (item.label === "Kompetisi") {
      return {
        ...item,
        children: [
          { href: item.href, label: "Kompetisi" },
          { href: "/kristal", label: "Kristal" },
        ],
      };
    }
    return item;
  });
</script>

<svelte:head>
  <title>Detail Departemen - Sentra Sinergi</title>
  <meta
    name="description"
    content="Overview seluruh departemen Kabinet Sentra Sinergi HIMATEKKOM ITS."
  />
</svelte:head>

<div
  class="min-h-screen w-full bg-white font-['Josefin_Sans',sans-serif] text-[#222]"
  use:inertiaEnhance
>
  <Navbar {homeUrl} {loginUrl} {navigationItems} />

  <main
    class="relative isolate min-h-[calc(100vh-74px)] overflow-hidden bg-[#222222] pt-24 pb-32 text-white"
  >
    <!-- Atmosphere Background -->
    <div
      class="absolute inset-0 -z-10 bg-gradient-to-br from-[#222222] to-[#111111]"
    >
      <picture class="contents">
        <source srcset={`${assetBase}/hero-bg.avif`} type="image/avif" />
        <source srcset={`${assetBase}/hero-bg.webp`} type="image/webp" />
        <img
          class="absolute inset-0 h-full w-full object-cover opacity-[0.08] mix-blend-screen"
          src={`${assetBase}/hero-bg.png`}
          alt=""
          loading="eager"
        />
      </picture>
      <picture class="contents">
        <source srcset={`${assetBase}/botanical.avif`} type="image/avif" />
        <source srcset={`${assetBase}/botanical.webp`} type="image/webp" />
        <img
          class="animate-slow-pan absolute -top-[10%] -left-[10%] h-[150%] w-[150%] max-w-none object-cover opacity-[0.05] mix-blend-screen"
          src={`${assetBase}/botanical.png`}
          alt=""
          loading="eager"
        />
      </picture>
    </div>

    <!-- Floating Ornaments -->
    <img
      src={`${assetBase}/star-large.svg`}
      alt=""
      class="pointer-events-none absolute top-0 left-0 opacity-20 mix-blend-screen"
      width="320"
      height="301"
    />

    <div class="relative z-10 mx-auto max-w-5xl px-6 lg:px-12">
      <!-- Title Section -->
      <div class="mt-10 mb-20 text-center">
        <span
          class="text-xs font-bold tracking-[0.15em] text-[#ff7a1a] uppercase"
          >KABINET SENTRA SINERGI</span
        >
        <h1
          class="mt-4 font-['Playfair_Display','Playfair_Display',serif] text-5xl font-bold tracking-tight text-[#ffd344] md:text-6xl"
        >
          Detail Departemen
        </h1>
        <div class="mx-auto mt-8 h-[2px] w-16 bg-[#ffd344]"></div>
        <p
          class="mx-auto mt-8 max-w-2xl text-lg leading-relaxed font-light text-white md:text-xl"
        >
          Eksplorasi struktur dan fokus utama dari setiap departemen yang
          menjadi motor penggerak Kabinet Sentra Sinergi.
        </p>
      </div>

      <!-- Department List -->
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {#each departments as dept}
          <a
            href={`/departemen/${dept.slug}`}
            class="group relative flex flex-col justify-center border border-white/10 bg-white/5 p-8 transition-all duration-300 hover:border-[#ffd344] hover:bg-white/10"
          >
            <!-- Hover accent line -->
            <div
              class="absolute top-0 left-0 h-full w-[3px] scale-y-0 bg-[#ffd344] opacity-0 transition-all duration-300 group-hover:scale-y-100 group-hover:opacity-100"
            ></div>

            <h2
              class="mb-2 font-['Playfair_Display',serif] text-2xl font-bold text-white transition-colors group-hover:text-[#ffd344]"
            >
              {dept.name}
            </h2>
            <p class="text-sm font-light text-white/70">
              {dept.desc}
            </p>

            <!-- Minimal arrow indicator -->
            <div
              class="mt-6 flex items-center gap-2 text-xs font-semibold tracking-widest text-[#ff7a1a] uppercase transition-all group-hover:translate-x-2 group-hover:text-[#ffd344]"
            >
              Lihat Detail
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M14 5l7 7m0 0l-7 7m7-7H3"
                />
              </svg>
            </div>
          </a>
        {/each}
      </div>
    </div>
  </main>

  <Footer
    {infoUrl}
    {acaraUrl}
    departemenUrl="/departemen"
    {tentangUrl}
    kompetisiUrl="/kompetisi"
    {organizationName}
  />
</div>
