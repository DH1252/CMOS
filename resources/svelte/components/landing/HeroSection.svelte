<script>
  import { onMount } from "svelte";

  let { assetBase = "/images/figma-taling" } = $props();

  let logoMarkLoaded = $state(false);
  let logoTextLoaded = $state(false);
  const logoLoaded = $derived(logoMarkLoaded && logoTextLoaded);

  let logoMarkEl = $state(null);
  let logoTextEl = $state(null);

  onMount(() => {
    if (logoMarkEl?.complete) {
      logoMarkLoaded = true;
    }
    if (logoTextEl?.complete) {
      logoTextLoaded = true;
    }
  });
</script>

<section
  class="relative h-[896px] w-full overflow-hidden bg-gradient-to-br from-[#5d0077] to-[#2a0078]"
>
  <picture class="contents">
    <source srcset={`${assetBase}/hero-bg.avif`} type="image/avif" />
    <source srcset={`${assetBase}/hero-bg.webp`} type="image/webp" />
    <img
      class="absolute inset-0 h-full w-full object-cover opacity-50 mix-blend-overlay"
      src={`${assetBase}/hero-bg.png`}
      alt=""
    />
  </picture>
  <picture class="contents">
    <source srcset={`${assetBase}/botanical.avif`} type="image/avif" />
    <source srcset={`${assetBase}/botanical.webp`} type="image/webp" />
    <img
      class="absolute -top-[22%] -left-[20%] h-[180%] w-[170%] object-cover opacity-25 mix-blend-soft-light"
      src={`${assetBase}/botanical.png`}
      alt=""
    />
  </picture>

  <div
    class="absolute top-[120px] left-1/2 flex w-full max-w-[398px] flex-col items-center {logoLoaded
      ? 'animate-fade-scale'
      : 'opacity-0'}"
  >
    <div class="animate-float-logo flex w-full flex-col items-center">
      <img
        bind:this={logoMarkEl}
        onload={() => {
          logoMarkLoaded = true;
        }}
        src={`${assetBase}/logo-mark.svg`}
        alt="Logo Mark"
        class="h-auto w-full drop-shadow-xl"
      />
      <img
        bind:this={logoTextEl}
        onload={() => {
          logoTextLoaded = true;
        }}
        src={`${assetBase}/text-logo.svg`}
        alt="Logo Text"
        class="-mt-3 h-auto w-[330px] drop-shadow-md"
      />
    </div>
  </div>

  <img
    src={`${assetBase}/star-large.svg`}
    alt=""
    class="star-large animate-float-large pointer-events-none opacity-80 drop-shadow-2xl"
  />
  <img
    src={`${assetBase}/star-small.svg`}
    alt=""
    class="star-small animate-float-small pointer-events-none opacity-80 drop-shadow-2xl"
  />

  <h1
    class="animate-fade-up absolute top-[504px] left-1/2 font-['Playfair_Display'] text-5xl whitespace-nowrap text-white drop-shadow-[0_0_20px_rgba(255,255,255,0.8)] md:text-[96px]"
  >
    dari kita untuk kita
  </h1>

  <div
    class="animate-fade-in absolute top-[623px] left-1/2 h-[22px] w-full max-w-[534px] bg-gradient-to-r from-transparent via-[#ff7a1a] to-transparent opacity-80 blur-[2px]"
  ></div>

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

  .animate-fade-scale {
    animation: scaleFadeIn 1000ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
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
    .animate-fade-scale,
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
</style>
