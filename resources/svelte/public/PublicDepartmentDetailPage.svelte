<script>
  import Navbar from "../components/landing/Navbar.svelte";
  import Footer from "../components/landing/Footer.svelte";
  import { onMount } from "svelte";
  import { router } from "@inertiajs/svelte";
  import { fade, scale } from "svelte/transition";
  import { inertiaEnhance } from "../lib/inertia-enhance.js";
  import { departmentTeams } from "../../js/mtt-data.js";

  let {
    organizationName = "HIMATEKKOM ITS",
    homeUrl = "/",
    loginUrl = "/login",
    infoUrl = "/informasi",
    acaraUrl = "/acara",
    selectedSlug = null,
  } = $props();

  const assetBase = "/images/figma-taling";

  // Spacing offsets for timing of large and small stars
  const largeStarDelay = "0s";
  const smallStarDelay = "-2s";
  const botanicalDelay = "-5s";

  // Fallback metadata for department descriptions and focus areas
  const departmentInfoMap = {
    personalia: {
      name: "Personalia",
      description:
        "Departemen yang berfokus pada harmonisasi internal fungsionaris, pengawasan kinerja organisasi, dan peningkatan keaktifan serta kesejahteraan internal pengurus HIMATEKKOM.",
      focus: ["Harmonisasi Internal", "Pengawasan Kinerja", "Engagement Staff"],
    },
    risprof: {
      name: "Riset dan Keprofesian (RISPROF)",
      description:
        "Departemen yang memfasilitasi riset teknologi, pembinaan kompetensi ilmiah, serta penyiapan karir fungsionaris Teknik Komputer untuk dunia industri maupun akademis.",
      focus: ["Riset Teknologi", "Kompetensi Ilmiah", "Career Preparation"],
    },
    kwu: {
      name: "Kewirausahaan (KWU)",
      description:
        "Departemen yang mewadahi minat kewirausahaan mahasiswa, mencari pendanaan mandiri untuk himpunan, serta membangun kemitraan bisnis strategis dengan eksternal.",
      focus: ["Entrepreneurship", "Pendanaan Mandiri", "Business Partnership"],
    },
    psdm: {
      name: "Pengembangan Sumber Daya Mahasiswa (PSDM)",
      description:
        "Departemen yang merancang dan melaksanakan kaderisasi fungsionaris, pembinaan karakter kepemimpinan, serta pengembangan bakat minat mahasiswa Teknik Komputer.",
      focus: [
        "Pengembangan Soft Skills",
        "Minat dan Bakat",
        "Pembinaan Anggota",
      ],
    },
    dagri: {
      name: "Dalam Negeri (DAGRI)",
      description:
        "Departemen yang membangun hubungan erat dengan internal angkatan, menampung aspirasi mahasiswa, serta memperkuat rasa kekeluargaan di lingkungan Teknik Komputer.",
      focus: ["Sinergi Komunitas", "Internal Engagement", "Apresiasi Anggota"],
    },
    bph: {
      name: "Badan Pengurus Harian (BPH / BPI)",
      description:
        "Inti koordinasi organisasi yang bertanggung jawab atas kesekretariatan, administrasi, manajemen keuangan, serta penentuan arah strategis kebijakan himpunan.",
      focus: [
        "Administrasi Organisasi",
        "Manajemen Keuangan",
        "Kesekretariatan",
      ],
    },
    hublu: {
      name: "Hubungan Luar (HUBLU)",
      description:
        "Departemen yang menjalin relasi eksternal dengan alumni, birokrasi kampus, serta pihak industri guna memperluas networking dan ekspansi kemitraan strategis.",
      focus: ["Relasi Alumni", "Kompetensi Eksternal", "Networking Strategis"],
    },
    kesma: {
      name: "Kesejahteraan Mahasiswa (KESMA)",
      description:
        "Departemen yang memberikan advokasi akademik, kesejahteraan finansial (beasiswa), serta bantuan layanan mahasiswa Teknik Komputer dalam menunjang perkuliahan.",
      focus: [
        "Advokasi Akademik",
        "Kesejahteraan Finansial",
        "Layanan Mahasiswa",
      ],
    },
    medfo: {
      name: "Media dan Informasi (MEDFO)",
      description:
        "Departemen kreatif yang mempublikasikan karya visual, mengelola saluran komunikasi publik himpunan, serta menyampaikan informasi aktual secara berkala.",
      focus: [
        "Kreatif & Desain Visual",
        "Kanal Komunikasi Publik",
        "Publikasi Informasi",
      ],
    },
    kaderisasi: {
      name: "Kaderisasi",
      description:
        "Departemen khusus yang merumuskan konsep pembinaan karakter fungsionaris dan mahasiswa baru, memantau nilai kedisiplinan, serta mengawal masa transisi organisasi.",
      focus: [
        "Kaderisasi Fungsionaris",
        "Latihan Kepemimpinan",
        "Evaluasi Karakter",
      ],
    },
  };

  // Map slugs to figma data ID (e.g. bph -> bpi)
  const mappedSlug = $derived(selectedSlug === "bph" ? "bpi" : selectedSlug);
  const deptInfo = $derived(
    departmentInfoMap[selectedSlug] || {
      name: selectedSlug ? selectedSlug.toUpperCase() : "Detail Departemen",
      description: "Informasi detail departemen Kabinet Sentra Sinergi.",
      focus: ["Kolaborasi", "Ekspansi", "Optimalisasi"],
    },
  );

  const teamData = $derived(departmentTeams[mappedSlug] || { members: [] });

  // Separate leaders (Kadep/Sekdep/Kabiro) from staff for visual hierarchy
  const leaders = $derived(
    teamData.members.filter(
      (m) =>
        m.role.includes("Ketua") ||
        m.role.includes("Kepala") ||
        m.role.includes("Sekretaris") ||
        m.role.includes("Bendahara") ||
        m.role.includes("Wakil"),
    ),
  );

  const staff = $derived(teamData.members.filter((m) => !leaders.includes(m)));

  const tentangUrl = "/tentang";
  const resolvedNavigation = $derived([
    { href: homeUrl, label: "Beranda" },
    { href: "/departemen", label: "Departemen" },
    { href: "/kompetisi", label: "Kompetisi" },
    { href: "/tentang", label: "Tentang Kami" },
  ]);

  const navigationItems = $derived(
    resolvedNavigation.map((item) => {
      if (item.label === "Departemen") {
        return {
          ...item,
          children: [
            { href: "/departemen", label: "Orbit departemen" },
            {
              href: `/departemen/${selectedSlug || "bph"}`,
              label: "Detail departemen",
            },
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
    }),
  );

  function getInitials(name) {
    if (!name) return "SS";
    const parts = name.split(" ");
    if (parts.length >= 2) {
      return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
  }

  // Brand gradients for avatar placeholding
  const avatarGradients = [
    "from-[#ffd344] to-[#ff7a1a]",
    "from-[#4e00de] to-[#5d0077]",
    "from-[#ff7a1a] to-[#5d0077]",
    "from-[#ffd344] to-[#4e00de]",
  ];

  function getAvatarGradient(index) {
    return avatarGradients[index % avatarGradients.length];
  }

  function handleBack() {
    router.visit("/departemen");
  }
</script>

<svelte:head>
  <title>{deptInfo.name} - Sentra Sinergi</title>
  <meta name="description" content={deptInfo.description} />
</svelte:head>

<div
  class="min-h-screen w-full bg-white font-['Josefin_Sans',sans-serif] text-[#222]"
  use:inertiaEnhance
>
  <Navbar {homeUrl} {loginUrl} {navigationItems} />

  <main
    class="relative isolate min-h-[calc(100vh-74px)] overflow-hidden bg-[#222222] pb-32 text-white"
  >
    <!-- Atmosphere Background - Combination B uses Dark Charcoal -->
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
      <!-- Deep subtle botanical overlay to match brand mood -->
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
      class="star-large animate-float-large pointer-events-none opacity-20 mix-blend-screen drop-shadow-2xl"
      width="320"
      height="301"
      style="position: absolute; animation-delay: {largeStarDelay};"
    />

    <!-- Main Container -->
    <div class="relative z-10 mx-auto max-w-7xl px-6 pt-16 md:pt-24 lg:px-12">
      <!-- Back Navigation Button -->
      <button
        onclick={handleBack}
        class="group mb-12 flex items-center gap-2 text-sm font-semibold tracking-wide text-[#e2bb44] transition-colors hover:text-white"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="2"
          stroke="currentColor"
          class="h-4 w-4 transform transition-transform group-hover:-translate-x-1"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M10 19l-7-7m0 0l7-7m-7 7h18"
          />
        </svg>
        Kembali ke Orbit
      </button>

      <!-- Department Introduction Section -->
      <div class="mb-32 grid grid-cols-1 items-start gap-16 lg:grid-cols-12">
        <div class="lg:col-span-7 xl:col-span-8">
          <span
            class="text-xs font-bold tracking-[0.15em] text-[#ff7a1a] uppercase"
            >PROFIL DEPARTEMEN</span
          >
          <h1
            class="mt-4 font-['Playfair_Display','Playfair_Display',serif] text-5xl leading-[1.1] font-bold tracking-tight text-[#ffd344] md:text-7xl"
            style="text-wrap: balance;"
          >
            {deptInfo.name}
          </h1>

          <!-- Minimal accent line -->
          <div class="mt-8 mb-8 h-[2px] w-16 bg-[#ffd344]"></div>

          <p
            class="max-w-2xl text-lg leading-relaxed font-light text-white md:text-2xl"
            style="text-wrap: pretty; text-align: justify;"
          >
            {deptInfo.description}
          </p>
        </div>

        <!-- Key Focus Areas Box - Dark Charcoal bg per Combination B -->
        <div class="lg:col-span-5 lg:pt-20 xl:col-span-4">
          <h2
            class="mb-6 text-xs font-bold tracking-widest text-[#ffd344] uppercase"
          >
            Fokus Utama
          </h2>
          <div class="flex flex-col gap-6 border-l-2 border-[#e2bb44] pl-6">
            {#each deptInfo.focus as focusItem}
              <div class="group relative">
                <span
                  class="absolute top-[0.45rem] -left-[29px] h-2 w-2 rounded-full bg-[#2a0078] transition-colors group-hover:bg-[#ff7a1a]"
                ></span>
                <span class="text-base font-semibold text-white"
                  >{focusItem}</span
                >
              </div>
            {/each}
          </div>
        </div>
      </div>

      <!-- Meet The Team Section -->
      <div class="border-t border-[#2a0078] pt-24">
        <div
          class="mb-20 flex flex-col items-center justify-center text-center"
        >
          <span
            class="text-xs font-bold tracking-widest text-[#ff7a1a] uppercase"
            >#OKE Sentra Sinergi</span
          >
          <h2
            class="mt-3 font-['Playfair_Display','Playfair_Display',serif] text-4xl font-bold text-[#ffd344] md:text-5xl"
          >
            Struktur Pengurus
          </h2>
        </div>

        {#if teamData.members.length === 0}
          <div class="py-12 text-center">
            <p class="text-lg font-light text-white/50">
              Data struktur kepengurusan departemen belum tersedia.
            </p>
          </div>
        {:else}
          <!-- Leaders Section -->
          {#if leaders.length > 0}
            <div class="mb-24">
              <h3
                class="mb-10 text-sm font-semibold tracking-wider text-[#e2bb44] uppercase"
              >
                Badan Pengurus Harian
              </h3>
              <!-- Editorial layout for leaders -->
              <div
                class="grid grid-cols-1 gap-x-12 gap-y-16 sm:grid-cols-2 md:grid-cols-3"
              >
                {#each leaders as leader, idx}
                  <div class="group flex flex-col items-start">
                    <!-- Nightingale (#2a0078) accent circle -->
                    <div
                      class="mb-6 flex h-32 w-32 items-center justify-center overflow-hidden rounded-full bg-[#2a0078] transition-all duration-300 group-hover:bg-[#5d0077]"
                    >
                      <span
                        class="font-['Playfair_Display'] text-4xl font-extrabold text-[#ffd344]"
                      >
                        {getInitials(leader.name)}
                      </span>
                    </div>
                    <div>
                      <h4
                        class="mb-1 text-xl leading-tight font-bold text-white"
                      >
                        {leader.name}
                      </h4>
                      <p
                        class="text-sm font-medium tracking-wide text-[#ff7a1a] uppercase"
                      >
                        {leader.role}
                      </p>
                    </div>
                  </div>
                {/each}
              </div>
            </div>
          {/if}

          <!-- Staff Section -->
          {#if staff.length > 0}
            <div>
              <h3
                class="mb-10 text-sm font-semibold tracking-wider text-[#e2bb44] uppercase"
              >
                Fungsionaris & Staff
              </h3>
              <!-- Clean minimal grid for staff -->
              <div
                class="grid grid-cols-2 gap-x-8 gap-y-12 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5"
              >
                {#each staff as member, idx}
                  <div class="group flex flex-col items-start">
                    <div
                      class="mb-4 flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-[#2a0078] transition-colors duration-300 group-hover:bg-[#5d0077]"
                    >
                      <span
                        class="font-['Playfair_Display'] text-xl font-bold text-white opacity-90"
                      >
                        {getInitials(member.name)}
                      </span>
                    </div>
                    <div>
                      <h4
                        class="mb-1 text-base leading-tight font-semibold text-white"
                      >
                        {member.name}
                      </h4>
                      <p
                        class="text-xs font-medium tracking-wide text-[#ff7a1a] uppercase"
                      >
                        {member.role}
                      </p>
                    </div>
                  </div>
                {/each}
              </div>
            </div>
          {/if}
        {/if}
      </div>
    </div>
  </main>

  <Footer
    {infoUrl}
    {acaraUrl}
    departemenUrl="/departemen"
    tentangUrl="/tentang"
    {organizationName}
  />
</div>

<style>
  /* Star Animations delay & placement identical to layout specs */
  .star-large {
    top: -60px;
    left: -90px;
    width: 200px;
    height: 188px;
  }
  @media (min-width: 768px) {
    .star-large {
      top: -100px;
      left: -150px;
      width: 320px;
      height: 301px;
    }
  }

  .star-small {
    top: 320px;
    right: -80px;
    width: 150px;
    height: 161px;
  }
  @media (min-width: 768px) {
    .star-small {
      top: 380px;
      right: -130px;
      width: 240px;
      height: 258px;
    }
  }

  .hero-glow-wrapper {
    position: relative;
    width: 100%;
    max-width: 300px;
    height: 22px;
  }

  .animate-slow-pan {
    animation: slowPan 25s linear infinite;
  }

  .animate-float-large {
    animation: floatLarge 8s ease-in-out infinite;
  }

  .animate-float-small {
    animation: floatSmall 10s ease-in-out infinite;
  }

  @keyframes slowPan {
    0%,
    100% {
      transform: translate(0, 0) scale(1);
    }
    50% {
      transform: translate(2%, 2%) scale(1.05);
    }
  }

  @keyframes floatLarge {
    0%,
    100% {
      transform: translateY(0px) rotate(5deg);
    }
    50% {
      transform: translateY(-15px) rotate(7deg);
    }
  }

  @keyframes floatSmall {
    0%,
    100% {
      transform: translateY(0px) rotate(8deg);
    }
    50% {
      transform: translateY(12px) rotate(6deg);
    }
  }
</style>
