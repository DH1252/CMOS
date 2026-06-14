<script>
  import { onMount } from "svelte";
  import { router } from "@inertiajs/svelte";
  import TalingLogoMark from "./TalingLogoMark.svelte";
  import TalingTextLogo from "./TalingTextLogo.svelte";

  let { assetBase = "/images/figma-taling" } = $props();

  let logoMarkEl = $state(null);
  let logoTextEl = $state(null);
  let skipEntry = $state(
    typeof window !== "undefined" && !!window.__skipEntryAnimation,
  );

  const isFromDepartment =
    typeof window !== "undefined" &&
    (window.__lastPathname === "/departemen" ||
      window.__lastPathname?.startsWith("/departemen/"));
  let logoLoaded = $state(true);

  const now = typeof Date !== "undefined" ? Date.now() : 0;
  const largeStarDelay = `-${(now % 8000) / 1000}s`;
  const smallStarDelay = `-${(now % 10000) / 1000}s`;
  const botanicalDelay = `-${(now % 25000) / 1000}s`;

  // Bind values for drawing animation
  let strokeColor = $state(isFromDepartment ? "transparent" : "white");
  let strokeWidth = $state(isFromDepartment ? "0" : "1.2");
  let fillOpacity = $state(isFromDepartment ? "1" : "0");

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
      const isTargetingDepartment =
        targetUrl === "/departemen" || targetUrl.startsWith("/departemen/");

      const tl = gsap.timeline({
        defaults: { ease: "power3.inOut" },
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

      if (isTargetingDepartment) {
        // Morph Anticipation Flow:
        // - Slide/fade out text logo paths
        if (textPaths.length > 0) {
          tl.to(textPaths, { opacity: 0, x: 30, duration: 0.8 }, 0);
        }
        // - Scale up the emblem container slightly to anticipate the department page size
        if (logoMarkEl) {
          tl.to(
            logoMarkEl,
            { scale: 1.0757, transformOrigin: "50% 50%", duration: 0.8 },
            0,
          );
        }
        // - Fade the paths' fill colors to the department badge colors
        if (emblemPaths.length > 0) {
          emblemPaths.forEach((path, i) => {
            tl.to(
              path,
              { fill: brandColors[i] || "#ffffff", duration: 0.8 },
              0,
            );
          });
        }
      } else {
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
          ...(logoMarkEl
            ? Array.from(logoMarkEl.querySelectorAll("path"))
            : []),
          ...(logoTextEl
            ? Array.from(logoTextEl.querySelectorAll("path"))
            : []),
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
      const logoMarkGroup = logoMarkEl.querySelector("g");

      // Mathematically pre-scaled and pre-translated emblem paths (fitting the 97.895 x 54.246 viewBox)
      const deptBadgePathsTransformed = [
        "M 18.368 14.680 C 13.233 17.242 9.799 20.242 8.666 22.440 C 7.681 24.349 7.768 26.005 7.768 26.005 C 7.785 26.301 7.864 27.230 8.326 28.224 C 9.294 30.247 11.253 31.135 12.299 31.616 C 17.330 33.900 25.613 31.672 26.416 31.498 L 26.407 35.543 C 26.407 35.595 26.416 35.891 26.678 36.135 C 26.922 36.361 27.227 36.353 27.279 36.344 C 28.107 36.248 28.952 36.135 29.815 36.014 A 63.902 63.902 0 0 0 35.639 34.838 Q 35.648 35.430 35.805 37.382 C 35.918 38.236 36.093 39.004 36.276 39.675 C 29.981 40.293 19.452 42.010 10.645 39.013 C 5.031 37.078 3.062 34.262 2.556 33.468 C 2.050 32.684 1.152 31.274 1.039 29.286 C 0.804 25.520 3.560 22.671 4.903 21.278 C 6.508 19.622 8.078 18.724 9.656 17.818 A 28.902 28.902 0 0 1 15.383 15.334",
        "M 28.011 31.087 L 28.011 34.941 C 33.093 34.078 34.907 33.633 35.290 33.415 C 35.308 33.398 35.683 33.180 36.171 33.154 C 36.441 33.145 36.572 33.197 36.642 33.249 C 36.842 33.371 36.929 33.589 36.982 33.790 C 37.295 34.949 37.705 38.559 37.731 38.777 C 43.128 37.312 50.024 34.871 57.452 30.730 A 87.498 87.498 0 0 0 75.943 16.947 C 75.263 17.007 74.548 17.095 73.816 17.208 C 71.706 17.548 69.858 18.097 68.306 18.681 A 102.062 102.062 0 0 1 57.461 26.022 A 103.191 103.191 0 0 1 48.681 30.634 C 47.626 30.015 46.563 29.396 45.508 28.777 L 55.002 22.770 L 50.896 19.893 C 49.832 20.530 48.324 21.445 46.484 22.491 C 42.866 24.557 39.989 26.205 36.371 27.861 C 34.340 28.786 31.524 29.945 28.011 31.087",
        "M 41.742 20.181 C 42.675 20.817 43.590 21.462 44.523 22.099 A 84.223 84.223 0 0 1 52.404 17.644 A 93.933 93.933 0 0 1 56.981 15.534 C 57.042 15.517 57.138 15.491 57.243 15.499 C 57.461 15.508 57.617 15.621 57.670 15.673 A 127.125 127.125 0 0 1 59.030 16.624 C 59.082 16.667 59.108 16.728 59.100 16.781 C 59.091 16.833 59.047 16.859 59.039 16.877 C 57.347 17.888 55.647 18.899 53.947 19.911 C 54.950 20.582 55.944 21.244 56.929 21.916 A 52.160 52.160 0 0 1 62.099 19.135 C 64.452 18.036 66.309 17.400 67.452 17.016 C 69.518 16.310 71.183 15.752 73.484 15.395 A 26.870 26.870 0 0 1 76.292 15.098 A 13.548 13.548 0 0 1 76.954 13.477 A 14.451 14.451 0 0 1 77.983 11.681 C 76.658 11.576 75.080 11.585 73.328 11.838 C 72.578 11.942 71.872 12.082 71.235 12.247 C 71.122 12.265 70.887 12.300 70.747 12.178 C 70.581 12.021 70.668 11.707 70.695 11.558 C 70.913 10.660 72.839 7.426 73.162 6.894 C 69.134 7.496 65.926 8.359 63.720 9.039 C 56.815 11.201 51.689 14.148 47.295 16.711 A 119.448 119.448 0 0 0 41.742 20.181",
        "M 359.226 44.236 L 349.651 61.608 A 101.378 101.378 0 0 1 379.958 61.492 A 1.328 1.328 0 0 1 380.768 62.033 C 381.233 62.608 380.963 63.343 380.923 63.423 C 379.183 66.858 377.408 70.293 375.673 73.729 C 391.114 73.153 404.935 79.749 411.075 91.260 C 414.356 97.395 414.705 103.571 414.471 107.586 L 442.963 107.586 C 443.118 101.831 442.618 90.985 436.863 79.254 C 435.627 76.704 427.442 60.448 408.835 51.142 C 397.214 45.276 386.328 44.621 377.103 43.962 A 112.888 112.888 0 0 0 359.226 44.236",
        "M 310.618 146.699 C 318.108 151.330 330.464 157.350 346.521 158.785 C 353.896 159.435 373.127 161.096 390.959 149.360 C 395.089 146.659 404.355 140.364 410.031 128.283 C 412.656 122.682 413.701 117.627 414.161 114.072 L 442.578 114.072 A 74.374 74.374 0 0 1 437.093 135.348 C 424.507 164.956 396.019 177.272 386.444 181.442 C 350.036 197.233 316.253 188.198 304.402 184.917 A 137.237 137.237 0 0 1 269.774 169.591 A 163.799 163.799 0 0 0 284.826 162.950 A 162.028 162.028 0 0 0 310.618 146.699",
        "M 169.436 196.848 A 283.328 283.328 0 0 0 178.932 195.883 C 209.510 192.058 236.107 182.447 258.463 170.941 C 263.634 174.186 268.849 177.467 274.020 180.707 C 251.858 193.723 229.737 206.729 207.579 219.740 C 194.874 212.095 182.137 204.489 169.436 196.848",
        "M 120.323 152.255 L 120.323 65.273 L 208.155 15.009 L 281.510 57.287 L 251.088 74.194 L 208.120 48.672 L 148.315 83.650 L 148.435 144.109 Z",
        "M 159.666 141.059 L 159.666 88.630 L 207.965 60.253 C 218.696 66.584 229.391 72.919 240.122 79.249 A 261.193 261.193 0 0 0 223.601 89.175 A 247.912 247.912 0 0 0 201.519 105.466 C 206.499 109.052 211.480 112.646 216.420 116.237 A 345.306 345.306 0 0 1 183.103 132.258 A 340.879 340.879 0 0 1 159.666 141.059",
      ];

      // Solid color representations of the department badge brand colors
      // (Inherited from component scope brandColors)

      if (isFromDepartment) {
        // --- MORPH ANIMATION (when navigating from Department Page on refresh/history) ---
        // Store original d attributes dynamically in data-original-d before changing them
        logoMarkPaths.forEach((path, i) => {
          const originalD = path.getAttribute("d") || "";
          path.setAttribute("data-original-d", originalD);
          path.setAttribute("d", deptBadgePathsTransformed[i]);
          path.setAttribute("fill", brandColors[i]); // Start with brand colors
        });

        // Set text paths to opacity 0 initially so they fade in during the morph
        gsap.set(logoTextPaths, { opacity: 0 });

        const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

        // Morph paths and transition colors to white in a loop
        logoMarkPaths.forEach((path, i) => {
          const originalD = path.getAttribute("data-original-d") || "";
          tl.to(
            path,
            {
              morphSVG: originalD,
              fill: "#ffffff", // Animate to white
              duration: 1.0,
            },
            i * 0.03, // manual stagger offset
          );
        });

        // Animate the container element's scale and position concurrently to keep emblem center static
        const containerEl = logoMarkEl.closest(".hero-logo-container");
        if (containerEl) {
          const isMobile = window.innerWidth < 768;

          if (isMobile) {
            const startScale = 210.97 / 204.62; // 1.0310
            gsap.set(containerEl, { transformOrigin: "50% 44.5px" });
            tl.fromTo(
              containerEl,
              { scale: startScale, xPercent: 9.2, x: 0, y: -70.44 },
              {
                scale: 1,
                xPercent: 0,
                x: 0,
                y: 0,
                duration: 1.0,
                ease: "power3.out",
              },
              0,
            );
          } else {
            const startScale = 398 / 370; // 1.0757
            gsap.set(containerEl, { transformOrigin: "50% 86.3px" });
            tl.fromTo(
              containerEl,
              { scale: startScale, xPercent: -50, x: 55, y: 24.04 },
              {
                scale: 1,
                xPercent: -50,
                x: 0,
                y: 0,
                duration: 1.0,
                ease: "power3.out",
              },
              0,
            );
          }
        }

        // Smoothly fade in the text paths
        tl.to(
          logoTextPaths,
          {
            opacity: 1,
            duration: 0.6,
          },
          "-=0.6",
        );
      } else {
        // --- DRAW OUTLINE ANIMATION ---
        const tl = gsap.timeline({
          defaults: { ease: "power2.out" },
          onComplete: () => {
            strokeColor = "transparent";
            strokeWidth = "0";
          },
        });

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
      }
    });
  });
</script>

<section
  class="relative h-[896px] w-full overflow-hidden bg-gradient-to-br from-[#5d0077] to-[#2a0078] {skipEntry
    ? 'skip-animations'
    : ''}"
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
      style="view-transition-name: hero-bg-texture;"
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
      style="view-transition-name: hero-botanical; animation-delay: {botanicalDelay};"
    />
  </picture>

  <!-- Center Hero Graphic, Title, and Glow Wrapper -->
  <div class="hero-content-wrapper">
    <!-- Center Hero Graphic -->
    <div class="hero-logo-container" style="view-transition-name: hero-logo;">
      <div class="animate-float-logo flex w-full flex-col items-center">
        <TalingLogoMark
          bind:bindRef={logoMarkEl}
          stroke={strokeColor}
          {strokeWidth}
          {fillOpacity}
          class="h-auto w-full drop-shadow-xl"
        />
        <TalingTextLogo
          bind:bindRef={logoTextEl}
          stroke={strokeColor}
          {strokeWidth}
          {fillOpacity}
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
    opacity: 1;
  }
  :global(.skip-animations) .animate-fade-in {
    animation: none !important;
    opacity: 0.8;
  }

  .animate-slow-pan {
    animation: slowPan 25s ease-in-out infinite alternate;
    transform-origin: center center;
  }

  @keyframes slowPan {
    0% {
      transform: scale(1) translate(0, 0);
    }
    100% {
      transform: scale(1.08) translate(2%, -1%);
    }
  }

  :global(.skip-animations) .animate-slow-pan {
    animation: none !important;
  }
</style>
