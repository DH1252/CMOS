<script>
  import { onMount, onDestroy } from "svelte";
  import { router } from "@inertiajs/svelte";
  import TalingLogoMark from "./TalingLogoMark.svelte";
  import TalingTextLogo from "./TalingTextLogo.svelte";

  let { assetBase = "/images/figma-taling" } = $props();

  let logoMarkEl = $state(null);
  let logoTextEl = $state(null);
  let skipEntry = $state(
    typeof window !== "undefined" && !!window.__skipEntryAnimation,
  );

  let logoLoaded = $state(skipEntry ? true : false);

  const getElapsedDelay = (periodMs, type) => {
    if (typeof window !== "undefined") {
      // Use captured time from previous page if available and fresh (under 2 seconds)
      if (window.__lastAnimationTimes && window.__lastAnimationTimes[type]) {
        const { time, timestamp } = window.__lastAnimationTimes[type];
        const elapsedSinceCapture = Date.now() - timestamp;
        if (elapsedSinceCapture < 2000) {
          return `-${(time % periodMs) / 1000}s`;
        }
      }

      if (!window.__initialAnimationTimestamp) {
        window.__initialAnimationTimestamp = Date.now();
      }
      const elapsedMs = Date.now() - window.__initialAnimationTimestamp;
      return `-${(elapsedMs % periodMs) / 1000}s`;
    }
    return "0s";
  };

  const largeStarDelay = getElapsedDelay(8000, "starLarge");
  const smallStarDelay = getElapsedDelay(10000, "starSmall");
  const botanicalDelay = getElapsedDelay(25000, "botanical");

  // Bind values for drawing animation
  let strokeColor = $state(skipEntry ? "transparent" : "white");
  let strokeWidth = $state(skipEntry ? "0" : "1.2");
  let fillOpacity = $state(skipEntry ? "1" : "0");
  let currentFillColors = $state(null);

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

  let gsapInstance;

  onMount(() => {
    if (typeof window === "undefined") return;

    // Reset bypass animation flag
    window.__bypassExitAnimation = false;

    // Register global exit animation for inertiaEnhance
    window.playGlobalExitAnimation = (targetUrl, callback) => {
      import("gsap")
        .then(async ({ gsap }) => {
          const { DrawSVGPlugin } = await import("gsap/DrawSVGPlugin");
          const { MorphSVGPlugin } = await import("gsap/MorphSVGPlugin");
          gsap.registerPlugin(DrawSVGPlugin, MorphSVGPlugin);

          playExitAnimation(gsap, targetUrl, callback);
        })
        .catch(() => {
          callback();
        });
    };

    function playExitAnimation(gsap, targetUrl, onCompleteCallback) {
      const tl = gsap.timeline({
        defaults: { ease: "power3.in" },
        onComplete: onCompleteCallback,
      });

      const titleEl = document.querySelector(".hero-title");
      const glowEl = document.querySelector(".hero-glow-wrapper");
      const sloganEl = document.querySelector(".animate-fade-left");

      // Disable CSS animations to allow GSAP inline styles to take effect
      if (titleEl) {
        titleEl.classList.remove("animate-fade-up");
        titleEl.style.opacity = "1";
      }
      if (glowEl) {
        glowEl.classList.remove("animate-fade-in");
        glowEl.style.opacity = "0.8";
      }
      if (sloganEl) {
        sloganEl.classList.remove("animate-fade-left");
        sloganEl.style.opacity = "1";
      }

      // 1. Fade out title, glow, slogan
      if (titleEl) tl.to(titleEl, { opacity: 0, y: -30, duration: 0.8 }, 0);
      if (glowEl) tl.to(glowEl, { opacity: 0, scaleX: 0.8, duration: 0.8 }, 0);
      if (sloganEl) tl.to(sloganEl, { opacity: 0, x: -30, duration: 0.8 }, 0);

      const textPaths = logoTextEl ? logoTextEl.querySelectorAll("path") : [];
      const emblemPaths = logoMarkEl ? logoMarkEl.querySelectorAll("path") : [];

      // Synchronously set stroke attributes to white/1.2 on all paths so GSAP can initialize DrawSVG
      if (logoMarkEl) {
        logoMarkEl.querySelectorAll("path").forEach((p) => {
          p.setAttribute("stroke", "white");
          p.setAttribute("stroke-width", "1.2");
        });
      }
      if (logoTextEl) {
        logoTextEl.querySelectorAll("path").forEach((p) => {
          p.setAttribute("stroke", "white");
          p.setAttribute("stroke-width", "1.2");
        });
      }

      // Animate the fill-opacity attribute of all paths directly
      const allPaths = [
        ...(logoMarkEl ? Array.from(logoMarkEl.querySelectorAll("path")) : []),
        ...(logoTextEl ? Array.from(logoTextEl.querySelectorAll("path")) : []),
      ];
      if (allPaths.length > 0) {
        tl.to(
          allPaths,
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
      if (logoMarkEl) {
        tl.to(
          logoMarkEl,
          {
            scale: 0.9,
            duration: 0.8,
            ease: "power3.inOut",
          },
          0,
        );
        tl.to(
          logoMarkEl,
          {
            opacity: 0,
            duration: 0.3,
            ease: "power2.in",
          },
          0.5,
        );
      }
      if (logoTextEl) {
        tl.to(
          logoTextEl,
          {
            scale: 0.9,
            duration: 0.8,
            ease: "power3.inOut",
          },
          0,
        );
        tl.to(
          logoTextEl,
          {
            opacity: 0,
            duration: 0.3,
            ease: "power2.in",
          },
          0.5,
        );
      }
    }

    // Dynamically import GSAP for entry animation
    import("gsap").then(async ({ gsap }) => {
      const { DrawSVGPlugin } = await import("gsap/DrawSVGPlugin");
      const { MorphSVGPlugin } = await import("gsap/MorphSVGPlugin");
      gsap.registerPlugin(DrawSVGPlugin, MorphSVGPlugin);
      gsapInstance = gsap;

      if (skipEntry) {
        if (typeof window !== "undefined") {
          window.__skipEntryAnimation = false;
        }
        strokeColor = "transparent";
        strokeWidth = "0";
        fillOpacity = "1";
        logoLoaded = true;
        return;
      }

      // Wait a tick if Svelte 5 DOM bindings are not fully propagated yet
      if (!logoMarkEl || !logoTextEl) {
        await new Promise((resolve) => setTimeout(resolve, 50));
        if (!logoMarkEl || !logoTextEl) {
          logoLoaded = true; // Fallback
          return;
        }
      }

      const logoMarkPaths = logoMarkEl.querySelectorAll("path");
      const logoTextPaths = logoTextEl.querySelectorAll("path");

      // --- DRAW OUTLINE ANIMATION ---
      const tl = gsap.timeline({
        defaults: { ease: "power2.out" },
        onComplete: () => {
          strokeColor = "transparent";
          strokeWidth = "0";
          logoLoaded = true;
        },
      });

      // Make container visible at the start of the drawing animation
      gsap.set(logoMarkEl.closest(".hero-logo-container"), { opacity: 1 });

      // 1. Draw outline of logo mark
      tl.fromTo(
        logoMarkPaths,
        { drawSVG: "0%" },
        { drawSVG: "100%", duration: 1.2, stagger: 0.05 },
      );

      // 2. Draw outline of text logo
      tl.fromTo(
        logoTextPaths,
        { drawSVG: "0%" },
        { drawSVG: "100%", duration: 1.0, stagger: 0.02 },
        "-=0.6",
      );

      // 3. Smoothly fade in fills and thin the strokes
      let animObj = { fill: 0, strokeW: 1.2 };
      tl.to(
        animObj,
        {
          fill: 1,
          strokeW: 0,
          duration: 0.8,
          ease: "power2.out",
          onUpdate: () => {
            fillOpacity = animObj.fill.toString();
            strokeWidth = animObj.strokeW.toString();
          },
        },
        "-=0.4",
      );
    });
  });

  onDestroy(() => {
    if (typeof window !== "undefined") {
      window.__lastAnimationTimes = {};
      const captureAnim = (selector, key, animNameSub) => {
        const el = document.querySelector(selector);
        if (el) {
          const anims = el.getAnimations();
          const anim = anims.find(
            (a) => a.animationName && a.animationName.includes(animNameSub),
          );
          if (anim && anim.currentTime !== null) {
            window.__lastAnimationTimes[key] = {
              time: anim.currentTime,
              timestamp: Date.now(),
            };
          }
        }
      };

      captureAnim(".animate-slow-pan", "botanical", "slowPan");
      captureAnim(".star-large", "starLarge", "floatLarge");
      captureAnim(".star-small", "starSmall", "floatSmall");
    }
  });
</script>

<section
  class="relative isolate h-[896px] w-full overflow-hidden {skipEntry
    ? 'skip-animations'
    : ''}"
>
  <div
    class="absolute inset-0 -z-10 bg-gradient-to-br from-[#5d0077] to-[#2a0078]"
    style="view-transition-name: hero-background;"
  >
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
      class="hero-logo-container transition-opacity duration-300 {logoLoaded
        ? 'opacity-100'
        : 'opacity-0'}"
      style={!logoLoaded ? "opacity: 0;" : ""}
    >
      <div class="animate-float-logo flex w-full flex-col items-center">
        <TalingLogoMark
          bind:bindRef={logoMarkEl}
          stroke={strokeColor}
          {strokeWidth}
          {fillOpacity}
          paths={null}
          fillColors={null}
          class="h-auto w-full drop-shadow-xl"
        />
        <TalingTextLogo
          bind:bindRef={logoTextEl}
          stroke={strokeColor}
          {strokeWidth}
          {fillOpacity}
          style=""
          class="-mt-3 h-auto w-[83%] max-w-[330px] drop-shadow-md"
        />
      </div>
    </div>

    <!-- Title -->
    <h1 class="hero-title animate-fade-up">
      dari kita<br class="md:hidden" /> untuk kita
    </h1>

    <!-- Glow Line under title -->
    <div class="hero-glow-wrapper animate-fade-in">
      <div
        class="h-full w-full bg-gradient-to-r from-transparent via-[#ff7a1a] to-transparent blur-[2px]"
      ></div>
    </div>
  </div>

  <img
    src={`${assetBase}/star-large.svg`}
    alt=""
    class="star-large animate-float-large pointer-events-none opacity-80 drop-shadow-2xl"
    style="view-transition-name: star-large; animation-delay: {largeStarDelay};"
  />
  <img
    src={`${assetBase}/star-small.svg`}
    alt=""
    class="star-small animate-float-small pointer-events-none opacity-80 drop-shadow-2xl"
    style="view-transition-name: star-small; animation-delay: {smallStarDelay};"
  />

  <div
    class="animate-fade-left absolute bottom-6 left-6 flex items-center gap-4 font-bold tracking-wider text-white md:bottom-[36px] md:left-[49px]"
  >
    <img
      src={`${assetBase}/logo-mark.svg`}
      alt=""
      class="w-[60px] md:w-[90px]"
    />
    <img
      src={`${assetBase}/text-logo.svg`}
      alt="Sentra Sinergi"
      class="w-[50px] md:w-[80px]"
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

<style>
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
    padding-top: 2rem;
    padding-bottom: 4rem;
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
    max-width: 220px;
    margin: 0 auto;
    pointer-events: auto;
  }

  @media (min-width: 768px) {
    .hero-logo-container {
      position: absolute;
      top: 120px;
      left: 50%;
      transform: translateX(-50%);
      max-width: 398px;
      margin: 0;
    }
  }

  .hero-title {
    font-family: "Playfair Display", "Playfair_Display", Georgia, serif;
    font-size: 2.5rem;
    line-height: 1.2;
    text-align: center;
    color: #ffffff;
    text-shadow: 0px 0px 20px rgba(255, 255, 255, 0.8);
    white-space: normal;
  }

  @media (min-width: 768px) {
    .hero-title {
      position: absolute;
      top: 504px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 96px;
      line-height: 125px;
      white-space: nowrap;
    }
  }

  .hero-glow-wrapper {
    position: relative;
    width: 100%;
    max-width: 240px;
    height: 12px;
    margin: 0 auto;
    opacity: 0.8;
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

  .animate-fade-up {
    animation: fadeInUp 1000ms cubic-bezier(0.16, 1, 0.3, 1) 200ms forwards;
    opacity: 0;
  }
  .animate-fade-in {
    animation: fadeIn 1000ms cubic-bezier(0.16, 1, 0.3, 1) 400ms forwards;
    opacity: 0;
  }
  .animate-fade-left {
    animation: fadeInLeft 1000ms cubic-bezier(0.16, 1, 0.3, 1) 500ms forwards;
    opacity: 0;
  }

  @media (min-width: 768px) {
    .animate-fade-up {
      animation: fadeInUpCenter 1000ms cubic-bezier(0.16, 1, 0.3, 1) 200ms
        forwards;
    }
    .animate-fade-in {
      animation: fadeInCenter 1000ms cubic-bezier(0.16, 1, 0.3, 1) 400ms
        forwards;
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

  @keyframes scaleFadeIn {
    from {
      opacity: 0;
      transform: scale(0.95);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  @keyframes scaleFadeInCenter {
    from {
      opacity: 0;
      transform: translate(-50%, 0) scale(0.95);
    }
    to {
      opacity: 1;
      transform: translate(-50%, 0) scale(1);
    }
  }

  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(16px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes fadeInUpCenter {
    from {
      opacity: 0;
      transform: translate(-50%, 16px);
    }
    to {
      opacity: 1;
      transform: translate(-50%, 0);
    }
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 0.8;
    }
  }

  @keyframes fadeInCenter {
    from {
      opacity: 0;
      transform: translate(-50%, 0);
    }
    to {
      opacity: 0.8;
      transform: translate(-50%, 0);
    }
  }

  @keyframes fadeInLeft {
    from {
      opacity: 0;
      transform: translateX(-16px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
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

  @keyframes floatLogo {
    0% {
      transform: translateY(0px) scale(1);
    }
    100% {
      transform: translateY(-8px) scale(1.015);
      filter: drop-shadow(0 20px 20px rgba(255, 255, 255, 0.08));
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .animate-fade-up,
    .animate-fade-in,
    .animate-fade-left {
      animation: none !important;
      opacity: 1 !important;
      transform: translate(-50%, 0) !important;
    }
    .animate-fade-left {
      transform: none !important;
    }
    .animate-float-large,
    .animate-float-small,
    .animate-float-logo {
      animation: none !important;
    }
  }

  :global(.skip-animations) .animate-fade-up,
  :global(.skip-animations) .animate-fade-left {
    animation: none !important;
    opacity: 1 !important;
  }
  :global(.skip-animations) .animate-fade-in {
    animation: none !important;
    opacity: 0.8 !important;
  }
</style>
