# AGENTS.md
## Contexto para agentes de IA que trabajen en este repositorio

Cualquier agente que retome este proyecto debe leer este archivo primero.

---

## ¿Qué es este proyecto?

Directorio de personas de una facultad de humanidades universitaria.
Arquitectura headless: **WordPress como CMS** (solo API REST) + **Astro como front-end estático**.
Es un proyecto de portafolio real con cliente institucional.

**Repositorio:** `/home/noone/Documents/humanities-web`

---

## Stack y versiones fijas

| Tecnología | Versión | Por qué fija |
|-----------|---------|-------------|
| Node.js | 20 (ver `.nvmrc`) | Astro 5+ requiere Node 22; el entorno actual tiene v20 |
| Astro | 4.x | Compatible con Node 20; no actualizar a 5 sin actualizar Node |
| Tailwind | vía `@astrojs/tailwind` | Integración instalada en `web/` |
| WordPress | 6.4+ | Hosted en InfinityFree (tier gratuito) |

---

## Convención de idioma — CRÍTICO

- **Comentarios en código:** español
- **Identificadores, variables, funciones, clases, nombres de archivo:** inglés (no tocar)
- **README.md:** español
- **AGENTS.md:** español
- **Documentación técnica** (`ARCHITECTURE.md`): inglés
- **Documentación de usuario** (`EDITOR-GUIDE.md`, `MAINTENANCE-GUIDE.md`): español

---

## Estructura de archivos

```
humanities-web/
├── web/                            # Proyecto Astro (front-end)
│   ├── src/
│   │   ├── components/PersonCard.astro
│   │   ├── layouts/BaseLayout.astro
│   │   ├── pages/
│   │   │   ├── index.astro         # Listado del directorio
│   │   │   └── personas/[slug].astro  # Perfil individual
│   │   └── lib/wp.ts               # Cliente REST API de WP
│   ├── astro.config.mjs
│   ├── tailwind.config.mjs
│   └── .env.example                # Copiar a .env y configurar WP_API_BASE_URL
├── wordpress/
│   ├── plugin/humanities-people-cpt.php  # CPT + ACF + filtro REST
│   ├── wp-config-security.php             # Snippet de hardening
│   └── htaccess-security.txt              # Reglas base .htaccess
├── cms-export/                            # JSON de grupos de campos ACF (versionar)
├── docs/
│   ├── ARCHITECTURE.md
│   ├── ROADMAP.md                 # Fases, tareas pendientes, decisiones abiertas
│   ├── EDITOR-GUIDE.md            # Español, para la secretaría
│   ├── MAINTENANCE-GUIDE.md       # Español, checklist trimestral
│   ├── CHANGELOG.md
│   └── cloudflare-rules.md
├── .nvmrc                         # Node 20
├── .editorconfig
├── .gitignore
└── AGENTS.md                      # Este archivo
```

---

## CPT WordPress: `persona`

- Endpoint REST: `/wp-json/wp/v2/persona`
- Campos ACF: `titulo`, `departamento`, `areas_investigacion`, `email`, `biografia`, `publicaciones`
- El campo `publicaciones` es un **Repetidor** — requiere ACF Pro o CMB2 (ACF Free no lo tiene)
- `show_in_rest: true` está habilitado en el CPT; el filtro `rest_prepare_persona` expone los campos ACF

---

## Variables de entorno

Copiar `web/.env.example` a `web/.env` y definir:

```
WP_API_BASE_URL=https://cms.humanidades.example.edu
SITE_URL=https://humanidades.example.edu
```

Sin `WP_API_BASE_URL`, `getAllPersonas()` retorna un array vacío (no rompe el build).

---

## Paleta de marca (Tailwind)

```js
brand: {
  DEFAULT: '#1a3a5c', // azul marino
  light:   '#2d5f8a',
  accent:  '#c8a951', // dorado
}
```
Actualizar con los valores hex reales de la institución antes del lanzamiento.

---

## Restricciones — no negociables

- Solo plugins con **+100k instalaciones activas** y mantenimiento reciente
- Sin page builders en WP
- Sin `npm install` de dependencias no aprobadas sin documentar el motivo
- ACF Free o CMB2 únicamente (sin ACF Pro sin aprobación del cliente)
- No mover la estructura de permalinks de WP sin actualizar `getStaticPaths()`
- Toda regla de Cloudflare nueva debe documentarse en `docs/cloudflare-rules.md`

---

## Estado al 2026-05-02

### Completado
- Scaffold completo del repositorio
- Proyecto Astro 4 con Tailwind
- `BaseLayout`, `PersonCard`, `index.astro`, `[slug].astro`
- `web/src/lib/wp.ts` — cliente REST API tipado
- Plugin WP completo
- Snippets de seguridad WP (`wp-config-security.php`, `htaccess-security.txt`)
- Toda la documentación (`ARCHITECTURE`, `EDITOR-GUIDE`, `MAINTENANCE-GUIDE`, `CHANGELOG`, `cloudflare-rules`)

### Pendiente
- Git init + primer commit
- `cms-export/acf-person-fields.json`
- Mock data para desarrollo local sin WP
- Favicon SVG (`web/public/favicon.svg`)
- Imagen OG por defecto (`web/public/og-default.png`)
- Configuración de Cloudflare Pages (build: `npm run build`, output: `dist/`)
- UpdraftPlus → Google Drive (semana 1)
- Subdominio de staging documentado en README
