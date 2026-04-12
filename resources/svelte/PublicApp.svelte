<script>
  import brandLogo from '../images/logokabinet.png?enhanced&w=80;160';
  import PublicInformationIndexPage from './public/PublicInformationIndexPage.svelte';
  import PublicInformationShowPage from './public/PublicInformationShowPage.svelte';
  import {
    ArrowRight,
    BookOpen,
    CheckCircle2,
    LayoutDashboard,
    LogIn,
    Menu,
    Newspaper,
    Target,
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
  } = $props();

  let mobileNavOpen = $state(false);

  const isInfoIndex = $derived(page === 'info-index');
  const isInfoShow = $derived(page === 'info-show');
  const latestArticles = $derived(latestInfo.slice(0, 3));

  const navigation = [
    { href: '#narasi', label: 'Arah Kabinet' },
    { href: '#program-kerja', label: 'Program' },
    { href: '#informasi', label: 'Arsip' },
  ];

  const heroNotes = [
    {
      label: 'Fokus publik',
      value: 'Arsip resmi, arah kerja kabinet, dan gambaran organisasi dalam satu halaman.',
    },
    {
      label: 'Untuk internal',
      value: 'CMOS dipakai pengurus untuk task, timeline, evaluasi, laporan, dan dokumentasi kerja.',
    },
    {
      label: 'Nada yang dijaga',
      value: 'Formal, rapi, dan terbuka untuk kebutuhan informasi organisasi.',
    },
  ];

  const missionItems = [
    {
      title: 'Pelayanan internal yang terukur',
      description: 'Ritme kerja dibuat lebih rapi melalui evaluasi rutin, pemantauan program, dan pembagian tanggung jawab yang jelas.',
    },
    {
      title: 'Kolaborasi yang lebih terbuka',
      description: 'Kabinet mendorong hubungan yang lebih aktif antara mahasiswa, alumni, dan mitra kegiatan.',
    },
    {
      title: 'Informasi yang mudah ditelusuri',
      description: 'Arus pengumuman, dokumentasi, dan pembaruan organisasi dijaga tetap terbuka dan tidak tercecer.',
    },
    {
      title: 'Pengembangan pengurus yang disiplin',
      description: 'Budaya kerja diarahkan agar pengurus tumbuh melalui koordinasi yang konsisten dan berkelanjutan.',
    },
  ];

  const programGroups = [
    {
      title: 'Optimalisasi',
      description: 'Penguatan sistem kerja internal, monitoring program, dan pengelolaan sumber daya manusia.',
      items: ['CMOS', 'Personalia'],
    },
    {
      title: 'Kolaborasi',
      description: 'Program yang menghubungkan mahasiswa, alumni, dan isu kesejahteraan secara lebih nyata.',
      items: ['Hi Alumni', 'Sosmas', 'Advocation Corner'],
    },
    {
      title: 'Ekspansi',
      description: 'Program yang memperluas jangkauan pembinaan karier, media, dan kanal digital organisasi.',
      items: ['COD', 'BIOS & Insight', 'Website & CMOS'],
    },
  ];

  const cmosFeatures = [
    'Monitoring program kerja dan task lintas departemen',
    'Kalender timeline dan dokumentasi kerja terpusat',
    'Evaluasi staff, laporan, dan akses arsip organisasi',
  ];

  const closeMobileNav = () => {
    mobileNavOpen = false;
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
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    });
  };
</script>

{#if page === 'landing'}
  <div class="min-h-screen bg-background text-foreground">
    <header class="sticky top-0 z-40 border-b border-border/80 bg-background/90 backdrop-blur-sm">
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
            class="hidden items-center gap-2 rounded-[10px] border border-border bg-card px-3.5 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted sm:inline-flex"
          >
            <LogIn size={16} />
            Masuk
          </a>

          <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-[10px] border border-border bg-card text-foreground md:hidden"
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
        <div class="border-t border-border/70 bg-background md:hidden">
          <div class="mx-auto grid max-w-[1180px] gap-1 px-5 py-3 lg:px-8">
            {#each navigation as item (item.href)}
              <a href={item.href} class="rounded-[10px] px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-card hover:text-foreground" onclick={closeMobileNav}>{item.label}</a>
            {/each}
            <a href={infoUrl} class="rounded-[10px] px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-card hover:text-foreground" onclick={closeMobileNav}>Papan Informasi</a>
            <a href={loginUrl} class="mt-1 rounded-[10px] border border-border bg-card px-3 py-2 text-sm font-medium text-foreground" onclick={closeMobileNav}>Masuk ke CMOS</a>
          </div>
        </div>
      {/if}
    </header>

    <main>
      <section class="border-b border-border/70">
        <div class="mx-auto grid max-w-[1180px] gap-10 px-5 py-14 lg:grid-cols-[minmax(0,1.35fr)_21rem] lg:px-8 lg:py-20">
          <div class="space-y-7">
            <div class="max-w-[64ch] space-y-5">
              <p class="text-sm font-medium text-brand-secondary">Platform resmi kabinet 2026</p>
              <h1 class="max-w-[13ch] text-5xl leading-none text-foreground md:text-6xl lg:text-[4.6rem]">
                Kerja kabinet yang lebih tertata dan mudah ditelusuri.
              </h1>
              <p class="max-w-[60ch] text-base leading-8 text-[var(--text-soft)] md:text-lg">
                Halaman ini menjelaskan arah kerja Kabinet Sentra Sinergi, merangkum fokus program, dan membuka arsip publik organisasi. Untuk pengurus internal, akses ke {appName} tetap tersedia sebagai ruang kerja operasional.
              </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
              <a href="#informasi" class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-brand-primary px-4 py-2.5 text-sm font-semibold text-[var(--primary-foreground)] transition-colors hover:bg-brand-hover">
                Baca arsip informasi
                <ArrowRight size={16} />
              </a>
              <a href="#narasi" class="inline-flex items-center justify-center gap-2 rounded-[10px] border border-border bg-card px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                Pahami arah kabinet
                <Target size={16} />
              </a>
            </div>

            <div class="grid gap-4 border-t border-border pt-5 md:grid-cols-3">
              <div>
                <div class="text-sm font-semibold text-foreground">Kabinet Sentra Sinergi</div>
                <p class="mt-2 max-w-[28ch] text-sm leading-6 text-muted-foreground">Arah kerja 2026 dibangun untuk koordinasi yang lebih rapi, terbuka, dan dapat dipertanggungjawabkan.</p>
              </div>
              <div>
                <div class="text-sm font-semibold text-foreground">Papan Informasi Publik</div>
                <p class="mt-2 max-w-[28ch] text-sm leading-6 text-muted-foreground">Pengumuman, dokumentasi kegiatan, dan pembaruan organisasi dikumpulkan dalam satu arsip resmi.</p>
              </div>
              <div>
                <div class="text-sm font-semibold text-foreground">CMOS untuk pengurus</div>
                <p class="mt-2 max-w-[28ch] text-sm leading-6 text-muted-foreground">Dashboard, task, timeline, evaluasi, dan laporan tetap berjalan sebagai sistem kerja internal.</p>
              </div>
            </div>
          </div>

          <aside class="rounded-[10px] border border-border bg-card px-5 py-6">
            <div class="border-b border-border pb-5">
              <enhanced:img src={brandLogo} alt={organizationName} class="h-16 w-16 object-contain" />
              <div class="mt-4 text-2xl leading-tight text-foreground">{organizationName}</div>
              <p class="mt-2 text-sm leading-6 text-muted-foreground">Himpunan Mahasiswa Teknik Komputer ITS dengan ruang publik yang lebih rapi dan akses internal yang lebih terstruktur.</p>
            </div>

            <div class="grid gap-4 pt-5">
              {#each heroNotes as note (note.label)}
                <div class="border-b border-border/80 pb-4 last:border-b-0 last:pb-0">
                  <div class="text-sm font-semibold text-foreground">{note.label}</div>
                  <p class="mt-2 text-sm leading-6 text-muted-foreground">{note.value}</p>
                </div>
              {/each}
            </div>
          </aside>
        </div>
      </section>

      <section id="narasi" class="scroll-mt-24 border-b border-border/70">
        <div class="mx-auto grid max-w-[1180px] gap-10 px-5 py-14 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:px-8 lg:py-18">
          <div class="space-y-4">
            <p class="text-sm font-medium text-brand-secondary">Arah kabinet</p>
            <h2 class="max-w-[12ch] text-4xl leading-tight text-foreground md:text-5xl">Narasi publik yang jelas sebelum masuk ke sistem kerja.</h2>
            <p class="max-w-[56ch] text-base leading-8 text-[var(--text-soft)]">
              Visi dan misi kabinet tidak ditampilkan sebagai slogan singkat. Bagian ini merangkum alasan mengapa tata kelola, kolaborasi, dan keterbukaan informasi menjadi prioritas kerja untuk satu periode penuh.
            </p>
          </div>

          <div class="grid gap-8">
            <section class="border-b border-border pb-6">
              <div class="text-sm font-semibold text-brand-primary">Visi</div>
              <p class="mt-4 max-w-[50ch] text-3xl leading-tight text-foreground md:text-[2.2rem]">
                Menjadikan HIMATEKKOM ITS sebagai himpunan yang progresif, inklusif, dan berdampak dengan tata kelola yang profesional serta budaya kolaborasi yang kuat.
              </p>
            </section>

            <section class="grid gap-4 sm:grid-cols-2">
              {#each missionItems as item, index (`${index}-${item.title}`)}
                <article class="border-t border-border pt-4">
                  <div class="text-sm font-semibold text-brand-primary">0{index + 1}</div>
                  <h3 class="mt-2 text-xl text-foreground">{item.title}</h3>
                  <p class="mt-3 text-sm leading-7 text-muted-foreground">{item.description}</p>
                </article>
              {/each}
            </section>
          </div>
        </div>
      </section>

      <section id="program-kerja" class="scroll-mt-24 border-b border-border/70 bg-muted/45">
        <div class="mx-auto grid max-w-[1180px] gap-8 px-5 py-14 lg:px-8 lg:py-18">
          <div class="max-w-[58ch] space-y-4">
            <p class="text-sm font-medium text-brand-secondary">Program kabinet</p>
            <h2 class="text-4xl leading-tight text-foreground md:text-5xl">Tiga rumpun kerja untuk membaca fokus kabinet secara cepat.</h2>
            <p class="text-base leading-8 text-[var(--text-soft)]">
              Struktur program disusun agar publik tidak harus memahami seluruh terminologi internal sekaligus. Setiap rumpun menunjukkan jenis kontribusi yang dibangun kabinet selama satu periode kerja.
            </p>
          </div>

          <div class="grid gap-6 lg:grid-cols-3">
            {#each programGroups as group, index (group.title)}
              <article class={`rounded-[10px] border border-border bg-card px-5 py-6 ${index === 1 ? 'lg:mt-8' : ''}`}>
                <div class="text-sm font-semibold text-brand-primary">{group.title}</div>
                <p class="mt-4 text-xl leading-snug text-foreground">{group.description}</p>
                <div class="mt-6 grid gap-3 border-t border-border pt-4">
                  {#each group.items as item (`${group.title}-${item}`)}
                    <div class="text-sm leading-6 text-muted-foreground">{item}</div>
                  {/each}
                </div>
              </article>
            {/each}
          </div>
        </div>
      </section>

      <section id="informasi" class="scroll-mt-24 border-b border-border/70">
        <div class="mx-auto grid max-w-[1180px] gap-8 px-5 py-14 lg:px-8 lg:py-18">
          <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div class="max-w-[58ch] space-y-4">
              <p class="text-sm font-medium text-brand-secondary">Arsip publik</p>
              <h2 class="text-4xl leading-tight text-foreground md:text-5xl">Pembaruan organisasi diletakkan di tempat yang mudah dicari kembali.</h2>
              <p class="text-base leading-8 text-[var(--text-soft)]">
                Papan informasi menampung pengumuman, dokumentasi kegiatan, dan publikasi resmi agar pengunjung tidak harus menebak apa yang sedang berlangsung di HIMATEKKOM ITS.
              </p>
            </div>
            <a href={infoUrl} class="inline-flex items-center gap-2 text-sm font-semibold text-foreground transition-colors hover:text-brand-secondary">
              Buka seluruh arsip
              <ArrowRight size={16} />
            </a>
          </div>

          {#if !latestArticles.length}
            <div class="grid gap-8 rounded-[10px] border border-border bg-card px-6 py-8 lg:grid-cols-[minmax(0,1.15fr)_18rem] lg:items-start">
              <div class="space-y-4">
                <div class="inline-flex items-center gap-2 text-sm font-medium text-brand-primary">
                  <BookOpen size={16} />
                  Arsip publik sedang disiapkan
                </div>
                <p class="max-w-[58ch] text-2xl leading-snug text-foreground md:text-3xl">
                  Saat ini belum ada publikasi yang tampil di beranda, tetapi seluruh pengumuman resmi akan diarahkan ke arsip yang sama agar tidak tercecer.
                </p>
                <p class="max-w-[58ch] text-sm leading-7 text-muted-foreground">
                  Pengunjung tetap dapat membuka halaman arsip untuk melihat struktur kategori, alur pencarian, dan pembaruan yang akan diterbitkan kabinet berikutnya.
                </p>
              </div>

              <div class="border-t border-border pt-4 lg:border-t-0 lg:border-l lg:pl-6 lg:pt-0">
                <div class="text-sm font-semibold text-foreground">Yang tersedia di arsip</div>
                <div class="mt-3 grid gap-3 text-sm leading-6 text-muted-foreground">
                  <div>Pengumuman organisasi dan pembaruan kegiatan.</div>
                  <div>Dokumentasi program yang telah berjalan.</div>
                  <div>Kategori agar publikasi mudah ditelusuri ulang.</div>
                </div>
                <a href={infoUrl} class="mt-5 inline-flex items-center gap-2 rounded-[10px] border border-border bg-background px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                  Lihat struktur arsip
                  <Newspaper size={16} />
                </a>
              </div>
            </div>
          {:else}
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.12fr)_minmax(0,0.88fr)]">
              <a href={latestArticles[0].url} class="rounded-[10px] border border-border bg-card px-6 py-6 text-inherit no-underline transition-colors hover:bg-muted/45">
                <div class="text-sm font-medium text-brand-primary">{formatDate(latestArticles[0].publishedAt) || 'Publikasi baru'}</div>
                <h3 class="mt-4 max-w-[18ch] text-4xl leading-tight text-foreground">{latestArticles[0].title}</h3>
                <p class="mt-4 max-w-[58ch] text-sm leading-7 text-muted-foreground">{latestArticles[0].excerpt}</p>
                <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                  <span>{latestArticles[0].category}</span>
                  <span class="text-foreground">Buka artikel</span>
                </div>
              </a>

              <div class="rounded-[10px] border border-border bg-card px-5 py-6">
                <div class="text-sm font-semibold text-foreground">Arsip terbaru</div>
                <div class="mt-4 grid gap-4">
                  {#each latestArticles.slice(1) as article (article.url || article.title)}
                    <a href={article.url} class="border-t border-border pt-4 text-inherit no-underline transition-colors hover:text-brand-secondary first:border-t-0 first:pt-0">
                      <div class="text-sm text-brand-primary">{formatDate(article.publishedAt) || 'Publikasi baru'}</div>
                      <h4 class="mt-2 text-xl leading-snug text-foreground">{article.title}</h4>
                      <p class="mt-2 text-sm leading-7 text-muted-foreground">{article.excerpt}</p>
                    </a>
                  {/each}
                </div>
              </div>
            </div>
          {/if}
        </div>
      </section>

      <section id="cmos" class="scroll-mt-24">
        <div class="mx-auto grid max-w-[1180px] gap-8 px-5 py-14 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)] lg:px-8 lg:py-18">
          <div class="space-y-4">
            <p class="text-sm font-medium text-brand-secondary">Untuk pengurus internal</p>
            <h2 class="text-4xl leading-tight text-foreground md:text-5xl">CMOS dipakai ketika pekerjaan kabinet sudah berjalan dari hari ke hari.</h2>
            <p class="max-w-[58ch] text-base leading-8 text-[var(--text-soft)]">
              {appName} bukan halaman publik. Sistem ini dipakai pengurus untuk menjaga ritme kerja tetap terpantau, mulai dari task, timeline, evaluasi staff, laporan, hingga dokumentasi kerja organisasi.
            </p>
            <a href={loginUrl} class="inline-flex items-center gap-2 rounded-[10px] bg-brand-primary px-4 py-2.5 text-sm font-semibold text-[var(--primary-foreground)] transition-colors hover:bg-brand-hover">
              Masuk ke CMOS
              <LayoutDashboard size={16} />
            </a>
          </div>

          <div class="rounded-[10px] border border-border bg-card px-6 py-6">
            <div class="grid gap-4 border-b border-border pb-5">
              <div class="text-sm font-semibold text-foreground">Apa yang dikerjakan di dalamnya</div>
              <p class="max-w-[52ch] text-sm leading-7 text-muted-foreground">
                Pengurus internal memakai satu sistem kerja yang sama agar pembagian tanggung jawab, pelacakan progres, dan evaluasi tidak berjalan di tempat yang terpisah-pisah.
              </p>
            </div>

            <div class="mt-5 grid gap-4">
              {#each cmosFeatures as item (item)}
                <div class="flex items-start gap-3 border-t border-border pt-4 first:border-t-0 first:pt-0">
                  <span class="mt-0.5 text-brand-primary"><CheckCircle2 size={16} /></span>
                  <span class="text-sm leading-7 text-muted-foreground">{item}</span>
                </div>
              {/each}
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="border-t border-border/80 bg-background/80">
      <div class="mx-auto grid max-w-[1180px] gap-8 px-5 py-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.55fr)_minmax(0,0.65fr)] lg:px-8">
        <div class="space-y-3">
          <div class="text-lg text-foreground">{organizationName}</div>
          <p class="max-w-[55ch] text-sm leading-7 text-muted-foreground">
            Himpunan Mahasiswa Teknik Komputer, Institut Teknologi Sepuluh Nopember. Halaman publik ini merangkum arah kabinet 2026 dan menghubungkan pengunjung ke arsip resmi organisasi.
          </p>
        </div>

        <div>
          <div class="text-sm font-semibold text-foreground">Navigasi</div>
          <div class="mt-3 grid gap-2 text-sm text-muted-foreground">
            {#each navigation as item (item.href)}
              <a href={item.href} class="transition-colors hover:text-foreground">{item.label}</a>
            {/each}
          </div>
        </div>

        <div>
          <div class="text-sm font-semibold text-foreground">Akses</div>
          <div class="mt-3 grid gap-2 text-sm text-muted-foreground">
            <a href={infoUrl} class="transition-colors hover:text-foreground">Papan Informasi</a>
            <a href={loginUrl} class="transition-colors hover:text-foreground">Masuk ke CMOS</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
{:else if isInfoIndex || isInfoShow}
  <div class="min-h-screen bg-background text-foreground">
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
          <a href={loginUrl} class="inline-flex items-center gap-2 rounded-[10px] border border-border bg-card px-3.5 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">
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
