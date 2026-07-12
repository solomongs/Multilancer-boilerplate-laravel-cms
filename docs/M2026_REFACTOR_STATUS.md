# M2026 Laravel CMS Refactor Status

## Project links

- Live checklist tracker: https://308f7ac1d1470ca9d4.v2.appdeploy.ai/
- Repository: https://github.com/solomongs/Multilancer-boilerplate-laravel-cms
- Draft pull request: https://github.com/solomongs/Multilancer-boilerplate-laravel-cms/pull/1
- Development branch: `feature/m2026-refactor`
- Protected admin path: `/soloadmin`

## Architecture decision

The project uses the Liberu Laravel boilerplate as the foundation and remains a modular monolith.
The existing M2026 frontend is the visual source of truth. Public pages remain accessible without authentication, while CMS administration is restricted to Filament at `/soloadmin`.

The frontend is being converted into reusable Blade layouts, components and structured sections. It must not be embedded through an iframe or stored as one unrestricted HTML field.

## Implemented scope

### Foundation and theme

- [x] Fork/copy the boilerplate into the Multilancer repository
- [x] Create `feature/m2026-refactor`
- [x] Publish a live checklist and GitHub activity tracker
- [x] Change the Filament admin path to `/soloadmin`
- [x] Add an automated test for the admin login path
- [x] Create and register the M2026 theme scaffold
- [x] Add responsive layout, navigation, footer and reusable section styling
- [x] Add a safe Python ZIP extractor and static-site inventory generator
- [x] Add Python tests for extraction, reporting and ZIP traversal rejection
- [x] Add the Python extraction test to pull-request CI
- [ ] Run the extractor against the original `m2026.zip`
- [ ] Complete the generated file-by-file M2026 audit
- [ ] Migrate the audited M2026 HTML, CSS, JavaScript, images and media

### CMS core

- [x] Create CMS Page, PageSection, PageRevision and Redirect models
- [x] Add CMS migrations
- [x] Add Filament Page, Page Section, Revision and Redirect resources
- [x] Add public page routing and homepage rendering
- [x] Add authenticated unpublished-page preview
- [x] Add structured hero, rich-text, image-text, contact-form and call-to-action renderers
- [x] Add publishing, homepage, preview, redirect, sitemap, robots and revision tests
- [x] Add XML sitemap generation
- [x] Add robots crawler policy
- [x] Add legacy redirects with analytics and status-code controls
- [x] Add revision restoration with current-state preservation

### Leads and enquiries

- [x] Create a separate Leads module
- [x] Add lead lifecycle statuses, assignment and internal notes
- [x] Add a public contact submission endpoint
- [x] Add CSRF protection through the web middleware stack
- [x] Add rate limiting and a honeypot field
- [x] Store a one-way visitor IP hash instead of a raw IP address
- [x] Add HTML and JSON submission responses
- [x] Add a reusable accessible M2026 contact-form section
- [x] Add `/soloadmin` lead management
- [x] Add configurable multi-recipient new-lead email notifications
- [x] Keep email notification delivery synchronous and failure-tolerant for shared hosting
- [x] Add lead submission, privacy, spam, rendering, workflow and notification tests

### Verification

- [x] Add pull-request CI configuration for Python extraction tests, migrations, Laravel tests and frontend build
- [ ] Receive a successful GitHub Actions run
- [ ] Complete visual comparison against the original M2026 package

## Verification state

Implementation files and tests are committed. GitHub currently reports no workflow run or commit checks for this fork, so the Python tests, PHP tests and frontend build must not be described as passing until Actions is enabled and completes successfully.

The live AppDeploy delivery tracker has passed its own frontend, backend, network and end-to-end QA checks. This verifies the tracker application only; it does not replace repository CI.

## Current CMS capabilities

- Draft, review, scheduled, published and archived page states
- Single-homepage enforcement
- Scheduled publication visibility
- Authenticated draft preview with `noindex,nofollow`
- Soft-deleted pages and leads
- SEO title, description, canonical URL, robots and social metadata
- Ordered, enable/disable structured page sections
- JSON content and display settings for page sections
- Page and section revision snapshots
- Transactional page and section restoration
- Published-page XML sitemap
- Public robots policy
- One-segment and nested legacy redirects
- Redirect enable/disable control, status codes, hit counts and last-hit timestamps
- Secure public lead capture with email-or-phone validation
- Rate limiting, honeypot rejection and privacy-safe request metadata
- Configurable comma-separated lead notification recipients through `LEADS_NOTIFICATION_EMAILS`
- Shared-hosting-compatible email delivery that does not require queue workers
- Theme-based homepage and default-page rendering
- Public fallback to the original welcome page when no CMS homepage exists

## Python extraction capability

The repository now includes `scripts/extract_m2026.py`. It safely extracts a supplied ZIP file and generates:

- the archive SHA-256 checksum;
- a complete file and extension inventory;
- detected HTML page titles and paths;
- HTML and CSS local/external references;
- missing page and asset references;
- duplicate file groups using SHA-256 hashes;
- a human-readable Markdown audit;
- a machine-readable JSON inventory.

Run it from the repository root with:

```bash
python scripts/extract_m2026.py /mnt/data/m2026.zip \
  --output storage/app/m2026-source \
  --report docs/m2026-audit \
  --force
```

## Hard constraints

- Preserve the existing M2026 layouts, CSS, navigation, images, responsive behaviour and interactions.
- Do not require login for the public website.
- Do not expose `/admin` as an alternate admin route.
- Do not require Redis, Horizon, Reverb, Octane, SSR or a Node server in production.
- Build for standard PHP shared hosting and VPS deployment.
- Do not use `php_flag` or `php_value` directives in `.htaccess`.
- Keep optional features modular so they can be enabled or disabled.
- Defer GrapesJS, advanced CRM, SMS, AI, multisite and e-commerce until the CMS foundation is stable.

## Static-site audit status

The original `m2026.zip` binary is not present in the current conversation attachment index or in the connected GitHub repositories. The extraction utility is committed and ready, but no exact page count, file count, asset inventory or static migration should be treated as complete until the archive is uploaded or made accessible.

## Pull request target

The implementation branch will remain in draft until the Python audit runs against the original archive, the Laravel test suite passes, frontend assets compile successfully, the original M2026 package is migrated, and the resulting pages pass visual review.
