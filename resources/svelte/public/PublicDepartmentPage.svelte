<script>
  import Navbar from "../components/landing/Navbar.svelte";
  import Footer from "../components/landing/Footer.svelte";
  import { onMount } from "svelte";
  import { router } from "@inertiajs/svelte";
  import { fade, scale } from "svelte/transition";

  let {
    organizationName = "HIMATEKKOM ITS",
    homeUrl = "/",
    loginUrl = "/login",
    infoUrl = "/informasi",
    acaraUrl = "/acara",
    selectedSlug = null,
  } = $props();

  const assetBase = "/images/figma-taling";

  // Hardcoded Figma Departments with placeholder texts
  const departments = [
    // Outer Orbit
    {
      id: "personalia",
      name: "Personalia",
      orbit: "outer",
      baseAngle: 0,
      dotColor: "from-[#ffd344] to-[#ffa500]",
      description:
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
      focus: ["Harmonisasi Internal", "Pengawasan Kinerja", "Engagement Staff"],
    },
    {
      id: "risprof",
      name: "Risprof",
      orbit: "outer",
      baseAngle: (72 * Math.PI) / 180,
      dotColor: "from-[#ff7a1a] to-[#ffd344]",
      description:
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia.",
      focus: ["Riset Teknologi", "Kompetensi Ilmiah", "Career Preparation"],
    },
    {
      id: "kwu",
      name: "KWU",
      orbit: "outer",
      baseAngle: (144 * Math.PI) / 180,
      dotColor: "from-[#ffd344] to-[#ffa500]",
      description:
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mollis pretium lorem primis senectus habitasse. Pretium aenean dictumst feugiat vulputate id, imperdiet sit cras proin mus.",
      focus: ["Entrepreneurship", "Pendanaan Mandiri", "Business Partnership"],
    },
    {
      id: "psdm",
      name: "PSDM",
      orbit: "outer",
      baseAngle: (216 * Math.PI) / 180,
      dotColor: "from-[#ffd344] to-[#ffa500]",
      description:
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Netus proin elementum hac ad iaculis interdum. Curae etiam sit class phasellus dis a non cubilia curabitur.",
      focus: [
        "Pengembangan Soft Skills",
        "Minat dan Bakat",
        "Pembinaan Anggota",
      ],
    },
    {
      id: "dagri",
      name: "Dagri",
      orbit: "outer",
      baseAngle: (288 * Math.PI) / 180,
      dotColor: "from-[#ff7a1a] to-[#ffd344]",
      description:
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Magnis non sed ad vivamus in. Inceptos pretium quisque dictumst platea; nisl class sit eros torquent.",
      focus: ["Sinergi Komunitas", "Internal Engagement", "Apresiasi Anggota"],
    },
    // Inner Orbit
    {
      id: "bph",
      name: "BPH",
      orbit: "inner",
      baseAngle: (36 * Math.PI) / 180,
      dotColor: "from-[#ff7a1a] to-[#ffd344]",
      description:
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Habitasse aenean id litora morbi scelerisque eros imperdiet. Sem integer nisl aenean cubilia eleifend nam non convallis.",
      focus: [
        "Administrasi Organisasi",
        "Manajemen Keuangan",
        "Kesekretariatan",
      ],
    },
    {
      id: "hublu",
      name: "Hublu",
      orbit: "inner",
      baseAngle: (108 * Math.PI) / 180,
      dotColor: "from-[#ff7a1a] to-[#ffd344]",
      description:
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Adipiscing scelerisque integer taciti ad facilisi erat cras varius phasellus. Aptent sed maecenas habitant torquent primis hendrerit torquent facilisis.",
      focus: ["Relasi Alumni", "Kompetensi Eksternal", "Networking Strategis"],
    },
    {
      id: "kesma",
      name: "Kesma",
      orbit: "inner",
      baseAngle: (180 * Math.PI) / 180,
      dotColor: "from-[#ffd344] to-[#ffa500]",
      description:
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Parturient maecenas quisque suspendisse class donec non. Convallis non inceptos congue a rhoncus scelerisque.",
      focus: [
        "Advokasi Akademik",
        "Kesejahteraan Finansial",
        "Layanan Mahasiswa",
      ],
    },
    {
      id: "medfo",
      name: "Medfo",
      orbit: "inner",
      baseAngle: (252 * Math.PI) / 180,
      dotColor: "from-[#ff7a1a] to-[#ffd344]",
      description:
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Hac etiam habitasse curae potenti at scelerisque per proin. Placerat primis interdum senectus rhoncus dictum, scelerisque nisl tempor tristique.",
      focus: [
        "Kreatif & Desain Visual",
        "Kanal Komunikasi Publik",
        "Publikasi Informasi",
      ],
    },
    {
      id: "kaderisasi",
      name: "Kaderisasi",
      orbit: "inner",
      baseAngle: (324 * Math.PI) / 180,
      dotColor: "from-[#ffd344] to-[#ffa500]",
      description:
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Dignissim sed scelerisque ad curae taciti per facilisi pulvinar nullam. Dictumst tristique hendrerit sit congue senectus netus mus taciti class.",
      focus: [
        "Kaderisasi Fungsionaris",
        "Latihan Kepemimpinan",
        "Evaluasi Karakter",
      ],
    },
  ];

  const tiltAngleDeg = -28;
  const tiltAngleRad = (tiltAngleDeg * Math.PI) / 180;
  const cosTilt = Math.cos(tiltAngleRad);
  const sinTilt = Math.sin(tiltAngleRad);

  // svelte-ignore state_referenced_locally
  let selectedDeptId = $state(selectedSlug);
  const activeDept = $derived(
    departments.find((d) => d.id === selectedDeptId) || null,
  );

  // Rotation angles for dynamic animation
  let angleOuter = $state(0);
  let angleInner = $state(0);

  // Mouse interactive background light
  let mouseX = $state(0);
  let mouseY = $state(0);
  let isMouseOver = $state(false);

  // Responsive scaling factor based on the remaining space in Orbit Wrapper (native size 1440x900)
  let orbitWrapperWidth = $state(1440);
  let orbitWrapperHeight = $state(900);
  let orbitWrapperEl = $state(null);

  function updateOrbitDimensions() {
    if (orbitWrapperEl) {
      orbitWrapperWidth = orbitWrapperEl.clientWidth || 1440;
      orbitWrapperHeight = orbitWrapperEl.clientHeight || 900;
    }
  }

  const scaleFactor = $derived(
    Math.min(orbitWrapperWidth / 1440, orbitWrapperHeight / 900),
  );

  let sectionEl = $state(null);
  let scrollProgress = $state(0);

  const headerOpacity = $derived(easeOutQuart(scrollProgress));
  const headerTranslateY = $derived((1 - easeOutQuart(scrollProgress)) * 30);

  const badgeHeaderOpacity = $derived(
    easeOutQuart(Math.max(0, Math.min(1, (scrollProgress - 0.15) / 0.85))),
  );
  const badgeHeaderTranslateY = $derived(
    (1 -
      easeOutQuart(Math.max(0, Math.min(1, (scrollProgress - 0.15) / 0.85)))) *
      30,
  );

  const outerRingOpacity = $derived(easeOutQuart(scrollProgress));
  const innerRingOpacity = $derived(
    easeOutQuart(Math.max(0, Math.min(1, (scrollProgress - 0.1) / 0.9))),
  );

  const coreOpacity = $derived(easeOutQuart(scrollProgress));
  const coreScale = $derived(0.8 + 0.2 * easeOutQuart(scrollProgress));

  function easeOutQuart(t) {
    return 1 - Math.pow(1 - t, 4);
  }

  function getBadgeProgress(i) {
    const startProgress = i * 0.04;
    const progress = (scrollProgress - startProgress) / (1 - startProgress);
    const bp = Math.max(0, Math.min(1, progress));
    const eased = easeOutQuart(bp);
    return {
      opacity: eased,
      scale: 0.8 + 0.2 * eased,
    };
  }

  function handleScroll() {
    if (!sectionEl) return;
    const rect = sectionEl.getBoundingClientRect();
    const viewportHeight = window.innerHeight;

    // Calculate the vertical center of the solar system relative to the viewport
    const systemY = rect.top + rect.height * 0.45;

    // Start progress (0) when system center is 75% down the viewport height
    // End progress (1) when system center reaches 25% down the viewport height (giving a wide 50% viewport range)
    const startY = viewportHeight * 0.75;
    const endY = viewportHeight * 0.25;

    const progress = (startY - systemY) / (startY - endY);
    scrollProgress = Math.max(0, Math.min(1, progress));
  }

  function handleMouseMove(e) {
    const rect = e.currentTarget.getBoundingClientRect();
    mouseX = e.clientX - rect.left;
    mouseY = e.clientY - rect.top;
    isMouseOver = true;
  }

  function handleMouseLeave() {
    isMouseOver = false;
  }

  let isTrackingScroll = false;

  onMount(() => {
    let frameId;
    let lastTime = performance.now();
    const update = (time) => {
      let delta = time - lastTime;
      lastTime = time;

      // Cap delta to prevent huge jumps when tab is inactive or lagging
      if (delta > 100) {
        delta = 16.67;
      }

      // Base speed in radians per millisecond
      const baseSpeed = 0.00004;
      // Freeze completely (speed = 0) ONLY when a department is selected
      const speedModifier = selectedDeptId !== null ? 0 : 1.0;

      angleOuter += baseSpeed * speedModifier * delta;
      angleInner -= baseSpeed * 1.5 * speedModifier * delta;

      // Only calculate scroll position when the section is visible in the viewport
      if (isTrackingScroll) {
        handleScroll();
      }

      frameId = requestAnimationFrame(update);
    };
    frameId = requestAnimationFrame(update);

    // Initial measurement
    updateOrbitDimensions();

    const handleResize = () => {
      updateOrbitDimensions();
      handleScroll();
    };

    window.addEventListener("resize", handleResize, { passive: true });

    // Setup intersection observer to toggle scroll event listeners dynamically
    let observer;
    if (typeof IntersectionObserver !== "undefined" && sectionEl) {
      observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              if (!isTrackingScroll) {
                isTrackingScroll = true;
                window.addEventListener("scroll", handleScroll, {
                  passive: true,
                });
                handleScroll();
              }
            } else {
              if (isTrackingScroll) {
                isTrackingScroll = false;
                window.removeEventListener("scroll", handleScroll);
              }
            }
          });
        },
        { rootMargin: "100px 0px 100px 0px" },
      );
      observer.observe(sectionEl);
    }

    return () => {
      cancelAnimationFrame(frameId);
      if (observer) {
        observer.disconnect();
      }
      window.removeEventListener("scroll", handleScroll);
      window.removeEventListener("resize", handleResize);
    };
  });

  // Sync state if selectedSlug changes via router back/forward
  $effect(() => {
    selectedDeptId = selectedSlug;
  });

  $effect(() => {
    selectedDeptId;
    updateOrbitDimensions();
    const timer = setTimeout(updateOrbitDimensions, 200);
    return () => clearTimeout(timer);
  });

  function handleDeptClick(dept, event) {
    event.stopPropagation();
    selectedDeptId = dept.id;
    const url = `/departemen/${dept.id}`;
    window.history.pushState(
      {
        ...window.history.state,
        url: url,
      },
      "",
      url,
    );
  }

  // Deselect on empty space click
  function handleDeselect() {
    if (selectedDeptId) {
      selectedDeptId = null;
      const url = "/departemen";
      window.history.pushState(
        {
          ...window.history.state,
          url: url,
        },
        "",
        url,
      );
    }
  }

  // Navigation Items
  const resolvedNavigation = $derived([
    { href: homeUrl, label: "Beranda" },
    { href: "/departemen", label: "Departemen" },
    { href: "/kompetisi", label: "Kompetisi" },
    { href: "/tentang", label: "Tentang Kami" },
  ]);

  const departemenUrl = "/departemen";
  const kompetisiUrl = "/kompetisi";
  const tentangUrl = "/tentang";
  const navigationItems = $derived(
    resolvedNavigation.map((item) => {
      if (item.label === "Departemen") {
        return {
          ...item,
          children: [
            { href: item.href, label: "Profil departemen" },
            { href: tentangUrl, label: "Sejarah himpunan" },
          ],
        };
      }
      if (item.label === "Kompetisi") {
        return {
          ...item,
          children: [
            { href: item.href, label: "Kompetisi" },
            { href: acaraUrl, label: "Agenda kegiatan" },
          ],
        };
      }
      return item;
    }),
  );

  const pageTitle = "Departemen HIMATEKKOM ITS | Kabinet Sentra Sinergi";
  const pageDescription =
    "Kenali 10 departemen operasional fungsional di HIMATEKKOM ITS Kabinet Sentra Sinergi.";
</script>

<svelte:head>
  <title>{pageTitle}</title>
  <meta name="description" content={pageDescription} />
  <meta property="og:type" content="website" />
  <meta property="og:title" content={pageTitle} />
  <meta property="og:description" content={pageDescription} />
</svelte:head>

<div
  class="min-h-screen w-full bg-white font-['Plus_Jakarta_Sans',sans-serif] text-[#222]"
>
  <Navbar {homeUrl} {loginUrl} {navigationItems} />

  <main>
    <!-- Hero Section -->
    <section
      class="relative h-[calc(100dvh-74px)] min-h-[600px] w-full overflow-hidden bg-gradient-to-br from-[#5d0077] to-[#2a0078] md:h-[896px]"
    >
      <picture class="contents">
        <source srcset={`${assetBase}/hero-bg.avif`} type="image/avif" />
        <source srcset={`${assetBase}/hero-bg.webp`} type="image/webp" />
        <img
          class="absolute inset-0 h-full w-full object-cover opacity-50 mix-blend-overlay"
          src={`${assetBase}/hero-bg.png`}
          alt=""
          style="position: absolute;"
        />
      </picture>
      <picture class="contents">
        <source srcset={`${assetBase}/botanical.avif`} type="image/avif" />
        <source srcset={`${assetBase}/botanical.webp`} type="image/webp" />
        <img
          class="animate-slow-pan absolute -top-[22%] -left-[20%] h-[180%] w-[170%] object-cover opacity-25 mix-blend-soft-light"
          src={`${assetBase}/botanical.png`}
          alt=""
          style="position: absolute;"
          width="1600"
          height="1066"
        />
      </picture>

      <!-- Center Hero Graphic, Title, and Glow Wrapper -->
      <div class="hero-content-wrapper">
        <!-- Center Hero Graphic -->
        <div class="hero-logo-container">
          <img
            src={`${assetBase}/dept-hero-graphic.svg`}
            alt="Department Hero Graphic"
            class="animate-float-logo h-auto w-full drop-shadow-2xl"
            style="width: 100%; max-width: var(--hero-max-width, 280px); height: auto;"
            width="598"
            height="748"
          />
        </div>

        <!-- Text Content -->
        <div class="hero-title-container">
          <h1 class="hero-title">Departemen</h1>
        </div>

        <!-- Glow Line under title -->
        <div class="hero-glow-wrapper">
          <div
            class="animate-fade-in-line h-full w-full bg-gradient-to-r from-transparent via-[#ff7a1a] to-transparent blur-[2px]"
          ></div>
        </div>
      </div>

      <!-- Left and Right Stars -->
      <img
        src={`${assetBase}/star-large.svg`}
        alt=""
        class="star-large animate-float-large pointer-events-none opacity-80 drop-shadow-2xl"
        width="492"
        height="463"
        style="position: absolute;"
      />
      <img
        src={`${assetBase}/star-small.svg`}
        alt=""
        class="star-small animate-float-small pointer-events-none opacity-80 drop-shadow-2xl"
        width="375"
        height="404"
        style="position: absolute;"
      />

      <!-- Slogan Bottom Left -->
      <div
        class="animate-fade-left absolute bottom-6 left-6 flex items-center gap-4 font-bold tracking-wider text-white opacity-0 delay-300 md:bottom-[36px] md:left-[49px]"
        style="position: absolute;"
      >
        <img
          src={`${assetBase}/logo-mark.svg`}
          alt=""
          class="w-[60px] md:w-[90px]"
          width="98"
          height="54"
        />
        <img
          src={`${assetBase}/text-logo.svg`}
          alt="Sentra Sinergi"
          class="w-[50px] md:w-[80px]"
          width="88"
          height="43"
        />
        <div class="h-[49px] w-px bg-white/70"></div>
        <div class="text-xs leading-tight md:text-sm">
          <p class="text-lg">#OKE</p>
          <p class="font-medium tracking-normal opacity-90">
            Optimalisasi | Kolaborasi | Ekspansi
          </p>
        </div>
      </div>
    </section>

    <!-- Reveal & Orbit Section -->
    <section
      bind:this={sectionEl}
      class="relative overflow-hidden border-t border-white/10 bg-[#2a0078] text-white lg:h-screen lg:min-h-[800px]"
    >
      <!-- Glow texture matching Figma Atmosphere -->
      <div
        class="absolute inset-0 bg-cover bg-center opacity-10 mix-blend-overlay"
        style="background-image: url('{assetBase}/hero-bg.png')"
      ></div>

      <div class="relative z-10 flex w-full flex-col lg:h-full lg:flex-row">
        <!-- Left Side: Orbit System Container -->
        <!-- svelte-ignore a11y_no_static_element_interactions -->
        <!-- svelte-ignore a11y_click_events_have_key_events -->
        <div
          class="relative flex min-h-[650px] flex-1 flex-col justify-between overflow-hidden px-6 py-12 lg:h-full lg:min-h-0 lg:py-6"
          onmousemove={handleMouseMove}
          onmouseleave={handleMouseLeave}
          onclick={handleDeselect}
        >
          <!-- Title and Stats Header -->
          <div
            class="relative z-10 mb-12 flex flex-col justify-between gap-6 md:flex-row md:items-end lg:mb-4"
          >
            <div
              style="opacity: {headerOpacity}; transform: translateY({headerTranslateY}px);"
            >
              <span
                class="text-sm font-bold tracking-wider text-[#ffd344] uppercase"
                >DEPARTEMENT REVEAL</span
              >
              <h2
                class="mt-2 font-['Playfair_Display'] text-4xl font-bold text-white md:text-6xl"
              >
                Departemen Himatekkom
              </h2>
              <p class="mt-4 max-w-2xl text-lg text-white/80">
                Sepuluh ruang gerak untuk bertumbuh, berkarya, and membawa
                semangat #OKE ke setiap kolaborasi.
              </p>
            </div>
            <div
              style="opacity: {badgeHeaderOpacity}; transform: translateY({badgeHeaderTranslateY}px);"
              class="flex items-center gap-4 self-start rounded-full border border-white/20 bg-white/10 px-6 py-3 shadow-lg backdrop-blur-md"
            >
              <span class="text-4xl leading-none font-bold text-white">10</span>
              <span class="text-sm leading-tight font-medium text-white/80"
                >departemen<br />siap dikenali</span
              >
            </div>
          </div>

          <!-- Orbit Wrapper (centered and scaled aspect-fit inside remaining height) -->
          <div
            bind:this={orbitWrapperEl}
            class="relative flex min-h-[400px] w-full flex-1 items-center justify-center lg:min-h-0"
          >
            <div
              class="orbit-scale-wrapper"
              style="transform: translate(-50%, -50%) scale({scaleFactor});"
            >
              <div class="orbit-system">
                <!-- Orbit Rings (Perfect Ellipses centered at 720, 450) -->
                <div
                  class="orbit-ring-outer {selectedDeptId ? 'blurred-out' : ''}"
                  style="opacity: {outerRingOpacity};"
                ></div>
                <div
                  class="orbit-ring-inner {selectedDeptId ? 'blurred-out' : ''}"
                  style="opacity: {innerRingOpacity};"
                ></div>

                <!-- Center Core Badge with Sun Glow -->
                <div
                  class="orbit-core-ring {selectedDeptId ? 'blurred-out' : ''}"
                  style="opacity: {coreOpacity}; transform: scale({coreScale});"
                ></div>
                <div
                  class="orbit-core-badge {selectedDeptId ? 'blurred-out' : ''}"
                  style="opacity: {coreOpacity}; transform: scale({coreScale});"
                >
                  <img
                    src={`${assetBase}/dept-core-badge.svg`}
                    alt="Himatekkom Shield"
                    class="h-full w-full object-contain"
                    width="298"
                    height="298"
                  />
                </div>

                <!-- Orbiting Badges -->
                {#each departments as dept, i}
                  {@const rad =
                    dept.orbit === "outer" ? angleOuter : angleInner}
                  {@const theta = dept.baseAngle + rad}
                  {@const dx =
                    (dept.orbit === "outer" ? 630 : 420) * Math.cos(theta)}
                  {@const dy =
                    (dept.orbit === "outer" ? 380 : 240) * Math.sin(theta)}
                  {@const x = 720 + dx * cosTilt - dy * sinTilt}
                  {@const y = 450 + dx * sinTilt + dy * cosTilt}
                  {@const bp = getBadgeProgress(i)}

                  <div
                    style="position: absolute; left: {x - 34}px; top: {y -
                      24}px; opacity: {bp.opacity}; transform: scale({bp.scale}); pointer-events: {bp.opacity >
                    0.15
                      ? 'auto'
                      : 'none'};"
                    class="transition-opacity duration-300 ease-out"
                  >
                    <button
                      type="button"
                      class="orbit-badge-btn {selectedDeptId === dept.id
                        ? 'active'
                        : ''} {selectedDeptId && selectedDeptId !== dept.id
                        ? 'blurred-out'
                        : ''}"
                      style="position: relative;"
                      onclick={(e) => handleDeptClick(dept, e)}
                    >
                      <span class="badge-dot bg-gradient-to-tr {dept.dotColor}"
                      ></span>
                      <span class="badge-label">{dept.name}</span>
                    </button>
                  </div>
                {/each}
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side: Persistent Detail Slide-in Panel (Opens only on click) -->
        <div
          class="detail-panel {selectedDeptId !== null
            ? 'panel-open'
            : 'panel-closed'}"
        >
          {#if activeDept}
            <div class="w-full" transition:fade={{ duration: 250 }}>
              <span
                class="text-xs font-bold tracking-widest text-[#ffd344] uppercase"
                >Fokus Departemen</span
              >
              <h3 class="mt-2 text-4xl font-extrabold text-white">
                {activeDept.name}
              </h3>
              <div
                class="mt-3 mb-6 h-1 w-20 rounded bg-gradient-to-r from-[#ff7a1a] to-[#ffd344]"
              ></div>
              <p class="text-sm leading-relaxed text-white/85">
                {activeDept.description}
              </p>
              <div class="mt-8">
                <h4
                  class="mb-3 text-xs font-bold tracking-wider text-white/60 uppercase"
                >
                  Key Focus Areas
                </h4>
                <div class="flex flex-col gap-2.5">
                  {#each activeDept.focus as f, idx}
                    <div
                      class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/5 px-4 py-3 transition-colors duration-300 hover:bg-white/10"
                      style="animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: {idx *
                        0.08}s;"
                    >
                      <span class="h-2 w-2 rounded-full bg-[#ffd344]"></span>
                      <span class="text-xs font-semibold text-white/95"
                        >{f}</span
                      >
                    </div>
                  {/each}
                </div>
              </div>
            </div>
          {/if}
        </div>
      </div>
    </section>

    <!-- Closing CTA Section -->
    <section
      class="relative border-t border-white/10 bg-[#2a0078] py-16 text-white"
    >
      <div class="mx-auto max-w-5xl px-6 text-center">
        <span class="text-xs font-bold tracking-widest text-[#ffd344] uppercase"
          >READY TO REVEAL</span
        >
        <h2
          class="mt-4 font-['Playfair_Display'] text-3xl font-bold text-white md:text-5xl"
        >
          Temukan departemenmu. Mulai ceritamu.
        </h2>
        <p class="mx-auto mt-6 max-w-2xl text-white/75">
          #OKE bukan sekadar tema. Ini cara kita mengoptimalkan potensi, membuka
          kolaborasi, dan memperluas dampak.
        </p>
        <div class="mt-8 flex justify-center">
          <span
            class="rounded-full bg-gradient-to-r from-[#ff7a1a] to-[#ffd344] px-8 py-3 text-sm font-bold tracking-wide text-white uppercase shadow-lg"
          >
            Departemen Himatekkom
          </span>
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

<style>
  /* Core Page Animations */
  .animate-slow-pan {
    animation: slowPan 25s ease-in-out infinite alternate;
    transform-origin: center center;
  }

  .animate-fade-up-center {
    animation: fadeUpCenter 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }

  .animate-fade-in-center {
    animation: fadeInCenter 1.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }

  .animate-fade-left {
    animation: fadeLeft 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }

  .animate-fade-up {
    animation: fadeUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }

  /* Delay utility classes */
  .delay-100 {
    animation-delay: 0.1s !important;
  }
  .delay-200 {
    animation-delay: 0.2s !important;
  }
  .delay-300 {
    animation-delay: 0.3s !important;
  }
  .delay-400 {
    animation-delay: 0.4s !important;
  }
  .delay-500 {
    animation-delay: 0.5s !important;
  }

  .hero-content-wrapper {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    padding-top: 4rem;
    padding-bottom: 6rem;
    pointer-events: none;
    z-index: 10;
  }

  @media (min-width: 768px) {
    .hero-content-wrapper {
      position: absolute;
      inset: 0;
      display: block;
      padding: 0;
    }
  }

  .hero-logo-container {
    position: relative;
    width: 100%;
    max-width: 360px;
    --hero-max-width: 360px;
    margin: 0 auto -160px auto;
    pointer-events: auto;
  }

  .hero-logo-container img {
    margin-left: 8.3%;
  }

  @media (min-width: 768px) {
    .hero-logo-container {
      position: absolute;
      top: 120px;
      left: 50%;
      transform: translateX(-50%);
      max-width: 598px;
      --hero-max-width: 598px;
      margin: 0;
    }
  }

  .hero-title-container {
    opacity: 0;
    pointer-events: none;
    animation: fadeUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }

  @media (min-width: 768px) {
    .hero-title-container {
      position: absolute;
      top: 504px;
      left: 50%;
      transform: translateX(-50%);
      width: auto;
      animation: fadeUpCenter 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
  }

  .hero-title {
    font-family: "The Seasons", "Playfair Display", Georgia, serif;
    font-style: normal;
    font-weight: 300;
    font-size: 3.75rem;
    line-height: 1.2;
    text-align: center;
    color: #ffffff;
    text-shadow: 0px 0px 20px #ffffff;
    white-space: nowrap;
  }

  @media (min-width: 768px) {
    .hero-title {
      font-size: 96px;
      line-height: 125px;
    }
  }

  .hero-glow-wrapper {
    position: relative;
    width: 100%;
    max-width: 300px;
    height: 16px;
    margin: 0 auto;
  }

  @media (min-width: 768px) {
    .hero-glow-wrapper {
      position: absolute;
      top: 623px;
      left: 50%;
      transform: translateX(-50%);
      max-width: 534px;
      height: 22px;
      margin: 0;
    }
  }

  .animate-fade-in-line {
    opacity: 0;
    animation: fadeInLine 1.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: 0.2s !important;
  }

  @keyframes fadeInLine {
    from {
      opacity: 0;
      transform: scaleX(0.8);
    }
    to {
      opacity: 0.8;
      transform: scaleX(1);
    }
  }

  @keyframes slowPan {
    0% {
      transform: scale(1) translate(0, 0);
    }
    100% {
      transform: scale(1.08) translate(2%, -1%);
    }
  }

  @keyframes fadeUpCenter {
    from {
      opacity: 0;
      transform: translate(-50%, 20px);
    }
    to {
      opacity: 1;
      transform: translate(-50%, 0);
    }
  }

  @keyframes fadeInCenter {
    from {
      opacity: 0;
      transform: translate(-50%, 0) scaleX(0.8);
    }
    to {
      opacity: 0.8;
      transform: translate(-50%, 0) scaleX(1);
    }
  }

  @keyframes fadeLeft {
    from {
      opacity: 0;
      transform: translateX(-20px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  @keyframes fadeUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes slideInRight {
    from {
      opacity: 0;
      transform: translateX(30px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  @keyframes scaleIn {
    from {
      opacity: 0;
      transform: scale(0.9);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }

  .star-large {
    position: absolute;
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
  @media (min-width: 1024px) {
    .star-large {
      top: -155px;
      left: -239px;
      width: 507px;
      height: 476px;
    }
  }

  .star-small {
    position: absolute;
    top: 520px;
    right: -80px;
    width: 150px;
    height: 161px;
  }
  @media (min-width: 768px) {
    .star-small {
      top: 500px;
      right: -130px;
      width: 240px;
      height: 258px;
    }
  }
  @media (min-width: 1024px) {
    .star-small {
      top: 496px;
      right: -208px;
      width: 386px;
      height: 415px;
    }
  }

  .animate-float-large {
    animation: floatLarge 8s ease-in-out infinite;
  }
  .animate-float-small {
    animation: floatSmall 10s ease-in-out infinite;
  }
  .animate-float-logo {
    animation: floatLogo 6s ease-in-out infinite alternate;
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
      transform: translateY(0px) rotate(8deg) translate(0, 0);
    }
    50% {
      transform: translateY(12px) rotate(6deg) translate(0, 0);
    }
  }

  @keyframes floatLogo {
    0% {
      transform: translateY(0px) scale(1);
    }
    100% {
      transform: translateY(-8px) scale(1.015);
      filter: drop-shadow(0 20px 20px rgba(255, 255, 255, 0.08));
    }
  }

  /* Orbit System Styles */
  .orbit-scale-wrapper {
    position: absolute;
    width: 1440px;
    height: 900px;
    left: 50%;
    top: 50%;
    transform-origin: center center;
  }

  .orbit-system {
    position: absolute;
    width: 1440px;
    height: 900px;
    top: 0;
    left: 0;
  }

  /* Orbit Rings (Ellipses centered at x=720, y=450 relative to orbit-system) */
  .orbit-ring-outer {
    position: absolute;
    left: 90px; /* Center 720 - 630 horizontal radius */
    top: 70px; /* Center 450 - 380 vertical radius */
    width: 1260px; /* 630 * 2 */
    height: 760px; /* 380 * 2 */
    border: 1.5px solid rgba(255, 255, 255, 0.22);
    border-radius: 50%;
    pointer-events: none;
    transform: rotate(-28deg);
    transform-origin: center center;
    transition:
      filter 0.4s ease,
      opacity 0.4s ease;
  }

  .orbit-ring-inner {
    position: absolute;
    left: 300px; /* Center 720 - 420 horizontal radius */
    top: 210px; /* Center 450 - 240 vertical radius */
    width: 840px; /* 420 * 2 */
    height: 480px; /* 240 * 2 */
    border: 1.2px solid rgba(255, 211, 68, 0.28);
    border-radius: 50%;
    pointer-events: none;
    transform: rotate(-28deg);
    transform-origin: center center;
    transition:
      filter 0.4s ease,
      opacity 0.4s ease;
  }

  /* Core Badge (Centered at 720, 450) */
  .orbit-core-ring {
    position: absolute;
    left: 575px; /* Center 720 - 145 */
    top: 305px; /* Center 450 - 145 */
    width: 290px;
    height: 290px;
    border: 8px solid rgba(255, 122, 26, 0.9);
    border-radius: 50%;
    box-shadow: 0px 4px 100px 19px rgba(255, 211, 68, 0.5);
    animation: pulseGlow 4s infinite alternate;
    transition:
      filter 0.4s ease,
      opacity 0.4s ease;
  }

  .orbit-core-badge {
    position: absolute;
    left: 571px; /* Center 720 - 149 */
    top: 321px; /* Center 450 - 129 */
    width: 298px;
    height: 298px;
    z-index: 5;
    transition:
      filter 0.4s ease,
      opacity 0.4s ease;
  }

  @keyframes pulseGlow {
    0% {
      box-shadow: 0px 4px 80px 10px rgba(255, 211, 68, 0.4);
    }
    100% {
      box-shadow: 0px 4px 120px 25px rgba(255, 211, 68, 0.75);
    }
  }

  /* Orbiting Badges Buttons */
  .orbit-badge-btn {
    position: absolute;
    display: flex;
    align-items: center;
    gap: 14px;
    background: rgba(255, 255, 255, 0.12);
    border: 1.2px solid rgba(255, 255, 255, 0.22);
    box-shadow: 0px 12px 26px -12px rgba(0, 0, 0, 0.16);
    border-radius: 32px;
    padding: 10px 20px;
    cursor: pointer;
    z-index: 10;
    transition:
      background 0.4s ease,
      border-color 0.4s ease,
      box-shadow 0.4s ease,
      transform 0.4s cubic-bezier(0.16, 1, 0.3, 1),
      filter 0.4s ease,
      opacity 0.4s ease;
  }

  .orbit-badge-btn:hover {
    background: rgba(255, 255, 255, 0.22);
    border-color: #ffd344;
    transform: translateY(-4px) scale(1.03);
    box-shadow: 0px 20px 30px -10px rgba(255, 211, 68, 0.3);
  }

  .orbit-badge-btn.active {
    background: rgba(255, 122, 26, 0.9);
    border-color: #ffd344;
    box-shadow: 0px 20px 30px -10px rgba(255, 122, 26, 0.5);
    transform: scale(1.05);
  }

  /* Focus and Blur Effects */
  .orbit-badge-btn.blurred-out,
  .orbit-ring-outer.blurred-out,
  .orbit-ring-inner.blurred-out,
  .orbit-core-ring.blurred-out,
  .orbit-core-badge.blurred-out {
    filter: blur(4px);
    opacity: 0.35 !important;
  }

  .badge-dot {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .badge-label {
    color: white;
    font-size: 20px;
    font-weight: 600;
    line-height: 1.2;
  }

  /* Dynamic Slide-out Detail Panel */
  .detail-panel {
    width: 100%;
    flex-shrink: 0;
    background-color: rgba(30, 0, 88, 0.9);
    position: relative;
    z-index: 20;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
    transition: all 0.12s ease-out;
  }

  /* Desktop style transitions */
  @media (min-width: 1024px) {
    .detail-panel {
      position: absolute;
      right: 0;
      top: 0;
      bottom: 0;
      border-left: 1px solid rgba(255, 255, 255, 0.1);
      pointer-events: none; /* Make click-through to prevent hover-flicker on overlay */
    }
    .detail-panel.panel-open {
      width: 420px;
      padding: 2rem;
      opacity: 1;
      transform: translateX(0);
      pointer-events: auto; /* Enable interaction when open */
    }
    .detail-panel.panel-closed {
      width: 0px;
      padding: 2rem 0;
      opacity: 0;
      transform: translateX(50px);
      border-left: 1px solid transparent;
      pointer-events: none; /* Prevent interaction when closed */
    }
  }

  /* Mobile/Tablet style transitions */
  @media (max-width: 1023px) {
    .detail-panel {
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      max-height: 500px;
      overflow: hidden;
    }
    .detail-panel.panel-open {
      max-height: 500px;
      padding: 2rem;
      opacity: 1;
      transform: translateY(0);
      pointer-events: auto; /* Enable interaction when open */
    }
    .detail-panel.panel-closed {
      max-height: 0px;
      padding: 0 2rem;
      opacity: 0;
      transform: translateY(50px);
      border-top: 1px solid transparent;
      pointer-events: none; /* Prevent interaction when closed */
    }
  }
</style>
