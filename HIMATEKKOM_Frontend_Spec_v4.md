# HIMATEKKOM ITS 2026 — Frontend Specification (v4)

> **Purpose:** Comprehensive prompt/specification for GPT-5.4 to build the frontend of `himatekkom.ceits.id`. All content sections are left as placeholders to be filled. The visual theme is **dark/black-purple** with **gold** as the primary accent and **purple** as the secondary accent.

---

## 1. Project Overview

**Website:** `himatekkom.ceits.id`
**Organization:** HIMATEKKOM ITS — Computer Engineering Student Association, FTEIC ITS
**Cabinet:** Kabinet Sentra Sinergi (2026 period)
**Platform name:** CMOS (internal management dashboard)
**Parent platform:** CEITS.id

Two surfaces:
1. **Public Landing Page** — hero, visi/misi, program kerja, navigation, contact
2. **Authenticated Dashboard (CMOS)** — internal member management with sidebar navigation

---

## 2. Design System

### 2.1 Color Palette (Dark + Gold + Purple)

| Token | Hex | Usage |
|-------|-----|-------|
| **— Core —** | | |
| `--bg-dark` | `#0D0D1A` / `#111122` | Primary page background, near-black with purple undertone |
| `--bg-surface` | `#1A1A2E` | Slightly lighter surfaces, nav bar background |
| `--gold-primary` | `#F5C518` / `#FFD700` | Primary accent — CTA fills, "Sentra" text, card borders, active states |
| `--gold-dark` | `#D4A017` | Outlined button borders, subtle gold details |
| `--purple-primary` | `#7B2FF7` / `#8B5CF6` | Secondary accent — "Sinergi" text, gradients, decorative elements |
| `--purple-dark` | `#4C1D95` | Deeper purple for gradient stops |
| **— Text —** | | |
| `--text-white` | `#FFFFFF` | Primary text, headings ("KABINET"), nav links |
| `--text-light` | `#D1D5DB` / `#E5E7EB` | Body text on dark backgrounds |
| `--text-muted` | `#9CA3AF` | Secondary text, sidebar labels, subtitles |
| `--text-dark` | `#1A1A2E` | Text on gold buttons, text on light backgrounds |
| **— Dashboard —** | | |
| `--dashboard-bg` | `#1E1E2E` | Dashboard content area background |
| `--dashboard-amber` | `#FF9800` / `#FFA726` | Active sidebar item, greeting card, FAB |
| `--sidebar-bg` | `#FFFFFF` | Sidebar background |
| **— Decorative —** | | |
| `--bokeh-gold` | `#F5C518` at 20-40% opacity | Glowing gold circles/orbs in background |
| `--bokeh-purple` | `#7B2FF7` at 15-30% opacity | Soft purple glows in background |

### 2.2 Typography

- **Font family:** Inter, Poppins, or Montserrat (sans-serif)
- **"Sentra Sinergi" wordmark:** "Sentra" in gold (`--gold-primary`), "Sinergi" in purple (`--purple-primary`), bold italic, large (~48-56px)
- **"KABINET":** White, uppercase, bold, letter-spaced (~24px)
- **Body:** 16px base, `--text-light` on dark backgrounds
- **Bold emphasis:** Key phrases in body text use white bold (`--text-white`)

### 2.3 Logos

- **HIMATEKKOM CE logo:** Hexagonal badge shape with circuit-board pattern, "CE" letters integrated. Rendered in blue/teal on dark navy hexagon, with a subtle glow. Displayed large (~200px) inside the hero card on the right side
- **Sentra Sinergi logo:** Small colorful interlocking/chain-link icon (~40px), used in header and CMOS sidebar

### 2.4 Recurring Visual Patterns

- **Gold CTA buttons:** Solid `--gold-primary` fill with dark text, rounded pill shape. Secondary variant: gold-outlined with gold text on dark bg
- **Gradient card border:** Cards with a gradient border transitioning from purple (top-left) → gold (bottom-right), using `border-image` or a wrapper with gradient background + inner padding
- **Bokeh/glow effects:** Soft blurred circles in gold and purple scattered across the dark background at low opacity, creating depth. Use `radial-gradient` or absolutely-positioned blurred divs
- **Puzzle piece watermarks:** Dark gray puzzle-piece shapes at ~10-15% opacity, positioned in corners/edges of sections
- **Gear watermarks:** Faint gear/cog outlines at ~10% opacity in the background
- **Dark overlay on hero:** No photo — pure dark gradient with decorative bokeh elements

---

## 3. Public Landing Page

### 3.1 Navigation Bar

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│  [🔗Logo] HIMATEKKOM ITS 2026      Informasi  Profil Organisasi                │
│           Kabinet Sentra Sinergi    Program Kerja  CMOS       [→ Login]         │
└──────────────────────────────────────────────────────────────────────────────────┘
```

- **Background:** `--bg-surface` (#1A1A2E), solid dark
- **Left:** Sentra Sinergi chain-link logo + "HIMATEKKOM ITS 2026" in white bold + "Kabinet Sentra Sinergi" as subtitle in muted text below
- **Center-right:** Navigation links in white — "Informasi", "Profil Organisasi", "Program Kerja", "CMOS"
- **Far right:** "Login" — gold outlined pill button with arrow/login icon (→)
- **Mobile:** Collapses to hamburger (☰)

### 3.2 Hero Section

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Background: --bg-dark with purple/gold bokeh effects                  │
│                                                                         │
│  ┌─── LEFT COLUMN ───────────────┐  ┌─── RIGHT COLUMN ──────────────┐ │
│  │                               │  │                                │ │
│  │  ┌──────────────────────────┐ │  │  ┌────────────────────────┐   │ │
│  │  │ ✅ [Verification Banner] │ │  │  │ ╭──────────────────╮   │   │ │
│  │  └──────────────────────────┘ │  │  │ │                  │   │   │ │
│  │                               │  │  │ │   [CE LOGO]      │   │   │ │
│  │  K A B I N E T                │  │  │ │   (hexagonal,    │   │   │ │
│  │  [Sentra] [Sinergi]          │  │  │ │    ~200px)       │   │   │ │
│  │   gold     purple             │  │  │ │                  │   │   │ │
│  │                               │  │  │ │  HIMATEKKOM ITS  │   │   │ │
│  │  [Body text — to be filled]   │  │  │ ╰──────────────────╯   │   │ │
│  │                               │  │  │                        │   │ │
│  │  [Gold CTA 1] [Gold CTA 2]   │  │  │  HIMATEKKOM ITS 2026   │   │ │
│  │   filled       outlined       │  │  │  [Tagline — to fill]   │   │ │
│  │                               │  │  └────────────────────────┘   │ │
│  └───────────────────────────────┘  └────────────────────────────────┘ │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

**Left column (~55% width):**
- **Verification banner:** Dark rounded pill with green checkmark (✅) + gold text — _text to be filled_
- **"KABINET":** White, uppercase, bold, letter-spaced
- **"Sentra Sinergi":** Large bold italic — "Sentra" in `--gold-primary`, "Sinergi" in `--purple-primary`
- **Body text:** `--text-light`, left-aligned, max-width ~600px. Key phrases bolded in white — _to be filled_
- **Two CTA buttons side by side:**
  - Primary: gold filled (`--gold-primary` bg, `--text-dark` text), rounded pill, icon + label
  - Secondary: gold outlined (`--gold-dark` border, `--gold-primary` text), rounded pill, icon + label

**Right column (~45% width):**
- **Logo card:** Rounded rectangle with a **gradient border** (purple → gold, clockwise). Inside:
  - Top area: white/light background zone containing the large hexagonal CE logo (~200px) with "HIMATEKKOM ITS" text beneath it
  - Bottom area: gradient fill (purple → gold), containing "HIMATEKKOM ITS 2026" in white bold and a tagline in lighter text — _to be filled_
- Card has subtle glow/shadow effect

**Background decorations:**
- `--bg-dark` base color
- Multiple **bokeh circles:** soft blurred gold and purple orbs at various sizes (30-150px), scattered across the section at 15-40% opacity
- **Puzzle piece** watermark in dark gray, positioned in left area at ~10% opacity
- **Gear/cog** watermark, faint, in lower or side areas
- **Gradient washes:** Subtle purple and gold gradient patches blending into the dark background

### 3.3 Visi Section

```
┌─────────────────────────────────────────────────────────────────┐
│  Background: [to be decided — dark or light variant]           │
│                                                                 │
│              ┌──────────┐                                       │
│              │  [Title] │  ← Gold or purple badge              │
│              └──────────┘                                       │
│         ┌──────────────────────────────────────┐                │
│         │                                      │                │
│         │  " [Vision statement — to be filled] " │              │
│         │                                      │                │
│         └──────────────────────────────────────┘                │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

- Badge title in gold or purple
- White or dark card with quote marks
- _All text to be filled_

### 3.4 Misi Section

```
┌─────────────────────────────────────────────────────────────────┐
│  Background: --bg-dark                                         │
│                                                                 │
│                 ┌──────────┐                                    │
│                 │  [Title] │  ← Badge                          │
│                 └──────────┘                                    │
│                                                                 │
│     ┌─1─────────────┐              ┌─2─────────────┐           │
│     │ [placeholder] │              │ [placeholder] │           │
│     └───────────────┘              └───────────────┘           │
│                                                                 │
│                  ┌─────────────┐                                │
│                  │  ⚙ GEAR     │  ← Central decorative gear    │
│                  └─────────────┘                                │
│                                                                 │
│     ┌─3─────────────┐              ┌─4─────────────┐           │
│     │ [placeholder] │              │ [placeholder] │           │
│     └───────────────┘              └───────────────┘           │
│                                                                 │
│                 ┌─5─────────────┐                               │
│                 │ [placeholder] │                               │
│                 └───────────────┘                               │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

- Dark background with bokeh effects
- 5 cards arranged radially around a central gear icon
- **Each card:** Purple or gold-accented background, white text, rounded, with numbered badge
- **Central gear:** Large compound gear, gold/purple gradient fill
- Mobile: cards stack vertically
- _All text to be filled_

### 3.5 Program Kerja Section

```
┌─────────────────────────────────────────────────────────────────┐
│  Background: --bg-surface or slightly lighter dark variant     │
│                                                                 │
│    ┌─────────────────────┐         ┌──────────────────────┐    │
│    │ ┌──────────────┐    │         │  ┌────────────────┐  │    │
│    │ │ [Title Badge] │   │         │  │  [Photo]       │  │    │
│    │ └──────────────┘    │         │  │  (tilted ~-3°) │  │    │
│    │                     │         │  └────────────────┘  │    │
│    │ [Description —      │         │   [Caption]       ▐  │    │
│    │  to be filled]      │         │                   ▐  │    │
│    │                     │         └──────────────────────┘    │
│    │ [→ CTA Button]      │         ← Card with gradient       │
│    └─────────────────────┘           purple→gold border        │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

- Two-column desktop, stacked mobile
- Left: badge title, white body text, gold CTA pill
- Right: card with gradient border (purple→gold), tilted photo, caption
- _All text and images to be filled_

---

## 4. Authenticated Dashboard (CMOS)

### 4.1 Sidebar

```
┌──────────────────────┐
│  [🔗Logo]  CMOS      │
│                      │
│  [SECTION LABEL]     │  ← Muted gray, uppercase, small
│  ┌──────────────┐    │
│  │ 🏠 [Item]    │    │  ← Active: --dashboard-amber bg, white text
│  └──────────────┘    │
│  📢 [Item]           │  ← Inactive: dark gray text
│  📋 [Item]           │
│                      │
│  [SECTION LABEL]     │
│  📁 [Item]           │
│  ☑️ [Item]           │
│                      │
│  [SECTION LABEL]     │
│  📅 [Item]           │
│  📝 [Item]           │
│                      │
│  [SECTION LABEL]     │
│  ...                 │
└──────────────────────┘
```

- White background, chain-link logo + "CMOS" title
- Section labels: uppercase, `--text-muted`, letter-spaced, ~12px
- Active: amber bg, white text, rounded ~8px
- Mobile: drawer overlay; desktop ≥1024px: fixed sidebar
- _All menu items and section labels to be filled_

### 4.2 Top Navigation Bar

```
┌────────────────────────────────────────┐
│  ☰  [Page Title]     🌙  🔔  [Avatar] │
└────────────────────────────────────────┘
```

- Hamburger, page title, dark mode toggle, notification bell, user avatar (initials in purple circle)

### 4.3 Dashboard Content

**Greeting Card:**
```
┌─────────────────────────────────────┐
│              [DD]                   │  ← Large date (48px+)
│           [Day Name]               │  ← Gold/amber text
│          [Month Year]              │
│                                     │
│  [Greeting], [Full Name]!          │  ← Bold white
│                        🕐 [HH:MM] WIB │
└─────────────────────────────────────┘
```
- Full-width, rounded, amber gradient (`--dashboard-amber`)
- Dynamic greeting: Pagi (04-10), Siang (11-14), Sore (15-17), Malam (18-03)

**Quick Action Buttons:**
- 3 stacked full-width outlined cards, `--gold-dark` border, centered icon+text
- _Labels to be filled_

**Stats Cards:**
- Grid of dark cards — icon, large count number, label
- _Stat types to be filled_

**FAB:**
- Circular, `--dashboard-amber` bg, white chat icon, bottom-right fixed

---

## 5. Responsive Behavior

- Hero: two-column desktop (text left, logo card right) → stacked mobile (card above or below text)
- Misi cards: 2-col desktop → stack mobile
- Program Kerja: 2-col desktop → stack mobile
- Sidebar: drawer mobile, fixed ≥1024px
- Nav: horizontal desktop → hamburger mobile

## 6. Component Inventory

| Component | Props |
|-----------|-------|
| `NavBar` | `links[]`, `logoSrc`, `orgName`, `subtitle`, `onLogin` |
| `HeroSection` | `verificationText`, `cabinetLine1`, `cabinetLine2`, `bodyText`, `ctaButtons[]`, `logoCard` |
| `LogoCard` | `logoSrc`, `orgTitle`, `tagline` — gradient-bordered card |
| `SectionBadge` | `text`, `color: 'gold' \| 'purple'` |
| `VisiCard` | `quoteText` |
| `MisiLayout` | `missions[]`, `gearIconSrc` |
| `MisiCard` | `number`, `text` |
| `ProgramKerjaSection` | `description`, `ctaText`, `photoCards[]` |
| `PhotoCard` | `imageSrc`, `caption`, `tiltDeg` |
| `Sidebar` | `menuSections[]`, `activeItem` |
| `TopNav` | `pageTitle`, `user`, `onToggleDarkMode` |
| `GreetingCard` | `userName`, `currentDate`, `currentTime` |
| `QuickActionButton` | `icon`, `label`, `href` |
| `StatCard` | `icon`, `count`, `label` |
| `FAB` | `icon`, `onClick` |
| `BokehBackground` | `orbs[]` — renders decorative blurred circles |

---

## 7. Color Flow Through Page

```
Section         Background              Primary Accent     Secondary Accent
──────────────  ──────────────────────  ────────────────   ────────────────
Nav             --bg-surface (dark)     White (links)      Gold (Login btn)
Hero            --bg-dark + bokeh       Gold (CTAs, text)  Purple (text, border)
Visi            Dark or light variant   Gold/Purple badge  White card
Misi            --bg-dark               Purple (cards)     Gold (gear, numbers)
Program Kerja   --bg-surface            Gold (CTA)         Purple→Gold (border)
Dashboard       --dashboard-bg          Amber (greeting)   Gold (borders)
```

---

## 8. Build Instructions for GPT-5.4

1. **Set up theme** with the exact color tokens from §2.1 — the palette is dark/black-purple, NOT blue
2. **Build the hero first** — two-column layout with text left, gradient-bordered logo card right, bokeh background effects
3. **Implement bokeh/glow effects** — use absolutely-positioned blurred divs or radial-gradients for the gold and purple orbs scattered across dark sections
4. **"Sentra Sinergi" wordmark** — split-color rendering: "Sentra" in gold, "Sinergi" in purple, bold italic
5. **Gradient card borders** — use a wrapper div with gradient background (purple→gold) and an inner div with dark padding to simulate a gradient border
6. **Logo card on right** — contains the hexagonal CE logo at top (white/light area) and title+tagline at bottom (gradient purple→gold area)
7. **Puzzle piece + gear watermarks** — faint SVG shapes at ~10% opacity, positioned in background corners
8. **Gold buttons** — solid gold for primary CTA, outlined gold for secondary CTA, both pill-shaped
9. **Dashboard sidebar** — white bg, amber active states, responsive drawer
10. **All text content is placeholder** — wrap in clearly labeled props/constants for easy replacement
11. **Gold is dominant, purple is supporting** — gold for interactive/actionable elements, purple for decorative/accent elements

---

*Specification v4 — dark + gold + purple theme from desktop screenshot. Content placeholders only.*
