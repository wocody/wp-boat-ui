# Changelog

All notable changes follow [Semantic Versioning](https://semver.org).

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
