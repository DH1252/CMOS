# Frontend Documentation - CMOS HIMATEKKOM

Based on visual inspection and DOM extraction, here is the comprehensive breakdown of the public and internal pages of the HIMATEKKOM ITS website.

---

## 1. Public Landing Page (`/`)
**Theme**: Dark mode (deep purple/black backgrounds with vibrant yellow/gold and purple accents).
**Target Audience**: General public, students, alumni.

### Sections Breakdown:
*   **Navigation Bar (Top)**:
    *   Logo (Left)
    *   Links: Informasi, Profil Organisasi, Program Kerja, CMOS.
    *   CTA Button: "Login" (Yellow button with icon).

*   **Hero Section**:
    *   **Badge**: "WEBSITE RESMI HIMATEKKOM ITS 2026"
    *   **Headline**: "KABINET HIMATEK" (HIMATEK is stylized with a purple-to-gold gradient).
    *   **Description**: Introduces the website as the information hub for HIMATEKKOM ITS 2026, built for transparency, documentation, and collaboration under the "Sentra Sinergi" cabinet.
    *   **CTAs**: "Lihat Informasi" (Solid Yellow) and "Profil HIMATEKKOM" (Outlined Yellow).
    *   **Visual**: Hexagonal logo of HIMATEKKOM ITS 2026 with "Kabinet Sentra Sinergi".

*   **Profil Organisasi (About)**:
    *   **Visi Organisasi**: A paragraph outlining the vision to make the association progressive, inclusive, and impactful.
    *   **Misi Strategis**: 3 numbered points detailing:
        1. Strengthening internal services.
        2. Encouraging cross-party collaboration.
        3. Realizing information transparency.

*   **Papan Informasi (Latest Updates)**:
    *   Displays recent news/articles.
    *   Cards show an image, date (e.g., 21 Feb 2026), Title, excerpt, and "Baca Detail" link.
    *   "Lihat Semua Informasi" button at the bottom.

*   **Resources & Social Links**:
    *   Two dark cards with yellow text.
    *   Left Card: Official documents ("Dokumen Organisasi", "Materi Presentasi").
    *   Right Card: Social media updates ("@sentrasinergi").

*   **Program Kerja (Work Programs)**:
    *   Divided into three pillars:
        *   **Optimalisasi** (Purple icon): CMOS, Personalia.
        *   **Kolaborasi** (Purple icon): Hi Alumni, Sosmas, Advocation Corner.
        *   **Ekspansi** (Purple icon): COD, BIOS, TEKKOM Insight, Buku Panduan Kaderisasi, Website HIMATEKKOM.
    *   Each program has a badge indicating the responsible department (e.g., PSDM, Medfo, Hublu).

*   **Flagship Product (CMOS)**:
    *   Dedicated section for "CMOS - Computer Monitoring System".
    *   Highlights features: Real-time monitoring, performance evaluation, centralized documentation, data-driven decisions.
    *   "Akses CMOS" CTA button.

*   **Footer**:
    *   Address, contact email, social links.
    *   Quick Links to sections.
    *   Copyright 2026.

---

## 2. Internal Dashboard (`/dashboard`)
**Theme**: Light mode (clean white/gray backgrounds with yellow/orange accents). Dark mode toggle is available.
**Target Audience**: Logged-in staff/members (e.g., Dharon Yusuf Fuadi).

### Layout & Components:
*   **Sidebar Navigation (Left)**:
    *   **Branding**: CMOS logo (Yellow square with white text).
    *   **Menu Utama**: Dashboard (Active), Pengumuman, Papan Informasi.
    *   **Program Kerja**: Proker Saya, Task Saya.
    *   **Kalender**: Kalender, Daftar Timeline.
    *   **Akses**: Google Drive, Kumpulan Link.
    *   **Penilaian**: Nilai Saya.
    *   **Bottom Profile Area**: User Avatar, Name ("Dharon Yusuf Fuadi"), Role ("Staff"), and a "Logout" button.

*   **Top Navigation Bar**:
    *   Hamburger menu toggle.
    *   Page Title ("Dashboard").
    *   Actions: Dark mode toggle (moon icon), Notifications (bell icon), Profile dropdown menu.

*   **Main Content Area (Right)**:
    *   **Welcome Banner (Yellow Card)**:
        *   Displays current Day, Date (e.g., "Sabtu, 11 April 2026").
        *   Personalized greeting: "Selamat Pagi, Dharon Yusuf Fuadi!".
        *   Current Time (e.g., 06:06 WIB).
    *   **Quick Links Bar**: Outlined buttons for "Kalender", "Drive", and "Links".
    *   **Overview Stats (White Cards)**:
        *   Proker (Count: 0)
        *   Task Selesai (Count: 0)
        *   Pending Task (Count: 0)
    *   **Widgets (Grid Layout)**:
        *   **Progress Task**: Visual chart area showing Todo (0), In Progress (0), Done (0).
        *   **Timeline**: Shows upcoming events. Currently empty ("Tidak ada timeline mendatang"). Link to "Lihat ->".
        *   **Task Terbaru**: Table with columns: TASK, STATUS, PROGRESS, DEADLINE. Currently empty ("Tidak ada task"). Link to "Semua ->".
        *   **Proker Saya**: List of user's programs. Currently empty ("Belum ada proker"). Link to "Semua ->".

*   **Floating Action Button (Bottom Right)**:
    *   Yellow circular button with a chat bubble icon, likely for internal messaging or support.

---

## 3. Login Page (`/login`)
*(From previous steps)*
- Email input field (`nama@email.com`)
- Password input field (`••••••••`)
- "Ingat saya di perangkat ini" checkbox
- "Masuk ke Dashboard" submit button
