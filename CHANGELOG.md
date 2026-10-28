# Changelog

All notable changes follow [Semantic Versioning](https://semver.org).

## 2.0.0 — 2026-10-28

New, original Wocody visual identity.

- Brand colour `#FC7900` with an accessible scale (`primary-contrast` dark text on filled orange, `primary-700` for orange text) and warm `ink` neutrals replacing slate.
- System font stack at 14px base, new type scale, 10px control and 16px card radii, softer borders and shadows.
- Phosphor Icons (regular weight, 1,512 icons) replace Heroicons.
- Layout: sticky app bar with brand mark, pill quick search and `actions` slot; sidebar navigation with group headings, icons or dots and an orange active indicator; aside column in the grid and sticky; mobile drawer.
- Components: header icon tile for `paper()`, `icon` and `footer` for `aside_box()` plus a dark `highlight` variant, `warning` and `dot` badges, toggles without inner icons, checkbox/radio/tile redesign, focus rings, dark toasts at the bottom right, larger modal with footer bar, drop zone with icon tile, table with uppercase headers.
- Settings sections switch to two columns from the card width (container query).

Breaking: see "Upgrading from 1.x" in the README.

## 1.0.1 — 2026-10-28

- Fix: focused links (sidebar items and link buttons) lost their rounded corners. Base link styles now use `:where(a)` so component styles always win.

## 1.0.0 — 2026-10-27

First release.

- Admin screen layout: brand wordmark or logo, sidebar navigation with collapsible groups and "Show more", quick search (⌘K / Ctrl+K), mobile navigation dialog, main column and fixed right-hand column.
- Hash router for single-screen plugins (`data-bui-view`).
- Components: paper with sticky (optionally dirty-only) footer, settings section, card, aside box, check list, button variants and sizes, link, badge, code, alert, banner, progress, tooltip, skeleton, text/textarea/select fields with validation, checkbox and radio groups (including tile radios), switch toggles on native checkboxes, file drop zone, disclosure, feature groups, responsive table, native dialog modal and toasts.
- Tailwind CSS v4.3.3 utilities with the `bui:` prefix, scoped preflight, Tailwind v3 hex palette and a re-themable primary scale.
- Version election between bundled copies; showcase screen behind `WOCODY_BOAT_UI_DEMO`.
- English and pt_BR strings.
