<script>
  import { onMount } from "svelte";
  import brandLogo from "../images/logokabinet.png?enhanced&w=80;160";
  import himatekkomGallery from "../images/himatekkom.jpg?enhanced&w=720;1080";
  import logoGallery from "../images/logokabinet.png?enhanced&w=720;1080";
  import OptimizedImage from "./components/OptimizedImage.svelte";
  import TerminalHeroCanvas from "./components/TerminalHeroCanvas.svelte";
  import TerminalTextReveal from "./components/TerminalTextReveal.svelte";
  import { logokabinetAscii } from "./lib/logokabinet-ascii.js";
  import {
    ArrowRight,
    Gauge,
    Handshake,
    LogIn,
    Menu,
    Rocket,
  } from "lucide-svelte";

  let {
    appName = "CMOS",
    organizationName = "HIMATEKKOM ITS",
    themeColor = "purple",
    themeVariables = null,
    homeUrl = "/",
    loginUrl = "/login",
    infoUrl = "/informasi",
    latestInfo = [],
    logoUrl = "/images/logokabinet.png",
    navigation = null,
    hero = null,
    quickFacts = null,
    profileSection = null,
    programSection = null,
    informationSection = null,
    ctaSection = null,
    footer = null,
  } = $props();

  const programGroupIcons = {
    Optimalisasi: Gauge,
    Kolaborasi: Handshake,
    Ekspansi: Rocket,
  };

  const resolvedNavigation = $derived(
    navigation ?? [
      { href: "#profil", label: "Profil Organisasi" },
      { href: "#program-kerja", label: "Program Kerja" },
      { href: "#informasi", label: "Informasi" },
    ],
  );

  const resolvedHero = $derived(
    hero ?? {
      titleVariants: ["Kabinet Sentra Sinergi", "HIMATEKKOM ITS"],
      description:
        "Kabinet Sentra Sinergi menjaga publikasi, dokumentasi, dan akses internal melalui satu sistem yang rapi dan mudah dipantau.",
      actions: [
        { href: "#informasi", label: "Buka arsip publik", variant: "primary" },
        { href: "#profil", label: "Profil organisasi", variant: "secondary" },
      ],
    },
  );

  const resolvedQuickFacts = $derived(
    quickFacts ?? [
      "Website resmi HIMATEKKOM ITS 2026.",
      "Kabinet Sentra Sinergi menjaga transparansi, dokumentasi, dan kolaborasi organisasi.",
      `${appName} dipakai pengurus untuk kerja operasional sehari-hari.`,
    ],
  );

  const resolvedProfileSection = $derived(
    profileSection ?? {
      title: "Profil organisasi",
      description:
        "HIMATEKKOM ITS 2026 menjalankan publikasi dan operasional kabinet dengan alur yang terstruktur, terdokumentasi, dan mudah dibaca oleh pengurus maupun publik.",
      visionLabel: "Visi",
      vision:
        "Menjadikan HIMATEKKOM ITS sebagai himpunan yang progresif, inklusif, dan berdampak, dengan tata kelola organisasi yang profesional serta budaya kolaborasi yang kuat dalam semangat Kabinet Sentra Sinergi.",
      missionTitle: "Misi kerja",
      missionItems: [
        "Menguatkan pelayanan internal melalui sistem kerja terstruktur, evaluasi berkala, dan pengembangan kapasitas pengurus yang berkelanjutan.",
        "Mendorong kolaborasi lintas pihak, mulai dari mahasiswa, alumni, departemen, hingga mitra kegiatan, untuk memperluas dampak program kerja.",
        "Mewujudkan transparansi informasi melalui publikasi kegiatan, dokumentasi terpusat, dan akses informasi yang mudah bagi warga Teknik Komputer ITS.",
      ],
    },
  );

  const resolvedProgramSection = $derived(
    programSection ?? {
      title: "Program kerja kabinet",
      description:
        "Tiga rumpun kerja utama kabinet, disusun sebagai garis kerja yang saling melengkapi.",
      groups: [
        {
          title: "Optimalisasi",
          description:
            "Penguatan sistem internal organisasi dan pengelolaan kerja yang lebih terukur.",
          items: [
            {
              name: "CMOS",
              unit: "Monitoring & Pelaporan",
              description:
                "Sistem monitoring dan pelaporan program kerja untuk mendukung transparansi, akuntabilitas, dan manajemen organisasi berbasis data.",
            },
            {
              name: "Personalia",
              unit: "Sumber Daya Manusia",
              description:
                "Pengelolaan rekrutmen, upgrading, rapor staf, dan sistem apresiasi untuk membangun budaya kerja yang sehat dan bertumbuh.",
            },
          ],
        },
        {
          title: "Kolaborasi",
          description:
            "Penguatan relasi organisasi dengan mahasiswa, alumni, dan isu kesejahteraan yang dekat dengan kebutuhan anggota.",
          items: [
            {
              name: "Hi Alumni",
              unit: "Hubungan Alumni",
              description:
                "Penguatan relasi mahasiswa aktif dan alumni melalui basis data terstruktur serta publikasi pengalaman dari dunia kuliah hingga dunia kerja.",
            },
            {
              name: "Sosmas",
              unit: "Hubungan Luar",
              description:
                "Program yang berfokus pada isu sosial kemasyarakatan seperti charity, bantuan sosial, dan kolaborasi eksternal.",
            },
            {
              name: "Advocation Corner",
              unit: "Kesejahteraan Mahasiswa",
              description:
                "Layanan advokasi aktif pada isu UKT, FRS, beasiswa, dan kebutuhan kesejahteraan mahasiswa lainnya.",
            },
          ],
        },
        {
          title: "Ekspansi",
          description:
            "Perluasan dampak kabinet lewat pengembangan karier, media, kaderisasi, dan kanal digital organisasi.",
          items: [
            {
              name: "COD",
              unit: "Pengembangan Karier",
              description:
                "Program pengembangan karier melalui pelatihan CV, simulasi interview, dan penguatan personal branding mahasiswa.",
            },
            {
              name: "BIOS",
              unit: "Kajian & Riset",
              description:
                "Forum kajian isu keprofesian Teknik Komputer untuk mendorong diskusi kritis dan solusi yang relevan.",
            },
            {
              name: "TEKKOM Insight",
              unit: "Media & Informasi",
              description:
                "Media informasi dengan konten edukatif dan kreatif untuk meningkatkan literasi teknologi mahasiswa.",
            },
            {
              name: "Buku Panduan Kaderisasi",
              unit: "Kader",
              description:
                "Pedoman nilai, alur, dan indikator kaderisasi untuk menjaga kesinambungan regenerasi kepemimpinan.",
            },
            {
              name: "Website HIMATEKKOM",
              unit: "Digital",
              description:
                "Pusat informasi, dokumentasi, dan layanan digital himpunan yang terhubung dengan CMOS.",
            },
          ],
        },
      ],
    },
  );

  const programGroups = $derived(
    (resolvedProgramSection.groups ?? []).map((group) => ({
      ...group,
      icon: programGroupIcons[group.title] ?? Rocket,
    })),
  );

  const resolvedInformationSection = $derived(
    informationSection ?? {
      title: "Informasi terbaru",
      description:
        "Publikasi dan dokumentasi terbaru yang sudah terbit di kanal resmi HIMATEKKOM ITS.",
      archiveLabel: "Arsip lengkap",
      emptyText: "Belum ada publikasi yang terbit di papan informasi.",
    },
  );

  const resolvedCtaSection = $derived(
    ctaSection ?? {
      title: "Kabinet Sentra Sinergi",
      description:
        "Satu sistem, satu kabinet. Transparansi dan dokumentasi kerja berjalan di satu tempat.",
      buttonLabel: "Jelajahi arsip publik",
      instagramUrl: "https://www.instagram.com/sentrasinergi/",
      instagramLabel: "@sentrasinergi",
      instagramPrefix: "Ikuti",
      instagramSuffix: "di Instagram",
    },
  );

  const resolvedFooter = $derived(
    footer ?? {
      description:
        "Kabinet Sentra Sinergi, Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember.",
      address: "Gedung Teknik Komputer, Kampus ITS Sukolilo, Surabaya.",
      sections: [
        {
          title: "Navigasi",
          links: resolvedNavigation,
        },
        {
          title: "Akses",
          links: [
            { href: loginUrl, label: "Masuk ke CMOS" },
            { href: infoUrl, label: "Arsip informasi" },
            {
              href: "https://www.instagram.com/sentrasinergi/",
              label: "Instagram resmi",
            },
          ],
        },
        {
          title: "Kanal pendukung",
          links: [
            {
              href: "https://its.id/m/RPOSentraSinergi",
              label: "Dokumen Organisasi",
            },
            {
              href: "https://its.id/m/PPTRPOSentraSinergi",
              label: "Materi Presentasi",
            },
            {
              href: "https://www.instagram.com/sentrasinergi/",
              label: "Instagram Sentra Sinergi",
            },
          ],
        },
      ],
    },
  );

  const footerSections = $derived(resolvedFooter.sections ?? []);

  const pageTitle =
    "Website Resmi HIMATEKKOM ITS 2026 | Kabinet Sentra Sinergi";
  const pageDescription =
    "Platform resmi HIMATEKKOM ITS untuk informasi publik dan kerja operasional kabinet.";

  let menuDetails = $state(null);
  let activeNavId = $state(null);

  const handleImageError = (event) => {
    if (event.currentTarget.src.endsWith(logoUrl)) {
      return;
    }

    event.currentTarget.src = logoUrl;
  };

  onMount(() => {
    let revealObserver;
    let spyObserver;
    let resetScrollTimeout;
    let removeUserIntentListeners = () => {};

    const setupLandingReveal = () => {
      if (typeof document === "undefined") {
        return;
      }

      const targets = Array.from(document.querySelectorAll("[data-reveal]"));

      if (!targets.length) {
        return;
      }

      if (typeof IntersectionObserver === "undefined") {
        targets.forEach((element) => {
          element.classList.add("is-revealed");
        });

        return;
      }

      revealObserver?.disconnect();

      revealObserver = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (!entry.isIntersecting) {
              return;
            }

            entry.target.classList.add("is-revealed");
            revealObserver?.unobserve(entry.target);
          });
        },
        {
          threshold: 0.18,
          rootMargin: "0px 0px -10% 0px",
        },
      );

      targets.forEach((element) => {
        element.classList.remove("is-revealed");
        revealObserver.observe(element);
      });
    };

    const setupScrollSpy = () => {
      if (typeof IntersectionObserver === "undefined") {
        return;
      }

      const sections = resolvedNavigation
        .map((item) => document.getElementById(item.href.replace("#", "")))
        .filter(Boolean);

      if (!sections.length) {
        return;
      }

      spyObserver?.disconnect();

      const ratios = new Map();

      spyObserver = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            ratios.set(entry.target, entry.intersectionRatio);
          });

          let maxRatio = 0;
          let bestId = null;

          ratios.forEach((ratio, target) => {
            if (ratio > maxRatio) {
              maxRatio = ratio;
              bestId = `#${target.id}`;
            }
          });

          if (bestId) {
            activeNavId = bestId;
          }
        },
        {
          threshold: [0, 0.15, 0.3, 0.5, 0.75, 1],
          rootMargin: "-12% 0px -60% 0px",
        },
      );

      sections.forEach((section) => spyObserver.observe(section));
    };

    const stabilizeInitialMobileScroll = () => {
      if (typeof window === "undefined") {
        return;
      }

      if (
        window.location.hash ||
        !window.matchMedia?.("(pointer: coarse)").matches
      ) {
        return;
      }

      const navigationEntry = performance.getEntriesByType?.("navigation")?.[0];

      if (navigationEntry?.type && navigationEntry.type !== "navigate") {
        return;
      }

      let userInteracted = false;

      const markUserIntent = () => {
        userInteracted = true;
      };

      const resetScroll = () => {
        if (userInteracted || window.scrollY <= 8) {
          return;
        }

        window.scrollTo({ top: 0, left: 0, behavior: "auto" });
      };

      const userIntentEvents = ["touchstart", "pointerdown", "keydown"];

      userIntentEvents.forEach((eventName) => {
        window.addEventListener(eventName, markUserIntent, { passive: true });
      });

      removeUserIntentListeners = () => {
        userIntentEvents.forEach((eventName) => {
          window.removeEventListener(eventName, markUserIntent);
        });
      };

      requestAnimationFrame(() => {
        requestAnimationFrame(resetScroll);
      });

      window.addEventListener("load", resetScroll, { once: true });
      resetScrollTimeout = window.setTimeout(resetScroll, 420);
    };

    const frame = requestAnimationFrame(() => {
      setupLandingReveal();
      setupScrollSpy();
    });

    stabilizeInitialMobileScroll();

    return () => {
      cancelAnimationFrame(frame);
      if (resetScrollTimeout) {
        window.clearTimeout(resetScrollTimeout);
      }
      removeUserIntentListeners();
      revealObserver?.disconnect();
      spyObserver?.disconnect();
    };
  });
</script>

<svelte:head>
  <title>{pageTitle}</title>
  <meta name="description" content={pageDescription} />
</svelte:head>

<div class="landing-terminal min-h-screen">
  <a href="#main-content" class="skip-link">Langsung ke konten</a>

  <header
    class="border-b border-[var(--landing-terminal-line)] bg-[var(--landing-terminal-bg)]"
  >
    <div
      class="mx-auto flex max-w-[1180px] items-center justify-between gap-4 px-5 py-4 lg:px-8"
    >
      <a
        href={homeUrl}
        class="flex min-w-0 items-center gap-3 text-inherit no-underline"
      >
        <enhanced:img
          src={brandLogo}
          alt={organizationName}
          class="h-10 w-auto shrink-0"
          loading="eager"
          fetchpriority="high"
          sizes="40px"
        />
        <div class="min-w-0">
          <div
            class="truncate text-sm font-semibold text-[var(--landing-terminal-heading-resolved)]"
          >
            {organizationName}
          </div>
          <div
            class="truncate text-xs text-[var(--landing-terminal-soft-resolved)]"
          >
            Kabinet Sentra Sinergi 2026
          </div>
        </div>
      </a>

      <nav class="hidden items-center gap-6 md:flex">
        {#each resolvedNavigation as item (item.href)}
          <a
            href={item.href}
            class="rounded-sm text-sm transition-colors duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--landing-terminal-interactive-resolved)] {activeNavId ===
            item.href
              ? 'font-medium text-[var(--landing-terminal-interactive-resolved)]'
              : 'text-[var(--landing-terminal-soft-resolved)] hover:text-[var(--landing-terminal-text-resolved)]'}"
          >
            {item.label}
          </a>
        {/each}
      </nav>

      <div class="flex items-center gap-2">
        <a
          href={loginUrl}
          class="landing-button-secondary hidden items-center gap-2 sm:inline-flex"
        >
          <LogIn size={16} />
          Masuk
        </a>

        <details class="relative md:hidden" bind:this={menuDetails}>
          <summary
            class="inline-flex min-h-11 min-w-11 cursor-pointer list-none items-center justify-center border border-[var(--landing-terminal-line-resolved)] bg-[var(--landing-terminal-panel-resolved)] text-[var(--landing-terminal-text-resolved)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--landing-terminal-interactive-resolved)] [&::-webkit-details-marker]:hidden"
          >
            <Menu size={18} />
          </summary>
          <div
            class="absolute top-[calc(100%+0.75rem)] right-0 z-20 grid min-w-56 gap-1 border border-[var(--landing-terminal-line-resolved)] bg-[var(--landing-terminal-panel-resolved)] p-3"
          >
            {#each resolvedNavigation as item (item.href)}
              <a
                href={item.href}
                class={`px-3 py-2 text-sm transition-colors duration-200 hover:bg-[var(--landing-terminal-panel-soft-resolved)] ${activeNavId === item.href ? "text-[var(--landing-terminal-interactive-resolved)]" : "text-[var(--landing-terminal-soft-resolved)] hover:text-[var(--landing-terminal-text-resolved)]"}`}
                onclick={() => {
                  menuDetails.open = false;
                }}
              >
                {item.label}
              </a>
            {/each}
            <a
              href={loginUrl}
              class="landing-button-secondary mt-1 justify-center"
              onclick={() => {
                menuDetails.open = false;
              }}>Masuk ke CMOS</a
            >
          </div>
        </details>
      </div>
    </div>
  </header>

  <main id="main-content">
    <section class="border-b border-[var(--landing-terminal-line-resolved)]">
      <div
        class="mx-auto grid max-w-[1180px] gap-8 px-5 py-8 lg:grid-cols-[minmax(0,1fr)_30rem] lg:px-8 lg:py-10"
      >
        <div data-reveal style="--reveal-delay: 40ms;" class="grid gap-6">
          <div class="grid gap-4">
            <TerminalTextReveal
              tag="h1"
              texts={resolvedHero.titleVariants}
              cycle={true}
              holdDuration={3000}
              textClass="min-h-[2.45em] max-w-[18ch] text-4xl leading-tight text-[var(--landing-terminal-heading-resolved)] md:text-5xl lg:text-[3.55rem]"
            />
            <TerminalTextReveal
              tag="p"
              text={resolvedHero.description}
              textClass="max-w-[66ch] text-[0.98rem] leading-7 text-[var(--landing-terminal-soft-resolved)]"
            />
          </div>

          <div class="flex flex-col gap-3 sm:flex-row">
            {#each resolvedHero.actions ?? [] as action (action.href)}
              <a
                href={action.href}
                class={`${action.variant === "secondary" ? "landing-button-secondary" : "landing-button-primary"} inline-flex items-center justify-center gap-2`}
              >
                {action.label}
                <ArrowRight size={16} />
              </a>
            {/each}
          </div>

          <div class="landing-command-block">
            {#each resolvedQuickFacts as item, index (`${index}-${item}`)}
              <div class="landing-command-row">
                <span class="landing-command-index">$</span>
                <TerminalTextReveal
                  tag="p"
                  text={item}
                  textClass="text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
                />
              </div>
            {/each}
          </div>
        </div>

        <aside data-reveal style="--reveal-delay: 140ms;">
          <TerminalHeroCanvas
            asciiArt={logokabinetAscii}
            fallbackSrc={logoUrl}
            alt="Logo Kabinet Sentra Sinergi dalam ASCII art"
            title="Kabinet logo"
            status="ASCII ready"
            footerLeft="render /logo"
            footerRight="image-to-ascii"
          />
        </aside>
      </div>
    </section>

    <section
      id="profil"
      data-reveal
      style="--reveal-delay: 220ms;"
      class="scroll-mt-24 border-b border-[var(--landing-terminal-line-resolved)]"
    >
      <div
        class="mx-auto grid max-w-[1180px] gap-6 px-5 py-8 lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)] lg:px-8 lg:py-10"
      >
        <section class="landing-panel px-5 py-5">
          <TerminalTextReveal
            animate={false}
            tag="h2"
            text={resolvedProfileSection.title}
            textClass="text-2xl leading-tight text-[var(--landing-terminal-heading-resolved)] md:text-[2rem]"
          />
          <TerminalTextReveal
            animate={false}
            tag="p"
            text={resolvedProfileSection.description}
            textClass="mt-4 max-w-[66ch] text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
          />
          <div
            class="mt-6 border-t border-[var(--landing-terminal-line-resolved)] pt-4"
          >
            <TerminalTextReveal
              animate={false}
              tag="div"
              text={resolvedProfileSection.visionLabel}
              textClass="text-sm font-semibold text-[var(--landing-terminal-heading-resolved)]"
            />
            <TerminalTextReveal
              animate={false}
              tag="p"
              text={resolvedProfileSection.vision}
              textClass="mt-3 max-w-[66ch] text-sm leading-8 text-[var(--landing-terminal-soft-resolved)]"
            />
          </div>
        </section>

        <section class="landing-panel px-5 py-5">
          <TerminalTextReveal
            animate={false}
            tag="h2"
            text={resolvedProfileSection.missionTitle}
            textClass="text-2xl leading-tight text-[var(--landing-terminal-heading-resolved)] md:text-[2rem]"
          />
          <ol
            class="mt-5 divide-y divide-[var(--landing-terminal-line-resolved)] border-t border-[var(--landing-terminal-line-resolved)]"
          >
            {#each resolvedProfileSection.missionItems ?? [] as item, index (`${index}-${item}`)}
              <li
                class="grid gap-3 py-4 md:grid-cols-[2.5rem_minmax(0,1fr)] md:items-start"
              >
                <div
                  class="text-sm font-semibold text-[var(--landing-terminal-command-resolved)]"
                >
                  0{index + 1}
                </div>
                <TerminalTextReveal
                  animate={false}
                  tag="p"
                  text={item}
                  textClass="text-sm leading-8 text-[var(--landing-terminal-soft-resolved)]"
                />
              </li>
            {/each}
          </ol>
        </section>
      </div>
    </section>

    <section
      data-reveal
      style="--reveal-delay: 280ms;"
      class="border-b border-[var(--landing-terminal-line-resolved)]"
    >
      <div class="mx-auto max-w-[1180px] px-5 py-6 lg:px-8 lg:py-8">
        <div class="grid gap-3 md:grid-cols-3">
          <figure class="landing-frame m-0">
            <div class="landing-frame__media">
              <OptimizedImage
                src={himatekkomGallery}
                alt="Dokumentasi kegiatan mahasiswa Teknik Komputer ITS"
                class="h-48 w-full object-cover"
                loading="lazy"
                decoding="async"
                sizes="(min-width: 768px) 33vw, 100vw"
              />
            </div>
            <figcaption class="landing-frame__caption">
              <span class="text-[var(--landing-terminal-muted-resolved)]"
                >lab</span
              >
              <span class="text-[var(--landing-terminal-frame-accent-resolved)]"
                >/workspace</span
              >
            </figcaption>
          </figure>
          <figure class="landing-frame m-0">
            <div class="landing-frame__media">
              <img
                src="/images/flare.jpg"
                alt="Aksen visual identitas publikasi HIMATEKKOM ITS"
                class="h-48 w-full object-cover"
                loading="lazy"
                decoding="async"
              />
            </div>
            <figcaption class="landing-frame__caption">
              <span class="text-[var(--landing-terminal-muted-resolved)]"
                >kolaborasi</span
              >
              <span class="text-[var(--landing-terminal-frame-accent-resolved)]"
                >/tim</span
              >
            </figcaption>
          </figure>
          <figure class="landing-frame m-0">
            <div class="landing-frame__media">
              <OptimizedImage
                src={logoGallery}
                alt="Logo kabinet Sentra Sinergi HIMATEKKOM ITS"
                class="h-48 w-full object-cover object-center p-6"
                loading="lazy"
                decoding="async"
                sizes="(min-width: 768px) 33vw, 100vw"
              />
            </div>
            <figcaption class="landing-frame__caption">
              <span class="text-[var(--landing-terminal-muted-resolved)]"
                >gedung</span
              >
              <span class="text-[var(--landing-terminal-frame-accent-resolved)]"
                >/teknik komputer</span
              >
            </figcaption>
          </figure>
        </div>
      </div>
    </section>

    <section
      id="program-kerja"
      data-reveal
      style="--reveal-delay: 360ms;"
      class="scroll-mt-24 border-b border-[var(--landing-terminal-line-resolved)]"
    >
      <div class="mx-auto max-w-[1180px] px-5 py-10 lg:px-8 lg:py-14">
        <div class="max-w-[72ch]">
          <TerminalTextReveal
            animate={false}
            tag="h2"
            text={resolvedProgramSection.title}
            textClass="text-2xl leading-tight text-[var(--landing-terminal-heading-resolved)] md:text-[2rem]"
          />
          <TerminalTextReveal
            animate={false}
            tag="p"
            text={resolvedProgramSection.description}
            textClass="mt-3 text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
          />
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-3">
          {#each programGroups as group (group.title)}
            <section class="landing-panel px-5 py-5">
              <div class="flex items-center gap-3">
                <div class="landing-group-icon" aria-hidden="true">
                  <group.icon size={18} strokeWidth={1.9} />
                </div>
                <TerminalTextReveal
                  animate={false}
                  tag="h3"
                  text={group.title}
                  textClass="text-xl leading-tight text-[var(--landing-terminal-heading-resolved)]"
                />
              </div>
              <TerminalTextReveal
                animate={false}
                tag="p"
                text={group.description}
                textClass="mt-3 text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
              />

              <div
                class="mt-5 divide-y divide-[var(--landing-terminal-line-resolved)] border-t border-[var(--landing-terminal-line-resolved)]"
              >
                {#each group.items as item, itemIndex (`${group.title}-${item.name}`)}
                  <article class={`py-4 ${itemIndex === 0 ? "pt-0" : ""}`}>
                    <div
                      class="flex flex-col gap-1 md:flex-row md:items-baseline md:justify-between"
                    >
                      <TerminalTextReveal
                        animate={false}
                        tag="div"
                        text={item.name}
                        textClass="text-sm font-semibold text-[var(--landing-terminal-heading-resolved)]"
                      />
                      <TerminalTextReveal
                        animate={false}
                        tag="div"
                        text={item.unit}
                        textClass="text-xs text-[var(--landing-terminal-muted-resolved)]"
                      />
                    </div>
                    <TerminalTextReveal
                      animate={false}
                      tag="p"
                      text={item.description}
                      textClass="mt-2 text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
                    />
                  </article>
                {/each}
              </div>
            </section>
          {/each}
        </div>
      </div>
    </section>

    <section
      id="informasi"
      data-reveal
      style="--reveal-delay: 420ms;"
      class="scroll-mt-24 border-b border-[var(--landing-terminal-line-resolved)]"
    >
      <div class="mx-auto max-w-[1180px] px-5 py-8 lg:px-8 lg:py-10">
        <section class="landing-panel px-5 py-5">
          <div
            class="flex flex-col gap-4 border-b border-[var(--landing-terminal-line-resolved)] pb-4 md:flex-row md:items-end md:justify-between"
          >
            <div>
              <TerminalTextReveal
                animate={false}
                tag="h2"
                text={resolvedInformationSection.title}
                textClass="text-2xl leading-tight text-[var(--landing-terminal-heading-resolved)] md:text-[2rem]"
              />
              <TerminalTextReveal
                animate={false}
                tag="p"
                text={resolvedInformationSection.description}
                textClass="mt-3 text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
              />
            </div>
            <a
              href={infoUrl}
              class="landing-inline-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--landing-terminal-interactive-resolved)] hover:text-[var(--landing-terminal-text-resolved)]"
            >
              {resolvedInformationSection.archiveLabel}
              <ArrowRight size={16} />
            </a>
          </div>

          {#if latestInfo.length}
            <div
              class="divide-y divide-[var(--landing-terminal-line-resolved)]"
            >
              {#each latestInfo as article (article.url || article.title)}
                <a href={article.url} class="landing-article-row">
                  <div
                    class={article.coverImage
                      ? "grid gap-3 lg:grid-cols-[11rem_8rem_minmax(0,1fr)] lg:items-start"
                      : "grid gap-3 lg:grid-cols-[9rem_minmax(0,1fr)] lg:items-start"}
                  >
                    {#if article.coverImage}
                      <div class="landing-frame overflow-hidden">
                        <div class="landing-frame__media border-b-0">
                          <OptimizedImage
                            src={article.coverImage}
                            alt={article.title}
                            class="h-28 w-full object-cover"
                            loading="lazy"
                            decoding="async"
                            sizes="11rem"
                            onerror={handleImageError}
                          />
                        </div>
                      </div>
                    {/if}
                    <div
                      class="space-y-1 text-[0.73rem] text-[var(--landing-terminal-muted-resolved)]"
                    >
                      <div>{article.publishedAtLabel || "Publikasi baru"}</div>
                      <div>{article.category}</div>
                    </div>
                    <div class="space-y-2">
                      <TerminalTextReveal
                        animate={false}
                        tag="h3"
                        text={article.title}
                        textClass="text-lg leading-snug text-[var(--landing-terminal-heading-resolved)]"
                      />
                      <TerminalTextReveal
                        animate={false}
                        tag="p"
                        text={article.excerpt}
                        textClass="max-w-[65ch] text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
                      />
                    </div>
                  </div>
                </a>
              {/each}
            </div>
          {:else}
            <TerminalTextReveal
              animate={false}
              tag="div"
              text={resolvedInformationSection.emptyText}
              textClass="py-5 text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
            />
          {/if}
        </section>
      </div>
    </section>

    <section
      data-reveal
      style="--reveal-delay: 480ms;"
      class="border-b border-[var(--landing-terminal-line-resolved)]"
    >
      <div class="mx-auto max-w-[1180px] px-5 py-14 lg:px-8 lg:py-16">
        <div class="landing-panel px-8 py-10 text-center lg:px-14 lg:py-14">
          <div class="mx-auto max-w-[48ch] space-y-5">
            <TerminalTextReveal
              animate={false}
              tag="h2"
              text={resolvedCtaSection.title}
              textClass="text-3xl leading-tight text-[var(--landing-terminal-heading-resolved)] md:text-[3rem]"
            />
            <TerminalTextReveal
              animate={false}
              tag="p"
              text={resolvedCtaSection.description}
              textClass="text-[0.98rem] leading-7 text-[var(--landing-terminal-soft-resolved)]"
            />
            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-center">
              <a
                href="#informasi"
                class="landing-button-primary inline-flex items-center justify-center gap-2"
              >
                {resolvedCtaSection.buttonLabel}
                <ArrowRight size={16} />
              </a>
            </div>
            <p class="text-sm text-[var(--landing-terminal-muted-resolved)]">
              {resolvedCtaSection.instagramPrefix}
              <a
                href={resolvedCtaSection.instagramUrl}
                target="_blank"
                rel="noreferrer"
                class="text-[var(--landing-terminal-interactive-resolved)] transition-colors hover:text-[var(--landing-terminal-text-resolved)]"
                >{resolvedCtaSection.instagramLabel}</a
              >
              {resolvedCtaSection.instagramSuffix}
            </p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer
    data-reveal
    style="--reveal-delay: 540ms;"
    class="border-t border-[var(--landing-terminal-line-resolved)]"
  >
    <div
      class="mx-auto grid max-w-[1180px] gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)_minmax(0,0.8fr)] lg:px-8"
    >
      <div class="space-y-3">
        <TerminalTextReveal
          animate={false}
          tag="div"
          text={organizationName}
          textClass="text-lg font-semibold text-[var(--landing-terminal-heading-resolved)]"
        />
        <TerminalTextReveal
          animate={false}
          tag="p"
          text={resolvedFooter.description}
          textClass="max-w-[60ch] text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
        />
        <TerminalTextReveal
          animate={false}
          tag="p"
          text={resolvedFooter.address}
          textClass="text-sm leading-7 text-[var(--landing-terminal-soft-resolved)]"
        />
      </div>

      {#each footerSections as section (section.title)}
        <div>
          <TerminalTextReveal
            animate={false}
            tag="div"
            text={section.title}
            textClass="text-sm font-semibold text-[var(--landing-terminal-heading-resolved)]"
          />
          <div
            class="mt-3 grid gap-2 text-sm text-[var(--landing-terminal-soft-resolved)]"
          >
            {#each section.links as link (link.href)}
              <a
                href={link.href}
                class="transition-colors duration-150 hover:text-[var(--landing-terminal-interactive-resolved)]"
                >{link.label}</a
              >
            {/each}
          </div>
        </div>
      {/each}
    </div>
  </footer>
</div>

<style>
  .landing-terminal {
    --landing-terminal-bg-resolved: var(
      --landing-terminal-bg,
      oklch(0.18 0.012 304)
    );
    --landing-terminal-hero-bg-resolved: var(
      --landing-terminal-hero-bg,
      var(--landing-terminal-bg, oklch(0.18 0.012 304))
    );
    --landing-terminal-panel-resolved: var(
      --landing-terminal-panel,
      oklch(0.22 0.014 304)
    );
    --landing-terminal-panel-soft-resolved: var(
      --landing-terminal-panel-soft,
      oklch(0.27 0.015 304)
    );
    --landing-terminal-line-resolved: var(
      --landing-terminal-line,
      oklch(0.52 0.032 82)
    );
    --landing-terminal-text-resolved: var(
      --landing-terminal-text,
      oklch(0.86 0.03 92)
    );
    --landing-terminal-heading-resolved: var(
      --landing-terminal-heading,
      oklch(0.86 0.03 92)
    );
    --landing-terminal-soft-resolved: var(
      --landing-terminal-soft,
      oklch(0.74 0.025 92)
    );
    --landing-terminal-muted-resolved: var(
      --landing-terminal-muted,
      oklch(0.62 0.02 88)
    );
    --landing-terminal-accent-resolved: var(
      --landing-terminal-accent,
      oklch(0.78 0.14 84)
    );
    --landing-terminal-interactive-resolved: var(
      --landing-terminal-interactive,
      oklch(0.78 0.14 84)
    );
    --landing-terminal-command-resolved: var(
      --landing-terminal-command,
      oklch(0.78 0.14 84)
    );
    --landing-terminal-frame-accent-resolved: var(
      --landing-terminal-frame-accent,
      oklch(0.78 0.14 84)
    );
    --landing-terminal-icon-resolved: var(
      --landing-terminal-icon,
      oklch(0.78 0.14 84)
    );
    --landing-terminal-button-text-resolved: var(
      --landing-terminal-button-text,
      oklch(0.22 0.02 74)
    );
    --landing-terminal-button-hover-resolved: var(
      --landing-terminal-button-hover,
      oklch(0.82 0.12 84)
    );
    --landing-terminal-button-secondary-text-resolved: var(
      --landing-terminal-button-secondary-text,
      oklch(0.86 0.03 92)
    );
    --landing-terminal-button-secondary-hover-resolved: var(
      --landing-terminal-button-secondary-hover,
      oklch(0.27 0.015 304)
    );
    background: var(--landing-terminal-bg-resolved);
    color: var(--landing-terminal-text-resolved);
    font-family: "Public Sans", sans-serif;
  }

  .landing-terminal :global(h1),
  .landing-terminal :global(h2),
  .landing-terminal :global(h3) {
    color: var(--landing-terminal-heading-resolved);
    font-family: "JetBrains Mono", monospace;
    letter-spacing: -0.02em;
    line-height: 1.2;
  }

  .landing-terminal header,
  .landing-terminal header :global(*),
  .landing-command-block,
  .landing-command-block :global(*),
  .landing-button-primary,
  .landing-button-secondary {
    font-family: "JetBrains Mono", monospace;
  }

  .landing-terminal :global(p) {
    line-height: 1.65;
  }

  .landing-panel,
  .landing-command-block {
    border: 1px solid var(--landing-terminal-line-resolved);
    background: var(--landing-terminal-panel-resolved);
  }

  .landing-button-primary,
  .landing-button-secondary {
    min-height: 2.75rem;
    padding: 0.65rem 1rem;
    border: 1px solid var(--landing-terminal-line-resolved);
    font-size: 0.92rem;
    font-weight: 600;
    text-decoration: none;
    transition:
      background-color 180ms var(--ease-out-quart),
      color 180ms var(--ease-out-quart),
      border-color 180ms var(--ease-out-quart);
  }

  .landing-button-primary {
    background: var(--landing-terminal-accent-resolved);
    border-color: color-mix(
      in oklch,
      var(--landing-terminal-accent-resolved) 72%,
      var(--landing-terminal-line-resolved) 28%
    );
    color: var(--landing-terminal-button-text-resolved);
  }

  .landing-button-primary:hover {
    background: var(--landing-terminal-button-hover-resolved);
  }

  .landing-button-secondary {
    background: var(--landing-terminal-panel-resolved);
    color: var(--landing-terminal-button-secondary-text-resolved);
  }

  .landing-button-secondary:hover,
  .landing-inline-link:hover,
  .landing-article-row:hover {
    background: var(--landing-terminal-panel-soft-resolved);
  }

  .landing-button-secondary:hover {
    background: var(--landing-terminal-button-secondary-hover-resolved);
  }

  .landing-command-block {
    display: grid;
  }

  .landing-command-row {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: 3rem minmax(0, 1fr);
    padding: 0.95rem 1rem;
    border-top: 1px solid var(--landing-terminal-line-resolved);
  }

  .landing-command-row:first-child {
    border-top: none;
  }

  .landing-command-index {
    color: var(--landing-terminal-command-resolved);
    font-size: 0.82rem;
    font-weight: 600;
  }

  .landing-inline-link,
  .landing-article-row {
    color: inherit;
    text-decoration: none;
    transition:
      background-color 180ms var(--ease-out-quart),
      color 180ms var(--ease-out-quart);
  }

  .landing-inline-link {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.2rem 0.35rem;
    margin: -0.2rem -0.35rem;
  }

  .landing-article-row {
    display: block;
    padding: 1rem 0.85rem;
    margin-inline: -0.85rem;
  }

  .landing-frame {
    border: 1px solid var(--landing-terminal-line-resolved);
    background: var(--landing-terminal-panel-resolved);
    transition:
      background-color 180ms var(--ease-out-quart),
      border-color 180ms var(--ease-out-quart);
  }

  .landing-frame__media {
    overflow: hidden;
    border-bottom: 1px solid var(--landing-terminal-line-resolved);
  }

  .landing-frame__media img {
    display: block;
    transition: filter 320ms var(--ease-out-quart);
    filter: grayscale(0.3) contrast(1.08);
  }

  .landing-frame:hover {
    background: var(--landing-terminal-panel-soft-resolved);
  }

  .landing-frame:hover .landing-frame__media img {
    filter: grayscale(0) contrast(1.02);
  }

  .landing-frame__caption {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
    padding: 0.6rem 0.8rem;
    font-family: "JetBrains Mono", monospace;
    font-size: 0.72rem;
  }

  .landing-group-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.2rem;
    height: 2.2rem;
    flex-shrink: 0;
    border: 1px solid var(--landing-terminal-line-resolved);
    background: color-mix(
      in oklch,
      var(--landing-terminal-panel-resolved) 82%,
      var(--landing-terminal-panel-soft-resolved) 18%
    );
    color: var(--landing-terminal-icon-resolved);
  }

  @media (max-width: 767px) {
    .landing-command-row {
      grid-template-columns: 2.4rem minmax(0, 1fr);
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .landing-terminal :global(*) {
      transition-duration: 0ms !important;
      animation-duration: 0ms !important;
    }
  }
</style>
