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

  const departmentGroups = [
    {
      title: "Inti & Manajemen",
      departments: [
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
      ],
    },
    {
      title: "Pengembangan Mahasiswa",
      departments: [
        {
          slug: "kaderisasi",
          name: "Kaderisasi",
          desc: "Pembinaan karakter dan kepemimpinan.",
        },
        {
          slug: "psdm",
          name: "PSDM",
          desc: "Pengembangan bakat minat dan kaderisasi mahasiswa.",
        },
        {
          slug: "risprof",
          name: "Riset & Keprofesian",
          desc: "Pengembangan kompetensi ilmiah dan karir.",
        },
      ],
    },
    {
      title: "Kesejahteraan & Relasi",
      departments: [
        {
          slug: "kesma",
          name: "Kesejahteraan Mahasiswa",
          desc: "Advokasi akademik dan layanan mahasiswa.",
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
      ],
    },
    {
      title: "Kreatif & Kewirausahaan",
      departments: [
        {
          slug: "medfo",
          name: "Media & Informasi",
          desc: "Kreatif visual dan komunikasi publik.",
        },
        {
          slug: "kwu",
          name: "Kewirausahaan",
          desc: "Kemitraan bisnis dan pendanaan mandiri.",
        },
      ],
    },
  ];

  const departemenUrl = "/departemen";
  const kompetisiUrl = "/kompetisi";
  const tentangUrl = "/tentang";
</script>

<svelte:head>
  <title>Detail Departemen - Sentra Sinergi</title>
  <meta
    name="description"
    content="Overview seluruh departemen Kabinet Sentra Sinergi HIMATEKKOM ITS."
  />
</svelte:head>

<div
  class="min-h-screen w-full bg-white font-['Plus_Jakarta_Sans',sans-serif] text-[#222]"
  use:inertiaEnhance
>
  <Navbar {homeUrl} {loginUrl} />

  <main class="font-['Josefin_Sans',sans-serif] relative isolate min-h-[calc(100vh-74px)] overflow-hidden bg-[#222222] pt-24 pb-32 text-white">
    <!-- Atmosphere Background -->
    <div
      class="pointer-events-none absolute inset-0 -z-10 overflow-hidden bg-gradient-to-br from-[#222222] to-[#111111]"
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
      <div class="mt-10 mb-24 max-w-prose">
        <h1 class="font-['The_Seasons','The_Seasons',serif] text-balance text-5xl font-bold tracking-tight text-[#ffd344] md:text-7xl">
          Departemen
        </h1>
        <p class="mt-8 text-pretty text-lg leading-relaxed text-white/70 md:text-xl">
          Eksplorasi struktur dan fokus utama dari setiap departemen yang
          menjadi motor penggerak Kabinet Sentra Sinergi.
        </p>
      </div>

      <div class="flex flex-col gap-24">
        {#each departmentGroups as group}
          <section class="grid grid-cols-1 items-start gap-12 lg:grid-cols-12 lg:gap-8">
            <div class="lg:col-span-4 xl:col-span-3">
              <h2 class="text-balance text-2xl font-bold tracking-tight text-white/90 md:text-3xl">
                {group.title}
              </h2>
              <div class="mt-6 h-px w-12 bg-[#ff7a1a]"></div>
            </div>
            
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:col-span-8 xl:col-span-9">
              {#each group.departments as dept}
                <a
                  href={`/departemen/${dept.slug}`}
                  class="group flex flex-col justify-between rounded-xl bg-white/[0.03] p-6 shadow-sm ring-1 ring-white/10 backdrop-blur-sm transition-all duration-300 ease-out hover:bg-white/[0.08] hover:shadow-lg hover:ring-white/20 active:scale-[0.98] active:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#ffd344]"
                >
                  <div>
                    <h3
                      class="mb-3 text-balance text-xl font-bold tracking-tight text-white transition-colors group-hover:text-[#ffd344]"
                    >
                      {dept.name}
                    </h3>
                    <p class="text-pretty text-sm leading-relaxed text-white/60">
                      {dept.desc}
                    </p>
                  </div>
                  <div class="mt-8 flex items-center text-sm font-semibold tracking-wide text-[#ff7a1a] transition-colors group-hover:text-[#ffd344]">
                    <span>Profil Lengkap &rarr;</span>
                  </div>
                </a>
              {/each}
            </div>
          </section>
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
