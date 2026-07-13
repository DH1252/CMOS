---
target: department overview
total_score: 29
p0_count: 0
p1_count: 2
timestamp: 2026-07-08T17-07-14Z
slug: svelte-public-publicdepartmentoverviewpage-svelte
---
# Critique: Department Overview Page

## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 2 | Hover states exist, but missing active/focus states |
| 2 | Match System / Real World | 3 | Department names are accurate |
| 3 | User Control and Freedom | 3 | Clear navigation via Navbar/Footer |
| 4 | Consistency and Standards | 2 | Severe typographic inconsistencies (mixing 3 fonts) |
| 5 | Error Prevention | 4 | No complex inputs to cause errors |
| 6 | Recognition Rather Than Recall | 4 | All departments are visible |
| 7 | Flexibility and Efficiency | 2 | No filtering or grouping for a long list of 10 items |
| 8 | Aesthetic and Minimalist Design | 2 | Cluttered with decorative AI slop markers |
| 9 | Error Recovery | 4 | N/A |
| 10 | Help and Documentation | 3 | N/A |
| **Total** | | **29/40** | **Good** |

## Anti-Patterns Verdict

**LLM assessment**: High AI Slop Detected. The page exhibits multiple classic AI tells: tiny uppercase tracked eyebrows, an identical repeating card grid, generic atmospheric clutter (botanical overlays, floating stars with mix-blend-screen) instead of deliberate brand assets, and a "ghost-card" style hover side-stripe border.

**Deterministic scan**: The CLI scan flagged `hero-eyebrow-chip` (a tracked-caps eyebrow above the h1). A manual source code check revealed a false negative: a hover accent line using a `<div class="w-[3px]">` instead of `border-left`, functioning as the banned side-stripe border anti-pattern.

**Visual overlays**: No reliable user-visible overlay is available due to the lack of browser automation in this environment.

## Overall Impression
The routing is solid, but the design feels like a generic template for a mystical agency, drastically mismatching the identity of a Computer Engineering Student Organization (Tekkom). The biggest opportunity is ditching the identical 10-card monotony and the botanical layers for a more structured, technical, and grounded hierarchy.

## What's Working
1. Direct, clear routing to individual department pages.
2. Clean separation of layout components (Navbar, Main content, Footer).
3. Text contrast against the dark background is generally legible.

## Priority Issues
- **[P1] Remove the Font Soup**: The page mixes `Plus_Jakarta_Sans`, `Josefin_Sans`, and `Playfair_Display`. This breaks aesthetic cohesion. Choose a primary brand font and stick to it. **Suggested command**: `$impeccable typeset`
- **[P1] Break the Card Monotony**: Presenting 10 identical cards creates a wall of choices with poor chunking. Group them into logical clusters (e.g., Core, Internal, External, Media). **Suggested command**: `$impeccable layout`
- **[P2] Purge AI Boilerplate**: Remove the tracked uppercase eyebrow, the repetitive side-stripe hover accent line, and the generic arrow indicator. Replace the generic botanical background with real HIMATEKKOM context. **Suggested command**: `$impeccable distill`
- **[P2] Implement Keyboard Accessibility**: The cards lack explicit `:focus-visible` rings for keyboard navigation. **Suggested command**: `$impeccable audit`

## Persona Red Flags

**Jordan (First-Timer)**: Will face a wall of 10 identical choices with no context on which departments handle administrative tasks versus student-facing services. Will likely experience choice paralysis.
**Sam (Accessibility-Dependent User)**: No explicit `:focus-visible` rings for keyboard navigation on the department cards. Cannot see hover states to understand interactivity.

## Minor Observations
- The `animate-slow-pan` class is applied to the botanical background but isn't a standard Tailwind utility, so it might be dead code.
- Yellow text `#ffd344` on a `bg-white/10` hover background might suffer from edge-case contrast issues.

## Questions to Consider
- Does an organization with 10 departments really need to present them as equal, undifferentiated tiles, or is there a natural hierarchy we are ignoring?
- If we strip away the decorative background layers and the hover animations, what remains of the actual HIMATEKKOM brand identity?
