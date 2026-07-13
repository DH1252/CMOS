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
    staffGraphics = [],
  } = $props();

  const assetBase = "/images/figma-taling";

  // Spacing offsets for timing of large and small stars
  const largeStarDelay = "0s";
  const smallStarDelay = "-2s";
  const botanicalDelay = "-5s";

  let isDescriptionExpanded = $state(false);
  let galleryHeight = $state(400);
  let naturalWidths = $state({});
  let naturalHeights = $state({});

  let sliderRef;
  let isDown = false;
  let startX = 0;
  let scrollLeft = 0;

  let isIntersecting = $state(false);
  let scrollDirection = 1;
  let isAutoScrollActive = $state(true);
  let currentScrollLeft = 0;
  let lastInteractionTime = 0;
  let highlightPausedUntil = 0;

  function registerInteraction() {
    lastInteractionTime = Date.now();
  }

  onMount(() => {
    if (typeof window === "undefined" || !sliderRef) return;

    const saved = localStorage.getItem("himatekkom_auto_scroll");
    if (saved !== null) {
      isAutoScrollActive = saved === "true";
    }

    currentScrollLeft = sliderRef.scrollLeft;

    // Set up observer to scroll only when in center of viewport
    const margin =
      window.innerWidth < 768 ? "-20% 0px -20% 0px" : "-40% 0px -40% 0px";
    const observer = new IntersectionObserver(
      ([entry]) => {
        isIntersecting = entry.isIntersecting;
      },
      {
        rootMargin: margin,
        threshold: 0,
      },
    );
    observer.observe(sliderRef);

    // Touch event listeners to pause auto scroll on mobile swiping
    const handleTouch = () => {
      registerInteraction();
      currentScrollLeft = sliderRef.scrollLeft;
    };
    sliderRef.addEventListener("touchstart", handleTouch, { passive: true });
    sliderRef.addEventListener("touchmove", handleTouch, { passive: true });

    let animationId;

    function step() {
      if (!sliderRef) return;

      const timeSinceLastInteraction = Date.now() - lastInteractionTime;
      const isTemporarilyPaused = timeSinceLastInteraction < 1000;
      const isHighlightPaused = Date.now() < highlightPausedUntil;

      if (
        isIntersecting &&
        !isDown &&
        isAutoScrollActive &&
        !isTemporarilyPaused &&
        !isHighlightPaused
      ) {
        const maxScroll = sliderRef.scrollWidth - sliderRef.clientWidth;

        if (maxScroll > 0) {
          // Synchronize accumulator if user scrolls manually (e.g. touch drag)
          if (Math.abs(currentScrollLeft - sliderRef.scrollLeft) > 5) {
            currentScrollLeft = sliderRef.scrollLeft;
          }

          if (currentScrollLeft >= maxScroll - 1) {
            scrollDirection = -1;
          } else if (currentScrollLeft <= 1) {
            scrollDirection = 1;
          }

          const baseSpeed = window.innerWidth < 768 ? 0.15 : 0.45;
          const overlayCount = staffList.length;

          let totalNaturalWidth = 0;
          if (staffGraphics) {
            staffGraphics.forEach((g, i) => {
              totalNaturalWidth += naturalWidths[i] || 400;
            });
          }

          const averageSpacing =
            overlayCount > 0 ? totalNaturalWidth / overlayCount : 400;
          const spacingMultiplier = Math.min(
            1.6,
            Math.max(0.5, averageSpacing / 250),
          );
          const scrollSpeed = baseSpeed * spacingMultiplier;

          // Accumulate fractional scroll speed
          currentScrollLeft += scrollDirection * scrollSpeed;
          currentScrollLeft = Math.max(
            0,
            Math.min(maxScroll, currentScrollLeft),
          );

          // Set rounded integer to DOM
          sliderRef.scrollLeft = Math.round(currentScrollLeft);
        }
      }
      animationId = requestAnimationFrame(step);
    }

    animationId = requestAnimationFrame(step);

    return () => {
      observer.disconnect();
      cancelAnimationFrame(animationId);
      if (sliderRef) {
        sliderRef.removeEventListener("touchstart", handleTouch);
        sliderRef.removeEventListener("touchmove", handleTouch);
      }
    };
  });

  function toggleAutoScroll() {
    isAutoScrollActive = !isAutoScrollActive;
    if (typeof window !== "undefined") {
      localStorage.setItem(
        "himatekkom_auto_scroll",
        String(isAutoScrollActive),
      );
    }
  }

  let minNaturalHeight = $derived.by(() => {
    let min = Infinity;
    if (staffGraphics) {
      for (let i = 0; i < staffGraphics.length; i++) {
        if (naturalHeights[i] && naturalHeights[i] < min) {
          min = naturalHeights[i];
        }
      }
    }
    return min === Infinity ? 400 : min;
  });

  let globalScale = $derived(galleryHeight / minNaturalHeight);

  function getVerticalOffset(gIndex, graphic, gScale) {
    if (!naturalHeights[gIndex]) return 0;
    const scaledHeight = naturalHeights[gIndex] * gScale;
    const extraHeight = scaledHeight - galleryHeight;
    if (extraHeight <= 0) return 0;

    let cy = 50;
    if (graphic.overlays && graphic.overlays.length > 0) {
      let sumY = 0;
      for (let o of graphic.overlays) {
        sumY += o.y !== undefined ? o.y : 50;
      }
      cy = sumY / graphic.overlays.length;
      cy = Math.max(0, cy - 15);
    }

    let topPx = galleryHeight / 2 - (cy / 100) * scaledHeight;
    topPx = Math.max(-extraHeight, Math.min(0, topPx));

    return topPx;
  }

  function handleMouseDown(e) {
    if (!sliderRef) return;
    isDown = true;
    registerInteraction();
    sliderRef.classList.add("cursor-grabbing");
    sliderRef.classList.remove("cursor-grab");
    startX = e.pageX - sliderRef.offsetLeft;
    scrollLeft = sliderRef.scrollLeft;
  }
  function handleMouseLeave() {
    if (!sliderRef) return;
    isDown = false;
    sliderRef.classList.remove("cursor-grabbing");
    sliderRef.classList.add("cursor-grab");
  }
  function handleMouseUp() {
    if (!sliderRef) return;
    isDown = false;
    sliderRef.classList.remove("cursor-grabbing");
    sliderRef.classList.add("cursor-grab");
  }
  function handleMouseMove(e) {
    if (!isDown || !sliderRef) return;
    e.preventDefault();
    registerInteraction();
    const x = e.pageX - sliderRef.offsetLeft;
    const walk = (x - startX) * 2;
    sliderRef.scrollLeft = scrollLeft - walk;
  }

  function scrollGallery(direction) {
    registerInteraction();
    if (sliderRef && typeof window !== "undefined") {
      sliderRef.scrollBy({
        left: direction * (window.innerWidth * 0.5),
        behavior: "smooth",
      });
    }
  }

  let slideRefs = [];

  let staffList = $derived.by(() => {
    let list = [];
    if (staffGraphics) {
      staffGraphics.forEach((graphic, graphicIndex) => {
        if (graphic.overlays) {
          graphic.overlays.forEach((overlay) => {
            const fullName = overlay.name || "";
            const match = fullName.match(/(.*?)\s+(CE\s*\d+)$/i);
            let name = fullName;
            let batch = null;
            if (match) {
              name = match[1];
              batch = match[2].toUpperCase().replace(/\s+/, "");
            }
            list.push({
              name,
              batch,
              role: overlay.role || "",
              picture: overlay.picture || null,
              graphicIndex,
            });
          });
        }
      });
    }
    return list;
  });

  let activeStaffName = $state(null);

  function scrollToStaff(staff) {
    activeStaffName = staff.name;
    highlightPausedUntil = Date.now() + 5000;
    const graphicIndex = staff.graphicIndex;

    if (slideRefs[graphicIndex] && sliderRef) {
      const slide = slideRefs[graphicIndex];
      // Calculate offset relative to slider
      const scrollLeft = slide.offsetLeft - sliderRef.offsetLeft;

      // Center the slide slightly if possible
      const centerOffset = (sliderRef.clientWidth - slide.clientWidth) / 2;
      const targetScroll = Math.max(0, scrollLeft - centerOffset);

      sliderRef.scrollTo({
        left: targetScroll,
        behavior: "smooth",
      });

      // Scroll the entire page to center the gallery
      sliderRef.scrollIntoView({
        behavior: "smooth",
        block: "center",
      });
    }
  }

  // Fallback metadata for department descriptions and focus areas
  const departmentInfoMap = {
    personalia: {
      name: "Personalia",
      description:
        "Biro yang bertanggung jawab atas manajemen sumber daya fungsionaris. Personalia berfokus pada penyusunan standar pengembangan fungsionaris, pemeliharaan motivasi, serta evaluasi kinerja guna memastikan setiap fungsionaris berkontribusi secara optimal dalam iklim organisasi.",
      focus: [
        "Manajemen Sumber Daya",
        "Pemeliharaan Motivasi",
        "Evaluasi Kinerja Staf",
      ],
    },
    risprof: {
      name: "Riset dan Keprofesian (RISPROF)",
      description:
        "Departemen yang mendorong anggota dalam mengembangkan kemampuan riset dan memperdalam keprofesian di bidang Teknik Komputer melalui program riset, inovasi, serta pengembangan karir untuk memperluas wawasan akademik dan profesional.",
      focus: [
        "Budaya Riset Keilmiahan",
        "Pengembangan Karir",
        "Literasi Isu Keprofesian",
      ],
    },
    kwu: {
      name: "Kewirausahaan (KWU)",
      description:
        "Departemen yang bertanggung jawab mengelola pendanaan mandiri HIMATEKKOM ITS melalui unit usaha kreatif dan meningkatkan wawasan seputar kewirausahaan bagi mahasiswa Teknik Komputer.",
      focus: [
        "Pendanaan Mandiri",
        "Unit Usaha Kreatif",
        "Edukasi Kewirausahaan",
      ],
    },
    psdm: {
      name: "Pengembangan Sumber Daya Mahasiswa (PSDM)",
      description:
        "Departemen yang merancang dan melaksanakan kaderisasi fungsionaris, pembinaan karakter kepemimpinan, serta peningkatan kompetensi anggota melalui pelatihan, workshop, dan pengembangan bakat minat.",
      focus: [
        "Pelatihan & Upgrading",
        "Minat Bakat Mahasiswa",
        "Kaderisasi Fungsionaris",
      ],
    },
    dagri: {
      name: "Dalam Negeri (DAGRI)",
      description:
        "Departemen yang bertanggung jawab meningkatkan keharmonisan dan hubungan internal antar anggota HIMATEKKOM ITS, menampung aspirasi, serta memfasilitasi kegiatan kekeluargaan dan minat bakat.",
      focus: [
        "Hubungan Internal Anggota",
        "Sinergi Lintas Angkatan",
        "Kekeluargaan Himpunan",
      ],
    },
    bph: {
      name: "Badan Pengurus Harian (BPH / BPI)",
      description:
        "Badan Pengurus Harian merupakan inti kepengurusan HIMATEKKOM ITS yang bertanggung jawab atas perumusan tata kelola organisasi, pengambilan keputusan strategis, arah gerak, serta fungsi kontrol dan koordinasi seluruh lini.",
      focus: [
        "Tata Kelola Organisasi",
        "Kebijakan & Arah Gerak",
        "Kontrol & Koordinasi",
      ],
    },
    hublu: {
      name: "Hubungan Luar (HUBLU)",
      description:
        "Departemen yang berfokus pada pengembangan dan pemeliharaan hubungan strategis dengan pihak eksternal, termasuk perusahaan, organisasi mahasiswa lain, dan alumni guna memperluas jejaring dan kemitraan.",
      focus: [
        "Relasi & Jejaring Eksternal",
        "Sinergi Alumni",
        "Kemitraan Industri",
      ],
    },
    kesma: {
      name: "Kesejahteraan Mahasiswa (KESMA)",
      description:
        "Departemen yang bertugas memberikan dukungan kesejahteraan dalam bentuk penyediaan fasilitas, layanan advokasi akademik, kesejahteraan finansial, dan penyaluran aspirasi mahasiswa Teknik Komputer.",
      focus: [
        "Advokasi & Layanan Akademik",
        "Kesejahteraan Mental & Sosial",
        "Fasilitas Pembelajaran",
      ],
    },
    medfo: {
      name: "Media dan Informasi (MEDFO)",
      description:
        "Garda terdepan dalam pengelolaan seluruh kanal komunikasi dan publikasi organisasi. Medfo bertanggung jawab memproduksi konten visual, mengelola media sosial, dan menyebarkan informasi strategis himpunan.",
      focus: [
        "Kanal Komunikasi Publik",
        "Produksi Konten Visual",
        "Jurnalistik & Media Kreatif",
      ],
    },
    kaderisasi: {
      name: "Kaderisasi (TUK)",
      description:
        "Departemen yang memiliki peran strategis dalam merumuskan konsep pembinaan, penanaman nilai karakter, dan loyalitas kader-kader HIMATEKKOM ITS melalui alur koordinasi langsung dengan Ketua Himpunan.",
      focus: [
        "Kaderisasi & Pembinaan",
        "Penanaman Nilai Karakter",
        "Regenerasi Kepemimpinan",
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
            { href: "/departemen", label: "Departemen" },
            {
              href: `/departemen/overview`,
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
    if (window.history.length > 1) {
      window.history.back();
    } else {
      router.visit("/departemen");
    }
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
        Kembali
      </button>

      <!-- Department Introduction Section -->
      <div class="mb-32 grid grid-cols-1 items-start gap-16 lg:grid-cols-12">
        <div class="lg:col-span-7 xl:col-span-8">
          <span
            class="text-xs font-bold tracking-[0.15em] text-[#ff7a1a] uppercase"
            >PROFIL DEPARTEMEN</span
          >
          <h1
            class="mt-4 font-['The_Seasons',serif] text-5xl leading-[1.1] font-bold tracking-tight text-[#ffd344] md:text-7xl"
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
          <h2
            class="mt-3 font-['The_Seasons',serif] text-4xl font-bold text-[#ffd344] md:text-5xl"
          >
            Struktur Pengurus
          </h2>
        </div>

        {#if staffGraphics && staffGraphics.length > 0}
          <!-- Control Bar for Auto Scroll -->
          <div class="mb-6 flex items-center justify-end gap-3 px-2">
            <!-- Auto Scroll Toggle -->
            <span
              class="text-xs font-semibold tracking-wider text-white/70 uppercase select-none"
            >
              Auto Scroll
            </span>
            <button
              type="button"
              class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#ff7a1a] focus:ring-offset-2 focus:ring-offset-black/50 {isAutoScrollActive
                ? 'bg-[#ff7a1a]'
                : 'bg-neutral-800'}"
              role="switch"
              aria-checked={isAutoScrollActive}
              onclick={toggleAutoScroll}
            >
              <span
                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {isAutoScrollActive
                  ? 'translate-x-5'
                  : 'translate-x-0'}"
              ></span>
            </button>
          </div>

          <div
            class="group relative mb-24 w-full overflow-hidden rounded-2xl shadow-2xl ring-1 ring-white/10"
          >
            <button
              class="absolute top-1/2 left-4 z-10 -translate-y-1/2 rounded-full border border-white/10 bg-black/50 p-3 text-white opacity-0 backdrop-blur-md transition-colors group-hover:opacity-100 hover:bg-[#ff7a1a]"
              onclick={() => scrollGallery(-1)}
              aria-label="Previous"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15 19l-7-7 7-7"
                />
              </svg>
            </button>

            <div
              bind:this={sliderRef}
              bind:clientHeight={galleryHeight}
              class="flex h-[350px] cursor-grab flex-row overflow-x-auto bg-black/20 active:cursor-grabbing md:h-[600px] [&::-webkit-scrollbar]:hidden"
              style="scrollbar-width: none;"
              role="application"
              aria-label="Galeri staff grafis"
              onmousedown={handleMouseDown}
              onmouseleave={handleMouseLeave}
              onmouseup={handleMouseUp}
              onmousemove={handleMouseMove}
            >
              {#each staffGraphics as graphic, graphicIndex}
                {@const gScale = globalScale || 1}
                {@const sWidth = (naturalWidths[graphicIndex] || 400) * gScale}
                {@const topPx = getVerticalOffset(
                  graphicIndex,
                  graphic,
                  gScale,
                )}

                <div
                  bind:this={slideRefs[graphicIndex]}
                  class="relative h-full flex-none overflow-hidden border-r border-white/5 select-none last:border-r-0"
                  style="width: {sWidth}px;"
                >
                  <div
                    class="pointer-events-none absolute left-0 w-full"
                    style="height: {(naturalHeights[graphicIndex] || 400) *
                      gScale}px; top: {topPx}px;"
                  >
                    <img
                      src={graphic.image}
                      alt="Struktur Staff"
                      class="pointer-events-none block h-full w-full max-w-none"
                      draggable="false"
                      onload={(e) => {
                        naturalWidths[graphicIndex] = e.target.naturalWidth;
                        naturalHeights[graphicIndex] = e.target.naturalHeight;
                      }}
                    />

                    {#if graphic.overlays && graphic.overlays.length > 0}
                      <div class="pointer-events-none absolute inset-0">
                        {#each graphic.overlays as overlay}
                          {@const parsedName = (() => {
                            const fullName = overlay.name || "";
                            const match =
                              fullName.match(/(.*?)\s+(CE\s*\d+)$/i);
                            if (match)
                              return {
                                name: match[1],
                                batch: match[2]
                                  .toUpperCase()
                                  .replace(/\s+/, ""),
                              };
                            return { name: fullName, batch: null };
                          })()}
                          <div
                            class="pointer-events-auto absolute flex w-max max-w-[160px] -translate-x-1/2 -translate-y-1/2 transform flex-col justify-center p-1.5 shadow-[0_8px_32px_rgba(0,0,0,0.4)] backdrop-blur-sm transition-all duration-300 hover:shadow-[0_8px_32px_rgba(255,165,0,0.15)] origin-center scale-[0.8] md:max-w-[250px] md:scale-100 md:p-3
                            {parsedName.name === activeStaffName
                              ? 'z-50 scale-[0.9] md:scale-110 border-2 border-[#ff7a1a] shadow-[0_0_30px_rgba(255,122,26,0.75)] ring-4 ring-[#ff7a1a]/20 animate-gradient-flow'
                              : 'z-10 border border-white/10 bg-gradient-to-br from-[#111111]/95 to-[#1a1a1a]/85'}"
                            style="left: {overlay.x !== undefined
                              ? overlay.x
                              : 50}%; top: {overlay.y !== undefined
                              ? overlay.y
                              : 50}%;"
                          >
                            <p
                              class="text-left font-['The_Seasons',serif] text-[10px] leading-tight font-normal tracking-wide text-balance text-white/95 drop-shadow-sm md:text-sm"
                            >
                              {overlay.role}
                            </p>
                            <h4
                              class="mt-1 flex items-baseline gap-1 text-left font-['The_Seasons',serif] text-xs font-normal tracking-wide text-white drop-shadow-md md:text-base"
                            >
                              {parsedName.name}
                              {#if parsedName.batch}
                                <span
                                  class="font-sans text-[10px] font-bold tracking-wider text-[#FFB52E] [text-shadow:0_0_10px_rgba(255,165,0,1),0_0_20px_rgba(255,165,0,0.8),0_0_30px_rgba(255,165,0,0.6)] md:text-sm"
                                  >{parsedName.batch}</span
                                >
                              {/if}
                            </h4>
                          </div>
                        {/each}
                      </div>
                    {/if}
                  </div>
                </div>
              {/each}
            </div>

            <button
              class="absolute top-1/2 right-4 z-10 -translate-y-1/2 rounded-full border border-white/10 bg-black/50 p-3 text-white opacity-0 backdrop-blur-md transition-colors group-hover:opacity-100 hover:bg-[#ff7a1a]"
              onclick={() => scrollGallery(1)}
              aria-label="Next"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </button>
          </div>

          <!-- Staff List Section -->
          {#if staffList.length > 0}
            <div class="mb-24">
              <h3
                class="mb-10 text-center text-sm font-semibold tracking-wider text-[#e2bb44] uppercase"
              >
                Daftar Pengurus
              </h3>
              <div
                class="grid grid-cols-1 gap-4 px-4 sm:grid-cols-2 md:grid-cols-3 md:px-0 lg:grid-cols-4"
              >
                {#each staffList as staff}
                  <button
                    onclick={() => scrollToStaff(staff)}
                    class="group flex w-full flex-col items-start rounded-2xl border border-white/5 bg-[#111111]/80 p-5 text-left transition-all hover:border-[#ff7a1a]/50 hover:bg-[#1a1a1a]"
                  >
                    <div class="flex w-full items-center gap-4">
                      {#if staff.picture}
                        <img
                          src={staff.picture}
                          alt={staff.name}
                          class="h-12 w-12 shrink-0 rounded-full border border-white/10 object-cover transition-colors group-hover:border-[#ff7a1a]/50"
                        />
                      {/if}
                      <div class="flex flex-col items-start">
                        <span
                          class="mb-1.5 text-[10px] leading-tight font-bold tracking-widest text-[#ff7a1a] uppercase"
                          >{staff.role}</span
                        >
                        <div class="flex flex-wrap items-center gap-2">
                          <span
                            class="font-['The_Seasons',serif] text-base text-white/90 transition-colors group-hover:text-white"
                            >{staff.name}</span
                          >
                          {#if staff.batch}
                            <span
                              class="rounded-md bg-white/5 px-2 py-0.5 text-[10px] font-bold text-[#FFB52E]"
                              >{staff.batch}</span
                            >
                          {/if}
                        </div>
                      </div>
                    </div>
                  </button>
                {/each}
              </div>
            </div>
          {/if}
        {:else if teamData.members.length === 0}
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
                        class="font-['The_Seasons',serif] text-4xl font-extrabold text-[#ffd344]"
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
                        class="font-['The_Seasons',serif] text-xl font-bold text-white opacity-90"
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

  @keyframes gradientFlow {
    0% {
      background-position: 0% 50%;
    }
    50% {
      background-position: 100% 50%;
    }
    100% {
      background-position: 0% 50%;
    }
  }

  @keyframes seaWaves {
    0% {
      background-position:
        0% 30%,
        100% 70%;
    }
    50% {
      background-position:
        100% 70%,
        0% 30%;
    }
    100% {
      background-position:
        0% 30%,
        100% 70%;
    }
  }

  :global(.animate-gradient-flow) {
    background-image:
      radial-gradient(
        circle at 30% 30%,
        rgba(255, 122, 26, 0.45) 0%,
        transparent 60%
      ),
      radial-gradient(
        circle at 70% 70%,
        rgba(42, 0, 120, 0.95) 0%,
        transparent 70%
      ),
      linear-gradient(135deg, #111111 0%, #1a1a1a 100%);
    background-size:
      200% 200%,
      200% 200%,
      100% 100%;
    animation: seaWaves 7s ease-in-out infinite;
  }
</style>
