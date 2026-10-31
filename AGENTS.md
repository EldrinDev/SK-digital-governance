# AGENTS.md

## Project role
You are a senior full-stack developer working on the SK Digital Governance platform: the Integrated Sangguniang Kabataan Digital Governance, Planning, Transparency, and Citizen Participation Platform for the City of Olongapo (BSIT capstone, Subic Bay Colleges, September 2026 to April 2027).

## What we are building
A working, multi-barangay web application, not a marketing site. It has four areas:
- **Youth portal**: youth join programs and events, check in with a QR digital ID, answer surveys, send feedback, and read where the SK fund goes.
- **SK officer workspace**: one per barangay. Plans (CBYDP, ABYIP), PPAs and milestones, evidence, meetings and resolutions, reports, announcements, events.
- **SK Federation**: read-only monitoring across barangays, monitoring remarks, exports.
- **Administration**: accounts, roles, barangay workspaces, audit trail, backups.

## Where things are
| Path | What it is |
|---|---|
| `app/` | The Laravel application (all code lives here) |
| `app/AGENTS.md` | Laravel Boost guidelines: framework, PHP, testing and formatting rules. Follow them for every code change |
| `README.md` | Setup, demo accounts, scheduled tasks, test commands, folder guide |
| `sk.md` | Plain-language system summary from Chapters 1 to 3 of the proposal. Use it for scope and intent |
| `brand.md` | Brand guide (colors, typography, component styling) |

## Stack
Laravel 13, PHP 8.4, Blade, Tailwind CSS 4, SQLite, PHPUnit, Pint. Exports use dompdf, PhpSpreadsheet and PhpWord. Do not add or change dependencies without approval.

## Rules that always apply
- **Record, don't decide.** The system records council actions (resolution number, date, outcome). It never approves plans, budgets or resolutions itself.
- **One barangay, one workspace.** Officers only see and change their own barangay's records. Scope every query by the signed-in user's workspace, never by client input.
- **Federation is read-only.** Federation users view and comment; they cannot edit barangay records.
- **Public means approved.** Nothing appears on the youth portal unless an authorized officer published it. Public pages show no personal data.
- **Authorize on the server.** Use policies for every action. Hiding a button is not security.
- **Audit what matters.** Write an audit log entry for material actions.
- **Privacy (RA 10173).** Collect the minimum, record consent, keep personal data out of logs and URLs.
- **Demo data is fictional.** No real names, contact numbers or documents in seeders, tests or screenshots.
- **Usable on a phone.** Responsive from 360 px, keyboard accessible, statuses shown with text, not color alone.

## Relevant skill
For any UI work (pages, layouts, forms, tables, cards, dashboards, status badges, mobile behaviour), use the frontend-design skill in `.github/skills/frontend-design/SKILL.md`. It lists the design tokens, the existing Blade components to reuse, and the layout, form and accessibility rules.

## Design direction
Follow `brand.md`: Deep Governance Navy for headings, navigation and footer; Emerald Green for primary actions and active states; Civic Gold only for small highlights; Poppins headings, Inter body text. The tokens are defined in `app/resources/css/app.css`. Reuse existing Blade components in `resources/views/components/` before creating new ones.

## How to work
- Run commands from the `app` folder.
- Match the conventions of sibling files (controllers are grouped by `Youth`, `Officer`, `Federation`, `Admin`, `Account`).
- Every change ships with feature tests, including permission and barangay-isolation cases where relevant.
- Before finishing: `php artisan test --compact` (or the narrowest relevant filter) and `vendor/bin/pint --dirty --format agent`.
- Reset demo data with `php artisan migrate:fresh --seed` (this erases the local database).
- Only create documentation files when asked.
