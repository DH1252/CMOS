<script>
  import { onMount } from 'svelte';
  import brandLogo from '../images/logokabinet.png?enhanced&w=80;160';
  import himatekkomTerminalImage from '../images/himatekkom.jpg';
  import PublicInformationIndexPage from './public/PublicInformationIndexPage.svelte';
  import PublicInformationShowPage from './public/PublicInformationShowPage.svelte';
  import TerminalHeroCanvas from './components/TerminalHeroCanvas.svelte';
  import TerminalTextReveal from './components/TerminalTextReveal.svelte';
  import {
    ArrowRight,
    ArrowUpRight,
    LayoutDashboard,
    LogIn,
    Menu,
  } from 'lucide-svelte';

  let {
    page = 'landing',
    appName = 'CMOS',
    organizationName = 'HIMATEKKOM ITS',
    themeColor = 'purple',
    themeVariables = null,
    homeUrl = '/',
    loginUrl = '/login',
    infoUrl = '/informasi',
    latestInfo = [],
    logoUrl = '/images/logokabinet.png',
    infoIndex = {},
    infoShow = {},
  } = $props();

  const isInfoIndex = $derived(page === 'info-index');
  const isInfoShow = $derived(page === 'info-show');
  const latestArticles = $derived(latestInfo);

  $effect(() => {
    document.documentElement.setAttribute('data-brand', themeColor || 'purple');
  });

  $effect(() => {
    if (!themeVariables || typeof themeVariables !== 'object') {
      return;
    }

    Object.entries(themeVariables).forEach(([token, value]) => {
      if (typeof token !== 'string' || typeof value !== 'string') {
        return;
      }

      document.documentElement.style.setProperty(`--${token}`, value);
    });
  });

  const navigation = [
    { href: '#profil', label: 'Profil Organisasi' },
    { href: '#program-kerja', label: 'Program Kerja' },
    { href: '#informasi', label: 'Informasi' },
    { href: '#cmos', label: 'CMOS' },
  ];

  const quickFacts = $derived([
    'Website resmi HIMATEKKOM ITS 2026.',
    'Kabinet Sentra Sinergi menjaga transparansi, dokumentasi, dan kolaborasi organisasi.',
    `${appName} dipakai pengurus untuk kerja operasional sehari-hari.`,
  ]);

  const vision =
    'Menjadikan HIMATEKKOM ITS sebagai himpunan yang progresif, inklusif, dan berdampak, dengan tata kelola organisasi yang profesional serta budaya kolaborasi yang kuat dalam semangat Kabinet Sentra Sinergi.';

  const missionItems = [
    'Menguatkan pelayanan internal melalui sistem kerja terstruktur, evaluasi berkala, dan pengembangan kapasitas pengurus yang berkelanjutan.',
    'Mendorong kolaborasi lintas pihak, mulai dari mahasiswa, alumni, departemen, hingga mitra kegiatan, untuk memperluas dampak program kerja.',
    'Mewujudkan transparansi informasi melalui publikasi kegiatan, dokumentasi terpusat, dan akses informasi yang mudah bagi warga Teknik Komputer ITS.',
  ];

  const programGroups = [
    {
      title: 'Optimalisasi',
      description: 'Penguatan sistem internal organisasi dan pengelolaan kerja yang lebih terukur.',
      items: [
        {
          name: 'CMOS',
          unit: 'Sistem Terintegrasi',
          description: 'Sistem monitoring dan pelaporan program kerja untuk mendukung transparansi, akuntabilitas, dan manajemen organisasi berbasis data.',
        },
        {
          name: 'Personalia',
          unit: 'Sumber Daya Manusia',
          description: 'Pengelolaan rekrutmen, upgrading, rapor staf, dan sistem apresiasi untuk membangun budaya kerja yang sehat dan bertumbuh.',
        },
      ],
    },
    {
      title: 'Kolaborasi',
      description: 'Penguatan relasi organisasi dengan mahasiswa, alumni, dan isu kesejahteraan yang dekat dengan kebutuhan anggota.',
      items: [
        {
          name: 'Hi Alumni',
          unit: 'Hubungan Alumni',
          description: 'Penguatan relasi mahasiswa aktif dan alumni melalui basis data terstruktur serta publikasi pengalaman dari dunia kuliah hingga dunia kerja.',
        },
        {
          name: 'Sosmas',
          unit: 'Hubungan Luar',
          description: 'Program yang berfokus pada isu sosial kemasyarakatan seperti charity, bantuan sosial, dan kolaborasi eksternal.',
        },
        {
          name: 'Advocation Corner',
          unit: 'Kesejahteraan Mahasiswa',
          description: 'Layanan advokasi aktif pada isu UKT, FRS, beasiswa, dan kebutuhan kesejahteraan mahasiswa lainnya.',
        },
      ],
    },
    {
      title: 'Ekspansi',
      description: 'Perluasan dampak kabinet lewat pengembangan karier, media, kaderisasi, dan kanal digital organisasi.',
      items: [
        {
          name: 'COD',
          unit: 'PSDM',
          description: 'Program pengembangan karier melalui pelatihan CV, simulasi interview, dan penguatan personal branding mahasiswa.',
        },
        {
          name: 'BIOS',
          unit: 'Risprof',
          description: 'Forum kajian isu keprofesian Teknik Komputer untuk mendorong diskusi kritis dan solusi yang relevan.',
        },
        {
          name: 'TEKKOM Insight',
          unit: 'Medfo',
          description: 'Media informasi dengan konten edukatif dan kreatif untuk meningkatkan literasi teknologi mahasiswa.',
        },
        {
          name: 'Buku Panduan Kaderisasi',
          unit: 'Kader',
          description: 'Pedoman nilai, alur, dan indikator kaderisasi untuk menjaga kesinambungan regenerasi kepemimpinan.',
        },
        {
          name: 'Website HIMATEKKOM',
          unit: 'Digital',
          description: 'Pusat informasi, dokumentasi, dan layanan digital himpunan yang terhubung dengan CMOS.',
        },
      ],
    },
  ];

  const supportLinks = [
    {
      title: 'Dokumen Organisasi',
      description: 'Referensi kegiatan dan dokumen resmi kabinet.',
      href: 'https://its.id/m/RPOSentraSinergi',
    },
    {
      title: 'Materi Presentasi',
      description: 'Materi publik yang dibagikan kabinet untuk kebutuhan sosialisasi dan dokumentasi.',
      href: 'https://its.id/m/PPTRPOSentraSinergi',
    },
    {
      title: 'Instagram Sentra Sinergi',
      description: 'Kanal publikasi kegiatan dan pembaruan kabinet terbaru.',
      href: 'https://www.instagram.com/sentrasinergi/',
    },
  ];

  const cmosFeatures = [
    'Monitoring program kerja secara real-time.',
    'Timeline kegiatan dan dokumentasi kerja yang terpusat.',
    'Evaluasi kinerja pengurus dan laporan organisasi.',
    'Akses data organisasi yang lebih rapi untuk pengambilan keputusan.',
  ];

  const footerSections = $derived([
    {
      title: 'Navigasi',
      links: [
        { href: '#profil', label: 'Profil Organisasi' },
        { href: '#program-kerja', label: 'Program Kerja' },
        { href: '#informasi', label: 'Papan Informasi' },
      ],
    },
    {
      title: 'Akses',
      links: [
        { href: loginUrl, label: 'Masuk ke CMOS' },
        { href: infoUrl, label: 'Lihat arsip informasi' },
        { href: 'https://www.instagram.com/sentrasinergi/', label: 'Instagram resmi' },
      ],
    },
  ]);

  const pageTitle = $derived.by(() => {
    if (page === 'landing') {
      return 'Website Resmi HIMATEKKOM ITS 2026 | Kabinet Sentra Sinergi';
    }

    if (page === 'info-show') {
      const seoTitle = infoShow?.article?.seoTitle;

      return `${seoTitle || infoShow?.article?.title || 'Papan Informasi'} - ${organizationName}`;
    }

    return `Papan Informasi - ${organizationName}`;
  });

  const pageDescription = $derived.by(() => {
    if (page === 'landing') {
      return 'Platform resmi HIMATEKKOM ITS untuk informasi publik dan kerja operasional kabinet.';
    }

    if (page === 'info-show') {
      return infoShow?.article?.excerpt || 'Publikasi resmi HIMATEKKOM ITS.';
    }

    return `Portal informasi resmi ${organizationName}. Artikel, pembaruan kegiatan, dan publikasi organisasi.`;
  });

  onMount(() => {
    let observer;

    const setupLandingReveal = () => {
      if (page !== 'landing' || typeof document === 'undefined') {
        return;
      }

      const targets = Array.from(document.querySelectorAll('[data-reveal]'));

      if (!targets.length) {
        return;
      }

      if (typeof IntersectionObserver === 'undefined') {
        targets.forEach((element) => {
          element.classList.add('is-revealed');
        });

        return;
      }

      observer?.disconnect();

      observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (!entry.isIntersecting) {
              return;
            }

            entry.target.classList.add('is-revealed');
            observer?.unobserve(entry.target);
          });
        },
        {
          threshold: 0.18,
          rootMargin: '0px 0px -10% 0px',
        },
      );

      targets.forEach((element) => {
        element.classList.remove('is-revealed');
        observer.observe(element);
      });
    };

    const frame = requestAnimationFrame(setupLandingReveal);

    return () => {
      cancelAnimationFrame(frame);
      observer?.disconnect();
    };
  });

  const heroTitle = 'Portal resmi HIMATEKKOM ITS 2026 untuk arsip publik dan kerja kabinet.';
  const heroDescription = 'Kabinet Sentra Sinergi menjaga publikasi, dokumentasi, dan akses internal melalui satu sistem yang rapi dan mudah dipantau.';

</script>

<svelte:head>
  <title>{pageTitle}</title>
  <meta name="description" content={pageDescription} />
</svelte:head>

{#if page === 'landing'}
  <div class="landing-terminal min-h-screen">
    <header class="border-b border-[var(--landing-terminal-line)] bg-[var(--landing-terminal-bg)]">
      <div class="mx-auto flex max-w-[1180px] items-center justify-between gap-4 px-5 py-4 lg:px-8">
        <a href={homeUrl} class="flex min-w-0 items-center gap-3 text-inherit no-underline">
          <enhanced:img src={brandLogo} alt={organizationName} class="h-10 w-auto shrink-0" loading="eager" fetchpriority="high" sizes="40px" />
          <div class="min-w-0">
            <div class="truncate text-sm font-semibold text-[var(--landing-terminal-text)]">{organizationName}</div>
            <div class="truncate text-xs text-[var(--landing-terminal-soft)]">Kabinet Sentra Sinergi 2026</div>
          </div>
        </a>

        <nav class="hidden items-center gap-6 md:flex">
          {#each navigation as item (item.href)}
            <a href={item.href} class="text-sm text-[var(--landing-terminal-soft)] transition-colors duration-150 hover:text-[var(--landing-terminal-text)]">{item.label}</a>
          {/each}
        </nav>

        <div class="flex items-center gap-2">
          <a href={loginUrl} class="landing-button-secondary hidden items-center gap-2 sm:inline-flex">
            <LogIn size={16} />
            Masuk
          </a>

          <details class="relative md:hidden">
            <summary class="inline-flex h-10 w-10 cursor-pointer list-none items-center justify-center border border-[var(--landing-terminal-line)] bg-[var(--landing-terminal-panel)] text-[var(--landing-terminal-text)] [&::-webkit-details-marker]:hidden">
              <Menu size={18} />
            </summary>
            <div class="absolute right-0 top-[calc(100%+0.75rem)] z-20 grid min-w-56 gap-1 border border-[var(--landing-terminal-line)] bg-[var(--landing-terminal-panel)] p-3">
              {#each navigation as item (item.href)}
                <a href={item.href} class="px-3 py-2 text-sm text-[var(--landing-terminal-soft)] transition-colors duration-150 hover:bg-[var(--landing-terminal-panel-soft)] hover:text-[var(--landing-terminal-text)]">{item.label}</a>
              {/each}
              <a href={loginUrl} class="landing-button-secondary mt-1 justify-center">Masuk ke CMOS</a>
            </div>
          </details>
        </div>
      </div>
    </header>

    <main id="main-content">
      <section class="border-b border-[var(--landing-terminal-line)]">
        <div class="mx-auto grid max-w-[1180px] gap-8 px-5 py-8 lg:grid-cols-[minmax(0,1fr)_30rem] lg:px-8 lg:py-10">
          <div data-reveal style="--reveal-delay: 40ms;" class="grid gap-6">
            <div class="grid gap-4">
              <TerminalTextReveal
                tag="h1"
                text={heroTitle}
                textClass="max-w-[15ch] text-4xl leading-tight text-[var(--landing-terminal-text)] md:text-5xl lg:text-[3.55rem]"
              />
              <TerminalTextReveal
                tag="p"
                text={heroDescription}
                textClass="max-w-[66ch] text-[0.98rem] leading-7 text-[var(--landing-terminal-soft)]"
              />
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
              <a href="#informasi" class="landing-button-primary inline-flex items-center justify-center gap-2">
                Buka arsip publik
                <ArrowRight size={16} />
              </a>
              <a href="#profil" class="landing-button-secondary inline-flex items-center justify-center gap-2">
                Profil organisasi
                <ArrowRight size={16} />
              </a>
            </div>

            <div class="landing-command-block">
              {#each quickFacts as item, index (`${index}-${item}`)}
                <div class="landing-command-row">
                  <span class="landing-command-index">0{index + 1}</span>
                  <TerminalTextReveal tag="p" text={item} textClass="text-sm leading-7 text-[var(--landing-terminal-soft)]" />
                </div>
              {/each}
            </div>

            <dl class="grid gap-3 md:grid-cols-3">
              <div class="landing-meta-cell">
                <dt>Organisasi</dt>
                <dd>{organizationName}</dd>
              </div>
              <div class="landing-meta-cell">
                <dt>Mode kerja</dt>
                <dd>Arsip publik, koordinasi, dokumentasi</dd>
              </div>
              <div class="landing-meta-cell">
                <dt>Akses internal</dt>
                <dd>{appName} untuk pengurus kabinet</dd>
              </div>
            </dl>
          </div>

          <aside data-reveal style="--reveal-delay: 140ms;">
            <TerminalHeroCanvas
              source={himatekkomTerminalImage}
              fallbackSrc={himatekkomTerminalImage}
              alt="Dokumentasi kabinet dalam tampilan terminal"
              footerLeft="render /public"
              footerRight="brand-tinted phosphor"
            />
          </aside>
        </div>
      </section>

      <section id="profil" data-reveal style="--reveal-delay: 220ms;" class="scroll-mt-24 border-b border-[var(--landing-terminal-line)]">
        <div class="mx-auto grid max-w-[1180px] gap-6 px-5 py-8 lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)] lg:px-8 lg:py-10">
          <section class="landing-panel px-5 py-5">
            <TerminalTextReveal tag="h2" text="Profil organisasi" textClass="text-2xl leading-tight text-[var(--landing-terminal-text)] md:text-[2rem]" />
            <TerminalTextReveal
              tag="p"
              text="HIMATEKKOM ITS 2026 menjalankan publikasi dan operasional kabinet dengan alur yang terstruktur, terdokumentasi, dan mudah dibaca oleh pengurus maupun publik."
              textClass="mt-4 max-w-[66ch] text-sm leading-7 text-[var(--landing-terminal-soft)]"
            />
            <div class="mt-6 border-t border-[var(--landing-terminal-line)] pt-4">
              <TerminalTextReveal tag="div" text="Visi" textClass="text-sm font-semibold text-[var(--landing-terminal-text)]" />
              <TerminalTextReveal tag="p" text={vision} textClass="mt-3 max-w-[66ch] text-sm leading-8 text-[var(--landing-terminal-soft)]" />
            </div>
          </section>

          <section class="landing-panel px-5 py-5">
            <TerminalTextReveal tag="h2" text="Misi kerja" textClass="text-2xl leading-tight text-[var(--landing-terminal-text)] md:text-[2rem]" />
            <ol class="mt-5 divide-y divide-[var(--landing-terminal-line)] border-t border-[var(--landing-terminal-line)]">
              {#each missionItems as item, index (`${index}-${item}`)}
                <li class="grid gap-3 py-4 md:grid-cols-[2.5rem_minmax(0,1fr)] md:items-start">
                  <div class="text-sm font-semibold text-[var(--landing-terminal-accent)]">0{index + 1}</div>
                  <TerminalTextReveal tag="p" text={item} textClass="text-sm leading-8 text-[var(--landing-terminal-soft)]" />
                </li>
              {/each}
            </ol>
          </section>
        </div>
      </section>

      <section id="program-kerja" data-reveal style="--reveal-delay: 320ms;" class="scroll-mt-24 border-b border-[var(--landing-terminal-line)]">
        <div class="mx-auto max-w-[1180px] px-5 py-8 lg:px-8 lg:py-10">
          <div class="max-w-[72ch]">
            <TerminalTextReveal tag="h2" text="Program kerja kabinet" textClass="text-2xl leading-tight text-[var(--landing-terminal-text)] md:text-[2rem]" />
            <TerminalTextReveal tag="p" text="Tiga rumpun kerja utama kabinet, disusun sebagai garis kerja yang saling melengkapi." textClass="mt-3 text-sm leading-7 text-[var(--landing-terminal-soft)]" />
          </div>

          <div class="mt-6 border border-[var(--landing-terminal-line)] bg-[var(--landing-terminal-panel)]">
            {#each programGroups as group, groupIndex (group.title)}
              <section class={`grid gap-4 px-5 py-5 lg:grid-cols-[12rem_minmax(0,1fr)] ${groupIndex > 0 ? 'border-t border-[var(--landing-terminal-line)]' : ''}`}>
                <div>
                  <TerminalTextReveal tag="h3" text={group.title} textClass="text-xl leading-tight text-[var(--landing-terminal-text)]" />
                  <TerminalTextReveal tag="p" text={group.description} textClass="mt-3 text-sm leading-7 text-[var(--landing-terminal-soft)]" />
                </div>

                <div class="divide-y divide-[var(--landing-terminal-line)] border-t border-[var(--landing-terminal-line)] lg:border-t-0">
                  {#each group.items as item, itemIndex (`${group.title}-${item.name}`)}
                    <article class={`py-4 ${itemIndex === 0 ? 'pt-0' : ''}`}>
                      <div class="flex flex-col gap-1 md:flex-row md:items-baseline md:justify-between">
                        <TerminalTextReveal tag="div" text={item.name} textClass="text-sm font-semibold text-[var(--landing-terminal-text)]" />
                        <TerminalTextReveal tag="div" text={item.unit} textClass="text-xs text-[var(--landing-terminal-muted)]" />
                      </div>
                      <TerminalTextReveal tag="p" text={item.description} textClass="mt-2 text-sm leading-7 text-[var(--landing-terminal-soft)]" />
                    </article>
                  {/each}
                </div>
              </section>
            {/each}
          </div>
        </div>
      </section>

      <section id="informasi" data-reveal style="--reveal-delay: 420ms;" class="scroll-mt-24 border-b border-[var(--landing-terminal-line)]">
        <div class="mx-auto grid max-w-[1180px] gap-6 px-5 py-8 lg:grid-cols-[minmax(0,1.25fr)_22rem] lg:px-8 lg:py-10">
          <section class="landing-panel px-5 py-5">
            <div class="flex flex-col gap-4 border-b border-[var(--landing-terminal-line)] pb-4 md:flex-row md:items-end md:justify-between">
              <div>
                <TerminalTextReveal tag="h2" text="Informasi terbaru" textClass="text-2xl leading-tight text-[var(--landing-terminal-text)] md:text-[2rem]" />
                <TerminalTextReveal tag="p" text="Publikasi dan dokumentasi terbaru yang sudah terbit di kanal resmi HIMATEKKOM ITS." textClass="mt-3 text-sm leading-7 text-[var(--landing-terminal-soft)]" />
              </div>
              <a href={infoUrl} class="landing-inline-link inline-flex items-center gap-2 text-sm font-semibold">
                Arsip lengkap
                <ArrowRight size={16} />
              </a>
            </div>

            {#if latestArticles.length}
              <div class="divide-y divide-[var(--landing-terminal-line)]">
                {#each latestArticles as article (article.url || article.title)}
                  <a href={article.url} class="landing-article-row">
                    <div class="grid gap-3 lg:grid-cols-[9rem_minmax(0,1fr)] lg:items-start">
                      <div class="space-y-1 text-[0.73rem] text-[var(--landing-terminal-muted)]">
                        <div>{article.publishedAtLabel || 'Publikasi baru'}</div>
                        <div>{article.category}</div>
                      </div>
                      <div class="space-y-2">
                        <TerminalTextReveal tag="h3" text={article.title} textClass="text-lg leading-snug text-[var(--landing-terminal-text)]" />
                        <TerminalTextReveal tag="p" text={article.excerpt} textClass="max-w-[65ch] text-sm leading-7 text-[var(--landing-terminal-soft)]" />
                      </div>
                    </div>
                  </a>
                {/each}
              </div>
            {:else}
              <TerminalTextReveal tag="div" text="Belum ada publikasi yang terbit di papan informasi." textClass="py-5 text-sm leading-7 text-[var(--landing-terminal-soft)]" />
            {/if}
          </section>

          <aside class="grid gap-4">
            <section class="landing-panel px-5 py-5">
              <TerminalTextReveal tag="h2" text="Kanal pendukung" textClass="text-xl leading-tight text-[var(--landing-terminal-text)]" />
              <div class="mt-5 divide-y divide-[var(--landing-terminal-line)] border-t border-[var(--landing-terminal-line)]">
                {#each supportLinks as item (item.href)}
                  <a href={item.href} class="landing-support-row" target="_blank" rel="noreferrer">
                    <div>
                      <TerminalTextReveal tag="div" text={item.title} textClass="text-sm font-semibold text-[var(--landing-terminal-text)]" />
                      <TerminalTextReveal tag="p" text={item.description} textClass="mt-2 text-sm leading-7 text-[var(--landing-terminal-soft)]" />
                    </div>
                    <ArrowUpRight class="mt-0.5 shrink-0 text-[var(--landing-terminal-muted)]" size={16} />
                  </a>
                {/each}
              </div>
            </section>

            <section id="cmos" class="landing-panel px-5 py-5">
              <TerminalTextReveal tag="h2" text="CMOS" textClass="text-xl leading-tight text-[var(--landing-terminal-text)]" />
              <TerminalTextReveal
                tag="p"
                text="Computer Monitoring System digunakan pengurus untuk memantau program kerja, menyimpan dokumentasi, dan menjaga evaluasi kabinet tetap rapi dari hari ke hari."
                textClass="mt-3 text-sm leading-7 text-[var(--landing-terminal-soft)]"
              />
              <div class="mt-5 divide-y divide-[var(--landing-terminal-line)] border-t border-[var(--landing-terminal-line)]">
                {#each cmosFeatures as item (item)}
                  <TerminalTextReveal tag="div" text={item} textClass="py-3 text-sm leading-7 text-[var(--landing-terminal-soft)]" />
                {/each}
              </div>
              <a href={loginUrl} class="landing-button-primary mt-5 inline-flex items-center gap-2">
                Masuk ke CMOS
                <LayoutDashboard size={16} />
              </a>
            </section>
          </aside>
        </div>
      </section>
    </main>

    <footer data-reveal style="--reveal-delay: 520ms;" class="border-t border-[var(--landing-terminal-line)]">
      <div class="mx-auto grid max-w-[1180px] gap-6 px-5 py-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)_minmax(0,0.8fr)] lg:px-8">
        <div class="space-y-3">
          <TerminalTextReveal tag="div" text={organizationName} textClass="text-lg font-semibold text-[var(--landing-terminal-text)]" />
          <TerminalTextReveal tag="p" text="Kabinet Sentra Sinergi, Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember." textClass="max-w-[60ch] text-sm leading-7 text-[var(--landing-terminal-soft)]" />
          <TerminalTextReveal tag="p" text="Gedung Teknik Komputer, Kampus ITS Sukolilo, Surabaya." textClass="text-sm leading-7 text-[var(--landing-terminal-soft)]" />
        </div>

        {#each footerSections as section (section.title)}
          <div>
            <TerminalTextReveal tag="div" text={section.title} textClass="text-sm font-semibold text-[var(--landing-terminal-text)]" />
            <div class="mt-3 grid gap-2 text-sm text-[var(--landing-terminal-soft)]">
              {#each section.links as link (link.href)}
                <a href={link.href} class="transition-colors duration-150 hover:text-[var(--landing-terminal-text)]">{link.label}</a>
              {/each}
            </div>
          </div>
        {/each}
      </div>
    </footer>
  </div>
{:else if isInfoIndex || isInfoShow}
  <div class="min-h-screen bg-background text-foreground">
    <header class="border-b border-border/80 bg-background/92 backdrop-blur-sm">
      <div class="mx-auto flex max-w-[1180px] items-center justify-between gap-4 px-5 py-4 lg:px-8">
        <a href={homeUrl} class="flex min-w-0 items-center gap-3 text-inherit no-underline">
          <enhanced:img src={brandLogo} alt={organizationName} class="h-10 w-auto shrink-0" loading="eager" sizes="40px" />
          <div class="min-w-0">
            <div class="truncate text-sm font-semibold text-foreground">{organizationName}</div>
            <div class="truncate text-sm text-muted-foreground">Papan Informasi Publik</div>
          </div>
        </a>

        <div class="flex items-center gap-3">
          <a href={homeUrl} class="hidden text-sm text-muted-foreground transition-colors hover:text-foreground sm:inline-flex">Beranda</a>
          <a href={infoUrl} class="hidden text-sm text-muted-foreground transition-colors hover:text-foreground md:inline-flex">Arsip</a>
          <a href={loginUrl} class="inline-flex items-center gap-2 rounded-[8px] border border-border bg-card px-3.5 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">
            <LogIn size={16} />
            Masuk
          </a>
        </div>
      </div>
    </header>

    <main id="main-content" class="mx-auto max-w-[1180px] px-5 py-10 lg:px-8 lg:py-14">
      {#if isInfoIndex}
        <PublicInformationIndexPage {...infoIndex} homeUrl={homeUrl} />
      {:else if isInfoShow}
        <PublicInformationShowPage {...infoShow} homeUrl={homeUrl} infoUrl={infoUrl} />
      {/if}
    </main>
  </div>
{/if}

<style>
  .landing-terminal {
    --landing-terminal-bg: color-mix(in oklch, oklch(0.18 0.01 304) 82%, var(--brand-secondary) 18%);
    --landing-terminal-panel: color-mix(in oklch, oklch(0.22 0.012 304) 84%, var(--brand-secondary) 16%);
    --landing-terminal-panel-soft: color-mix(in oklch, oklch(0.26 0.014 304) 78%, var(--brand-secondary) 22%);
    --landing-terminal-line: color-mix(in oklch, oklch(0.5 0.026 82) 46%, var(--brand-primary) 54%);
    --landing-terminal-text: oklch(0.86 0.03 92);
    --landing-terminal-soft: color-mix(in oklch, oklch(0.74 0.02 92) 78%, var(--brand-light) 22%);
    --landing-terminal-muted: color-mix(in oklch, oklch(0.62 0.018 88) 82%, var(--brand-secondary-soft) 18%);
    --landing-terminal-accent: color-mix(in oklch, oklch(0.78 0.14 84) 78%, var(--brand-primary) 22%);
    --landing-terminal-accent-soft: color-mix(in oklch, oklch(0.56 0.08 84) 62%, var(--brand-secondary) 38%);
    background: var(--landing-terminal-bg);
    color: var(--landing-terminal-text);
    font-family: 'JetBrains Mono', monospace;
  }

  .landing-terminal :global(h1),
  .landing-terminal :global(h2),
  .landing-terminal :global(h3) {
    font-family: inherit;
    letter-spacing: -0.02em;
  }

  .landing-panel,
  .landing-command-block,
  .landing-meta-cell {
    border: 1px solid var(--landing-terminal-line);
    background: var(--landing-terminal-panel);
  }

  .landing-button-primary,
  .landing-button-secondary {
    min-height: 2.75rem;
    padding: 0.65rem 1rem;
    border: 1px solid var(--landing-terminal-line);
    color: var(--landing-terminal-text);
    font-size: 0.92rem;
    font-weight: 600;
    text-decoration: none;
    transition: background-color 180ms var(--ease-out-quart), color 180ms var(--ease-out-quart), border-color 180ms var(--ease-out-quart);
  }

  .landing-button-primary {
    background: var(--landing-terminal-accent);
    border-color: color-mix(in oklch, var(--landing-terminal-accent) 72%, var(--landing-terminal-line) 28%);
    color: oklch(0.22 0.02 74);
  }

  .landing-button-primary:hover {
    background: color-mix(in oklch, var(--landing-terminal-accent) 84%, oklch(0.42 0.05 75) 16%);
  }

  .landing-button-secondary {
    background: var(--landing-terminal-panel);
  }

  .landing-button-secondary:hover,
  .landing-inline-link:hover,
  .landing-article-row:hover,
  .landing-support-row:hover {
    background: var(--landing-terminal-panel-soft);
  }

  .landing-command-block {
    display: grid;
  }

  .landing-command-row {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: 3rem minmax(0, 1fr);
    padding: 0.95rem 1rem;
    border-top: 1px solid var(--landing-terminal-line);
  }

  .landing-command-row:first-child {
    border-top: none;
  }

  .landing-command-index {
    color: var(--landing-terminal-accent);
    font-size: 0.82rem;
    font-weight: 600;
  }

  .landing-meta-cell {
    padding: 0.9rem 1rem;
  }

  .landing-meta-cell dt {
    color: var(--landing-terminal-muted);
    font-size: 0.76rem;
  }

  .landing-meta-cell dd {
    margin: 0.5rem 0 0;
    color: var(--landing-terminal-text);
    font-size: 0.88rem;
    line-height: 1.6;
  }

  .landing-inline-link,
  .landing-article-row,
  .landing-support-row {
    color: inherit;
    text-decoration: none;
    transition: background-color 180ms var(--ease-out-quart), color 180ms var(--ease-out-quart);
  }

  .landing-article-row,
  .landing-support-row {
    display: block;
    padding: 1rem 0;
  }

  .landing-support-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
  }

  @media (max-width: 767px) {
    .landing-command-row {
      grid-template-columns: 2.4rem minmax(0, 1fr);
    }
  }
</style>
