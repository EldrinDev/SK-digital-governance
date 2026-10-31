---
name: frontend-design
description: 'Design and build UI for the SK Digital Governance Laravel app with Blade and Tailwind CSS 4. Use when creating or changing pages, layouts, forms, tables, cards, dashboards, status badges, or mobile behaviour in the youth portal, officer workspace, federation or admin areas.'
---

# Frontend Design

## When to use
- Building a new page or screen in `app/resources/views`
- Changing layouts, forms, tables, cards, charts, badges or empty states
- Making a page work better on phones or with a keyboard
- Restyling anything so it matches the brand

## Where the UI lives
| Path | What it is |
|---|---|
| `app/resources/css/app.css` | Tailwind 4 setup and the design tokens (`@theme`) |
| `app/resources/js/app.js` | Small enhancements: sidebar drawer and collapse, dropdowns, share button |
| `app/resources/views/components/layouts/` | Page shells: `app` (staff workspaces), `public` (youth portal), `auth` (sign-in pages) |
| `app/resources/views/components/ui/` | Shared pieces: `badge`, `stat`, `bar-list`, `column-chart`, `plan-status`, `ppa-status`, `export-menu`, `form-page`, `guide-steps`, `toast`, `toaster` |
| `app/resources/views/components/form/` | `input`, `select`, `submit`, `alert` |
| `app/resources/views/components/` (others) | Domain pieces: `announcement/*`, `event/*`, `program/card`, `youth/id-card`, `barangay/facebook-link`, `notifications-menu` |

Reuse these components before writing new markup. If a pattern repeats on two or more pages, make it a component.

## Design tokens (defined in app.css, from brand.md)
Use the Tailwind names, never raw hex codes in views.

| Token | Use |
|---|---|
| `navy` (`#142B4A`), `navy-deep` | Headings, navigation, sidebar, footer, body text |
| `emerald` (`#16845B`), `emerald-dark` | Primary buttons, links, active menu items, selected states |
| `gold` (`#E5B64B`) | Small highlights and the focus outline only; never large text or every button |
| `mist` (`#F5F7FA`) | Page background, alternating sections |
| white | Cards, forms, panels |
| `slate-muted` (`#64748B`) | Supporting text |
| `line` (`#E2E8F0`) | Borders, dividers, input outlines |
| `success`, `warning`, `error`, `info` | Status only, kept separate from brand colors |

- Fonts: `font-display` (Poppins) for headings, `font-sans` (Inter) for body and UI.
- Radius: `rounded-card` (12px) for cards, `rounded-button` (8px) for buttons and fields.
- Shadows: soft and minimal. No glows.
- Focus: the global 3px gold `:focus-visible` outline. Do not remove it.

## Layout rules
- Staff pages use the `app` layout: side menu (a drawer on phones and tablets, collapsible on desktop), top bar with page title and subtitle, content area.
- Youth pages use the `public` layout and are designed phone-first.
- Show only the menu items and buttons the user may use, but remember the server (policies) still decides.
- Primary actions are emerald and prominent; on phones, page-level create buttons are full width.
- Long lists are paginated and support search and filters that update as the user types or picks a filter.
- Every list has an empty state with a clear next action ("No PPAs yet. Add the first PPA").

## Forms
- Label above the field, required marker, helper text where useful.
- Inline error text next to the field, not only a toast.
- Disable the submit button while saving.
- Destructive actions use a confirmation that names the target and a red button.
- Success feedback through the toaster.

## Accessibility and mobile (required)
- Works from 360 px wide; tables become cards or scroll with a sticky first column.
- Touch targets and fields at least 44 px high; form fields 16px text on phones.
- Semantic headings and landmarks, labelled controls, table headers with `scope`.
- Status is always text plus color (use `ui/badge`, `ui/ppa-status`, `ui/plan-status`), never color alone.
- Charts and progress bars show the number as text too.
- Respect `prefers-reduced-motion` (already handled globally in app.css).

## Content rules
- Plain, short English. Strings that a user sees should be clear and consistent across pages.
- Public pages never show personal data of youth; show "Posted by SK {barangay}", not a person's name.
- Demo content and screenshots use fictional data only.
- The system records council actions: buttons say "Record council approval", never "Approve plan".

## Before finishing
- Check the page at phone (360 px), tablet and desktop widths.
- Run `npm run build` (or keep `composer run dev` running) so Vite picks up new classes.
- Run the related feature tests and `vendor/bin/pint --dirty --format agent` if PHP changed.
