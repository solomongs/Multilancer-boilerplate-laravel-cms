# M2026 Laravel CMS Refactor Status

## Project links

- Live checklist tracker: https://308f7ac1d1470ca9d4.v2.appdeploy.ai/
- Repository: https://github.com/solomongs/Multilancer-boilerplate-laravel-cms
- Development branch: `feature/m2026-refactor`
- Protected admin path: `/soloadmin`

## Architecture decision

The project uses the Liberu Laravel boilerplate as the foundation and remains a modular monolith.
The existing M2026 frontend is the visual source of truth. Public pages remain accessible without authentication, while CMS administration is restricted to Filament at `/soloadmin`.

The frontend will be converted into reusable Blade layouts, components and structured sections. It must not be embedded through an iframe or stored as one unrestricted HTML field.

## First implementation scope

- [x] Fork/copy the boilerplate into the Multilancer repository
- [x] Create `feature/m2026-refactor`
- [x] Publish a live checklist and GitHub activity tracker
- [x] Change the Filament admin path to `/soloadmin`
- [x] Add an automated test for the admin login path
- [ ] Complete the file-by-file M2026 audit
- [ ] Create the M2026 theme scaffold
- [ ] Create CMS Page and PageSection models
- [ ] Add migrations and the initial Filament Page resource
- [ ] Add public page routing and homepage rendering
- [ ] Add baseline CMS tests

## Hard constraints

- Preserve the existing M2026 layouts, CSS, navigation, images, responsive behaviour and interactions.
- Do not require login for the public website.
- Do not expose `/admin` as an alternate admin route.
- Do not require Redis, Horizon, Reverb, Octane, SSR or a Node server in production.
- Build for standard PHP shared hosting and VPS deployment.
- Do not use `php_flag` or `php_value` directives in `.htaccess`.
- Keep optional features modular so they can be enabled or disabled.
- Defer GrapesJS, advanced CRM, SMS, AI, multisite and e-commerce until the CMS foundation is stable.

## Known static-site scope

The current known M2026 package contains approximately 20 HTML pages, shared `styles.css`, `app.js`, `ai-assistant.js`, and image assets. The known route groups include home, about, services, courses, hosting, blog, Saturday class, AI consultation and contact pages. Exact filenames and counts must be validated against the uploaded package during the audit phase.

## Pull request target

The implementation branch will be reviewed through a pull request into `main` after the first controlled milestone passes tests and visual review.
