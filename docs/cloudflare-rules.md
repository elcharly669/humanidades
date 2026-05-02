# Cloudflare Rules Catalogue

All WAF, Transform, and Redirect rules for the Humanities Directory project.
**Update this file every time a Cloudflare rule is added, modified, or deleted.**

Each rule entry includes: purpose, expression, action, and the reasoning (WHY).

---

## WAF Custom Rules

### Rule 1 — Block write methods to WP REST API

| Field | Value |
|-------|-------|
| **Name** | Block REST API writes |
| **Expression** | `(http.request.uri.path contains "/wp-json" and http.request.method in {"POST" "PUT" "PATCH" "DELETE"})` |
| **Action** | Block |
| **Priority** | 1 |

**Why:** The Astro build only needs GET requests to `/wp-json`. Any write method from an unauthenticated source is either a scraping attempt, an exploit probe, or a mistake. Blocking at the CDN edge means the origin server never receives the request.

---

### Rule 2 — Block XML-RPC endpoint

| Field | Value |
|-------|-------|
| **Name** | Block XML-RPC |
| **Expression** | `(http.request.uri.path eq "/xmlrpc.php")` |
| **Action** | Block |
| **Priority** | 2 |

**Why:** XML-RPC is a legacy WordPress endpoint used for remote publishing. It is also the #1 brute-force amplification vector (one request can test thousands of passwords). This site has no use for it; blocking at edge costs zero performance and eliminates the entire attack class.

---

### Rule 3 — Block access to wp-login for non-allowlisted IPs

| Field | Value |
|-------|-------|
| **Name** | Restrict wp-login by IP |
| **Expression** | `(http.request.uri.path eq "/wp-login.php" and not ip.src in {YOUR_OFFICE_IP/32 YOUR_HOME_IP/32})` |
| **Action** | Block (or Managed Challenge if IPs are dynamic) |
| **Priority** | 3 |

**Why:** Brute-forcing WP login is automated and constant. Restricting the login page to known IP ranges eliminates credential-stuffing attacks without affecting legitimate admins. If IPs are dynamic, use Cloudflare Access (Zero Trust) instead (see Rule 4).

---

### Rule 4 — Cloudflare Access: WP Admin gate (preferred over IP allowlist)

| Field | Value |
|-------|-------|
| **Name** | Protect WP Admin with Zero Trust |
| **Type** | Cloudflare Access Application |
| **Path** | `cms.humanidades.example.edu/wp-admin/*` |
| **Auth method** | One-time PIN to allowlisted email domains |

**Why:** Cloudflare Access provides identity-aware access control without a VPN. Admins authenticate via email OTP before any request reaches the WP origin. This is more robust than IP allowlisting when staff work from home or travel.

---

### Rule 5 — Security Headers (Transform Rule)

| Field | Value |
|-------|-------|
| **Name** | Inject security response headers |
| **Type** | HTTP Response Header Modification |
| **Expression** | `true` (applies to all responses) |

Headers injected:

```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
```

**Why — X-Content-Type-Options:** Prevents browsers from MIME-sniffing responses; stops certain XSS attack classes.
**Why — X-Frame-Options:** Prevents clickjacking by blocking the site from being embedded in an iframe on a third-party domain.
**Why — Referrer-Policy:** Limits referrer data sent to third parties; reduces information leakage.
**Why — Permissions-Policy:** Disables browser APIs the site does not use; reduces attack surface if a dependency is ever compromised.

---

### Rule 6 — Rate Limit: wp-login.php

| Field | Value |
|-------|-------|
| **Name** | Rate limit login attempts |
| **Type** | Rate Limiting Rule |
| **Expression** | `(http.request.uri.path eq "/wp-login.php" and http.request.method eq "POST")` |
| **Threshold** | 5 requests per 60 seconds per IP |
| **Action** | Block for 10 minutes |

**Why:** Even with IP allowlisting (Rule 3), having a rate limit as a second layer means a compromised allowlisted IP cannot run a fast brute-force.

---

### Rule 7 — Cache static Astro assets aggressively

| Field | Value |
|-------|-------|
| **Name** | Cache Astro static assets |
| **Type** | Cache Rule |
| **Expression** | `(http.request.uri.path matches "\\.(js|css|woff2|jpg|png|webp|svg|ico)$")` |
| **Edge TTL** | 1 year |
| **Browser TTL** | 1 year |

**Why:** Astro generates content-hashed filenames for all assets. A 1-year cache is safe because filename changes force cache misses. Aggressive caching eliminates origin requests and is the primary mechanism for Lighthouse performance scores.

---

## Redirect Rules

### Redirect 1 — www to non-www (canonical)

| Field | Value |
|-------|-------|
| **Expression** | `(http.host eq "www.humanidades.example.edu")` |
| **Action** | Redirect 301 to `https://humanidades.example.edu${http.request.uri.path}` |

**Why:** Enforces a single canonical domain. Prevents duplicate content indexing in search engines.

---

## Maintenance notes

- When adding a new rule, also add an entry in this file in the same PR.
- Screenshot the CF dashboard after creating a rule and add it to `/docs/screenshots/`.
- Review this file during the quarterly maintenance checklist.
