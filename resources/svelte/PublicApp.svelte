<script>
  import { onMount } from 'svelte';
  import brandLogo from '../images/logokabinet.png?enhanced&w=80;160';
  import himatekkomBanner from '../images/himatekkom.jpg?enhanced&w=480;960;1440';
  import { inertiaEnhance } from '$lib/inertia-enhance.js';
  import PublicInformationIndexPage from './public/PublicInformationIndexPage.svelte';
  import PublicInformationShowPage from './public/PublicInformationShowPage.svelte';
  import {
    ArrowRight,
    ArrowUpRight,
    LayoutDashboard,
    LogIn,
    Menu,
    Newspaper,
    X,
  } from 'lucide-svelte';

  let {
    page = 'landing',
    appName = 'CMOS',
    organizationName = 'HIMATEKKOM ITS',
    homeUrl = '/',
    loginUrl = '/login',
    infoUrl = '/informasi',
    latestInfo = [],
    logoUrl = '/images/logokabinet.png',
    infoIndex = {},
    infoShow = {},
    enableSpaNavigation = false,
  } = $props();

  let mobileNavOpen = $state(false);

  const isInfoIndex = $derived(page === 'info-index');
  const isInfoShow = $derived(page === 'info-show');
  const latestArticles = $derived(latestInfo.slice(0, 3));
  const fallbackImage = $derived(logoUrl || '/images/logokabinet.png');

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

  const closeMobileNav = () => {
    mobileNavOpen = false;
  };

  const handleImageError = (event) => {
    if (event.currentTarget.src === fallbackImage) {
      return;
    }

    event.currentTarget.src = fallbackImage;
  };

  const formatDate = (value) => {
    if (!value) {
      return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
      return '';
    }

    return date.toLocaleDateString('id-ID', {
      timeZone: 'Asia/Jakarta',
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    });
  };

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
    document.documentElement.setAttribute('data-theme', 'public');
  });
</script>

<svelte:head>
  <title>{pageTitle}</title>
  <meta name="description" content={pageDescription} />
</svelte:head>

{#if page === 'landing'}
  <div class="min-h-screen bg-background text-foreground" use:inertiaEnhance={enableSpaNavigation}>
    <header class="border-b border-border bg-background/96">
      <div class="mx-auto flex max-w-[1180px] items-center justify-between gap-4 px-5 py-4 lg:px-8">
        <a href={homeUrl} class="flex min-w-0 items-center gap-3 text-inherit no-underline">
          <enhanced:img src={brandLogo} alt={organizationName} class="h-10 w-10 shrink-0 object-contain" loading="eager" fetchpriority="high" />
          <div class="min-w-0">
            <div class="truncate text-sm font-semibold text-foreground">{organizationName}</div>
            <div class="truncate text-sm text-muted-foreground">Kabinet Sentra Sinergi 2026</div>
          </div>
        </a>

        <nav class="hidden items-center gap-6 md:flex">
          {#each navigation as item (item.href)}
            <a href={item.href} class="text-sm text-muted-foreground transition-colors hover:text-foreground">{item.label}</a>
          {/each}
        </nav>

        <div class="flex items-center gap-2">
          <a
            href={loginUrl}
            class="hidden items-center gap-2 rounded-[8px] border border-border bg-card px-3.5 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted sm:inline-flex"
          >
            <LogIn size={16} />
            Masuk
          </a>

          <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-[8px] border border-border bg-card text-foreground md:hidden"
            aria-label="Toggle navigation"
            onclick={() => (mobileNavOpen = !mobileNavOpen)}
          >
            {#if mobileNavOpen}
              <X size={18} />
            {:else}
              <Menu size={18} />
            {/if}
          </button>
        </div>
      </div>

      {#if mobileNavOpen}
        <div class="border-t border-border bg-background md:hidden">
          <div class="mx-auto grid max-w-[1180px] gap-1 px-5 py-3 lg:px-8">
            {#each navigation as item (item.href)}
              <a href={item.href} class="rounded-[8px] px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-card hover:text-foreground" onclick={closeMobileNav}>{item.label}</a>
            {/each}
            <a href={loginUrl} class="mt-1 rounded-[8px] border border-border bg-card px-3 py-2 text-sm font-medium text-foreground" onclick={closeMobileNav}>Masuk ke CMOS</a>
          </div>
        </div>
      {/if}
    </header>

    <main>
      <section class="border-b border-border">
        <div class="mx-auto grid max-w-[1180px] gap-10 px-5 py-14 lg:grid-cols-[minmax(0,1.05fr)_24rem] lg:px-8 lg:py-18">
          <div class="space-y-6">
            <h1 class="max-w-[14ch] text-4xl leading-tight text-foreground md:text-5xl lg:text-[3.5rem]">
              Website resmi HIMATEKKOM ITS 2026 untuk informasi publik dan kerja kabinet.
            </h1>
            <p class="max-w-[62ch] text-base leading-8 text-[var(--text-soft)] md:text-lg">Ringkasan publik dan kanal resmi HIMATEKKOM ITS.</p>

            <div class="flex flex-col gap-3 sm:flex-row">
              <a href="#informasi" class="inline-flex items-center justify-center gap-2 rounded-[8px] bg-brand-primary px-4 py-2.5 text-sm font-semibold text-[var(--primary-foreground)] transition-colors hover:bg-brand-hover">
                Lihat informasi terbaru
                <ArrowRight size={16} />
              </a>
              <a href="#profil" class="inline-flex items-center justify-center gap-2 rounded-[8px] border border-border bg-card px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                Profil organisasi
                <ArrowRight size={16} />
              </a>
            </div>

            <div class="grid gap-4 border-t border-border pt-5 md:grid-cols-3">
              <div>
                <div class="text-sm font-semibold text-foreground">Kabinet</div>
                <p class="mt-2 text-sm leading-7 text-muted-foreground">Tata kelola.</p>
              </div>
              <div>
                <div class="text-sm font-semibold text-foreground">Arsip publik</div>
                <p class="mt-2 text-sm leading-7 text-muted-foreground">Arsip publik.</p>
              </div>
              <div>
                <div class="text-sm font-semibold text-foreground">Sistem internal</div>
                <p class="mt-2 text-sm leading-7 text-muted-foreground">Sistem internal kabinet.</p>
              </div>
            </div>
          </div>

          <aside class="overflow-hidden rounded-[8px] border border-border bg-card">
            <enhanced:img
              src={himatekkomBanner}
              alt="Kabinet Sentra Sinergi"
              class="h-56 w-full border-b border-border object-cover"
              loading="eager"
              fetchpriority="high"
              sizes="(min-width: 1024px) 24rem, 100vw"
            />
            <div class="grid gap-4 px-5 py-5">
              <div>
                <div class="text-lg font-semibold text-foreground">Kabinet Sentra Sinergi</div>
                <p class="mt-2 text-sm leading-7 text-muted-foreground">Himpunan Mahasiswa Teknik Komputer Institut Teknologi Sepuluh Nopember.</p>
              </div>

              <div class="grid gap-3 border-t border-border pt-4">
                {#each quickFacts as item (item)}
                  <p class="text-sm leading-7 text-muted-foreground">{item}</p>
                {/each}
              </div>
            </div>
          </aside>
        </div>
      </section>

      <section id="profil" class="scroll-mt-24 border-b border-border">
        <div class="mx-auto grid max-w-[1180px] gap-10 px-5 py-14 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:px-8 lg:py-18">
          <div class="space-y-5">
            <h2 class="text-3xl leading-tight text-foreground md:text-4xl">Tentang HIMATEKKOM ITS 2026</h2>
            <p class="max-w-[60ch] text-base leading-8 text-[var(--text-soft)]">Akses internal kabinet.</p>
            <div class="border-t border-border pt-4">
              <div class="text-sm font-semibold text-foreground">Visi organisasi</div>
              <p class="mt-3 text-sm leading-8 text-muted-foreground">{vision}</p>
            </div>
          </div>

          <div class="grid gap-4">
            {#each missionItems as item, index (`${index}-${item}`)}
              <article class="rounded-[8px] border border-border bg-card px-5 py-5">
                <div class="text-sm font-semibold text-foreground">Misi {index + 1}</div>
                <p class="mt-3 text-sm leading-8 text-muted-foreground">{item}</p>
              </article>
            {/each}
          </div>
        </div>
      </section>

      <section id="program-kerja" class="scroll-mt-24 border-b border-border bg-muted/35">
        <div class="mx-auto max-w-[1180px] px-5 py-14 lg:px-8 lg:py-18">
          <div class="max-w-[64ch] space-y-4">
            <h2 class="text-3xl leading-tight text-foreground md:text-4xl">Rangkaian program kerja kabinet</h2>
            <p class="text-base leading-8 text-[var(--text-soft)]">Tiga rumpun kerja utama.</p>
          </div>

          <div class="mt-8 grid gap-6 lg:grid-cols-3">
            {#each programGroups as group (group.title)}
              <section class="rounded-[8px] border border-border bg-card px-5 py-5">
                <h3 class="text-xl text-foreground">{group.title}</h3>
                <p class="mt-3 text-sm leading-7 text-muted-foreground">{group.description}</p>

                <div class="mt-5 grid gap-4 border-t border-border pt-4">
                  {#each group.items as item (`${group.title}-${item.name}`)}
                    <article class="border-b border-border pb-4 last:border-b-0 last:pb-0">
                      <div class="text-sm font-semibold text-foreground">{item.name}</div>
                      <div class="mt-1 text-sm text-muted-foreground">{item.unit}</div>
                      <p class="mt-2 text-sm leading-7 text-muted-foreground">{item.description}</p>
                    </article>
                  {/each}
                </div>
              </section>
            {/each}
          </div>
        </div>
      </section>

      <section id="informasi" class="scroll-mt-24 border-b border-border">
        <div class="mx-auto max-w-[1180px] px-5 py-14 lg:px-8 lg:py-18">
          <div class="grid gap-8 lg:grid-cols-[minmax(0,1.05fr)_20rem] lg:items-start">
            <div class="space-y-6">
              <div class="space-y-4">
                <h2 class="text-3xl leading-tight text-foreground md:text-4xl">Informasi terbaru dan kanal publik resmi</h2>
                <p class="max-w-[62ch] text-base leading-8 text-[var(--text-soft)]">Berita, pengumuman, dan dokumentasi.</p>
              </div>

              {#if latestArticles.length}
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                  {#each latestArticles as article (article.url || article.title)}
                    <a href={article.url} class="overflow-hidden rounded-[8px] border border-border bg-card text-inherit no-underline transition-colors hover:bg-muted/40">
                      {#if article.coverImage}
                        <img
                          src={article.coverImage}
                          alt={article.title}
                          class="h-28 w-full border-b border-border object-cover"
                          loading="lazy"
                          decoding="async"
                          sizes="(min-width: 1280px) 18rem, (min-width: 768px) 40vw, 100vw"
                          onerror={handleImageError}
                        />
                      {:else}
                        <div class="flex h-44 items-center justify-center border-b border-border bg-muted/40 px-5 text-sm text-muted-foreground">
                          Publikasi resmi HIMATEKKOM ITS
                        </div>
                      {/if}
                      <div class="grid gap-3 px-5 py-5">
                        <div class="text-sm text-muted-foreground">{formatDate(article.publishedAt) || 'Publikasi baru'}</div>
                        <h3 class="text-xl leading-snug text-foreground">{article.title}</h3>
                        <p class="text-sm leading-7 text-muted-foreground">{article.excerpt}</p>
                        <div class="text-sm font-medium text-foreground">{article.category}</div>
                      </div>
                    </a>
                  {/each}
                </div>
              {:else}
                <div class="rounded-[8px] border border-border bg-card px-6 py-6">
                  <div class="flex items-center gap-2 text-sm font-medium text-foreground">
                    <Newspaper size={16} />
                    Arsip publik
                  </div>
                  <p class="mt-3 max-w-[60ch] text-sm leading-8 text-muted-foreground">Publikasi baru akan muncul di sini.</p>
                </div>
              {/if}

              <a href={infoUrl} class="inline-flex items-center gap-2 text-sm font-semibold text-foreground transition-colors hover:text-brand-hover">
                Buka seluruh papan informasi
                <ArrowRight size={16} />
              </a>
            </div>

            <aside class="grid gap-4">
              {#each supportLinks as item (item.href)}
                <a href={item.href} class="rounded-[8px] border border-border bg-card px-5 py-5 text-inherit no-underline transition-colors hover:bg-muted/40" target="_blank" rel="noreferrer">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <div class="text-base font-semibold text-foreground">{item.title}</div>
                      <p class="mt-2 text-sm leading-7 text-muted-foreground">{item.description}</p>
                    </div>
                    <ArrowUpRight class="mt-0.5 shrink-0 text-muted-foreground" size={16} />
                  </div>
                </a>
              {/each}
            </aside>
          </div>
        </div>
      </section>

      <section id="cmos" class="scroll-mt-24">
        <div class="mx-auto grid max-w-[1180px] gap-8 px-5 py-14 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] lg:px-8 lg:py-18">
          <div class="space-y-4">
            <h2 class="text-3xl leading-tight text-foreground md:text-4xl">CMOS - Computer Monitoring System</h2>
            <p class="max-w-[60ch] text-base leading-8 text-[var(--text-soft)]">
              CMOS adalah sistem monitoring dan pelaporan program kerja yang dikembangkan untuk mendukung tata kelola organisasi HIMATEKKOM ITS 2026 secara akuntabel, transparan, dan berbasis data. Akses ini ditujukan untuk pengurus internal yang menjalankan operasional kabinet dari hari ke hari.
            </p>
            <a href={loginUrl} class="inline-flex items-center gap-2 rounded-[8px] bg-brand-primary px-4 py-2.5 text-sm font-semibold text-[var(--primary-foreground)] transition-colors hover:bg-brand-hover">
              Masuk ke CMOS
              <LayoutDashboard size={16} />
            </a>
          </div>

          <div class="rounded-[8px] border border-border bg-card px-6 py-6">
            <div class="text-sm font-semibold text-foreground">Yang tersedia di CMOS</div>
            <div class="mt-4 grid gap-4 border-t border-border pt-4">
              {#each cmosFeatures as item (item)}
                <div class="border-b border-border pb-4 text-sm leading-7 text-muted-foreground last:border-b-0 last:pb-0">{item}</div>
              {/each}
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="border-t border-border bg-background">
      <div class="mx-auto grid max-w-[1180px] gap-8 px-5 py-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.55fr)_minmax(0,0.55fr)] lg:px-8">
        <div class="space-y-3">
          <div class="text-lg font-semibold text-foreground">{organizationName}</div>
            <p class="max-w-[60ch] text-sm leading-7 text-muted-foreground">Kabinet Sentra Sinergi, Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember.</p>
          <p class="text-sm leading-7 text-muted-foreground">Gedung Teknik Komputer, Kampus ITS Sukolilo, Surabaya.</p>
        </div>

        {#each footerSections as section (section.title)}
          <div>
            <div class="text-sm font-semibold text-foreground">{section.title}</div>
            <div class="mt-3 grid gap-2 text-sm text-muted-foreground">
              {#each section.links as link (link.href)}
                <a href={link.href} class="transition-colors hover:text-foreground">{link.label}</a>
              {/each}
            </div>
          </div>
        {/each}
      </div>
    </footer>
  </div>
{:else if isInfoIndex || isInfoShow}
  <div class="min-h-screen bg-background text-foreground" use:inertiaEnhance={enableSpaNavigation}>
    <header class="border-b border-border/80 bg-background/92 backdrop-blur-sm">
      <div class="mx-auto flex max-w-[1180px] items-center justify-between gap-4 px-5 py-4 lg:px-8">
        <a href={homeUrl} class="flex min-w-0 items-center gap-3 text-inherit no-underline">
          <enhanced:img src={brandLogo} alt={organizationName} class="h-10 w-10 shrink-0 object-contain" />
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

    <main class="mx-auto max-w-[1180px] px-5 py-10 lg:px-8 lg:py-14">
      {#if isInfoIndex}
        <PublicInformationIndexPage {...infoIndex} homeUrl={homeUrl} />
      {:else if isInfoShow}
        <PublicInformationShowPage {...infoShow} homeUrl={homeUrl} infoUrl={infoUrl} />
      {/if}
    </main>
  </div>
{/if}
