# Arquitectura del Sistema — Directorio de Personas

Audience: technical hiring manager / senior developer reviewer.

---

## Stack diagram

```
┌─────────────────────────────────────────────────────────┐
│                    CLOUDFLARE EDGE                       │
│  WAF rules · DDoS · SSL/TLS · Cache (static assets)     │
└───────────────────┬────────────────────┬────────────────┘
                    │                    │
          ┌─────────▼──────┐   ┌────────▼────────────────┐
          │ Cloudflare      │   │ Cloudflare Pages         │
          │ Access          │   │ (Astro static build)     │
          │ (WP admin gate) │   │ humanidades.example.edu  │
          └─────────┬──────┘   └────────┬────────────────┘
                    │                    │ Build trigger: git push
          ┌─────────▼──────┐            │ (webhook → CF Pages)
          │ WordPress CMS   │            │
          │ (InfinityFree)  │◄───────────┘
          │ REST API only   │  Astro fetches at build time
          │ /wp-json/wp/v2/ │  → zero runtime WP traffic
          └─────────────────┘

  Editor ──► WP Admin ──► ACF fields ──► REST API ──► next build
```

**Key architectural decision:** Astro fetches ALL WordPress data at build time.
The deployed site is 100% static HTML — WordPress is never hit by end users.
This means a compromised or slow WordPress instance has zero impact on site availability.

---

## Directory layout

| Path | Role |
|------|------|
| `web/src/lib/wp.ts` | WP REST API client, TypeScript types |
| `web/src/pages/index.astro` | Directory listing, static generation |
| `web/src/pages/personas/[slug].astro` | Profile pages, `getStaticPaths()` |
| `web/src/layouts/BaseLayout.astro` | `<head>`, SEO, skip-nav |
| `web/src/components/` | PersonCard, PersonProfile, Nav |
| `wordpress/plugin/` | Custom post type + ACF field registration |
| `cms-export/` | ACF JSON export (version-controlled) |
| `docs/` | All documentation |

---

## Security decisions

| Decision | Why |
|----------|-----|
| XML-RPC disabled | Primary brute-force and DDoS amplification vector; headless setup has no use for it |
| `DISALLOW_FILE_EDIT = true` | Prevents code injection via Theme/Plugin Editor if admin account is compromised |
| `WP_DEBUG = false` in production | Suppresses stack traces and file paths that aid attackers |
| WP version string removed | Reduces automated fingerprinting; version info is public on WordPress.org but there's no reason to broadcast it |
| WP admin behind Cloudflare Access | Zero-Trust gate; unauthenticated requests to `/wp-admin` never reach the origin |
| REST API write endpoints blocked at WAF | Site is read-only headless; WAF rule blocks POST/PUT/DELETE to `/wp-json` |
| File permissions: wp-config.php 440 | Prevents web-readable access if webroot traversal occurs |
| `.htaccess` blocks `wp-includes` direct access | Prevents execution of PHP files in non-plugin directories |
| `SECURE_AUTH_KEY` + salts via env vars | Salts rotated without touching code; committed placeholder reminds team to set real values |
| No page builders installed | Eliminates the largest category of WP plugin CVEs |
| Plugin policy: 100k+ installs, actively maintained | Narrows to audited, widely-tested plugins only |

---

## Performance targets

| Metric | Target | How achieved |
|--------|--------|--------------|
| LCP | < 2.5 s | Static HTML, images via `<Image>` with `loading="lazy"` and WebP |
| CLS | < 0.1 | Explicit width/height on every image, no layout-shifting fonts |
| FID/INP | < 200 ms | Near-zero JavaScript; Astro ships 0 JS by default |
| Lighthouse Performance | ≥ 90 | Static build, Cloudflare CDN, minimal CSS |
| Lighthouse Accessibility | ≥ 90 | Semantic HTML, skip-nav, ARIA labels, color contrast |
| Lighthouse SEO | 100 | Canonical tags, Schema.org Person, meta descriptions |

---

## Build & deploy pipeline

```
Developer → git push → GitHub → Cloudflare Pages build hook
  → npm run build (Astro)
    → getStaticPaths() fetches all /personas from WP REST API
    → generates static HTML per person
  → deploys dist/ to Cloudflare CDN
  → invalidates edge cache
```

Build time estimate: ~30 s for 200 profiles (WP REST API calls are batched, 100 per page).

---

## Handoff notes

1. **WordPress credentials** are stored in the team password manager (not in this repo).
2. **ACF field group** JSON is in `cms-export/acf-person-fields.json`. Import via ACF > Tools > Import after activating the plugin.
3. **Rebuild trigger**: any Cloudflare Pages deployment hook URL is in the team password manager under `CF_BUILD_HOOK_URL`. A new editor record in WP requires a manual rebuild trigger until a publish webhook is configured.
4. **Staging**: duplicate CF Pages project connected to `staging` branch. WP staging = subdomain on same host. See README environments table.
5. **Backup**: UpdraftPlus → Google Drive, weekly schedule, 4 copies retained. Verify in WP Admin > UpdraftPlus > Existing Backups.
