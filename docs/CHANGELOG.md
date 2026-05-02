# CHANGELOG

All notable changes to this project are documented here.  
Format: [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) · Versioning: [SemVer](https://semver.org)

---

## [Unreleased]

---

## [0.1.0] — 2026-05-02

### Added
- Repo scaffold: directory structure, `.gitignore`, `.editorconfig`, `.nvmrc` (Node 20)
- `README.md` with environment table, repo map, and quick-start instructions
- Astro project initialized in `web/` with TypeScript strict mode and Tailwind CSS
- Base layout (`BaseLayout.astro`) with SEO head, skip-nav, and site nav
- Directory listing page shell (`src/pages/index.astro`)
- Profile page shell (`src/pages/personas/[slug].astro`) with Schema.org `Person` markup placeholder
- WP REST API data-fetching module (`src/lib/wp.ts`) with TypeScript types for `Person` CPT
- WordPress custom post type plugin (`wordpress/plugin/humanities-people-cpt.php`) covering all Person fields
- `wp-config.php` security hardening snippet (`wordpress/wp-config-security.php`)
- `.htaccess` baseline security rules (`wordpress/htaccess-security.txt`)
- `docs/ARCHITECTURE.md` — stack diagram, security decisions, performance targets
- `docs/EDITOR-GUIDE.md` — Spanish, non-tech audience
- `docs/MAINTENANCE-GUIDE.md` — quarterly checklist, what not to touch
- `docs/cloudflare-rules.md` — WAF rules catalogue
- `cms-export/` directory prepared for ACF field group JSON

### Security decisions
- XML-RPC endpoint disabled (no legitimate use case in headless setup; common brute-force vector)
- WP file editing disabled via `DISALLOW_FILE_EDIT` (prevents code injection via compromised admin)
- Debug output disabled in production (information leakage prevention)
- WP version string hidden (reduces attack surface fingerprinting)
