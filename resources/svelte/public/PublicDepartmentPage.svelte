<script>
  import Navbar from "../components/landing/Navbar.svelte";
  import Footer from "../components/landing/Footer.svelte";
  import TalingDeptHeroGraphic from "../components/landing/TalingDeptHeroGraphic.svelte";
  import { onMount } from "svelte";
  import { router } from "@inertiajs/svelte";
  import { fade, scale } from "svelte/transition";
  import { inertiaEnhance } from "../lib/inertia-enhance.js";

  let {
    organizationName = "HIMATEKKOM ITS",
    homeUrl = "/",
    loginUrl = "/login",
    infoUrl = "/informasi",
    acaraUrl = "/acara",
    selectedSlug = null,
  } = $props();

  const assetBase = "/images/figma-taling";

  const getElapsedDelay = (periodMs) => {
    if (typeof window !== "undefined") {
      if (!window.__initialAnimationTimestamp) {
        window.__initialAnimationTimestamp = Date.now();
      }
      const elapsedMs = Date.now() - window.__initialAnimationTimestamp;
      return `-${(elapsedMs % periodMs) / 1000}s`;
    }
    return "0s";
  };

  const largeStarDelay = getElapsedDelay(8000);
  const smallStarDelay = getElapsedDelay(10000);
  const botanicalDelay = getElapsedDelay(25000);

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
  // Initialize with browser window dimensions minus exact padding to prevent initial layout flash on mount
  const isBrowser = typeof window !== "undefined";
  let orbitWrapperWidth = $state(isBrowser ? window.innerWidth - 58 : 1440);
  let orbitWrapperHeight = $state(
    isBrowser
      ? window.innerWidth < 1024
        ? 400
        : window.innerHeight - 231
      : 900,
  );
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
  let graphicEl = $state(null);
  let skipEntry = $state(
    typeof window !== "undefined" && !!window.__skipEntryAnimation,
  );
  const isFromLanding =
    typeof window !== "undefined" && window.__lastPathname === "/";

  const logoMarkPaths = [
    "M18.36 14.677c-5.137 2.564-8.573 5.564-9.706 7.762-.986 1.91-.899 3.566-.899 3.566.018.297.096 1.23.559 2.224.968 2.023 2.93 2.913 3.976 3.393 5.032 2.284 13.317.052 14.12-.175 0 1.343 0 2.695-.01 4.046 0 .053.01.35.262.594.244.226.55.218.602.209.828-.096 1.674-.21 2.537-.331a64 64 0 0 0 5.826-1.178q.013.892.157 1.954c.113.854.288 1.622.47 2.293-3.296.62-13.83 2.337-22.63-.663-5.616-1.936-7.587-4.752-8.092-5.546-.506-.785-1.404-2.198-1.518-4.186-.235-3.767 2.52-6.619 3.863-8.014 1.605-1.657 3.175-2.555 4.753-3.462a29 29 0 0 1 5.73-2.486",
    "M28.005 31.09v3.854c5.084-.863 6.898-1.308 7.282-1.526.017-.017.392-.236.88-.262.27-.008.402.044.471.096.201.122.288.34.34.54.314 1.16.724 4.771.75 4.99a87.8 87.8 0 0 0 19.727-8.05 87.5 87.5 0 0 0 18.497-13.788c-.68.062-1.396.149-2.128.262-2.11.34-3.96.89-5.512 1.474a102 102 0 0 1-10.848 7.343 103 103 0 0 1-8.782 4.613c-1.055-.62-2.12-1.238-3.175-1.857l9.497-6.01-4.107-2.877c-1.064.637-2.573 1.552-4.413 2.599-3.619 2.067-6.497 3.715-10.116 5.372a80 80 0 0 1-8.363 3.227",
    "M41.74 20.18c.933.636 1.849 1.282 2.782 1.918a84 84 0 0 1 7.883-4.456 94 94 0 0 1 4.579-2.11.7.7 0 0 1 .261-.035.68.68 0 0 1 .428.174c.453.314.907.628 1.36.95.052.044.078.105.07.158-.009.052-.053.078-.061.096a2601 2601 0 0 1-5.093 3.035c1.003.671 1.997 1.334 2.982 2.005a52 52 0 0 1 5.172-2.782 49 49 0 0 1 5.354-2.119c2.067-.706 3.733-1.264 6.035-1.622a27 27 0 0 1 2.808-.296 14.5 14.5 0 0 1 1.692-3.418 21 21 0 0 0-4.657.156c-.75.105-1.456.244-2.093.41-.113.017-.349.052-.488-.07-.166-.157-.079-.47-.053-.619.218-.898 2.146-4.134 2.468-4.666a64 64 0 0 0-9.444 2.146c-6.907 2.162-12.035 5.11-16.43 7.674a119 119 0 0 0-5.555 3.47",
    "M74.975 6.602-2.163 3.924a22.9 22.9 0 0 1 6.846-.026.3.3 0 0 1 .183.122c.105.13.044.296.035.314-.393.776-.794 1.552-1.186 2.328 3.488-.13 6.61 1.36 7.997 3.96.741 1.386.82 2.781.767 3.688h6.436c.035-1.3-.078-3.75-1.378-6.4-.279-.576-2.128-4.248-6.331-6.35-2.625-1.325-5.084-1.473-7.168-1.622a25.5 25.5 0 0 0-4.038.062",
    "M63.995 29.747c1.692 1.046 4.483 2.406 8.11 2.73 1.666.147 6.01.522 10.038-2.129.933-.61 3.026-2.032 4.308-4.761.593-1.265.829-2.407.933-3.21h6.419a16.8 16.8 0 0 1-1.239 4.806c-2.843 6.688-9.278 9.47-11.441 10.412-8.224 3.567-15.855 1.526-18.532.785a31 31 0 0 1-7.822-3.462 37 37 0 0 0 3.4-1.5 36.6 36.6 0 0 0 5.826-3.671",
    "M32.104 41.075a64 64 0 0 0 2.145-.218c6.907-.864 12.915-3.035 17.965-5.634 1.168.733 2.346 1.474 3.514 2.206-5.006 2.94-10.003 5.878-15.008 8.817-2.87-1.727-5.747-3.445-8.616-5.171",
    "M21.01 31.002V11.354L40.85 0l16.57 9.55-6.872 3.819-9.706-5.765-13.509 7.901.027 13.657z",
    "M29.897 28.473V16.63l10.91-6.41c2.424 1.43 4.84 2.861 7.264 4.291a59 59 0 0 0-3.732 2.242 56 56 0 0 0-4.988 3.68c1.125.81 2.25 1.622 3.366 2.433a78 78 0 0 1-7.526 3.619 77 77 0 0 1-5.294 1.988",
  ];

  let graphicLoaded = $state(isFromLanding || skipEntry ? true : false);
  let deptStrokeColor = $state(
    isFromLanding || skipEntry ? "transparent" : "white",
  );
  let deptStrokeWidth = $state(isFromLanding || skipEntry ? "0" : "1.2");
  let deptFillOpacity = $state(isFromLanding || skipEntry ? "1" : "0");
  let deptTextOpacity = $state(isFromLanding ? 0 : 1);
  let outerOrbitEl = $state(null);
  let innerOrbitEl = $state(null);
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
    // Reset bypass animation flag
    window.__bypassExitAnimation = false;

    window.playGlobalExitAnimation = (targetUrl, callback) => {
      if (!isBrowser) {
        callback();
        return;
      }
      playExitAnimation(gsapInstance, targetUrl, callback);
    };

    function playExitAnimation(gsap, targetUrl, onCompleteCallback) {
      const isTargetingLanding = targetUrl === "/";
      const brandColors = [
        "#ffd344",
        "#4e00de",
        "#ff7a1a",
        "#2a0078",
        "#ffd344",
        "#5d0077",
        "#5d0077",
        "#ff7a1a",
      ];

      const tl = gsap.timeline({
        defaults: { ease: "power3.inOut" },
        onComplete: onCompleteCallback,
      });

      const titleEl = document.querySelector(".hero-title-container");
      const glowEl = document.querySelector(".hero-glow-wrapper");
      const orbitEl = document.querySelector(".orbit-system");
      const sloganEl = document.querySelector(".animate-fade-left");

      // Disable CSS animations to allow GSAP inline styles to take effect
      if (titleEl) {
        titleEl.style.opacity = "1";
        titleEl.style.animation = "none";
      }
      if (glowEl) {
        glowEl.style.opacity = "0.8";
        glowEl.style.animation = "none";
        const lineEl = glowEl.querySelector(".animate-fade-in-line");
        if (lineEl) {
          lineEl.classList.remove("animate-fade-in-line");
        }
      }
      if (sloganEl) {
        sloganEl.classList.remove("animate-fade-left", "delay-300");
        sloganEl.style.opacity = "1";
      }

      // 1. Fade out title, glow, orbit system, slogan
      if (titleEl) tl.to(titleEl, { opacity: 0, y: -30, duration: 0.8 }, 0);
      if (glowEl) tl.to(glowEl, { opacity: 0, scaleX: 0.8, duration: 0.8 }, 0);
      if (orbitEl) tl.to(orbitEl, { opacity: 0, scale: 0.9, duration: 0.8 }, 0);
      if (sloganEl) tl.to(sloganEl, { opacity: 0, x: -30, duration: 0.8 }, 0);

      if (graphicEl) {
        const paths = Array.from(graphicEl.querySelectorAll("path"));
        const emblemPaths = paths.slice(0, 8);
        const textPaths = paths.slice(8);

        if (isTargetingLanding) {
          // Morph Anticipation Flow:
          // - Slide/fade out text paths
          if (textPaths.length > 0) {
            tl.to(textPaths, { opacity: 0, x: 30, duration: 0.8 }, 0);
          }
          // - Scale down the emblem container slightly to anticipate the landing page size
          tl.to(
            graphicEl,
            { scale: 0.93, transformOrigin: "50% 50%", duration: 0.8 },
            0,
          );
          // - Animate fill colors back to white
          emblemPaths.forEach((path, i) => {
            gsap.set(path, { fill: brandColors[i] || "#ffffff" });
            tl.to(path, { fill: "#ffffff", duration: 0.8 }, 0);
          });
        } else {
          // Synchronously set stroke attributes to white/1.2 on all paths so GSAP can initialize DrawSVG
          const allDeptPaths = Array.from(graphicEl.querySelectorAll("path"));
          allDeptPaths.forEach((p) => {
            p.setAttribute("stroke", "white");
            p.setAttribute("stroke-width", "1.2");
          });

          // Animate the fill-opacity attribute of all paths directly
          if (allDeptPaths.length > 0) {
            tl.to(
              allDeptPaths,
              {
                fillOpacity: 0,
                duration: 0.3,
                ease: "power2.inOut",
              },
              0,
            );
          }

          // - Undraw emblem paths
          if (emblemPaths.length > 0) {
            tl.to(
              emblemPaths,
              {
                drawSVG: "0%",
                duration: 0.5,
                stagger: 0.03,
                ease: "power2.in",
              },
              0.2,
            );
          }

          // - Undraw text logo paths
          if (textPaths.length > 0) {
            tl.to(
              textPaths,
              {
                drawSVG: "0%",
                duration: 0.5,
                stagger: 0.01,
                ease: "power2.in",
              },
              0.2,
            );
          }

          // - Scale down container over 0.8s and fade out only at the end
          tl.to(
            graphicEl,
            {
              scale: 0.9,
              duration: 0.8,
              ease: "power3.inOut",
            },
            0,
          );
          tl.to(
            graphicEl,
            {
              opacity: 0,
              duration: 0.3,
              ease: "power2.in",
            },
            0.5,
          );
        }
      }
    }

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

      frameId = requestAnimationFrame(update);
    };
    frameId = requestAnimationFrame(update);

    // Decouple scroll handling from animation frame
    window.addEventListener("scroll", handleScroll, { passive: true });

    // Initial measurement
    updateOrbitDimensions();

    let gsapInstance;
    let orbitsDrawn = false;

    // Dynamically load GSAP for SSR safety
    import("gsap").then(async ({ gsap }) => {
      const { DrawSVGPlugin } = await import("gsap/DrawSVGPlugin");
      const { MorphSVGPlugin } = await import("gsap/MorphSVGPlugin");
      gsap.registerPlugin(DrawSVGPlugin, MorphSVGPlugin);
      gsapInstance = gsap;

      if (skipEntry) {
        if (typeof window !== "undefined") {
          window.__skipEntryAnimation = false;
        }
        deptStrokeColor = "transparent";
        deptStrokeWidth = "0";
        deptFillOpacity = "1";
        graphicLoaded = true;
        orbitsDrawn = true;
        if (outerOrbitEl) {
          gsap.set(outerOrbitEl, { drawSVG: "100%", opacity: 1 });
        }
        if (innerOrbitEl) {
          gsap.set(innerOrbitEl, { drawSVG: "100%", opacity: 1 });
        }
        return;
      }

      if (graphicEl) {
        const paths = Array.from(graphicEl.querySelectorAll("path"));
        const emblemPaths = paths.slice(0, 8);
        const textPaths = paths.slice(8);
        if (isFromLanding) {
          // --- MORPH ANIMATION (when navigating from Landing Page) ---
          // 1. Instantly set fill opacity to 1 and remove outline stroke settings
          deptFillOpacity = "1";
          deptStrokeColor = "transparent";
          deptStrokeWidth = "0";
          graphicLoaded = true;

          const deptDefaultPaths = [
            "M108.576 79.981c-22.741 11.351-37.953 24.633-42.973 34.363-4.363 8.456-3.976 15.792-3.976 15.792.077 1.312.424 5.444 2.47 9.845 4.286 8.958 12.974 12.896 17.607 15.019 22.278 10.116 58.957.232 62.509-.772 0 5.946 0 11.931-.038 17.915 0 .232.038 1.545 1.158 2.626 1.081 1.004 2.432.965 2.664.926 3.668-.424 7.413-.926 11.236-1.467 9.227-1.428 17.837-3.204 25.791-5.212.039 2.625.27 5.521.695 8.648.502 3.784 1.274 7.182 2.085 10.155-14.595 2.741-61.235 10.347-100.193-2.934-24.865-8.572-33.59-21.043-35.83-24.556-2.24-3.475-6.216-9.73-6.718-18.533-1.043-16.68 11.158-29.305 17.104-35.483C69.271 98.977 76.221 95 83.21 90.985a128 128 0 0 1 25.366-11.004",
            "M151.279 152.645v17.066c22.509-3.823 30.54-5.792 32.239-6.757.077-.077 1.737-1.043 3.9-1.158 1.196-.039 1.776.193 2.084.424.888.541 1.275 1.506 1.506 2.394 1.39 5.135 3.205 21.12 3.321 22.085 23.899-6.486 54.44-17.297 87.336-35.637a387.5 387.5 0 0 0 81.892-61.042c-3.012.27-6.178.656-9.421 1.158-9.344 1.506-17.529 3.938-24.402 6.525a452 452 0 0 1-48.031 32.51 457 457 0 0 1-38.88 20.424c-4.672-2.741-9.382-5.482-14.054-8.224l42.046-26.602-18.185-12.741c-4.711 2.818-11.39 6.872-19.537 11.506-16.023 9.15-28.764 16.447-44.787 23.783-8.997 4.093-21.468 9.228-37.027 14.286",
            "M212.089 104.344c4.132 2.818 8.186 5.675 12.317 8.494a373 373 0 0 1 34.903-19.73 416 416 0 0 1 20.271-9.344c.27-.077.695-.192 1.158-.154.965.039 1.66.54 1.892.772a563 563 0 0 1 6.023 4.209c.232.193.347.463.309.695-.039.231-.232.347-.27.424-7.491 4.48-15.02 8.958-22.549 13.437 4.44 2.973 8.842 5.907 13.205 8.88a231 231 0 0 1 22.896-12.317c10.424-4.864 18.648-7.683 23.706-9.382 9.151-3.127 16.525-5.598 26.718-7.181a119 119 0 0 1 12.433-1.313 60 60 0 0 1 2.934-7.181 64 64 0 0 1 4.556-7.954c-5.869-.463-12.857-.425-20.618.695-3.32.463-6.447 1.081-9.266 1.814-.502.078-1.544.232-2.162-.308-.734-.695-.348-2.085-.232-2.742.965-3.977 9.498-18.3 10.927-20.656-17.838 2.664-32.046 6.486-41.815 9.498-30.579 9.575-53.282 22.626-72.741 33.977a529 529 0 0 0-24.595 15.367",
            "m359.232 44.228-9.575 17.374a101 101 0 0 1 16.68-1.197c5.019.039 9.614.502 13.629 1.081.154.039.54.155.811.54.463.58.193 1.314.154 1.39-1.737 3.437-3.513 6.874-5.251 10.31 15.444-.58 29.267 6.023 35.406 17.529 3.282 6.139 3.629 12.316 3.397 16.332h28.495c.154-5.753-.348-16.603-6.101-28.34-1.235-2.548-9.421-18.803-28.031-28.108-11.621-5.869-22.509-6.525-31.737-7.182-7.259-.502-13.436-.115-17.877.27",
            "M310.622 146.699c7.491 4.633 19.846 10.656 35.908 12.085 7.374.656 26.602 2.316 44.44-9.421 4.131-2.703 13.397-8.996 19.073-21.081a48 48 0 0 0 4.131-14.209h28.417c-.579 5.367-1.93 12.935-5.482 21.274-12.587 29.614-41.081 41.931-50.657 46.101-36.409 15.791-70.193 6.757-82.046 3.475-15.367-4.363-27.066-10.657-34.633-15.328a162 162 0 0 0 15.058-6.641 162 162 0 0 0 25.791-16.255",
            "M169.425 196.853c3.128-.27 6.294-.579 9.499-.965 30.579-3.822 57.181-13.436 79.536-24.942 5.174 3.243 10.386 6.525 15.56 9.768-22.162 13.012-44.286 26.024-66.448 39.035-12.703-7.645-25.444-15.251-38.147-22.896",
            "M120.314 152.259V65.27L208.152 15l73.359 42.278-30.425 16.911-42.973-25.521-59.807 34.98.116 60.464z",
            "M159.658 141.061V88.629l48.301-28.378c10.733 6.332 21.428 12.664 32.162 18.996-5.29 2.896-10.811 6.216-16.525 9.923a249 249 0 0 0-22.085 16.293c4.981 3.591 9.961 7.182 14.903 10.772a346 346 0 0 1-33.32 16.023 340 340 0 0 1-23.436 8.803",
          ];

          const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

          // Morph paths to their original shapes in a loop to ensure 100% robustness
          emblemPaths.forEach((path, i) => {
            tl.to(
              path,
              {
                morphSVG: deptDefaultPaths[i],
                duration: 1.0,
              },
              i * 0.03, // manual stagger offset
            );
          });

          // Smoothly fade in the entire text group
          const textGroup = graphicEl.querySelector('g[filter*="filter2_d"]');
          if (textGroup) {
            tl.to(
              textGroup,
              {
                opacity: 1,
                duration: 0.6,
              },
              "-=0.6",
            );
          }
        } else {
          // --- DRAW OUTLINE ANIMATION (on initial page load / direct visit) ---
          const tl = gsap.timeline({
            defaults: { ease: "power2.out" },
            onComplete: () => {
              deptStrokeColor = "transparent";
              deptStrokeWidth = "0";
              graphicLoaded = true;
            },
          });

          // Make container visible at the start of the drawing animation
          gsap.set(graphicEl.closest(".hero-logo-container"), { opacity: 1 });

          // 1. Draw outline of logo paths
          tl.fromTo(
            paths,
            { drawSVG: "0%" },
            { drawSVG: "100%", duration: 1.5, stagger: 0.04 },
          );

          // 2. Fade in colored fills without using this.targets()
          let fillObj = { val: 0 };
          tl.to(
            fillObj,
            {
              val: 1,
              duration: 0.6,
              onUpdate: () => {
                deptFillOpacity = fillObj.val.toString();
              },
            },
            "-=0.5",
          );
        }
      }
    });

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
              // Draw orbit paths once when the section enters viewport
              if (
                !orbitsDrawn &&
                outerOrbitEl &&
                innerOrbitEl &&
                gsapInstance
              ) {
                orbitsDrawn = true;
                const tl = gsapInstance.timeline({
                  defaults: { ease: "power2.out" },
                });
                tl.fromTo(
                  outerOrbitEl,
                  { drawSVG: "0%", opacity: 0 },
                  { drawSVG: "100%", opacity: 1, duration: 1.5 },
                ).fromTo(
                  innerOrbitEl,
                  { drawSVG: "0%", opacity: 0 },
                  { drawSVG: "100%", opacity: 1, duration: 1.2 },
                  "-=0.8",
                );
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
  use:inertiaEnhance
>
  <Navbar {homeUrl} {loginUrl} {navigationItems} />

  <main>
    <!-- Hero Section -->
    <section
      class="relative isolate h-[calc(100svh-74px)] min-h-[600px] w-full overflow-hidden md:h-[896px] {skipEntry
        ? 'skip-animations'
        : ''}"
    >
      <div class="absolute inset-0 bg-gradient-to-br from-[#5d0077] to-[#2a0078] -z-10" style="view-transition-name: hero-background;">
        <picture class="contents">
          <source srcset={`${assetBase}/hero-bg.avif`} type="image/avif" />
          <source srcset={`${assetBase}/hero-bg.webp`} type="image/webp" />
          <img
            class="absolute inset-0 h-full w-full object-cover opacity-50 mix-blend-overlay"
            src={`${assetBase}/hero-bg.png`}
            alt=""
            loading="eager"
            decoding="async"
            fetchpriority="high"
          />
        </picture>
        <picture class="contents">
          <source srcset={`${assetBase}/botanical.avif`} type="image/avif" />
          <source srcset={`${assetBase}/botanical.webp`} type="image/webp" />
          <img
            class="animate-slow-pan absolute -top-[22%] -left-[20%] h-[180%] w-[170%] object-cover opacity-25 mix-blend-soft-light"
            src={`${assetBase}/botanical.png`}
            alt=""
            width="1600"
            height="1066"
            loading="eager"
            decoding="async"
            fetchpriority="high"
            style="animation-delay: {botanicalDelay};"
          />
        </picture>
      </div>

      <!-- Center Hero Graphic, Title, and Glow Wrapper -->
      <div class="hero-content-wrapper">
        <!-- Center Hero Graphic -->
        <div
          class="hero-logo-container transition-opacity duration-300 {graphicLoaded
            ? 'opacity-100'
            : 'opacity-0'}"
          style="view-transition-name: hero-logo; {!graphicLoaded
            ? 'opacity: 0;'
            : ''}"
        >
          <TalingDeptHeroGraphic
            bind:bindRef={graphicEl}
            stroke={deptStrokeColor}
            strokeWidth={deptStrokeWidth}
            fillOpacity={deptFillOpacity}
            paths={isFromLanding ? logoMarkPaths : null}
            fillColors={isFromLanding ? "white" : null}
            textOpacity={deptTextOpacity}
            class="animate-float-logo h-auto w-full drop-shadow-2xl"
            style="width: 100%; max-width: var(--hero-max-width, 280px); height: auto;"
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
        style="position: absolute; view-transition-name: star-large; animation-delay: {largeStarDelay};"
      />
      <img
        src={`${assetBase}/star-small.svg`}
        alt=""
        class="star-small animate-float-small pointer-events-none opacity-80 drop-shadow-2xl"
        width="375"
        height="404"
        style="position: absolute; view-transition-name: star-small; animation-delay: {smallStarDelay};"
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
                <svg
                  class="pointer-events-none absolute inset-0 h-[900px] w-[1440px] overflow-visible"
                  viewBox="0 0 1440 900"
                >
                  <!-- Outer Orbit -->
                  <ellipse
                    bind:this={outerOrbitEl}
                    cx="720"
                    cy="450"
                    rx="630"
                    ry="380"
                    transform="rotate(-28, 720, 450)"
                    stroke="rgba(255, 255, 255, 0.22)"
                    stroke-width="1.5"
                    fill="none"
                    class="orbit-ring-outer-svg {selectedDeptId
                      ? 'blurred-out'
                      : ''}"
                    style="opacity: {outerRingOpacity}; transition: filter 0.4s ease, opacity 0.4s ease;"
                  />
                  <!-- Inner Orbit -->
                  <ellipse
                    bind:this={innerOrbitEl}
                    cx="720"
                    cy="450"
                    rx="420"
                    ry="240"
                    transform="rotate(-28, 720, 450)"
                    stroke="rgba(255, 211, 68, 0.28)"
                    stroke-width="1.2"
                    fill="none"
                    class="orbit-ring-inner-svg {selectedDeptId
                      ? 'blurred-out'
                      : ''}"
                    style="opacity: {innerRingOpacity}; transition: filter 0.4s ease, opacity 0.4s ease;"
                  />
                </svg>

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
                    style="position: absolute; left: 0; top: 0; transform: translate({x -
                      34}px, {y -
                      24}px) scale({bp.scale}); opacity: {bp.opacity}; pointer-events: {bp.opacity >
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
            <div class="relative w-full" transition:fade={{ duration: 250 }}>
              <!-- Close Button -->
              <button
                type="button"
                onclick={handleDeselect}
                class="absolute -top-4 right-0 rounded-full p-2 text-white/70 transition-colors hover:bg-white/10 hover:text-white"
                aria-label="Close details"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-6 w-6"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>

              <span
                class="text-xs font-bold tracking-widest text-[#ffd344] uppercase"
                >Fokus Departemen</span
              >
              <h3 class="mt-2 pr-10 text-4xl font-extrabold text-white">
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
  .orbit-ring-outer-svg.blurred-out,
  .orbit-ring-inner-svg.blurred-out,
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
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 100%;
    z-index: 20;
    box-shadow: -10px 0 30px rgba(0, 0, 0, 0.3);
    background-color: rgba(30, 0, 88, 0.95);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .detail-panel.panel-open {
    max-width: 100%;
    padding: 2rem;
    opacity: 1;
    transform: translateX(0);
    pointer-events: auto;
    border-left: 1px solid rgba(255, 255, 255, 0.1);
  }

  @media (min-width: 768px) {
    .detail-panel.panel-open {
      max-width: 420px;
    }
  }

  .detail-panel.panel-closed {
    max-width: 0px;
    padding: 2rem 0;
    opacity: 0;
    transform: translateX(100%);
    border-left: 1px solid transparent;
    pointer-events: none;
  }

  :global(.skip-animations) .hero-title-container {
    animation: none !important;
    opacity: 1;
  }
  :global(.skip-animations) .animate-fade-in-line {
    animation: none !important;
    opacity: 1;
  }
  :global(.skip-animations) .animate-fade-left {
    animation: none !important;
    opacity: 1;
  }
  :global(.skip-animations) .animate-slow-pan {
    animation: none !important;
  }
</style>
